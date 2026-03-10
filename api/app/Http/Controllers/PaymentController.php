<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    /**
     * Create a new payment
     */
    public function create(Request $request)
    {
        try {
            // Load user with assigned gateway in a single query (optimized)
            $user = Auth::user()->load('assignedGateway');

            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:pix,credit_card,bank_slip',
                'description' => 'nullable|string|max:255',
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document' => 'required|string',
                'customer.phone' => 'nullable|string',
                'installments' => 'nullable|integer|min:1|max:12',
                'metadata' => 'nullable|array',
                'postbackUrl' => 'nullable|url|max:255',
                'split' => 'nullable|array|max:10',
                'split.*.recipient_id' => 'required_with:split|string',
                'split.*.amount' => 'required_with:split|numeric|min:0.01',
                'split.*.description' => 'nullable|string|max:255',
                'pix_expires_in_minutes' => 'nullable|integer|min:1|max:129600', // 1 minuto a 90 dias (129,600 minutos)
                'expires_in' => 'nullable|integer|min:60|max:7776000', // Compatibilidade: em segundos (1 min a 90 dias)
                'products' => 'nullable|array',
                'products.*.title' => 'required_with:products|string|max:255',
                'products.*.name' => 'nullable|string|max:255',
                'products.*.description' => 'nullable|string|max:500',
                'products.*.quantity' => 'nullable|integer|min:1',
                'products.*.unitPrice' => 'nullable|numeric|min:0.01',
                'products.*.price' => 'nullable|numeric|min:0.01',
                'products.*.tangible' => 'nullable|boolean',
                'items' => 'nullable|array', // Alias para products
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validate split if provided
            if ($request->has('split') && is_array($request->split)) {
                $splitTotal = 0;
                $recipients = [];
                
                foreach ($request->split as $index => $split) {
                    $splitTotal += $split['amount'];
                    
                    // Validate recipient exists
                    $recipient = \App\Models\User::where('id', $split['recipient_id'])
                        ->orWhere('email', $split['recipient_id'])
                        ->first();
                    
                    if (!$recipient) {
                        return response()->json([
                            'success' => false,
                            'error' => "Destinatário '{$split['recipient_id']}' não encontrado no índice {$index}",
                        ], 400);
                    }
                    
                    $recipients[] = $recipient;
                }
                
                // Validate split total doesn't exceed transaction amount
                if ($splitTotal > $request->amount) {
                    return response()->json([
                        'success' => false,
                        'error' => sprintf(
                            'A soma dos valores de split (R$ %s) não pode ultrapassar o valor total da transação (R$ %s)',
                            number_format($splitTotal, 2, ',', '.'),
                            number_format($request->amount, 2, ',', '.')
                        ),
                    ], 400);
                }
            }

            // Check if user has assigned gateway
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não possui gateway de pagamento configurado. Entre em contato com o suporte.',
                ], 400);
            }

            // Validate transaction limits based on payment method (cached for 10 minutes)
            $fees = Cache::remember('global_fees', 600, function () {
                return \App\Helpers\FeeHelper::getGlobalFees();
            });
            $paymentMethod = $request->payment_method;
            $amount = $request->amount;
            
            if (isset($fees[$paymentMethod])) {
                $methodFees = $fees[$paymentMethod];
                
                // Check minimum transaction value
                if (isset($methodFees['min_transaction_value']) && $amount < $methodFees['min_transaction_value']) {
                    return response()->json([
                        'success' => false,
                        'error' => sprintf(
                            'O valor mínimo para %s é R$ %s',
                            $paymentMethod === 'pix' ? 'PIX' : ($paymentMethod === 'credit_card' ? 'Cartão de Crédito' : 'Boleto'),
                            number_format($methodFees['min_transaction_value'], 2, ',', '.')
                        ),
                    ], 400);
                }
                
                // Check maximum transaction value
                if (isset($methodFees['max_transaction_value']) && $methodFees['max_transaction_value'] > 0 && $amount > $methodFees['max_transaction_value']) {
                    return response()->json([
                        'success' => false,
                        'error' => sprintf(
                            'O valor máximo para %s é R$ %s',
                            $paymentMethod === 'pix' ? 'PIX' : ($paymentMethod === 'credit_card' ? 'Cartão de Crédito' : 'Boleto'),
                            number_format($methodFees['max_transaction_value'], 2, ',', '.')
                        ),
                    ], 400);
                }
            }

            // Create payment service
            $paymentService = new PaymentGatewayService($user->assignedGateway);

            // Create transaction
            $requestData = $request->all();
            
            // CRITICAL: Ensure user_id is explicitly set in the request data
            $requestData['user_id'] = $user->id;
            
            Log::info('Creating transaction via API', [
                'user_id' => $user->id,
                'amount' => $requestData['amount'],
                'payment_method' => $requestData['payment_method'],
                'has_postback_url' => !empty($requestData['postbackUrl'])
            ]);
            
            $result = $paymentService->createTransaction($user, $requestData);

            if (!$result['success']) {
                Log::error('Failed to create transaction via API', [
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

            // Check if we have the necessary PIX data for PIX payments
            if ($transaction->payment_method === 'pix') {
                $pixData = $gatewayResponse['payment_data']['pix'] ?? null;
                
                // Check if we have any valid PIX code (payload, qrcode, or emv)
                $hasPixCode = false;
                if ($pixData) {
                    $hasPixCode = !empty($pixData['payload']) || 
                                  !empty($pixData['qrcode']) || 
                                  !empty($pixData['emv']);
                }
                
                if (!$hasPixCode) {
                    // Delete the transaction as it's not usable
                    $transaction->delete();
                    
                    Log::error('PIX code not found in gateway response', [
                        'transaction_id' => $transaction->transaction_id,
                        'gateway_response' => $gatewayResponse,
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'error' => 'Não foi possível gerar o QR Code PIX. Por favor, tente novamente.',
                    ], 400);
                }
            }

            // Format response based on payment method
            $responseData = [
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => $transaction->amount,
                'fee_amount' => $transaction->fee_amount,
                'net_amount' => $transaction->net_amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'expires_at' => $transaction->expires_at?->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
            ];

            // Add payment-specific data
            if ($transaction->payment_method === 'pix' && isset($gatewayResponse['payment_data']['pix'])) {
                $pixData = $gatewayResponse['payment_data']['pix'];
                
                // Extract PIX code (payload/emv/qrcode)
                $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
                
                // Add PIX data in nested structure (for modern clients)
                $responseData['pix'] = [
                    'qr_code' => $pixCode,
                    'expiration_date' => $pixData['expirationDate'] ?? null,
                ];
                
                // Add PIX code at root level for compatibility with test scripts
                // This allows scripts to access: $result['pix_code'] and $result['qr_code']
                $responseData['pix_code'] = $pixCode;
                $responseData['qr_code'] = $pixCode; // Alias for backward compatibility
                
                // Ensure qr_code_url is not included
                if (isset($responseData['pix']['qr_code_url'])) {
                    unset($responseData['pix']['qr_code_url']);
                }
            }

            // Add customer data
            $responseData['customer'] = $transaction->customer_data;

            // Add split data if provided
            if ($request->has('split') && is_array($request->split)) {
                $responseData['split'] = $request->split;
            }

            // Add postbackUrl to response if it was provided
            if (!empty($request->postbackUrl)) {
                $responseData['postbackUrl'] = $request->postbackUrl;
            }

            Log::info('Transação criada com sucesso', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id,
                'has_postback_url' => !empty($request->postbackUrl)
            ]);
            
            // Dispatch webhook event for transaction.created
            // Always dispatch, even for retained transactions (they will send amount=0)
            $webhookService = new WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.created');
            
            // Enviar para UTMify se integração estiver ativa
            try {
                $utmifyService = new \App\Services\UtmifyService();
                $utmifyService->sendTransaction($transaction, 'created');
            } catch (\Exception $e) {
                Log::error('Erro ao enviar transação para UTMify (created)', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            Log::info('Transaction created successfully via API', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => $transaction->amount,
                'is_retained' => $transaction->is_retained,
                'webhook_dispatched' => true // Sempre dispara webhook, mesmo para retidas
            ]);

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Erro ao criar transação: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Get transaction details
     */
    public function show(Request $request, string $transactionId)
    {
        try {
            $user = Auth::user();

            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->with('gateway')
                ->withCount(['walletTransactions as refund_count' => function($q) {
                    $q->where('type', 'debit')
                      ->where('category', 'refund');
                }])
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transação não encontrada',
                ], 404);
            }

            // For retained transactions, always show as pending to the user
            $status = $transaction->is_retained ? 'pending' : $transaction->status;
            $isPaid = $transaction->is_retained ? false : $transaction->isPaid();

            $responseData = [
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'gateway_transaction_id' => $transaction->external_id,
                'amount' => $transaction->amount,
                'fee_amount' => $transaction->fee_amount,
                'net_amount' => $transaction->net_amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'status' => $status,
                'paid' => $isPaid,
                'customer_data' => $transaction->customer_data,
                'payment_data' => $transaction->payment_data,
                'metadata' => $transaction->metadata,
                'expires_at' => $transaction->expires_at?->toISOString(),
                'paid_at' => $transaction->paid_at?->toISOString(),
                'created_at' => $transaction->created_at->toISOString(),
            ];
            
            // Include postbackUrl in response if it exists in metadata
            if (!empty($transaction->metadata['postbackUrl'])) {
                $responseData['postbackUrl'] = $transaction->metadata['postbackUrl'];
            }
            
            // Remove qr_code_url from response
            if (isset($responseData['payment_data']['pix'])) {
                unset($responseData['payment_data']['pix']['qr_code_url']);
            }
            
            // Add PIX code at root level for compatibility with test scripts
            if ($transaction->payment_method === 'pix' && $transaction->payment_data) {
                $pixData = null;
                
                // Try to get PIX data from nested structure
                if (isset($transaction->payment_data['payment_data']['pix'])) {
                    $pixData = $transaction->payment_data['payment_data']['pix'];
                } elseif (isset($transaction->payment_data['pix'])) {
                    $pixData = $transaction->payment_data['pix'];
                }
                
                if ($pixData) {
                    // Extract PIX code (payload/emv/qrcode)
                    $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
                    
                    // Add PIX code at root level for compatibility with test scripts
                    $responseData['pix_code'] = $pixCode;
                    $responseData['qr_code'] = $pixCode; // Alias for backward compatibility
                }
            }
            
            if (isset($responseData['payment_data']['invoice'])) {
                unset($responseData['payment_data']['invoice']['invoiceUrl']);
            }
            
            // Remove any URL fields from the response
            if (isset($responseData['payment_data']['pix']['receiptUrl'])) {
                unset($responseData['payment_data']['pix']['receiptUrl']);
            }
            
            if (isset($responseData['payment_data']['secureUrl'])) {
                unset($responseData['payment_data']['secureUrl']);
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar transação: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * Check transaction status (simplified endpoint)
     */
    public function checkStatus(Request $request, string $transactionId)
    {
        try {
            $user = Auth::user();

            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->first();
                
            // If transaction is not found, return 404
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transação não encontrada',
                ], 404);
            }

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transação não encontrada',
                ], 404);
            }

            // For retained transactions, always show as pending to the user
            $status = $transaction->is_retained ? 'pending' : $transaction->status;
            $isPaid = $transaction->is_retained ? false : $transaction->isPaid();

            $responseData = [
                'success' => true,
                'transaction_id' => $transaction->transaction_id,
                'status' => $status,
                'paid' => $isPaid,
                'amount' => $transaction->amount,
                'payment_method' => $transaction->payment_method,
                'paid_at' => $transaction->paid_at?->toISOString(),
                'updated_at' => $transaction->updated_at->toISOString(),
            ];
            
            // Add PIX code if payment method is PIX
            if ($transaction->payment_method === 'pix' && $transaction->payment_data) {
                $pixData = null;
                
                // Try to get PIX data from nested structure
                if (isset($transaction->payment_data['payment_data']['pix'])) {
                    $pixData = $transaction->payment_data['payment_data']['pix'];
                } elseif (isset($transaction->payment_data['pix'])) {
                    $pixData = $transaction->payment_data['pix'];
                }
                
                if ($pixData) {
                    // Extract PIX code (payload/emv/qrcode)
                    $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
                    
                    // Add PIX code at root level for compatibility with test scripts
                    $responseData['pix_code'] = $pixCode;
                    $responseData['qr_code'] = $pixCode; // Alias for backward compatibility
                }
            }
            
            // Include postbackUrl in response if it exists in metadata
            if (!empty($transaction->metadata['postbackUrl'])) {
                $responseData['postbackUrl'] = $transaction->metadata['postbackUrl'];
            }

            return response()->json($responseData);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da transação: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
            ], 500);
        }
    }

    /**
     * List user transactions
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Transaction::forUser($user->id)
                ->with('gateway')
                ->orderBy('created_at', 'desc');

            // Filters
            if ($request->has('payment_method')) {
                $query->byPaymentMethod($request->payment_method);
            }

            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            if ($request->has('start_date')) {
                $query->where('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('created_at', '<=', $request->end_date);
            }

            $transactions = $query->paginate($request->get('per_page', 15));

            $formattedTransactions = $transactions->getCollection()->map(function ($transaction) {
                // For retained transactions, always show as pending to the user
                $status = $transaction->is_retained ? 'pending' : $transaction->status;
                
                $data = [
                    'transaction_id' => $transaction->transaction_id,
                    'gateway_transaction_id' => $transaction->external_id,
                    'amount' => $transaction->amount,
                    'fee_amount' => $transaction->fee_amount,
                    'net_amount' => $transaction->net_amount,
                    'currency' => $transaction->currency,
                    'payment_method' => $transaction->payment_method,
                    'status' => $status,
                    'created_at' => $transaction->created_at->toISOString(),
                ];
                
                // Include postbackUrl in response if it exists in metadata
                if (!empty($transaction->metadata['postbackUrl'])) {
                    $data['postbackUrl'] = $transaction->metadata['postbackUrl'];
                }
                
                return $data;
            });

            return response()->json([
                'success' => true,
                'transactions' => $formattedTransactions,
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar transações: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
            ], 500);
        }
    }
}