<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $rental = Rental::inRandomOrder()->first();
        
        return [
            'rental_id' => $rental->id,
            'reviewer_id' => $rental->renter_id,
            'reviewee_id' => $rental->owner_id,
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->paragraph(),
            'criteria_ratings' => [
                'communication' => fake()->numberBetween(3, 5),
                'accuracy' => fake()->numberBetween(3, 5),
                'cleanliness' => fake()->numberBetween(3, 5),
                'value' => fake()->numberBetween(3, 5),
            ],
            'is_approved' => true,
        ];
    }
} 