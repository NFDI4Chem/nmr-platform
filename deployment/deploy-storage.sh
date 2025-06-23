#!/bin/bash
# Storage (MinIO) Deployment Script for NMR Platform

set -euo pipefail

# Configuration
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly COMPOSE_FILE="$SCRIPT_DIR/docker-compose.minio.yml"
readonly ENV_FILE="$SCRIPT_DIR/.env.minio"

# Colors for output
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly RED='\033[0;31m'
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

# Check requirements
check_requirements() {
    log "Checking requirements..."
    
    [[ -f "$COMPOSE_FILE" ]] || error "Docker compose file $COMPOSE_FILE not found"
    [[ -f "$ENV_FILE" ]] || error "Environment file $ENV_FILE not found"
    
    command -v docker >/dev/null 2>&1 || error "Docker is not installed"
    command -v docker-compose >/dev/null 2>&1 || error "Docker Compose is not installed"
    
    success "Requirements check passed"
}

# Deploy MinIO
deploy_minio() {
    log "Deploying MinIO standalone..."
    
    cd "$SCRIPT_DIR"
    
    # Pull latest MinIO image
    log "Pulling latest MinIO image..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" pull
    
    # Start MinIO service
    log "Starting MinIO service..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d
    
    # Wait for MinIO to be healthy
    wait_for_health
    
    success "MinIO deployment completed successfully!"
    
    # Display access information
    local minio_user=$(grep MINIO_ROOT_USER "$ENV_FILE" | cut -d'=' -f2)
    log "MinIO is available at: http://localhost:9000"
    log "MinIO Console is available at: http://localhost:9001"
    log "Default credentials: ${minio_user} / (password from env file)"
}

# Wait for MinIO health
wait_for_health() {
    local timeout=60
    local interval=5
    local elapsed=0
    
    log "Waiting for MinIO to be healthy..."
    
    while [[ $elapsed -lt $timeout ]]; do
        if docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps minio | grep -q "healthy\|Up"; then
            success "MinIO is healthy"
            return 0
        fi
        
        sleep $interval
        elapsed=$((elapsed + interval))
        echo -n "."
    done
    
    error "MinIO failed to become healthy within ${timeout}s"
}

# Stop MinIO
stop_minio() {
    log "Stopping MinIO..."
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" down
    success "MinIO stopped"
}

# Show MinIO status
status_minio() {
    log "MinIO Status:"
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" ps
}

# Show MinIO logs
logs_minio() {
    log "MinIO Logs:"
    docker-compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" logs -f minio
}

# Main function
main() {
    case "${1:-deploy}" in
        deploy)
            check_requirements
            deploy_minio
            ;;
        stop)
            stop_minio
            ;;
        restart)
            stop_minio
            sleep 2
            check_requirements
            deploy_minio
            ;;
        status)
            status_minio
            ;;
        logs)
            logs_minio
            ;;
        *)
            echo "Usage: $0 {deploy|stop|restart|status|logs}"
            echo ""
            echo "Commands:"
            echo "  deploy   - Deploy MinIO standalone (default)"
            echo "  stop     - Stop MinIO service"
            echo "  restart  - Restart MinIO service"
            echo "  status   - Show MinIO status"
            echo "  logs     - Show MinIO logs"
            exit 1
            ;;
    esac
}

main "$@" 