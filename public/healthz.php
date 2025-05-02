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
    'environment' => getenv('RAILWAY_ENVIRONMENT_NAME') ?: 'production'
];

// Check if DB connection works, but don't fail if it doesn't
try {
    if (extension_loaded('pdo_mysql')) {
        $host = getenv('DB_HOST') ?: 'mysql.railway.internal';
        $port = getenv('DB_PORT') ?: '3306';
        $database = getenv('DB_DATABASE') ?: 'railway';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];
        
        $pdo = new PDO($dsn, $username, $password, $options);
        $stmt = $pdo->query('SELECT NOW() as server_time');
        $result = $stmt->fetch();
        
        $data['database'] = [
            'status' => 'connected',
            'server_time' => $result['server_time'] ?? 'unknown'
        ];
    } else {
        $data['database'] = [
            'status' => 'extension_not_loaded',
            'message' => 'PDO MySQL extension not loaded'
        ];
    }
} catch (Exception $e) {
    $data['database'] = [
        'status' => 'error',
        'message' => 'Could not connect to database'
    ];
}

// Add PHP information
$data['php'] = [
    'version' => phpversion(),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

// Respond with 200 OK status
http_response_code(200);
echo json_encode($data, JSON_PRETTY_PRINT);
exit; 