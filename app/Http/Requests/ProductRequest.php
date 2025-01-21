<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Basic Information
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'brand' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0|max:999999.99',
            
            // Status and Visibility
            'is_available' => 'boolean',
            
            // Location
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',

            // Images
            'media' => 'sometimes|array|max:10',
            'media.*' => 'image|max:5120', // 5MB max per image
        ];
    }

    public function messages(): array
    {
        return [
            'size_id.required_unless' => 'A specific size must be selected unless the item is one-size-fits-all.',
            'media.*.max' => 'Each image must not exceed 5MB in size.',
            'media.max' => 'You may not upload more than 10 images per product.',
        ];
    }
} 