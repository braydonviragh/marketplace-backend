<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'style_id' => 'required|exists:styles,id',
            'price' => 'required|numeric|min:0',
            'color_id' => 'required|exists:colors,id',
            
            // Size validation
            'size_type' => ['required', Rule::in(['letter', 'number', 'waist', 'shoe', 'none'])],
            
            // Size IDs - only one should be provided based on size_type
            'letter_size_id' => [
                'required_if:size_type,letter',
                'prohibited_unless:size_type,letter',
                Rule::exists('letter_sizes', 'id'),
            ],
            'number_size_id' => [
                'required_if:size_type,number',
                'prohibited_unless:size_type,number',
                Rule::exists('number_sizes', 'id'),
            ],
            'waist_size_id' => [
                'required_if:size_type,waist',
                'prohibited_unless:size_type,waist',
                Rule::exists('waist_sizes', 'id'),
            ],
            'shoe_size_id' => [
                'required_if:size_type,shoe',
                'prohibited_unless:size_type,shoe',
                Rule::exists('shoe_sizes', 'id'),
            ],
            
            // Location
            'city' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
            
            // Images
            'media' => 'required|array|min:1',
            'media.*.image' => 'required|string',
            'media.*.order' => 'required|integer|min:1'
        ];
    }

    protected function prepareForValidation()
    {
        // If category requires no size, force size_type to 'none'
        if ($this->category_id && $this->shouldHaveNoSize($this->category_id)) {
            $this->merge([
                'size_type' => 'none',
                'letter_size_id' => null,
                'number_size_id' => null,
                'waist_size_id' => null,
                'shoe_size_id' => null
            ]);
        }
    }

    private function shouldHaveNoSize(int $categoryId): bool
    {
        $noSizeCategories = [
            'accessories',
            'handbags',
            'jewelry'
        ];

        $category = \App\Models\Category::find($categoryId);
        return in_array($category->slug, $noSizeCategories);
    }

    public function messages(): array
    {
        return [
            'letter_size_id.prohibited_unless' => 'Letter size can only be set when size type is letter',
            'number_size_id.prohibited_unless' => 'Number size can only be set when size type is number',
            'waist_size_id.prohibited_unless' => 'Waist size can only be set when size type is waist',
            'shoe_size_id.prohibited_unless' => 'Shoe size can only be set when size type is shoe',
            'letter_size_id.required_if' => 'Letter size is required when size type is letter',
            'number_size_id.required_if' => 'Number size is required when size type is number',
            'waist_size_id.required_if' => 'Waist size is required when size type is waist',
            'shoe_size_id.required_if' => 'Shoe size is required when size type is shoe',
        ];
    }
} 