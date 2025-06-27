# =============================================================================
# Laravel NMR Platform - Production Dockerfile
# =============================================================================
# Multi-stage build for Laravel application with FrankenPHP
# Includes Composer authentication for private repositories
# =============================================================================

# Build arguments
ARG PHP_VERSION=8.3
ARG FRANKENPHP_VERSION=latest
ARG COMPOSER_VERSION=latest
ARG NODE_VERSION="18"

# =============================================================================
# Composer stage - Copy Composer binary
# =============================================================================
FROM composer:${COMPOSER_VERSION} AS vendor

# =============================================================================
# Main application stage
# =============================================================================
FROM dunglas/frankenphp:${FRANKENPHP_VERSION}-php${PHP_VERSION} AS final

# Metadata
LABEL maintainer="NFDI4Chem Team"
LABEL org.opencontainers.image.title="NMR Platform"
LABEL org.opencontainers.image.description="Production Laravel NMR research platform with FrankenPHP"
LABEL org.opencontainers.image.source="https://github.com/NFDI4Chem/nmr-platform"
LABEL org.opencontainers.image.licenses="MIT"

# Build arguments
ARG WWWUSER=1000
ARG WWWGROUP=1000
ARG TZ=UTC
ARG APP_DIR=/var/www/html
ARG COMPOSER_AUTH

# Environment variables
ENV COMPOSER_AUTH=$COMPOSER_AUTH
ENV DEBIAN_FRONTEND=noninteractive \
    TERM=xterm-color \
    WITH_HORIZON=false \
    WITH_SCHEDULER=false \
    OCTANE_SERVER=frankenphp \
    USER=octane \
    ROOT=${APP_DIR} \
    COMPOSER_FUND=0 \
    COMPOSER_MAX_PARALLEL_HTTP=24 \
    XDG_CONFIG_HOME=${APP_DIR}/.config \
    XDG_DATA_HOME=${APP_DIR}/.data

WORKDIR ${ROOT}

# Use bash with strict error handling (only for build stage)
SHELL ["/bin/sh", "-eux", "-c"]

# =============================================================================
# System setup and timezone configuration
# =============================================================================
RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone

# =============================================================================
# Install system dependencies and PHP extensions
# =============================================================================
RUN apt-get update \
    && apt-get upgrade -yqq \
    && apt-get install -yqq --no-install-recommends --show-progress \
        apt-utils \
        curl \
        wget \
        vim \
        git \
        ncdu \
        procps \
        ca-certificates \
        supervisor \
        libsodium-dev \
    # Install PHP extensions
    && install-php-extensions \
        bz2 \
        pcntl \
        mbstring \
        bcmath \
        sockets \
        pgsql \
        pdo_pgsql \
        opcache \
        exif \
        pdo_mysql \
        zip \
        intl \
        gd \
        redis \
        rdkafka \
        memcached \
        igbinary \
        ldap \
        imagick \
    # Cleanup
    && apt-get -y autoremove \
    && apt-get clean \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && rm /var/log/lastlog /var/log/faillog

# =============================================================================
# Install Supercronic for scheduled tasks
# =============================================================================
RUN arch="$(uname -m)" \
    && case "$arch" in \
        armhf) _cronic_fname='supercronic-linux-arm' ;; \
        aarch64) _cronic_fname='supercronic-linux-arm64' ;; \
        x86_64) _cronic_fname='supercronic-linux-amd64' ;; \
        x86) _cronic_fname='supercronic-linux-386' ;; \
        *) echo >&2 "error: unsupported architecture: $arch"; exit 1 ;; \
    esac \
    && wget -q "https://github.com/aptible/supercronic/releases/download/v0.2.29/${_cronic_fname}" \
        -O /usr/bin/supercronic \
    && chmod +x /usr/bin/supercronic \
    && mkdir -p /etc/supercronic \
    && echo "*/1 * * * * php ${ROOT}/artisan schedule:run --no-interaction" > /etc/supercronic/laravel

