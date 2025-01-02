<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CreateSuperAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only allow existing super admins to create other super admins
        return $this->user() && $this->user()->role === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'unique:users', 'regex:/^\+1[0-9]{10}$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.regex' => 'Phone number must be in format: +1XXXXXXXXXX',
            'phone_number.unique' => 'Phone number already in use',
            'email.unique' => 'Email address already in use',
            'email.regex' => 'Email address must be in format: example@example.com',
            'password.min' => 'Password must be at least 8 characters long',
        ];
    }
} 