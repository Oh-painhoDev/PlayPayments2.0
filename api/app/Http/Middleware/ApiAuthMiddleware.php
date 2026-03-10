<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Extrair token do header Authorization
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'message' => 'Não autorizado.'
            ], 401);
        }
        
        $apiKey = substr($authHeader, 7); // Remove "Bearer "
        
        if (empty($apiKey)) {
            return response()->json([
                'message' => 'Não autorizado.'
            ], 401);
        }
        
        // Verificar se é SECRET KEY (sk_... ou SK-playpayments-...) ou PUBLIC KEY (pk_... ou PB-playpayments-...)
        $isSecretKey = str_starts_with($apiKey, 'sk_') || str_starts_with($apiKey, 'SK-playpayments-');
        $isPublicKey = str_starts_with($apiKey, 'pk_') || str_starts_with($apiKey, 'PB-playpayments-');
        $httpMethod = strtoupper($request->method());
        $isReadOnly = in_array($httpMethod, ['GET', 'HEAD', 'OPTIONS']);
        
        // Validação: SECRET KEY pode fazer tudo, PUBLIC KEY só pode fazer GET
        if ($isPublicKey && !$isReadOnly) {
            return response()->json([
                'success' => false,
                'error' => 'Não autorizado.',
                'message' => 'Public Key (pk_... ou PB-playpayments-...) só pode ser usada para consultas (GET). Use Secret Key (sk_... ou SK-playpayments-...) para criar/modificar pagamentos.'
            ], 403);
        }
        
        // Buscar usuário pela chave apropriada
        $cacheKey = 'api_user_' . md5($apiKey);
        $user = Cache::remember($cacheKey, 300, function () use ($apiKey, $isSecretKey, $isPublicKey) {
            if ($isSecretKey) {
                return User::where('api_secret', $apiKey)->first();
            } elseif ($isPublicKey) {
                return User::where('api_public_key', $apiKey)->first();
            }
            return null;
        });
        
        if (!$user) {
            Log::warning('Tentativa de acesso com API Key inválida', [
                'api_key_prefix' => substr($apiKey, 0, 10) . '...',
                'key_type' => $isSecretKey ? 'SECRET' : ($isPublicKey ? 'PUBLIC' : 'UNKNOWN'),
                'method' => $httpMethod,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'message' => 'Não autorizado.'
            ], 401);
        }
        
        // Verificar se o usuário está bloqueado
        if ($user->isBlocked()) {
            Log::warning('Tentativa de acesso API com usuário bloqueado', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Não autorizado.',
                'message' => 'Conta bloqueada. Entre em contato com o suporte para mais informações.'
            ], 403);
        }
        
        // Verificar se o usuário está ativo
        if (!in_array($user->role, ['user', 'admin', 'gerente'])) {
            return response()->json([
                'success' => false,
                'error' => 'Não autorizado.',
                'message' => 'Usuário não autorizado'
            ], 403);
        }
        
        // OTIMIZADO: Removido update() que causava query lenta (200ms) a cada request
        // Se necessário rastrear último uso, fazer isso assincronamente ou em job
        
        // Autenticar o usuário para a requisição
        Auth::login($user);
        
        Log::info('Acesso autorizado via API Key', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'key_type' => $isSecretKey ? 'SECRET' : 'PUBLIC',
            'method' => $httpMethod,
            'endpoint' => $request->path()
        ]);
        
        return $next($request);
    }
}
