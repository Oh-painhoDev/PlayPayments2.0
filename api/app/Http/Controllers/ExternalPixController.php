<?php

namespace App\Http\Controllers;

use App\Services\ExternalPixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExternalPixController extends Controller
{
    /**
     * Criar uma transação PIX (formato PodPay)
     * 
     * POST /api/external-pix/create
     * 
     * Formato PodPay:
     * {
     *   "amount": 10000,  // em centavos
     *   "currency": "BRL",
     *   "paymentMethod": "pix",
     *   "pix": {
     *     "expiresIn": 3600  // em segundos
     *   },
     *   "items": [...],
     *   "customer": {...},
     *   "postbackUrl": "...",
     *   "returnUrl": "...",
     *   "metadata": "...",
     *   "externalRef": "...",
     *   "ip": "..."
     * }
     * 
     * Headers:
     *   - Authorization: Basic {base64(username:password)}
     *   - X-API-Provider: Nome do provedor (opcional)
     *   - api_url: URL da API externa (opcional, pode vir no body)
     *   - api_token: Token da API (opcional, pode vir no body)
     */
    public function create(Request $request)
    {
        try {
            // Obter usuário autenticado (via ApiAuthMiddleware)
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Não autorizado',
                    'message' => 'Token de autorização inválido ou ausente. Use: Authorization: Bearer SK-playpayments-...'
                ], 401);
            }

            // Validar dados no formato PodPay
            $validator = Validator::make($request->all(), [
                'amount' => 'required|integer|min:1', // em centavos
                'currency' => 'nullable|string|size:3',
                'paymentMethod' => 'required|in:credit_card,boleto,pix',
                'pix' => 'nullable|array',
                'pix.expiresIn' => 'nullable|integer|min:60|max:7776000', // 1 min a 90 dias em segundos
                'items' => 'required|array|min:1',
                'items.*.title' => 'required|string|max:255',
                'items.*.unitPrice' => 'nullable|integer|min:1',
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.tangible' => 'nullable|boolean',
                'customer' => 'required|array',
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document' => 'required|string|min:11|max:18',
                'customer.phone' => 'nullable|string|max:20',
                'postbackUrl' => 'nullable|url|max:500',
                'returnUrl' => 'nullable|url|max:500',
                'metadata' => 'nullable|string|max:500',
                'externalRef' => 'nullable|string|max:255',
                'ip' => 'nullable|ip',
                
                // Configuração da API externa (opcional)
                'api_url' => 'nullable|url',
                'api_token' => 'nullable|string',
                'api_secret' => 'nullable|string',
                'auth_type' => 'nullable|in:bearer,basic,header',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Determinar provedor
            $provider = $request->header('X-API-Provider') 
                     ?? $request->input('provider') 
                     ?? 'default';

            // Criar serviço
            $pixService = $this->createPixService($request, $provider);

            // Converter formato PodPay para formato interno
            $pixData = $this->convertPodPayFormat($request->all());
            
            // Adicionar informações do usuário para logs
            $pixData['user_id'] = $user->id;
            $pixData['user_email'] = $user->email;

            // Criar QR Code PIX
            $result = $pixService->createPixQrCode($pixData);
            
            // Log da criação
            Log::info('PIX criado via API Externa', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'amount' => $request->amount,
                'transaction_id' => $result['pix']['transaction_id'] ?? null,
            ]);

            // Retornar no formato PodPay
            return response()->json([
                'id' => $result['pix']['transaction_id'] ?? null,
                'status' => 'pending',
                'paymentMethod' => 'pix',
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'BRL',
                'pix' => [
                    'qrcode' => $result['pix']['qrcode'] ?? $result['pix']['payload'] ?? null,
                    'payload' => $result['pix']['payload'] ?? $result['pix']['qrcode'] ?? null,
                    'emv' => $result['pix']['emv'] ?? null,
                    'expirationDate' => $result['pix']['expiration_date'] ?? null,
                ],
                'customer' => $request->customer,
                'items' => $request->items,
                'externalRef' => $request->externalRef,
                'createdAt' => now()->toISOString(),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao criar PIX via API externa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Converter formato PodPay para formato interno
     */
    protected function convertPodPayFormat(array $podPayData): array
    {
        // Converter amount de centavos para reais
        $amount = ($podPayData['amount'] ?? 0) / 100;
        
        // Converter expiresIn de segundos para minutos
        $expiresIn = null;
        if (isset($podPayData['pix']['expiresIn'])) {
            $expiresIn = (int)ceil($podPayData['pix']['expiresIn'] / 60); // segundos -> minutos
        }

        // Preparar descrição dos items
        $description = '';
        if (isset($podPayData['items']) && is_array($podPayData['items'])) {
            $titles = array_column($podPayData['items'], 'title');
            $description = implode(', ', $titles);
        }
        if (empty($description) && isset($podPayData['metadata'])) {
            $description = $podPayData['metadata'];
        }
        if (empty($description)) {
            $description = 'Pagamento PIX';
        }

        return [
            'amount' => $amount,
            'description' => $description,
            'currency' => $podPayData['currency'] ?? 'BRL',
            'customer' => $podPayData['customer'] ?? [],
            'expires_in' => $expiresIn ?? 15, // Padrão: 15 minutos
            'external_id' => $podPayData['externalRef'] ?? null,
            'items' => $podPayData['items'] ?? [],
            'postbackUrl' => $podPayData['postbackUrl'] ?? null,
            'returnUrl' => $podPayData['returnUrl'] ?? null,
            'metadata' => $podPayData['metadata'] ?? null,
            'ip' => $podPayData['ip'] ?? null,
        ];
    }

    /**
     * Consultar status de um pagamento PIX
     * 
     * GET /api/external-pix/status/{transactionId}
     * 
     * Headers:
     *   - X-API-Provider: Nome do provedor (opcional)
     */
    public function checkStatus(Request $request, string $transactionId)
    {
        try {
            // Obter usuário autenticado (via ApiAuthMiddleware)
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Não autorizado',
                    'message' => 'Token de autorização inválido ou ausente. Use: Authorization: Bearer SK-playpayments-...'
                ], 401);
            }
            
            // Determinar provedor
            $provider = $request->header('X-API-Provider') 
                     ?? $request->input('provider') 
                     ?? 'default';

            // Criar serviço
            $pixService = $this->createPixService($request, $provider);
            
            // Log da consulta
            Log::info('Status PIX consultado via API Externa', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'transaction_id' => $transactionId,
            ]);

            // Consultar status
            $result = $pixService->getPixStatus($transactionId);

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transactionId,
                    'status' => $result['status'],
                ],
                'raw_response' => $result['raw_response'] ?? null,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao consultar status PIX via API externa', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar instância do serviço PIX
     */
    protected function createPixService(Request $request, string $provider): ExternalPixService
    {
        // Tentar obter credenciais do Basic Auth header primeiro
        $username = null;
        $password = null;
        
        if ($request->hasHeader('Authorization')) {
            $authHeader = $request->header('Authorization');
            if (preg_match('/^Basic\s+(.+)$/i', $authHeader, $matches)) {
                $decoded = base64_decode($matches[1]);
                if ($decoded && strpos($decoded, ':') !== false) {
                    list($username, $password) = explode(':', $decoded, 2);
                }
            }
        }

        // Se configuração completa foi fornecida no request, usar ela
        if ($request->has('api_url') && ($request->has('api_token') || $username)) {
            $config = [
                'api_url' => $request->api_url,
                'api_token' => $request->api_token ?? $username,
                'auth_type' => $request->auth_type ?? ($password ? 'basic' : 'bearer'),
                'token_header' => $request->token_header ?? 'Authorization',
                'api_secret' => $request->api_secret ?? $password,
                'timeout' => $request->timeout ?? 30,
                'verify_ssl' => $request->boolean('verify_ssl', true),
            ];

            return new ExternalPixService($config);
        }

        // Tentar obter do header
        if ($request->hasHeader('api_url') && ($request->hasHeader('api_token') || $username)) {
            $config = [
                'api_url' => $request->header('api_url'),
                'api_token' => $request->header('api_token') ?? $username,
                'auth_type' => $request->header('auth_type') ?? ($password ? 'basic' : 'bearer'),
                'api_secret' => $request->header('api_secret') ?? $password,
                'timeout' => 30,
                'verify_ssl' => true,
            ];

            return new ExternalPixService($config);
        }

        // Caso contrário, usar configuração do arquivo de config
        return ExternalPixService::fromConfig($provider);
    }
}

