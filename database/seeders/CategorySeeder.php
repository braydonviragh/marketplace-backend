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
            [
                'name' => 'Dresses',
                'description' => 'All types of dresses including casual, formal, and special occasion',
                'icon' => 'dress'
            ],
            [
                'name' => 'Tops',
                'description' => 'Shirts, blouses, t-shirts, and all upper body garments',
                'icon' => 'top'
            ],
            [
                'name' => 'Handbags',
                'description' => 'Purses, totes, clutches, and all types of bags',
                'icon' => 'handbag'
            ],
            [
                'name' => 'Accessories',
                'description' => 'Belts, scarves, hats, and other fashion accessories',
                'icon' => 'accessories'
            ],
            [
                'name' => 'Activewear',
                'description' => 'Athletic and workout clothing',
                'icon' => 'activewear'
            ],
            [
                'name' => 'Blazers',
                'description' => 'Professional and casual blazers and jackets',
                'icon' => 'blazer'
            ],
            [
                'name' => 'Bodysuits',
                'description' => 'Form-fitting one-piece garments',
                'icon' => 'bodysuit'
            ],
            [
                'name' => 'Jeans',
                'description' => 'Denim pants in various styles and cuts',
                'icon' => 'jeans'
            ],
            [
                'name' => 'Jewelry',
                'description' => 'Necklaces, earrings, bracelets, and other jewelry items',
                'icon' => 'jewelry'
            ],
            [
                'name' => 'Pants',
                'description' => 'All types of pants excluding jeans',
                'icon' => 'pants'
            ],
            [
                'name' => 'Jumpsuits',
                'description' => 'One-piece outfits and rompers',
                'icon' => 'jumpsuit'
            ],
            [
                'name' => 'Shoes',
                'description' => 'All types of footwear',
                'icon' => 'shoes'
            ],
            [
                'name' => 'Shorts',
                'description' => 'Casual and formal shorts',
                'icon' => 'shorts'
            ],
            [
                'name' => 'Skirts',
                'description' => 'All styles and lengths of skirts',
                'icon' => 'skirt'
            ],
            [
                'name' => 'Sweats & Hoodies',
                'description' => 'Comfortable loungewear and casual outerwear',
                'icon' => 'sweats'
            ],
            [
                'name' => 'Sweaters & Knits',
                'description' => 'Knitted tops and sweaters',
                'icon' => 'sweater'
            ],
            [
                'name' => 'Suits',
                'description' => 'Professional and formal suit sets',
                'icon' => 'suit'
            ],
            [
                'name' => 'Other',
                'description' => 'Other clothing items not listed in other categories',
                'icon' => 'other'
            ],
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