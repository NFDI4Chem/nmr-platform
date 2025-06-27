ARG PHP_VERSION=8.3
ARG FRANKENPHP_VERSION=latest
ARG NODE_VERSION=18

###########################################
# Composer dependencies stage
###########################################
FROM composer:2 AS composer-deps

WORKDIR /app

COPY composer.json composer.lock ./

ARG COMPOSER_AUTH

ENV COMPOSER_AUTH=$COMPOSER_AUTH

# Install dependencies without dev packages and optimize for production
RUN -COMPOSER_AUTH="$COMPOSER_AUTH" composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-scripts \
    --no-plugins \
    --ignore-platform-req=ext-imagick \
    --ignore-platform-req=ext-intl \
    --ignore-platform-req=ext-pcntl \
    --ignore-platform-req=ext-exif \
    --prefer-dist

COPY . .

RUN composer dump-autoload --optimize --classmap-authoritative --no-dev

###########################################
# Frontend build stage
###########################################
FROM node:${NODE_VERSION}-alpine AS frontend-build

WORKDIR /app

# Copy package files and install dependencies
COPY package*.json ./
RUN npm ci --no-audit --no-fund

# Copy source files and build
COPY --from=composer-deps /app/vendor ./vendor
COPY . .
RUN npm run build && rm -rf node_modules

###########################################
# Production runtime stage
###########################################
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS production

# Security labels
LABEL org.opencontainers.image.title="Laravel Orion POS"
LABEL org.opencontainers.image.description="Production Laravel application with FrankenPHP"
LABEL org.opencontainers.image.vendor="Orion POS"
LABEL org.opencontainers.image.licenses="MIT"

# Build arguments
ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG TZ=UTC

# Environment variables for production
ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    OCTANE_SERVER=frankenphp \
    USER=octane \
    ROOT=/var/www/html \
    TZ=${TZ}

WORKDIR ${ROOT}

# Install system dependencies and PHP extensions in a single layer
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    supervisor \
    unzip \
    && install-php-extensions \
    bcmath \
    exif \
    gd \
    intl \
    opcache \
    pcntl \
    pdo_pgsql \
    pgsql \
    redis \
    sockets \
    zip \
    && ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Create application user and set permissions
RUN groupadd --force -g ${WWWGROUP} ${USER} \
    && useradd -ms /bin/bash --no-log-init --no-user-group -g ${WWWGROUP} -u ${WWWUSER} ${USER} \
    && mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R ${USER}:${USER} ${ROOT} \
    && chmod -R 755 ${ROOT}

# Copy PHP configuration
COPY --chown=${WWWUSER}:${WWWUSER} deployment/php.ini ${PHP_INI_DIR}/conf.d/99-laravel.ini

# Copy supervisor configuration
COPY --chown=${WWWUSER}:${WWWUSER} deployment/supervisord.conf /etc/supervisor/
COPY --chown=${WWWUSER}:${WWWUSER} deployment/octane/FrankenPHP/supervisord.frankenphp.conf /etc/supervisor/conf.d/

# Copy application files
COPY --chown=${WWWUSER}:${WWWUSER} --from=composer-deps /app/vendor ./vendor
COPY --chown=${WWWUSER}:${WWWUSER} --from=frontend-build /app/public ./public
COPY --chown=${WWWUSER}:${WWWUSER} . .

# Copy runtime scripts
COPY --chown=${WWWUSER}:${WWWUSER} --chmod=755 deployment/start-container /usr/local/bin/start-container
COPY --chown=${WWWUSER}:${WWWUSER} --chmod=755 deployment/healthcheck /usr/local/bin/healthcheck

# Set proper permissions for storage and cache
RUN chown -R ${USER}:${USER} storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Switch to non-root user
USER ${USER}

# Health check
HEALTHCHECK --start-period=30s --interval=30s --timeout=10s --retries=3 \
    CMD healthcheck || exit 1

# Expose ports
EXPOSE 8000 443 443/udp 2019

# Start the application
ENTRYPOINT ["start-container"]
