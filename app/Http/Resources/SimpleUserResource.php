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
            'name' => $this->name ?? '',
            'username' => $this->username ?? '',
            'profile_picture' => $this->profile?->profile_picture ?? '',
            'phone_number' => $this->phone_number ?? '',
        ];
    }
} 