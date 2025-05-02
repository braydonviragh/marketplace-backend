#!/bin/bash
set -e

echo "Starting entrypoint script..."

# Create required directories
echo "Creating Laravel storage directories..."
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/bootstrap/cache

# Set permissions
echo "Setting directory permissions..."
chmod -R 775 /var/www/storage
chmod -R 775 /var/www/bootstrap/cache
chown -R www-data:www-data /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache

# Check for .env file
echo "Checking environment file..."
if [ ! -f /var/www/.env ]; then
    echo ".env file not found, creating from example..."
    if [ -f /var/www/.env.example ]; then
        cp /var/www/.env.example /var/www/.env
        echo ".env file created from example."
    else
        echo "ERROR: .env.example file not found. Cannot create .env file."
        exit 1
    fi
fi

# Generate application key if not set
echo "Checking application key..."
if grep -q "APP_KEY=\|APP_KEY=" /var/www/.env; then
    if grep -q "APP_KEY=$" /var/www/.env; then
        echo "APP_KEY exists but is empty, generating key..."
        php /var/www/artisan key:generate --force
        echo "Application key generated."
    else
        echo "Application key already exists."
    fi
else
    echo "APP_KEY not found in .env, generating key..."
    php /var/www/artisan key:generate --force
    echo "Application key generated."
fi

# Optimize application
echo "Optimizing application..."
php /var/www/artisan optimize:clear
php /var/www/artisan optimize

# Run migrations in production only if environment variable is set
if [ "$APP_ENV" = "production" ] && [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running migrations in production..."
    php /var/www/artisan migrate --force
else
    echo "Skipping migrations (not in production or RUN_MIGRATIONS not set to true)."
fi

# Check if nginx is properly installed
echo "Checking Nginx installation..."
if command -v nginx >/dev/null 2>&1; then
    echo "Nginx is installed. Testing configuration..."
    nginx -t
else
    echo "ERROR: Nginx is not installed or not in PATH."
    exit 1
fi

# Check if supervisord is properly installed
echo "Checking Supervisor installation..."
if command -v supervisord >/dev/null 2>&1; then
    echo "Supervisor is installed."
    if [ -f /etc/supervisor/conf.d/supervisord.conf ]; then
        echo "Supervisor configuration found."
    else
        echo "ERROR: Supervisor configuration not found at /etc/supervisor/conf.d/supervisord.conf."
        exit 1
    fi
else
    echo "ERROR: Supervisor is not installed or not in PATH."
    exit 1
fi

echo "Entrypoint script completed successfully."
echo "Starting command: $@"

exec "$@" 