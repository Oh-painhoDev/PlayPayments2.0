<?php

/**
 * Health Check Routes
 * Lightweight endpoints for deployment health checks
 */

use Illuminate\Support\Facades\Route;

// Simple health check - no database, no auth, just returns 200 OK
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'brpix',
        'timestamp' => now()->toIso8601String(),
    ], 200);
})->name('health.check');

// Detailed health check (optional, for monitoring)
Route::get('/health/detailed', function () {
    $health = [
        'status' => 'ok',
        'service' => 'brpix',
        'timestamp' => now()->toIso8601String(),
        'checks' => []
    ];
    
    // Check database connection (with timeout)
    try {
        \DB::connection()->getPdo();
        $health['checks']['database'] = 'ok';
    } catch (\Exception $e) {
        $health['checks']['database'] = 'error';
        $health['status'] = 'degraded';
    }
    
    // Check cache
    try {
        \Cache::put('health_check', true, 1);
        $health['checks']['cache'] = \Cache::get('health_check') ? 'ok' : 'error';
    } catch (\Exception $e) {
        $health['checks']['cache'] = 'error';
    }
    
    return response()->json($health, $health['status'] === 'ok' ? 200 : 503);
})->name('health.detailed');

// Simple ping endpoint
Route::get('/ping', function () {
    return response('pong', 200)
        ->header('Content-Type', 'text/plain');
})->name('ping');
