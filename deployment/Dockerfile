#Multi-stage build for optimized production image

# Stage 1: Composer dependencies
FROM dunglas/frankenphp:php8.3-alpine AS composer-builder
ARG COMPOSER_AUTH

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install system dependencies needed for composer
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    autoconf \
    build-base \
    linux-headers \
    postgresql-dev \
    imagemagick-dev \
    icu-dev \
    libzip-dev \
    && rm -rf /var/cache/apk/*

# Configure Git to use HTTPS instead of SSH for GitHub (this fixes the SSH error)
RUN git config --global url."https://github.com/".insteadOf "git@github.com:" && \
    git config --global url."https://".insteadOf "git://"

# Install PHP extensions required by your packages
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pcntl \
    intl \
    exif \
    zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && rm -rf /tmp/pear \
    && apk del autoconf build-base linux-headers

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --no-interaction

# Stage 2: Node.js build stage (with vendor directory)
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

# Copy vendor directory from composer stage
COPY --from=composer-builder /app/vendor/ ./vendor/

# Build production assets
RUN npm run build

# Stage 3: Production image
FROM dunglas/frankenphp:php8.3-alpine AS production

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
    libzip-dev \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pcntl \
    opcache \
    intl \
    exif \
    zip \
    && pecl install redis imagick \
    && docker-php-ext-enable redis imagick \
    && rm -rf /tmp/pear \
    && apk del autoconf build-base linux-headers

# Create non-root user
RUN addgroup -g 1000 -S www && \
    adduser -u 1000 -S www -G www

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor directory from composer stage
COPY --from=composer-builder /app/vendor/ ./vendor/

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