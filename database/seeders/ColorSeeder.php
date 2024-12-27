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
                'hex_code' => '#000000',
            ],
            [
                'name' => 'White',
                'hex_code' => '#FFFFFF',
            ],
            [
                'name' => 'Gray',
                'hex_code' => '#808080',
            ],
            [
                'name' => 'Gold',
                'hex_code' => '#FFD700',
            ],
            [
                'name' => 'Silver',
                'hex_code' => '#C0C0C0',
            ],
            [
                'name' => 'Tan',
                'hex_code' => '#D2B48C',
            ],
            [
                'name' => 'Brown',
                'hex_code' => '#8B4513',
            ],
            [
                'name' => 'Red',
                'hex_code' => '#FF0000',
            ],
            [
                'name' => 'Orange',
                'hex_code' => '#FFA500',
            ],
            [
                'name' => 'Yellow',
                'hex_code' => '#FFFF00',
            ],
            [
                'name' => 'Green',
                'hex_code' => '#008000',
            ],
            [
                'name' => 'Blue',
                'hex_code' => '#0000FF',
            ],
            [
                'name' => 'Purple',
                'hex_code' => '#800080',
            ],
            [
                'name' => 'Pink',
                'hex_code' => '#FFC0CB',
            ],
            [
                'name' => 'Multiple',
                'hex_code' => '#RAINBOW', // Special code for multiple colors
            ],
            [
                'name' => 'Other',
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