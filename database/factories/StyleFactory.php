<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Style;

class StyleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement(['Mens', 'Womens', 'Unisex']),
            'slug' => function (array $attributes) {
                return strtolower($attributes['name']);
            },
        ];
    }
} 