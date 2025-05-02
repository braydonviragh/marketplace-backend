<?php
/**
 * Railway Health Check File
 * 
 * This file provides a simple health check endpoint for Railway
 * that doesn't depend on Laravel being fully initialized.
 */

// Set appropriate headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// If it's a preflight request, just return headers
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Basic status information
$data = [
    'status' => 'ok',
    'timestamp' => date('c'),
    'service' => getenv('RAILWAY_SERVICE_NAME') ?: 'marketplace-backend',
    'environment' => getenv('RAILWAY_ENVIRONMENT_NAME') ?: 'production',
    'port' => getenv('PORT') ?: '8080',
    'railway_public_domain' => getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'unknown'
];

// Check if PHP is working correctly
$data['php'] = [
    'version' => phpversion(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

// Check if we can write to temp directory
$temp_file = tempnam(sys_get_temp_dir(), 'healthcheck');
if ($temp_file !== false) {
    file_put_contents($temp_file, 'test');
    $data['filesystem'] = [
        'status' => 'ok',
        'temp_dir' => sys_get_temp_dir(),
        'writable' => true
    ];
    unlink($temp_file);
} else {
    $data['filesystem'] = [
        'status' => 'error',
        'temp_dir' => sys_get_temp_dir(),
        'writable' => false
    ];
}

// HTTP status will always be 200 for health checks
http_response_code(200);
echo json_encode($data, JSON_PRETTY_PRINT);
exit; 