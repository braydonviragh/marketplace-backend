<?php
/**
 * Railway Environment Variables Verification Script
 * 
 * This script can be accessed at /verify-railway-env.php and helps verify
 * that Railway environment variables are correctly being passed to your application.
 */

// Only allow running this in specified environments
$allowedEnvs = ['local', 'development', 'staging', 'production'];
$currentEnv = getenv('APP_ENV') ?: '';

// Basic security: Prevent running this in unauthorized environments
if (!in_array($currentEnv, $allowedEnvs) && $currentEnv !== '') {
    header('HTTP/1.1 403 Forbidden');
    echo "Access denied in this environment.";
    exit;
}

// List of important Railway variables to check
$railwayVars = [
    'RAILWAY_PUBLIC_DOMAIN',
    'RAILWAY_PROJECT_ID',
    'RAILWAY_SERVICE_ID',
    'RAILWAY_ENVIRONMENT_NAME',
    'RAILWAY_ENVIRONMENT_ID',
    'RAILWAY_SERVICE_NAME',
    'RAILWAY_PROJECT_NAME',
    'PORT' // Port is important for web servers
];

// List of important Laravel variables that should be set
$laravelVars = [
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
    'DB_HOST',
    'DB_DATABASE',
    'DB_USERNAME',
    'SANCTUM_STATEFUL_DOMAINS',
    'SESSION_DOMAIN',
    'CORS_ALLOWED_ORIGINS'
];

// Helper function to safely get and mask sensitive environment variables
function getEnvVarSafe($varName) {
    $value = getenv($varName);
    
    // Mask sensitive data if present
    $sensitiveVars = ['DB_PASSWORD', 'APP_KEY', 'STRIPE_SECRET', 'TWILIO_AUTH_TOKEN'];
    if (in_array($varName, $sensitiveVars) && $value) {
        return '********' . substr($value, -4);
    }
    
    return $value ?: '[not set]';
}

// Output as JSON for API requests or HTML for browser requests
$isApiRequest = isset($_SERVER['HTTP_ACCEPT']) && 
    strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($isApiRequest) {
    header('Content-Type: application/json');
    
    $result = [
        'status' => 'success',
        'message' => 'Environment variables check',
        'railway_vars' => [],
        'laravel_vars' => [],
        'server' => [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'request_time' => date('Y-m-d H:i:s')
        ]
    ];
    
    foreach ($railwayVars as $var) {
        $result['railway_vars'][$var] = getEnvVarSafe($var);
    }
    
    foreach ($laravelVars as $var) {
        $result['laravel_vars'][$var] = getEnvVarSafe($var);
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    exit;
}

// HTML output for browser requests
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Railway Environment Variables Check</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1, h2 {
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
            padding: 12px 15px;
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
        .not-set {
            color: #e74c3c;
            font-style: italic;
        }
    </style>
</head>
<body>
    <h1>Railway Environment Variables Check</h1>
    
    <div class="container">
        <h2>Railway Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            <?php foreach ($railwayVars as $var): ?>
            <tr>
                <td><?= htmlspecialchars($var) ?></td>
                <td><?= getEnvVarSafe($var) === '[not set]' ? 
                    '<span class="not-set">' . htmlspecialchars(getEnvVarSafe($var)) . '</span>' : 
                    htmlspecialchars(getEnvVarSafe($var)) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="container">
        <h2>Laravel Variables</h2>
        <table>
            <tr>
                <th>Variable</th>
                <th>Value</th>
            </tr>
            <?php foreach ($laravelVars as $var): ?>
            <tr>
                <td><?= htmlspecialchars($var) ?></td>
                <td><?= getEnvVarSafe($var) === '[not set]' ? 
                    '<span class="not-set">' . htmlspecialchars(getEnvVarSafe($var)) . '</span>' : 
                    htmlspecialchars(getEnvVarSafe($var)) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="container">
        <h2>Server Information</h2>
        <table>
            <tr>
                <td>PHP Version</td>
                <td><?= phpversion() ?></td>
            </tr>
            <tr>
                <td>Server Software</td>
                <td><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') ?></td>
            </tr>
            <tr>
                <td>Request Time</td>
                <td><?= date('Y-m-d H:i:s') ?></td>
            </tr>
        </table>
    </div>
    
    <div class="footer">
        <p>This page is for debugging purposes only. Consider removing or restricting access in production environments.</p>
    </div>
</body>
</html> 