<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'rental_from' => 'required|date|after:now',
            'rental_to' => 'required|date|after:rental_from',
            'payment_data' => 'required|array',
            'payment_data.method' => 'required|string|in:credit_card,apple_pay,paypal',
            'payment_data.token' => 'required|string'
        ];
    }
} 