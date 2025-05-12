<?php

// Test script to verify S3 uploads

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\MediaService;
use App\Services\PicsumService;
use App\Models\Product;

echo "S3 Upload Test Script\n";
echo "====================\n\n";

// 1. Check current configuration
echo "Current Storage Configuration:\n";
echo "- Default Disk: " . config('filesystems.default') . "\n";
echo "- S3 Bucket: " . config('filesystems.disks.s3.bucket') . "\n";
echo "- S3 URL: " . config('filesystems.disks.s3.url') . "\n\n";

// 2. Test basic S3 connection
echo "Testing S3 Connection...\n";
try {
    // Try to create a test file
    $testContent = "S3 Connection Test - " . date('Y-m-d H:i:s');
    $testPath = 'test-uploads/test-' . time() . '.txt';
    
    $success = Storage::disk('s3')->put($testPath, $testContent);
    
    if ($success) {
        echo "✅ Successfully created file on S3: {$testPath}\n";
        echo "- URL: " . Storage::disk('s3')->url($testPath) . "\n\n";
    } else {
        echo "❌ Failed to create test file on S3.\n\n";
    }
} catch (\Exception $e) {
    echo "❌ S3 Connection Error: " . $e->getMessage() . "\n\n";
}

// 3. Test image upload via MediaService
echo "Testing Image Upload via MediaService...\n";
try {
    // Get the first product
    $product = Product::first();
    
    if (!$product) {
        echo "⚠️ No products found in database. Creating a test product...\n";
        $product = Product::create([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Test Product',
            'description' => 'Test product for S3 upload',
            'price' => 100,
            'is_available' => true
        ]);
    }
    
    // Use PicsumService to get a test image
    $picsumService = app(PicsumService::class);
    $mediaService = app(MediaService::class);
    
    $image = $picsumService->getRandomImage();
    
    if (!$image) {
        echo "❌ Failed to get test image from Picsum.\n";
    } else {
        echo "✅ Got test image from Picsum.\n";
        
        // Upload the image
        $media = $mediaService->uploadMedia($product, $image, [
            'is_primary' => true,
            'order' => 0
        ]);
        
        echo "✅ Image uploaded:\n";
        echo "- Disk: {$media->disk}\n";
        echo "- Path: {$media->path}\n";
        echo "- URL: {$media->url}\n\n";
        
        // Clean up temp file
        unlink($image->getPathname());
    }
    
} catch (\Exception $e) {
    echo "❌ Media Upload Error: " . $e->getMessage() . "\n\n";
}

echo "\nTest completed.\n"; 