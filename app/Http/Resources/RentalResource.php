<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\OfferResource;

class RentalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'offer' => new OfferResource($this->whenLoaded('offer')),
            'product' => new ProductResource($this->offer->product),
            'product_user_id' => $this->offer->product->user_id,
            'product_user' => new UserResource($this->offer->product->user),
            'renter_id' => $this->offer->user_id,
            'renter' => new UserResource($this->offer->user),
            'status_id' => $this->rental_status_id,
            'status' => $this->rentalStatus->slug,
            'start_date' => $this->offer->start_date->toDateTimeString(),
            'end_date' => $this->offer->end_date->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString()
        ];
    }
} 