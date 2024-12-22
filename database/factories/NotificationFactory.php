<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $types = ['rental_request', 'payment_received', 'review_received', 'rental_reminder'];
        $type = fake()->randomElement($types);
        
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'type' => $type,
            'title' => $this->getTitleForType($type),
            'message' => fake()->sentence(),
            'data' => [
                'action_url' => '/dashboard/' . fake()->word(),
                'icon' => 'fa-bell',
            ],
            'status' => fake()->randomElement(['pending', 'sent', 'read']),
            'channel' => fake()->randomElement(['push', 'email', 'sms']),
        ];
    }

    private function getTitleForType(string $type): string
    {
        return match($type) {
            'rental_request' => 'New Rental Request',
            'payment_received' => 'Payment Received',
            'review_received' => 'New Review',
            'rental_reminder' => 'Upcoming Rental Reminder',
            default => 'Notification'
        };
    }
} 