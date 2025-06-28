#!/bin/bash

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
COMPOSE_FILE="$SCRIPT_DIR/docker-compose.prod.yml"
ENV_FILE="$PROJECT_ROOT/.env"
APP_IMAGE="nfdi4chem/nmr-platform:latest"
WORKER_IMAGE="nfdi4chem/nmr-platform:latest"
NEW_CONTAINER_ID=""
BACKUP_DIR="./backups"
BUILD=false
DEPLOY=false
BACKUP=false

# === Load environment ===

echo "Script directory: $SCRIPT_DIR"
echo "Project root: $PROJECT_ROOT"
echo "Compose file: $COMPOSE_FILE"
echo "Environment file: $ENV_FILE"

cd "$PROJECT_ROOT"
echo "Changed to directory: $(pwd)"

# Check if .env file exists and is readable
if [[ ! -f ".env" ]]; then
    error ".env file not found in $PROJECT_ROOT"
fi

if [[ ! -r ".env" ]]; then
    error ".env file is not readable in $PROJECT_ROOT"
fi

echo "Loading environment from: $(pwd)/.env"
set -a
source .env
set +a

export COMPOSE_PROJECT_NAME=production

set -x

# === Functions ===

# Utility functions
log() {
    echo "ℹ️  $1"
}

error() {
    echo "❌ Error: $1" >&2
    exit 1
}

success() {
    echo "✅ $1"
}

# Check requirements
check_requirements() {
    log "Checking requirements..."
    
    [[ -f "$COMPOSE_FILE" ]] || error "Docker compose file $COMPOSE_FILE not found"
    [[ -f "$ENV_FILE" ]] || error "Environment file $ENV_FILE not found"
    
    command -v docker >/dev/null 2>&1 || error "Docker is not installed"
    command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is not installed"
    
    # Create acme.json file with correct permissions for Traefik
    local acme_file="$SCRIPT_DIR/acme.json"
    if [[ ! -f "$acme_file" ]]; then
        log "Creating acme.json file for Traefik SSL certificates..."
        touch "$acme_file"
        chmod 600 "$acme_file"
        echo "Created $acme_file with permissions 600"
    else
        # Ensure correct permissions
        chmod 600 "$acme_file"
        echo "Set correct permissions (600) for $acme_file"
    fi
    
    success "Requirements check passed"
}

wait_for_health() {
    echo "⏳ Waiting for new container to pass health check (up to 10 retries)..."
    for i in {1..10}; do
        if check_container_health; then
            echo "✅ Container is healthy."
            return 0
        else
            echo "Retry $i/10: Waiting 60s..."
            sleep 60
        fi
    done
    return 1
}

check_container_health() {
    if [[ -z "$NEW_CONTAINER_ID" ]]; then
        return 1
    fi
    HEALTH=$(docker inspect --format='{{json .State.Health.Status}}' "$NEW_CONTAINER_ID" 2>/dev/null || echo '"unhealthy"')
    [[ "$HEALTH" == *"healthy"* ]]
}

remove_old_containers() {
    local name_prefix=$1
    echo "🧼 Removing old ${name_prefix} container(s)..."

    container_ids=$(docker ps -a --filter "name=${name_prefix}" --format "{{.ID}}")
    sorted_container_ids=$(echo "$container_ids" | xargs docker inspect --format='{{.Created}} {{.ID}}' | sort | awk '{print $2}')
    oldest_container_id=$(echo "$sorted_container_ids" | head -n 1)

    if [ -z "$oldest_container_id" ]; then
        echo "❌ No containers found with name prefix: ${name_prefix}"
        exit 1
    fi

    docker stop "$oldest_container_id"
    cleanup

    echo "✅ Deleted old container ID: $oldest_container_id"
}

# Cleanup
cleanup() {
    echo "Cleaning up..."
    
    # Remove stopped containers
    docker container prune -f >/dev/null 2>&1 || true

    # Remove unused images
    docker image prune -f >/dev/null 2>&1 || true
    
    # Keep only last 5 backups
    if [[ -d "$BACKUP_DIR" ]]; then
        find "$BACKUP_DIR" -name "*.sql" -type f | sort -r | tail -n +6 | xargs -r rm -f
    fi
    
    echo "Cleanup completed"
}

