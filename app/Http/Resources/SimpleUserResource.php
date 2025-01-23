<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SimpleUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'name' => $this->profile?->name ?? '',
            'username' => $this->profile?->username ?? '',
            'profile_picture' => $this->profile?->profile_picture_url ?? '',
            'phone_number' => $this->phone_number,
            'location' => [
                'city' => $this->profile?->city ?? '',
                'province' => $this->profile?->province ? [
                    'name' => $this->profile->province->name,
                    'abbreviation' => $this->profile->province->abbreviation,
                ] : null,
                'country' => $this->profile?->country ? [
                    'name' => $this->profile->country->name, 
                    'abbreviation' => $this->profile->country->abbreviation,
                ] : null,
                'postal_code' => $this->profile?->postal_code ?? '',
            ],
        ];
    }
} 