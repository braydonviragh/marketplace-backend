<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\WaistSize;

class WaistSizeController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new WaistSize();
        $this->resourceName = 'Waist Size';
    }

    protected function validationRules(): array
    {
        return [
            'size' => 'required|integer',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
        ];
    }
} 