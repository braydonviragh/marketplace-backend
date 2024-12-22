<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
            'rental_id' => 'sometimes|integer|exists:rentals,id',
            'status' => 'sometimes|string|in:pending,processing,completed,failed,refunded,partially_refunded',
            'payment_method' => 'sometimes|string|in:apple_pay,paypal',
            'date_range' => 'sometimes|array',
            'date_range.*' => 'required|date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'date_range.*.date' => 'Invalid date format in date range',
            'status.in' => 'Invalid payment status provided',
            'payment_method.in' => 'Invalid payment method provided',
            'per_page.max' => 'Maximum items per page is 100',
        ];
    }
} 