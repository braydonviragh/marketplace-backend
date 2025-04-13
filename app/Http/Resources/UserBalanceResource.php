<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserBalanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'available_balance' => (float) $this->available_balance,
            'pending_balance' => (float) $this->pending_balance,
            'total_balance' => (float) $this->available_balance + (float) $this->pending_balance,
            'total_earned' => (float) $this->total_earned,
            'total_withdrawn' => (float) $this->total_withdrawn,
            'withdrawal_available' => (float) $this->available_balance > 0,
            'min_withdrawal' => (float) config('app.minimum_withdrawal', 1.00),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
} 