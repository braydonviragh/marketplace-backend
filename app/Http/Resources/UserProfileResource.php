<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Group sizes by type
        $letterSizes = collect();
        $waistSizes = collect();
        $numberSizes = collect();

        // Populate the collections
        foreach ($this->user->detailedSizes as $size) {
            if ($size->size_id) {
                $letterSizes->push([
                    'id' => $size->letterSize->id,
                    'name' => $size->letterSize->name,
                    'slug' => $size->letterSize->slug,
                ]);
            }
            if ($size->waist_size_id) {
                $waistSizes->push([
                    'id' => $size->waistSize->id,
                    'name' => $size->waistSize->name,
                    'slug' => $size->waistSize->slug,
                ]);
            }
            if ($size->number_size_id) {
                $numberSizes->push([
                    'id' => $size->numberSize->id,
                    'name' => $size->numberSize->name,
                    'slug' => $size->numberSize->slug,
                ]);
            }
        }

        $array = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'language' => $this->language,
            'style' => [
                'id' => $this->style->id,
                'name' => $this->style->name,
                'slug' => $this->style->slug,
            ],
            'sizes' => [
                'letter_sizes' => $letterSizes->unique('id')->values(),
                'waist_sizes' => $waistSizes->unique('id')->values(),
                'number_sizes' => $numberSizes->unique('id')->values(),
            ],
            'brands' => $this->user->brands->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ];
            }),
            'location' => [
                'city' => $this->city,
                'postal_code' => $this->postal_code,
                'country' => $this->country,
                'coordinates' => $this->when($this->latitude && $this->longitude, [
                    'latitude' => (float) $this->latitude,
                    'longitude' => (float) $this->longitude,
                ]),
            ],
        ];

        // Add distance if it was calculated in the query
        if (isset($this->distance_in_km)) {
            $array['location']['distance'] = [
                'value' => round($this->distance_in_km, 1),
                'unit' => 'km',
                'formatted' => $this->formatDistance($this->distance_in_km)
            ];
        }

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