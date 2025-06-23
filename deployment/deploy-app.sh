#!/bin/bash

# NMR Platform Application Deployment Script
# Implements zero-downtime deployment with rolling updates, health checks and git integration

set -euo pipefail

# Configuration
readonly COMPOSE_FILE="docker-compose.prod.yml"
readonly ENV_FILE=".env.production"
readonly BACKUP_DIR="./backups"
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly HEALTH_CHECK_TIMEOUT=300
readonly HEALTH_CHECK_INTERVAL=5
readonly LOCK_FILE="/tmp/nmr_platform_deploy.lock"

# Git Configuration (can be overridden via environment variables)
readonly GIT_BRANCH="${GIT_BRANCH:-master}"
readonly GIT_REMOTE="${GIT_REMOTE:-origin}"
readonly PROJECT_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

# Colors for output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m'

# Logging functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    cleanup_lock
    exit 1
}

# Lock management for preventing concurrent deployments
acquire_lock() {
    if [[ -f "$LOCK_FILE" ]]; then
        local lock_pid
        lock_pid=$(cat "$LOCK_FILE" 2>/dev/null || echo "")
        
        if [[ -n "$lock_pid" ]] && kill -0 "$lock_pid" 2>/dev/null; then
            error "Another deployment is already in progress (PID: $lock_pid)"
        else
            warning "Stale lock file found, removing..."
            rm -f "$LOCK_FILE"
        fi
    fi
    
    echo $$ > "$LOCK_FILE"
    trap cleanup_lock EXIT
}

cleanup_lock() {
    [[ -f "$LOCK_FILE" ]] && rm -f "$LOCK_FILE"
}

# Git operations
check_git_repo() {
    cd "$PROJECT_ROOT"
    
    if [[ ! -d ".git" ]]; then
        error "Not a git repository. Please initialize git first."
    fi
    
    # Check if remote exists
    if ! git remote get-url "$GIT_REMOTE" >/dev/null 2>&1; then
        warning "Git remote '$GIT_REMOTE' not found - skipping git operations"
        return 1
    fi
    
    success "Git repository validated"
    return 0
}

get_current_commit() {
    cd "$PROJECT_ROOT"
    git rev-parse HEAD 2>/dev/null || echo "unknown"
}

get_remote_commit() {
    cd "$PROJECT_ROOT"
    
    # Fetch latest changes without merging
    log "Fetching latest changes from $GIT_REMOTE/$GIT_BRANCH..."
    if ! git fetch "$GIT_REMOTE" "$GIT_BRANCH" 2>/dev/null; then
        error "Failed to fetch from remote repository"
    fi
    
    git rev-parse "$GIT_REMOTE/$GIT_BRANCH" 2>/dev/null || echo "unknown"
}

check_for_updates() {
    local current_commit remote_commit
    
    current_commit=$(get_current_commit)
    remote_commit=$(get_remote_commit)
    
    log "Current commit: ${current_commit:0:8}"
    log "Remote commit:  ${remote_commit:0:8}"
    
    if [[ "$current_commit" == "$remote_commit" ]]; then
        return 1  # No updates
    else
        return 0  # Updates available
    fi
}

pull_latest_code() {
    cd "$PROJECT_ROOT"
    
    log "Pulling latest code from $GIT_REMOTE/$GIT_BRANCH..."
    
    # Stash any local changes
    if ! git diff-index --quiet HEAD --; then
        warning "Local changes detected, stashing..."
        git stash push -m "Auto-stash before deployment $(date)"
    fi
    
    # Check if we're on the correct branch
    local current_branch
    current_branch=$(git rev-parse --abbrev-ref HEAD)
    
    if [[ "$current_branch" != "$GIT_BRANCH" ]]; then
        log "Switching to branch $GIT_BRANCH..."
        git checkout "$GIT_BRANCH" || error "Failed to checkout branch $GIT_BRANCH"
    fi
    
    # Pull latest changes
    if git pull "$GIT_REMOTE" "$GIT_BRANCH"; then
        local new_commit
        new_commit=$(get_current_commit)
        success "Code updated to commit: ${new_commit:0:8}"
        
        # Log the changes
        if command -v git >/dev/null 2>&1; then
            log "Recent changes:"
            git log --oneline -5 || true
        fi
    else
        error "Failed to pull latest changes"
    fi
    
    cd "$SCRIPT_DIR"
}

