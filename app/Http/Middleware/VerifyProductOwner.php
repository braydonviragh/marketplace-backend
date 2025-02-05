<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyProductOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $product = $request->route('product');
        
        if (!$product || $product->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'You are not authorized to perform this action on this product.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
} 