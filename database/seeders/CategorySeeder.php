<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Essential Categories
            [
                'name' => 'Dresses',
                'icon' => 'fa-dress',
                'description' => 'All types of dresses'
            ],
            [
                'name' => 'Tops',
                'icon' => 'fa-shirt',
                'description' => 'Shirts, blouses, and tank tops'
            ],
            [
                'name' => 'Skirts',
                'icon' => 'fa-skirt',
                'description' => 'Mini to maxi skirts'
            ],
            [
                'name' => 'Pants',
                'icon' => 'fa-pants',
                'description' => 'Jeans, trousers, and leggings'
            ],
            [
                'name' => 'Sweaters',
                'icon' => 'fa-sweater',
                'description' => 'Sweaters and cardigans'
            ],
            [
                'name' => 'Jackets',
                'icon' => 'fa-jacket',
                'description' => 'Jackets, coats, and blazers'
            ],
            [
                'name' => 'Sets',
                'icon' => 'fa-clothes-hanger',
                'description' => 'Matching sets and coordinates'
            ],
            [
                'name' => 'Accessories',
                'icon' => 'fa-bag',
                'description' => 'Bags, jewelry, and other accessories'
            ]
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'icon' => $category['icon'],
                'is_active' => true
            ]);
        }
    }
} 