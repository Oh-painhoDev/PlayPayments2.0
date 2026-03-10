<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class TokenAuthMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is already authenticated
        if (Auth::check()) {
            return $next($request);
        }
        
        // Check for auth token in query parameter or header
        $token = $request->query('auth_token') ?? $request->header('X-Auth-Token');
        
        if ($token) {
            try {
                // Verify token and authenticate user
                $decoded = base64_decode($token);
                $parts = explode(':', $decoded);
                
                Log::info('Token auth attempt', [
                    'token' => substr($token, 0, 10) . '...',
                    'decoded' => $decoded,
                    'parts_count' => count($parts),
                ]);
                
                if (count($parts) === 2) {
                    $userId = $parts[0];
                    $timestamp = $parts[1];
                    $timeDiff = time() - $timestamp;
                    
                    Log::info('Token validation', [
                        'user_id' => $userId,
                        'timestamp' => $timestamp,
                        'time_diff' => $timeDiff,
                        'is_valid' => abs($timeDiff) < 3600
                    ]);
                    
                    // Token valid for 1 hour (check both directions for clock skew)
                    if (abs($timeDiff) < 3600) {
                        $user = User::find($userId);
                        
                        if ($user) {
                            Auth::login($user);
                            Log::info('User authenticated via token', [
                                'user_id' => $user->id,
                                'user_email' => $user->email
                            ]);
                        } else {
                            Log::warning('User not found for token', ['user_id' => $userId]);
                        }
                    } else {
                        Log::warning('Token expired', ['time_diff' => $timeDiff]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Token auth error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        return $next($request);
    }
}
