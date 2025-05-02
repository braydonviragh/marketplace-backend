<?php
/**
 * Railway Domain Test
 * 
 * A simple script to test if Railway domain variables are properly configured.
 */

// Set JSON content type
header('Content-Type: application/json');

// Get the domain variables that should be set
$result = [
    'timestamp' => date('c'),
    'railway_public_domain' => getenv('RAILWAY_PUBLIC_DOMAIN'),
    'app_url' => getenv('APP_URL'),
    'sanctum_stateful_domains' => getenv('SANCTUM_STATEFUL_DOMAINS'),
    'session_domain' => getenv('SESSION_DOMAIN'),
    'cors_allowed_origins' => getenv('CORS_ALLOWED_ORIGINS'),
    'request_headers' => [],
    'update_railway_vars_loaded' => file_exists(__DIR__.'/update-railway-vars.php')
];

// Add request headers for debugging
foreach (getallheaders() as $name => $value) {
    // Don't include authorization headers
    if (strtolower($name) !== 'authorization' && strtolower($name) !== 'cookie') {
        $result['request_headers'][$name] = $value;
    }
}

// Check if our Railway vars helper was applied
if (file_exists(__DIR__.'/update-railway-vars.php')) {
    // Force reload to test
    require __DIR__.'/update-railway-vars.php';
    
    // Get values after reload
    $result['after_reload'] = [
        'railway_public_domain' => getenv('RAILWAY_PUBLIC_DOMAIN'),
        'app_url' => getenv('APP_URL'),
        'sanctum_stateful_domains' => getenv('SANCTUM_STATEFUL_DOMAINS'),
        'session_domain' => getenv('SESSION_DOMAIN'),
        'cors_allowed_origins' => getenv('CORS_ALLOWED_ORIGINS'),
    ];
}

// Output the result
echo json_encode($result, JSON_PRETTY_PRINT); 