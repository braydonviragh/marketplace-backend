<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin user
        User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '+1234567890',
            'bio' => 'System Administrator',
            'role' => 'admin',
            'account_type' => 'business',
            'email_verified_at' => now(),
            'is_active' => true,
            'timezone' => 'America/Toronto',
            'locale' => 'en',
            'country_code' => 'CA',
            'region_code' => 'ON',
            'last_login_at' => now(),
        ]);

        // Create moderator user
        User::create([
            'first_name' => 'Content',
            'last_name' => 'Moderator',
            'email' => 'moderator@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '+1234567891',
            'bio' => 'Content Moderator',
            'role' => 'moderator',
            'account_type' => 'business',
            'email_verified_at' => now(),
            'is_active' => true,
            'timezone' => 'America/Toronto',
            'locale' => 'en',
            'country_code' => 'CA',
            'region_code' => 'ON',
            'last_login_at' => now(),
        ]);

        // Create test business user
        User::create([
            'first_name' => 'Business',
            'last_name' => 'User',
            'email' => 'business@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '+1234567892',
            'bio' => 'Business Account',
            'role' => 'user',
            'account_type' => 'business',
            'email_verified_at' => now(),
            'is_active' => true,
            'timezone' => 'America/Toronto',
            'locale' => 'en',
            'country_code' => 'CA',
            'region_code' => 'ON',
        ]);

        // Create test personal user
        User::create([
            'first_name' => 'Personal',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'phone_number' => '+1234567893',
            'bio' => 'Personal Account',
            'role' => 'user',
            'account_type' => 'personal',
            'email_verified_at' => now(),
            'is_active' => true,
            'timezone' => 'America/Toronto',
            'locale' => 'en',
            'country_code' => 'CA',
            'region_code' => 'ON',
        ]);

        // Create random users with factory
        User::factory(20)->create();

        // Create some unverified users
        User::factory(5)
            ->unverified()
            ->create();

        // Create some inactive users
        User::factory(3)
            ->state([
                'is_active' => false,
                'email_verified_at' => null
            ])
            ->create();
    }
} 