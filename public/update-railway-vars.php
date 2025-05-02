<?php
/**
 * Railway Environment Variable Helper
 * 
 * This script handles mapping of Railway environment variables to Laravel environment variables.
 * It's included before the Laravel bootstrap process to ensure environment variables are properly set.
 */

// Skip if not in Railway environment
if (!getenv('RAILWAY_ENVIRONMENT_NAME')) {
    return;
}

// Map Railway variables to Laravel environment variables
$mappings = [
    // Core Laravel variables
    'APP_URL' => 'https://' . (getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'web-production-3047.up.railway.app'),
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    
    // Domain settings
    'SANCTUM_STATEFUL_DOMAINS' => getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'web-production-3047.up.railway.app',
    'SESSION_DOMAIN' => getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'web-production-3047.up.railway.app',
    
    // Security settings
    'SESSION_SECURE_COOKIE' => 'true',
    
    // Database settings (if using Railway MySQL)
    'DB_HOST' => getenv('RAILWAY_MYSQL_HOST') ?: getenv('DB_HOST') ?: 'mysql.railway.internal',
    'DB_PORT' => getenv('RAILWAY_MYSQL_PORT') ?: getenv('DB_PORT') ?: '3306',
    'DB_DATABASE' => getenv('RAILWAY_MYSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'railway',
    'DB_USERNAME' => getenv('RAILWAY_MYSQL_USERNAME') ?: getenv('DB_USERNAME') ?: 'root',
    'DB_PASSWORD' => getenv('RAILWAY_MYSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '',
    
    // Redis (if using Railway Redis)
    'REDIS_HOST' => getenv('RAILWAY_REDIS_HOST') ?: getenv('REDIS_HOST') ?: '127.0.0.1',
    'REDIS_PORT' => getenv('RAILWAY_REDIS_PORT') ?: getenv('REDIS_PORT') ?: '6379',
    'REDIS_PASSWORD' => getenv('RAILWAY_REDIS_PASSWORD') ?: getenv('REDIS_PASSWORD') ?: null,
];

// Process CORS origins
$appUrl = $mappings['APP_URL'];
$corsOrigins = getenv('CORS_ALLOWED_ORIGINS');
if (!$corsOrigins) {
    // If not already set, include the app URL, Railway domain, and a wildcard
    $corsOrigins = "$appUrl,https://" . (getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'web-production-3047.up.railway.app') . ',*';
    $mappings['CORS_ALLOWED_ORIGINS'] = $corsOrigins;
}

// Apply all mappings to environment
foreach ($mappings as $key => $value) {
    if ($value !== null) {
        putenv("$key=$value");
        $_SERVER[$key] = $value;
        $_ENV[$key] = $value;
    }
}

// Uncomment for debugging
// file_put_contents('/tmp/railway-env-debug.log', json_encode($mappings, JSON_PRETTY_PRINT)); 