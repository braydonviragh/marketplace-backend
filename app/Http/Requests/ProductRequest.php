<?php

namespace App\Http\Requests;

use App\Models\LetterSize;
use App\Models\NumberSize;
use App\Models\WaistSize;
use App\Models\ShoeSize;
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
        $needsSize = $this->category_id ? !$this->shouldHaveNoSize((int)$this->category_id) : false;

        $rules = [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'brand_id' => 'sometimes|required|exists:brands,id',
            'style_id' => 'sometimes|required|exists:styles,id',
            'price' => 'sometimes|required|numeric|min:0',
            'color_id' => 'sometimes|required|exists:colors,id',
            
            // Size validation - only required if category needs size
            'size_type' => [
                'nullable',
                function($attribute, $value, $fail) use ($needsSize) {
                    if ($needsSize && !in_array($value, ['letter', 'number', 'waist', 'shoe'])) {
                        $fail('The selected size type is invalid.');
                    }
                    if (!$needsSize && !is_null($value)) {
                        $fail('This category does not require a size.');
                    }
                }
            ],
            'size_id' => [
                'nullable',
                function($attribute, $value, $fail) use ($needsSize) {
                    if ($needsSize && is_null($value)) {
                        $fail('The size field is required for this category.');
                    }
                    if (!$needsSize && !is_null($value)) {
                        $fail('This category does not require a size.');
                    }
                }
            ],
            
            // Media handling - make it optional for updates
            'media' => 'sometimes|array',
            'media.*' => 'sometimes|file|mimes:jpeg,png,jpg|max:5120', // 5MB max per image
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        // Convert string "null" to actual null
        if ($this->size_type === "null") {
            $this->merge(['size_type' => null]);
        }
        if ($this->size_id === "null") {
            $this->merge(['size_id' => null]);
        }
        if ($this->style_id === "undefined") {
            $this->merge(['style_id' => null]);
        }

        // If category doesn't need size, clear size fields
        if ($this->category_id && $this->shouldHaveNoSize((int)$this->category_id)) {
            $this->merge([
                'size_type' => null,
                'size_id' => null,
                'sizeable_type' => null,
                'sizeable_id' => null
            ]);
            return;
        }

        // Map size_type and size_id to sizeable relationship
        if ($this->size_type && $this->size_id) {
            $sizeableType = match ($this->size_type) {
                'letter' => LetterSize::class,
                'number' => NumberSize::class,
                'waist' => WaistSize::class,
                'shoe' => ShoeSize::class,
                default => null
            };

            if ($sizeableType) {
                $this->merge([
                    'sizeable_type' => $sizeableType,
                    'sizeable_id' => $this->size_id
                ]);
            }
        }
    }

    private function shouldHaveNoSize(int $categoryId): bool
    {
        $noSizeCategories = [
            'accessories',
            'handbags',
            'jewelry',
            'bags',
            'other'
        ];

        $category = \App\Models\Category::find($categoryId);
        return $category && in_array(strtolower($category->slug), $noSizeCategories);
    }

    public function messages(): array
    {
        return [
            'size_type.required_if' => 'Size type is required for this category',
            'size_id.required_if' => 'Size is required for this category',
            'media.*.mimes' => 'Images must be in jpeg, png, or jpg format',
            'media.*.max' => 'Each image may not be greater than 5MB'
        ];
    }
} 