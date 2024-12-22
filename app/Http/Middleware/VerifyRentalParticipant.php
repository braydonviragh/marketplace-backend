<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Rental;

class VerifyRentalParticipant
{
    public function handle(Request $request, Closure $next)
    {
        $rental = Rental::findOrFail($request->route('id'));
        
        if (!$request->user()->isRentalParticipant($rental)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You must be a participant in this rental to perform this action',
                'code' => 'UNAUTHORIZED_ACTION'
            ], 403);
        }

        return $next($request);
    }
} 