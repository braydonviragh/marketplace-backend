<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CountryAndProvinceSeeder::class,
            CategorySeeder::class,
            ColorSeeder::class,
            PaymentStatusSeeder::class,
            SizesAndBrandsSeeder::class,
            RentalStatusSeeder::class,  
            UserSeeder::class,
            ProductSeeder::class,
            OfferStatusSeeder::class,
        ]);
    }
} 