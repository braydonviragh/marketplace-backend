<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaResource;

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
            
            // Product Details
            'is_available' => $this->is_available,
            
            // Location
            'city' => $this->city,
            'province' => $this->province,
            'postal_code' => $this->postal_code,
            
            // Simplified Relationships
            'user' => new SimpleUserResource($this->whenLoaded('user')),
            'category' => new SimpleCategoryResource($this->whenLoaded('category')),
            'size' => $this->size,
            'color' => $this->color,
            
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
} 