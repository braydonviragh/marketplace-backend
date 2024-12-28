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
            'username' => $this->username,
            'name' => $this->name,
            'birthday' => $this->birthday?->toISOString(),
            'zip_code' => $this->zip_code,
            'profile_picture' => $this->profile_picture,
            'style_preference' => $this->style_preference,
            'preferences' => $this->preferences
        ];
    }
} 