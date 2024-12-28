<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Size;

class SizeController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new Size();
        $this->resourceName = 'Size';
    }

    protected function validationRules(): array
    {
        return [
            'size_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255'
        ];
    }
} 