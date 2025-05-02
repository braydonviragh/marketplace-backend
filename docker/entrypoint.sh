#!/bin/bash
set -e

# Create necessary directories if they don't exist
mkdir -p /var/www/storage/framework/cache
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs

# Set proper permissions
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage

# Set up environment if .env doesn't exist
if [ ! -f /var/www/.env ]; then
    echo "No .env file found, copying .env.example"
    cp /var/www/.env.example /var/www/.env
fi

# Generate app key if not set
if ! grep -q "^APP_KEY=[a-zA-Z0-9:+\/=]\{32,\}" /var/www/.env; then
    echo "Generating application key"
    php /var/www/artisan key:generate --force
fi

# Run migrations (only in production)
if [ "$APP_ENV" = "production" ]; then
    echo "Running migrations..."
    php /var/www/artisan migrate --force
fi

# Start supervisor
echo "Starting services..."
exec "$@" 