#!/bin/bash
# Simple health check script for Railway

echo "[$(date)] STARTUP: Creating health check endpoints..."

# Create all the possible health check files to ensure Railway can reach at least one
mkdir -p /var/www/public/api

# Simple text health check
echo "check complete" > /var/www/public/api/health
chmod 644 /var/www/public/api/health

# JSON health check 
echo '{"status":"ok","timestamp":"'$(date -u +"%Y-%m-%dT%H:%M:%SZ")'",' > /var/www/public/api/health.json
echo '"message":"Railway health check endpoint"}' >> /var/www/public/api/health.json
chmod 644 /var/www/public/api/health.json

# Simple PHP health check that doesn't use Laravel
cat > /var/www/public/healthz.php << 'EOF'
<?php
header('Content-Type: text/plain');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
echo 'check complete';
exit(0);
EOF
chmod 644 /var/www/public/healthz.php

# Create the log directories needed by nginx
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log
touch /var/log/nginx/error.log

echo "[$(date)] STARTUP: Health check files created successfully."
echo "[$(date)] STARTUP: Starting supervisord to launch services..."

# Start supervisord to launch PHP-FPM and Nginx
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf 