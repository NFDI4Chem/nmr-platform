#!/bin/bash

# Build script for Docker image with Composer authentication
# Usage: ./build-with-auth.sh [tag]

set -e

# Default tag
TAG=${1:-"nfdi4chem/nmr-platform:latest"}

# Check if auth.json exists or create from environment
if [ ! -f "auth.json" ]; then
    if [ -n "$COMPOSER_AUTH_JSON" ]; then
        echo "Creating auth.json from environment variable..."
        echo "$COMPOSER_AUTH_JSON" > auth.json
    else
        echo "Error: auth.json file not found and COMPOSER_AUTH_JSON environment variable not set"
        echo "Please provide one of the following:"
        echo "1. Create an auth.json file in the current directory"
        echo "2. Set COMPOSER_AUTH_JSON environment variable"
        echo ""
        echo "Example auth.json format:"
        cat << 'EOF'
{
    "http-basic": {
        "repo.packagist.com": {
            "username": "token",
            "password": "your-auth-token"
        }
    },
    "github-oauth": {
        "github.com": "your-github-token"
    }
}
EOF
        exit 1
    fi
fi

# Ensure BuildKit is enabled
export DOCKER_BUILDKIT=1

echo "Building Docker image: $TAG"
echo "Using auth.json for Composer authentication..."

# Build with secret mount
docker build \
    --secret id=composer_auth,src=auth.json \
    --tag "$TAG" \
    --file deployment/Dockerfile \
    .

echo "Build completed: $TAG"

# Clean up temporary auth.json if it was created from env var
if [ -n "$COMPOSER_AUTH_JSON" ]; then
    rm -f auth.json
    echo "Cleaned up temporary auth.json"
fi 