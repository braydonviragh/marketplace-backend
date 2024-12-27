<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            
            // Basic Information
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'brand' => $this->brand,
            
            // Size Information
            'size_id' => $this->size_id,
            
            // Product Details
            'specifications' => $this->specifications,
            'is_available' => $this->is_available,
            
            // Location
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            // 'images' => ProductImageResource::collection($this->whenLoaded('images')),
            
            // Related Counts
            'offers_count' => $this->whenCounted('offers'),

            
            // Timestamps
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            
            // Meta
            'can_edit' => $request->user()?->id === $this->user_id,
        ];
    }
} 