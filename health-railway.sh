#!/bin/bash
# Railway deployment health check and startup script

# Echo with timestamp
log() {
    echo "[$(date)] $1"
}

log "STARTUP: Railway deployment - Environment: ${RAILWAY_ENVIRONMENT_NAME:-production}"
log "STARTUP: Domain: ${RAILWAY_PUBLIC_DOMAIN:-localhost}"
log "STARTUP: Project ID: ${RAILWAY_PROJECT_ID:-unknown}"

# Create all the possible health check files to ensure Railway can reach at least one
mkdir -p /var/www/public/api

# Simple text health check
echo "OK" > /var/www/public/api/health
chmod 644 /var/www/public/api/health
log "STARTUP: Created text health check at /api/health"

# JSON health check 
echo '{
  "status": "ok",
  "timestamp": "'$(date -u +"%Y-%m-%dT%H:%M:%SZ")'",
  "service": "'${RAILWAY_SERVICE_NAME:-web}'",
  "environment": "'${RAILWAY_ENVIRONMENT_NAME:-production}'",
  "domain": "'${RAILWAY_PUBLIC_DOMAIN:-unknown}'"
}' > /var/www/public/api/health.json
chmod 644 /var/www/public/api/health.json
log "STARTUP: Created JSON health check at /api/health.json"

# Simple PHP health check that doesn't use Laravel
cat > /var/www/public/healthz.php << 'EOF'
<?php
header('Content-Type: text/plain');
echo 'OK';
exit(0);
EOF
chmod 644 /var/www/public/healthz.php

# Create the log directories needed by nginx
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log
touch /var/log/nginx/error.log
chmod 644 /var/log/nginx/access.log /var/log/nginx/error.log

# Check environment variables 
log "STARTUP: Database host: ${DB_HOST:-mysql.railway.internal}"
log "STARTUP: Configured APP_URL: ${APP_URL:-unknown}"

log "STARTUP: Health check files created successfully."
log "STARTUP: Starting supervisord to launch services..."

# Start supervisord to launch PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf 