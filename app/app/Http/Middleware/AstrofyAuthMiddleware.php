<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AstrofyIntegration;
use Illuminate\Support\Facades\Log;

class AstrofyAuthMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Valida os headers obrigatórios:
     * - X-Gateway-Key: identifica o gateway
     * - X-Api-Key: identifica o cliente final (formato: {gateway_id}:{user_private_key})
     */
    public function handle(Request $request, Closure $next)
    {
        // Extrair headers obrigatórios
        $gatewayKey = $request->header('X-Gateway-Key');
        $apiKey = $request->header('X-Api-Key');

        // Validar presença dos headers
        if (!$gatewayKey || !$apiKey) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid authentication headers.',
            ], 401);
        }

        // Validar formato da X-Api-Key conforme regex oficial
        // Formato: {gateway_id}:{user_private_key}
        // Regex: ^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}:[A-Za-z0-9-_]{1,36}$
        $apiKeyPattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}:[A-Za-z0-9-_]{1,36}$/i';
        
        if (!preg_match($apiKeyPattern, $apiKey)) {
            Log::warning('Astrofy: X-Api-Key com formato inválido', [
                'api_key_prefix' => substr($apiKey, 0, 20) . '...',
                'gateway_key' => substr($gatewayKey, 0, 10) . '...',
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid authentication headers.',
            ], 401);
        }

        // Validar comprimento total (máximo 73 caracteres)
        if (strlen($apiKey) > 73) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid authentication headers.',
            ], 401);
        }

        // Buscar integração pela gateway_key
        $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)
            ->where('is_active', true)
            ->first();

        if (!$integration) {
            Log::warning('Astrofy: Gateway Key não encontrada ou inativa', [
                'gateway_key' => substr($gatewayKey, 0, 10) . '...',
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Missing or invalid authentication headers.',
            ], 401);
        }

        // Extrair gateway_id da X-Api-Key para validação adicional (opcional)
        $parts = explode(':', $apiKey, 2);
        $gatewayIdFromApiKey = $parts[0] ?? null;
        $userPrivateKey = $parts[1] ?? null;

        // Validar que o gateway_id da X-Api-Key corresponde ao gateway_key (opcional, mas recomendado)
        // Por enquanto, apenas validamos o formato e a existência da integração

        // Adicionar integração ao request para uso nos controllers
        $request->merge([
            'astrofy_integration' => $integration,
            'astrofy_gateway_id' => $gatewayIdFromApiKey,
            'astrofy_user_private_key' => $userPrivateKey,
        ]);

        Log::debug('Astrofy: Autenticação bem-sucedida', [
            'integration_id' => $integration->id,
            'gateway_key' => substr($gatewayKey, 0, 10) . '...',
            'endpoint' => $request->path(),
            'method' => $request->method(),
        ]);

        return $next($request);
    }
}

