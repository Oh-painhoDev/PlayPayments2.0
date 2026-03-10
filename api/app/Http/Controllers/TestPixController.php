<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\UtmifyService;

class TestPixController extends Controller
{
    /**
     * Criar PIX de teste para verificar integração UTMify
     */
    public function createTestPix(Request $request)
    {
        try {
            // Buscar usuário (pode ser por ID ou usar o autenticado)
            $userId = $request->input('user_id') ?? Auth::id();
            
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'error' => 'user_id é obrigatório ou usuário deve estar autenticado'
                ], 400);
            }

            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não encontrado'
                ], 404);
            }

            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Usuário não tem gateway configurado'
                ], 400);
            }

            // Valor padrão ou do request
            $amount = $request->input('amount', 10.00);
            
            // Tempo de expiração PIX (em minutos)
            $pixExpiresInMinutes = $request->input('pix_expires_in_minutes', 15); // Default: 15 minutos

            // Preparar dados da transação
            $transactionData = [
                'amount' => $amount,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'document' => preg_replace('/[^0-9]/', '', $user->document ?? '00000000000'),
                    'phone' => preg_replace('/[^0-9]/', '', $user->whatsapp ?? null),
                ],
                'external_id' => 'TEST_PIX_' . time() . '_' . uniqid(),
                'description' => 'PIX de Teste - UTMify',
                'metadata' => [
                    'product_name' => 'Produto de Teste',
                    'test' => true,
                ],
                'pix_expires_in_minutes' => (int)$pixExpiresInMinutes,
                'expires_in' => (int)$pixExpiresInMinutes * 60, // Também enviar em segundos para compatibilidade
            ];

            Log::info('🧪 TEST PIX: Criando transação de teste', [
                'user_id' => $user->id,
                'amount' => $amount,
                'pix_expires_in_minutes' => $pixExpiresInMinutes,
                'external_id' => $transactionData['external_id'],
            ]);

            // Criar serviço de pagamento
            $paymentService = new PaymentGatewayService($user->assignedGateway);

            // Verificar se está configurado
            if (!$paymentService->isConfigured()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Gateway não está configurado'
                ], 400);
            }

            // Criar transação
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro ao criar transação'
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'];

            // Verificar se tem código PIX
            $pixData = $gatewayResponse['payment_data']['pix'] ?? [];
            $pixCode = $pixData['emv'] ?? $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['code'] ?? null;

            // Verificar integração UTMify do usuário
            $utmifyIntegrations = \App\Models\UtmifyIntegration::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            // Testar envio manual para UTMify
            $utmifySent = false;
            $utmifyError = null;
            $utmifyDebug = [];
            $utmifyApiError = null;
            $utmifyApiStatus = null;
            
            try {
                // Recarregar a transação do banco para ter dados atualizados
                $transaction->refresh();
                
                Log::info('🧪 TEST PIX: Verificando transação antes de enviar para UTMify', [
                    'transaction_id' => $transaction->transaction_id,
                    'user_id' => $transaction->user_id,
                    'payment_method' => $transaction->payment_method,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                ]);
                
                $utmifyService = new UtmifyService();
                
                // Verificar se passa pelos filtros
                $utmifyDebug['payment_method'] = $transaction->payment_method;
                $utmifyDebug['status'] = $transaction->status;
                $utmifyDebug['is_pix'] = strtolower($transaction->payment_method) === 'pix';
                $utmifyDebug['status_allowed'] = in_array(strtolower($transaction->status), ['pending', 'paid', 'refunded', 'partially_refunded']);
                $utmifyDebug['integrations_count'] = $utmifyIntegrations->count();
                
                // Aguardar um pouco para o log ser escrito (se houver)
                sleep(1);
                
                // Tentar capturar resposta da API dos logs mais recentes
                $lastLogFile = storage_path('logs/laravel.log');
                if (file_exists($lastLogFile)) {
                    // Ler últimas 50 linhas do log
                    $logLines = file($lastLogFile);
                    $lastLines = array_slice($logLines, -50);
                    $logContent = implode('', $lastLines);
                    
                    // Buscar última resposta da API UTMify para esta transação
                    $pattern = '/"response_status":(\d+).*?"response_body":"({[^}]+API_CREDENTIAL[^}]+})"/';
                    if (preg_match($pattern, $logContent, $matches)) {
                        $utmifyApiStatus = (int)$matches[1];
                        $responseBodyStr = stripslashes($matches[2]);
                        $responseBody = json_decode($responseBodyStr, true);
                        if ($responseBody) {
                            $utmifyApiError = $responseBody['message'] ?? null;
                            if ($utmifyApiStatus === 404 && $utmifyApiError === 'API_CREDENTIAL_NOT_FOUND') {
                                $utmifyError = 'Token da API UTMify inválido ou não encontrado (404). O token no banco de dados não é reconhecido pela API UTMify.';
                            }
                        }
                    }
                }
                
                $utmifySent = $utmifyService->sendTransaction($transaction, 'created');
                
                $utmifyDebug['sent_result'] = $utmifySent;
                if ($utmifyApiStatus) {
                    $utmifyDebug['api_status'] = $utmifyApiStatus;
                }
                if ($utmifyApiError) {
                    $utmifyDebug['api_error'] = $utmifyApiError;
                }
                
                Log::info('🧪 TEST PIX: Resultado do envio para UTMify', [
                    'transaction_id' => $transaction->transaction_id,
                    'sent' => $utmifySent,
                    'debug' => $utmifyDebug,
                ]);
            } catch (\Exception $e) {
                $utmifyError = $e->getMessage();
                $utmifyDebug['exception'] = $e->getMessage();
                $utmifyDebug['exception_file'] = $e->getFile();
                $utmifyDebug['exception_line'] = $e->getLine();
                
                Log::error('🧪 TEST PIX: Erro ao enviar para UTMify', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $utmifyError,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Preparar resposta UTMify
            $utmifyResponse = [
                'integrations_found' => $utmifyIntegrations->count(),
                'sent' => $utmifySent,
                'error' => $utmifyError,
                'debug' => $utmifyDebug,
                'integrations' => $utmifyIntegrations->map(function($integration) {
                    return [
                        'id' => $integration->id,
                        'name' => $integration->name,
                        'user_id' => $integration->user_id,
                        'is_active' => $integration->is_active,
                        'trigger_on_creation' => $integration->trigger_on_creation,
                        'trigger_on_payment' => $integration->trigger_on_payment,
                        'api_token_preview' => substr($integration->api_token, 0, 10) . '...',
                    ];
                }),
                'transaction_user_id' => $transaction->user_id,
            ];
            
            // Adicionar mensagem de ajuda se houver erro de token
            if (($utmifyApiStatus === 404 && $utmifyApiError === 'API_CREDENTIAL_NOT_FOUND') || 
                ($utmifyError && strpos($utmifyError, 'inválido') !== false)) {
                $utmifyResponse['help'] = [
                    'problem' => 'Token da API UTMify inválido ou não encontrado (404)',
                    'current_token' => substr($utmifyIntegrations->first()->api_token ?? '', 0, 20) . '...',
                    'solution' => [
                        '1. Acesse https://utmify.com.br e faça login',
                        '2. Vá em: Integrações > Webhooks > Credenciais de API',
                        '3. Verifique se há uma credencial ativa',
                        '4. Se não houver ou estiver inativa, crie uma nova credencial',
                        '5. Copie o token EXATO (sem espaços no início/fim)',
                        '6. Atualize no banco: UPDATE utmify_integrations SET api_token = \'NOVO_TOKEN\' WHERE id = 2;',
                        '7. OU use o script: php public/update-utmify-token.php "NOVO_TOKEN"',
                        '8. Teste novamente criando um novo PIX',
                    ],
                    'note' => 'O código está funcionando corretamente. O problema é apenas que o token no banco de dados não é válido na plataforma UTMify.',
                ];
                $utmifyResponse['status'] = 'token_invalid';
            } elseif ($utmifySent) {
                $utmifyResponse['status'] = 'success';
            } else {
                $utmifyResponse['status'] = 'failed';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'PIX de teste criado com sucesso',
                'transaction' => [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'expires_at' => $transaction->expires_at?->toISOString(),
                    'created_at' => $transaction->created_at->toISOString(),
                ],
                'pix' => [
                    'code' => $pixCode,
                    'emv' => $pixCode,
                    'expires_in_minutes' => $pixExpiresInMinutes,
                ],
                'utmify' => $utmifyResponse,
                'logs' => [
                    'check' => 'Verifique os logs em storage/logs/laravel.log',
                    'search_for' => 'UTMify: ou TransactionObserver:',
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('🧪 TEST PIX: Erro ao criar PIX de teste', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao criar PIX de teste: ' . $e->getMessage()
            ], 500);
        }
    }
}

