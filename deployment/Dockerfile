FROM dunglas/frankenphp:latest-alpine

ARG COMPOSER_AUTH
# Setup working directory
WORKDIR /var/www/html

# Install system dependencies and build tools in fewer layers
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
    nodejs \
    npm \
    && rm -rf /var/cache/apk/*

# Install PostgreSQL client
RUN apk --update add postgresql17-client --repository=https://dl-cdn.alpinelinux.org/alpine/edge/main && \
    rm -rf /var/cache/apk/*

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
             /var/log/supervisor \
             /run/supervisor \
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