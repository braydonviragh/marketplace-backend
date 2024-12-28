<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Size;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'profile_picture' => 'profiles/default.jpg',
            'birthday' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'zip_code' => fake()->postcode(),
            'style_preference' => fake()->randomElement(['male', 'female', 'unisex']),
            'language' => 'en',
            'preferences' => [
                'favorite_brands' => Brand::inRandomOrder()->limit(rand(2, 4))->pluck('id')->toArray(),
                'preferred_categories' => fake()->randomElements([
                    'Dresses', 'Tops', 'Handbags', 'Accessories', 'Activewear',
                    'Blazers', 'Bodysuits', 'Jeans', 'Jewelry', 'Pants',
                    'Jumpsuits', 'Shoes', 'Shorts', 'Skirts', 'Sweats & Hoodies',
                    'Sweaters & Knits', 'Suits'
                ], rand(3, 5)),
            ]
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($profile) {
            // Sync favorite brands to the pivot table
            if (!empty($profile->preferences['favorite_brands'])) {
                $profile->user->brands()->syncWithoutDetaching($profile->preferences['favorite_brands']);
            }
        });
    }
} 