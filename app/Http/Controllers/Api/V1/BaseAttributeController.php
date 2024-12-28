<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class BaseAttributeController extends Controller
{
    protected Model $model;
    protected string $resourceName;

    public function index(): JsonResponse
    {
        $items = $this->model::all();
        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->validationRules());
        $item = $this->model::create($validated);
        
        return response()->json([
            'message' => "{$this->resourceName} created successfully",
            'data' => $item
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->model::findOrFail($id);
        return response()->json(['data' => $item]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $item = $this->model::findOrFail($id);
        $validated = $request->validate($this->validationRules());
        $item->update($validated);
        
        return response()->json([
            'message' => "{$this->resourceName} updated successfully",
            'data' => $item
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = $this->model::findOrFail($id);
        $item->delete();
        
        return response()->json([
            'message' => "{$this->resourceName} deleted successfully"
        ]);
    }

    abstract protected function validationRules(): array;
} 