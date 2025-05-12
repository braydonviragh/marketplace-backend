<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\Style;
use App\Models\Color;
use App\Services\MediaService;
use App\Services\PicsumService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TestS3Seeder extends Seeder
{
    protected MediaService $mediaService;
    protected PicsumService $picsumService;

    public function __construct(MediaService $mediaService, PicsumService $picsumService)
    {
        $this->mediaService = $mediaService;
        $this->picsumService = $picsumService;
    }

    public function run(): void
    {
        $this->command->info('Starting S3 test seeder...');
        
        // Get required data for creating products
        $user = User::first();
        $brand = Brand::first();
        $category = Category::first();
        $style = Style::first();
        $color = Color::first();
        
        if (!$user || !$brand || !$category || !$style || !$color) {
            $this->command->error('Missing required data. Make sure users, brands, categories, styles, and colors exist.');
            return;
        }
        
        // Create just 2 test products
        $this->command->info('Creating test products...');
        
        for ($i = 0; $i < 2; $i++) {
            $product = Product::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'style_id' => $style->id,
                'brand_id' => $brand->id,
                'title' => "S3 Test Product #{$i}",
                'description' => "This is a test product to verify S3 uploads.",
                'color_id' => $color->id,
                'price' => 9999,
                'is_available' => true,
            ]);
            
            $this->command->info("Created product ID: {$product->id}");
            
            // Add 2 images for this product
            for ($j = 0; $j < 2; $j++) {
                if ($image = $this->picsumService->getRandomImage()) {
                    $media = $this->mediaService->uploadMedia($product, $image, [
                        'is_primary' => $j === 0,
                        'order' => $j,
                        'metadata' => [
                            'width' => 600,
                            'height' => 800,
                            'source' => 'picsum',
                            'picsum_id' => rand(1, 1000),
                            'test' => true
                        ],
                    ]);
                    
                    $this->command->info("Uploaded image: {$media->path} to disk: {$media->disk}");
                    Log::info("S3 Test Product {$product->id} image uploaded:", [
                        'disk' => $media->disk,
                        'path' => $media->path,
                        'url' => $media->url,
                    ]);
                    
                    // Clean up temp file
                    unlink($image->getPathname());
                }
            }
        }
        
        $this->command->info('S3 test seeder completed successfully!');
    }
} 