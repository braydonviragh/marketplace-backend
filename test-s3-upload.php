<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\PicsumService;
use App\Services\MediaService;
use App\Models\Product;

echo "S3 Upload Test\n";
echo "=============\n\n";

// Check env & configuration
echo "Environment: " . config('app.env') . "\n";
echo "Filesystem Disk: " . config('filesystems.default') . "\n";
echo "Media Service Disk: ";
$mediaService = app(MediaService::class);
$reflectionClass = new ReflectionClass($mediaService);
$property = $reflectionClass->getProperty('disk');
$property->setAccessible(true);
echo $property->getValue($mediaService) . "\n\n";

// Test direct S3 upload
try {
    $result = Storage::disk('s3')->put('test-file.txt', 'This is a test file');
    echo "Direct S3 upload test: " . ($result ? "SUCCESS" : "FAILED") . "\n";
} catch (\Exception $e) {
    echo "Direct S3 upload error: " . $e->getMessage() . "\n";
}

// Test MediaService upload with first product
try {
    $product = Product::first();
    if ($product) {
        $picsumService = app(PicsumService::class);
        $image = $picsumService->getRandomImage();
        if ($image) {
            $media = $mediaService->uploadMedia($product, $image, [
                'is_primary' => true,
                'order' => 0,
                'metadata' => ['test' => true]
            ]);
            echo "Media upload succeeded. Details:\n";
            echo "- ID: " . $media->id . "\n";
            echo "- Disk used: " . $media->disk . "\n";
            echo "- Path: " . $media->path . "\n";
            echo "- URL: " . $media->url . "\n";
            
            // Clean up temp file
            unlink($image->getPathname());
        } else {
            echo "Failed to get test image from Picsum\n";
        }
    } else {
        echo "No products found in database\n";
    }
} catch (\Exception $e) {
    echo "Media upload error: " . $e->getMessage() . "\n";
}
