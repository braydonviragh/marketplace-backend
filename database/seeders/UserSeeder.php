<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use App\Models\Style;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'phone_number' => '+1234567890',
            'role' => 'super_admin',
            'onboarding_completed' => true,
        ]);

        // Create admin profile
        UserProfile::factory()->create([
            'user_id' => $admin->id,
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'style_id' => Style::where('slug', 'unisex')->first()->id,
        ]);

        // Attach some random brands to admin (using sync instead of attach)
        $brands = Brand::inRandomOrder()->limit(3)->pluck('id');
        $admin->brands()->sync($brands);

        // Create 15 random users with profiles and brand preferences
        User::factory(15)
            ->has(UserProfile::factory(), 'profile')
            ->create(['role' => 'user']);
    }
} 