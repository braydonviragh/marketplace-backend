<?php

namespace App\Http\Requests;

use App\Models\LetterSize;
use App\Models\NumberSize;
use App\Models\WaistSize;
use App\Models\ShoeSize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
{
    public function rules(): array
    {
        $needsSize = !$this->shouldHaveNoSize($this->category_id);
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'style_id' => 'required|exists:styles,id',
            'price' => 'required|numeric|min:0',
            'color_id' => 'required|exists:colors,id',
            
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
            
            // Media validation
            'media' => [
                'required',
                'array',
                'min:1',
                function($attribute, $value, $fail) use ($isUpdate) {
                    $hasValidMedia = false;
                    
                    foreach ($value as $media) {
                        // Valid if it has an ID (existing) or a file (new upload)
                        if (!empty($media['id']) || !empty($media['file'])) {
                            $hasValidMedia = true;
                            break;
                        }
                    }
                    
                    if (!$hasValidMedia) {
                        $fail('At least one valid media item (existing or new) is required.');
                    }
                }
            ],
            'media.*.id' => 'nullable|exists:media,id',
            'media.*.order' => 'required|integer|min:1',
            'media.*.file' => 'nullable|file|mimes:jpeg,png,jpg|max:10240',
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        \Log::info('Request data before preparation:', [
            'media' => $this->media,
            'files' => $this->allFiles(),
        ]);

        // Handle media data
        if ($this->has('media')) {
            $mediaData = is_string($this->media) ? json_decode($this->media, true) : $this->media;
            
            if (is_array($mediaData)) {
                foreach ($mediaData as $index => &$media) {
                    // Ensure order is set
                    if (!isset($media['order'])) {
                        $media['order'] = $index + 1;
                    }
                }
                
                $this->merge(['media' => $mediaData]);
            }
        }

        // Convert string "null" to actual null
        if ($this->size_type === "null") {
            $this->merge(['size_type' => null]);
        }
        if ($this->size_id === "null") {
            $this->merge(['size_id' => null]);
        }

        // If category doesn't need size, clear size fields
        if ($this->category_id && $this->shouldHaveNoSize($this->category_id)) {
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

    private function shouldHaveNoSize($categoryId): bool
    {
        if (empty($categoryId)) {
            return false;
        }

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
            'media.required' => 'At least one image is required',
            'media.*.mimes' => 'Images must be in jpeg, png, or jpg format',
            'media.*.max' => 'Each image may not be greater than 10MB'
        ];
    }
} 