<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemLogsController extends Controller
{
    public function index(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        $errors = [];
        $apiStats = $this->getApiStats();
        $ddosAlerts = $this->checkDDoSThreats();
        
        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = array_slice(explode("\n", $content), -500); // Last 500 lines
            
            foreach ($lines as $line) {
                if (empty($line)) continue;
                
                $logEntry = [
                    'timestamp' => $this->extractTimestamp($line),
                    'level' => $this->extractLevel($line),
                    'message' => $line,
                    'is_error' => $this->isError($line)
                ];
                
                $logs[] = $logEntry;
                
                if ($logEntry['is_error']) {
                    $errors[] = $logEntry;
                }
            }
        }
        
        $logs = array_reverse($logs);
        $errors = array_slice(array_reverse($errors), 0, 50);
        
        return view('admin.system-logs.index', compact('logs', 'errors', 'apiStats', 'ddosAlerts'));
    }
    
    public function getLiveLogs(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $lastCheck = $request->input('last_check', Carbon::now()->subSeconds(5)->timestamp);
        $logs = [];
        
        if (File::exists($logFile)) {
            // Read more lines to ensure we don't miss anything during high traffic
            $content = File::get($logFile);
            $lines = explode("\n", $content);
            
            foreach (array_slice($lines, -200) as $line) {
                if (empty($line) || strlen($line) < 10) continue;
                
                $timestamp = $this->extractTimestamp($line);
                
                if ($timestamp) {
                    $logTime = Carbon::parse($timestamp)->timestamp;
                    if ($logTime > $lastCheck) {
                        $logs[] = [
                            'timestamp' => Carbon::parse($timestamp)->format('H:i:s'),
                            'level' => $this->extractLevel($line),
                            'message' => $line,
                            'is_error' => $this->isError($line)
                        ];
                    }
                }
            }
        }
        
        // Also return fresh stats for the UI
        $stats = $this->getApiStats();
        
        return response()->json([
            'logs' => $logs,
            'stats' => [
                'current_minute' => $stats['current_minute'],
                'current_hour' => $stats['current_hour'],
                'current_day' => $stats['current_day'],
                'usage' => $stats['usage'],
                'hourly_breakdown' => $stats['hourly_breakdown']
            ],
            'timestamp' => time()
        ]);
    }
    
    public function getApiStats()
    {
        $now = Carbon::now();
        $stats = [];
        
        // Get API rate limits from cache (per minute, hour, day)
        $minuteKey = 'api_requests_minute_' . $now->format('Y-m-d_H:i');
        $hourKey = 'api_requests_hour_' . $now->format('Y-m-d_H');
        $dayKey = 'api_requests_day_' . $now->format('Y-m-d');
        
        $stats['current_minute'] = Cache::get($minuteKey, 0);
        $stats['current_hour'] = Cache::get($hourKey, 0);
        $stats['current_day'] = Cache::get($dayKey, 0);
        
        // Define rate limits
        $stats['limits'] = [
            'minute' => 1000,
            'hour' => 50000,
            'day' => 1000000
        ];
        
        // Calculate percentages
        $stats['usage'] = [
            'minute' => ($stats['current_minute'] / $stats['limits']['minute']) * 100,
            'hour' => ($stats['current_hour'] / $stats['limits']['hour']) * 100,
            'day' => ($stats['current_day'] / $stats['limits']['day']) * 100,
        ];
        
        // Get last hour stats (minute by minute)
        $hourlyStats = [];
        for ($i = 59; $i >= 0; $i--) {
            $time = $now->copy()->subMinutes($i);
            $key = 'api_requests_minute_' . $time->format('Y-m-d_H:i');
            $hourlyStats[] = [
                'time' => $time->format('H:i'),
                'count' => Cache::get($key, 0)
            ];
        }
        $stats['hourly_breakdown'] = $hourlyStats;
        
        return $stats;
    }
    
    public function checkDDoSThreats()
    {
        $alerts = [];
        $now = Carbon::now();
        
        // Check for unusual traffic patterns
        $currentMinute = Cache::get('api_requests_minute_' . $now->format('Y-m-d_H:i'), 0);
        
        if ($currentMinute > 500) {
            $alerts[] = [
                'level' => 'critical',
                'message' => "Taxa de requisições muito alta: {$currentMinute} req/min",
                'timestamp' => $now->toDateTimeString()
            ];
        } elseif ($currentMinute > 300) {
            $alerts[] = [
                'level' => 'warning',
                'message' => "Taxa de requisições elevada: {$currentMinute} req/min",
                'timestamp' => $now->toDateTimeString()
            ];
        }
        
        // Check for multiple failed requests from same IP
        $failedRequests = Cache::get('failed_requests_ips', []);
        foreach ($failedRequests as $ip => $count) {
            if ($count > 50) {
                $alerts[] = [
                    'level' => 'critical',
                    'message' => "IP suspeito com {$count} requisições falhas: {$ip}",
                    'timestamp' => $now->toDateTimeString()
                ];
            }
        }
        
        // Check database connection issues
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $alerts[] = [
                'level' => 'critical',
                'message' => 'Erro de conexão com banco de dados',
                'timestamp' => $now->toDateTimeString()
            ];
        }
        
        return $alerts;
    }
    
    public function clearLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }
        
        return redirect()->back()->with('success', 'Logs limpos com sucesso!');
    }
    
    private function extractTimestamp($line)
    {
        if (preg_match('/\[(.*?)\]/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    private function extractLevel($line)
    {
        if (preg_match('/\]\.(\w+):/', $line, $matches)) {
            return strtoupper($matches[1]);
        }
        
        if (stripos($line, 'error') !== false) return 'ERROR';
        if (stripos($line, 'warning') !== false) return 'WARNING';
        if (stripos($line, 'info') !== false) return 'INFO';
        if (stripos($line, 'debug') !== false) return 'DEBUG';
        
        return 'INFO';
    }
    
    private function isError($line)
    {
        $level = $this->extractLevel($line);
        return in_array($level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY']);
    }
}
