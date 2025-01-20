<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            [
                'name' => 'Black',
                'slug' => 'black',
                'hex_code' => '#000000',
            ],
            [
                'name' => 'White',
                'slug' => 'white',
                'hex_code' => '#FFFFFF',
            ],
            [
                'name' => 'Gray',
                'slug' => 'gray',
                'hex_code' => '#808080',
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'hex_code' => '#FFD700',
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'hex_code' => '#C0C0C0',
            ],
            [
                'name' => 'Tan',
                'slug' => 'tan',
                'hex_code' => '#D2B48C',
            ],
            [
                'name' => 'Brown',
                'slug' => 'brown',
                'hex_code' => '#8B4513',
            ],
            [
                'name' => 'Red',
                'slug' => 'red',
                'hex_code' => '#FF0000',
            ],
            [
                'name' => 'Orange',
                'slug' => 'orange',
                'hex_code' => '#FFA500',
            ],
            [
                'name' => 'Yellow',
                'slug' => 'yellow',
                'hex_code' => '#FFFF00',
            ],
            [
                'name' => 'Green',
                'slug' => 'green',
                'hex_code' => '#008000',
            ],
            [
                'name' => 'Blue',
                'slug' => 'blue',
                'hex_code' => '#0000FF',
            ],
            [
                'name' => 'Purple',
                'slug' => 'purple',
                'hex_code' => '#800080',
            ],
            [
                'name' => 'Pink',
                'slug' => 'pink',
                'hex_code' => '#FFC0CB',
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'hex_code' => '#OTHER', // Special code for other colors
            ],
        ];

        foreach ($colors as $color) {
            Color::create([
                'name' => $color['name'],
                'hex_code' => $color['hex_code'],
                'slug' => Str::slug($color['name']),
            ]);
        }
    }
} 