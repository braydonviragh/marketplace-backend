<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Models\OfferStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run()
    {
        // Get available products that are not owned by the potential renters
        $products = Product::where('is_available', true)->take(5)->get();
        
        // Get potential renters (excluding product owners)
        $users = User::all();
        
        // Get all possible offer statuses
        $offerStatuses = OfferStatus::all();

        foreach ($products as $product) {
            // Filter out the product owner from potential renters
            $potentialRenters = $users->where('id', '!=', $product->user_id);
            
            // Create an offer
            $renter = $potentialRenters->random();
            
            // Generate random dates within next 30 days
            $startDate = Carbon::now()->addDays(rand(1, 15));
            $endDate = (clone $startDate)->addDays(rand(3, 14));

            Offer::create([
                'product_id' => $product->id,
                'user_id' => $renter->id,
                'offer_status_id' => $offerStatuses->random()->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }
    }
} 