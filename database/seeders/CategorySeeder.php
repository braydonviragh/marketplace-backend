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
                'icon' => '001-dress'
            ],
            [
                'name' => 'Tops',
                'slug' => 'tops',
                'description' => 'Shirts, blouses, t-shirts, and all upper body garments',
                'icon' => '002-tshirt'
            ],
            [
                'name' => 'Handbags',
                'slug' => 'handbags',
                'description' => 'Purses, totes, clutches, and all types of bags',
                'icon' => '003-handbag'
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Belts, scarves, hats, and other fashion accessories',
                'icon' => '004-safety-glasses'
            ],
            [
                'name' => 'Activewear',
                'slug' => 'activewear',
                'description' => 'Athletic and workout clothing',
                'icon' => '005-sport'
            ],
            [
                'name' => 'Blazers',
                'slug' => 'blazers',
                'description' => 'Professional and casual blazers and jackets',
                'icon' => '006-suit'
            ],
            [
                'name' => 'Bodysuits',
                'slug' => 'bodysuits',
                'description' => 'Form-fitting one-piece garments',
                'icon' => '007-diving-suit'
            ],
            [
                'name' => 'Jeans',
                'slug' => 'jeans',
                'description' => 'Denim pants in various styles and cuts',
                'icon' => '008-race-suit'
            ],
            [
                'name' => 'Jewelry',
                'slug' => 'jewelry',
                'description' => 'Necklaces, earrings, bracelets, and other jewelry items',
                'icon' => '010-diamonds'
            ],
            [
                'name' => 'Pants',
                'slug' => 'pants',
                'description' => 'All types of pants excluding jeans',
                'icon' => '009-trousers'
            ],
            [
                'name' => 'Jumpsuits',
                'slug' => 'jumpsuits',
                'description' => 'One-piece outfits and rompers',
                'icon' => '011-trousers-1'
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
                'icon' => '012-short'
            ],
            [
                'name' => 'Skirts',
                'slug' => 'skirts',
                'description' => 'All styles and lengths of skirts',
                'icon' => '013-skirt'
            ],
            [
                'name' => 'Sweats',
                'slug' => 'sweats',
                'description' => 'Comfortable loungewear and casual outerwear',
                'icon' => '015-hoodie'
            ],
            [
                'name' => 'Knitwear',
                'slug' => 'knitwear',
                'description' => 'Knitted tops and sweaters',
                'icon' => '014-sweater'
            ],
            [
                'name' => 'Suits',
                'slug' => 'suits',
                'description' => 'Professional and formal suit sets',
                'icon' => '016-suit-1'
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Other clothing items not listed in other categories',
                'icon' => '017-laundry-bag'
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