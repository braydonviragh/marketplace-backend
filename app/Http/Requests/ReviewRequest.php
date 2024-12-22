<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
            'rating' => 'sometimes|integer|between:1,5',
            'is_approved' => 'sometimes|boolean',
            'sort' => 'sometimes|string|in:rating,created_at,updated_at',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'rating.between' => 'Rating must be between 1 and 5',
            'per_page.max' => 'Maximum items per page is 100',
        ];
    }
} 