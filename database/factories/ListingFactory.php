<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Listing;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

class ListingFactory extends Factory
{
    protected $model = Listing::class;

    public function definition(): array
    {
        $conditions = ['new', 'like_new', 'good', 'fair'];
        $sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        
        $latitude = fake()->latitude(42.0, 44.0);
        $longitude = fake()->longitude(-80.0, -76.0);
        
        return [
            'user_id' => User::factory(),
            'category_id' => Category::inRandomOrder()->first()->id,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'brand' => fake()->company(),
            'size' => fake()->randomElement($sizes),
            'condition' => fake()->randomElement($conditions),
            'daily_price' => fake()->randomFloat(2, 10, 100),
            'weekly_price' => fake()->randomFloat(2, 50, 500),
            'monthly_price' => fake()->randomFloat(2, 150, 1500),
            'security_deposit' => fake()->randomFloat(2, 50, 300),
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'location' => DB::raw("ST_GeomFromText('POINT($longitude $latitude)')"),
            'is_available' => true,
            'is_approved' => true,
            'specifications' => [
                'color' => fake()->colorName(),
                'material' => fake()->randomElement(['cotton', 'silk', 'wool', 'polyester']),
                'style' => fake()->word(),
            ],
            'care_instructions' => [
                'washing' => fake()->sentence(),
                'drying' => fake()->sentence(),
                'ironing' => fake()->sentence(),
            ],
        ];
    }
} 