<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        $path = $request->path();
        
        if (preg_match('/\.(jpg|jpeg|png|gif|webp|svg|ico)$/i', $path)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }
        
        elseif (preg_match('/\.(css|js)$/i', $path) || str_contains($path, '/build/assets/')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Expires', gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        }
        
        elseif (preg_match('/\.(woff|woff2|ttf|eot|otf)$/i', $path)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Access-Control-Allow-Origin', '*');
        }
        
        return $response;
    }
}
