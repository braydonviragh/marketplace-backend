<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return match ($this->method()) {
            'POST' => $this->createRules(),
            'PUT', 'PATCH' => $this->updateRules(),
            'GET' => $this->indexRules(),
            default => []
        };
    }

    protected function createRules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:500'],
            'valid_until' => ['nullable', 'date', 'after:now'],
            'counter_offer_id' => ['nullable', 'exists:offers,id']
        ];
    }

    protected function updateRules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'message' => ['nullable', 'string', 'max:500'],
            'valid_until' => ['nullable', 'date', 'after:now'],
            'status' => ['sometimes', Rule::in(['pending', 'accepted', 'rejected', 'expired', 'withdrawn'])]
        ];
    }

    protected function indexRules(): array
    {
        return [
            'product_id' => ['sometimes', 'exists:products,id'],
            'user_id' => ['sometimes', 'exists:users,id'],
            'status' => ['sometimes', Rule::in(['pending', 'accepted', 'rejected', 'expired', 'withdrawn'])],
            'min_amount' => ['sometimes', 'numeric', 'min:0'],
            'max_amount' => ['sometimes', 'numeric', 'gt:min_amount'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after:date_from'],
            'sort_by' => ['sometimes', Rule::in(['amount', 'created_at', 'valid_until'])],
            'sort_direction' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100']
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please specify which product you are making an offer for.',
            'product_id.exists' => 'The selected product does not exist.',
            'amount.required' => 'Please specify the offer amount.',
            'amount.numeric' => 'The offer amount must be a number.',
            'amount.min' => 'The offer amount cannot be negative.',
            'message.max' => 'The message cannot exceed 500 characters.',
            'valid_until.after' => 'The offer expiration date must be in the future.',
            'counter_offer_id.exists' => 'The referenced counter offer does not exist.'
        ];
    }
} 