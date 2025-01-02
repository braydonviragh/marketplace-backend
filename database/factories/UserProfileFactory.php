<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Size;
use App\Models\Brand;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    public function definition(): array
    {
        $preferredBrands = Brand::inRandomOrder()->limit(rand(2, 4))->pluck('id')->toArray();
        $preferredCategories = Category::inRandomOrder()->limit(rand(3, 5))->pluck('id')->toArray();
        
        $city = fake()->randomElement(['Toronto', 'Ottawa', 'Mississauga', 'Hamilton']);
        
        return [
            'user_id' => User::factory(),
            'profile_picture' => 'profiles/default.jpg',
            'birthday' => Carbon::now()->subYears(rand(18, 50))->format('Y-m-d'),
            'city' => $city,
            'postal_code' => match($city) {
                'Toronto' => fake()->randomElement(['M4B', 'M5A', 'M5J', 'M5V', 'M6H', 'M6J', 'M6K']),
                'Ottawa' => fake()->randomElement(['K1P', 'K1R', 'K1S', 'K1Y', 'K2P']), 
                'Mississauga' => fake()->randomElement(['L4W', 'L4X', 'L4Y', 'L4Z', 'L5A', 'L5B']),
                'Hamilton' => fake()->randomElement(['L8E', 'L8G', 'L8H', 'L8J', 'L8K', 'L8L', 'L8M', 'L8N', 'L8P', 'L8R', 'L8S', 'L8T', 'L8V', 'L8W']),
                default => throw new \Exception('Invalid city')
            } . ' ' . str_pad(fake()->numberBetween(0, 999), 3, '0', STR_PAD_LEFT),
            'country' => 'Canada',
            'style_preference' => fake()->randomElement(['male', 'female', 'unisex']),
            'language' => 'en',
            'preferences' => [
                'preferred_brands' => $preferredBrands,
                'preferred_categories' => $preferredCategories,
            ]
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($profile) {
            // Sync preferred brands to the pivot table
            if (!empty($profile->preferences['preferred_brands'])) {
                $profile->user->brands()->syncWithoutDetaching($profile->preferences['preferred_brands']);
            }
            
            // Sync preferred categories to the pivot table
            if (!empty($profile->preferences['preferred_categories'])) {
                $profile->user->categories()->syncWithoutDetaching($profile->preferences['preferred_categories']);
            }
        });
    }
} 