<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Define allowed origins
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:5173',
            'http://127.0.0.1:3000',
            'http://127.0.0.1:5173',
            'https://go-score-fe.vercel.app',
            'https://go-score-oqb7qsi94-naalayas-projects.vercel.app'
        ];

        $origin = $request->headers->get('Origin');

        // Check if origin is allowed or if it matches Vercel pattern
        $isAllowed = in_array($origin, $allowedOrigins);
        if (!$isAllowed && $origin) {
            // Check if it's a Vercel deployment
            if (preg_match('/^https:\/\/.*\.vercel\.app$/', $origin) ||
                preg_match('/^https:\/\/go-score-.*\.vercel\.app$/', $origin)) {
                $isAllowed = true;
            }
        }

        // Handle preflight requests
        if ($request->getMethod() === 'OPTIONS') {
            $response = response('', 204);
        } else {
            $response = $next($request);
        }

        // Add CORS headers if origin is allowed
        if ($isAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'false');
            $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
        }

        return $response;
    }
}
