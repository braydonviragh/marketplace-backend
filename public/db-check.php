<?php
// Simple database connection check for Railway

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Basic environment information
$info = [
    'environment' => getenv('APP_ENV') ?: 'unknown',
    'timestamp' => date('Y-m-d H:i:s'),
    'railway_domain' => getenv('RAILWAY_PUBLIC_DOMAIN') ?: 'unknown',
    'railway_service' => getenv('RAILWAY_SERVICE_NAME') ?: 'unknown',
];

// Check database connection
try {
    $host = getenv('DB_HOST') ?: 'mysql.railway.internal';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: 'railway';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    $dsn = "mysql:host={$host};port={$port};dbname={$database}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3
    ];
    
    $pdo = new PDO($dsn, $username, $password, $options);
    $stmt = $pdo->query('SELECT NOW() as server_time');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $info['database'] = [
        'status' => 'connected',
        'host' => $host,
        'database' => $database,
        'server_time' => $result['server_time']
    ];
    
    echo json_encode(['success' => true, 'data' => $info], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    $info['database'] = [
        'status' => 'error',
        'message' => $e->getMessage(),
        'host' => $host ?? 'unknown',
        'database' => $database ?? 'unknown',
    ];
    
    echo json_encode(['success' => false, 'data' => $info], JSON_PRETTY_PRINT);
} 