<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!$response) {
            return $response;
        }

        $allowedOrigins = explode(',', env('CORS_ALLOWED_ORIGINS', '*'));
        $origin = $request->header('Origin');
        
        if ($origin && (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins))) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else if (count($allowedOrigins) === 1) {
            $response->headers->set('Access-Control-Allow-Origin', $allowedOrigins[0]);
        }
        
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '86400');

        return $response;
    }
} 