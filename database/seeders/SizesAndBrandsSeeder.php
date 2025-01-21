<?php

namespace Database\Seeders;

use App\Models\LetterSize;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SizesAndBrandsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed general sizes (XS-XXL)
        $letterSizes = [
            [
                'name' => 'XS',
                'display_name' => 'Extra Small',
                'description' => 'Extra Small size, typically fits US size 0-2',
                'slug' => 'xs',
            ],
            [
                'name' => 'S',
                'display_name' => 'Small',
                'description' => 'Small size, typically fits US size 4-6',
                'slug' => 's',
            ],
            [
                'name' => 'M',
                'display_name' => 'Medium',
                'description' => 'Medium size, typically fits US size 8-10',
                'slug' => 'm',
            ],
            [
                'name' => 'L',
                'display_name' => 'Large',
                'description' => 'Large size, typically fits US size 12-14',
                'slug' => 'l',
            ],
            [
                'name' => 'XL',
                'display_name' => 'Extra Large',
                'description' => 'Extra Large size, typically fits US size 16-18',
                'slug' => 'xl',
            ],
            [
                'name' => 'XXL',
                'display_name' => 'Double Extra Large',
                'description' => 'Double Extra Large size, typically fits US size 20-22',
                'slug' => 'xxl',
            ],
        ];

        foreach ($letterSizes as $size) {
            LetterSize::create([
                'name' => $size['name'],
                'display_name' => $size['display_name'],
                'description' => $size['description'],
                'slug' => $size['slug'],
            ]);
        }

        // Seed number sizes (00-22)
        $numberSizes = ['00', '0', '2', '4', '6', '8', '10', '12', '14', '16', '18', '20', '22'];
        foreach ($numberSizes as $index => $size) {
            DB::table('number_sizes')->insert([
                'name' => $size,
                'display_name' => "Size {$size}",
                'description' => "US Women's Size {$size}",
                'slug' => $size,
            ]);
        }

        // Seed waist sizes (24-48)
        for ($size = 24; $size <= 48; $size += 2) {
            DB::table('waist_sizes')->insert([
                'name' => $size,
                'display_name' => "{$size}\"",
                'description' => "{$size} inch waist measurement",
                'slug' => $size,
            ]);
        }

        // Shoe Sizes (5-15, including half sizes)
        $shoeSizes = [];
        for ($size = 5; $size <= 15; $size += 0.5) {
            DB::table('shoe_sizes')->insert([
                'size' => $size,
                'display_name' => number_format($size, 1),
                'description' => "US Size {$size}",
                'order' => ($size - 5) * 2 + 1
            ]);
        }

        // Seed brands
        $brands = [
            ['name' => 'Nike', 'description' => 'Athletic and casual wear', 'slug' => 'nike'],
            ['name' => 'Zara', 'description' => 'Contemporary fashion', 'slug' => 'zara'],
            ['name' => 'H&M', 'description' => 'Affordable fashion', 'slug' => 'h&m'],
            ['name' => 'Gucci', 'description' => 'Luxury fashion house', 'slug' => 'gucci'],
            ['name' => 'Lululemon', 'description' => 'Athletic and yoga wear', 'slug' => 'lululemon'],
            ['name' => 'Uniqlo', 'description' => 'Basic casual wear', 'slug' => 'uniqlo'],
            ['name' => 'The North Face', 'description' => 'Outdoor apparel', 'slug' => 'the-north-face'],
            ['name' => 'Aritzia', 'description' => 'Contemporary women\'s fashion', 'slug' => 'aritzia'],
            ['name' => 'Frank And Oak', 'description' => 'Sustainable casual wear', 'slug' => 'frank-and-oak'],
            ['name' => 'Canada Goose', 'description' => 'Premium outerwear', 'slug' => 'canada-goose'],
            ['name' => 'Other', 'description' => 'Other brands', 'slug' => 'other']
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
} 