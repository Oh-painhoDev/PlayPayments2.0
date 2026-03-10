<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HopyGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Hopy (SkalePay)
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway Hopy não está configurado');
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

            // Prepare Hopy payload baseado no backup funcional
            $payload = $this->prepareHopyPayload($transaction, $data);

            // Send request to Hopy
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                ])
                ->withBasicAuth($this->credentials->secret_key, 'x')
                ->post($this->gateway->getApiUrl('/transactions'), $payload);

            $this->logRequest('create_transaction', $payload, $response->json());

            if (!$response->successful()) {
                throw new \Exception('Erro na API Hopy: ' . $response->body());
            }

            $responseData = $response->json();

            // Format response baseado no backup funcional
            $formattedResponse = $this->formatHopyResponse($responseData, $transaction);

            // Update transaction with external ID and payment data
            $transaction->update([
                'external_id' => $responseData['id'] ?? null,
                'payment_data' => $formattedResponse['payment_data'],
            ]);

            // Dispatch webhook event for transaction.created
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
     * Prepare Hopy payload baseado no backup funcional
     */
    private function prepareHopyPayload(Transaction $transaction, array $data): array
    {
        $customer = $transaction->customer_data;

        // Configure webhook URL
        $webhookUrl = config('app.url') . '/webhook/hopy';
        
        // If we're in production, use the full domain URL
        if (app()->environment('production') || app()->environment('staging')) {
            $webhookUrl = 'https://app.brpix.com/webhook/hopy';
        }

        $payload = [
            'amount' => (int)round($transaction->amount * 100), // Convert to cents with proper rounding
            'paymentMethod' => $transaction->payment_method,
            'customer' => [
                'name' => $customer['name'],
                'email' => $customer['email'],
                'phone' => $customer['phone'] ?? '',
                'document' => [
                    'number' => preg_replace('/[^0-9]/', '', $customer['document']),
                    'type' => $this->getDocumentType($customer['document'])
                ]
            ],
            'items' => [
                [
                    'title' => $data['description'] ?? 'Pagamento via PixBolt',
                    'unitPrice' => (int)round($transaction->amount * 100),
                    'quantity' => 1,
                    'tangible' => false
                ]
            ],
            'pix' => [
                'expiresIn' => 3600 // 1 hour
            ],
            'metadata' => $transaction->transaction_id,
            'postbackUrl' => $webhookUrl,
            'installments' => (int)($data['installments'] ?? 1)
        ];

        return $payload;
    }

    /**
     * Format Hopy response baseado no backup funcional
     */
    private function formatHopyResponse(array $responseData, Transaction $transaction): array
    {
        return [
            'gateway_transaction_id' => $responseData['id'] ?? null,
            'gateway_id' => $responseData['id'] ?? null,
            'status' => $this->mapHopyStatus($responseData['status'] ?? 'waiting_payment'),
            'payment_data' => [
                'pix' => [
                    'qrcode' => $responseData['pix']['qrcode'] ?? null,
                    'payload' => $responseData['pix']['qrcode'] ?? null,
                    'expirationDate' => $responseData['pix']['expirationDate'] ?? null,
                ],
                'amount' => $responseData['amount'] ?? null,
                'installments' => $responseData['installments'] ?? null,
                'customer' => $responseData['customer'] ?? null,
            ],
            'raw_response' => $responseData
        ];
    }

    /**
     * Map Hopy status to our status
     */
    private function mapHopyStatus(string $gatewayStatus): string
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
}