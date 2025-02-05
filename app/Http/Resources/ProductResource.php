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
        $array = [
            'id' => $this->id,
            
            // Basic Information
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'brand' => $this->brand,
            
            // Product Details
            'is_available' => $this->is_available,
            
            // Get location from user's profile
            'location' => [
                'city' => $this->user->profile->city,
                'province' => [
                    'name' => $this->user->profile->province->name,
                    'abbreviation' => $this->user->profile->province->abbreviation,
                ],
                'postal_code' => $this->user->profile->postal_code,
                'coordinates' => $this->when(
                    $this->user->profile->latitude && $this->user->profile->longitude,
                    [
                        'latitude' => (float) $this->user->profile->latitude,
                        'longitude' => (float) $this->user->profile->longitude,
                    ]
                ),
            ],
            
            // Simplified Relationships
            'user' => new SimpleUserResource($this->whenLoaded('user')),
            'category' => new SimpleCategoryResource($this->whenLoaded('category')),
            'size' => $this->size,
            'color' => $this->color,
            'style' => $this->style,
            
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];

        return $array;
    }

    private function formatDistance(float $distance): string
    {
        if ($distance < 1) {
            return 'Less than 1 km away';
        }
        if ($distance < 10) {
            return round($distance, 1) . ' km away';
        }
        return round($distance) . ' km away';
    }
} 