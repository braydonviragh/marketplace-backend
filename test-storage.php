<?php

echo "Storage Link Test\n";
echo "----------------\n\n";

// Check if storage directory exists in public
$storagePublicPath = public_path('storage');
echo "Storage public path: $storagePublicPath\n";
echo "Exists: " . (file_exists($storagePublicPath) ? "Yes" : "No") . "\n";
echo "Is symlink: " . (is_link($storagePublicPath) ? "Yes" : "No") . "\n";

if (is_link($storagePublicPath)) {
    echo "Target: " . readlink($storagePublicPath) . "\n";
}

// Check a product media file
$testFilePath = storage_path('app/public/product/1/05854a19-6de9-484f-a83e-920d60cd803b.jpg');
echo "\nTest file path: $testFilePath\n";
echo "Exists: " . (file_exists($testFilePath) ? "Yes" : "No") . "\n";

// Show storage configuration
echo "\nStorage Configuration\n";
echo "--------------------\n";
echo "Storage disk 'public' configuration:\n";
print_r(config('filesystems.disks.public'));

// Check URL generation
$path = 'product/1/05854a19-6de9-484f-a83e-920d60cd803b.jpg';
$url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
echo "\nGenerated URL for test file: $url\n";

// Check APP_URL
echo "\nAPP_URL: " . env('APP_URL') . "\n"; 