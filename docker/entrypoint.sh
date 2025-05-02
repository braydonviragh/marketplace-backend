#!/bin/bash
set -e

echo "[$(date)] CONTAINER STARTUP: Beginning initialization..."

# Create a container environment marker for detection
touch /.dockerenv

# Special handling for Railway deployment
if [ -n "$RAILWAY_ENVIRONMENT" ]; then
    echo "[$(date)] Detected Railway environment: $RAILWAY_ENVIRONMENT"
    echo "[$(date)] Setting up for Railway deployment..."
fi

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
chmod -R 777 /var/www/storage
chmod -R 777 /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache
echo "[$(date)] Permissions set successfully."

# Display directory contents for debugging
ls -la /var/www/storage
ls -la /var/www/bootstrap

# Create static health check files
echo "[$(date)] STEP 3: Creating health check files..."
mkdir -p /var/www/public/api
echo '{"status":"ok","timestamp":"'$(date -u +"%Y-%m-%dT%H:%M:%SZ")'",' > /var/www/public/api/health.json
echo '"php_version":"'$(php -r 'echo phpversion();')'","message":"Static health check file"}' >> /var/www/public/api/health.json
chmod 644 /var/www/public/api/health.json
echo "[$(date)] Health check files created."

# Display PHP version
echo "[$(date)] STEP 4: Checking PHP installation..."
php -v
echo "[$(date)] PHP installation verified."

# Check for .env file
echo "[$(date)] STEP 5: Checking environment file..."
if [ ! -f /var/www/.env ]; then
    echo "[$(date)] .env file not found, creating from example..."
    if [ -f /var/www/.env.example ]; then
        cp /var/www/.env.example /var/www/.env
        # Add debug to .env
        echo "APP_DEBUG=true" >> /var/www/.env
        echo "LOG_LEVEL=debug" >> /var/www/.env
        echo "LOG_CHANNEL=stderr" >> /var/www/.env
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
LOG_CHANNEL=stderr

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

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
echo "[$(date)] STEP 6: Checking application key..."
if grep -q "APP_KEY=" /var/www/.env && ! grep -q "APP_KEY=$" /var/www/.env; then
    echo "[$(date)] Application key already exists."
else
    echo "[$(date)] Generating application key..."
    php /var/www/artisan key:generate --force
    echo "[$(date)] Application key generated."
fi

# Cache configuration
echo "[$(date)] STEP 7: Caching configuration..."
cd /var/www
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo "[$(date)] Configuration cache cleared."

# Create a test log file
echo "[$(date)] Container startup successful" > /var/www/storage/logs/laravel.log
chmod 666 /var/www/storage/logs/laravel.log

echo "[$(date)] All initialization steps completed successfully."
echo "[$(date)] Starting command: $@"

# Execute the command passed to entrypoint
exec "$@" 