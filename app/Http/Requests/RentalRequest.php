<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,confirmed,in_progress,completed,cancelled,disputed',
            'date_range' => 'sometimes|array',
            'date_range.*' => 'required|date',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'min_price' => 'sometimes|numeric|min:0',
            'max_price' => 'sometimes|numeric|gt:min_price',
            'user_id' => 'sometimes|integer|exists:users,id',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'date_range.*.date' => 'Invalid date format in date range',
            'max_price.gt' => 'Maximum price must be greater than minimum price',
            'per_page.max' => 'Maximum items per page is 100',
        ];
    }
} 