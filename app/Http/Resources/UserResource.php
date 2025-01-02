<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            
            // Basic Info
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'is_active' => $this->is_active,
            'two_factor_enabled' => $this->two_factor_enabled,
            
            // Profile
            'profile' => new UserProfileResource($this->whenLoaded('profile')),
            
            // Related Counts
            'products_count' => $this->whenCounted('products'),
            'offers_count' => $this->whenCounted('offers'),
            
            // Timestamps
            'last_login_at' => $this->last_login_at?->toISOString(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Meta
            'can_edit' => $request->user()?->id === $this->id,
        ];
    }
} 