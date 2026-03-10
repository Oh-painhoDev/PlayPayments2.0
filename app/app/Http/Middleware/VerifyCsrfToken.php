<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Closure;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '*',             // TEMPORARIAMENTE desabilitar CSRF para todas as rotas (corrigir iframe Replit)
    ];
    
    /**
     * Handle an incoming request - skip CSRF validation completely
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}