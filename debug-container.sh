#!/bin/bash
# This script is for diagnosing issues inside the Docker container
# Run this inside the container with: bash /var/www/debug-container.sh

# Function for timestamped output
log() {
  echo "[$(date +"%Y-%m-%d %H:%M:%S")] $1"
}

# Banner
log "===== DOCKER CONTAINER DIAGNOSTIC TOOL ====="
log "Running diagnostics for marketplace-backend container"

# System Info
log "----- SYSTEM INFO -----"
log "Hostname: $(hostname)"
log "Kernel: $(uname -a)"
log "Available memory:"
free -h

# Check processes
log "----- RUNNING PROCESSES -----"
log "Processes running as root:"
ps aux | grep root
log ""
log "Processes running as www-data:"
ps aux | grep www-data
log ""
log "Nginx processes:"
ps aux | grep nginx
log ""
log "PHP-FPM processes:"
ps aux | grep php-fpm
log ""
log "Supervisor processes:"
ps aux | grep supervisor

# Port usage
log "----- NETWORK INFO -----"
log "Listening ports:"
netstat -tulpn 2>/dev/null || log "netstat not available"
log ""
log "Network interfaces:"
ip addr 2>/dev/null || ifconfig 2>/dev/null || log "No network tools available"

# Laravel and environment
log "----- LARAVEL ENVIRONMENT -----"
log "PHP version: $(php -v | head -n 1)"
log "Composer version: $(composer --version 2>/dev/null || log "Composer not found")"
log "Laravel environment:"
php /var/www/artisan env 2>/dev/null || log "Laravel Artisan command failed"
log ""
log "PHP extensions:"
php -m
log ""
log "Environment variables affecting Laravel:"
env | grep -E "APP_|DB_|LOG_|CACHE_|SESSION_|QUEUE_|MAIL_|PORT" | sort

# File permissions
log "----- FILE PERMISSIONS -----"
log "Laravel storage directory:"
ls -la /var/www/storage
log ""
log "Laravel bootstrap/cache directory:"
ls -la /var/www/bootstrap/cache
log ""
log "Nginx directories:"
ls -la /var/log/nginx
ls -la /etc/nginx

# Check configs
log "----- CONFIGURATION FILES -----"
log "Nginx configuration:"
if [ -f /etc/nginx/nginx.conf ]; then
  echo "--- /etc/nginx/nginx.conf exists ---"
else
  log "ERROR: Nginx config file missing!"
fi

log "PHP-FPM configuration:"
if [ -d /usr/local/etc/php-fpm.d ]; then
  echo "--- PHP-FPM configs: ---"
  ls -la /usr/local/etc/php-fpm.d/
else
  log "ERROR: PHP-FPM config directory missing!"
fi

log "Supervisor configuration:"
if [ -f /etc/supervisor/conf.d/supervisord.conf ]; then
  echo "--- /etc/supervisor/conf.d/supervisord.conf exists ---"
else
  log "ERROR: Supervisor config file missing!"
fi

# Check logs
log "----- LOG FILES -----"
log "Nginx error log (last 10 lines):"
if [ -f /var/log/nginx/error.log ]; then
  tail -10 /var/log/nginx/error.log
else
  log "ERROR: Nginx error log not found"
fi

log "API health check log (last 10 lines):"
if [ -f /var/log/nginx/api_health_check.log ]; then
  tail -10 /var/log/nginx/api_health_check.log
else
  log "ERROR: API health check log not found"
fi

log "Laravel log (last 10 lines):"
if [ -f /var/www/storage/logs/laravel.log ]; then
  tail -10 /var/www/storage/logs/laravel.log
else
  log "ERROR: Laravel log not found"
fi

log "Supervisor log (last 10 lines):"
if [ -f /var/log/supervisor/supervisord.log ]; then
  tail -10 /var/log/supervisor/supervisord.log
else
  log "ERROR: Supervisor log not found"
fi

# Health checks
log "----- HEALTH CHECKS -----"
log "Curl test of root endpoint:"
curl -v -s localhost:${PORT:-8080}/ 2>&1 || log "ERROR: Curl failed or not installed"

log "Curl test of /health endpoint:"
curl -v -s localhost:${PORT:-8080}/health 2>&1 || log "ERROR: Curl failed or not installed"

log "Curl test of API health endpoint (Railway health check):"
curl -v -s localhost:${PORT:-8080}/api/health 2>&1 || log "ERROR: Curl failed or not installed"

log "Curl test of /api/v1/health endpoint:"
curl -v -s localhost:${PORT:-8080}/api/v1/health 2>&1 || log "ERROR: Curl failed or not installed"

log "===== DIAGNOSTIC COMPLETE =====" 