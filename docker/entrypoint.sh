#!/bin/bash
set -e

echo "[$(date)] CONTAINER STARTUP: Beginning initialization..."

# Create required directories
echo "[$(date)] STEP 1: Creating Laravel storage directories..."
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache
echo "[$(date)] Storage directories created successfully."

# Set permissions
echo "[$(date)] STEP 2: Setting directory permissions..."
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache
echo "[$(date)] Permissions set successfully."

# Display directory contents for debugging
ls -la /var/www/storage
ls -la /var/www/bootstrap

# Display PHP version
echo "[$(date)] STEP 3: Checking PHP installation..."
php -v
echo "[$(date)] PHP installation verified."

# Check for .env file
echo "[$(date)] STEP 4: Checking environment file..."
if [ ! -f /var/www/.env ]; then
    echo "[$(date)] .env file not found, creating from example..."
    if [ -f /var/www/.env.example ]; then
        cp /var/www/.env.example /var/www/.env
        # Add debug to .env
        echo "APP_DEBUG=true" >> /var/www/.env
        echo "LOG_LEVEL=debug" >> /var/www/.env
        echo "[$(date)] .env file created from example with debug enabled."
    else
        echo "[$(date)] ERROR: .env.example file not found! Creating minimal .env..."
        cat > /var/www/.env <<EOL
APP_NAME=MarketplaceBackend
APP_ENV=production
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8080
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOL
        echo "[$(date)] Minimal .env file created."
    fi
fi

# Generate application key if not set
echo "[$(date)] STEP 5: Checking application key..."
if grep -q "APP_KEY=" /var/www/.env && ! grep -q "APP_KEY=$" /var/www/.env; then
    echo "[$(date)] Application key already exists."
else
    echo "[$(date)] Generating application key..."
    php /var/www/artisan key:generate --force
    echo "[$(date)] Application key generated."
fi

# Cache configuration
echo "[$(date)] STEP 6: Caching configuration..."
php /var/www/artisan config:clear
echo "[$(date)] Configuration cache cleared."

# Verify storage directory permissions again
echo "[$(date)] STEP 7: Verifying storage permissions..."
ls -la /var/www/storage
echo "[$(date)] Storage permissions verified."

# Test Nginx configuration
echo "[$(date)] STEP 8: Testing Nginx configuration..."
nginx -t || echo "[$(date)] Warning: Nginx configuration test failed"
echo "[$(date)] Nginx configuration verified."

# Check if supervisord is properly installed
echo "[$(date)] STEP 9: Testing Supervisor installation..."
if command -v supervisord >/dev/null 2>&1; then
    echo "[$(date)] Supervisor is installed."
else
    echo "[$(date)] ERROR: Supervisor is not installed or not in PATH."
    apt-get update && apt-get install -y supervisor
    echo "[$(date)] Installed supervisor."
fi

# Check supervisor config
if [ -f /etc/supervisor/conf.d/supervisord.conf ]; then
    echo "[$(date)] STEP 10: Supervisor configuration found."
    cat /etc/supervisor/conf.d/supervisord.conf
else
    echo "[$(date)] ERROR: Supervisor configuration not found at /etc/supervisor/conf.d/supervisord.conf."
    mkdir -p /etc/supervisor/conf.d/
    cp /var/www/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
    echo "[$(date)] Copied supervisor config from docker directory."
fi

# Make sure storage directory exists and is writable
echo "[$(date)] STEP 11: Final directory check..."
mkdir -p /var/www/storage/logs
chmod -R 777 /var/www/storage/logs
echo "[$(date)] Final directory permissions set."

# Create a test log file
echo "[$(date)] Container startup successful" > /var/www/storage/logs/laravel.log
chmod 666 /var/www/storage/logs/laravel.log

echo "[$(date)] All initialization steps completed successfully."
echo "[$(date)] Starting command: $@"

# Execute the command (supervisord)
exec "$@" 