<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UtmifyService
{
    /**
     * Envia transação para UTMify
     * Suporta PIX, Cartão de Crédito e Boleto
     */
    public function sendTransaction(Transaction $transaction, string $event = 'created'): bool
    {
        try {
            // Extrair método de pagamento padronizado
            $paymentMethod = strtolower($transaction->payment_method);
            
            // FILTRO 1: MÉTODOS DE PAGAMENTO SUPORTADOS PELA UTMIFY
            $allowedMethods = ['pix', 'credit_card', 'bank_slip', 'boleto'];
            if (!in_array($paymentMethod, $allowedMethods)) {
                Log::debug('UTMify: Transação ignorada - método de pagamento não suportado', [
                    'transaction_id' => $transaction->transaction_id,
                    'payment_method' => $transaction->payment_method,
                    'event' => $event,
                ]);
                return false;
            }

            // FILTRO 2: APENAS STATUS PERMITIDOS
            $allowedStatuses = [
                'pending', 'paid', 'approved', 'completed', 'success', 'successful', 'confirmed',
                'refunded', 'partially_refunded', 'chargeback', 'cancelled', 'failed', 'expired'
            ];
            if (!in_array(strtolower($transaction->status), $allowedStatuses)) {
                Log::debug('UTMify: Transação ignorada - status não permitido', [
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                    'event' => $event,
                    'allowed_statuses' => $allowedStatuses,
                ]);
                return false;
            }

            // FILTRO 3: EVENTOS
            // Eventos permitidos:
            // 'created' -> quando a transação é gerada
            // 'paid' -> quando é aprovada/paga
            // 'refunded' -> quando é estornada
            // 'cancelled' -> quando falha ou expira
            $allowedEvents = ['created', 'paid', 'refunded', 'cancelled'];
            if (!in_array($event, $allowedEvents)) {
                Log::debug('UTMify: Evento ignorado - evento não permitido', [
                    'transaction_id' => $transaction->transaction_id,
                    'event' => $event,
                    'allowed_events' => $allowedEvents,
                ]);
                return false;
            }

            Log::info('🟢 UTMify: Iniciando processo de envio', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'event' => $event,
                'amount' => $transaction->amount,
            ]);

            // Buscar integrações ativas:
            // 1. Integrações globais (user_id = null) - capturam TODOS os PIX de todos os usuários
            // 2. Integrações específicas do usuário (user_id = transaction->user_id)
            $integrations = UtmifyIntegration::where(function($query) use ($transaction) {
                    $query->whereNull('user_id') // Integrações globais
                          ->orWhere('user_id', $transaction->user_id); // Integrações do usuário
                })
                ->where('is_active', true)
                ->get();

            if ($integrations->isEmpty()) {
                Log::debug('UTMify: Nenhuma integração ativa encontrada', [
                    'user_id' => $transaction->user_id,
                    'transaction_id' => $transaction->transaction_id,
                    'event' => $event,
                    'note' => 'Nenhuma integração global ou específica do usuário encontrada',
                ]);
                return false;
            }

            $globalIntegrations = $integrations->whereNull('user_id');
            $userIntegrations = $integrations->where('user_id', $transaction->user_id);
            
            Log::info('UTMify: Integrações encontradas', [
                'user_id' => $transaction->user_id,
                'transaction_id' => $transaction->transaction_id,
                'event' => $event,
                'total_integrations' => $integrations->count(),
                'global_integrations' => $globalIntegrations->count(),
                'user_specific_integrations' => $userIntegrations->count(),
                'integration_ids' => $integrations->pluck('id')->toArray(),
                'global_integration_ids' => $globalIntegrations->pluck('id')->toArray(),
                'user_integration_ids' => $userIntegrations->pluck('id')->toArray(),
            ]);

            $successCount = 0;

            foreach ($integrations as $integration) {
                Log::info('🟣 UTMify: Processando integração', [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'integration_type' => $integration->isGlobal() ? 'GLOBAL' : 'USER_SPECIFIC',
                    'user_id' => $integration->user_id,
                    'transaction_user_id' => $transaction->user_id,
                    'is_active' => $integration->is_active,
                    'trigger_on_creation' => $integration->trigger_on_creation,
                    'trigger_on_payment' => $integration->trigger_on_payment,
                    'event' => $event,
                ]);

                // Verificar se deve acionar na criação
                if ($event === 'created' && !$integration->trigger_on_creation) {
                    Log::warning('⚠️ UTMify: Pulando integração - trigger_on_creation DESABILITADO', [
                        'integration_id' => $integration->id,
                        'integration_name' => $integration->name,
                        'user_id' => $integration->user_id,
                        'event' => $event,
                        'trigger_on_creation' => $integration->trigger_on_creation,
                        'note' => 'Habilite trigger_on_creation na integração para enviar quando PIX for gerado',
                    ]);
                    continue;
                }

                // Verificar se deve acionar no pagamento
                if ($event === 'paid' && !$integration->trigger_on_payment) {
                    Log::warning('⚠️ UTMify: Pulando integração - trigger_on_payment DESABILITADO', [
                        'integration_id' => $integration->id,
                        'integration_name' => $integration->name,
                        'user_id' => $integration->user_id,
                        'event' => $event,
                        'trigger_on_payment' => $integration->trigger_on_payment,
                        'note' => 'Habilite trigger_on_payment na integração para enviar quando PIX for pago',
                    ]);
                    continue;
                }

                // Para reembolsos, sempre enviar se integração estiver ativa
                // Preparar payload
                try {
                    $payload = $this->preparePayload($transaction, $integration, $event);
                } catch (\Exception $payloadException) {
                    Log::error('❌ UTMify: Erro ao preparar payload', [
                        'transaction_id' => $transaction->transaction_id,
                        'integration_id' => $integration->id,
                        'error' => $payloadException->getMessage(),
                        'file' => $payloadException->getFile(),
                        'line' => $payloadException->getLine(),
                        'trace' => $payloadException->getTraceAsString(),
                    ]);
                    continue; // Pular para próxima integração
                }
                
                // Log do payload completo (sem token da API)
                Log::info('🟡 UTMify: Payload preparado - Enviando para API', [
                    'integration_id' => $integration->id,
                    'integration_name' => $integration->name,
                    'user_id' => $integration->user_id ?? 'GLOBAL',
                    'transaction_id' => $transaction->transaction_id,
                    'event' => $event,
                    'payload' => [
                        'orderId' => $payload['orderId'],
                        'platform' => $payload['platform'],
                        'paymentMethod' => $payload['paymentMethod'],
                        'status' => $payload['status'],
                        'createdAt' => $payload['createdAt'],
                        'approvedDate' => $payload['approvedDate'],
                        'refundedAt' => $payload['refundedAt'],
                        'customer' => [
                            'name' => $payload['customer']['name'],
                            'email' => $payload['customer']['email'],
                            'phone' => $payload['customer']['phone'] ?? null,
                            'document' => $payload['customer']['document'] ?? null,
                            'country' => $payload['customer']['country'],
                        ],
                        'products' => $payload['products'],
                        'trackingParameters' => $payload['trackingParameters'],
                        'commission' => $payload['commission'],
                    ],
                    'api_endpoint' => 'https://api.utmify.com.br/api-credentials/orders',
                    'api_token_length' => strlen($integration->api_token),
                ]);

                // Enviar para API UTMify
                try {
                    // Limpar token (remover espaços, quebras de linha, etc.)
                    $apiToken = trim($integration->api_token);
                    $apiToken = preg_replace('/\s+/', '', $apiToken);
                    
                    // Se o token foi limpo e é diferente, atualizar no banco
                    if ($apiToken !== $integration->api_token && strlen($apiToken) > 0) {
                        Log::info('🔧 UTMify: Token tinha espaços/quebras de linha, limpando automaticamente', [
                            'integration_id' => $integration->id,
                            'original_length' => strlen($integration->api_token),
                            'cleaned_length' => strlen($apiToken),
                        ]);
                        
                        $integration->api_token = $apiToken;
                        $integration->save();
                    }
                    
                    Log::info('🟢 UTMify: Fazendo requisição HTTP para API', [
                        'transaction_id' => $transaction->transaction_id,
                        'integration_id' => $integration->id,
                        'url' => 'https://api.utmify.com.br/api-credentials/orders',
                        'method' => 'POST',
                        'headers' => [
                            'x-api-token' => substr($apiToken, 0, 10) . '...',
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ],
                        'token_length' => strlen($apiToken),
                    ]);
                    
                    $response = Http::timeout(30)
                        ->withHeaders([
                            'x-api-token' => $apiToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])
                        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
                    
                    Log::info('🟢 UTMify: Resposta recebida da API', [
                        'transaction_id' => $transaction->transaction_id,
                        'integration_id' => $integration->id,
                        'status_code' => $response->status(),
                        'response_body_preview' => substr($response->body(), 0, 500),
                    ]);

                    $responseStatus = $response->status();
                    $responseBody = $response->body();
                    $isSuccessful = $response->successful();
                    
                    // Log detalhado da resposta
                    Log::info('📡 UTMify: Resposta da API analisada', [
                        'transaction_id' => $transaction->transaction_id,
                        'integration_id' => $integration->id,
                        'response_status' => $responseStatus,
                        'is_successful' => $isSuccessful,
                        'response_body' => $responseBody,
                        'response_body_length' => strlen($responseBody),
                    ]);
                    
                    if ($isSuccessful) {
                        $successCount++;
                        Log::info('✅ UTMify: Transação enviada com SUCESSO para a API', [
                            'transaction_id' => $transaction->transaction_id,
                            'integration_id' => $integration->id,
                            'integration_name' => $integration->name,
                            'user_id' => $integration->user_id,
                            'event' => $event,
                            'response_status' => $responseStatus,
                            'response_body' => $responseBody,
                            'api_endpoint' => 'https://api.utmify.com.br/api-credentials/orders',
                        ]);
                    } else {
                        // Parsear erro da API
                        $errorData = null;
                        $errorMessage = 'Erro desconhecido';
                        
                        try {
                            $errorData = json_decode($responseBody, true);
                            if ($errorData) {
                                $errorMessage = $errorData['message'] ?? 'Erro desconhecido';
                                
                                // Se for erro de credencial, marcar integração como problema
                                if ($errorMessage === 'API_CREDENTIAL_NOT_FOUND' || $responseStatus === 404) {
                                    Log::error('🔴 UTMify: Token da API inválido ou não encontrado', [
                                        'transaction_id' => $transaction->transaction_id,
                                        'integration_id' => $integration->id,
                                        'integration_name' => $integration->name,
                                        'user_id' => $integration->user_id,
                                        'error' => 'API_CREDENTIAL_NOT_FOUND',
                                        'response_status' => $responseStatus,
                                        'response_body' => $responseBody,
                                        'api_token_preview' => substr($integration->api_token, 0, 10) . '...',
                                        'api_token_length' => strlen($integration->api_token),
                                        'note' => 'O token da API UTMify está inválido ou não existe. Verifique em https://utmify.com.br',
                                    ]);
                                } else {
                                    Log::error('❌ UTMify: Erro ao enviar transação para API', [
                                        'transaction_id' => $transaction->transaction_id,
                                        'integration_id' => $integration->id,
                                        'integration_name' => $integration->name,
                                        'user_id' => $integration->user_id,
                                        'event' => $event,
                                        'response_status' => $responseStatus,
                                        'response_body' => $responseBody,
                                        'error_message' => $errorMessage,
                                        'error_data' => $errorData,
                                        'payload_preview' => [
                                            'orderId' => $payload['orderId'],
                                            'platform' => $payload['platform'],
                                            'paymentMethod' => $payload['paymentMethod'],
                                            'status' => $payload['status'],
                                            'customer_email' => $payload['customer']['email'] ?? 'N/A',
                                            'customer_name' => $payload['customer']['name'] ?? 'N/A',
                                            'totalPriceInCents' => $payload['commission']['totalPriceInCents'],
                                            'userCommissionInCents' => $payload['commission']['userCommissionInCents'],
                                            'gatewayFeeInCents' => $payload['commission']['gatewayFeeInCents'],
                                            'products_count' => count($payload['products']),
                                        ],
                                        'api_endpoint' => 'https://api.utmify.com.br/api-credentials/orders',
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error('❌ UTMify: Erro ao parsear resposta da API', [
                                'transaction_id' => $transaction->transaction_id,
                                'integration_id' => $integration->id,
                                'response_status' => $responseStatus,
                                'response_body' => $responseBody,
                                'parse_error' => $e->getMessage(),
                            ]);
                        }
                    }
                } catch (\Exception $httpException) {
                    Log::error('❌ UTMify: Exceção HTTP ao enviar para API', [
                        'transaction_id' => $transaction->transaction_id,
                        'integration_id' => $integration->id,
                        'integration_name' => $integration->name,
                        'user_id' => $integration->user_id,
                        'error' => $httpException->getMessage(),
                        'file' => $httpException->getFile(),
                        'line' => $httpException->getLine(),
                        'trace' => $httpException->getTraceAsString(),
                    ]);
                }
            }

            if ($successCount > 0) {
                Log::info('✅ UTMify: Envio concluído com sucesso', [
                    'transaction_id' => $transaction->transaction_id,
                    'user_id' => $transaction->user_id,
                    'success_count' => $successCount,
                    'total_integrations' => $integrations->count(),
                ]);
            } else {
                Log::warning('⚠️ UTMify: Nenhuma integração enviou com sucesso', [
                    'transaction_id' => $transaction->transaction_id,
                    'user_id' => $transaction->user_id,
                    'total_integrations' => $integrations->count(),
                    'integrations_processed' => $integrations->map(function($integration) {
                        return [
                            'id' => $integration->id,
                            'name' => $integration->name,
                            'is_active' => $integration->is_active,
                            'trigger_on_creation' => $integration->trigger_on_creation,
                            'trigger_on_payment' => $integration->trigger_on_payment,
                        ];
                    })->toArray(),
                    'event' => $event,
                    'note' => 'Verifique os logs anteriores para ver por que nenhuma integração enviou. Pode ser: trigger desabilitado, erro na API, ou payload inválido.',
                ]);
            }

            return $successCount > 0;
        } catch (\Exception $e) {
            Log::error('UTMify: ❌ Exceção ao processar transação', [
                'transaction_id' => $transaction->transaction_id ?? 'N/A',
                'event' => $event,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Prepara o payload para a API UTMify
     */
    private function preparePayload(Transaction $transaction, UtmifyIntegration $integration, string $event = 'created'): array
    {
        // Nome da plataforma sempre será "playpayments"
        $customerData = $transaction->customer_data ?? [];
        $paymentData = $transaction->payment_data ?? [];
        $metadata = $transaction->metadata ?? [];

        // Mapear status baseado no evento/status_da_transacao
        $utmifyStatus = match ($event) {
            'created' => 'waiting_payment',
            'paid' => 'paid',
            'refunded' => 'refunded',
            'cancelled' => 'cancelled',
            default => 'waiting_payment'
        };

        // Mapear método de pagamento para o formato da UTMify
        $pm = strtolower($transaction->payment_method);
        $utmifyPaymentMethod = match ($pm) {
            'credit_card' => 'credit_card',
            'bank_slip', 'boleto' => 'bank_slip',
            default => 'pix'
        };

        // Extrair parâmetros de tracking (UTM) do metadata ou payment_data
        $trackingParams = $this->extractTrackingParameters($metadata, $paymentData);

        // Carregar relacionamento do usuário se não estiver carregado
        if (!$transaction->relationLoaded('user')) {
            $transaction->load('user');
        }
        
        // Preparar dados do cliente - CAMPOS OBRIGATÓRIOS: name e email
        $customerName = $customerData['name'] ?? null;
        $customerEmail = $customerData['email'] ?? null;
        
        // Se não tem nos dados do cliente, pegar do usuário
        if (empty($customerName) && $transaction->user) {
            $customerName = $transaction->user->name;
        }
        if (empty($customerEmail) && $transaction->user) {
            $customerEmail = $transaction->user->email;
        }
        
        // Fallback para valores padrão se ainda estiver vazio
        if (empty($customerName)) {
            $customerName = 'Cliente';
        }
        if (empty($customerEmail)) {
            $customerEmail = 'cliente@example.com';
            Log::warning('UTMify: Email do cliente vazio, usando valor padrão', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
            ]);
        }
        
        // Preparar customer
        // IMPORTANTE: phone e document são OBRIGATÓRIOS (podem ser null)
        // Mas ip deve ser omitido se for null
        $customer = [
            'name' => $customerName,
            'email' => $customerEmail,
            'country' => $customerData['country'] ?? 'BR',
            'phone' => $customerData['phone'] ?? null,
            'document' => $customerData['document'] ?? null,
        ];
        
        // Adicionar ip apenas se não for null (API UTMify não aceita ip null)
        if (!empty($customerData['ip'])) {
            $customer['ip'] = $customerData['ip'];
        }

        // Verificar se é depósito
        $isDeposit = isset($metadata['is_deposit']) && $metadata['is_deposit'] === true;
        
        // Verificar se é integração global (admin)
        $isGlobalIntegration = $integration->isGlobal(); // user_id = null
        
        // Preparar produtos
        // IMPORTANTE: Priorizar NOME DO PRODUTO, não descrição da transação
        // Para integrações globais (admin), usar nome do gateway
        // Para integrações de usuário, usar nome do produto
        $productName = null;
        
        if ($isDeposit) {
            $productName = 'Depósito';
        } elseif ($isGlobalIntegration) {
            // Para integrações globais (admin), usar nome do gateway
            if ($transaction->relationLoaded('gateway') && $transaction->gateway) {
                $productName = $transaction->gateway->name ?? 'Gateway';
            } else {
                $transaction->load('gateway');
                $productName = $transaction->gateway->name ?? 'Gateway';
            }
        } else {
            // Para integrações de usuário, usar nome do produto
            // PRIORIDADE 1: Verificar se há produtos no array products (nome/título do produto)
            $products = $transaction->products ?? [];
            if (!empty($products) && is_array($products) && isset($products[0])) {
                $firstProduct = $products[0];
                // Priorizar title (nome/título) e name, depois description
                $productName = $firstProduct['title'] ?? $firstProduct['name'] ?? null;
                
                // Se não encontrou title ou name, usar description do produto (não da transação)
                if (empty($productName)) {
                    $productName = $firstProduct['description'] ?? null;
                }
            }
            
            // PRIORIDADE 2: Verificar metadata['sale_name'] (nome da venda/produto)
            // Este é o nome configurado pelo usuário ao criar a venda
            if (empty($productName)) {
                $productName = $metadata['sale_name'] ?? null;
            }
            
            // PRIORIDADE 3: Verificar metadata['product_name'] (nome do produto no metadata)
            if (empty($productName)) {
                $productName = $metadata['product_name'] ?? null;
            }
            
            // PRIORIDADE 4: Verificar description no metadata (descrição, não ideal mas aceitável)
            // IMPORTANTE: Não usar como primeira opção, apenas como fallback
            if (empty($productName)) {
                $productName = $metadata['description'] ?? null;
            }
            
            // PRIORIDADE 5: Se ainda não encontrou, usar padrão genérico (só como último recurso)
            if (empty($productName) || trim($productName) === '') {
                $productName = 'Produto/Serviço';
            }
        }
        
        // Garantir que o valor do produto está em centavos
        $productPriceInCents = (int)round($transaction->amount * 100);
        
        // Preparar produto
        // IMPORTANTE: planId e planName devem ser null (não omitidos) quando não existirem
        $product = [
            'id' => $transaction->transaction_id,
            'name' => $productName,
            'planId' => $metadata['plan_id'] ?? null,
            'planName' => $metadata['plan_name'] ?? null,
            'quantity' => isset($metadata['quantity']) ? (int)$metadata['quantity'] : 1,
            'priceInCents' => $productPriceInCents,
        ];
        
        $products = [$product];

        // Preparar comissão - IMPORTANTE: userCommissionInCents não pode ser 0
        $totalPriceInCents = (int)round($transaction->amount * 100);
        $gatewayFeeInCents = (int)round(($transaction->fee_amount ?? 0) * 100);
        $userCommissionInCents = (int)round(($transaction->net_amount ?? 0) * 100);
        
        // Se userCommissionInCents for 0 ou negativo, usar totalPriceInCents (conforme documentação)
        if ($userCommissionInCents <= 0) {
            $userCommissionInCents = $totalPriceInCents;
        }
        
        // Se gatewayFeeInCents for maior que totalPriceInCents, ajustar
        if ($gatewayFeeInCents > $totalPriceInCents) {
            $gatewayFeeInCents = 0;
            $userCommissionInCents = $totalPriceInCents;
        }

        $commission = [
            'totalPriceInCents' => $totalPriceInCents,
            'gatewayFeeInCents' => $gatewayFeeInCents,
            'userCommissionInCents' => $userCommissionInCents,
        ];

        // Adicionar moeda se não for BRL
        if ($transaction->currency && $transaction->currency !== 'BRL') {
            $commission['currency'] = $transaction->currency;
        }

        // Preparar tracking parameters
        // IMPORTANTE: trackingParameters deve ser um objeto, mesmo que vazio
        // Campos opcionais podem ser null, mas o objeto não pode estar vazio ou ser array vazio
        $trackingParameters = [
            'src' => $trackingParams['src'] ?? null,
            'sck' => $trackingParams['sck'] ?? null,
            'utm_source' => $trackingParams['utm_source'] ?? null,
            'utm_campaign' => $trackingParams['utm_campaign'] ?? null,
            'utm_medium' => $trackingParams['utm_medium'] ?? null,
            'utm_content' => $trackingParams['utm_content'] ?? null,
            'utm_term' => $trackingParams['utm_term'] ?? null,
        ];
        
        // Preparar payload - usar UTC para as datas
        // IMPORTANTE: approvedDate e refundedAt devem ser null (não omitidos) quando não existirem
        // Mas customer.ip, phone, document devem ser omitidos se null
        // Nome da plataforma sempre será "playpayments"
        $payload = [
            'orderId' => $transaction->transaction_id,
            'platform' => 'playpayments',
            'paymentMethod' => $utmifyPaymentMethod,
            'status' => $utmifyStatus,
            'createdAt' => $transaction->created_at->utc()->format('Y-m-d H:i:s'),
            'approvedDate' => $transaction->paid_at ? $transaction->paid_at->utc()->format('Y-m-d H:i:s') : null,
            'refundedAt' => $transaction->refunded_at ? $transaction->refunded_at->utc()->format('Y-m-d H:i:s') : null,
            'customer' => $customer,
            'products' => $products,
            'trackingParameters' => $trackingParameters,
            'commission' => $commission,
        ];

        return $payload;
    }

    /**
     * Obtém o nome da plataforma
     * Sempre retorna "playpayments"
     */
    private function getPlatformName(UtmifyIntegration $integration = null): string
    {
        // Sempre retornar "playpayments" como nome da plataforma
        return 'playpayments';
    }

    /**
     * Extrai parâmetros de tracking (UTM) do metadata ou payment_data
     */
    private function extractTrackingParameters(array $metadata, array $paymentData): array
    {
        // Procurar parâmetros UTM no metadata
        $utmSource = $metadata['utm_source'] ?? $metadata['src'] ?? null;
        $utmCampaign = $metadata['utm_campaign'] ?? null;
        $utmMedium = $metadata['utm_medium'] ?? null;
        $utmContent = $metadata['utm_content'] ?? null;
        $utmTerm = $metadata['utm_term'] ?? null;
        $src = $metadata['src'] ?? null;
        $sck = $metadata['sck'] ?? null;

        // Se não encontrou no metadata, procurar no payment_data
        if (!$utmSource && isset($paymentData['utm_source'])) {
            $utmSource = $paymentData['utm_source'];
        }
        if (!$utmCampaign && isset($paymentData['utm_campaign'])) {
            $utmCampaign = $paymentData['utm_campaign'];
        }
        if (!$utmMedium && isset($paymentData['utm_medium'])) {
            $utmMedium = $paymentData['utm_medium'];
        }
        if (!$utmContent && isset($paymentData['utm_content'])) {
            $utmContent = $paymentData['utm_content'];
        }
        if (!$utmTerm && isset($paymentData['utm_term'])) {
            $utmTerm = $paymentData['utm_term'];
        }
        if (!$src && isset($paymentData['src'])) {
            $src = $paymentData['src'];
        }
        if (!$sck && isset($paymentData['sck'])) {
            $sck = $paymentData['sck'];
        }

        return [
            'src' => $src,
            'sck' => $sck,
            'utm_source' => $utmSource,
            'utm_campaign' => $utmCampaign,
            'utm_medium' => $utmMedium,
            'utm_content' => $utmContent,
            'utm_term' => $utmTerm,
        ];
    }
}
