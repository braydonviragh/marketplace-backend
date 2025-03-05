<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Province;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProvinceCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProvinceController extends Controller
{
    /**
     * Display a listing of all provinces.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Province::query();
        
        // Filter by country_id if provided
        if ($request->has('country_id')) {
            $query->where('country_id', $request->country_id);
        }
        
        $provinces = $query->get();
        
        return response()->json(new ProvinceCollection($provinces));
    }
} 