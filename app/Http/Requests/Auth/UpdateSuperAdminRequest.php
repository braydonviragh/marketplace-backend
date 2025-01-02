<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuperAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->role === 'super_admin';
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['sometimes', 'string', 'unique:users,phone_number,' . $this->superAdmin->id, 'regex:/^\+1[0-9]{10}$/'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $this->superAdmin->id],
            'password' => ['sometimes', 'string', 'min:8'],
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