# Check if app is responding
check_app_health(){
    echo "🏥 Checking application health..."
    if docker compose -p "$COMPOSE_PROJECT_NAME" -f "$COMPOSE_FILE" exec -T app curl -f http://localhost/health > /dev/null 2>&1; then
        echo "✅ Application is healthy!"
    else
        echo "❌ Application health check failed"
        echo "📋 Showing app logs:"
        docker compose -f "$COMPOSE_FILE" logs app --tail=50
        exit 1
    fi
}
deploy_service() {
    local service=$1
    local image=$2
    local run_healthcheck=$3

    check_requirements

    if [ "$(docker pull "$image" | grep -c "Status: Image is up to date")" -eq 0 ]; then
        echo "📦 New $(echo "$service" | tr '[:lower:]' '[:upper:]') image available."

        backup_database
        
        docker compose -f "$COMPOSE_FILE" up -d "$service" --scale "$service"=2 --no-deps --no-recreate
        NEW_CONTAINER_ID=$(docker ps -q -l)
        echo "🔍 New container ID: $NEW_CONTAINER_ID"

        if wait_for_health; then
            remove_old_containers "$service"
            echo "✅ Deployment of $service done successfully.."
            run_migration_and_clear_cache
            echo "Application is available at: https://platform.fsu.nmrxiv.org/"
        else
            echo "❌ Deployment aborted: new $service container is unhealthy."
            docker stop "$NEW_CONTAINER_ID"
            docker rm "$NEW_CONTAINER_ID"
            exit 1
        fi
    else
        echo "✅ No update for $service Skipping deployment."
    fi
}

# Create database backup
backup_database() {
    echo "Creating database backup..."
    
    mkdir -p "$BACKUP_DIR"
    local backup_file="$BACKUP_DIR/db_backup_$(date +%Y%m%d_%H%M%S).sql"
    
    if docker compose -p "$COMPOSE_PROJECT_NAME" -f "$COMPOSE_FILE"  exec -T postgres \
        pg_dump -h localhost -U "${DB_USERNAME}" "${DB_DATABASE}" > "$backup_file" 2>/dev/null; then
        echo "Database backup created: $backup_file"
    else
        echo "Database backup failed. Please check your database connection and credentials."
    fi
}

# Run database seeders
run_migration_and_clear_cache() {
    echo "Running database migration..."
    
    # Run seeders
    echo "Executing Laravel database migration..."
    docker compose -f "$COMPOSE_FILE" exec -T app php artisan migrate --force
    docker compose -f "$COMPOSE_FILE" exec -T app php artisan cache:clear
    docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize:clear
    docker compose -f "$COMPOSE_FILE" exec -T app php artisan optimize

    docker compose -f "$COMPOSE_FILE" ps

    echo "Database migration completed successfully"
}

# === Parse arguments ===
while [[ $# -gt 0 ]]; do
    case $1 in
        --build) BUILD=true; shift ;;
        --deploy) DEPLOY=true; shift ;;
        --backup) BACKUP=true; shift ;;
        *) echo "Unknown option: $1"; exit 1 ;;
    esac
done


# === Deployment Flow ===
if [ "$DEPLOY" = true ]; then
    echo "Starting zero-downtime deployment..."
    
    deploy_service app "$APP_IMAGE" true
    # deploy_service worker "$WORKER_IMAGE" true
    
elif [ "$BUILD" = true ]; then
    if docker compose -f "$COMPOSE_FILE" ps -q | grep -q .; then 
        docker compose -f "$COMPOSE_FILE" down --remove-orphans; 
    fi

    echo "Building containers..."
    docker compose -f "$COMPOSE_FILE" build --no-cache
    docker compose -f "$COMPOSE_FILE" up -d

    echo "Waiting for database to be ready..."
    sleep 10

    run_migration_and_clear_cache
    
    cleanup 
    echo "🎉 Build completed successfully!"
    echo "Application is available at: https://platform.fsu.nmrxiv.org/"

elif [ "$BACKUP" = true ]; then
    backup_database
else 
    echo "Skipping build and deploy step — please pass at least one argument (--build (if you want to build everything for the first time) or --deploy(for zero downtime deployment))..."
fi
