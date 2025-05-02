#!/bin/bash
set -e

timestamp() {
  date +"%Y-%m-%d %H:%M:%S"
}

log() {
  echo "$(timestamp) - $1"
}

log "Application startup beginning"

# Determine if we're running in Docker or not
IN_DOCKER=false
if [ -f "/.dockerenv" ] || grep -q docker /proc/1/cgroup 2>/dev/null; then
  IN_DOCKER=true
  log "Running in Docker container"
else
  log "Running in non-Docker environment"
fi

# Set base directory based on environment
if $IN_DOCKER; then
  BASE_DIR="/var/www"
else
  BASE_DIR="."
fi

cd "$BASE_DIR"
log "Working directory: $(pwd)"

# Create Laravel storage directories
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set proper permissions for Laravel
if $IN_DOCKER; then
  log "Setting Docker container permissions"
  chmod -R 777 storage
  chmod -R 777 bootstrap/cache
  
  # Create Nginx log files and directories if they don't exist
  log "Setting up Nginx logs"
  mkdir -p /var/log/nginx
  touch /var/log/nginx/access.log
  touch /var/log/nginx/error.log
  touch /var/log/nginx/health_check.log
  touch /var/log/nginx/root_check.log
  touch /var/log/nginx/api_access.log
  chmod 777 /var/log/nginx/*.log
  
  # Ensure .env exists
  if [ ! -f .env ]; then
    log "Creating .env file from example"
    cp -n .env.example .env 2>/dev/null || echo "APP_KEY=" > .env
    php artisan key:generate --force
  fi
  
  # Ensure PORT is set for Nginx
  if [ -z "$PORT" ]; then
    log "PORT environment variable not set, defaulting to 8080"
    export PORT=8080
  else
    log "PORT environment variable set to $PORT"
  fi
  
  # Test Nginx configuration
  log "Testing Nginx configuration"
  nginx -t || log "WARNING: Nginx configuration test failed"
  
  # Test if PHP-FPM is working
  log "Testing PHP-FPM"
  if [ -e /usr/local/sbin/php-fpm ]; then
    log "PHP-FPM binary exists at /usr/local/sbin/php-fpm"
  else
    log "WARNING: PHP-FPM binary not found at expected location"
  fi
  
  # Optimize Laravel for production
  log "Optimizing Laravel application"
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  php artisan cache:clear
  
  # Check for supervisor config
  if [ -f "/etc/supervisor/conf.d/supervisord.conf" ]; then
    log "Starting supervisord with config at /etc/supervisor/conf.d/supervisord.conf"
    # Show the config for debugging
    log "Supervisor configuration:"
    cat /etc/supervisor/conf.d/supervisord.conf
    
    # Start supervisor
    exec supervisord -c /etc/supervisor/conf.d/supervisord.conf -n
  else
    log "ERROR: Supervisor config not found at /etc/supervisor/conf.d/supervisord.conf"
    log "Available files in /etc/supervisor/conf.d/:"
    ls -la /etc/supervisor/conf.d/ || log "Directory not accessible"
    
    # Fallback to direct service start if supervisor isn't available
    log "Falling back to direct service startup"
    php-fpm &
    nginx -g "daemon off;" &
    wait
  fi
else
  # Non-Docker environment (like Railway without Docker)
  log "Setting up for non-Docker environment"
  chmod -R 775 storage
  chmod -R 775 bootstrap/cache
  
  # Ensure we have an app key
  if [ ! -f .env ]; then
    log "Creating .env file from example"
    cp -n .env.example .env 2>/dev/null || echo "APP_KEY=" > .env
  fi
  
  php artisan key:generate --force
  php artisan config:cache
  php artisan route:cache
  
  # Start the Laravel development server
  log "Starting Laravel development server"
  exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
fi 