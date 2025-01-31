<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOfferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // We'll handle authorization through middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'start_date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:now'
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d H:i:s',
                'after:start_date'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'A product must be selected.',
            'product_id.exists' => 'The selected product is not valid.',
            'start_date.required' => 'The rental start date is required.',
            'start_date.date_format' => 'The start date must be in the format: YYYY-MM-DD HH:mm:ss',
            'start_date.after' => 'The start date must be in the future.',
            'end_date.required' => 'The rental end date is required.',
            'end_date.date_format' => 'The end date must be in the format: YYYY-MM-DD HH:mm:ss',
            'end_date.after' => 'The end date must be after the start date.'
        ];
    }
} 