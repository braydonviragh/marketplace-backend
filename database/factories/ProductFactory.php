<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use App\Models\NumberSize;
use App\Models\ShoeSize;
use App\Models\WaistSize;
use App\Models\Color;
use App\Models\Brand;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected function getSizeByCategory(Category $category): array
    {
        // Define category-size type mappings
        $sizeTypes = [
            // Categories using letter sizes (XS-XXL)
            'Top' => 'letter',
            'Blazer' => 'letter',
            'Sweats/Hoodies' => 'letter',
            
            // Categories using number sizes (00-22)
            'Dress' => 'number',
            'Bodysuit' => 'number',
            'Jumpsuit' => 'number',
            'Skirts' => 'number',
            
            // Categories using waist sizes (24-48)
            'Jeans' => 'waist',
            'Pants' => 'waist',
            'Shorts' => 'waist',
            
            // Categories using shoe sizes (5-15)
            'Shoes' => 'shoe',
            
            // Categories that don't use sizes
            'Handbag' => 'one_size',
            'Accessories' => 'one_size',
            'Jewelry' => 'one_size',
        ];

        $sizeType = $sizeTypes[$category->name] ?? 'letter';
        $size = null;
        $sizeId = null;

        switch ($sizeType) {
            case 'number':
                $sizeModel = NumberSize::inRandomOrder()->first();
                $size = $sizeModel->name;
                $sizeId = $sizeModel->id;
                break;
                
            case 'waist':
                $sizeModel = WaistSize::inRandomOrder()->first();
                $size = $sizeModel->size . '"';
                $sizeId = $sizeModel->id;
                break;
                
            case 'shoe':
                $sizeModel = ShoeSize::inRandomOrder()->first();
                $size = (string)$sizeModel->size;
                $sizeId = $sizeModel->id;
                break;
                
            case 'one_size':
                $size = 'One Size';
                break;
                
            case 'letter':
            default:
                $size = fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']);
                break;
        }

        return [
            'size_id' => $sizeId,
        ];
    }

    public function definition(): array
    {
        $category = Category::inRandomOrder()->first() ?? Category::factory()->create();
        $sizeInfo = $this->getSizeByCategory($category);
        
        return [
            'user_id' => User::factory(),
            'category_id' => $category->id,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'brand_id' => Brand::factory(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'size_id' => $sizeInfo['size_id'],
            'color_id' => Color::factory(),
            'is_available' => true,
            'city' => fake()->city(),
            'province' => 'Ontario',
            'postal_code' => fake()->postcode(),
        ];
    }
} 