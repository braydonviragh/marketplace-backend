FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    nano \
    procps \
    iputils-ping \
    net-tools \
    ca-certificates \
    lsb-release \
    acl \
    htop \
    vim \
    strace \
    tcpdump

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

# Install additional PHP extensions
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system directories and set permissions
RUN mkdir -p /var/log/nginx \
    && mkdir -p /var/cache/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /etc/supervisor/conf.d \
    && mkdir -p /var/run/supervisor \
    && mkdir -p /run \
    && touch /run/.containerenv \
    && touch /.dockerenv \
    && chown -R www-data:www-data /var/log/nginx \
    && chown -R www-data:www-data /var/cache/nginx \
    && chown -R www-data:www-data /var/log/supervisor

# Configure PHP-FPM
RUN { \
    echo '[global]'; \
    echo 'error_log = /proc/self/fd/2'; \
    echo 'log_level = notice'; \
    echo 'daemonize = no'; \
    echo '[www]'; \
    echo 'access.log = /proc/self/fd/2'; \
    echo 'clear_env = no'; \
    echo 'catch_workers_output = yes'; \
    echo 'decorate_workers_output = no'; \
} > /usr/local/etc/php-fpm.d/zz-docker.conf

# Copy nginx and supervisor configurations
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /var/www/docker/entrypoint.sh
COPY start-railway.sh /var/www/start-railway.sh

# Make scripts executable
RUN chmod +x /var/www/docker/entrypoint.sh \
    && chmod +x /var/www/start-railway.sh

# Copy existing application directory contents
COPY . /var/www

# Generate initial Laravel storage structure
RUN mkdir -p /var/www/storage/app/public \
    && mkdir -p /var/www/storage/framework/cache/data \
    && mkdir -p /var/www/storage/framework/sessions \
    && mkdir -p /var/www/storage/framework/testing \
    && mkdir -p /var/www/storage/framework/views \
    && mkdir -p /var/www/storage/logs \
    && touch /var/www/storage/logs/laravel.log

# Set correct permissions
RUN chown -R www-data:www-data /var/www/storage \
    && chown -R www-data:www-data /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage \
    && chmod -R 777 /var/www/bootstrap/cache \
    && chmod 666 /var/www/storage/logs/laravel.log

# Install composer dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-interaction --no-dev --optimize-autoloader

# Create health check endpoint
RUN mkdir -p /var/www/public/api && \
    echo "OK" > /var/www/public/api/health && \
    chmod 644 /var/www/public/api/health

# Create simple PHP health check
RUN echo '<?php header("Content-Type: text/plain"); echo "OK"; exit(0);' > /var/www/public/healthz.php && \
    chmod 644 /var/www/public/healthz.php

# Create health check shell script
RUN echo '#!/bin/bash\n\
mkdir -p /var/www/public/api\n\
echo "OK" > /var/www/public/api/health\n\
chmod 644 /var/www/public/api/health\n\
\n\
# Start supervisor which will start nginx and php-fpm\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf\n\
' > /var/www/health-railway.sh && chmod +x /var/www/health-railway.sh

# Copy our health check script and make it executable
COPY health-railway.sh /var/www/health-railway.sh
RUN chmod +x /var/www/health-railway.sh

# Default port - Railway uses PORT env var
EXPOSE 8080

# Create a healthcheck for Docker
HEALTHCHECK --interval=5s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:${PORT:-8080}/healthz.php || exit 1

# Use the health check script for Railway
CMD ["/var/www/health-railway.sh"] 