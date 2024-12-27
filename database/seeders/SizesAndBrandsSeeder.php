<?php

namespace Database\Seeders;

use App\Models\Size;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SizesAndBrandsSeeder extends Seeder
{
    public function run(): void
    {
        // Seed general sizes (XS-XXL)
        $generalSizes = [
            [
                'size_name' => 'XS',
                'display_name' => 'Extra Small',
                'description' => 'Extra Small size, typically fits US size 0-2',
                'order' => 1
            ],
            [
                'size_name' => 'S',
                'display_name' => 'Small',
                'description' => 'Small size, typically fits US size 4-6',
                'order' => 2
            ],
            [
                'size_name' => 'M',
                'display_name' => 'Medium',
                'description' => 'Medium size, typically fits US size 8-10',
                'order' => 3
            ],
            [
                'size_name' => 'L',
                'display_name' => 'Large',
                'description' => 'Large size, typically fits US size 12-14',
                'order' => 4
            ],
            [
                'size_name' => 'XL',
                'display_name' => 'Extra Large',
                'description' => 'Extra Large size, typically fits US size 16-18',
                'order' => 5
            ],
            [
                'size_name' => 'XXL',
                'display_name' => 'Double Extra Large',
                'description' => 'Double Extra Large size, typically fits US size 20-22',
                'order' => 6
            ],
        ];

        foreach ($generalSizes as $size) {
            Size::create([
                'size_name' => $size['size_name'],
                'display_name' => $size['display_name'],
                'description' => $size['description'],
                'order' => $size['order'],
                'is_active' => true
            ]);
        }

        // Seed number sizes (00-22)
        $numberSizes = ['00', '0', '2', '4', '6', '8', '10', '12', '14', '16', '18', '20', '22'];
        foreach ($numberSizes as $index => $size) {
            DB::table('number_sizes')->insert([
                'name' => $size,
                'display_name' => "Size {$size}",
                'description' => "US Women's Size {$size}",
                'order' => $index + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed waist sizes (24-48)
        for ($size = 24; $size <= 48; $size += 2) {
            DB::table('waist_sizes')->insert([
                'size' => $size,
                'display_name' => "{$size}\"",
                'description' => "{$size} inch waist measurement",
                'order' => ($size - 22) / 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed shoe sizes (5-15 with half sizes)
        for ($size = 5.0; $size <= 15.0; $size += 0.5) {
            DB::table('shoe_sizes')->insert([
                'size' => $size,
                'display_name' => "US {$size}",
                'description' => "US Women's Shoe Size {$size}",
                'order' => ($size - 5) * 2 + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed brands
        $brands = [
            ['name' => 'Nike', 'description' => 'Athletic and casual wear'],
            ['name' => 'Zara', 'description' => 'Contemporary fashion'],
            ['name' => 'H&M', 'description' => 'Affordable fashion'],
            ['name' => 'Gucci', 'description' => 'Luxury fashion house'],
            ['name' => 'Lululemon', 'description' => 'Athletic and yoga wear'],
            ['name' => 'Uniqlo', 'description' => 'Basic casual wear'],
            ['name' => 'The North Face', 'description' => 'Outdoor apparel'],
            ['name' => 'Aritzia', 'description' => 'Contemporary women\'s fashion'],
            ['name' => 'Frank And Oak', 'description' => 'Sustainable casual wear'],
            ['name' => 'Canada Goose', 'description' => 'Premium outerwear'],
            ['name' => 'Other', 'description' => 'Other brands']
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
} 