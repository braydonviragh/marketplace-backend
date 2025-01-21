<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'image' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg',
                'max:10240', // 10MB max
                'dimensions:min_width=200,min_height=200,max_width=4000,max_height=4000',
            ],
            'order' => 'sometimes|integer|min:0',
            'is_primary' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'image.dimensions' => 'Image must be between 200x200 and 4000x4000 pixels.',
            'image.max' => 'Image size must not exceed 10MB.',
        ];
    }
} 