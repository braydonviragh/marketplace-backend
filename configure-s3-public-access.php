<?php

require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;

echo "S3 Bucket Public Access Configuration\n";
echo "===================================\n\n";

try {
    $region = env('AWS_DEFAULT_REGION');
    $bucket = env('AWS_BUCKET');
    
    echo "Configuring bucket: {$bucket} in region: {$region}\n\n";
    
    // Create S3 client
    $s3 = new S3Client([
        'version'     => 'latest',
        'region'      => $region,
        'credentials' => [
            'key'     => env('AWS_ACCESS_KEY_ID'),
            'secret'  => env('AWS_SECRET_ACCESS_KEY'),
        ],
    ]);
    
    // Create bucket policy for public read access
    echo "1. Setting bucket policy for public read access...\n";
    
    $policy = [
        'Version' => '2012-10-17',
        'Statement' => [
            [
                'Sid' => 'PublicReadGetObject',
                'Effect' => 'Allow',
                'Principal' => '*',
                'Action' => 's3:GetObject',
                'Resource' => "arn:aws:s3:::{$bucket}/*"
            ]
        ]
    ];
    
    try {
        $result = $s3->putBucketPolicy([
            'Bucket' => $bucket,
            'Policy' => json_encode($policy)
        ]);
        
        echo "✅ Bucket policy successfully applied.\n";
    } catch (\Exception $e) {
        echo "❌ Error setting bucket policy: " . $e->getMessage() . "\n";
    }
    
    // Set bucket to allow public ACLs
    echo "\n2. Configuring bucket to allow public ACLs...\n";
    
    try {
        $result = $s3->putPublicAccessBlock([
            'Bucket' => $bucket,
            'PublicAccessBlockConfiguration' => [
                'BlockPublicAcls' => false,
                'IgnorePublicAcls' => false,
                'BlockPublicPolicy' => false,
                'RestrictPublicBuckets' => false,
            ]
        ]);
        
        echo "✅ Public access block configuration updated.\n";
    } catch (\Exception $e) {
        echo "❌ Error configuring public access block: " . $e->getMessage() . "\n";
        echo "   This is normal if your IAM user doesn't have sufficient permissions.\n";
    }
    
    // Set bucket CORS configuration
    echo "\n3. Setting CORS configuration...\n";
    
    try {
        $result = $s3->putBucketCors([
            'Bucket' => $bucket,
            'CORSConfiguration' => [
                'CORSRules' => [
                    [
                        'AllowedHeaders' => ['*'],
                        'AllowedMethods' => ['GET', 'PUT', 'POST', 'DELETE', 'HEAD'],
                        'AllowedOrigins' => ['*'], // For production, restrict to your actual domains
                        'ExposeHeaders' => ['ETag'],
                        'MaxAgeSeconds' => 3000,
                    ],
                ],
            ],
        ]);
        
        echo "✅ CORS configuration set successfully.\n";
    } catch (\Exception $e) {
        echo "❌ Error setting CORS configuration: " . $e->getMessage() . "\n";
    }
    
    // Set default ACL for the bucket
    echo "\n4. Setting up bucket default ACL...\n";
    
    try {
        $result = $s3->putBucketAcl([
            'Bucket' => $bucket,
            'ACL' => 'public-read'
        ]);
        
        echo "✅ Bucket ACL set to public-read successfully.\n";
    } catch (\Exception $e) {
        echo "❌ Error setting bucket ACL: " . $e->getMessage() . "\n";
        echo "   This might be normal if your bucket has S3 Object Ownership set to 'Bucket owner enforced'.\n";
    }
    
    echo "\n✅ Configuration completed!\n";
    echo "Note: Some operations might require additional IAM permissions. Check AWS Console if needed.\n";
    
} catch (\Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
}

echo "\nManual steps that may be required in AWS Console:\n";
echo "1. Go to the S3 bucket in AWS Console\n";
echo "2. Navigate to 'Permissions' tab\n";
echo "3. Under 'Block public access (bucket settings)', click 'Edit' and uncheck all options\n";
echo "4. Under 'Object Ownership', click 'Edit' and select 'ACLs enabled' if you want to use ACLs\n";
echo "   (Or consider using bucket policy only for public access instead of ACLs)\n";
echo "5. Save changes\n"; 