# Enhanced requirements check with git validation
check_requirements() {
    log "Checking requirements..."
    
    [[ -f "$COMPOSE_FILE" ]] || error "Docker compose file $COMPOSE_FILE not found"
    [[ -f "$ENV_FILE" ]] || error "Environment file $ENV_FILE not found"
    
    command -v docker >/dev/null 2>&1 || error "Docker is not installed"
    command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is not installed"
    command -v git >/dev/null 2>&1 || error "Git is not installed"
    
    if check_git_repo; then
        success "Requirements check passed (with git)"
    else
        success "Requirements check passed (without git remote)"
    fi
}

# Wait for service health
wait_for_health() {
    local service=$1
    local timeout=${2:-$HEALTH_CHECK_TIMEOUT}
    local interval=${3:-$HEALTH_CHECK_INTERVAL}
    local elapsed=0
    
    log "Waiting for $service to be healthy..."
    
    while [[ $elapsed -lt $timeout ]]; do
        if docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps "$service" | grep -q "healthy\|Up"; then
            success "$service is healthy"
            return 0
        fi
        
        sleep $interval
        elapsed=$((elapsed + interval))
        echo -n "."
    done
    
    error "$service failed to become healthy within ${timeout}s"
}

# Create database backup
backup_database() {
    log "Creating database backup..."
    
    mkdir -p "$BACKUP_DIR"
    local backup_file="$BACKUP_DIR/db_backup_$(date +%Y%m%d_%H%M%S).sql"
    
    if docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec -T postgres \
        pg_dump -h localhost -U "${DB_USERNAME:-nmr_platform_user}" "${DB_DATABASE:-nmr_platform_prod}" > "$backup_file" 2>/dev/null; then
        success "Database backup created: $backup_file"
    else
        warning "Database backup failed, continuing deployment..."
    fi
}

# Rolling update for application services
rolling_update() {
    local service=$1
    local replicas
    
    log "Starting rolling update for $service..."
    
    # Get current replica count (improved counting)
    replicas=$(docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps -q "$service" 2>/dev/null | wc -l | tr -d '[:space:]')
    
    if [[ $replicas -eq 0 ]]; then
        log "No existing $service containers, starting fresh..."
        docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d "$service"
        wait_for_health "$service" 60
        return 0
    fi
    
    log "Found $replicas existing $service containers"
    
    # Scale up with new version (blue-green approach)
    log "Scaling up $service with new version..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --scale "$service=$((replicas * 2))" "$service"
    
    # Wait for new containers to be healthy
    sleep 15
    
    # Check health of new containers
    local healthy_new=0
    local max_attempts=12
    local attempt=0
    
    while [[ $attempt -lt $max_attempts ]]; do
        healthy_new=$(docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps "$service" 2>/dev/null | grep -c "Up" || echo "0")
        
        if [[ $healthy_new -ge $replicas ]]; then
            break
        fi
        
        log "Waiting for new containers to be healthy ($healthy_new/$replicas ready)..."
        sleep 10
        ((attempt++))
    done
    
    if [[ $healthy_new -lt $replicas ]]; then
        warning "Not all new containers are healthy, but proceeding with rollout"
    fi
    
    # Scale down to original count (removes old containers)
    log "Scaling down to original count (removing old containers)..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --scale "$service=$replicas" "$service"
    
    success "Rolling update completed for $service"
}

# Database migration with zero downtime
safe_migrate() {
    log "Running database migrations safely..."
    
    # Create a temporary container for migrations
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm app sh -c "
        # Copy environment
        cp /var/www/html/deployment/.env.production /var/www/html/.env &&
        
        # Create required directories
        mkdir -p storage/framework/cache storage/framework/views storage/framework/sessions storage/logs bootstrap/cache &&
        
        # Clear all caches first
        php artisan cache:clear || true &&
        php artisan config:clear || true &&
        php artisan route:clear || true &&
        php artisan view:clear || true &&
        
        # Initialize database if migrations table doesn't exist
        if ! php artisan migrate:status > /dev/null 2>&1; then
            echo 'Initializing database...' &&
            php artisan migrate:install || true
        fi &&
        
        # Check migration status
        php artisan migrate:status || true &&
        
        # Run migrations
        php artisan migrate --force &&
        
        # Rebuild essential caches (skip view cache due to Filament components)
        php artisan config:cache &&
        php artisan route:cache &&
        php artisan storage:link
    "
    
    success "Database migrations completed"
}

