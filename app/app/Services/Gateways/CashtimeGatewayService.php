<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CashtimeGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Cashtime
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway Cashtime não está configurado');
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

            // Prepare Cashtime payload
            $payload = $this->prepareCashtimePayload($transaction, $data);

            // Build headers
            $headers = [
                'Content-Type' => 'application/json',
                'User-Agent' => 'PixBolt-API/1.0',
                'x-authorization-key' => $this->credentials->secret_key,
            ];

            // Add public key if exists and is not dummy
            if ($this->credentials->public_key && $this->credentials->public_key !== 'dummy_public_key') {
                $headers['x-store-key'] = $this->credentials->public_key;
            }

            // Log request details for debugging
            \Log::info('Cashtime - Enviando requisição', [
                'url' => $this->gateway->getApiUrl('/cob'),
                'payload' => $payload,
                'headers' => array_merge($headers, ['x-authorization-key' => '***'])
            ]);

            // Send request to Cashtime with increased timeout
            $response = Http::timeout(60)
                ->withHeaders($headers)
                ->post($this->gateway->getApiUrl('/cob'), $payload);

            // Log response
            $responseBody = $response->body();
            $responseJson = $response->json();
            $statusCode = $response->status();
            
            \Log::info('Cashtime - Resposta recebida', [
                'status_code' => $statusCode,
                'response_body' => $responseBody,
                'response_json' => $responseJson
            ]);
            
            $this->logRequest('create_transaction', $payload, $responseJson);

            if (!$response->successful()) {
                $errorMessage = $responseJson['message'] ?? $responseJson['error'] ?? $responseBody ?? 'Erro desconhecido na API Cashtime';
                \Log::error('Cashtime - Erro na API', [
                    'status_code' => $statusCode,
                    'error_message' => $errorMessage,
                    'full_response' => $responseBody
                ]);
                throw new \Exception('Erro na API Cashtime: ' . $errorMessage);
            }

            $responseData = $response->json();

            // Update transaction with external ID and payment data
            $transaction->update([
                'external_id' => $responseData['id'] ?? null,
                'payment_data' => $this->formatCashtimeResponse($responseData),
            ]);

            // Dispatch webhook event for transaction.created
            $this->dispatchTransactionCreatedWebhook($transaction);

            return [
                'success' => true,
                'transaction' => $transaction,
                'gateway_response' => $this->formatCashtimeResponse($responseData),
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
     * Prepare Cashtime payload
     */
    private function prepareCashtimePayload(Transaction $transaction, array $data): array
    {
        $customer = $transaction->customer_data;
        
        // Configure webhook URL
        $webhookUrl = config('app.url') . '/webhook/cashtime';
        
        // If we're in production, use the full domain URL
        if (app()->environment('production') || app()->environment('staging')) {
            $webhookUrl = 'https://app.brpix.com/webhook/cashtime';
        }

        $payload = [
            'postbackUrl' => $webhookUrl,
            'amount' => (int)round($transaction->amount * 100), // Convert to cents
        ];

        // Add optional fields
        if (isset($data['externalCode'])) {
            $payload['externalCode'] = $data['externalCode'];
        } else {
            $payload['externalCode'] = $transaction->transaction_id;
        }

        if (request()->ip()) {
            $payload['ip'] = request()->ip();
        }

        // Add metadata if provided
        if (isset($data['metadata']) || isset($customer)) {
            $payload['metadata'] = [
                'provider' => 'PixBolt',
                'user_identification_number' => $customer['document'] ?? '',
                'user_email' => $customer['email'] ?? '',
                'user_name' => $customer['name'] ?? '',
            ];

            // Merge with additional metadata if provided
            if (isset($data['metadata']) && is_array($data['metadata'])) {
                $payload['metadata'] = array_merge($payload['metadata'], $data['metadata']);
            }
        }

        return $payload;
    }

    /**
     * Format Cashtime response
     */
    private function formatCashtimeResponse(array $responseData): array
    {
        // Log the raw response for debugging
        $this->logRequest('cashtime_response_debug', [], $responseData);
        
        $result = [
            'gateway_transaction_id' => $responseData['id'] ?? null,
            'gateway_id' => $responseData['id'] ?? null,
            'status' => $this->mapCashtimeStatus($responseData['status'] ?? 'pending'),
            'external_id' => $responseData['id'] ?? null,
            'payment_data' => [
                'pix' => [
                    'payload' => $responseData['pix']['payload'] ?? null,
                    'qrcode' => $responseData['pix']['payload'] ?? null,
                    'encodedImage' => $responseData['pix']['encodedImage'] ?? null,
                    'expirationDate' => $responseData['expirationDate'] ?? null,
                ],
            ],
            'raw_response' => $responseData
        ];
        
        return $result;
    }

    /**
     * Map Cashtime status to our status
     */
    private function mapCashtimeStatus(string $gatewayStatus): string
    {
        return match(strtolower($gatewayStatus)) {
            'pending', 'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback', 'infraction' => 'chargeback',
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
}
