<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\LetterSize;
use App\Models\WaistSize;
use App\Models\NumberSize;
use App\Models\Color;
use App\Models\Style;
use App\Services\MediaService;
use App\Services\PicsumService;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
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
        // Get all regular users (excluding super_admin)
        $users = User::where('role', 'user')->get();
        $brands = Brand::all();
        $categories = Category::all();
        $letterSizes = LetterSize::all();
        $waistSizes = WaistSize::all();
        $numberSizes = NumberSize::all();
        $colors = Color::all();
        $styles = Style::all();

        // Check if we have the necessary data
        if ($users->isEmpty()) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        if ($brands->isEmpty()) {
            $this->command->error('No brands found. Please run SizesAndBrandsSeeder first.');
            return;
        }

        if ($categories->isEmpty()) {
            $this->command->error('No categories found. Please run CategorySeeder first.');
            return;
        }

        if ($letterSizes->isEmpty()) {
            $this->command->error('No letter sizes found. Please run SizesAndBrandsSeeder first.');
            return;
        }

        if ($styles->isEmpty()) {
            $this->command->error('No styles found. Please run StyleSeeder first.');
            return;
        }
        
        foreach ($users as $user) {
            // Create 2-5 products per user
            $productsCount = rand(2, 5);
            
            for ($i = 0; $i < $productsCount; $i++) {
                $category = $categories->random();
                
                // Initialize sizeable fields as null
                $sizeableType = null;
                $sizeableId = null;

                // Assign appropriate size based on category type
                switch ($category->slug) {
                    // Top wear - Letter sizes
                    case 'tops':
                    case 'sweaters-knits':
                    case 'blazers':
                    case 'bodysuits':
                    case 'sweats-hoodies':
                        $size = $letterSizes->random();
                        $sizeableType = LetterSize::class;
                        $sizeableId = $size->id;
                        break;

                    // Full body/Bottom wear - Number sizes
                    case 'dresses':
                    case 'activewear':
                    case 'skirts':
                    case 'jumpsuits':
                    case 'suits':
                        $size = $numberSizes->random();
                        $sizeableType = NumberSize::class;
                        $sizeableId = $size->id;
                        break;

                    // Waist measured items
                    case 'jeans':
                    case 'pants':
                    case 'shorts':
                        $size = $waistSizes->random();
                        $sizeableType = WaistSize::class;
                        $sizeableId = $size->id;
                        break;

                    // No size needed for these categories
                    case 'accessories':
                    case 'handbags':
                    case 'jewelry':
                    case 'shoes':
                    case 'other':
                        break;
                }
                
                // Generate a price that's a multiple of 25 with .00 cents
                $price = ceil(fake()->numberBetween(10, 1000) / 25) * 25;

                // Generate realistic clothing titles
                $adjectives = ['Vintage', 'Designer', 'Classic', 'Luxury', 'Premium', 'Elegant', 'Casual', 'Modern', 'Trendy', 'Chic'];
                $conditions = ['Like New', 'Gently Used', 'Excellent Condition', 'New with Tags'];
                
                // Category-specific item names
                $itemNames = [
                    'tops' => ['Blouse', 'T-Shirt', 'Tank Top', 'Crop Top', 'Button-Down Shirt'],
                    'sweaters-knits' => ['Cardigan', 'Pullover', 'Turtleneck', 'Cashmere Sweater', 'Knit Top'],
                    'blazers' => ['Blazer', 'Suit Jacket', 'Sport Coat', 'Tailored Jacket'],
                    'dresses' => ['Maxi Dress', 'Cocktail Dress', 'Summer Dress', 'Evening Gown', 'Wrap Dress'],
                    'jeans' => ['Skinny Jeans', 'Mom Jeans', 'Boyfriend Jeans', 'Straight Leg Jeans', 'Bootcut Jeans'],
                    'pants' => ['Trousers', 'Slacks', 'Palazzo Pants', 'Dress Pants', 'Chinos'],
                    // Add more categories as needed
                ];

                $itemName = isset($itemNames[$category->slug]) 
                    ? fake()->randomElement($itemNames[$category->slug])
                    : $category->name;

                // Get a random brand once for this product
                $brand = $brands->random();
                
                $title = fake()->randomElement($adjectives) . ' ' . 
                         $brand->name . ' ' .
                         $itemName . ' - ' .
                         fake()->randomElement($conditions);

                $product = Product::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'style_id' => $styles->random()->id,
                    'brand_id' => $brand->id,
                    'title' => $title,
                    'description' => fake()->paragraphs(3, true),
                    'sizeable_type' => $sizeableType,
                    'sizeable_id' => $sizeableId,
                    'color_id' => $colors->random()->id,
                    'price' => $price,
                    'is_available' => true,
                ]);
            }
        }

        // Add sample images for each product
        foreach (Product::all() as $product) {
            // Add 1-5 images for each product
            $imageCount = rand(1, 5);
            
            for ($i = 0; $i < $imageCount; $i++) {
                if ($image = $this->picsumService->getRandomImage()) {
                    $this->mediaService->uploadMedia($product, $image, [
                        'is_primary' => $i === 0,
                        'order' => $i,
                        'metadata' => [
                            'width' => 600,
                            'height' => 800,
                            'source' => 'picsum',
                            'picsum_id' => rand(1, 1000),
                        ],
                    ]);
                    
                    // Clean up temp file
                    unlink($image->getPathname());
                }
            }
        }
    }
} 