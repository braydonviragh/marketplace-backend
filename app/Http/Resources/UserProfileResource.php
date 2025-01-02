<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'birthday' => $this->birthday,
            'postal_code' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country,
            'profile_picture' => $this->profile_picture,
            'style_preference' => $this->style_preference,
            'preferences' => $this->preferences
        ];
    }
} 