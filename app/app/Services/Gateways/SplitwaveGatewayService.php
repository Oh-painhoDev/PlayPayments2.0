<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SplitwaveGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Splitwave (ReflowPay)
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway Splitwave não está configurado');
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

            // Prepare Splitwave payload baseado no backup funcional
            $payload = $this->prepareSplitwavePayload($transaction, $data);

            // Send request to Splitwave
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'x-authorization-key' => $this->credentials->secret_key,
                ])
                ->post($this->gateway->getApiUrl('/cob'), $payload);

            $this->logRequest('create_transaction', $payload, $response->json());

            if (!$response->successful()) {
                throw new \Exception('Erro na API Splitwave: ' . $response->body());
            }

            $responseData = $response->json();

            // Update transaction with external ID and payment data
            $transaction->update([
                'external_id' => $responseData['id'] ?? null,
                'payment_data' => $this->formatSplitwaveResponse($responseData, $transaction->payment_method),
            ]);

            // Dispatch webhook event for transaction.created
            $this->dispatchTransactionCreatedWebhook($transaction);

            return [
                'success' => true,
                'transaction' => $transaction,
                'gateway_response' => $this->formatSplitwaveResponse($responseData, $transaction->payment_method),
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
     * Prepare Splitwave payload baseado no backup funcional
     */
    private function prepareSplitwavePayload(Transaction $transaction, array $data): array
    {
        $customer = $transaction->customer_data;
        
        // Configure webhook URL
        $webhookUrl = config('app.url') . '/webhook/splitwave';
        
        // If we're in production, use the full domain URL
        if (app()->environment('production') || app()->environment('staging')) {
            $webhookUrl = 'https://app.brpix.com/webhook/splitwave';
        }

        $payload = [
            'postbackUrl' => $webhookUrl,
            'paymentMethod' => $transaction->payment_method,
            'ip' => request()->ip() ?? '127.0.0.1',
            'amount' => (int)round($transaction->amount * 100), // Convert to cents with proper rounding
            'externalRef' => $transaction->transaction_id, // Add externalRef explicitly
            'installments' => (int)($data['installments'] ?? 1),
            'installmentRate' => 0, // Default to 0
            'isInfoProducts' => false, // Required boolean field
            'orderId' => $transaction->transaction_id, // Use our transaction ID as orderId
            'customer' => [
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'] ?? '',
                'document' => [
                    'number' => preg_replace('/[^0-9]/', '', $customer['document']),
                    'type' => $this->getDocumentType($customer['document'])
                ]
            ],
            'shipping' => [
                'fee' => 0,
                'address' => [
                    'street' => 'Sem Endereço',
                    'streetNumber' => '0',
                    'zipCode' => '00000000',
                    'neighborhood' => 'Desconhecido',
                    'city' => 'Cidade',
                    'state' => 'SP',
                    'country' => 'Brasil',
                    'complement' => 'N/A'
                ]
            ],
            'items' => [
                [
                    'title' => $data['description'] ?? 'Pagamento via PixBolt',
                    'description' => $data['description'] ?? 'Pagamento via PixBolt',
                    'unitPrice' => (int)round($transaction->amount * 100),
                    'quantity' => 1,
                    'tangible' => true
                ]
            ]
        ];

        return $payload;
    }

    /**
     * Format Splitwave response
     */
    private function formatSplitwaveResponse(array $responseData, string $paymentMethod): array
    {
        // Log the raw response for debugging
        $this->logRequest('splitwave_response_debug', [], $responseData);
        
        $result = [
            'gateway_transaction_id' => $responseData['id'] ?? null,
            'gateway_id' => $responseData['id'] ?? null,
            'status' => $this->mapSplitwaveStatus($responseData['status'] ?? 'pending'),
            'external_id' => $responseData['id'] ?? null,
            'payment_data' => [
                'pix' => [
                    'payload' => $responseData['pix']['payload'] ?? null,
                    'qrcode' => $responseData['pix']['payload'] ?? null,
                    'encodedImage' => $responseData['pix']['encodedImage'] ?? $responseData['pix']['qr_code_url'] ?? $responseData['pix']['qrCodeBase64'] ?? null,
                    'expirationDate' => $responseData['pix']['expirationDate'] ?? null,
                ],
                'amount' => $responseData['amount'] ?? null,
                'installments' => $responseData['installments'] ?? null,
                'customer' => $responseData['customer'] ?? null,
            ],
            'raw_response' => $responseData
        ];
        
        
        return $result;
    }

    /**
     * Map Splitwave status to our status
     */
    private function mapSplitwaveStatus(string $gatewayStatus): string
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
}