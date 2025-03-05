<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Country;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryCollection;
use Illuminate\Http\JsonResponse;

class CountryController extends Controller
{
    /**
     * Display a listing of all countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        $countries = Country::all();
        
        return response()->json(new CountryCollection($countries));
    }
} 