<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PixApiController extends Controller
{
    /**
     * Criar um pagamento PIX
     * 
     * POST /api/pix
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            // Validação dos dados
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document' => 'required|string|max:20',
                'external_id' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:500',
                'expires_in' => 'nullable|integer|min:60|max:86400', // 1 minuto a 24 horas
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Obter usuário autenticado via API Key
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum gateway de pagamento configurado para este usuário'
                ], 400);
            }

            // Preparar dados da transação
            $transactionData = [
                'amount' => $request->amount,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $request->input('customer.name'),
                    'email' => $request->input('customer.email'),
                    'document' => preg_replace('/[^0-9]/', '', $request->input('customer.document')), // Remove formatação
                ],
                'external_id' => $request->external_id ?? 'pix_' . time() . '_' . uniqid(),
                'description' => $request->description ?? 'Pagamento PIX',
                'expires_in' => $request->expires_in ?? 3600, // 1 hora padrão
            ];

            // Adicionar telefone se fornecido
            if ($request->has('customer.phone')) {
                $transactionData['customer']['phone'] = preg_replace('/[^0-9]/', '', $request->input('customer.phone'));
            }

            // Criar serviço de pagamento
            $paymentService = new PaymentGatewayService($user->assignedGateway);

            Log::info('Criando pagamento PIX via API', [
                'user_id' => $user->id,
                'amount' => $transactionData['amount'],
                'external_id' => $transactionData['external_id'],
            ]);

            // Criar transação
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success']) {
                Log::error('Falha ao criar pagamento PIX via API', [
                    'user_id' => $user->id,
                    'error' => $result['error']
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'];

            // Verificar se temos os dados PIX necessários
            $pixData = $gatewayResponse['payment_data']['pix'] ?? null;
            
            if (!$pixData) {
                $transaction->delete();
                
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível gerar o QR Code PIX. Por favor, tente novamente.',
                ], 400);
            }

            // Extrair código PIX (payload/emv/qrcode)
            $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
            
            if (!$pixCode) {
                $transaction->delete();
                
                Log::error('Código PIX não encontrado na resposta do gateway', [
                    'transaction_id' => $transaction->transaction_id,
                    'gateway_response' => $gatewayResponse,
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Não foi possível gerar o código PIX. Por favor, tente novamente.',
                ], 400);
            }

            // Gerar QR Code em base64 se não foi fornecido
            $qrCodeBase64 = null;
            // Não gerar QR Code no backend - o frontend vai gerar usando JavaScript
            // Retornar apenas o código PIX (EMV) para o frontend gerar o QR Code
            $qrCodeBase64 = null;

            // Formatar resposta
            $response = [
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => (float) $transaction->amount,
                'fee_amount' => (float) $transaction->fee_amount,
                'net_amount' => (float) $transaction->net_amount,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'pix_code' => $pixCode,
                'qr_code' => $pixCode, // Alias para compatibilidade
                'qr_code_base64' => $qrCodeBase64,
                'expires_at' => $transaction->expires_at?->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
            ];

            // Adicionar dados aninhados do PIX
            $response['pix'] = [
                'code' => $pixCode,
                'qr_code' => $pixCode,
                'qr_code_base64' => $qrCodeBase64,
                'expiration_date' => $pixData['expirationDate'] ?? $transaction->expires_at?->toISOString(),
            ];

            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar pagamento PIX via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consultar status de um pagamento PIX
     * 
     * GET /api/pix/{transactionId}
     * 
     * @param Request $request
     * @param string $transactionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $transactionId)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Buscar transação
            $transaction = \App\Models\Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->where('payment_method', 'pix')
                ->with('gateway')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Pagamento não encontrado'
                ], 404);
            }

            // Verificar status na API do gateway (igual ao depósito)
            $this->checkTransactionStatus($transaction);

            // Atualizar transação após verificação
            $transaction->refresh();

            // Extrair código PIX se disponível
            $pixData = $transaction->payment_data['pix'] ?? null;
            $pixCode = null;
            $qrCodeBase64 = null;

            if ($pixData) {
                $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
                
                if (isset($pixData['encodedImage'])) {
                    $qrCodeBase64 = $pixData['encodedImage'];
                } elseif ($pixCode) {
                    try {
                        // Não gerar QR Code no backend - frontend vai gerar usando JavaScript
                        $qrCodeBase64 = null;
                    } catch (\Exception $e) {
                        // Ignorar erro de geração de QR Code
                    }
                }
            }

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => (float) $transaction->amount,
                'fee_amount' => (float) $transaction->fee_amount,
                'net_amount' => (float) $transaction->net_amount,
                'currency' => $transaction->currency,
                'status' => $transaction->status,
                'pix_code' => $pixCode,
                'qr_code' => $pixCode,
                'qr_code_base64' => $qrCodeBase64,
                'expires_at' => $transaction->expires_at?->toISOString(),
                'paid_at' => $transaction->paid_at?->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
                'pix' => [
                    'code' => $pixCode,
                    'qr_code' => $pixCode,
                    'qr_code_base64' => $qrCodeBase64,
                    'expiration_date' => $pixData['expirationDate'] ?? $transaction->expires_at?->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao consultar pagamento PIX via API', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao consultar pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar status de um pagamento PIX (igual ao depósito)
     * 
     * GET /api/pix/status/{transactionId}
     * 
     * @param Request $request
     * @param string $transactionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request, string $transactionId)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            Log::info('Verificando status do PIX via API', [
                'user_id' => $user->id,
                'transaction_id' => $transactionId,
            ]);

            // Buscar transação por qualquer identificador possível
            $transaction = \App\Models\Transaction::where('user_id', $user->id)
                ->where('payment_method', 'pix')
                ->where(function($query) use ($transactionId) {
                    $query->where('transaction_id', $transactionId)
                          ->orWhere('external_id', $transactionId);
                })
                ->with('gateway')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Pagamento não encontrado'
                ], 404);
            }

            // Verificar status na API do gateway (igual ao depósito)
            $this->checkTransactionStatus($transaction);

            // Atualizar transação após verificação
            $transaction->refresh();

            Log::info('Status do PIX encontrado via API', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'status' => $transaction->status,
                'amount' => $transaction->amount
            ]);

            return response()->json([
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'status' => $transaction->status,
                'amount' => (float) $transaction->amount,
                'paid_at' => $transaction->paid_at?->toISOString(),
                'expires_at' => $transaction->expires_at?->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status do PIX via API', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao verificar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar status na API do gateway (igual ao DepositController)
     */
    private function checkTransactionStatus($transaction)
    {
        // Se o gateway for Shark (sharkgateway), verificar status na API
        $gatewayType = $transaction->gateway ? $transaction->gateway->getConfig('gateway_type') : null;
        
        Log::info('Verificando gateway para checkStatus PIX', [
            'transaction_id' => $transaction->transaction_id,
            'external_id' => $transaction->external_id,
            'gateway_id' => $transaction->gateway_id,
            'gateway_type' => $gatewayType,
            'has_gateway' => $transaction->gateway !== null,
        ]);

        // Verificar status na API do gateway (Shark ou Pluggou)
        if ($transaction->gateway && in_array($gatewayType, ['sharkgateway', 'pluggou'])) {
            try {
                $paymentService = new PaymentGatewayService($transaction->gateway);
                
                Log::info('Verificando status na API do gateway (PIX)', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'gateway_id' => $transaction->gateway->id,
                    'gateway_type' => $gatewayType,
                    'gateway_name' => $transaction->gateway->name,
                ]);
                
                $statusResult = $paymentService->checkTransactionStatus($transaction);
                
                Log::info('Resultado da verificação de status na API do gateway (PIX)', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'gateway_type' => $gatewayType,
                    'success' => $statusResult['success'] ?? false,
                    'status' => $statusResult['status'] ?? null,
                    'status_changed' => $statusResult['status_changed'] ?? false,
                    'error' => $statusResult['error'] ?? null,
                ]);
                
                if ($statusResult['success']) {
                    // Atualizar a transação com os dados mais recentes
                    $transaction->refresh();
                    
                    Log::info('Status atualizado da API do gateway (PIX)', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $transaction->external_id,
                        'gateway_type' => $gatewayType,
                        'old_status' => $transaction->getOriginal('status'),
                        'new_status' => $transaction->status,
                        'status_from_api' => $statusResult['status'],
                        'status_changed' => $statusResult['status_changed'] ?? false,
                    ]);
                } else {
                    Log::warning('Erro ao verificar status na API do gateway (PIX)', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $transaction->external_id,
                        'gateway_type' => $gatewayType,
                        'error' => $statusResult['error'] ?? 'Erro desconhecido',
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Erro ao verificar status na API da Shark (PIX)', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continua com o status local em caso de erro
            }
        } else {
            Log::info('Gateway não é Shark, pulando verificação de status na API (PIX)', [
                'transaction_id' => $transaction->transaction_id,
                'gateway_type' => $gatewayType,
            ]);
        }
    }

    /**
     * Listar pagamentos PIX
     * 
     * GET /api/pix
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = \App\Models\Transaction::where('user_id', $user->id)
                ->where('payment_method', 'pix')
                ->orderBy('created_at', 'desc');

            // Filtros
            if ($status) {
                $query->where('status', $status);
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $transactions = $query->paginate($perPage);

            $data = $transactions->map(function ($transaction) {
                $pixData = $transaction->payment_data['pix'] ?? null;
                $pixCode = null;

                if ($pixData) {
                    $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
                }

                return [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => (float) $transaction->amount,
                    'fee_amount' => (float) $transaction->fee_amount,
                    'net_amount' => (float) $transaction->net_amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'pix_code' => $pixCode,
                    'expires_at' => $transaction->expires_at?->toISOString(),
                    'paid_at' => $transaction->paid_at?->toISOString(),
                    'created_at' => $transaction->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao listar pagamentos PIX via API', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao listar pagamentos: ' . $e->getMessage()
            ], 500);
        }
    }
}
