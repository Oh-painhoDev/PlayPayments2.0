<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SharkgatewayGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Sharkgateway (Payshark)
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway Sharkgateway não está configurado');
            }

            // Calculate fees
            $feeData = $this->calculateFee(
                $user, 
                $data['amount'], 
                $data['payment_method'], 
                $data['installments'] ?? 1
            );

            // Create transaction record
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->gateway_id = $this->gateway->id;
            $transaction->transaction_id = $this->generateTransactionId();
            $transaction->amount = $data['amount'];
            $transaction->fee_amount = $feeData['fee_amount'];
            $transaction->net_amount = $feeData['net_amount'];
            $transaction->currency = 'BRL';
            $transaction->payment_method = $data['payment_method'];
            $transaction->status = 'pending';
            $transaction->customer_data = $data['customer'];
            $transaction->metadata = array_merge($data['metadata'] ?? [], [
                'postbackUrl' => $data['postbackUrl'] ?? null,
                'redirectUrl' => $data['redirect_url'] ?? null,
            ]);
            
            // Store products if provided
            if (isset($data['products']) && is_array($data['products'])) {
                $transaction->products = $data['products'];
            } elseif (isset($data['items']) && is_array($data['items'])) {
                // Support 'items' as alias for 'products'
                $transaction->products = $data['items'];
            }
            
            // Set transaction expiration based on PIX expiration if provided, otherwise default to 24 hours
            if ($data['payment_method'] === 'pix') {
                $expiresInMinutes = null;
                
                if (isset($data['pix_expires_in_minutes']) && is_numeric($data['pix_expires_in_minutes'])) {
                    $expiresInMinutes = (int)$data['pix_expires_in_minutes'];
                } elseif (isset($data['expires_in']) && is_numeric($data['expires_in'])) {
                    // Convert seconds to minutes
                    $expiresInMinutes = (int)ceil($data['expires_in'] / 60);
                }
                
                // Default to 15 minutes if not specified
                // Maximum 90 days (129,600 minutes) as per API limit
                if ($expiresInMinutes === null || $expiresInMinutes < 1) {
                    $expiresInMinutes = 15; // 15 minutes default
                }
                
                // Cap at 90 days (129,600 minutes)
                $maxMinutes = 90 * 1440; // 129,600 minutes = 90 days
                if ($expiresInMinutes > $maxMinutes) {
                    $expiresInMinutes = $maxMinutes;
                }
                
                $transaction->expires_at = now()->addMinutes($expiresInMinutes);
            } else {
                $transaction->expires_at = now()->addHours(24);
            }

            // Process retention before saving
            $retentionService = new RetentionService();
            $shouldRetain = $retentionService->processTransaction($transaction);

            $transaction->save();

            // Prepare Sharkgateway payload baseado no backup funcional
            $payload = $this->prepareSharkgatewayPayload($transaction, $data);

            // Log detalhado do payload PIX para depuração
            if ($transaction->payment_method === 'pix' && isset($payload['pix'])) {
                \Log::info('🔵 PAYLOAD PIX ENVIADO PARA API', [
                    'transaction_id' => $transaction->transaction_id,
                    'pix_config' => $payload['pix'],
                    'expires_at_calculated' => $transaction->expires_at?->toDateTimeString(),
                    'expires_at_timestamp' => $transaction->expires_at?->timestamp,
                ]);
            }

            // Send request to Sharkgateway
            $auth = base64_encode($this->credentials->public_key . ':' . $this->credentials->secret_key);
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'Authorization' => 'Basic ' . $auth,
                ])
                ->post($this->gateway->getApiUrl('/transactions'), $payload);

            $this->logRequest('create_transaction', $payload, $response->json());

            if (!$response->successful()) {
                throw new \Exception('Erro na API Sharkgateway: ' . $response->body());
            }

            $responseData = $response->json();

            // Format response baseado no backup funcional
            $formattedResponse = $this->formatSharkgatewayResponse($responseData, $transaction);

            // Se a API retornou uma data de expiração, usar ela (a API pode ter seu próprio cálculo)
            $apiExpirationDate = null;
            if (isset($responseData['pix']['expirationDate']) && !empty($responseData['pix']['expirationDate'])) {
                try {
                    // A API pode retornar apenas a data (YYYY-MM-DD) ou data/hora completa
                    $apiExpirationDate = \Carbon\Carbon::parse($responseData['pix']['expirationDate']);
                    
                    \Log::info('🔵 API retornou expirationDate diferente', [
                        'transaction_id' => $transaction->transaction_id,
                        'expires_at_calculated' => $transaction->expires_at?->toDateTimeString(),
                        'expirationDate_from_api' => $responseData['pix']['expirationDate'],
                        'expirationDate_parsed' => $apiExpirationDate->toDateTimeString(),
                        'difference_minutes' => $transaction->expires_at ? $transaction->expires_at->diffInMinutes($apiExpirationDate) : null,
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Erro ao parsear expirationDate da API', [
                        'transaction_id' => $transaction->transaction_id,
                        'expirationDate' => $responseData['pix']['expirationDate'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Update transaction with external ID and payment data
            // Se a API retornou expirationDate, usar ela (pode ser diferente do que enviamos)
            $updateData = [
                'external_id' => $responseData['id'] ?? null,
                'payment_data' => $formattedResponse['payment_data'],
            ];
            
            // REGRA CRÍTICA: SharkPay é a ADQUIRENTE - ela decide quando o PIX expira
            // A data de expiração retornada pela API é a VERDADE ABSOLUTA
            // Devemos SEMPRE usar a expirationDate que a API retorna para manter sincronização
            if ($apiExpirationDate && $apiExpirationDate->isFuture()) {
                $expectedExpiresAt = $transaction->expires_at;
                $differenceInHours = $expectedExpiresAt ? $expectedExpiresAt->diffInHours($apiExpirationDate) : null;
                $differenceInMinutes = $expectedExpiresAt ? $expectedExpiresAt->diffInMinutes($apiExpirationDate) : null;
                
                // Calcular quantos minutos foram solicitados originalmente
                $originalMinutes = null;
                if (isset($data['pix_expires_in_minutes']) && is_numeric($data['pix_expires_in_minutes'])) {
                    $originalMinutes = (int)$data['pix_expires_in_minutes'];
                } elseif ($expectedExpiresAt && $transaction->created_at) {
                    // Calcular a partir da diferença entre expires_at e created_at
                    $originalMinutes = (int)$expectedExpiresAt->diffInMinutes($transaction->created_at);
                }
                
                // IMPORTANTE: SEMPRE usar a data da API (SharkPay é a adquirente - fonte da verdade)
                // Mesmo que seja diferente do que enviamos, a API decide quando o PIX realmente expira
                $updateData['expires_at'] = $apiExpirationDate;
                
                \Log::info('✅ Atualizando expires_at com data da SharkPay (adquirente - fonte da verdade)', [
                    'transaction_id' => $transaction->transaction_id,
                    'original_minutes_solicitados' => $originalMinutes,
                    'expires_at_calculado_local' => $expectedExpiresAt?->toDateTimeString(),
                    'expires_at_da_api_sharkpay' => $apiExpirationDate->toDateTimeString(),
                    'difference_minutes' => $differenceInMinutes,
                    'difference_hours' => $differenceInHours,
                    'action' => 'Usando expirationDate da SharkPay para manter sincronização',
                    'reason' => 'SharkPay é adquirente - data da API é a verdade absoluta',
                ]);
                
                // Se houver diferença grande, logar como warning mas ainda assim usar a data da API
                if ($differenceInHours && $differenceInHours > 1) {
                    \Log::warning('⚠️ SharkPay retornou expirationDate diferente do solicitado, mas usaremos a data da API (adquirente)', [
                        'transaction_id' => $transaction->transaction_id,
                        'original_minutes_solicitados' => $originalMinutes,
                        'expires_at_calculado_local' => $expectedExpiresAt?->toDateTimeString(),
                        'expires_at_da_api_sharkpay' => $apiExpirationDate->toDateTimeString(),
                        'difference_hours' => $differenceInHours,
                        'difference_minutes' => $differenceInMinutes,
                        'action' => 'Usando expirationDate da SharkPay mesmo sendo diferente',
                        'reason' => 'SharkPay (adquirente) decide quando o PIX expira - manter sincronização',
                    ]);
                }
            }
            
            $transaction->update($updateData);

            // Dispatch webhook event for transaction.created
            // This ensures ALL transactions trigger webhooks, even if created directly in gateways
            $this->dispatchTransactionCreatedWebhook($transaction);

            return [
                'success' => true,
                'transaction' => $transaction,
                'gateway_response' => $formattedResponse,
            ];

        } catch (\Exception $e) {
            $this->logError('create_transaction', $e->getMessage(), $data);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare Sharkgateway payload baseado no backup funcional
     */
    private function prepareSharkgatewayPayload(Transaction $transaction, array $data): array
    {
        $customer = $transaction->customer_data;
        
        // Configure webhook URL
        $webhookUrl = config('app.url') . '/webhook/sharkgateway';
        
        // If we're in production, use the full domain URL
        if (app()->environment('production') || app()->environment('staging')) {
            $webhookUrl = 'https://app.playpayments.pro/webhook/sharkgateway';
        }

        $payload = [
            'amount' => (int)round($transaction->amount * 100), // Convert to cents with proper rounding
            'paymentMethod' => $transaction->payment_method,
            'customer' => [
                'name' => $customer['name'],
                'email' => $customer['email'],
                'document' => [
                    'type' => $this->getDocumentType($customer['document']),
                    'number' => preg_replace('/[^0-9]/', '', $customer['document'])
                ]
            ],
            'items' => $this->prepareItems($transaction, $data),
            'postbackUrl' => $webhookUrl,
            'externalRef' => $transaction->transaction_id
        ];

        // Add PIX expiration configuration if payment method is PIX
        if ($transaction->payment_method === 'pix') {
            // Get expiration time in minutes (default: 1440 minutes = 1 day)
            // Accepts: pix_expires_in_minutes or expires_in (in seconds, convert to minutes)
            $expiresInMinutes = null;
            
            if (isset($data['pix_expires_in_minutes']) && is_numeric($data['pix_expires_in_minutes'])) {
                $expiresInMinutes = (int)$data['pix_expires_in_minutes'];
            } elseif (isset($data['expires_in']) && is_numeric($data['expires_in'])) {
                // Convert seconds to minutes
                $expiresInMinutes = (int)ceil($data['expires_in'] / 60);
            }
            
            // Default to 15 minutes if not specified
            if ($expiresInMinutes === null || $expiresInMinutes < 1) {
                $expiresInMinutes = 15; // 15 minutes default
            }
            
            // Calculate expiration in seconds (for expiresIn field)
            $expiresInSeconds = $expiresInMinutes * 60;
            
            // Calculate days from minutes (for expiresInDays field)
            // Use floor() to get exact days when >= 1440 minutes (1 day)
            $expiresInDaysDecimal = $expiresInMinutes / 1440;
            $expiresInDays = (int)floor($expiresInDaysDecimal); // Use floor for exact days
            
            // If exactly 1 day or more, use the calculated days
            // If less than 1 day but user wants days, ensure minimum 1 day
            if ($expiresInMinutes >= 1440 && $expiresInDays < 1) {
                $expiresInDays = 1;
            }
            
            // Ensure maximum of 90 days (API requirement)
            $maxMinutes = 90 * 1440; // 129,600 minutes = 90 days
            if ($expiresInMinutes > $maxMinutes) {
                $expiresInMinutes = $maxMinutes;
                $expiresInSeconds = $maxMinutes * 60;
                $expiresInDays = 90;
            }
            
            if ($expiresInDays > 90) {
                $expiresInDays = 90;
            }
            
            // IMPORTANTE: Para SharkBanking API
            // - Valores < 1 dia (< 1440 minutos): usar expiresIn em SEGUNDOS
            // - Valores >= 1 dia (>= 1440 minutos): usar expiresInDays em DIAS INTEIROS (1-90)
            
            $pixConfig = [];
            
            if ($expiresInMinutes < 1440) {
                // Menos de 1 dia: SEMPRE usar expiresIn em SEGUNDOS
                // Exemplo: 15 minutos = 900 segundos, 30 minutos = 1800 segundos
                // VALIDAÇÃO: Garantir que está correto
                if ($expiresInSeconds < 60) {
                    // Mínimo 1 minuto = 60 segundos
                    $expiresInSeconds = 60;
                    $expiresInMinutes = 1;
                    \Log::warning('⚠️ Valor de expiração muito baixo, ajustando para 1 minuto (60 segundos)', [
                        'transaction_id' => $transaction->transaction_id,
                        'original_seconds' => $expiresInSeconds,
                    ]);
                }
                
                $pixConfig['expiresIn'] = $expiresInSeconds;
                
                \Log::info('🔵 SharkBanking PIX expiration (using seconds) - CRÍTICO', [
                    'transaction_id' => $transaction->transaction_id,
                    'original_minutes' => $expiresInMinutes,
                    'calculated_seconds' => $expiresInSeconds,
                    'payload_expiresIn' => $pixConfig['expiresIn'],
                    'strategy' => 'expiresIn (seconds) - OBRIGATÓRIO para valores < 1 dia',
                    'validation' => 'Valor < 1 dia (' . $expiresInMinutes . ' minutos) = ' . $expiresInSeconds . ' segundos',
                ]);
            } else {
                // 1 dia ou mais: Usar expiresInDays (dias inteiros de 1 a 90)
                // API requirement: expiresInDays must be between 1 and 90 (integer days only)
                $finalDays = max(1, $expiresInDays); // Ensure minimum 1 day
                $pixConfig['expiresInDays'] = $finalDays;
                
                \Log::info('🔵 SharkBanking PIX expiration (using days)', [
                    'transaction_id' => $transaction->transaction_id,
                    'original_minutes' => $expiresInMinutes,
                    'calculated_days' => $expiresInDays,
                    'payload_expiresInDays' => $pixConfig['expiresInDays'],
                    'strategy' => 'expiresInDays (integer days) for values >= 1 day',
                    'validation' => 'Valor >= 1 dia (' . $expiresInMinutes . ' minutos) = ' . $finalDays . ' dias',
                ]);
            }
            
            // VALIDAÇÃO FINAL: Garantir que não há conflito entre expiresIn e expiresInDays
            // Se temos expiresIn, NÃO devemos ter expiresInDays e vice-versa
            if (isset($pixConfig['expiresIn']) && isset($pixConfig['expiresInDays'])) {
                \Log::error('❌ ERRO CRÍTICO: Payload PIX tem ambos expiresIn e expiresInDays! Removendo expiresInDays', [
                    'transaction_id' => $transaction->transaction_id,
                    'expiresIn' => $pixConfig['expiresIn'],
                    'expiresInDays' => $pixConfig['expiresInDays'],
                    'expiresInMinutes' => $expiresInMinutes,
                ]);
                // Remover expiresInDays se temos expiresIn (prioridade para valores < 1 dia)
                unset($pixConfig['expiresInDays']);
            }
            
            // Add pix object with expiration configuration
            $payload['pix'] = $pixConfig;
            
            // Log final do payload PIX para debug
            \Log::info('✅ Payload PIX final preparado', [
                'transaction_id' => $transaction->transaction_id,
                'pix_config' => $pixConfig,
                'expiresInMinutes_original' => $expiresInMinutes,
                'has_expiresIn' => isset($pixConfig['expiresIn']),
                'has_expiresInDays' => isset($pixConfig['expiresInDays']),
            ]);
        }

        return $payload;
    }

    /**
     * Prepare items array for Sharkgateway API
     */
    private function prepareItems(Transaction $transaction, array $data): array
    {
        // If products are provided, use them
        $products = $transaction->products ?? $data['products'] ?? $data['items'] ?? null;
        
        if ($products && is_array($products) && !empty($products)) {
            $items = [];
            foreach ($products as $product) {
                // Support multiple formats - prioritize title, then name, then description, then sale_name
                $title = $product['title'] 
                    ?? $product['name'] 
                    ?? $product['description'] 
                    ?? $data['sale_name'] 
                    ?? 'Produto';
                
                $quantity = isset($product['quantity']) ? (int)$product['quantity'] : 1;
                
                // Handle unitPrice - can be in cents or reais
                $unitPrice = null;
                if (isset($product['unitPrice'])) {
                    // If unitPrice is less than 100, assume it's in reais (need to convert)
                    $unitPrice = (int)round($product['unitPrice'] < 100 ? $product['unitPrice'] * 100 : $product['unitPrice']);
                } elseif (isset($product['price'])) {
                    // Price in reais, convert to cents
                    $unitPrice = (int)round($product['price'] * 100);
                } else {
                    // Fallback to transaction amount
                    $unitPrice = (int)round($transaction->amount * 100);
                }
                
                $tangible = isset($product['tangible']) ? (bool)$product['tangible'] : false;
                
                $items[] = [
                    'title' => $title,
                    'quantity' => $quantity,
                    'unitPrice' => $unitPrice,
                    'tangible' => $tangible
                ];
            }
            
            // Validate total amount matches transaction amount
            $totalItemsAmount = array_sum(array_map(function($item) {
                return $item['unitPrice'] * $item['quantity'];
            }, $items));
            
            $transactionAmountCents = (int)round($transaction->amount * 100);
            
            // If there's a mismatch, adjust the last item
            if ($totalItemsAmount !== $transactionAmountCents && !empty($items)) {
                $difference = $transactionAmountCents - $totalItemsAmount;
                $lastIndex = count($items) - 1;
                $items[$lastIndex]['unitPrice'] += $difference;
            }
            
            return $items;
        }
        
        // Default: single item with sale_name or description
        $itemTitle = $data['sale_name'] 
            ?? $data['description'] 
            ?? 'Venda';
        
        return [
            [
                'title' => $itemTitle,
                'quantity' => 1,
                'unitPrice' => (int)round($transaction->amount * 100),
                'tangible' => false
            ]
        ];
    }

    /**
     * Format Sharkgateway response baseado no backup funcional
     */
    private function formatSharkgatewayResponse(array $responseData, Transaction $transaction): array
    {
        return [
            'gateway_transaction_id' => $responseData['id'] ?? null,
            'gateway_id' => $responseData['id'] ?? null,
            'status' => $this->mapSharkgatewayStatus($responseData['status'] ?? 'waiting_payment'),
            'payment_data' => [
                'pix' => [
                    'qrcode' => $responseData['pix']['qrcode'] ?? null,
                    'payload' => $responseData['pix']['qrcode'] ?? null,
                    'expirationDate' => $responseData['pix']['expirationDate'] ?? null,
                ],
                'amount' => $responseData['amount'] ?? null,
                'installments' => $responseData['installments'] ?? null,
                'customer' => $responseData['customer'] ?? null,
                'secureId' => $responseData['secureId'] ?? null,
            ],
            'raw_response' => $responseData
        ];
    }

    /**
     * Map Sharkgateway status to our status
     */
    private function mapSharkgatewayStatus(string $gatewayStatus): string
    {
        return match(strtolower($gatewayStatus)) {
            'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }

    /**
     * Get document type (CPF or CNPJ)
     */
    private function getDocumentType(string $document): string
    {
        $cleanDocument = preg_replace('/[^0-9]/', '', $document);
        return strlen($cleanDocument) === 11 ? 'cpf' : 'cnpj';
    }

    /**
     * Check transaction status from Sharkgateway API
     * GET /v1/transactions/{id}
     */
    public function checkTransactionStatus(Transaction $transaction): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Gateway Sharkgateway não está configurado',
                ];
            }

            if (!$transaction->external_id) {
                return [
                    'success' => false,
                    'error' => 'Transação não possui external_id',
                ];
            }

            // Prepare authentication
            $auth = base64_encode($this->credentials->public_key . ':' . $this->credentials->secret_key);
            
            // Build API URL - try /v1/transactions/{id} first, then /transactions/{id}
            // The base URL might already include /v1 or might not
            $apiBaseUrl = rtrim($this->gateway->api_url, '/');
            $endpoint = '/v1/transactions/' . $transaction->external_id;
            
            // If base URL already includes /v1, use /transactions/{id}
            if (substr($apiBaseUrl, -3) === '/v1') {
                $endpoint = '/transactions/' . $transaction->external_id;
            }
            
            $fullUrl = $apiBaseUrl . $endpoint;
            
            $this->logRequest('check_transaction_status_request', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'api_base_url' => $apiBaseUrl,
                'endpoint' => $endpoint,
                'full_url' => $fullUrl,
            ], []);
            
            // Make request to Sharkgateway API
            // GET https://api.podpay.co/v1/transactions/{id}
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'Authorization' => 'Basic ' . $auth,
                ])
                ->get($fullUrl);

            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseJson = $response->json();
            
            $this->logRequest('check_transaction_status', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'response_json' => $responseJson,
                'url' => $fullUrl,
            ], $responseJson);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $statusCode = $response->status();
                
                $this->logError('check_transaction_status', 'Erro na API Sharkgateway', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'status_code' => $statusCode,
                    'error_body' => $errorBody,
                    'url' => $fullUrl,
                    'response_headers' => $response->headers(),
                ]);
                
                // Se for 404, a transação pode não existir mais ou o ID está errado
                if ($statusCode === 404) {
                    return [
                        'success' => false,
                        'error' => 'Transação não encontrada na API do gateway (404)',
                        'status_code' => $statusCode,
                    ];
                }
                
                return [
                    'success' => false,
                    'error' => 'Erro ao consultar status na API Sharkgateway (HTTP ' . $statusCode . '): ' . substr($errorBody, 0, 200),
                    'status_code' => $statusCode,
                ];
            }

            // Use responseJson that was already parsed
            $responseData = $responseJson;
            
            // Validate response data
            if (!$responseData || !is_array($responseData)) {
                $this->logError('check_transaction_status', 'Resposta inválida da API Sharkgateway', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'response_body' => $responseBody,
                    'url' => $fullUrl,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Resposta inválida da API do gateway (não é JSON válido)',
                ];
            }
            
            // Log the full response for debugging
            $this->logRequest('check_transaction_status_response', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'response_data' => $responseData,
                'has_status' => isset($responseData['status']),
                'status_value' => $responseData['status'] ?? null,
                'has_paidAt' => isset($responseData['paidAt']),
                'paidAt_value' => $responseData['paidAt'] ?? null,
            ], []);
            
            // Check for refunds first (refundedAmount or refunds array)
            $refundedAmount = $responseData['refundedAmount'] ?? 0;
            $refundsArray = $responseData['refunds'] ?? [];
            
            // Calculate total refunded amount from refunds array if refundedAmount is not available
            if ($refundedAmount == 0 && !empty($refundsArray) && is_array($refundsArray)) {
                foreach ($refundsArray as $refund) {
                    if (isset($refund['amount'])) {
                        $refundedAmount += (int)$refund['amount'];
                    } elseif (isset($refund['value'])) {
                        $refundedAmount += (int)$refund['value'];
                    }
                }
            }
            
            $hasRefunds = ($refundedAmount > 0) || (!empty($refundsArray) && is_array($refundsArray));
            
            // PRIORIDADE 1: Verificar se tem paidAt - se tiver, está PAGO (a menos que tenha refund)
            $paidAt = $responseData['paidAt'] ?? $responseData['paid_at'] ?? null;
            $hasPaidAt = ($paidAt !== null && $paidAt !== '' && $paidAt !== 'null');
            
            // PRIORIDADE 2: Mapear status do gateway
            $gatewayStatus = $responseData['status'] ?? 'waiting_payment';
            $mappedStatus = $this->mapSharkgatewayStatus($gatewayStatus);
            
            // PRIORIDADE 3: Se tem paidAt, SEMPRE é paid (a menos que tenha refund)
            if ($hasPaidAt && !$hasRefunds) {
                $mappedStatus = 'paid';
                $this->logRequest('check_transaction_status_paidAt_detected', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'gateway_status' => $gatewayStatus,
                    'paidAt' => $paidAt,
                    'mapped_status' => 'paid',
                    'reason' => 'paidAt presente na resposta',
                ], []);
            }
            
            // PRIORIDADE 4: Override status if there are refunds (refund tem prioridade sobre paid)
            if ($hasRefunds) {
                // Check if it's a partial or full refund
                $transactionAmount = $responseData['amount'] ?? ($transaction->amount * 100); // Amount in cents
                if ($refundedAmount >= $transactionAmount) {
                    $mappedStatus = 'refunded';
                } else {
                    $mappedStatus = 'partially_refunded';
                }
                
                $this->logRequest('check_transaction_status_refund_detected', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'gateway_status' => $gatewayStatus,
                    'refundedAmount' => $refundedAmount,
                    'transactionAmount' => $transactionAmount,
                    'refunds_count' => count($refundsArray),
                    'mapped_status' => $mappedStatus,
                ], []);
            }
            
            // Log status mapping com TODOS os detalhes
            $this->logRequest('check_transaction_status_mapping', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'gateway_status' => $gatewayStatus,
                'mapped_status' => $mappedStatus,
                'current_status' => $transaction->status,
                'has_paidAt' => $hasPaidAt,
                'paidAt' => $paidAt,
                'has_refunds' => $hasRefunds,
                'status_will_change' => ($transaction->status !== $mappedStatus),
                'response_keys' => array_keys($responseData),
            ], []);
            
            // Check if status changed
            $oldStatus = $transaction->status;
            $statusChanged = $oldStatus !== $mappedStatus;
            
            // Always check for refunded_at even if status didn't change (in case refund happened)
            $shouldUpdateRefundedAt = $hasRefunds && !$transaction->refunded_at;
            
            // Se tem paidAt mas status ainda não é paid, FORÇAR atualização
            $shouldForceUpdateToPaid = ($hasPaidAt && $oldStatus !== 'paid' && $mappedStatus === 'paid');
            
            // Update transaction if status changed, has paidAt, or needs refunded_at
            if ($statusChanged || $shouldUpdateRefundedAt || $shouldForceUpdateToPaid) {
                $updateData = ['status' => $mappedStatus];
                
                // Set paid_at if status changed to paid
                if ($mappedStatus === 'paid' && ($oldStatus !== 'paid' || $shouldForceUpdateToPaid)) {
                    try {
                        if ($hasPaidAt && $paidAt) {
                            $updateData['paid_at'] = \Carbon\Carbon::parse($paidAt);
                        } else {
                            $updateData['paid_at'] = now();
                        }
                    } catch (\Exception $e) {
                        $this->logError('check_transaction_status', 'Erro ao parsear paidAt: ' . $e->getMessage(), [
                            'transaction_id' => $transaction->transaction_id,
                            'paidAt' => $paidAt,
                        ]);
                        $updateData['paid_at'] = now();
                    }
                }
                
                // Set refunded_at if status is refunded or partially_refunded
                if (($mappedStatus === 'refunded' || $mappedStatus === 'partially_refunded') && 
                    (!$transaction->refunded_at || $shouldUpdateRefundedAt)) {
                    try {
                        // Try to get refundedAt from refunds array (most recent refund)
                        $refundedAt = null;
                        if (!empty($refundsArray) && is_array($refundsArray)) {
                            // Get the most recent refund
                            $latestRefund = end($refundsArray);
                            if (isset($latestRefund['createdAt']) || isset($latestRefund['created_at'])) {
                                $refundedAt = \Carbon\Carbon::parse($latestRefund['createdAt'] ?? $latestRefund['created_at']);
                            }
                        }
                        
                        // If not found in refunds array, check updatedAt or use now
                        if (!$refundedAt) {
                            if (isset($responseData['updatedAt']) && !empty($responseData['updatedAt'])) {
                                $refundedAt = \Carbon\Carbon::parse($responseData['updatedAt']);
                            } else {
                                $refundedAt = now();
                            }
                        }
                        
                        $updateData['refunded_at'] = $refundedAt;
                    } catch (\Exception $e) {
                        $this->logError('check_transaction_status', 'Erro ao parsear refundedAt: ' . $e->getMessage(), [
                            'transaction_id' => $transaction->transaction_id,
                            'refunds' => $refundsArray,
                        ]);
                        $updateData['refunded_at'] = now();
                    }
                }
                
                // FORÇAR atualização mesmo se status não mudou mas tem paidAt
                if ($shouldForceUpdateToPaid) {
                    $statusChanged = true; // Forçar como mudado para processar wallet
                }
                
                $transaction->update($updateData);
                
                // Log da atualização
                $this->logRequest('transaction_status_updated', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'old_status' => $oldStatus,
                    'new_status' => $mappedStatus,
                    'gateway_status' => $gatewayStatus,
                    'status_changed' => $statusChanged,
                    'has_paidAt' => $hasPaidAt,
                    'paidAt' => $paidAt,
                    'update_data' => $updateData,
                ], []);
                
                // Handle status changes (process wallet and dispatch webhooks immediately)
                if ($statusChanged || $shouldForceUpdateToPaid) {
                    try {
                        // Refresh transaction to get latest data
                        $transaction->refresh();
                        
                        // Call handleStatusChange to process wallet and dispatch webhooks
                        $webhookController = app(\App\Http\Controllers\WebhookController::class);
                        $webhookController->handleStatusChange($transaction, $oldStatus, $mappedStatus);
                        
                        $this->logRequest('status_change_handled', [
                            'transaction_id' => $transaction->transaction_id,
                            'old_status' => $oldStatus,
                            'new_status' => $mappedStatus,
                            'webhook_dispatched' => true,
                        ], []);
                    } catch (\Exception $e) {
                        $this->logError('status_change_handle', 'Erro ao processar mudança de status: ' . $e->getMessage(), [
                            'transaction_id' => $transaction->transaction_id,
                            'old_status' => $oldStatus,
                            'new_status' => $mappedStatus,
                            'trace' => $e->getTraceAsString(),
                        ]);
                        
                        // Fallback: try to dispatch webhook directly if handleStatusChange fails
                        try {
                            $webhookService = new \App\Services\WebhookService();
                            $event = $this->mapStatusToWebhookEvent($mappedStatus);
                            if ($event) {
                                $webhookService->dispatchTransactionEvent($transaction->fresh(), $event);
                            }
                        } catch (\Exception $e2) {
                            $this->logError('webhook_dispatch_fallback', 'Erro ao disparar webhook (fallback): ' . $e2->getMessage(), [
                                'transaction_id' => $transaction->transaction_id,
                                'status' => $mappedStatus,
                            ]);
                        }
                    }
                }
                
                $this->logRequest('transaction_status_updated', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'old_status' => $oldStatus,
                    'new_status' => $mappedStatus,
                    'gateway_status' => $gatewayStatus,
                ], $responseData);
            }

            return [
                'success' => true,
                'status' => $mappedStatus,
                'paid' => $mappedStatus === 'paid',
                'data' => $responseData,
                'status_changed' => $statusChanged,
            ];

        } catch (\Exception $e) {
            $this->logError('check_transaction_status', $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Map status to webhook event
     */
    protected function mapStatusToWebhookEvent(string $status): ?string
    {
        return match($status) {
            'paid' => 'transaction.paid',
            'failed' => 'transaction.failed',
            'expired' => 'transaction.expired',
            'refunded', 'partially_refunded' => 'transaction.refunded',
            'chargeback' => 'transaction.chargeback',
            'cancelled' => 'transaction.cancelled',
            default => null,
        };
    }
}