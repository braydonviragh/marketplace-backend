<?php

namespace Database\Seeders;

use App\Models\RentalStatus;
use Illuminate\Database\Seeder;

class RentalStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name' => 'Pending',
                'slug' => 'pending',
                'description' => 'Rental request is pending approval'
            ],
            [
                'name' => 'Active',
                'slug' => 'active',
                'description' => 'Rental is currently active'
            ],
            [
                'name' => 'Completed',
                'slug' => 'completed',
                'description' => 'Rental period has ended'
            ],
            [
                'name' => 'Rejected',
                'slug' => 'rejected',
                'description' => 'Rental request was rejected by the owner'
            ],
            [
                'name' => 'Cancelled',
                'slug' => 'cancelled',
                'description' => 'Rental was cancelled'
            ]
        ];

        foreach ($statuses as $status) {
            RentalStatus::create($status);
        }
    }
} 