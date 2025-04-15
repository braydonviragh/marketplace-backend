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
                'slug' => 'dresses',
                'description' => 'All types of dresses including casual, formal, and special occasion',
                'icon' => 'dress'
            ],
            [
                'name' => 'Tops',
                'slug' => 'tops',
                'description' => 'Shirts, blouses, t-shirts, and all upper body garments',
                'icon' => 'top'
            ],
            [
                'name' => 'Handbags',
                'slug' => 'handbags',
                'description' => 'Purses, totes, clutches, and all types of bags',
                'icon' => 'handbag'
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Belts, scarves, hats, and other fashion accessories',
                'icon' => 'accessories'
            ],
            [
                'name' => 'Activewear',
                'slug' => 'activewear',
                'description' => 'Athletic and workout clothing',
                'icon' => 'activewear'
            ],
            [
                'name' => 'Blazers',
                'slug' => 'blazers',
                'description' => 'Professional and casual blazers and jackets',
                'icon' => 'blazer'
            ],
            [
                'name' => 'Bodysuits',
                'slug' => 'bodysuits',
                'description' => 'Form-fitting one-piece garments',
                'icon' => 'bodysuit'
            ],
            [
                'name' => 'Jeans',
                'slug' => 'jeans',
                'description' => 'Denim pants in various styles and cuts',
                'icon' => 'jeans'
            ],
            [
                'name' => 'Jewelry',
                'slug' => 'jewelry',
                'description' => 'Necklaces, earrings, bracelets, and other jewelry items',
                'icon' => 'jewelry'
            ],
            [
                'name' => 'Pants',
                'slug' => 'pants',
                'description' => 'All types of pants excluding jeans',
                'icon' => 'pants'
            ],
            [
                'name' => 'Jumpsuits',
                'slug' => 'jumpsuits',
                'description' => 'One-piece outfits and rompers',
                'icon' => 'jumpsuit'
            ],
            // [
            //     'name' => 'Shoes',
            //     'slug' => 'shoes',
            //     'description' => 'All types of footwear',
            //     'icon' => 'shoes'
            // ],
            [
                'name' => 'Shorts',
                'slug' => 'shorts',
                'description' => 'Casual and formal shorts',
                'icon' => 'shorts'
            ],
            [
                'name' => 'Skirts',
                'slug' => 'skirts',
                'description' => 'All styles and lengths of skirts',
                'icon' => 'skirt'
            ],
            [
                'name' => 'Sweats',
                'slug' => 'sweats',
                'description' => 'Comfortable loungewear and casual outerwear',
                'icon' => 'sweat'
            ],
            [
                'name' => 'Knitwear',
                'slug' => 'knitwear',
                'description' => 'Knitted tops and sweaters',
                'icon' => 'knit'
            ],
            [
                'name' => 'Suits',
                'slug' => 'suits',
                'description' => 'Professional and formal suit sets',
                'icon' => 'suit'
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
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
            ]);
        }
    }
}