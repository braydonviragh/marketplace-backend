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
        $data = [
            'id' => $this->id,
            
            // Basic Info
            'name' => $this->profile?->name,
            'username' => $this->profile?->username,
            'profile_picture' => $this->profile?->profile_picture_url,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->relationLoaded('profile')) {
            $data['profile'] = [
                'id' => $this->profile?->id,
                'birthday' => $this->profile?->birthday,
                'postal_code' => $this->profile?->postal_code,
                'city' => $this->profile?->city,
                'country' => $this->profile?->country,
                'style' => $this->when($this->profile?->style, [
                    'id' => $this->profile?->style?->id,
                    'name' => $this->profile?->style?->name,
                    'slug' => $this->profile?->style?->slug,
                ]),
            ];

            // Add media information if available
            if ($this->profile?->relationLoaded('media')) {
                $data['profile']['media'] = $this->profile?->media->map(function($media) {
                    return [
                        'id' => $media->id,
                        'url' => $media->url,
                        'path' => $media->path,
                        'is_primary' => $media->is_primary,
                    ];
                });

                // Set the profile_picture attribute from media if available
                $primaryImage = $this->profile?->media->firstWhere('is_primary', true) ?? $this->profile?->media->first();
                if ($primaryImage) {
                    $data['profile_picture'] = $primaryImage->url;
                }
            }
        }

        if ($this->relationLoaded('detailedSizes')) {
            $data['sizes'] = [
                'letter_sizes' => $this->detailedSizes->where('letter_size_id', '!=', null)
                    ->map(fn($size) => [
                        'id' => $size->letterSize->id,
                        'name' => $size->letterSize->name,
                        'slug' => $size->letterSize->slug,
                    ])->unique('id')->values(),
                'waist_sizes' => $this->detailedSizes->where('waist_size_id', '!=', null)
                    ->map(fn($size) => [
                        'id' => $size->waistSize->id,
                        'name' => $size->waistSize->name,
                        'slug' => $size->waistSize->slug,
                    ])->unique('id')->values(),
                'number_sizes' => $this->detailedSizes->where('number_size_id', '!=', null)
                    ->map(fn($size) => [
                        'id' => $size->numberSize->id,
                        'name' => $size->numberSize->name,
                        'slug' => $size->numberSize->slug,
                    ])->unique('id')->values(),
            ];
        }

        if ($this->relationLoaded('brands')) {
            $data['brands'] = $this->brands->map(fn($brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ]);
        }

        if ($this->relationLoaded('stripeAccount')) {
            $data['stripe_account'] = $this->stripeAccount ? [
                'customer_id' => $this->stripeAccount->customer_id,
                'account_id' => $this->stripeAccount->account_id,
                'account_enabled' => $this->stripeAccount->account_enabled,
                'default_payment_method' => $this->stripeAccount->default_payment_method,
                'account_verified_at' => $this->stripeAccount->account_verified_at,
            ] : null;
        }

        return $data;
    }
} 