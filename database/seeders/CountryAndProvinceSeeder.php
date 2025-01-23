<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountryAndProvinceSeeder extends Seeder
{
    public function run()
    {
        // Insert Canada
        $canadaId = DB::table('countries')->insertGetId([
            'name' => 'Canada',
            'slug' => 'canada',
            'abbreviation' => 'CA'
        ]);

        // Insert Canadian provinces and territories
        $provinces = [
            ['name' => 'Alberta', 'abbreviation' => 'AB'],
            ['name' => 'British Columbia', 'abbreviation' => 'BC'],
            ['name' => 'Manitoba', 'abbreviation' => 'MB'],
            ['name' => 'New Brunswick', 'abbreviation' => 'NB'],
            ['name' => 'Newfoundland and Labrador', 'abbreviation' => 'NL'],
            ['name' => 'Nova Scotia', 'abbreviation' => 'NS'],
            ['name' => 'Ontario', 'abbreviation' => 'ON'],
            ['name' => 'Prince Edward Island', 'abbreviation' => 'PE'],
            ['name' => 'Quebec', 'abbreviation' => 'QC'],
            ['name' => 'Saskatchewan', 'abbreviation' => 'SK'],
            ['name' => 'Northwest Territories', 'abbreviation' => 'NT'],
            ['name' => 'Nunavut', 'abbreviation' => 'NU'],
            ['name' => 'Yukon', 'abbreviation' => 'YT'],
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->insert([
                'country_id' => $canadaId,
                'name' => $province['name'],
                'slug' => Str::slug($province['name']),
                'abbreviation' => $province['abbreviation']
            ]);
        }
    }
} 