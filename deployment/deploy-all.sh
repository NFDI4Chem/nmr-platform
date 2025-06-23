#!/bin/bash
# Unified Deployment Script for NMR Platform (Main App + MinIO)

set -euo pipefail

# Configuration
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly MAIN_DEPLOY_SCRIPT="$SCRIPT_DIR/deploy.sh"
readonly MINIO_DEPLOY_SCRIPT="$SCRIPT_DIR/deploy-minio.sh"

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
    exit 1
}

# Check if required scripts exist
check_scripts() {
    [[ -f "$MAIN_DEPLOY_SCRIPT" ]] || error "Main deployment script not found: $MAIN_DEPLOY_SCRIPT"
    [[ -f "$MINIO_DEPLOY_SCRIPT" ]] || error "MinIO deployment script not found: $MINIO_DEPLOY_SCRIPT"
    [[ -x "$MAIN_DEPLOY_SCRIPT" ]] || error "Main deployment script is not executable: $MAIN_DEPLOY_SCRIPT"
    [[ -x "$MINIO_DEPLOY_SCRIPT" ]] || error "MinIO deployment script is not executable: $MINIO_DEPLOY_SCRIPT"
}

# Deploy MinIO
deploy_minio() {
    log "🗄️  Deploying MinIO..."
    if "$MINIO_DEPLOY_SCRIPT" deploy; then
        success "MinIO deployment completed"
    else
        error "MinIO deployment failed"
    fi
}

# Deploy main application
deploy_main() {
    log "🚀 Deploying main application..."
    if "$MAIN_DEPLOY_SCRIPT" simple-deploy; then
        success "Main application deployment completed"
    else
        error "Main application deployment failed"
    fi
}

# Deploy both (MinIO first, then main app)
deploy_all() {
    log "🔄 Starting full NMR Platform deployment..."
    
    # Deploy MinIO first
    deploy_minio
    
    # Wait a moment for MinIO to be fully ready
    log "Waiting for MinIO to be fully ready..."
    sleep 10
    
    # Deploy main application
    deploy_main
    
    success "🎉 Full deployment completed successfully!"
    log "📍 MinIO is available at: http://localhost:9000"
    log "📍 MinIO Console is available at: http://localhost:9001"
    log "📍 Main application is available at: http://localhost"
}

# Stop MinIO
stop_minio() {
    log "🛑 Stopping MinIO..."
    if "$MINIO_DEPLOY_SCRIPT" stop; then
        success "MinIO stopped"
    else
        warning "Failed to stop MinIO (it may not be running)"
    fi
}

# Stop main application
stop_main() {
    log "🛑 Stopping main application..."
    cd "$SCRIPT_DIR"
    if docker-compose -f docker-compose.prod.yml --env-file .env.production down; then
        success "Main application stopped"
    else
        warning "Failed to stop main application (it may not be running)"
    fi
}

# Stop both services
stop_all() {
    log "🛑 Stopping all services..."
    stop_main
    stop_minio
    success "All services stopped"
}

# Show status of both deployments
status_all() {
    log "📊 NMR Platform Status:"
    echo ""
    
    log "MinIO Status:"
    "$MINIO_DEPLOY_SCRIPT" status || true
    echo ""
    
    log "Main Application Status:"
    cd "$SCRIPT_DIR"
    docker-compose -f docker-compose.prod.yml --env-file .env.production ps || true
    echo ""
}

# Show logs for both deployments
logs_all() {
    local service=${1:-""}
    
    if [[ "$service" == "minio" ]]; then
        log "📋 MinIO Logs:"
        "$MINIO_DEPLOY_SCRIPT" logs
    elif [[ "$service" == "app" ]]; then
        log "📋 Main Application Logs:"
        cd "$SCRIPT_DIR"
        docker-compose -f docker-compose.prod.yml --env-file .env.production logs -f
    else
        log "📋 All Service Logs (showing last 50 lines each):"
        echo ""
        echo "=== MinIO Logs ==="
        cd "$SCRIPT_DIR"
        docker-compose -f docker-compose.minio.yml --env-file .env.minio logs --tail=50 || true
        echo ""
        echo "=== Main Application Logs ==="
        docker-compose -f docker-compose.prod.yml --env-file .env.production logs --tail=50 || true
    fi
}

