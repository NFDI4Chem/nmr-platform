# Multi-stage build for optimized production image
FROM composer:2.7 AS composer

# Copy composer files
WORKDIR /app
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application code and generate optimized autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

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
    nodejs \
    npm \
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
COPY deployment/config/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy Caddyfile
COPY deployment/config/Caddyfile /etc/caddy/Caddyfile

# Copy application with dependencies from composer stage
COPY --from=composer --chown=www-data:www-data /app /var/www/html

# Install Node.js dependencies and build assets
COPY package*.json ./
RUN npm ci --only=production && \
    npm run build && \
    npm cache clean --force && \
    rm -rf node_modules

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create Laravel caches
RUN php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Expose port
EXPOSE 80 443

# Default command
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"] 