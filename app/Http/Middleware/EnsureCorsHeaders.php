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
        $frontendOrigin = 'https://frontend-production-2dab.up.railway.app';
        
        // Debug log for troubleshooting
        Log::debug("CORS Request from origin: " . ($origin ?? 'null'));
        
        // Default allowed headers
        $allowedHeaders = 'X-Requested-With, Content-Type, Authorization, Accept, X-XSRF-TOKEN, Origin';
        
        // In Railway production environment, be more permissive with CORS
        if (env('RAILWAY_ENVIRONMENT') === 'production') {
            // Handle preflight OPTIONS requests
            if ($request->getMethod() === 'OPTIONS') {
                if (!$response instanceof Response) {
                    $response = response('', 204);
                }
                
                $response->headers->set('Access-Control-Allow-Origin', $frontendOrigin);
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
                $response->headers->set('Access-Control-Allow-Credentials', 'true');
                $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
                $response->headers->set('Vary', 'Origin');
                
                Log::debug("EnsureCorsHeaders: Set preflight response headers for OPTIONS request");
                return $response;
            }
            
            // For non-OPTIONS requests in Railway
            $response->headers->set('Access-Control-Allow-Origin', $frontendOrigin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
            $response->headers->set('Vary', 'Origin');
            
            // Log that headers were set
            Log::debug("EnsureCorsHeaders: Set response headers for Railway environment");
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
            
            // Handle preflight OPTIONS requests for all environments
            if ($request->getMethod() === 'OPTIONS') {
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', $allowedHeaders);
                $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
                
                // Log preflight response
                Log::debug("EnsureCorsHeaders: Set preflight response for non-Railway environment");
            }
        }
        
        return $response;
    }
} 