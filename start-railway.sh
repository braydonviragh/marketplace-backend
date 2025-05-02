#!/bin/bash
set -e

echo "Starting Railway service..."

# Check if we're running in Dockerfile mode
if [ -f /etc/supervisor/conf.d/supervisord.conf ]; then
    echo "Running in Docker mode with supervisor"
    
    # Start supervisor
    exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    echo "Fallback mode: Starting the application directly"
    
    # Create the storage directory structure
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    
    # Set permissions
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    
    # Generate the key if needed
    php artisan key:generate --force
    
    # Start the web server
    php artisan serve --port=${PORT:-8080} --host=0.0.0.0
fi 