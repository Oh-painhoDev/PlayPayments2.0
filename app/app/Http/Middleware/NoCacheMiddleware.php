<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NoCacheMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Headers de no-cache
        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
        ];

        // Se for StreamedResponse, usar headers() diretamente
        if ($response instanceof StreamedResponse) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
            return $response;
        }

        // Para outros tipos de resposta, usar withHeaders() se disponível
        if (method_exists($response, 'withHeaders')) {
            return $response->withHeaders($headers);
        }

        // Fallback: adicionar headers manualmente
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
