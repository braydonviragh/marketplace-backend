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
            'phone_number' => ['required', 'string', 'unique:users', 'regex:/^\+1[0-9]{10}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'terms_accepted' => ['required', 'boolean', 'accepted'],
            
            // Profile information (optional for one-step registration)
            'username' => ['sometimes', 'string', 'max:255', 'unique:user_profiles'],
            'name' => ['sometimes', 'string', 'max:255'],
            'city' => ['sometimes', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'string', 'max:20'],
            'province_id' => ['sometimes', 'integer', 'exists:provinces,id'],
            'country_id' => ['sometimes', 'integer', 'exists:countries,id'],
            'profile_picture' => ['sometimes', 'file', 'image', 'max:5120'], // 5MB max
        ];
    }

    public function messages(): array
    {
        return [
            // Account validation messages
            'phone_number.regex' => 'Phone number must be in format: +1XXXXXXXXXX',
            'phone_number.unique' => 'Phone number already in use',
            'email.unique' => 'Email address already in use',
            'email.regex' => 'Email address must be in format: example@example.com',
            'password.min' => 'Password must be at least 8 characters long',
            'terms_accepted.accepted' => 'You must accept the terms of service',
            
            // Profile validation messages
            'username.unique' => 'Username is already taken',
            'username.max' => 'Username cannot be longer than 255 characters',
            'name.max' => 'Name cannot be longer than 255 characters',
            'province_id.exists' => 'Selected province does not exist',
            'country_id.exists' => 'Selected country does not exist',
            'profile_picture.max' => 'Profile picture cannot be larger than 5MB',
        ];
    }
} 