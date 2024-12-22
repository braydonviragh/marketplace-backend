<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Rental;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalFactory extends Factory
{
    protected $model = Rental::class;

    public function definition(): array
    {
        $listing = Listing::inRandomOrder()->first();
        $startDate = fake()->dateTimeBetween('-1 month', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+2 months');
        $totalDays = $startDate->diff($endDate)->days;
        $totalPrice = $listing->daily_price * $totalDays;
        
        return [
            'renter_id' => User::factory(),
            'owner_id' => $listing->user_id,
            'listing_id' => $listing->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_price' => $totalPrice,
            'owner_earnings' => $totalPrice * 0.9,
            'platform_fee' => $totalPrice * 0.1,
            'status' => fake()->randomElement(['pending', 'confirmed', 'in_progress', 'completed']),
            'status_history' => [
                [
                    'from' => 'pending',
                    'to' => 'confirmed',
                    'timestamp' => now()->subDays(5)->toIso8601String()
                ]
            ]
        ];
    }
} 