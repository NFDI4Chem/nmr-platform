FROM dunglas/frankenphp:latest-alpine

ARG COMPOSER_AUTH
# Setup working directory
WORKDIR /var/www/html

# Install system dependencies and build tools in fewer layers
RUN apk update && \
    apk add --no-cache \
        zip unzip git curl \
        libpng-dev libzip-dev icu-dev oniguruma-dev \
        postgresql-dev zlib-dev \
        nodejs npm \
        autoconf gcc g++ make && \
    rm -rf /var/cache/apk/*

# Install PostgreSQL client
RUN apk --update add postgresql17-client --repository=https://dl-cdn.alpinelinux.org/alpine/edge/main && \
    rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd intl zip

# Install APCu and Redis via PECL and clean up build dependencies in same layer
RUN pecl install apcu redis && \
    docker-php-ext-enable apcu redis && \
    apk del autoconf gcc g++ make && \
    rm -rf /tmp/pear

# Configure PHP - combine echo commands
RUN { \
        echo 'max_execution_time = 3600'; \
        echo 'memory_limit = 4G'; \
        echo 'upload_max_filesize = 100M'; \
        echo 'post_max_size = 250M'; \
        echo 'max_input_time = 3600'; \
    } > /usr/local/etc/php/conf.d/docker-php-custom.ini

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP from custom file
COPY deployment/config/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copy package files first for better caching
COPY composer.json composer.lock package*.json ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# Install Node dependencies  
RUN npm ci

# Copy project files
COPY . .

# Create directories and set permissions in one layer
RUN mkdir -p /var/www/html/config/caddy \
             /data/caddy/data \
             /data/caddy/config && \
    chmod -R 775 /var/www/html/config \
                 /var/www/html/public && \
    chmod -R 755 /data/caddy && \
    chown -R www-data:www-data /var/www/html/storage \
                               /var/www/html/bootstrap/cache \
                               /var/www/html/config \
                               /var/www/html/public \
                               /data/caddy

# Build frontend assets
RUN npm run build && \
    npm cache clean --force

# Configure Caddy
COPY deployment/config/Caddyfile /etc/caddy/Caddyfile

# Expose port
EXPOSE 80 443

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Default command
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]