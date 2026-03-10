<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiPublicPrivateAuthMiddleware
{
    /**
     * Handle an incoming request.
     * 
     * Requer ambos os tokens: Public Key e Private Key (Secret Key)
     * Para operações de criação de PIX
     */
    public function handle(Request $request, Closure $next)
    {
        // Extrair tokens dos headers
        // Laravel normaliza headers HTTP convertendo para formato HTTP_HEADER_NAME
        // Mas o método header() do Request já faz essa normalização
        $publicKey = '';
        $privateKey = '';
        
        // PRIMEIRO: Tentar ler diretamente dos headers brutos (mais confiável)
        $allHeaders = $request->headers->all();
        
        // Buscar Public Key em TODOS os headers possíveis
        foreach ($allHeaders as $key => $value) {
            $keyLower = strtolower(str_replace(['-', '_'], '', $key));
            $headerValue = is_array($value) ? ($value[0] ?? '') : $value;
            $headerValue = trim($headerValue);
            
            // Procurar por "public" e "key" no nome do header
            if (empty($publicKey) && 
                (strpos($keyLower, 'public') !== false && strpos($keyLower, 'key') !== false) &&
                !empty($headerValue) && str_starts_with($headerValue, 'PB-playpayments-')) {
                $publicKey = $headerValue;
            }
            
            // Procurar por "private" ou "secret" e "key" no nome do header
            if (empty($privateKey) && 
                ((strpos($keyLower, 'private') !== false || strpos($keyLower, 'secret') !== false) && 
                 strpos($keyLower, 'key') !== false) &&
                !empty($headerValue) && str_starts_with($headerValue, 'SK-playpayments-')) {
                $privateKey = $headerValue;
            }
        }
        
        // SEGUNDO: Tentar usando o método header() do Request (normalizado)
        if (empty($publicKey)) {
            $publicKeyHeaders = ['X-Public-Key', 'x-public-key', 'X-PUBLIC-KEY', 'Public-Key', 'public-key'];
            foreach ($publicKeyHeaders as $header) {
                $value = $request->header($header);
                if (!empty($value) && str_starts_with(trim($value), 'PB-playpayments-')) {
                    $publicKey = trim($value);
                    break;
                }
            }
        }
        
        if (empty($privateKey)) {
            $privateKeyHeaders = ['X-Private-Key', 'x-private-key', 'X-PRIVATE-KEY', 'Private-Key', 'private-key', 
                                 'X-Secret-Key', 'x-secret-key', 'X-SECRET-KEY', 'Secret-Key', 'secret-key'];
            foreach ($privateKeyHeaders as $header) {
                $value = $request->header($header);
                if (!empty($value) && str_starts_with(trim($value), 'SK-playpayments-')) {
                    $privateKey = trim($value);
                    break;
                }
            }
        }
        
        // TERCEIRO: Busca mais agressiva - qualquer header que contenha "public" e "key"
        if (empty($publicKey)) {
            foreach ($allHeaders as $key => $value) {
                $keyLower = strtolower($key);
                if (strpos($keyLower, 'public') !== false && strpos($keyLower, 'key') !== false) {
                    $headerValue = trim(is_array($value) ? ($value[0] ?? '') : $value);
                    if (!empty($headerValue)) {
                        $publicKey = $headerValue;
                        break;
                    }
                }
            }
        }
        
        if (empty($privateKey)) {
            foreach ($allHeaders as $key => $value) {
                $keyLower = strtolower($key);
                if ((strpos($keyLower, 'private') !== false || strpos($keyLower, 'secret') !== false) && 
                    strpos($keyLower, 'key') !== false) {
                    $headerValue = trim(is_array($value) ? ($value[0] ?? '') : $value);
                    if (!empty($headerValue)) {
                        $privateKey = $headerValue;
                        break;
                    }
                }
            }
        }
        
        // Também aceitar no formato Bearer para compatibilidade
        // Aceita vários formatos:
        // 1. "Bearer public_key:private_key"
        // 2. "Bearer public_key" (se começar com PB-playpayments-)
        // 3. "public_key:private_key" (sem Bearer)
        $authHeader = $request->header('Authorization');
        if ($authHeader) {
            $authValue = trim($authHeader);
            
            // Remover "Bearer " se existir
            if (str_starts_with($authValue, 'Bearer ')) {
                $authValue = trim(substr($authValue, 7));
            }
            
            // Verificar se tem dois tokens separados por ":"
            if (strpos($authValue, ':') !== false) {
                $tokens = explode(':', $authValue, 2);
                $token1 = trim($tokens[0]);
                $token2 = trim($tokens[1] ?? '');
                
                // Identificar qual é public e qual é private
                if (str_starts_with($token1, 'PB-playpayments-')) {
                    $publicKey = $token1;
                    if (!empty($token2) && str_starts_with($token2, 'SK-playpayments-')) {
                        $privateKey = $token2;
                    }
                } elseif (str_starts_with($token1, 'SK-playpayments-')) {
                    $privateKey = $token1;
                    if (!empty($token2) && str_starts_with($token2, 'PB-playpayments-')) {
                        $publicKey = $token2;
                    }
                }
            } else {
                // Se é um único token, identificar pelo prefixo
                if (str_starts_with($authValue, 'PB-playpayments-')) {
                    $publicKey = $authValue;
                } elseif (str_starts_with($authValue, 'SK-playpayments-')) {
                    $privateKey = $authValue;
                }
            }
        }
        
        // QUARTO: Se ainda não encontrou, tentar ler do body da requisição (último recurso)
        // Alguns clientes podem enviar nos dados POST
        if ((empty($publicKey) || empty($privateKey)) && $request->isMethod('POST')) {
            $publicKeyFromBody = $request->input('public_key') ?? $request->input('publicKey') ?? $request->input('X-Public-Key');
            $privateKeyFromBody = $request->input('private_key') ?? $request->input('privateKey') ?? $request->input('X-Private-Key') ?? $request->input('secret_key') ?? $request->input('secretKey');
            
            if (!empty($publicKeyFromBody) && str_starts_with(trim($publicKeyFromBody), 'PB-playpayments-')) {
                $publicKey = trim($publicKeyFromBody);
            }
            if (!empty($privateKeyFromBody) && str_starts_with(trim($privateKeyFromBody), 'SK-playpayments-')) {
                $privateKey = trim($privateKeyFromBody);
            }
        }
        
        // Log detalhado para debug (apenas se faltar algum token)
        if (empty($publicKey) || empty($privateKey)) {
            $allHeaders = $request->headers->all();
            $debugHeaders = [];
            $allHeadersList = [];
            
            // Listar TODOS os headers recebidos (para debug)
            foreach ($allHeaders as $key => $value) {
                $headerValue = is_array($value) ? ($value[0] ?? '') : $value;
                $allHeadersList[$key] = !empty($headerValue) ? 'PRESENTE (' . strlen($headerValue) . ' chars)' : 'VAZIO';
                
                // Headers relevantes (com preview)
                if (stripos($key, 'key') !== false || stripos($key, 'authorization') !== false) {
                    if (!empty($headerValue)) {
                        // Para Authorization, mostrar mais caracteres para ver se tem os dois tokens
                        $previewLength = stripos($key, 'authorization') !== false ? 60 : 20;
                        $preview = substr($headerValue, 0, $previewLength);
                        $debugHeaders[$key] = $preview . (strlen($headerValue) > $previewLength ? '...' : '');
                    } else {
                        $debugHeaders[$key] = 'VAZIO';
                    }
                }
            }
            
            Log::warning('Falha na autenticação Public/Private Key - Tokens ausentes', [
                'public_key_received' => !empty($publicKey),
                'private_key_received' => !empty($privateKey),
                'public_key_preview' => !empty($publicKey) ? substr($publicKey, 0, 15) . '...' : 'AUSENTE',
                'private_key_preview' => !empty($privateKey) ? substr($privateKey, 0, 15) . '...' : 'AUSENTE',
                'headers_relevantes' => $debugHeaders,
                'todos_headers' => array_keys($allHeadersList),
                'method' => $request->method(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'http_code' => 401,
                'success' => true,
                'data' => [
                    'success' => false,
                    'error' => 'Não autorizado.',
                    'message' => 'Ambos os tokens são necessários para criar PIX. Forneça Public Key e Private Key nos headers: X-Public-Key e X-Private-Key',
                    'required_headers' => [
                        'X-Public-Key' => 'Public Key (PB-playpayments-...)',
                        'X-Private-Key' => 'Private Key (SK-playpayments-...) ou X-Secret-Key'
                    ],
                    'debug' => [
                        'public_key_received' => !empty($publicKey),
                        'private_key_received' => !empty($privateKey),
                        'headers_encontrados' => array_keys($debugHeaders),
                        'todos_headers_recebidos' => array_keys($allHeadersList),
                        'note' => 'Verifique se os headers X-Public-Key e X-Private-Key estão sendo enviados. Headers são case-insensitive mas devem conter "public" e "key" no nome.'
                    ]
                ]
            ], 401);
        }
        
        // Buscar usuário pelos tokens
        $cacheKey = 'api_user_pub_priv_' . md5($publicKey . '_' . $privateKey);
        $user = Cache::remember($cacheKey, 300, function () use ($publicKey, $privateKey) {
            // Buscar usuário que tenha ambos os tokens
            $user = User::where('api_public_key', $publicKey)
                ->where('api_secret', $privateKey)
                ->first();
            
            return $user;
        });
        
        if (!$user) {
            Log::warning('Tentativa de acesso com tokens inválidos (Public/Private)', [
                'public_key_prefix' => substr($publicKey, 0, 10) . '...',
                'private_key_prefix' => substr($privateKey, 0, 10) . '...',
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Não autorizado.',
                'message' => 'Tokens de autorização inválidos. Verifique se a Public Key e Private Key estão corretas.'
            ], 401);
        }
        
        // Verificar se o usuário está bloqueado
        if ($user->isBlocked()) {
            Log::warning('Tentativa de acesso API com usuário bloqueado (Public/Private)', [
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
        
        // Verificar se o usuário tem gateway configurado
        if (!$user->assignedGateway) {
            return response()->json([
                'success' => false,
                'error' => 'Nenhum gateway de pagamento configurado para este usuário'
            ], 400);
        }
        
        // Autenticar o usuário para a requisição
        Auth::login($user);
        
        Log::info('Acesso autorizado via Public/Private Key', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'method' => $request->method(),
            'endpoint' => $request->path()
        ]);
        
        return $next($request);
    }
}

