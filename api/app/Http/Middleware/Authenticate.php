<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            Log::info('User authenticated via custom middleware', [
                'user_id' => Auth::id(),
                'session_id' => $request->session()->getId(),
                'path' => $request->path()
            ]);
            return $next($request);
        }

        Log::warning('User not authenticated - redirecting to login', [
            'path' => $request->path(),
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession(),
            'session_started' => $request->session()->isStarted(),
            'cookies' => $request->cookies->all()
        ]);

        return redirect()->guest(route('login'));
    }
}
