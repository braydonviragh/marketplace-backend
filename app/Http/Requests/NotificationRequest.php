<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|integer|exists:users,id',
            'type' => 'sometimes|string|in:rental_request,payment_received,review_received,rental_reminder',
            'status' => 'sometimes|string|in:pending,sent,failed,read',
            'unread' => 'sometimes|boolean',
            'date_range' => 'sometimes|array',
            'date_range.*' => 'required|date',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Invalid notification type provided',
            'status.in' => 'Invalid notification status provided',
            'date_range.*.date' => 'Invalid date format in date range',
            'per_page.max' => 'Maximum items per page is 100',
        ];
    }
} 