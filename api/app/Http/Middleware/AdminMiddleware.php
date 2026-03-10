<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!Auth::check() || !in_array($user->role, ['admin', 'gerente'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acesso negado'], 403);
            }
            
            return redirect()->route('dashboard')->with('error', 'Acesso negado. Apenas administradores e gerentes podem acessar esta área.');
        }

        return $next($request);
    }
}