# =============================================================================
# User and permission setup
# =============================================================================
RUN userdel --remove --force www-data \
    && groupadd --force -g ${WWWGROUP} ${USER} \
    && useradd -ms /bin/bash --no-log-init --no-user-group -g ${WWWGROUP} -u ${WWWUSER} ${USER}

RUN chown -R ${USER}:${USER} ${ROOT} /var/log /var/run \
    && chmod -R a+rw ${ROOT} /var/log /var/run

# Configure PHP
RUN cp ${PHP_INI_DIR}/php.ini-production ${PHP_INI_DIR}/php.ini

# Switch to non-root user
USER ${USER}

# =============================================================================
# Install Composer dependencies
# =============================================================================
COPY --link --chown=${WWWUSER}:${WWWUSER} --from=vendor /usr/bin/composer /usr/bin/composer
COPY --link --chown=${WWWUSER}:${WWWUSER} composer.json composer.lock ./

RUN COMPOSER_AUTH="$COMPOSER_AUTH" composer install \
    --no-dev \
    --no-interaction \
    --no-autoloader \
    --no-ansi \
    --no-scripts \
    --ignore-platform-req=ext-imagick \
    --ignore-platform-req=ext-intl \
    --ignore-platform-req=ext-pcntl \
    --ignore-platform-req=ext-exif \
    --audit

# Copy application source code
COPY --link --chown=${WWWUSER}:${WWWUSER} . .

# =============================================================================
# Frontend build stage
# =============================================================================
FROM node:${NODE_VERSION} AS build

ENV ROOT=/var/www/html
WORKDIR ${ROOT}

# Install Node.js dependencies
COPY --link package.json package-lock.json ./
RUN npm ci

# Copy vendor dependencies and source
COPY --link --from=final ${ROOT}/vendor vendor
COPY --link . .

# Build frontend assets
RUN npm run build

# =============================================================================
# Final production stage
# =============================================================================
FROM final

# Reset shell to default for final stage
SHELL ["/bin/sh", "-c"]

WORKDIR ${ROOT}

# Copy built frontend assets
COPY --link --chown=${WWWUSER}:${WWWUSER} --from=build ${ROOT}/public public

# Create required directories and set permissions
RUN mkdir -p \
        storage/framework/{sessions,views,cache,testing} \
        storage/logs \
        bootstrap/cache \
    && chmod -R a+rw storage

# =============================================================================
# Copy configuration files and scripts
# =============================================================================
COPY --link --chown=${WWWUSER}:${WWWUSER} supervisord.conf /etc/supervisor/
COPY --link --chown=${WWWUSER}:${WWWUSER} ./octane/FrankenPHP/supervisord.frankenphp.conf /etc/supervisor/conf.d/
COPY --link --chown=${WWWUSER}:${WWWUSER} supervisord.*.conf /etc/supervisor/conf.d/
COPY --link --chown=${WWWUSER}:${WWWUSER} start-container /usr/local/bin/start-container
COPY --link --chown=${WWWUSER}:${WWWUSER} healthcheck /usr/local/bin/healthcheck
COPY --link --chown=${WWWUSER}:${WWWUSER} php.ini ${PHP_INI_DIR}/conf.d/99-octane.ini

# FrankenPHP embedded PHP configuration
COPY --link --chown=${WWWUSER}:${WWWUSER} php.ini /lib/php.ini

# =============================================================================
# Final Composer optimization
# =============================================================================
RUN composer install \
    --classmap-authoritative \
    --no-interaction \
    --no-ansi \
    --no-dev \
    --ignore-platform-req=ext-imagick \
    --ignore-platform-req=ext-intl \
    --ignore-platform-req=ext-pcntl \
    --ignore-platform-req=ext-exif \
    && composer clear-cache

# Set executable permissions
RUN chmod +x /usr/local/bin/start-container /usr/local/bin/healthcheck

# Add utility functions to bash profile
RUN cat utilities.sh >> ~/.bashrc

# =============================================================================
# Expose ports and configure startup
# =============================================================================
EXPOSE 8000 443 443/udp 2019

ENTRYPOINT ["start-container"]

HEALTHCHECK --start-period=5s --interval=2s --timeout=5s --retries=8 \
    CMD healthcheck || exit 1