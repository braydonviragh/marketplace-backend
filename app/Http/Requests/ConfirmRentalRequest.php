<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class ConfirmRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $rental = $this->route('rental');
        $userId = Auth::id();

        // Check if user is either the renter or the product owner
        return $userId === $rental->user_id || 
               $userId === $rental->product->user_id;
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization()
    {
        throw ValidationException::withMessages([
            'rental' => ['You are not authorized to confirm this rental.']
        ]);
    }
} 