<?php

namespace App\Traits;

trait WithDynamicRelations
{
    protected function loadRequestedRelations($query)
    {
        $allowedRelations = [
            'profile',
            'detailedSizes',
            'detailedSizes.letterSize',
            'detailedSizes.waistSize',
            'detailedSizes.numberSize',
            'brands'
        ];

        $with = array_filter(
            explode(',', request()->input('with', '')),
            fn($relation) => in_array($relation, $allowedRelations)
        );

        if (!empty($with)) {
            $query->with($with);
        }

        return $query;
    }
} 