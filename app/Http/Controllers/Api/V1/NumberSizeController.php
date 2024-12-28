<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\NumberSize;

class NumberSizeController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new NumberSize();
        $this->resourceName = 'Number Size';
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ];
    }
} 