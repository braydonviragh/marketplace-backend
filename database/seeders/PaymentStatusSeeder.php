<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payment_status')->insert([
            [
                'id' => 1,
                'name' => 'Pending',
                'slug' => 'pending',
            ],
            [
                'id' => 2,
                'name' => 'Completed',
                'slug' => 'completed',
            ],
            [
                'id' => 3,
                'name' => 'Failed',
                'slug' => 'failed',
            ],
            [
                'id' => 4,
                'name' => 'Cancelled',
                'slug' => 'cancelled',
            ],
            [
                'id' => 5,
                'name' => 'Processing',
                'slug' => 'processing',
            ],
            [
                'id' => 6,
                'name' => 'Refunded',
                'slug' => 'refunded',
            ],
        ]);
    }
} 