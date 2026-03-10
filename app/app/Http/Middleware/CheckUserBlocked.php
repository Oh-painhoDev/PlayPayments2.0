<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckUserBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            $isBlocked = Cache::remember("user_blocked_{$user->id}", 300, function () use ($user) {
                return $user->isBlocked();
            });
            
            if ($isBlocked) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Sua conta está bloqueada. Entre em contato com o suporte para mais informações.'
                    ], 403);
                }
                
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Sua conta está bloqueada. Entre em contato com o suporte para mais informações.');
            }
        }

        return $next($request);
    }
}
