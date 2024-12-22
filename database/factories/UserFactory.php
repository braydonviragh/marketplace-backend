<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $accountType = $this->faker->randomElement(['personal', 'business']);
        $isPersonal = $accountType === 'personal';

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'phone_number' => $this->faker->phoneNumber(),
            'bio' => $this->faker->text(200),
            'profile_picture' => $this->faker->imageUrl(200, 200, 'people'),
            'account_type' => $accountType,
            'role' => $this->faker->randomElement(['user', 'user', 'user', 'moderator']), // 75% users
            'email_verified_at' => now(),
            'is_active' => true,
            'timezone' => $this->faker->randomElement([
                'America/Toronto', 'America/New_York', 'America/Los_Angeles'
            ]),
            'locale' => $this->faker->randomElement(['en', 'fr', 'es']),
            'country_code' => 'CA',
            'region_code' => $this->faker->randomElement([
                'ON', 'BC', 'AB', 'QC', 'NS'
            ]),
            'last_login_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'last_login_ip' => $this->faker->ipv4,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
} 