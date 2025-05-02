<?php
/**
 * Railway Debug Information Script
 * 
 * This script provides debugging information about environment variables
 * and server configuration when deployed on Railway.
 * 
 * SECURITY WARNING: This script exposes sensitive information and should
 * be protected with a secret or removed in production environments.
 */

// Very basic security - prevent accidental exposure 
// Add a secret query parameter to access this file
$debugSecret = getenv('RAILWAY_DEBUG_SECRET') ?: 'railway-debug-' . date('Ymd');

if (!isset($_GET['secret']) || $_GET['secret'] !== $debugSecret) {
    http_response_code(403);
    echo "Access denied. This file requires a valid secret.";
    echo "<br>Add ?secret=YOUR_SECRET to the URL.";
    echo "<br>Current debug secret is set to: " . htmlspecialchars($debugSecret);
    exit;
}

// Get all available environment variables
$allVars = getenv();

// Railway specific variables
$railwayVars = array_filter($allVars, function($key) {
    return strpos($key, 'RAILWAY_') === 0;
}, ARRAY_FILTER_USE_KEY);

// Laravel specific important variables
$laravelKeys = [
    'APP_ENV', 'APP_DEBUG', 'APP_URL', 
    'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
    'SESSION_DOMAIN', 'SANCTUM_STATEFUL_DOMAINS', 'CORS_ALLOWED_ORIGINS'
];
$laravelVars = array_intersect_key($allVars, array_flip($laravelKeys));

// Server variables
$serverInfo = [
    'PHP_VERSION' => PHP_VERSION,
    'SERVER_SOFTWARE' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'REQUEST_TIME' => date('Y-m-d H:i:s'),
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'Unknown',
    'SERVER_PROTOCOL' => $_SERVER['SERVER_PROTOCOL'] ?? 'Unknown',
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? 'Unknown',
    'HTTPS' => isset($_SERVER['HTTPS']) ? 'on' : 'off',
    'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
];

// Mask sensitive data
$sensitiveKeys = ['DB_PASSWORD', 'APP_KEY', 'STRIPE_SECRET', 'TWILIO_AUTH_TOKEN'];
foreach ($sensitiveKeys as $key) {
    if (isset($allVars[$key]) && !empty($allVars[$key])) {
        $allVars[$key] = '********' . substr($allVars[$key], -4);
    }
    if (isset($laravelVars[$key]) && !empty($laravelVars[$key])) {
        $laravelVars[$key] = '********' . substr($laravelVars[$key], -4);
    }
}

// Prepare data for output
$data = [
    'status' => 'success',
    'message' => 'Environment variables for Railway debugging',
    'environment' => getenv('APP_ENV') ?: 'unknown',
    'railway_vars' => $railwayVars,
    'laravel_vars' => $laravelVars,
    'server_info' => $serverInfo,
    'all_vars_count' => count($allVars)
];

// Output data in the requested format
$format = $_GET['format'] ?? 'html';

if ($format === 'json') {
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

// HTML output (default)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Environment Debug</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
        }
        .container {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .footer {
            margin-top: 30px;
            font-size: 0.8em;
            color: #666;
            text-align: center;
        }
        .warning {
            color: #e74c3c;
            font-weight: bold;
        }
        .not-set {
            color: #e74c3c;
            font-style: italic;
        }
        .formats {
            margin-bottom: 20px;
        }
        .formats a {
            display: inline-block;
            margin-right: 10px;
            padding: 8px 15px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .formats a:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>Railway Environment Debug</h1>
    
    <div class="warning container">
        <h3>⚠️ Security Warning</h3>
        <p>This page displays sensitive information about your environment. Do not share this URL publicly and remove this file after debugging.</p>
    </div>
    
    <div class="formats">
        <a href="?secret=<?= htmlspecialchars($debugSecret) ?>&format=html">HTML Format</a>
        <a href="?secret=<?= htmlspecialchars($debugSecret) ?>&format=json">JSON Format</a>
    </div>
    
    <div class="container">
        <h2>Railway Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            <?php foreach ($railwayVars as $key => $value): ?>
            <tr>
                <td><?= htmlspecialchars($key) ?></td>
                <td><?= empty($value) ? '<span class="not-set">[not set]</span>' : htmlspecialchars($value) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($railwayVars)): ?>
            <tr>
                <td colspan="2" class="not-set">No Railway variables found</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="container">
        <h2>Laravel Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            <?php foreach ($laravelVars as $key => $value): ?>
            <tr>
                <td><?= htmlspecialchars($key) ?></td>
                <td><?= empty($value) ? '<span class="not-set">[not set]</span>' : htmlspecialchars($value) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="container">
        <h2>Server Information</h2>
        <table>
            <?php foreach ($serverInfo as $key => $value): ?>
            <tr>
                <td><?= htmlspecialchars($key) ?></td>
                <td><?= htmlspecialchars($value) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="container">
        <h3>All Environment Variables Count: <?= count($allVars) ?></h3>
        <p>For security reasons, not all variables are displayed. Use the JSON endpoint for a complete list.</p>
    </div>
    
    <div class="footer">
        <p>Environment: <?= htmlspecialchars(getenv('APP_ENV') ?: 'unknown') ?> | Time: <?= date('Y-m-d H:i:s') ?></p>
        <p>This page is for debugging purposes only. Remove after troubleshooting is complete.</p>
    </div>
</body>
</html> 