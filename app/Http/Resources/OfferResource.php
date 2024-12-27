<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Offer Details
            'amount' => $this->amount,
            'message' => $this->message,
            'status' => $this->status,
            'valid_until' => $this->valid_until?->toISOString(),
            
            // Relationships
            'product' => new ProductResource($this->whenLoaded('product')),
            'user' => new UserResource($this->whenLoaded('user')),
            'counter_offer' => new OfferResource($this->whenLoaded('counterOffer')),
            
            // Counter Offers
            'counter_offers_count' => $this->when(
                isset($this->counter_offers_count),
                fn() => $this->counter_offers_count
            ),
            
            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Meta
            'can_edit' => $this->when(
                auth()->check(),
                fn() => auth()->id() === $this->user_id && $this->status === 'pending'
            ),
            'can_accept' => $this->when(
                auth()->check(),
                fn() => $this->product->seller_id === auth()->id() && $this->status === 'pending'
            ),
            'can_counter' => $this->when(
                auth()->check(),
                fn() => $this->product->seller_id === auth()->id() && $this->status === 'pending'
            ),
            'is_expired' => $this->when(
                $this->valid_until !== null,
                fn() => $this->valid_until->isPast()
            )
        ];
    }
} 