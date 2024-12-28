<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Brand;

class BrandController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new Brand();
        $this->resourceName = 'Brand';
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:brands,name,' . request()->route('id'),
            'description' => 'nullable|string'
        ];
    }
} 