<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\LetterSize;

class LetterSizeController extends BaseAttributeController
{
    public function __construct()
    {
        $this->model = new LetterSize();
        $this->resourceName = 'LetterSize';
    }

    protected function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'required|string|max:255'
        ];
    }
} 