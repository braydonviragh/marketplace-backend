<?php

/*
|--------------------------------------------------------------------------
| S3 Bucket CORS Configuration Script
|--------------------------------------------------------------------------
|
| This script configures the CORS settings for your S3 bucket to ensure 
| your media files can be accessed from your web application.
|
*/

require __DIR__.'/vendor/autoload.php';

use Aws\S3\S3Client;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create S3 client
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => $_ENV['AWS_DEFAULT_REGION'],
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
]);

// Set CORS Configuration
$result = $s3Client->putBucketCors([
    'Bucket' => $_ENV['AWS_BUCKET'],
    'CORSConfiguration' => [
        'CORSRules' => [
            [
                'AllowedHeaders' => ['*'],
                'AllowedMethods' => ['GET', 'HEAD', 'PUT', 'POST', 'DELETE'],
                'AllowedOrigins' => ['*'], // For production, restrict to your actual domains
                'ExposeHeaders' => ['ETag'],
                'MaxAgeSeconds' => 3000,
            ],
        ],
    ],
]);

if ($result['@metadata']['statusCode'] === 200) {
    echo "✅ CORS configuration applied successfully to the S3 bucket.\n";
} else {
    echo "❌ Error applying CORS configuration.\n";
}

// Set public access policy (make bucket objects readable by anyone)
$policy = '{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Sid": "PublicReadGetObject",
            "Effect": "Allow",
            "Principal": "*",
            "Action": "s3:GetObject",
            "Resource": "arn:aws:s3:::'.$_ENV['AWS_BUCKET'].'/*"
        }
    ]
}';

try {
    $s3Client->putBucketPolicy([
        'Bucket' => $_ENV['AWS_BUCKET'],
        'Policy' => $policy,
    ]);
    echo "✅ Public read access policy applied successfully.\n";
} catch (Exception $e) {
    echo "❌ Error applying public read access policy: " . $e->getMessage() . "\n";
}

echo "\nIMPORTANT: For production, make sure to:\n";
echo "1. Restrict AllowedOrigins to your actual domains instead of '*'\n";
echo "2. Consider using CloudFront or other CDN for better performance\n";
echo "3. Review your S3 bucket settings for security best practices\n"; 