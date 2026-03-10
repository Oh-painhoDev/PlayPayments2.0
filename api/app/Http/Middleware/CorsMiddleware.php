<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getMethod() === 'OPTIONS') {
            return response()->noContent(200)->withHeaders($this->headers($request));
        }

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        foreach ($this->headers($request) as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

    /**
     * Default CORS headers
     */
    private function headers(Request $request): array
    {
        $origin = $request->headers->get('Origin', '*');

        return [
            'Access-Control-Allow-Origin' => $origin ?: '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, X-Public-Key, X-Private-Key',
            'Access-Control-Max-Age' => '86400',
        ];
    }
}


