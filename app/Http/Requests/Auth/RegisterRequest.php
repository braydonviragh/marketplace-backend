<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Account information
            'phone_number' => ['required', 'string', 'max:15'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:64'],
            'terms_accepted' => ['required', 'boolean', 'accepted'],
            
            // Profile information (optional for one-step registration)
            'username' => ['nullable', 'string', 'max:50', 'unique:user_profiles,username'],
            'name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'style_id' => ['required', 'integer', 'exists:styles,id'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'profile_picture' => ['nullable', 'image', 'max:5120'], // Max 5MB
        ];
    }

    public function messages(): array
    {
        return [
            // Account validation messages
            'phone_number.required' => 'Phone number is required.',
            'phone_number.max' => 'Phone number cannot exceed 15 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Email address must be a valid email format.',
            'email.unique' => 'Email address is already in use.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
            
            // Profile validation messages
            'username.unique' => 'Username is already taken.',
            'username.max' => 'Username cannot exceed 50 characters.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'province_id.exists' => 'Selected province does not exist',
            'country_id.exists' => 'Selected country does not exist',
            'style_id.required' => 'Style preference is required. Please select Man, Woman, or Unisex.',
            'style_id.exists' => 'Please select a valid style preference.',
            'profile_picture.image' => 'Profile picture must be an image.',
            'profile_picture.max' => 'Profile picture cannot exceed 5MB.',
            'latitude.numeric' => 'Latitude must be a valid number',
            'longitude.numeric' => 'Longitude must be a valid number',
        ];
    }
} 