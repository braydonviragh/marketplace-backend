<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyUserAccess
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->route('id');
        if (!$request->user()->canAccessUser($userId)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to access this user\'s data',
                'code' => 'UNAUTHORIZED_ACCESS'
            ], 403);
        }

        return $next($request);
    }
} 