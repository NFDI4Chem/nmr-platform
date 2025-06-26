# Multi-stage build for optimized production image

# Stage 1: Node.js build stage for Vite assets
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node.js dependencies
RUN npm ci

# Copy frontend assets and configuration
COPY resources/ ./resources/
COPY public/ ./public/
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

# Build production assets
RUN npm run build

# Stage 2: Base runtime image with composer
FROM dunglas/frankenphp:php8.3-alpine AS base

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install system dependencies and build tools
RUN apk add --no-cache \
    postgresql-dev \
    curl \
    zip \
    unzip \
    git \
    autoconf \
    build-base \
    linux-headers \
    imagemagick-dev \
    icu-dev \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pcntl \
    opcache \
    intl \
    exif \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && rm -rf /tmp/pear \
    && apk del autoconf build-base linux-headers

# Stage 3: Production image
FROM base AS production

# Create non-root user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (production only)
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --no-interaction

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative

# Copy built assets from node stage
COPY --from=node-builder --chown=www:www /app/public/build /var/www/html/public/build

# Copy PHP configuration
COPY deployment/config/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy Caddyfile
COPY deployment/config/Caddyfile /etc/caddy/Caddyfile

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Switch to non-root user
USER www

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Default command
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]