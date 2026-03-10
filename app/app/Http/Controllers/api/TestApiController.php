<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestApiController extends Controller
{
    /**
     * Test API endpoint
     * 
     * GET /api/test
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function test(Request $request)
    {
        try {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'message' => 'API funcionando corretamente!',
                'timestamp' => now()->toISOString(),
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ] : null,
                'server_info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'timezone' => config('app.timezone'),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erro no Test API', [
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
     * Test PIX generation
     * 
     * POST /api/test/pix
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testPix(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Validar dados
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
            ]);

            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum gateway de pagamento configurado para este usuário'
                ], 400);
            }

            // Dados de teste
            $testData = [
                'amount' => $request->amount ?? 10.00,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'document' => preg_replace('/[^0-9]/', '', $user->document ?? '10563796006'), // CPF válido de teste
                ],
                'external_id' => 'test_' . time() . '_' . uniqid(),
                'description' => 'Teste de geração de PIX',
                'expires_in' => 3600,
            ];

            // Criar serviço de pagamento
            $paymentService = new \App\Services\PaymentGatewayService($user->assignedGateway);

            Log::info('Teste de criação de PIX via API', [
                'user_id' => $user->id,
                'amount' => $testData['amount'],
            ]);

            // Criar transação
            $result = $paymentService->createTransaction($user, $testData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'test_mode' => true,
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'];

            // Verificar dados PIX
            $pixData = $gatewayResponse['payment_data']['pix'] ?? null;
            $pixCode = null;
            $qrCodeBase64 = null;

            if ($pixData) {
                $pixCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null;
                
                // Não gerar QR Code no backend - frontend vai gerar usando JavaScript
                $qrCodeBase64 = null;
            }

            // Verificar integração UTMify do usuário
            $utmifyIntegrations = \App\Models\UtmifyIntegration::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            // Testar envio manual para UTMify (já deve ter sido enviado pelo Observer)
            $utmifySent = false;
            $utmifyError = null;
            $utmifyDetails = [];
            
            try {
                $utmifyService = new \App\Services\UtmifyService();
                $utmifySent = $utmifyService->sendTransaction($transaction, 'created');
                
                foreach ($utmifyIntegrations as $integration) {
                    $utmifyDetails[] = [
                        'id' => $integration->id,
                        'name' => $integration->name,
                        'is_active' => $integration->is_active,
                        'trigger_on_creation' => $integration->trigger_on_creation,
                        'trigger_on_payment' => $integration->trigger_on_payment,
                    ];
                }
            } catch (\Exception $e) {
                $utmifyError = $e->getMessage();
                Log::error('Erro ao testar envio para UTMify', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $utmifyError,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'PIX gerado com sucesso!',
                'test_mode' => true,
                'transaction' => [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'expires_at' => $transaction->expires_at?->toISOString(),
                    'created_at' => $transaction->created_at->toISOString(),
                ],
                'pix' => [
                    'code' => $pixCode,
                    'emv' => $pixCode,
                ],
                'utmify' => [
                    'integrations_found' => $utmifyIntegrations->count(),
                    'sent' => $utmifySent,
                    'error' => $utmifyError,
                    'integrations' => $utmifyDetails,
                    'note' => 'Verifique os logs em storage/logs/laravel.log para mais detalhes',
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erro no Test PIX API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor: ' . $e->getMessage(),
                'test_mode' => true,
            ], 500);
        }
    }

    /**
     * Test transaction creation with product name and description
     * 
     * POST /api/test/transaction
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testTransaction(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não autenticado'
                ], 401);
            }

            // Validar dados - nome e descrição são obrigatórios
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:pix,credit_card,bank_slip',
                'sale_name' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'customer.name' => 'nullable|string|max:255',
                'customer.email' => 'nullable|email|max:255',
                'customer.document' => 'nullable|string|min:11|max:18',
            ], [
                'sale_name.required' => 'O nome do produto é obrigatório.',
                'description.required' => 'A descrição do produto é obrigatória.',
            ]);

            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum gateway de pagamento configurado para este usuário'
                ], 400);
            }

            // Dados de teste
            $testData = [
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'sale_name' => $request->sale_name,
                'description' => $request->description,
                'customer' => [
                    'name' => $request->input('customer.name') ?? $user->name,
                    'email' => $request->input('customer.email') ?? $user->email,
                    'document' => preg_replace('/[^0-9]/', '', $request->input('customer.document') ?? $user->document ?? '10563796006'), // CPF válido de teste
                ],
                'external_id' => 'test_' . time() . '_' . uniqid(),
                'metadata' => [
                    'created_via' => 'api_v2_test',
                    'user_ip' => $request->ip(),
                    'sale_name' => $request->sale_name,
                    'description' => $request->description,
                ],
            ];

            // Adicionar expiração PIX se for PIX
            if ($request->payment_method === 'pix') {
                $testData['pix_expires_in_minutes'] = $request->pix_expires_in_minutes ?? 15;
            }

            // Criar serviço de pagamento
            $paymentService = new \App\Services\PaymentGatewayService($user->assignedGateway);

            Log::info('Teste de criação de transação via API', [
                'user_id' => $user->id,
                'amount' => $testData['amount'],
                'sale_name' => $testData['sale_name'],
                'description' => $testData['description'],
            ]);

            // Criar transação
            $result = $paymentService->createTransaction($user, $testData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                    'test_mode' => true,
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'] ?? [];

            // Extrair dados PIX se for PIX
            $pixData = null;
            if ($request->payment_method === 'pix') {
                $pixData = $gatewayResponse['payment_data']['pix'] ?? 
                          $transaction->payment_data['payment_data']['pix'] ?? 
                          $transaction->payment_data['pix'] ?? null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Transação criada com sucesso!',
                'test_mode' => true,
                'transaction' => [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'expires_at' => $transaction->expires_at?->toISOString(),
                    'created_at' => $transaction->created_at->toISOString(),
                    'product_name' => $transaction->metadata['sale_name'] ?? null,
                    'product_description' => $transaction->metadata['description'] ?? null,
                ],
                'pix' => $pixData ? [
                    'code' => $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null,
                    'emv' => $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? null,
                    'expiration_date' => $pixData['expirationDate'] ?? $pixData['expiration_date'] ?? null,
                ] : null,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Dados inválidos',
                'errors' => $e->errors(),
                'test_mode' => true,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro no Test Transaction API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor: ' . $e->getMessage(),
                'test_mode' => true,
            ], 500);
        }
    }
}
