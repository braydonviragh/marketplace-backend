<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        // Process the request
        $response = $next($request);
        
        // Get the origin from the request
        $origin = $request->header('Origin');
        
        // Debug log for troubleshooting
        Log::debug("CORS Request from origin: " . ($origin ?? 'null'));
        
        // In Railway production environment, be more permissive with CORS
        if (env('RAILWAY_ENVIRONMENT') === 'production') {
            if ($origin) {
                // Allow the frontend domain
                $frontendDomain = 'https://frontend-production-2dab.up.railway.app';
                
                // For debugging: log allowed domain and actual origin
                Log::debug("Comparing origin: $origin with allowed domain: $frontendDomain");
                
                // Set CORS headers for Railway
                $response->headers->set('Access-Control-Allow-Origin', $origin);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization, X-XSRF-TOKEN');
                $response->headers->set('Vary', 'Origin');
                
                // Log that headers were set
                Log::debug("CORS headers set for origin: $origin");
            }
        } else {
            // Original implementation for non-Railway environments
            $allowedOrigins = env('CORS_ALLOWED_ORIGINS', '*');
            
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
            }
        }
        
        // Handle preflight OPTIONS requests for all environments
        if ($request->getMethod() === 'OPTIONS') {
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization, X-XSRF-TOKEN');
            $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
            
            // Log preflight response
            Log::debug("OPTIONS preflight response sent");
        }
        
        return $response;
    }
} 