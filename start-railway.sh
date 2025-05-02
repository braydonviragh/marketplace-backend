#!/bin/bash

# Exit on error
set -e

# Log function for clarity
log() {
    echo "[$(date)] $1"
}

log "STARTUP: Railway deployment starting..."

# Create health check files FIRST (before anything else)
log "SETUP: Setting up health check endpoints..."

# Create the public directory if it doesn't exist
mkdir -p /var/www/public
mkdir -p /var/www/public/api

# Simple text health check (static file)
echo "check complete" > /var/www/public/api/health
chmod 644 /var/www/public/api/health
log "SETUP: Created static health check at /api/health"

# JSON health check (static file)
echo '{"status":"ok","timestamp":"'$(date -u +"%Y-%m-%dT%H:%M:%SZ")'",' > /var/www/public/api/health.json
echo '"message":"Railway health check endpoint"}' >> /var/www/public/api/health.json
chmod 644 /var/www/public/api/health.json
log "SETUP: Created JSON health check at /api/health.json"

# Simple PHP health check (no Laravel dependency)
cat > /var/www/public/healthz.php << 'EOF'
<?php
// Health check - no Laravel dependency
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight requests
    http_response_code(200);
    exit();
}

// Return success
echo "OK";
exit(0);
EOF
chmod 644 /var/www/public/healthz.php
log "SETUP: Created PHP health check at /healthz.php"

# Log directories for nginx
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log
touch /var/log/nginx/error.log
touch /var/log/nginx/api_health_access.log
touch /var/log/nginx/api_health_error.log
touch /var/log/nginx/healthz_access.log
touch /var/log/nginx/healthz_error.log
log "SETUP: Created log directories and files"

# Determine if we're running in Docker
if [ -f /.dockerenv ] || [ -f /run/.containerenv ]; then
    # We're in a container
    IN_DOCKER=true
    log "INFO: Running in Docker container"
else
    # We're not in a container
    IN_DOCKER=false
    log "INFO: Running outside of Docker container"
fi

# Display environment info for debugging
log "ENV: PHP Version: $(php -v | head -n 1)"
log "ENV: OS: $(uname -a)"
log "ENV: APP_URL: $APP_URL"
log "ENV: APP_ENV: $APP_ENV"

# Create storage structure if missing
if [ ! -d /var/www/storage/logs ]; then
    log "SETUP: Creating missing storage directories"
    mkdir -p /var/www/storage/app/public
    mkdir -p /var/www/storage/framework/cache/data
    mkdir -p /var/www/storage/framework/sessions
    mkdir -p /var/www/storage/framework/testing
    mkdir -p /var/www/storage/framework/views
    mkdir -p /var/www/storage/logs
    touch /var/www/storage/logs/laravel.log
fi

# Set correct permissions
log "SETUP: Setting permissions"
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage
chown -R www-data:www-data /var/www/bootstrap/cache
chmod -R 775 /var/www/bootstrap/cache

# Handle startup based on environment
if [ "$IN_DOCKER" = true ]; then
    log "STARTUP: Using supervisord to manage services"
    # In Docker, use supervisord (from entrypoint.sh)
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
else
    # Not in Docker, direct service start
    log "STARTUP: Starting PHP-FPM"
    php-fpm &
    
    # Wait for PHP-FPM to be ready
    sleep 2
    
    log "STARTUP: Starting Nginx"
    nginx &
    
    # Wait for Nginx to be ready
    sleep 2
    
    # Test the health endpoint
    log "TEST: Testing health endpoint"
    curl -v http://localhost:${PORT:-8080}/healthz.php || log "WARNING: Health check test failed"
    
    # Keep the script running
    log "READY: Application is running"
    tail -f /var/log/nginx/error.log
fi 