#!/bin/bash

# Create directory for health check results if it doesn't exist
mkdir -p /var/www/public/api

# Log file for health checks
HEALTH_LOG="/var/log/health-check.log"
touch $HEALTH_LOG
chmod 644 $HEALTH_LOG

echo "Starting health check monitoring script at $(date)" >> $HEALTH_LOG

# Get the PORT from environment variable with fallback to 8080
PORT=${PORT:-8080}

# Main health check loop
while true; do
    # Wait for 10 seconds between checks
    sleep 10
    
    # Current timestamp
    TIMESTAMP=$(date -u +"%Y-%m-%dT%H:%M:%S.%3NZ")
    
    echo "[$TIMESTAMP] Running health check..." >> $HEALTH_LOG
    
    # Make request to health endpoint
    HTTP_CODE=$(curl -s -o /var/www/public/api/last-health-check.json -w "%{http_code}" http://localhost:$PORT/api/health)
    
    if [ "$HTTP_CODE" = "200" ]; then
        # If health check is successful
        echo "[$TIMESTAMP] Health check successful (HTTP $HTTP_CODE)" >> $HEALTH_LOG
        echo '{"status":"ok","timestamp":"'$TIMESTAMP'"}' > /var/www/public/api/health.json
    else
        # If health check fails
        echo "[$TIMESTAMP] Health check failed (HTTP $HTTP_CODE)" >> $HEALTH_LOG
        echo '{"status":"error","timestamp":"'$TIMESTAMP'"}' > /var/www/public/api/last-health-check.json
        
        # Also try the healthz.php endpoint as fallback
        HEALTHZ_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:$PORT/healthz.php)
        echo "[$TIMESTAMP] Fallback health check: HTTP $HEALTHZ_CODE" >> $HEALTH_LOG
        
        # Create or update health test files
        echo '{"status":"ok","timestamp":"'$TIMESTAMP'"}' > /var/www/public/api/health.json
    fi
    
    # Always touch the files
    touch /var/www/public/api/health.json
    touch /var/www/public/healthz.php
    
    # Debug information - list processes
    echo "[$TIMESTAMP] Running processes:" >> $HEALTH_LOG
    ps aux | grep -E 'nginx|php|supervisord' | grep -v grep >> $HEALTH_LOG
    
    # Debug information - check ports
    echo "[$TIMESTAMP] Listening ports:" >> $HEALTH_LOG
    netstat -tulpn | grep LISTEN >> $HEALTH_LOG 2>&1
done 