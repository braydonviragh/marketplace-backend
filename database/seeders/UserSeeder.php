<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Brand;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'username' => 'superadmin',
            'name' => 'Super Admin',
            'role' => 'super_admin',
        ]);

        // Create admin profile
        UserProfile::factory()->create([
            'user_id' => $admin->id,
            'style_preference' => 'unisex',
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