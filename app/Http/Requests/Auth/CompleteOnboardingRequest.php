<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'profile_picture' => ['required', 'image', 'max:2048'], // 2MB max
            'username' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('user_profiles')->where(function ($query) {
                    return $query->where('user_id', '!=', $this->user_id);
                })
            ],
            'name' => ['required', 'string', 'max:255'],
            'birthday' => ['required', 'date', 'before:today'],
            'postal_code' => ['required', 'string', 'regex:/^[A-Z]\d[A-Z] \d[A-Z]\d$/'],
            'city' => ['required', 'string', 'in:Toronto,Ottawa,Mississauga,Hamilton'],
            'style_id' => ['required', 'exists:styles,id'],
            'preferred_brands' => ['required', 'array', 'min:1'],
            'preferred_brands.*' => ['exists:brands,id'],
            'sizes' => ['required', 'array'],
            'sizes.letter' => ['required', 'array'],
            'sizes.letter.*' => ['exists:letter_sizes,id'],
            'sizes.number' => ['array'],
            'sizes.number.*' => ['exists:number_sizes,id'],
            'sizes.waist' => ['array'],
            'sizes.waist.*' => ['exists:waist_sizes,id'],
        ];
    }

    protected function prepareForValidation()
    {
        // Ensure the authenticated user can only create/update their own profile
        if ($this->user() && $this->user_id != $this->user()->id) {
            abort(403, 'You can only update your own profile');
        }
    }
} 