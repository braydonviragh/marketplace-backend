<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $rental = Rental::inRandomOrder()->first();
        $paymentMethods = ['stripe', 'paypal', 'apple_pay'];
        
        return [
            'rental_id' => $rental->id,
            'payer_id' => $rental->renter_id,
            'payee_id' => $rental->owner_id,
            'payment_method' => fake()->randomElement($paymentMethods),
            'payment_id' => 'PAY-' . fake()->uuid(),
            'amount' => $rental->total_price,
            'currency' => 'CAD',
            'status' => 'completed',
            'payment_details' => [
                'transaction_id' => fake()->uuid(),
                'payment_method_details' => [
                    'type' => 'card',
                    'last4' => fake()->randomNumber(4, true),
                ]
            ]
        ];
    }
} 