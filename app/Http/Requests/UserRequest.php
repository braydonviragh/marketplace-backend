<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email' => 'sometimes|required|email|unique:users,email,' . $this->user?->id,
            'phone_number' => 'sometimes|required|string|unique:users,phone_number,' . $this->user?->id,
            'password' => $this->isMethod('POST') ? 'required|min:8' : 'sometimes|min:8',
            
            // Profile data
            'username' => 'sometimes|required|string|max:255|unique:user_profiles,username,' . $this->user?->id . ',user_id',
            'name' => 'sometimes|required|string|max:255',
            'birthday' => 'sometimes|date',
            'zip_code' => 'sometimes|string|max:10',
            'profile_picture' => 'sometimes|image|max:5120', // 5MB max
            'style_preference' => 'sometimes|in:male,female,both'
        ];

        return $rules;
    }
} 