# Restart both services
restart_all() {
    log "🔄 Restarting all services..."
    stop_all
    sleep 5
    deploy_all
}

# Health check for both services
health_check() {
    log "🏥 Performing health checks..."
    local failed=0
    
    # Check MinIO health
    log "Checking MinIO health..."
    if curl -f -s http://localhost:9000/minio/health/live >/dev/null 2>&1; then
        success "✅ MinIO is healthy"
    else
        error "❌ MinIO health check failed"
        failed=1
    fi
    
    # Check main application health
    log "Checking main application health..."
    if curl -f -s http://localhost/health >/dev/null 2>&1; then
        success "✅ Main application is healthy"
    elif curl -f -s http://localhost >/dev/null 2>&1; then
        success "✅ Main application is responding"
    else
        error "❌ Main application health check failed"
        failed=1
    fi
    
    if [[ $failed -eq 0 ]]; then
        success "🎉 All health checks passed!"
    else
        error "❌ Some health checks failed"
    fi
}

# Auto-deploy with git checking (for main app only)
auto_deploy_main() {
    log "🔍 Running auto-deployment for main application..."
    "$MAIN_DEPLOY_SCRIPT" auto-deploy
}

# Watch for changes (for main app only)
watch_main() {
    local interval=${1:-300}
    log "👀 Starting watch mode for main application (checking every ${interval}s)..."
    "$MAIN_DEPLOY_SCRIPT" watch "$interval"
}

# Backup function (for main app database)
backup() {
    log "💾 Creating backup..."
    "$MAIN_DEPLOY_SCRIPT" backup
}

# Show usage information
show_usage() {
    echo "Usage: $0 {deploy|deploy-minio|deploy-app|stop|stop-all|restart|status|logs|health|auto-deploy|watch|backup}"
    echo ""
    echo "🚀 Deployment Commands:"
    echo "  deploy         - Deploy both MinIO and main application (default)"
    echo "  deploy-minio   - Deploy only MinIO"
    echo "  deploy-app     - Deploy only main application"
    echo ""
    echo "🛑 Control Commands:"
    echo "  stop           - Stop both services"
    echo "  stop-all       - Stop both services (alias for stop)"
    echo "  restart        - Restart both services"
    echo ""
    echo "📊 Monitoring Commands:"
    echo "  status         - Show status of both services"
    echo "  logs [service] - Show logs (service: minio|app|all)"
    echo "  health         - Perform health checks on both services"
    echo ""
    echo "🔄 Automation Commands (Main App Only):"
    echo "  auto-deploy    - Check for git updates and deploy main app if found"
    echo "  watch [secs]   - Watch for git changes and auto-deploy (default: 300s)"
    echo ""
    echo "🛠️  Utility Commands:"
    echo "  backup         - Create database backup"
    echo ""
    echo "📍 Service URLs:"
    echo "  Main App:      http://localhost"
    echo "  MinIO API:     http://localhost:9000"
    echo "  MinIO Console: http://localhost:9001"
}

# Main function
main() {
    check_scripts
    
    case "${1:-deploy}" in
        deploy|deploy-all)
            deploy_all
            ;;
        deploy-minio)
            deploy_minio
            ;;
        deploy-app|deploy-main)
            deploy_main
            ;;
        stop|stop-all)
            stop_all
            ;;
        stop-minio)
            stop_minio
            ;;
        stop-app|stop-main)
            stop_main
            ;;
        restart|restart-all)
            restart_all
            ;;
        restart-minio)
            stop_minio
            sleep 2
            deploy_minio
            ;;
        restart-app|restart-main)
            stop_main
            sleep 2
            deploy_main
            ;;
        status)
            status_all
            ;;
        logs)
            logs_all "${2:-}"
            ;;
        health)
            health_check
            ;;
        auto-deploy)
            auto_deploy_main
            ;;
        watch)
            watch_main "${2:-300}"
            ;;
        backup)
            backup
            ;;
        help|--help|-h)
            show_usage
            ;;
        *)
            error "Unknown command: $1"
            echo ""
            show_usage
            exit 1
            ;;
    esac
}

main "$@" 