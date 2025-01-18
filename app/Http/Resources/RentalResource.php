<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;

class RentalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'product' => $this->product,
            'product_user_id' => $this->product->user_id,
            'renter_id' => $this->user_id,
            'status_id' => $this->rental_status_id,
            'status' => $this->rentalStatus->slug,
            'start_date' => $this->start_date->toDateTimeString(),
            'end_date' => $this->end_date->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString()
        ];
    }
} 