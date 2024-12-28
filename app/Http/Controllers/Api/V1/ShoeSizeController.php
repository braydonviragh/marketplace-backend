<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ShoeSize;

class ShoeSizeController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new ShoeSize();
        $this->resourceName = 'Shoe Size';
    }

    protected function validationRules(): array
    {
        return [
            'size' => 'required|numeric',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ];
    }
} 