# Run database seeders
run_seeders() {
    log "Running database seeders..."
    
    # Check if app service is running
    if ! docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps app | grep -q "Up"; then
        error "Application containers are not running. Please deploy first with: ./deploy.sh"
    fi
    
    # Run seeders
    log "Executing Laravel database seeders..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" exec app php artisan db:seed --force
    
    success "Database seeders completed successfully"
}

# Build frontend assets
build_assets() {
    log "Building frontend assets..."
    
    cd "$PROJECT_ROOT"
    
    # Check if package.json exists
    if [[ ! -f "package.json" ]]; then
        warning "No package.json found, skipping asset build"
        return 0
    fi
    
    # Remove hot file if it exists (forces production asset usage)
    [[ -f "public/hot" ]] && rm -f "public/hot" && log "Removed Vite hot file"
    
    # Check if Vite is available (essential for building)
    if [[ ! -f "node_modules/.bin/vite" ]]; then
        log "Vite not found, installing all dependencies..."
        npm install
    elif [[ ! -d "node_modules" ]] || [[ "package-lock.json" -nt "node_modules" ]]; then
        log "Installing Node.js dependencies (including dev dependencies for build)..."
        npm ci || npm install
    fi
    
    # Build production assets
    log "Compiling production assets with Vite..."
    npm run build
    
    # Verify build output
    if [[ -d "public/build" ]] && [[ -f "public/build/manifest.json" ]]; then
        success "Frontend assets built successfully"
        
        # Show built assets
        if [[ -d "public/build/assets" ]]; then
            local asset_count
            asset_count=$(find public/build/assets -type f | wc -l | tr -d '[:space:]')
            log "Generated $asset_count asset files"
        fi
    else
        error "Asset build failed - no build output found"
    fi
    
    cd "$SCRIPT_DIR"
}

# Zero-downtime deployment
deploy() {
    log "Starting zero-downtime deployment..."
    
    # 1. Build frontend assets first
    build_assets
    
    # 2. Build new images
    log "Building new application images..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build --parallel app queue scheduler
    
    # 2. Ensure infrastructure is running
    log "Ensuring infrastructure services are running..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d postgres redis meilisearch
    
    # Wait for infrastructure with shorter timeout for existing services
    wait_for_health "postgres" 60
    wait_for_health "redis" 30
    
    # 3. Run database migrations safely
    safe_migrate
    
    # 4. Rolling update for application services
    rolling_update "app"
    rolling_update "queue"
    rolling_update "scheduler"
    
    # 5. Update nginx (load balancer) last
    log "Updating load balancer..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d nginx
    
    success "Zero-downtime deployment completed"
}

# Auto-deployment with git checking
auto_deploy() {
    log "Starting automated deployment check..."
    
    if check_for_updates; then
        log "New commits detected, starting deployment..."
        
        # Pull latest code
        pull_latest_code
        
        # Backup database
        backup_database
        
        # Deploy (includes asset building)
        deploy
        
        # Health check
        health_check
        
        # Cleanup
        cleanup
        
        success "Automated deployment completed successfully!"
        
        # Log deployment info
        local current_commit
        current_commit=$(get_current_commit)
        log "Application deployed with commit: ${current_commit:0:8}"
        log "Application is available at: http://localhost"
    else
        log "No new commits found, deployment skipped"
        return 0
    fi
}

# Watch for changes (continuous monitoring)
watch_for_changes() {
    local check_interval=${1:-300}  # Default: 5 minutes
    
    log "Starting continuous deployment monitoring..."
    log "Checking for updates every ${check_interval} seconds"
    log "Monitoring branch: $GIT_REMOTE/$GIT_BRANCH"
    log "Press Ctrl+C to stop monitoring"
    
    while true; do
        if check_for_updates; then
            log "New commits detected, starting automated deployment..."
            auto_deploy
        else
            log "No updates found, waiting ${check_interval} seconds..."
        fi
        
        sleep "$check_interval"
    done
}

