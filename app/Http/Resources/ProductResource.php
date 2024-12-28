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
            
            // Simplified Relationships
            'user' => new SimpleUserResource($this->whenLoaded('user')),
            'category' => new SimpleCategoryResource($this->whenLoaded('category')),
            
            // Meta
            'can_edit' => $request->user()?->id === $this->user_id,
        ];
    }
} 