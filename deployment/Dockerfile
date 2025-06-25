# Multi-stage build for optimized production image
FROM composer:2.7 AS composer
FROM dunglas/frankenphp:php8.3-alpine AS base

# Install system dependencies and build tools in a single layer
RUN apk add --no-cache \
    postgresql-dev \
    curl \
    zip \
    unzip \
    autoconf \
    build-base \
    linux-headers \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear \
    && apk del autoconf build-base linux-headers

# Production stage
FROM base AS production

# Set working directory
WORKDIR /var/www/html

# Copy PHP configuration
COPY config/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy Caddyfile
COPY deployment/config/Caddyfile /etc/caddy/Caddyfile

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Default command
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"] 