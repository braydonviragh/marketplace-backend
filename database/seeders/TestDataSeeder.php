<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Listing;
use App\Models\Rental;
use App\Models\Review;
use App\Models\Payment;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed categories if none exist
        if (Category::count() === 0) {
            $this->call([
                CategorySeeder::class
            ]);
        }

        // Create users
        $users = User::factory(10)->create();

        // Create listings with existing users and categories
        $listings = Listing::factory(10)->create([
            'user_id' => fn() => $users->random()->id,
            'category_id' => fn() => Category::inRandomOrder()->first()->id
        ]);

        // Create rentals with existing listings
        $rentals = Rental::factory(10)->create([
            'listing_id' => fn() => $listings->random()->id,
            'renter_id' => fn() => $users->random()->id,
            'owner_id' => fn() => $listings->random()->user_id
        ]);

        // Create reviews for completed rentals
        Review::factory(10)->create([
            'rental_id' => fn() => $rentals->random()->id,
            'reviewer_id' => fn() => $rentals->random()->renter_id,
            'reviewee_id' => fn() => $rentals->random()->owner_id
        ]);

        // Create payments for rentals
        Payment::factory(10)->create([
            'rental_id' => fn() => $rentals->random()->id,
            'payer_id' => fn() => $rentals->random()->renter_id,
            'payee_id' => fn() => $rentals->random()->owner_id
        ]);

        // Create notifications for users
        Notification::factory(10)->create([
            'user_id' => fn() => $users->random()->id
        ]);
    }
} 