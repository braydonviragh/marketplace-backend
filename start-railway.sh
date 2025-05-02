#!/bin/bash
set -e

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# CRITICAL: Create health check endpoint immediately as the first thing
log "Creating health check endpoint for Railway..."
mkdir -p /var/www/public/api
echo "check complete" > /var/www/public/api/health
chmod 644 /var/www/public/api/health
log "Health check created at /var/www/public/api/health"

# Create a static health check JSON file as well
echo '{"status":"ok","message":"Health check file found"}' > /var/www/public/api/health.json
chmod 644 /var/www/public/api/health.json

# Check if we're in a Docker container
if [ -f /.dockerenv ] || [ -f /etc/supervisor/conf.d/supervisord.conf ]; then
    log "Running in Docker container"
    
    # Create storage structure
    log "Setting up Laravel storage directories..."
    mkdir -p /var/www/storage/framework/cache
    mkdir -p /var/www/storage/framework/sessions
    mkdir -p /var/www/storage/framework/views
    mkdir -p /var/www/storage/logs
    mkdir -p /var/www/bootstrap/cache
    
    # Set permissions
    log "Setting directory permissions..."
    chmod -R 777 /var/www/storage
    chmod -R 777 /var/www/bootstrap/cache
    
    # Create log files with proper permissions
    log "Creating log files..."
    touch /var/log/nginx/access.log
    touch /var/log/nginx/error.log
    touch /var/log/nginx/api_health_access.log
    touch /var/log/nginx/api_health_error.log
    
    # Check for Nginx
    if command -v nginx &> /dev/null; then
        log "Nginx is available"
    else
        log "WARNING: Nginx not found!"
    fi
    
    # Check for PHP-FPM
    if command -v php-fpm &> /dev/null; then
        log "PHP-FPM is available"
    else
        log "WARNING: PHP-FPM not found!"
    fi
    
    # Clear Laravel configuration cache
    log "Clearing Laravel configuration cache..."
    cd /var/www
    php artisan config:clear
    php artisan route:clear
    php artisan cache:clear
    
    # Start supervisor (which will start nginx and php-fpm)
    log "Starting supervisord with configuration: /etc/supervisor/conf.d/supervisord.conf"
    if [ -f /etc/supervisor/conf.d/supervisord.conf ]; then
        cat /etc/supervisor/conf.d/supervisord.conf
    else
        log "WARNING: Supervisor config not found at /etc/supervisor/conf.d/supervisord.conf"
    fi
    
    # CRITICAL: Output proof that the static file exists
    log "Verifying health check file exists..."
    ls -la /var/www/public/api/health
    cat /var/www/public/api/health
    
    # Start supervisor in background
    /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
    
    # Sleep briefly to give services time to start
    log "Waiting for services to start..."
    sleep 3
    
    # Test health endpoint with curl
    log "Testing health endpoint..."
    curl -v http://localhost:${PORT:-8080}/api/health || log "WARNING: Health check test failed"
    
    # Keep container running
    log "Container started, health check should now be working"
    tail -f /var/log/nginx/access.log
else
    # Running locally, not in a container
    log "Running outside Docker container - using fallback mode"
    
    # Create storage structure
    log "Setting up Laravel storage directories..."
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    mkdir -p bootstrap/cache
    
    # Set permissions
    log "Setting directory permissions..."
    chmod -R 775 storage
    chmod -R 775 bootstrap/cache
    
    # Generate key if needed
    php artisan key:generate --no-interaction
    
    # Start the web server
    log "Starting PHP development server..."
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
fi 