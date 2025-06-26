# Stage 1: Composer to install PHP dependencies
FROM composer:2.7 AS vendor

WORKDIR /app

# Copy only composer files initially for better layer caching
COPY composer.json composer.lock ./

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Base PHP with required extensions
FROM dunglas/frankenphp:php8.3-alpine AS base

RUN apk add --no-cache \
    postgresql-dev \
    curl \
    zip \
    unzip \
    autoconf \
    build-base \
    linux-headers \
    && rm -rf /var/cache/apk/*

RUN docker-php-ext-install -j$(nproc) pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/pear \
    && apk del autoconf build-base linux-headers

# Stage 3: Final production image
FROM base AS production

WORKDIR /var/www/html

# Copy PHP config
COPY deployment/config/php.ini /usr/local/etc/php/conf.d/app.ini

# Copy Caddyfile (FrankenPHP)
COPY deployment/config/Caddyfile /etc/caddy/Caddyfile

# Copy full Laravel application
COPY . .

# Copy vendor folder from composer build
COPY --from=vendor /app/vendor ./vendor

# Set proper permissions (optional but good)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

EXPOSE 80 443

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
