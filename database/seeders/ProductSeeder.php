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
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
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
                
                // Initialize all size IDs as null
                $letterSizeId = null;
                $waistSizeId = null;
                $numberSizeId = null;

                // Assign appropriate size based on category type
                switch ($category->slug) {
                    // Top wear - Letter sizes
                    case 'tops':
                    case 'sweaters-knits':
                    case 'blazers':
                    case 'bodysuits':
                    case 'sweats-hoodies':
                        $letterSizeId = $letterSizes->random()->id;
                        break;

                    // Full body/Bottom wear - Number sizes
                    case 'dresses':
                    case 'activewear':
                    case 'skirts':
                    case 'jumpsuits':
                    case 'suits':
                        $numberSizeId = $numberSizes->random()->id;
                        break;

                    // Waist measured items
                    case 'jeans':
                    case 'pants':
                    case 'shorts':
                        $waistSizeId = $waistSizes->random()->id;
                        break;

                    // No size needed for these categories
                    case 'accessories':
                    case 'handbags':
                    case 'jewelry':
                    case 'shoes':
                    case 'other':
                        break;
                }
                
                $product = Product::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'style_id' => $styles->random()->id,
                    'brand_id' => $brands->random()->id,
                    'title' => fake()->sentence(4),
                    'description' => fake()->paragraphs(3, true),
                    'letter_size_id' => $letterSizeId,
                    'waist_size_id' => $waistSizeId,
                    'number_size_id' => $numberSizeId,
                    'color_id' => $colors->random()->id,
                    'price' => fake()->randomFloat(2, 10, 1000),
                    'city' => fake()->city(),
                    'province' => 'Ontario',
                    'postal_code' => fake()->postcode(),
                    'is_available' => true,
                ]);
            }
        }
    }
} 