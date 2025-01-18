<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:add,remove',
            'rental_id' => 'sometimes|exists:rentals,id'
        ];
    }
} 