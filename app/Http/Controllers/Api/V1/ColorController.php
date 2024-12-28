<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Color;

class ColorController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new Color();
        $this->resourceName = 'Color';
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:colors,name,' . request()->route('id'),
            'hex_code' => 'required|string|size:6',
            'slug' => 'required|string|max:255|unique:colors,slug,' . request()->route('id')
        ];
    }
} 