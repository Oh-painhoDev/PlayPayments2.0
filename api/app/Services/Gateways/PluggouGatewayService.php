<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PluggouGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Pluggou
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                // More descriptive error message
                if ($this->credentials === null) {
                    throw new \Exception('Gateway Pluggou não está configurado. Por favor, configure as credenciais no painel administrativo.');
                } else {
                    $missing = [];
                    if (empty($this->credentials->public_key)) {
                        $missing[] = 'Public Key';
                    }
                    if (empty($this->credentials->secret_key)) {
                        $missing[] = 'Secret Key';
                    }
                    throw new \Exception('Gateway Pluggou não está configurado corretamente. Credenciais faltando: ' . implode(', ', $missing) . '. Por favor, configure as credenciais no painel administrativo.');
                }
            }
            
            // Validate credentials are not empty before using
            if (empty($this->credentials->public_key) || empty($this->credentials->secret_key)) {
                throw new \Exception('Credenciais do gateway Pluggou estão vazias. Por favor, configure as credenciais no painel administrativo.');
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
            $transaction->expires_at = now()->addHours(24);

            // Process retention before saving
            $retentionService = new RetentionService();
            $shouldRetain = $retentionService->processTransaction($transaction);

            $transaction->save();

            // Prepare Pluggou payload
            $payload = $this->preparePluggouPayload($transaction, $data);

            // Send request to Pluggou
            // URL base conforme documentação oficial: https://api.pluggoutech.com/api
            // Endpoint: POST /api/transactions
            // URL completa: https://api.pluggoutech.com/api/transactions
            
            // Construir URL corretamente
            // Se a URL base já termina com /api, apenas adicionar /transactions
            // Se não termina com /api, adicionar /api/transactions
            $baseUrl = rtrim($this->gateway->api_url, '/');
            
            // Verificar se já termina com /api
            if (substr($baseUrl, -4) === '/api') {
                // URL base já tem /api, apenas adicionar /transactions
                $apiUrl = $baseUrl . '/transactions';
            } else {
                // URL base não tem /api, adicionar /api/transactions
                $apiUrl = $baseUrl . '/api/transactions';
            }
            
            // Garantir que não há duplicação
            $apiUrl = preg_replace('#/api/api/#', '/api/', $apiUrl);
            $apiUrl = preg_replace('#/api/api$#', '/api', $apiUrl);
            
            // Log para debug
            Log::info('Pluggou: URL construída', [
                'base_url' => $this->gateway->api_url,
                'final_url' => $apiUrl,
                'transaction_id' => $transaction->transaction_id,
            ]);
            
            // Validate credentials are not empty before using (double check)
            if (empty($this->credentials->public_key) || empty($this->credentials->secret_key)) {
                Log::error('Pluggou: Credenciais vazias ao tentar enviar requisição', [
                    'gateway_id' => $this->gateway->id,
                    'has_public_key' => !empty($this->credentials->public_key),
                    'has_secret_key' => !empty($this->credentials->secret_key),
                    'public_key_length' => $this->credentials->public_key ? strlen($this->credentials->public_key) : 0,
                    'secret_key_length' => $this->credentials->secret_key ? strlen($this->credentials->secret_key) : 0,
                ]);
                throw new \Exception('Credenciais do gateway Pluggou estão vazias. Por favor, configure as credenciais no painel administrativo.');
            }
            
            // Clean credentials: trim, remove newlines, and remove any invisible characters
            $publicKey = trim($this->credentials->public_key);
            $secretKey = trim($this->credentials->secret_key);
            
            // Remove any newlines, carriage returns, or other whitespace characters
            $publicKey = preg_replace('/\s+/', '', $publicKey);
            $secretKey = preg_replace('/\s+/', '', $secretKey);
            
            // Remove any non-printable characters
            $publicKey = preg_replace('/[\x00-\x1F\x7F]/', '', $publicKey);
            $secretKey = preg_replace('/[\x00-\x1F\x7F]/', '', $secretKey);
            
            // Validate credentials after cleaning
            if (empty($publicKey) || empty($secretKey)) {
                Log::error('Pluggou: Credenciais vazias após limpeza', [
                    'gateway_id' => $this->gateway->id,
                    'public_key_original_length' => strlen($this->credentials->public_key ?? ''),
                    'public_key_cleaned_length' => strlen($publicKey),
                    'secret_key_original_length' => strlen($this->credentials->secret_key ?? ''),
                    'secret_key_cleaned_length' => strlen($secretKey),
                ]);
                throw new \Exception('Credenciais do gateway Pluggou estão vazias após limpeza. Por favor, verifique as credenciais no painel administrativo.');
            }
            
            Log::info('Enviando requisição para Pluggou', [
                'url' => $apiUrl,
                'transaction_id' => $transaction->transaction_id,
                'payload' => $payload,
                'headers' => [
                    'X-Public-Key' => substr($publicKey, 0, 10) . '...', // Log apenas parcial
                    'X-Secret-Key' => '***' . substr($secretKey, -4), // Log apenas últimos 4 chars
                ],
                'public_key_length' => strlen($publicKey),
                'secret_key_length' => strlen($secretKey),
                'public_key_starts_with' => substr($publicKey, 0, 5),
                'secret_key_ends_with' => substr($secretKey, -5),
                'api_url' => $apiUrl,
            ]);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Public-Key' => $publicKey,
                    'X-Secret-Key' => $secretKey,
                ])
                ->post($apiUrl, $payload);

            $responseBody = $response->body();
            $responseJson = $response->json();
            $statusCode = $response->status();

            $this->logRequest('create_transaction', $payload, $responseJson);

            if (!$response->successful()) {
                // Log detailed error information
                Log::error('Erro na API Pluggou', [
                    'status_code' => $statusCode,
                    'response_body' => $responseBody,
                    'response_json' => $responseJson,
                    'payload_sent' => $payload,
                    'transaction_id' => $transaction->transaction_id,
                    'api_url' => $apiUrl,
                    'public_key_length' => strlen($publicKey),
                    'secret_key_length' => strlen($secretKey),
                    'public_key_preview' => substr($publicKey, 0, 10) . '...',
                    'response_headers' => $response->headers(),
                ]);

                // Extract error message from response with detailed field information
                $errorMessage = 'Erro desconhecido';
                $errorDetails = [];
                
                if (is_array($responseJson)) {
                    // Check for detailed validation errors
                    if (isset($responseJson['data']['errors']) && is_array($responseJson['data']['errors'])) {
                        $errors = $responseJson['data']['errors'];
                        foreach ($errors as $field => $messages) {
                            if (is_array($messages)) {
                                $errorDetails[] = ucfirst(str_replace('buyer.', '', $field)) . ': ' . implode(', ', $messages);
                            } else {
                                $errorDetails[] = ucfirst(str_replace('buyer.', '', $field)) . ': ' . $messages;
                            }
                        }
                        
                        if (!empty($errorDetails)) {
                            $errorMessage = 'Erro de validação: ' . implode(' | ', $errorDetails);
                        }
                    }
                    
                    // Fallback to general message
                    if (empty($errorDetails)) {
                        if (isset($responseJson['message'])) {
                            $errorMessage = $responseJson['message'];
                        } elseif (isset($responseJson['error'])) {
                            $errorMessage = is_array($responseJson['error']) 
                                ? json_encode($responseJson['error']) 
                                : $responseJson['error'];
                        } elseif (isset($responseJson['errors'])) {
                            $errorMessage = is_array($responseJson['errors']) 
                                ? json_encode($responseJson['errors']) 
                                : $responseJson['errors'];
                        }
                    }
                    
                    // Detectar especificamente erros de documento da API Pluggou
                    $errorMessageLower = strtolower($errorMessage);
                    $isDocumentError = false;
                    $documentFieldError = null;
                    
                    // Verificar se é especificamente um erro de documento nos erros estruturados
                    if (isset($responseJson['data']['errors']) && is_array($responseJson['data']['errors'])) {
                        foreach ($responseJson['data']['errors'] as $field => $messages) {
                            $fieldLower = strtolower($field);
                            // Verificar se o campo é especificamente buyer_document ou buyer.document
                            if ($fieldLower === 'buyer_document' || 
                                $fieldLower === 'buyer.document' ||
                                strpos($fieldLower, 'buyer_document') !== false) {
                                $isDocumentError = true;
                                $documentFieldError = is_array($messages) ? implode(', ', $messages) : $messages;
                                break;
                            }
                        }
                    }
                    
                    // Se não encontrou nos erros estruturados, verificar na mensagem
                    if (!$isDocumentError) {
                        // Palavras-chave muito específicas que indicam erro de documento
                        $documentErrorKeywords = [
                            'buyer_document',
                            'cpf ou cnpj do comprador inválido',
                            'cpf/cnpj do comprador inválido',
                            'documento do comprador inválido',
                            'buyer document invalid',
                            'invalid buyer document'
                        ];
                        
                        foreach ($documentErrorKeywords as $keyword) {
                            if (strpos($errorMessageLower, $keyword) !== false) {
                                $isDocumentError = true;
                                break;
                            }
                        }
                    }
                    
                    // Só melhorar mensagem se for realmente erro de documento confirmado
                    if ($isDocumentError) {
                        // Usar a mensagem do campo de erro se disponível, senão usar a mensagem original
                        if ($documentFieldError) {
                            $errorMessage = "Documento inválido: " . $documentFieldError . " A Pluggou exige CPF/CNPJ válidos (com dígitos verificadores corretos). O documento será enviado no formato: 105.637.960-06 (CPF) ou 12.345.678/0001-90 (CNPJ).";
                        } else {
                            // Remover duplicações de "Erro de validação"
                            $errorMessage = preg_replace('/^Erro de validação:\s*/i', '', $errorMessage);
                            $errorMessage = "Documento inválido: " . trim($errorMessage) . " A Pluggou exige CPF/CNPJ válidos (com dígitos verificadores corretos). O documento será enviado no formato: 105.637.960-06 (CPF) ou 12.345.678/0001-90 (CNPJ).";
                        }
                    }
                } elseif (!empty($responseBody)) {
                    $errorMessage = $responseBody;
                }

                // For 401 errors, provide more specific guidance
                if ($statusCode === 401) {
                    $detailedMessage = "Erro de autenticação na API Pluggou (HTTP 401): {$errorMessage}";
                    $detailedMessage .= "\n\nPossíveis causas:";
                    $detailedMessage .= "\n1. As credenciais (Public Key e/ou Secret Key) estão incorretas";
                    $detailedMessage .= "\n2. As credenciais estão inativas no painel da Pluggou";
                    $detailedMessage .= "\n3. As credenciais não têm permissões adequadas";
                    $detailedMessage .= "\n4. As credenciais são do ambiente errado (sandbox vs produção)";
                    $detailedMessage .= "\n\nPor favor, verifique as credenciais no painel administrativo e no painel da Pluggou.";
                    throw new \Exception($detailedMessage);
                }
                
                // For 422 errors, don't add HTTP status code prefix (error message is already descriptive)
                if ($statusCode === 422) {
                    throw new \Exception($errorMessage);
                } else {
                    $errorMessage = "Erro na API Pluggou (HTTP {$statusCode}): {$errorMessage}";
                    throw new \Exception($errorMessage);
                }
            }

            $responseData = $response->json();

            // Log complete response for debugging (sanitized)
            Log::info('Pluggou API Response recebida', [
                'transaction_id' => $transaction->transaction_id,
                'status_code' => $statusCode,
                'response_structure' => [
                    'has_success' => isset($responseData['success']),
                    'success' => $responseData['success'] ?? null,
                    'has_message' => isset($responseData['message']),
                    'has_data' => isset($responseData['data']),
                    'data_type' => isset($responseData['data']) ? gettype($responseData['data']) : null,
                    'data_keys' => isset($responseData['data']) && is_array($responseData['data']) ? array_keys($responseData['data']) : null,
                    'has_pix' => isset($responseData['data']['pix']),
                    'pix_type' => isset($responseData['data']['pix']) ? gettype($responseData['data']['pix']) : null,
                    'pix_keys' => isset($responseData['data']['pix']) && is_array($responseData['data']['pix']) ? array_keys($responseData['data']['pix']) : null,
                    'has_emv' => isset($responseData['data']['pix']['emv']),
                    'emv_length' => isset($responseData['data']['pix']['emv']) ? strlen($responseData['data']['pix']['emv']) : 0,
                    'emv_preview' => isset($responseData['data']['pix']['emv']) ? substr($responseData['data']['pix']['emv'], 0, 50) . '...' : null,
                ],
                'full_response_preview' => json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);

            // Check if response is successful
            // A API Pluggou retorna: { "success": true, "message": "...", "data": { ... } }
            if (!isset($responseData['success']) || !$responseData['success']) {
                $errorMsg = $responseData['message'] ?? 'Erro desconhecido';
                if (isset($responseData['error'])) {
                    $errorMsg = is_array($responseData['error']) ? json_encode($responseData['error']) : $responseData['error'];
                }
                Log::error('Pluggou: Resposta não foi bem-sucedida', [
                    'transaction_id' => $transaction->transaction_id,
                    'success' => $responseData['success'] ?? null,
                    'message' => $responseData['message'] ?? null,
                    'error' => $responseData['error'] ?? null,
                ]);
                throw new \Exception('Erro na resposta da API Pluggou: ' . $errorMsg);
            }

            // Verificar se temos os dados da resposta
            if (!isset($responseData['data']) || !is_array($responseData['data'])) {
                Log::error('Pluggou: Resposta não contém campo "data" ou não é um array', [
                    'transaction_id' => $transaction->transaction_id,
                    'response_keys' => array_keys($responseData),
                    'has_data' => isset($responseData['data']),
                    'data_type' => isset($responseData['data']) ? gettype($responseData['data']) : null,
                    'full_response' => json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);
                throw new \Exception('A API Pluggou não retornou os dados da transação. Por favor, tente novamente ou entre em contato com o suporte.');
            }

            $data = $responseData['data'];

            // Check if we have the PIX data
            // A estrutura é: data.pix.emv
            // Mas também pode estar em: data.pix.qrcode, data.pix.payload, data.emv, etc.
            if (!isset($data['pix']) || !is_array($data['pix'])) {
                Log::error('Pluggou: Resposta não contém campo "pix" ou não é um array', [
                    'transaction_id' => $transaction->transaction_id,
                    'data_keys' => array_keys($data),
                    'has_pix' => isset($data['pix']),
                    'pix_type' => isset($data['pix']) ? gettype($data['pix']) : null,
                    'full_data' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);
                throw new \Exception('A API Pluggou não retornou os dados PIX. Estrutura recebida: ' . json_encode(array_keys($data)) . '. Por favor, tente novamente ou entre em contato com o suporte.');
            }
            
            $pixData = $data['pix'];
            
            // EXTRAIR CÓDIGO PIX (EMV) - tentar múltiplos campos possíveis
            $emvCode = null;
            $emvSource = null;
            
            // PRIORIDADE 1: data.pix.emv (campo padrão da Pluggou conforme documentação)
            if (isset($pixData['emv']) && !empty(trim($pixData['emv']))) {
                $emvCode = trim($pixData['emv']);
                $emvSource = 'data.pix.emv';
            }
            // PRIORIDADE 2: data.pix.qrcode
            elseif (isset($pixData['qrcode']) && !empty(trim($pixData['qrcode']))) {
                $emvCode = trim($pixData['qrcode']);
                $emvSource = 'data.pix.qrcode';
            }
            // PRIORIDADE 3: data.pix.payload
            elseif (isset($pixData['payload']) && !empty(trim($pixData['payload']))) {
                $emvCode = trim($pixData['payload']);
                $emvSource = 'data.pix.payload';
            }
            // PRIORIDADE 4: data.pix.code
            elseif (isset($pixData['code']) && !empty(trim($pixData['code']))) {
                $emvCode = trim($pixData['code']);
                $emvSource = 'data.pix.code';
            }
            // PRIORIDADE 5: data.emv (direto no data)
            elseif (isset($data['emv']) && !empty(trim($data['emv']))) {
                $emvCode = trim($data['emv']);
                $emvSource = 'data.emv';
            }
            // PRIORIDADE 6: data.pix_code (direto no data)
            elseif (isset($data['pix_code']) && !empty(trim($data['pix_code']))) {
                $emvCode = trim($data['pix_code']);
                $emvSource = 'data.pix_code';
            }
            
            // Log da extração
            Log::info('Pluggou: Extraindo código PIX (EMV) da resposta', [
                'transaction_id' => $transaction->transaction_id,
                'emv_found' => !empty($emvCode),
                'emv_source' => $emvSource,
                'emv_length' => $emvCode ? strlen($emvCode) : 0,
                'emv_preview' => $emvCode ? substr($emvCode, 0, 50) . '...' : null,
                'pix_data_keys' => array_keys($pixData),
                'data_keys' => array_keys($data),
                'checked_fields' => [
                    'data.pix.emv' => isset($pixData['emv']) ? (empty(trim($pixData['emv'])) ? 'empty' : 'found') : 'not_set',
                    'data.pix.qrcode' => isset($pixData['qrcode']) ? (empty(trim($pixData['qrcode'])) ? 'empty' : 'found') : 'not_set',
                    'data.pix.payload' => isset($pixData['payload']) ? (empty(trim($pixData['payload'])) ? 'empty' : 'found') : 'not_set',
                    'data.pix.code' => isset($pixData['code']) ? (empty(trim($pixData['code'])) ? 'empty' : 'found') : 'not_set',
                    'data.emv' => isset($data['emv']) ? (empty(trim($data['emv'])) ? 'empty' : 'found') : 'not_set',
                    'data.pix_code' => isset($data['pix_code']) ? (empty(trim($data['pix_code'])) ? 'empty' : 'found') : 'not_set',
                ],
            ]);
            
            if (empty($emvCode)) {
                Log::error('Pluggou: Código PIX (EMV) não encontrado em nenhum campo esperado', [
                    'transaction_id' => $transaction->transaction_id,
                    'pix_data' => $pixData,
                    'data' => $data,
                    'full_response' => json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                ]);
                
                throw new \Exception('A API Pluggou não retornou o código PIX (EMV) em nenhum campo esperado (data.pix.emv, data.pix.qrcode, data.pix.payload, data.emv, data.pix_code). Estrutura recebida: ' . json_encode(array_keys($data)) . ' | Pix keys: ' . json_encode(array_keys($pixData)) . '. Por favor, verifique a documentação da API Pluggou ou entre em contato com o suporte.');
            }

            // Garantir que o EMV está no formato esperado para formatPluggouResponse
            // Se não estiver em data.pix.emv, adicionar
            if (!isset($responseData['data']['pix']['emv']) || empty($responseData['data']['pix']['emv'])) {
                $responseData['data']['pix']['emv'] = $emvCode;
            }
            
            // Format response
            $formattedResponse = $this->formatPluggouResponse($responseData, $transaction);

            // Log formatted response
            Log::info('Pluggou: Resposta formatada', [
                'transaction_id' => $transaction->transaction_id,
                'has_pix_payload' => !empty($formattedResponse['payment_data']['pix']['payload']),
                'pix_payload_length' => strlen($formattedResponse['payment_data']['pix']['payload'] ?? ''),
                'formatted_response' => $formattedResponse,
            ]);

            // Update transaction with external ID and payment data
            // IMPORTANTE: A estrutura deve ser compatível com o que o TransactionsController espera
            // O TransactionsController busca em: $transaction->payment_data['pix']['qr_code'] ou ['payload']
            // CORRIGIDO: Remover o nível extra de 'payment_data' que estava causando problema
            $paymentDataToSave = $formattedResponse['payment_data'];

            // Garantir que a estrutura está correta: payment_data['pix'] diretamente
            // Não deve ter payment_data['payment_data']['pix']
            if (isset($paymentDataToSave['payment_data']['pix'])) {
                // Se tiver nível extra, corrigir
                $paymentDataToSave = $paymentDataToSave['payment_data'];
            }

            // Extrair external_id da resposta
            // IMPORTANTE: Converter para string para garantir compatibilidade
            $externalId = $responseData['data']['id'] ?? null;
            if ($externalId !== null) {
                $externalId = (string) $externalId; // Garantir que é string
            }
            
            // Log para garantir que external_id está sendo salvo
            Log::info('Pluggou: Salvando external_id', [
                'transaction_id' => $transaction->transaction_id,
                'external_id_from_api' => $externalId,
                'external_id_type' => gettype($externalId),
                'external_id_length' => $externalId ? strlen($externalId) : 0,
                'response_data_id' => $responseData['data']['id'] ?? null,
                'response_data_keys' => array_keys($responseData['data'] ?? []),
            ]);
            
            if (empty($externalId)) {
                Log::error('Pluggou: external_id está vazio na resposta da API!', [
                    'transaction_id' => $transaction->transaction_id,
                    'response_data' => $responseData,
                ]);
                throw new \Exception('A API Pluggou não retornou o ID da transação (external_id). Por favor, tente novamente ou entre em contato com o suporte.');
            }
            
            $transaction->update([
                'external_id' => $externalId,
                'payment_data' => $paymentDataToSave,
            ]);

            // Recarregar transação para garantir que temos os dados atualizados
            $transaction->refresh();
            
            // Verificar se external_id foi salvo corretamente
            if (empty($transaction->external_id)) {
                Log::error('Pluggou: external_id NÃO foi salvo após update!', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id_tentado' => $externalId,
                    'external_id_salvo' => $transaction->external_id,
                ]);
            } else {
                Log::info('Pluggou: external_id salvo com sucesso', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                ]);
            }

            // Log saved payment data
            Log::info('Pluggou: Dados de pagamento salvos', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'has_payment_data' => !empty($transaction->payment_data),
                'has_pix' => isset($transaction->payment_data['pix']),
                'has_qr_code' => isset($transaction->payment_data['pix']['qr_code']),
                'has_payload' => isset($transaction->payment_data['pix']['payload']),
                'has_emv' => isset($transaction->payment_data['pix']['emv']),
                'payment_data_structure' => [
                    'keys' => is_array($transaction->payment_data) ? array_keys($transaction->payment_data) : [],
                    'pix_keys' => isset($transaction->payment_data['pix']) && is_array($transaction->payment_data['pix']) ? array_keys($transaction->payment_data['pix']) : [],
                ],
            ]);

            // Dispatch webhook event for transaction.created
            $this->dispatchTransactionCreatedWebhook($transaction);

            return [
                'success' => true,
                'transaction' => $transaction,
                'gateway_response' => $formattedResponse,
            ];

        } catch (\Exception $e) {
            $this->logError('create_transaction', $e->getMessage(), [
                'data' => $data,
                'customer_data' => $data['customer'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return user-friendly error message
            $errorMessage = $e->getMessage();
            
            // If it's a validation error, make it more user-friendly
            if (strpos($errorMessage, 'Erro de validação') !== false || 
                strpos($errorMessage, 'validation') !== false ||
                strpos($errorMessage, 'obrigatório') !== false) {
                $errorMessage = 'Erro de validação: ' . str_replace('Erro na API Pluggou: ', '', $errorMessage);
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }
    }

    /**
     * Prepare Pluggou payload
     */
    private function preparePluggouPayload(Transaction $transaction, array $data): array
    {
        $customer = $transaction->customer_data;

        // Validate required customer fields
        if (empty($customer['name'])) {
            throw new \Exception('Nome do comprador é obrigatório');
        }

        if (empty($customer['document'])) {
            throw new \Exception('Documento do comprador é obrigatório');
        }

        // Format and validate document
        // A Pluggou exige CPF/CNPJ SEM formatação (apenas números)
        // Remover TODOS os caracteres não numéricos: pontos, traços, barras, espaços, etc.
        $document = $this->formatDocument($customer['document']);
        
        // Garantir que o documento contém APENAS números (sem formatação)
        $document = preg_replace('/[^0-9]/', '', $document);
        
        // Validate document length
        if (empty($document)) {
            throw new \Exception('Documento do comprador é obrigatório e não pode estar vazio');
        }
        
        $documentLength = strlen($document);
        if ($documentLength !== 11 && $documentLength !== 14) {
            throw new \Exception("Documento inválido. Deve ser CPF (11 dígitos) ou CNPJ (14 dígitos). Fornecido: {$documentLength} dígitos após remover formatação");
        }
        
        // Garantir que o documento contém APENAS dígitos numéricos
        if (!ctype_digit($document)) {
            throw new \Exception('Documento inválido: o documento deve conter apenas números (sem pontos, traços, barras ou espaços)');
        }
        
        // Log para debug
        Log::info('Pluggou: Documento formatado', [
            'document_original' => substr($customer['document'], 0, 3) . '***' . substr($customer['document'], -2),
            'document_formatted' => substr($document, 0, 3) . '***' . substr($document, -2),
            'document_length' => $documentLength,
            'is_numeric_only' => ctype_digit($document),
            'transaction_id' => $transaction->transaction_id
        ]);
        
        // Validar formato básico de CPF/CNPJ
        // A validação algorítmica completa será feita pela API da Pluggou
        // Aqui apenas validamos o formato (quantidade de dígitos)
        if ($documentLength === 11) {
            // Validar formato básico (11 dígitos numéricos)
            if (!preg_match('/^[0-9]{11}$/', $document)) {
                throw new \Exception('CPF inválido: o documento deve conter exatamente 11 dígitos numéricos. Verifique se não há caracteres especiais ou espaços.');
            }
            
            // Lista de CPFs de teste comuns (mesmo que inválidos, são aceitos em alguns ambientes)
            $testCpfs = [
                '11111111111',
                '00000000000',
                '12345678900', // CPF de teste comum (mesmo com dígitos verificadores inválidos)
                '12345678909', // CPF de teste válido
                '11144477735', // CPF válido de teste
            ];
            
            // Se for CPF de teste conhecido, não validar algoritmo
            if (in_array($document, $testCpfs)) {
                Log::info('Pluggou: CPF de teste detectado, enviando para API', [
                    'cpf' => substr($document, 0, 3) . '***' . substr($document, -2),
                    'transaction_id' => $transaction->transaction_id
                ]);
            } else {
                // Validação opcional: apenas log de warning se CPF parecer inválido
                // Mas não bloqueia - deixa a API da Pluggou validar
                if (!$this->isValidCPF($document)) {
                    Log::warning('Pluggou: CPF pode ser inválido, mas enviando para API validar', [
                        'cpf' => substr($document, 0, 3) . '***' . substr($document, -2),
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
            }
        }
        
        // Validar formato básico de CNPJ
        if ($documentLength === 14) {
            // Validar formato básico (14 dígitos numéricos)
            if (!preg_match('/^[0-9]{14}$/', $document)) {
                throw new \Exception('CNPJ inválido: o documento deve conter exatamente 14 dígitos numéricos. Verifique se não há caracteres especiais ou espaços.');
            }
            
            // Validação opcional: apenas log de warning se CNPJ parecer inválido
            // Mas não bloqueia - deixa a API da Pluggou validar
            if (!$this->isValidCNPJ($document)) {
                Log::warning('Pluggou: CNPJ pode ser inválido, mas enviando para API validar', [
                    'cnpj' => substr($document, 0, 2) . '***' . substr($document, -2),
                    'transaction_id' => $transaction->transaction_id
                ]);
            }
        }

        // Format and validate phone
        // Pluggou requires phone, so we must always provide a valid one
        $phone = '';
        if (!empty($customer['phone'])) {
            $phone = $this->formatPhone($customer['phone']);
        }
        
        // Phone is required by Pluggou API
        // Must be at least 10 digits (DDD + number) and at most 11 digits (DDD + 9 digits for mobile)
        // IMPORTANTE: Telefone deve conter APENAS números (sem letras)
        if (empty($phone) || strlen($phone) < 10 || strlen($phone) > 11 || !ctype_digit($phone)) {
            // Generate a valid default phone: DDD (11 = São Paulo) + 9 digits (mobile)
            // Use only NUMERIC characters from transaction ID hash
            $hash = md5($transaction->transaction_id);
            // Extract only digits from hash
            $digits = preg_replace('/[^0-9]/', '', $hash);
            // If not enough digits, pad with transaction ID and current timestamp
            if (strlen($digits) < 9) {
                $timestamp = str_replace(['-', ':', ' ', '.'], '', now()->toDateTimeString());
                $digits .= substr($timestamp, -9);
            }
            // If still not enough, use transaction ID numbers
            if (strlen($digits) < 9) {
                $transactionIdDigits = preg_replace('/[^0-9]/', '', $transaction->transaction_id);
                $digits .= substr($transactionIdDigits, 0, 9 - strlen($digits));
            }
            // Final fallback: pad with zeros
            if (strlen($digits) < 9) {
                $digits = str_pad($digits, 9, '0', STR_PAD_RIGHT);
            }
            // Take first 9 digits and pad if necessary
            $phoneNumber = substr($digits, 0, 9);
            $phoneNumber = str_pad($phoneNumber, 9, '0', STR_PAD_LEFT);
            // Format: DDD (11) + 9 digits
            $phone = '11' . $phoneNumber;
            
            Log::warning('Pluggou: Telefone não fornecido ou inválido, usando telefone padrão gerado', [
                'customer_phone_original' => $customer['phone'] ?? null,
                'phone_generated' => $phone,
                'transaction_id' => $transaction->transaction_id
            ]);
        }
        
        // Ensure phone contains ONLY digits
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Ensure phone is exactly 10 or 11 digits (DDD + number)
        $phone = substr($phone, 0, 11); // Max 11 digits
        if (strlen($phone) < 10) {
            $phone = str_pad($phone, 10, '0', STR_PAD_LEFT);
        }
        
        // Final validation: phone must be numeric and have correct length
        if (!ctype_digit($phone) || strlen($phone) < 10 || strlen($phone) > 11) {
            // Last resort: use a valid default phone
            $phone = '11999999999';
            Log::error('Pluggou: Telefone inválido após validação, usando telefone padrão de emergência', [
                'transaction_id' => $transaction->transaction_id
            ]);
        }

        // Validate amount (must be at least 1 cent)
        $amountInCents = (int)round($transaction->amount * 100);
        if ($amountInCents < 1) {
            throw new \Exception('Valor da transação deve ser maior que zero');
        }

        // Ensure phone is never empty (critical for Pluggou API)
        if (empty($phone) || strlen($phone) < 10) {
            // Last resort: generate a valid phone number
            $phone = '11999999999'; // Default valid phone
            Log::error('Pluggou: Telefone ainda está vazio após validação, usando telefone padrão de emergência', [
                'transaction_id' => $transaction->transaction_id,
                'customer_phone' => $customer['phone'] ?? null
            ]);
        }

        // Extrair nome do produto da transação
        $productName = null;
        $metadata = $transaction->metadata ?? [];
        
        // PRIORIDADE 1: Verificar se há produtos no array products
        $products = $transaction->products ?? [];
        if (!empty($products) && is_array($products) && isset($products[0])) {
            $firstProduct = $products[0];
            $productName = $firstProduct['title'] ?? $firstProduct['name'] ?? null;
        }
        
        // PRIORIDADE 2: Verificar metadata['sale_name']
        if (empty($productName)) {
            $productName = $metadata['sale_name'] ?? null;
        }
        
        // PRIORIDADE 3: Verificar metadata['product_name']
        if (empty($productName)) {
            $productName = $metadata['product_name'] ?? null;
        }
        
        // PRIORIDADE 4: Verificar description no metadata
        if (empty($productName)) {
            $productName = $metadata['description'] ?? null;
        }
        
        // PRIORIDADE 5: Verificar sale_name diretamente em $data
        if (empty($productName) && isset($data['sale_name'])) {
            $productName = $data['sale_name'];
        }
        
        // Se ainda não tiver nome, usar um padrão
        if (empty($productName)) {
            $productName = 'Produto/Serviço';
        }
        
        // Formatar documento para envio à Pluggou
        // A Pluggou aceita CPF/CNPJ com formatação (ex: 105.637.960-06 ou 12.345.678/0001-90)
        $documentToSend = $this->formatDocumentForPluggou($document);
        
        // Validação final: garantir que o documento está correto
        if (empty($documentToSend)) {
            throw new \Exception('Erro interno: Documento não pôde ser formatado corretamente. Por favor, verifique o documento informado.');
        }
        
        // Payload conforme documentação oficial da Pluggou
        // https://api.pluggoutech.com/api/transactions
        // Campos obrigatórios: payment_method, amount, buyer (buyer_name, buyer_document, buyer_phone)
        // Campos opcionais: description (nome do produto/descrição da transação)
        $payload = [
            'payment_method' => 'pix', // Pluggou só suporta PIX
            'amount' => $amountInCents, // Valor em centavos (integer)
            'description' => $productName, // Nome do produto/descrição da transação
            'buyer' => [
                'buyer_name' => trim($customer['name']), // Nome do comprador (string)
                'buyer_document' => $documentToSend, // CPF/CNPJ formatado (ex: 105.637.960-06 ou 12.345.678/0001-90)
                'buyer_phone' => (string)$phone, // DDD + número sem formatação (apenas números)
            ],
        ];
        
        // Log final do payload (sem dados sensíveis completos)
        Log::info('Pluggou: Payload final preparado', [
            'product_name' => $productName,
            'description' => $payload['description'],
            'buyer_document_formatted' => $payload['buyer']['buyer_document'],
            'buyer_document_length' => strlen($payload['buyer']['buyer_document']),
            'buyer_document_preview' => substr($payload['buyer']['buyer_document'], 0, 3) . '***' . substr($payload['buyer']['buyer_document'], -2),
            'buyer_phone_length' => strlen($payload['buyer']['buyer_phone']),
            'transaction_id' => $transaction->transaction_id
        ]);
        
        // Final validation before sending
        if (empty($payload['buyer']['buyer_phone'])) {
            throw new \Exception('Erro interno: Telefone não pôde ser gerado. Por favor, forneça um telefone válido.');
        }

        // Log payload for debugging (sem dados sensíveis completos)
        Log::info('Pluggou payload preparado', [
            'transaction_id' => $transaction->transaction_id,
            'amount_cents' => $amountInCents,
            'amount_original' => $transaction->amount,
            'amount_formatted' => 'R$ ' . number_format($transaction->amount, 2, ',', '.'),
            'product_name' => $productName,
            'description' => $payload['description'],
            'buyer_name' => $payload['buyer']['buyer_name'],
            'buyer_document_length' => strlen($payload['buyer']['buyer_document']),
            'buyer_document_preview' => substr($payload['buyer']['buyer_document'], 0, 3) . '***' . substr($payload['buyer']['buyer_document'], -2),
            'buyer_phone_length' => strlen($payload['buyer']['buyer_phone']),
            'buyer_phone_preview' => substr($payload['buyer']['buyer_phone'], 0, 2) . '****' . substr($payload['buyer']['buyer_phone'], -4),
            'payload_structure' => [
                'payment_method' => $payload['payment_method'],
                'description' => $payload['description'],
                'amount' => $payload['amount'],
                'has_buyer' => isset($payload['buyer']),
                'buyer_fields' => array_keys($payload['buyer']),
            ]
        ]);

        return $payload;
    }

    /**
     * Format Pluggou response
     * 
     * ESTRUTURA DA RESPOSTA PLUGGOU:
     * {
     *   "success": true,
     *   "message": "Pagamento criado com sucesso!",
     *   "data": {
     *     "id": "9b257f52-9f9e-4364-9ce7-10f07cfaaaf8",
     *     "amount": 10000,
     *     "platform_tax": 649,
     *     "liquid_amount": 9351,
     *     "pix": {
     *       "emv": "00020126860014br.gov.bcb.pix2564ADQUIRENTE/qr/v3/at/ab836bee-d7fc-4b70-9a9e-f1a6924519475204000053039865802BR5925RECEBEDORFINAL6009JOINVILLE62070503***63049863"
     *     }
     *   }
     * }
     */
    private function formatPluggouResponse(array $responseData, Transaction $transaction): array
    {
        $data = $responseData['data'] ?? [];
        $pixData = $data['pix'] ?? [];

        // O código EMV já foi extraído em createTransaction()
        // Aqui apenas garantimos que temos o valor correto
        // EXTRAIR CÓDIGO PIX (EMV) DA RESPOSTA DA PLUGGOU
        // A Pluggou retorna o código EMV no campo: data.pix.emv (conforme documentação)
        // Este é o código PIX Copia e Cola completo que deve ser usado para gerar o QR Code
        $emvCode = null;
        $emvSource = null;
        
        // PRIORIDADE 1: data.pix.emv (campo padrão da Pluggou conforme documentação)
        if (isset($pixData['emv']) && !empty(trim($pixData['emv']))) {
            $emvCode = trim($pixData['emv']);
            $emvSource = 'data.pix.emv';
        }
        // PRIORIDADE 2: data.pix.qrcode
        elseif (isset($pixData['qrcode']) && !empty(trim($pixData['qrcode']))) {
            $emvCode = trim($pixData['qrcode']);
            $emvSource = 'data.pix.qrcode';
        }
        // PRIORIDADE 3: data.pix.payload
        elseif (isset($pixData['payload']) && !empty(trim($pixData['payload']))) {
            $emvCode = trim($pixData['payload']);
            $emvSource = 'data.pix.payload';
        }
        // PRIORIDADE 4: data.pix.code
        elseif (isset($pixData['code']) && !empty(trim($pixData['code']))) {
            $emvCode = trim($pixData['code']);
            $emvSource = 'data.pix.code';
        }
        // PRIORIDADE 5: data.emv (direto no data)
        elseif (isset($data['emv']) && !empty(trim($data['emv']))) {
            $emvCode = trim($data['emv']);
            $emvSource = 'data.emv';
        }
        // PRIORIDADE 6: data.pix_code (direto no data)
        elseif (isset($data['pix_code']) && !empty(trim($data['pix_code']))) {
            $emvCode = trim($data['pix_code']);
            $emvSource = 'data.pix_code';
        }
        
        // Log extraction for debugging
        Log::info('Pluggou formatPluggouResponse: Extraindo código PIX (EMV) da resposta JSON', [
            'transaction_id' => $transaction->transaction_id,
            'has_pix_data' => !empty($pixData),
            'has_emv' => !empty($emvCode),
            'emv_source' => $emvSource,
            'emv_length' => $emvCode ? strlen($emvCode) : 0,
            'emv_preview' => $emvCode ? substr($emvCode, 0, 50) . '...' : null,
            'emv_starts_with' => $emvCode ? substr($emvCode, 0, 10) : null,
            'pix_data_keys' => !empty($pixData) ? array_keys($pixData) : [],
            'data_keys' => array_keys($data),
            'response_structure' => [
                'has_data' => isset($responseData['data']),
                'has_pix' => isset($responseData['data']['pix']),
                'pix_fields' => isset($responseData['data']['pix']) ? array_keys($responseData['data']['pix']) : [],
            ],
        ]);

        // Validar se temos o código EMV (CRÍTICO)
        if (empty($emvCode)) {
            Log::error('Pluggou formatPluggouResponse: Código PIX (EMV) não encontrado na resposta JSON', [
                'transaction_id' => $transaction->transaction_id,
                'response_structure' => [
                    'has_data' => isset($responseData['data']),
                    'data_keys' => isset($responseData['data']) ? array_keys($responseData['data']) : [],
                    'has_pix' => isset($responseData['data']['pix']),
                    'pix_keys' => isset($responseData['data']['pix']) ? array_keys($responseData['data']['pix']) : [],
                    'pix_data' => $pixData,
                    'full_data' => $data,
                ],
                'full_response_preview' => json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ]);
            
            throw new \Exception('A API Pluggou não retornou o código PIX (EMV) em nenhum campo esperado. Estrutura recebida: ' . json_encode(array_keys($data)) . ' | Pix keys: ' . json_encode(array_keys($pixData)));
        }

        // NÃO gerar QR Code no backend - o frontend vai gerar usando JavaScript
        // A Pluggou retorna o código EMV no campo data.pix.emv
        // O frontend vai usar a biblioteca qrcode.js (via CDN) para gerar o QR Code a partir do EMV
        // Isso evita necessidade de instalar biblioteca PHP e funciona perfeitamente
        $qrCodeBase64 = null;

        // Mapear resposta da API Pluggou conforme documentação oficial
        // Estrutura: { "success": true, "message": "...", "data": { "id": "...", "amount": 10000, "platform_tax": 649, "liquid_amount": 9351, "pix": { "emv": "..." } } }
        $gatewayTransactionId = $data['id'] ?? null;
        $amount = isset($data['amount']) ? ($data['amount'] / 100) : $transaction->amount; // Convert from cents to reais
        $platformTax = isset($data['platform_tax']) ? ($data['platform_tax'] / 100) : null;
        $liquidAmount = isset($data['liquid_amount']) ? ($data['liquid_amount'] / 100) : ($transaction->net_amount ?? $amount);
        $status = $this->mapPluggouStatus($data['status'] ?? 'pending');
        
        // Extrair e2e_id se disponível (End-to-End ID - identificador único PIX no sistema bancário)
        // Conforme documentação, o e2e_id vem no webhook quando o pagamento é confirmado
        // Na criação, pode não estar presente ainda
        $e2eId = $data['e2e_id'] ?? null;
        
        return [
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_id' => $gatewayTransactionId,
            'status' => $status,
            'payment_data' => [
                'pix' => [
                    'emv' => $emvCode,
                    'qrcode' => $emvCode, // EMV é o código PIX (Copia e Cola) - conforme documentação
                    'payload' => $emvCode, // Payload também é o EMV
                    'qr_code' => $emvCode, // Alias para compatibilidade com TransactionsController
                    'qrcode_base64' => $qrCodeBase64, // QR Code gerado a partir do EMV (se disponível)
                    'encodedImage' => $qrCodeBase64, // Alias para compatibilidade
                ],
                'amount' => $amount,
                'platform_tax' => $platformTax,
                'liquid_amount' => $liquidAmount,
                'e2e_id' => $e2eId, // End-to-End ID (identificador único PIX no sistema bancário)
            ],
            'raw_response' => $responseData
        ];
    }

    /**
     * Map Pluggou status to our status
     * 
     * Conforme documentação oficial da Pluggou:
     * - pending: Transação criada, aguardando pagamento
     * - paid: Pagamento recebido e aprovado
     * - expired: QR code expirou sem pagamento
     * - failed: Falha no processamento do pagamento
     * - refunded: Transação foi reembolsada
     * - chargeback: Cliente abriu disputa/chargeback
     */
    private function mapPluggouStatus(string $gatewayStatus): string
    {
        $status = strtolower(trim($gatewayStatus));
        
        switch ($status) {
            case 'pending':
            case 'waiting_payment':
                return 'pending';
                
            case 'processing':
                return 'processing';
                
            case 'paid':
            case 'approved':
            case 'success':
            case 'completed':
                return 'paid';
                
            case 'cancelled':
            case 'canceled':
                return 'cancelled';
                
            case 'expired':
                return 'expired';
                
            case 'failed':
            case 'refused':
            case 'error':
                return 'failed';
                
            case 'refunded':
            case 'refound':
                return 'refunded';
                
            case 'partially_refunded':
                return 'partially_refunded';
                
            case 'chargeback':
                return 'chargeback';
                
            default:
                // Se não reconhecer, assumir pending (mais seguro)
                Log::warning('Pluggou: Status desconhecido mapeado para pending', [
                    'unknown_status' => $gatewayStatus,
                    'normalized_status' => $status
                ]);
                return 'pending';
        }
    }

    /**
     * Validate CPF with complete algorithm
     * Pluggou API validates CPF/CNPJ strictly, so we need to validate before sending
     */
    private function isValidCPF(string $cpf): bool
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Check length
        if (strlen($cpf) != 11) {
            return false;
        }

        // Check if all digits are the same (invalid)
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validate first check digit (10th position)
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$cpf[$i] * (10 - $i);
        }
        $remainder = 11 - ($sum % 11);
        if ($remainder == 10 || $remainder == 11) {
            $remainder = 0;
        }
        if ($remainder != (int)$cpf[9]) {
            return false;
        }

        // Validate second check digit (11th position)
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$cpf[$i] * (11 - $i);
        }
        $remainder = 11 - ($sum % 11);
        if ($remainder == 10 || $remainder == 11) {
            $remainder = 0;
        }
        if ($remainder != (int)$cpf[10]) {
            return false;
        }

        return true;
    }

    /**
     * Validate CNPJ with complete algorithm
     * Pluggou API validates CPF/CNPJ strictly, so we need to validate before sending
     */
    private function isValidCNPJ(string $cnpj): bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Check length
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Check if all digits are the same (invalid)
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validate first check digit
        for ($i = 0, $j = 5, $sum = 0; $i < 12; $i++) {
            $sum += (int)$cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        if ((int)$cnpj[12] != ($remainder < 2 ? 0 : 11 - $remainder)) {
            return false;
        }

        // Validate second check digit
        for ($i = 0, $j = 6, $sum = 0; $i < 13; $i++) {
            $sum += (int)$cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $remainder = $sum % 11;
        return (int)$cnpj[13] == ($remainder < 2 ? 0 : 11 - $remainder);
    }

    /**
     * Format document for Pluggou API
     * Formata CPF/CNPJ com pontos, traços e barras conforme esperado pela Pluggou
     * Exemplos: 105.637.960-06 (CPF) ou 12.345.678/0001-90 (CNPJ)
     */
    private function formatDocumentForPluggou(string $document): string
    {
        // Remover toda formatação primeiro
        $cleanDocument = preg_replace('/[^0-9]/', '', $document);
        
        // Verificar se é CPF (11 dígitos) ou CNPJ (14 dígitos)
        if (strlen($cleanDocument) === 11) {
            // Formatar CPF: XXX.XXX.XXX-XX (ex: 105.637.960-06)
            $formatted = substr($cleanDocument, 0, 3) . '.' . 
                        substr($cleanDocument, 3, 3) . '.' . 
                        substr($cleanDocument, 6, 3) . '-' . 
                        substr($cleanDocument, 9, 2);
            
            Log::info('Pluggou: CPF formatado para envio', [
                'cpf_original' => substr($cleanDocument, 0, 3) . '***' . substr($cleanDocument, -2),
                'cpf_formatted' => $formatted,
            ]);
            
            return $formatted;
        } elseif (strlen($cleanDocument) === 14) {
            // Formatar CNPJ: XX.XXX.XXX/XXXX-XX (ex: 12.345.678/0001-90)
            $formatted = substr($cleanDocument, 0, 2) . '.' . 
                        substr($cleanDocument, 2, 3) . '.' . 
                        substr($cleanDocument, 5, 3) . '/' . 
                        substr($cleanDocument, 8, 4) . '-' . 
                        substr($cleanDocument, 12, 2);
            
            Log::info('Pluggou: CNPJ formatado para envio', [
                'cnpj_original' => substr($cleanDocument, 0, 2) . '***' . substr($cleanDocument, -2),
                'cnpj_formatted' => $formatted,
            ]);
            
            return $formatted;
        }
        
        // Se não for CPF nem CNPJ, retornar sem formatação
        Log::warning('Pluggou: Documento não é CPF nem CNPJ, enviando sem formatação', [
            'document_length' => strlen($cleanDocument),
            'document_preview' => substr($cleanDocument, 0, 3) . '***' . substr($cleanDocument, -2),
        ]);
        
        return $cleanDocument;
    }

    /**
     * Check transaction status from Pluggou API
     * 
     * NOTA: A Pluggou não possui endpoint GET para consultar transações.
     * Este método tenta buscar, mas se não existir (404), retorna sucesso
     * pois a Pluggou funciona principalmente via webhooks.
     * 
     * Se o endpoint existir no futuro, funcionará automaticamente.
     */
    public function checkTransactionStatus(Transaction $transaction): array
    {
        try {
            if (!$this->isConfigured()) {
                return [
                    'success' => false,
                    'error' => 'Gateway Pluggou não está configurado',
                ];
            }

            if (!$transaction->external_id) {
                return [
                    'success' => false,
                    'error' => 'Transação não possui external_id',
                ];
            }

            // Clean credentials
            $publicKey = trim($this->credentials->public_key ?? '');
            $secretKey = trim($this->credentials->secret_key ?? '');

            if (empty($publicKey) || empty($secretKey)) {
                return [
                    'success' => false,
                    'error' => 'Credenciais do gateway Pluggou estão vazias',
                ];
            }

            // Build API URL - tentar endpoint GET (pode não existir)
            // URL base: https://api.pluggoutech.com/api
            // Tentar: GET /api/transactions/{id}
            $apiBaseUrl = rtrim($this->gateway->api_url, '/');
            
            // Remover /api duplicado se houver
            $apiBaseUrl = preg_replace('#/api/api$#', '/api', $apiBaseUrl);
            
            $endpoint = '/transactions/' . $transaction->external_id;
            
            // Se a URL base já termina com /api, não precisa adicionar novamente
            if (substr($apiBaseUrl, -4) === '/api') {
                $fullUrl = $apiBaseUrl . $endpoint;
            } else {
                $fullUrl = $apiBaseUrl . '/api' . $endpoint;
            }
            
            $this->logRequest('check_transaction_status_request', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'api_base_url' => $apiBaseUrl,
                'endpoint' => $endpoint,
                'full_url' => $fullUrl,
                'note' => 'Pluggou pode não ter endpoint GET - funciona via webhook',
            ], []);
            
            // Make request to Pluggou API
            // NOTA: Este endpoint pode não existir - Pluggou funciona via webhooks
            $response = Http::timeout(10) // Timeout menor pois pode não existir
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Public-Key' => $publicKey,
                    'X-Secret-Key' => $secretKey,
                ])
                ->get($fullUrl);

            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseJson = $response->json();
            
            $this->logRequest('check_transaction_status', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'status_code' => $statusCode,
                'response_body' => substr($responseBody, 0, 500),
                'url' => $fullUrl,
            ], $responseJson);

            // Se for 404, o endpoint não existe - Pluggou funciona via webhook
            if ($statusCode === 404) {
                $this->logRequest('check_transaction_status_endpoint_not_found', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'note' => 'Endpoint GET não existe na Pluggou - funciona via webhook apenas',
                ], []);
                
                // Retornar sucesso mas sem mudança (webhook vai atualizar)
                return [
                    'success' => true,
                    'status' => $transaction->status,
                    'paid' => $transaction->status === 'paid',
                    'status_changed' => false,
                    'note' => 'Pluggou não possui endpoint GET - atualização via webhook',
                ];
            }

            if (!$response->successful()) {
                $errorBody = $response->body();
                
                $this->logError('check_transaction_status', 'Erro na API Pluggou', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'status_code' => $statusCode,
                    'error_body' => substr($errorBody, 0, 200),
                    'url' => $fullUrl,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Erro ao consultar status na API Pluggou (HTTP ' . $statusCode . ')',
                    'status_code' => $statusCode,
                ];
            }

            // Use responseJson that was already parsed
            $responseData = $responseJson;
            
            // Validate response data
            if (!$responseData || !is_array($responseData)) {
                $this->logError('check_transaction_status', 'Resposta inválida da API Pluggou', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'response_body' => substr($responseBody, 0, 200),
                    'url' => $fullUrl,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'Resposta inválida da API do gateway (não é JSON válido)',
                ];
            }
            
            // Pluggou retorna { success: true, data: {...} }
            $transactionData = $responseData['data'] ?? $responseData;
            
            // Log the full response for debugging
            $this->logRequest('check_transaction_status_response', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'response_data' => $transactionData,
                'has_status' => isset($transactionData['status']),
                'status_value' => $transactionData['status'] ?? null,
                'has_paid_at' => isset($transactionData['paid_at']),
                'paid_at_value' => $transactionData['paid_at'] ?? null,
            ], []);
            
            // PRIORIDADE 1: Verificar se tem paid_at - se tiver, está PAGO
            $paidAt = $transactionData['paid_at'] ?? null;
            $hasPaidAt = ($paidAt !== null && $paidAt !== '' && $paidAt !== 'null');
            
            // PRIORIDADE 2: Mapear status do gateway
            $gatewayStatus = $transactionData['status'] ?? 'pending';
            $mappedStatus = $this->mapPluggouStatus($gatewayStatus);
            
            // PRIORIDADE 3: Se tem paid_at, SEMPRE é paid
            if ($hasPaidAt) {
                $mappedStatus = 'paid';
                $this->logRequest('check_transaction_status_paidAt_detected', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'gateway_status' => $gatewayStatus,
                    'paid_at' => $paidAt,
                    'mapped_status' => 'paid',
                    'reason' => 'paid_at presente na resposta',
                ], []);
            }
            
            // Log status mapping com TODOS os detalhes
            $this->logRequest('check_transaction_status_mapping', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'gateway_status' => $gatewayStatus,
                'mapped_status' => $mappedStatus,
                'current_status' => $transaction->status,
                'has_paid_at' => $hasPaidAt,
                'paid_at' => $paidAt,
                'status_will_change' => ($transaction->status !== $mappedStatus),
                'response_keys' => array_keys($transactionData),
            ], []);
            
            // Check if status changed
            $oldStatus = $transaction->status;
            $statusChanged = $oldStatus !== $mappedStatus;
            
            // Se tem paid_at mas status ainda não é paid, FORÇAR atualização
            $shouldForceUpdateToPaid = ($hasPaidAt && $oldStatus !== 'paid' && $mappedStatus === 'paid');
            
            // Update transaction if status changed or has paid_at
            if ($statusChanged || $shouldForceUpdateToPaid) {
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
                        $this->logError('check_transaction_status', 'Erro ao parsear paid_at: ' . $e->getMessage(), [
                            'transaction_id' => $transaction->transaction_id,
                            'paid_at' => $paidAt,
                        ]);
                        $updateData['paid_at'] = now();
                    }
                }
                
                // FORÇAR atualização mesmo se status não mudou mas tem paid_at
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
                    'has_paid_at' => $hasPaidAt,
                    'paid_at' => $paidAt,
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
            }

            return [
                'success' => true,
                'status' => $mappedStatus,
                'paid' => $mappedStatus === 'paid',
                'data' => $transactionData,
                'status_changed' => $statusChanged,
                'old_status' => $oldStatus,
            ];

        } catch (\Exception $e) {
            $this->logError('check_transaction_status', $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'trace' => $e->getTraceAsString(),
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
        switch ($status) {
            case 'paid':
                return 'transaction.paid';
            case 'failed':
                return 'transaction.failed';
            case 'expired':
                return 'transaction.expired';
            case 'refunded':
            case 'partially_refunded':
                return 'transaction.refunded';
            case 'chargeback':
                return 'transaction.chargeback';
            case 'cancelled':
                return 'transaction.cancelled';
            default:
                return null;
        }
    }
}

