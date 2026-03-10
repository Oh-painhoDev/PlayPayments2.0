<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AstrofyIntegration;
use App\Models\Transaction;
use App\Services\AstrofyService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller para endpoints do provedor de pagamento (chamados pela Astrofy)
 * 
 * Endpoints:
 * - POST /order - Criar ordem
 * - GET /order/:externalId - Consultar ordem
 */
class AstrofyGatewayController extends Controller
{
    protected $astrofyService;
    protected $astrofyBaseUrl = 'https://gatewayhub.astrofy.site';

    public function __construct(AstrofyService $astrofyService)
    {
        $this->astrofyService = $astrofyService;
    }

    /**
     * POST /order - Cria uma ordem de pagamento
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     * - X-Api-Key: Chave da API do usuário (formato: {gateway_id}:{user_private_key})
     * 
     * Payload:
     * {
     *   "orderId": "12345",
     *   "amount": 50.00,
     *   "currency": "BRL",
     *   "description": "Pedido de teste",
     *   "paymentMethod": "PIX",
     *   "customer": {
     *     "name": "João da Silva",
     *     "email": "joao@email.com",
     *     "document": {
     *       "type": "CPF",
     *       "value": "18646546004"
     *     }
     *   }
     * }
     */
    public function createOrder(Request $request)
    {
        try {
            // Obter integração do middleware
            $integration = $request->get('astrofy_integration');
            
            if (!$integration) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Validar payload
            $validator = Validator::make($request->all(), [
                'orderId' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|in:BRL',
                'description' => 'nullable|string|max:500',
                'paymentMethod' => 'required|string|in:PIX,CARD',
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document.type' => 'required|string|in:CPF',
                'customer.document.value' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Invalid payload: ' . $firstError,
                ], 400);
            }

            $orderId = $request->input('orderId');
            $paymentMethod = strtoupper($request->input('paymentMethod'));

            // Verificar se método de pagamento é suportado
            if (!in_array($paymentMethod, $integration->payment_types)) {
                return response()->json([
                    'error' => 'UnsupportedPaymentMethod',
                    'message' => "The payment method {$paymentMethod} is not supported by this gateway.",
                ], 400);
            }

            // IDEMPOTÊNCIA: Verificar se já existe uma transação com este orderId
            $existingTransaction = Transaction::where('external_id', $orderId)
                ->where('user_id', $integration->user_id)
                ->first();

            if ($existingTransaction) {
                // Retornar a mesma transação existente
                $status = $this->mapStatusToAstrofy($existingTransaction->status);
                $response = [
                    'externalId' => $existingTransaction->transaction_id,
                    'status' => $status,
                ];

                // Adicionar instructions se disponível
                if ($paymentMethod === 'PIX') {
                    $pixData = $existingTransaction->payment_data ?? [];
                    $qrCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? $pixData['qr_code'] ?? $pixData['emvqrcps'] ?? null;
                    
                    if ($qrCode) {
                        $response['instructions'] = [
                            'type' => 'TOKEN',
                            'value' => $qrCode,
                        ];
                    }
                } elseif ($paymentMethod === 'CARD') {
                    $cardData = $existingTransaction->payment_data ?? [];
                    $checkoutUrl = $cardData['checkout_url'] ?? $cardData['url'] ?? null;
                    
                    if ($checkoutUrl) {
                        $response['instructions'] = [
                            'type' => 'URL',
                            'value' => $checkoutUrl,
                        ];
                    }
                }

                Log::info('✅ Astrofy: Ordem idempotente retornada', [
                    'order_id' => $orderId,
                    'transaction_id' => $existingTransaction->transaction_id,
                ]);

                return response()->json($response, 201);
            }

            // Buscar o usuário da integração
            $user = $integration->user;
            
            if (!$user) {
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Integration user not found.',
                ], 400);
            }

            // Verificar se o usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Gateway not configured for this user. Please configure a payment gateway in your account.',
                ], 400);
            }

            // Preparar dados para criar transação
            $transactionData = [
                'amount' => $request->input('amount'),
                'payment_method' => strtolower($paymentMethod),
                'description' => $request->input('description', $request->input('orderId')),
                'customer' => [
                    'name' => $request->input('customer.name'),
                    'email' => $request->input('customer.email'),
                    'document' => $request->input('customer.document.value'),
                ],
                'external_id' => $orderId,
            ];

            // Criar PaymentGatewayService com o gateway do usuário
            $paymentService = new PaymentGatewayService($user->assignedGateway);
            
            // Criar transação
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success'] || !isset($result['transaction'])) {
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => $result['error'] ?? 'Failed to create transaction.',
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'] ?? [];

            // Preparar resposta
            $status = $this->mapStatusToAstrofy($transaction->status);
            $response = [
                'externalId' => $transaction->transaction_id,
                'status' => $status,
            ];

            // Adicionar instructions baseado no método de pagamento
            if ($paymentMethod === 'PIX') {
                // Buscar dados do PIX
                $pixData = $gatewayResponse['payment_data']['pix'] ?? $transaction->payment_data ?? [];
                $qrCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? $pixData['qr_code'] ?? $pixData['emvqrcps'] ?? null;
                
                if ($qrCode) {
                    $response['instructions'] = [
                        'type' => 'TOKEN',
                        'value' => $qrCode,
                    ];
                } else {
                    return response()->json([
                        'error' => 'BadRequest',
                        'message' => 'Failed to generate PIX QR Code.',
                    ], 400);
                }
            } elseif ($paymentMethod === 'CARD') {
                // Buscar URL de checkout
                $cardData = $gatewayResponse['payment_data']['card'] ?? $transaction->payment_data ?? [];
                $checkoutUrl = $cardData['checkout_url'] ?? $cardData['url'] ?? $cardData['payment_url'] ?? null;
                
                if ($checkoutUrl) {
                    $response['instructions'] = [
                        'type' => 'URL',
                        'value' => $checkoutUrl,
                    ];
                } else {
                    // Se não tiver URL, criar uma URL de checkout genérica
                    $checkoutUrl = rtrim(config('app.url'), '/') . '/checkout/' . $transaction->transaction_id;
                    $response['instructions'] = [
                        'type' => 'URL',
                        'value' => $checkoutUrl,
                    ];
                }
            }

            // Validar que instructions.type é TOKEN ou URL
            if (isset($response['instructions']['type']) && !in_array($response['instructions']['type'], ['TOKEN', 'URL'])) {
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Invalid instructions type. Must be TOKEN or URL.',
                ], 400);
            }

            Log::info('✅ Astrofy: Ordem criada com sucesso', [
                'integration_id' => $integration->id,
                'order_id' => $orderId,
                'transaction_id' => $transaction->transaction_id,
                'payment_method' => $paymentMethod,
            ]);

            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao criar ordem', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'BadRequest',
                'message' => 'An error occurred while processing the request.',
            ], 400);
        }
    }

    /**
     * GET /order/:externalId - Consulta status de uma ordem
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     * - X-Api-Key: Chave da API do usuário
     */
    public function getOrderStatus(Request $request, string $externalId)
    {
        try {
            // Obter integração do middleware
            $integration = $request->get('astrofy_integration');
            
            if (!$integration) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Buscar transação pelo externalId (pode ser transaction_id ou external_id)
            $transaction = Transaction::where(function($query) use ($externalId) {
                    $query->where('transaction_id', $externalId)
                          ->orWhere('external_id', $externalId);
                })
                ->where('user_id', $integration->user_id)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'error' => 'NotFound',
                    'message' => 'Order not found.',
                ], 404);
            }

            // Mapear status
            $status = $this->mapStatusToAstrofy($transaction->status);

            $response = [
                'externalId' => $transaction->transaction_id,
                'status' => $status,
            ];

            // Adicionar instructions se disponível e ainda pendente
            if ($status === 'PENDING') {
                $paymentMethod = strtoupper($transaction->payment_method ?? 'PIX');
                
                if ($paymentMethod === 'PIX') {
                    $pixData = $transaction->payment_data ?? [];
                    $qrCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? $pixData['qr_code'] ?? $pixData['emvqrcps'] ?? null;
                    
                    if ($qrCode) {
                        $response['instructions'] = [
                            'type' => 'TOKEN',
                            'value' => $qrCode,
                        ];
                    }
                } elseif ($paymentMethod === 'CARD') {
                    $cardData = $transaction->payment_data ?? [];
                    $checkoutUrl = $cardData['checkout_url'] ?? $cardData['url'] ?? $cardData['payment_url'] ?? null;
                    
                    if ($checkoutUrl) {
                        $response['instructions'] = [
                            'type' => 'URL',
                            'value' => $checkoutUrl,
                        ];
                    }
                }
            }

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao consultar status da ordem', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'BadRequest',
                'message' => 'An error occurred while processing the request.',
            ], 400);
        }
    }

    /**
     * Mapeia o status da transação para o formato Astrofy
     * 
     * Status possíveis: PENDING, APPROVED, REJECTED, REFUNDED
     */
    private function mapStatusToAstrofy(string $status): string
    {
        $statusLower = strtolower($status);

        // APPROVED
        if (in_array($statusLower, ['paid', 'paid_out', 'paidout', 'completed', 'success', 'successful', 'approved', 'confirmed', 'settled', 'captured'])) {
            return 'APPROVED';
        }

        // PENDING
        if (in_array($statusLower, ['pending', 'waiting_payment', 'waiting', 'processing', 'authorized'])) {
            return 'PENDING';
        }

        // REFUNDED
        if (in_array($statusLower, ['refunded', 'partially_refunded', 'reversed'])) {
            return 'REFUNDED';
        }

        // REJECTED (equivale a FAILED)
        if (in_array($statusLower, ['failed', 'cancelled', 'canceled', 'expired', 'rejected', 'declined', 'error', 'failed_payment'])) {
            return 'REJECTED';
        }

        // Default: PENDING
        return 'PENDING';
    }
}

