#!/bin/bash

# Exit on error
set -e

# Log helper function
log() {
    echo "[$(date)] $1"
}

log "Starting Railway deployment script..."

# Export PORT for various services to use
export PORT=${PORT:-8080}
log "Using PORT: $PORT"

# Include the test script to verify environment variables
if [ -f /var/www/test-env.sh ]; then
    log "Running environment test script"
    bash /var/www/test-env.sh
fi

# Create static health check files immediately
log "Creating initial health check files..."
mkdir -p /var/www/public/api
echo "check complete" > /var/www/public/api/health
echo '{"status":"ok","timestamp":"'$(date -u +"%Y-%m-%dT%H:%M:%SZ")'",' > /var/www/public/api/health.json
echo '"php_version":"'$(php -r 'echo phpversion();')'","message":"Static health check file"}' >> /var/www/public/api/health.json
chmod 644 /var/www/public/api/health.json
chmod 644 /var/www/public/api/health

# Create a Healthz.php file in public directory
log "Creating healthz.php in public directory..."
cat > /var/www/public/healthz.php <<EOL
<?php
// Simple health check file for Railway
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

echo json_encode([
    'status' => 'ok',
    'timestamp' => date('c'),
    'php_version' => phpversion(),
    'message' => 'Health check from healthz.php',
    'port' => getenv('PORT') ?: '8080'
]);
EOL
chmod 644 /var/www/public/healthz.php

# Create health test file in JSON format
log "Creating health test file in JSON format"
echo '{"status":"ok","message":"Health check test file found"}' > /var/www/public/health-test.json
chmod 644 /var/www/public/health-test.json

# Run through entrypoint script for environment setup
if [ -f /var/www/docker/entrypoint.sh ]; then
    log "Running entrypoint script for environment setup"
    bash /var/www/docker/entrypoint.sh echo "Entrypoint setup complete"
fi

# Configure log directory for Nginx
log "Setting up Nginx log directory"
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log
touch /var/log/nginx/error.log
chmod 755 /var/log/nginx
chmod 644 /var/log/nginx/access.log
chmod 644 /var/log/nginx/error.log

# Laravel database migration and seeding
cd /var/www
log "Running Laravel migrations with --force flag..."
php artisan migrate --force
log "Database migrations completed successfully."

log "Running Laravel database seeders..."
php artisan db:seed --force
log "Database seeding completed successfully."

# Log environment info
log "Environment information:"
log "PORT: ${PORT}"
log "PWD: $(pwd)"
log "USER: $(whoami)"
log "PHP Version: $(php -v | head -n 1)"
log "Nginx Version: $(nginx -v 2>&1)"

# Start services with supervisord
log "Starting services with supervisord..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf 