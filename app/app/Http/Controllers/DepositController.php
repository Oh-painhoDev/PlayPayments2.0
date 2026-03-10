<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\PaymentGatewayService;
use App\Models\Transaction;
use App\Models\Wallet;

class DepositController extends Controller
{
    /**
     * Show deposit page or form
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get pending deposits
        $pendingDeposits = Transaction::where('user_id', $user->id)
            ->where('payment_method', 'pix')
            ->where('status', 'pending')
            ->where('external_id', 'like', 'DEPOSIT_%')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('deposit.index', compact('user', 'pendingDeposits'));
    }
    
    /**
     * Generate PIX deposit
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1.00',
            'pix_expires_in_minutes' => 'nullable|integer|min:1|max:129600', // 1 minuto a 90 dias (129,600 minutos)
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Valor inválido. Mínimo: R$ 1,00',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum gateway de pagamento configurado. Entre em contato com o suporte.'
                ], 400);
            }
            
            // Verificar se o gateway está ativo
            if (!$user->assignedGateway->is_active) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gateway de pagamento está inativo. Entre em contato com o suporte.'
                ], 400);
            }
            
            // Log gateway information for debugging
            Log::info('Verificando gateway para depósito', [
                'user_id' => $user->id,
                'gateway_id' => $user->assignedGateway->id,
                'gateway_name' => $user->assignedGateway->name,
                'gateway_slug' => $user->assignedGateway->slug,
                'gateway_is_active' => $user->assignedGateway->is_active,
            ]);
            
            $amount = $request->amount;
            
            // Tempo de expiração PIX fixo: 15 minutos (não configurável para depósitos)
            $pixExpiresInMinutes = 15;
            
            // Preparar dados da transação de depósito
            $transactionData = [
                'amount' => $amount,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'document' => preg_replace('/[^0-9]/', '', $user->document ?? '00000000000'),
                ],
                'external_id' => 'DEPOSIT_' . time() . '_' . uniqid(),
                'description' => 'Depósito na conta - ' . $user->name,
                'pix_expires_in_minutes' => $pixExpiresInMinutes,
                'expires_in' => $pixExpiresInMinutes * 60, // 900 segundos (15 minutos)
            ];
            
            // Criar serviço de pagamento
            $paymentService = new PaymentGatewayService($user->assignedGateway);
            
            // Verificar se o serviço está configurado antes de tentar criar a transação
            if (!$paymentService->isConfigured()) {
                // Buscar mais informações sobre o problema
                $adminUser = \App\Models\User::where('role', 'admin')->first();
                $credentials = null;
                $credentialsActive = null;
                $credentialsInactive = null;
                
                if ($adminUser) {
                    // Buscar credenciais ativas
                    $credentialsActive = \App\Models\UserGatewayCredential::where('user_id', $adminUser->id)
                        ->where('gateway_id', $user->assignedGateway->id)
                        ->where('is_active', true)
                        ->first();
                    
                    // Buscar credenciais inativas
                    $credentialsInactive = \App\Models\UserGatewayCredential::where('user_id', $adminUser->id)
                        ->where('gateway_id', $user->assignedGateway->id)
                        ->where('is_active', false)
                        ->first();
                    
                    // Qualquer credencial (para diagnóstico)
                    $credentials = \App\Models\UserGatewayCredential::where('user_id', $adminUser->id)
                        ->where('gateway_id', $user->assignedGateway->id)
                        ->first();
                }
                
                // Verificar gateway type
                $gatewayType = $user->assignedGateway->getConfig('gateway_type');
                $gatewaySlug = $user->assignedGateway->slug;
                
                Log::error('Gateway não está configurado para depósito', [
                    'user_id' => $user->id,
                    'gateway_id' => $user->assignedGateway->id,
                    'gateway_name' => $user->assignedGateway->name,
                    'gateway_slug' => $gatewaySlug,
                    'gateway_type' => $gatewayType,
                    'gateway_api_url' => $user->assignedGateway->api_url,
                    'gateway_is_active' => $user->assignedGateway->is_active,
                    'admin_user_exists' => $adminUser !== null,
                    'admin_user_id' => $adminUser ? $adminUser->id : null,
                    'credentials_exists' => $credentials !== null,
                    'credentials_active_exists' => $credentialsActive !== null,
                    'credentials_inactive_exists' => $credentialsInactive !== null,
                    'credentials_id' => $credentials ? $credentials->id : null,
                    'credentials_is_active' => $credentials ? $credentials->is_active : null,
                    'has_public_key' => $credentials ? !empty($credentials->public_key) : null,
                    'has_secret_key' => $credentials ? !empty($credentials->secret_key) : null,
                    'public_key_length' => $credentials && $credentials->public_key ? strlen($credentials->public_key) : 0,
                ]);
                
                // Mensagem de erro mais específica baseada no diagnóstico
                $errorMessage = 'Gateway de pagamento não está configurado com credenciais válidas.';
                
                if (!$credentials) {
                    $errorMessage .= " Nenhuma credencial encontrada para o gateway '{$user->assignedGateway->name}' (ID: {$user->assignedGateway->id}).";
                } elseif ($credentialsInactive) {
                    $errorMessage .= " As credenciais existem mas estão inativas. Ative as credenciais no painel administrativo.";
                } elseif (empty($credentials->public_key) || empty($credentials->secret_key)) {
                    $errorMessage .= " As credenciais existem mas estão incompletas (Public Key ou Secret Key faltando).";
                }
                
                $errorMessage .= " Por favor, configure as credenciais do gateway no painel administrativo (Admin > Gateways > Configurar).";
                $errorMessage .= " Gateway: {$user->assignedGateway->name} (ID: {$user->assignedGateway->id}, Tipo: " . ($gatewayType ?? 'N/A') . ")";
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }
            
            Log::info('Criando PIX de depósito', [
                'user_id' => $user->id,
                'amount' => $amount,
                'pix_expires_in_minutes' => $pixExpiresInMinutes,
                'external_id' => $transactionData['external_id'],
                'gateway_id' => $user->assignedGateway->id,
                'gateway_name' => $user->assignedGateway->name,
            ]);
            
            // Criar transação
            $result = $paymentService->createTransaction($user, $transactionData);
            
            if (!$result['success']) {
                Log::error('Falha ao criar PIX de depósito', [
                    'user_id' => $user->id,
                    'gateway_id' => $user->assignedGateway->id,
                    'gateway_name' => $user->assignedGateway->name,
                    'error' => $result['error']
                ]);
                
                // Mensagem de erro mais amigável
                $errorMessage = $result['error'];
                if (strpos($errorMessage, 'não está configurado') !== false || 
                    strpos($errorMessage, 'credenciais') !== false ||
                    strpos($errorMessage, 'configure') !== false) {
                    $errorMessage = 'Gateway de pagamento não está configurado corretamente. Por favor, configure as credenciais do gateway Pluggou no painel administrativo (Admin > Gateways > Configurar).';
                }
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 400);
            }
            
            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'];
            
            // Marcar transação como depósito (para diferenciar de pagamentos normais)
            // Podemos usar metadata para isso
            $metadata = $transaction->metadata ?? [];
            $metadata['is_deposit'] = true;
            $transaction->update(['metadata' => $metadata]);
            
            // Enviar para UTMify se integração estiver ativa
            try {
                $utmifyService = new \App\Services\UtmifyService();
                $utmifyService->sendTransaction($transaction, 'created');
            } catch (\Exception $e) {
                Log::error('Erro ao enviar depósito para UTMify (created) - DepositController', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            // Preparar resposta
            // A Pluggou retorna o código EMV em: payment_data.pix.emv (ou outros campos)
            $pixData = $gatewayResponse['payment_data']['pix'] ?? [];
            
            // Extrair código PIX (EMV) - tentar múltiplos campos possíveis
            // PRIORIDADE: emv > payload > qrcode > code
            $pixCode = null;
            $pixCodeSource = null;
            
            if (isset($pixData['emv']) && !empty(trim($pixData['emv']))) {
                $pixCode = trim($pixData['emv']);
                $pixCodeSource = 'payment_data.pix.emv';
            } elseif (isset($pixData['payload']) && !empty(trim($pixData['payload']))) {
                $pixCode = trim($pixData['payload']);
                $pixCodeSource = 'payment_data.pix.payload';
            } elseif (isset($pixData['qrcode']) && !empty(trim($pixData['qrcode']))) {
                $pixCode = trim($pixData['qrcode']);
                $pixCodeSource = 'payment_data.pix.qrcode';
            } elseif (isset($pixData['code']) && !empty(trim($pixData['code']))) {
                $pixCode = trim($pixData['code']);
                $pixCodeSource = 'payment_data.pix.code';
            }
            
            // Se ainda não encontrou, tentar outros campos do gateway_response
            if (empty($pixCode)) {
                if (isset($gatewayResponse['emv']) && !empty(trim($gatewayResponse['emv']))) {
                    $pixCode = trim($gatewayResponse['emv']);
                    $pixCodeSource = 'gateway_response.emv';
                } elseif (isset($gatewayResponse['pix_code']) && !empty(trim($gatewayResponse['pix_code']))) {
                    $pixCode = trim($gatewayResponse['pix_code']);
                    $pixCodeSource = 'gateway_response.pix_code';
                }
            }
            
            // Log para debug
            Log::info('DepositController: Preparando resposta PIX', [
                'transaction_id' => $transaction->transaction_id,
                'has_pix_data' => !empty($pixData),
                'pix_data_keys' => !empty($pixData) ? array_keys($pixData) : [],
                'gateway_response_keys' => array_keys($gatewayResponse),
                'pix_code_found' => !empty($pixCode),
                'pix_code_source' => $pixCodeSource,
                'pix_code_length' => $pixCode ? strlen($pixCode) : 0,
                'pix_code_preview' => $pixCode ? substr($pixCode, 0, 50) . '...' : null,
                'checked_fields' => [
                    'payment_data.pix.emv' => isset($pixData['emv']) ? (empty(trim($pixData['emv'])) ? 'empty' : 'found') : 'not_set',
                    'payment_data.pix.payload' => isset($pixData['payload']) ? (empty(trim($pixData['payload'])) ? 'empty' : 'found') : 'not_set',
                    'payment_data.pix.qrcode' => isset($pixData['qrcode']) ? (empty(trim($pixData['qrcode'])) ? 'empty' : 'found') : 'not_set',
                    'payment_data.pix.code' => isset($pixData['code']) ? (empty(trim($pixData['code'])) ? 'empty' : 'found') : 'not_set',
                    'gateway_response.emv' => isset($gatewayResponse['emv']) ? (empty(trim($gatewayResponse['emv'])) ? 'empty' : 'found') : 'not_set',
                    'gateway_response.pix_code' => isset($gatewayResponse['pix_code']) ? (empty(trim($gatewayResponse['pix_code'])) ? 'empty' : 'found') : 'not_set',
                ],
            ]);
            
            if (empty($pixCode)) {
                Log::error('DepositController: Código PIX não encontrado na resposta', [
                    'transaction_id' => $transaction->transaction_id,
                    'gateway_response' => $gatewayResponse,
                    'pix_data' => $pixData,
                    'gateway_response_keys' => array_keys($gatewayResponse),
                    'full_gateway_response' => json_encode($gatewayResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Código PIX não foi retornado pela gateway. Estrutura recebida: ' . json_encode(array_keys($gatewayResponse)) . '. Tente novamente ou entre em contato com o suporte.',
                ], 500);
            }
            
            $responseData = [
                'success' => true,
                'message' => 'PIX de depósito gerado com sucesso!',
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => $transaction->amount,
                'status' => $transaction->status,
                'pix_code' => $pixCode, // Código EMV da Pluggou
                'qr_code_base64' => null, // Não gerar no backend - frontend vai gerar
                'expires_at' => $transaction->expires_at?->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
                'pix' => [
                    'emv' => $pixCode, // Código EMV (Copia e Cola) - campo principal
                    'payload' => $pixCode, // Alias
                    'qrcode' => $pixCode, // Alias
                    'code' => $pixCode, // Alias
                ],
            ];
            
            return response()->json($responseData, 201);
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar PIX de depósito: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno ao gerar PIX de depósito.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check deposit status
     */
    public function checkStatus($transaction)
    {
        $user = Auth::user();
        
        Log::info('Verificando status do depósito', [
            'user_id' => $user->id,
            'transaction_param' => $transaction,
            'transaction_type' => gettype($transaction)
        ]);
        
        // Buscar transação por qualquer identificador possível
        $transactionModel = null;
        
        // Primeiro, tentar buscar por transaction_id (mais comum)
        $transactionModel = Transaction::where('user_id', $user->id)
            ->where('transaction_id', $transaction)
            ->with('gateway')
            ->first();
        
        // Se não encontrou, tentar por external_id
        if (!$transactionModel) {
            $transactionModel = Transaction::where('user_id', $user->id)
                ->where('external_id', $transaction)
                ->with('gateway')
                ->first();
        }
        
        // Se ainda não encontrou e é numérico, tentar por id
        if (!$transactionModel && is_numeric($transaction)) {
            $transactionModel = Transaction::where('user_id', $user->id)
                ->where('id', $transaction)
                ->with('gateway')
                ->first();
        }
        
        // Se ainda não encontrou, buscar por qualquer campo que contenha o valor (caso tenha sido codificado)
        if (!$transactionModel) {
            $transactionModel = Transaction::where('user_id', $user->id)
                ->where(function($query) use ($transaction) {
                    $query->where('transaction_id', 'like', '%' . $transaction . '%')
                          ->orWhere('external_id', 'like', '%' . $transaction . '%');
                })
                ->where('payment_method', 'pix')
                ->with('gateway')
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        if (!$transactionModel) {
            // Buscar todas as transações do usuário para debug
            $allTransactions = Transaction::where('user_id', $user->id)
                ->where('payment_method', 'pix')
                ->select('id', 'transaction_id', 'external_id', 'status', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray();
            
            Log::warning('Depósito não encontrado', [
                'user_id' => $user->id,
                'transaction_param' => $transaction,
                'transaction_param_type' => gettype($transaction),
                'transaction_param_length' => is_string($transaction) ? strlen($transaction) : null,
                'recent_transactions' => $allTransactions,
                'search_pattern' => $transaction
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Depósito não encontrado',
                'debug' => [
                    'searched' => $transaction,
                    'recent_transactions' => $allTransactions
                ]
            ], 404);
        }
        
        // Refresh transaction data
        $transactionModel->refresh();
        
        // Se o gateway for Shark (sharkgateway), verificar status na API
        $gatewayType = $transactionModel->gateway ? $transactionModel->gateway->getConfig('gateway_type') : null;
        
        Log::info('Verificando gateway para checkStatus', [
            'transaction_id' => $transactionModel->transaction_id,
            'external_id' => $transactionModel->external_id,
            'gateway_id' => $transactionModel->gateway_id,
            'gateway_type' => $gatewayType,
            'has_gateway' => $transactionModel->gateway !== null,
        ]);
        
        if ($transactionModel->gateway && $gatewayType === 'sharkgateway') {
            try {
                $paymentService = new PaymentGatewayService($transactionModel->gateway);
                
                Log::info('Verificando status na API da Shark', [
                    'transaction_id' => $transactionModel->transaction_id,
                    'external_id' => $transactionModel->external_id,
                    'gateway_id' => $transactionModel->gateway->id,
                    'gateway_name' => $transactionModel->gateway->name,
                    'api_url' => $transactionModel->gateway->api_url,
                ]);
                
                $statusResult = $paymentService->checkTransactionStatus($transactionModel);
                
                Log::info('Resultado da verificação de status na API da Shark', [
                    'transaction_id' => $transactionModel->transaction_id,
                    'external_id' => $transactionModel->external_id,
                    'success' => $statusResult['success'] ?? false,
                    'status' => $statusResult['status'] ?? null,
                    'status_changed' => $statusResult['status_changed'] ?? false,
                    'error' => $statusResult['error'] ?? null,
                ]);
                
                if ($statusResult['success']) {
                    // Atualizar a transação com os dados mais recentes
                    $transactionModel->refresh();
                    
                    Log::info('Status atualizado da API da Shark', [
                        'transaction_id' => $transactionModel->transaction_id,
                        'external_id' => $transactionModel->external_id,
                        'old_status' => $transactionModel->getOriginal('status'),
                        'new_status' => $transactionModel->status,
                        'status_from_api' => $statusResult['status'],
                        'status_changed' => $statusResult['status_changed'] ?? false,
                    ]);
                } else {
                    Log::warning('Erro ao verificar status na API da Shark', [
                        'transaction_id' => $transactionModel->transaction_id,
                        'external_id' => $transactionModel->external_id,
                        'error' => $statusResult['error'] ?? 'Erro desconhecido',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao verificar status na API da Shark', [
                    'transaction_id' => $transactionModel->transaction_id,
                    'external_id' => $transactionModel->external_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continua com o status local em caso de erro
            }
        } else {
            Log::info('Gateway não é Shark, pulando verificação de status na API', [
                'transaction_id' => $transactionModel->transaction_id,
                'gateway_type' => $gatewayType,
            ]);
        }
        
        Log::info('Status do depósito encontrado', [
            'user_id' => $user->id,
            'transaction_id' => $transactionModel->transaction_id,
            'external_id' => $transactionModel->external_id,
            'status' => $transactionModel->status,
            'amount' => $transactionModel->amount
        ]);
        
        return response()->json([
            'success' => true,
            'transaction_id' => $transactionModel->transaction_id,
            'external_id' => $transactionModel->external_id,
            'status' => $transactionModel->status,
            'amount' => $transactionModel->amount,
            'paid_at' => $transactionModel->paid_at?->toISOString(),
            'expires_at' => $transactionModel->expires_at?->toISOString(),
        ]);
    }
}
