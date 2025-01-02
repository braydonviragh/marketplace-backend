<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\Size;
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
        $sizes = Size::all();
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

        if ($sizes->isEmpty()) {
            $this->command->error('No sizes found. Please run SizesAndBrandsSeeder first.');
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
                $size = $sizes->random();
                
                $product = Product::create([
                    'user_id' => $user->id,
                    'category_id' => $categories->random()->id,
                    'style_id' => $styles->random()->id,
                    'brand_id' => $brands->random()->id,
                    'title' => fake()->sentence(4),
                    'description' => fake()->paragraphs(3, true),
                    'size_id' => $size->id,
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