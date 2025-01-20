<?php

namespace Database\Seeders;

use App\Models\OfferStatus;
use Illuminate\Database\Seeder;

class OfferStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'description' => 'Offer is waiting for owner response'
            ],
            [
                'name' => 'Accepted',
                'slug' => 'accepted',
                'description' => 'Offer has been accepted by the owner'
            ],
            [
                'name' => 'Rejected',
                'slug' => 'rejected',
                'description' => 'Offer has been rejected by the owner'
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'description' => 'Offer was cancelled by the renter'
            ],
            [
                'name' => 'Expired',
                'slug' => 'expired',
                'description' => 'Offer has expired without response'
            ],
            [
                'name' => 'Withdrawn',
                'slug' => 'withdrawn',
                'description' => 'Offer was withdrawn by the owner'
            ]
        ];

        foreach ($statuses as $status) {
            OfferStatus::create($status);
        }
    }
} 