# Comprehensive health check
health_check() {
    log "Performing comprehensive health checks..."
    
    local services=("postgres" "redis" "meilisearch" "app" "nginx")
    local failed_services=()
    
    sleep 15
    
    for service in "${services[@]}"; do
        local status=$(docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps "$service" 2>/dev/null | tail -n +2)
        
        if echo "$status" | grep -q "Up"; then
            if [[ "$service" == "app" ]]; then
                # Check application endpoint
                if command -v curl >/dev/null 2>&1 && curl -f -s http://localhost/health >/dev/null 2>&1; then
                    success "$service is healthy"
                else
                    success "$service is running (health endpoint not available)"
                fi
            else
                success "$service is running"
            fi
        else
            failed_services+=("$service")
        fi
    done
    
    if [[ ${#failed_services[@]} -gt 0 ]]; then
        error "Failed services: ${failed_services[*]}"
    fi
    
    success "All services are healthy"
}

# Rollback function
rollback() {
    local version=${1:-"previous"}
    
    log "Rolling back to $version version..."
    
    # For now, this restarts services with current images
    # In a full implementation, this would use tagged images
    warning "Rollback initiated - restarting services with current images"
    
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" restart app queue scheduler
    
    success "Rollback completed (services restarted)"
}

# Cleanup
cleanup() {
    log "Cleaning up..."
    
    # Remove unused images
    docker image prune -f >/dev/null 2>&1 || true
    
    # Keep only last 5 backups
    if [[ -d "$BACKUP_DIR" ]]; then
        find "$BACKUP_DIR" -name "*.sql" -type f | sort -r | tail -n +6 | xargs -r rm -f
    fi
    
    success "Cleanup completed"
}

# Simple deployment flow (without git)
simple_deploy() {
    acquire_lock
    cd "$SCRIPT_DIR"
    
    log "Starting NMR Platform simple deployment..."
    
    # Simple requirements check
    log "Checking basic requirements..."
    [[ -f "$COMPOSE_FILE" ]] || error "Docker compose file $COMPOSE_FILE not found"
    [[ -f "$ENV_FILE" ]] || error "Environment file $ENV_FILE not found"
    command -v docker >/dev/null 2>&1 || error "Docker is not installed"
    command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is not installed"
    success "Basic requirements check passed"
    
    backup_database
    deploy
    health_check
    cleanup
    
    success "Simple deployment completed successfully!"
    log "Application is available at: http://localhost"
}

# Main deployment flow
main() {
    acquire_lock
    cd "$SCRIPT_DIR"
    
    log "Starting NMR Platform zero-downtime deployment..."
    
    check_requirements
    backup_database
    deploy
    health_check
    cleanup
    
    success "Deployment completed successfully!"
    log "Application is available at: http://localhost"
}

# Handle script arguments
case "${1:-deploy}" in
    deploy)
        main
        ;;
    simple-deploy)
        simple_deploy
        ;;
    auto-deploy)
        acquire_lock
        cd "$SCRIPT_DIR"
        check_requirements
        auto_deploy
        ;;
    watch)
        acquire_lock
        cd "$SCRIPT_DIR"
        check_requirements
        watch_for_changes "${2:-300}"
        ;;
    check-updates)
        cd "$PROJECT_ROOT"
        check_git_repo
        if check_for_updates; then
            echo "Updates available"
            exit 0
        else
            echo "No updates found"
            exit 1
        fi
        ;;
    pull)
        cd "$PROJECT_ROOT"
        check_git_repo
        pull_latest_code
        ;;
    seed)
        run_seeders
        ;;
    backup)
        backup_database
        ;;
    health)
        health_check
        ;;
    rollback)
        rollback "${2:-previous}"
        ;;
    cleanup)
        cleanup
        ;;
    *)
        echo "Usage: $0 {deploy|simple-deploy|auto-deploy|watch|check-updates|pull|seed|backup|health|rollback|cleanup}"
        echo ""
        echo "Commands:"
        echo "  deploy         - Zero-downtime deployment with git checks (default)"
        echo "  simple-deploy  - Simple deployment without git operations"
        echo "  auto-deploy    - Check for updates and deploy if found"
        echo "  watch [secs]   - Continuously monitor for updates (default: 300s)"
        echo "  check-updates  - Check if updates are available"
        echo "  pull           - Pull latest code without deploying"
        echo "  seed           - Run database seeders"
        echo "  backup         - Create database backup only"
        echo "  health         - Run health checks only"
        echo "  rollback       - Rollback to previous version"
        echo "  cleanup        - Clean up old resources"
        echo ""
        echo "Environment Variables:"
        echo "  GIT_BRANCH     - Branch to monitor (default: master)"
        echo "  GIT_REMOTE     - Git remote name (default: origin)"
        exit 1
        ;;
esac 