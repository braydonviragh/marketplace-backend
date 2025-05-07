<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        // Get the allowed origins from the environment
        $allowedOrigins = env('CORS_ALLOWED_ORIGINS', '*');
        
        // Get the origin from the request
        $origin = $request->header('Origin');
        
        // If we have a specific origin and it's in our allowed list, or if we allow all origins
        if ($origin) {
            if ($allowedOrigins === '*') {
                $response->headers->set('Access-Control-Allow-Origin', '*');
            } else {
                $allowedOriginsArray = array_filter(explode(',', $allowedOrigins));
                
                if (in_array($origin, $allowedOriginsArray)) {
                    $response->headers->set('Access-Control-Allow-Origin', $origin);
                    $response->headers->set('Access-Control-Allow-Credentials', 'true');
                    $response->headers->set('Vary', 'Origin');
                }
            }
            
            // Handle preflight OPTIONS requests
            if ($request->getMethod() === 'OPTIONS') {
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
                $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
            }
        }
        
        return $response;
    }
} 