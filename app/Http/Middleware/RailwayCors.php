<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RailwayCors
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
        // Only apply in Railway environment
        if (env('RAILWAY_ENVIRONMENT') !== 'production') {
            return $next($request);
        }
        
        // Log incoming request for debugging
        Log::debug("RailwayCors middleware processing request from: " . $request->header('Origin'));
        
        // If this is a preflight OPTIONS request, respond immediately
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
            Log::debug("RailwayCors: Handling OPTIONS preflight");
        } else {
            // For normal requests, continue through middleware stack
            $response = $next($request);
            Log::debug("RailwayCors: Processing normal request");
        }
        
        // Add CORS headers to the response
        $frontendOrigin = 'https://frontend-production-2dab.up.railway.app';
        $requestOrigin = $request->header('Origin');
        
        // If there's an origin header, set it as the allowed origin 
        if ($requestOrigin) {
            $response->header('Access-Control-Allow-Origin', $requestOrigin);
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization, X-Requested-With, Accept, X-XSRF-TOKEN');
            $response->header('Access-Control-Expose-Headers', 'Content-Disposition');
            $response->header('Vary', 'Origin');
            Log::debug("RailwayCors: Added headers for origin: $requestOrigin");
        }
        
        return $response;
    }
} 