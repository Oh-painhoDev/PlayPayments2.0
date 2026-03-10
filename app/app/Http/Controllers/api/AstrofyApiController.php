<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AstrofyIntegration;
use App\Services\AstrofyService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AstrofyApiController extends Controller
{
    protected $astrofyService;
    protected $paymentGatewayService;

    public function __construct(AstrofyService $astrofyService, PaymentGatewayService $paymentGatewayService)
    {
        $this->astrofyService = $astrofyService;
        $this->paymentGatewayService = $paymentGatewayService;
    }

    /**
     * POST /order - Cria um pedido (chamado pela Astrofy)
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     * - X-Api-Key: Chave da API do usuário
     */
    public function createOrder(Request $request)
    {
        try {
            // Validar headers
            $gatewayKey = $request->header('X-Gateway-Key');
            $apiKey = $request->header('X-Api-Key');

            if (!$gatewayKey || !$apiKey) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Buscar integração pela gateway_key (chave que a Astrofy nos dá)
            $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // O X-Api-Key é do cliente final da Astrofy, não precisamos validar aqui
            // A validação principal é o X-Gateway-Key que identifica nossa integração
            // O X-Api-Key pode ser usado para identificar o cliente final se necessário

            // Validar payload
            $validator = Validator::make($request->all(), [
                'orderId' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|in:BRL',
                'description' => 'nullable|string|max:500',
                'paymentMethod' => 'required|string|in:PIX', // Apenas PIX por enquanto
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document.type' => 'required|string|in:CPF',
                'customer.document.value' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Invalid payload: ' . $validator->errors()->first(),
                ], 400);
            }

            // Verificar se método de pagamento é suportado
            $paymentMethod = strtoupper($request->input('paymentMethod'));
            if (!in_array($paymentMethod, $integration->payment_types)) {
                return response()->json([
                    'error' => 'UnsupportedPaymentMethod',
                    'message' => "The payment method {$paymentMethod} is not supported by this gateway.",
                ], 400);
            }

            // Buscar o usuário da integração e usar suas credenciais de gateway
            $user = $integration->user;
            
            if (!$user) {
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Integration user not found.',
                ], 400);
            }

            // Verificar se o usuário tem gateway configurado (assignedGateway)
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
                'external_id' => $request->input('orderId'),
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

            // Preparar resposta (apenas PIX por enquanto)
            $response = [
                'externalId' => $transaction->transaction_id,
                'status' => 'PENDING',
            ];

            // Apenas PIX é suportado
            if ($paymentMethod === 'PIX') {
                // Buscar dados do PIX do gateway_response ou payment_data
                $pixData = $gatewayResponse['payment_data']['pix'] ?? $transaction->payment_data ?? [];
                $qrCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? $pixData['qr_code'] ?? $pixData['emvqrcps'] ?? null;
                
                if ($qrCode) {
                    $response['instructions'] = [
                        'type' => 'TOKEN',
                        'value' => $qrCode,
                    ];
                } else {
                    // Se não encontrou o QR Code, tentar buscar do payment_data da transação
                    $transactionPixData = $transaction->payment_data ?? [];
                    $qrCode = $transactionPixData['qr_code'] ?? $transactionPixData['emvqrcps'] ?? null;
                    
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
                }
            } else {
                // Apenas PIX é suportado por enquanto
                return response()->json([
                    'error' => 'UnsupportedPaymentMethod',
                    'message' => "The payment method {$paymentMethod} is not supported by this gateway.",
                ], 400);
            }

            Log::info('✅ Astrofy: Pedido criado com sucesso', [
                'integration_id' => $integration->id,
                'order_id' => $request->input('orderId'),
                'transaction_id' => $transaction->transaction_id,
                'payment_method' => $paymentMethod,
            ]);

            return response()->json($response, 201);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao criar pedido', [
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
     * GET /order/:externalId - Consulta status de um pedido (chamado pela Astrofy)
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     * - X-Api-Key: Chave da API do usuário
     */
    public function getOrderStatus(Request $request, string $externalId)
    {
        try {
            // Validar headers
            $gatewayKey = $request->header('X-Gateway-Key');
            $apiKey = $request->header('X-Api-Key');

            if (!$gatewayKey || !$apiKey) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Buscar integração pela gateway_key (chave que a Astrofy nos dá)
            $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // O X-Api-Key é do cliente final da Astrofy, não precisamos validar aqui
            // A validação principal é o X-Gateway-Key

            // Buscar status do pedido
            $orderStatus = $this->astrofyService->getOrderStatus($externalId, $integration);

            if (!$orderStatus) {
                return response()->json([
                    'error' => 'NotFound',
                    'message' => 'Order not found.',
                ], 404);
            }

            return response()->json($orderStatus, 200);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao consultar status do pedido', [
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
}

