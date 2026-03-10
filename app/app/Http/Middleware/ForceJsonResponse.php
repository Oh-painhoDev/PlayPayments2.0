<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     * 
     * Força todas as respostas de API a retornarem JSON
     */
    public function handle(Request $request, Closure $next)
    {
        // Forçar Accept: application/json para rotas de API
        if ($request->is('api/*')) {
            $request->headers->set('Accept', 'application/json');
        }
        
        $response = $next($request);
        
        // Garantir header Content-Type para rotas de API
        if ($request->is('api/*')) {
            $response->headers->set('Content-Type', 'application/json; charset=utf-8');
        }
        
        return $response;
    }
}

