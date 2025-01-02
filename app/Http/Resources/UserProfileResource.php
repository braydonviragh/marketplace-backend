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

        return [
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
        ];
    }
} 