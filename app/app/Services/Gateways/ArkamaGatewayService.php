<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArkamaGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Arkama
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway Arkama não está configurado');
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

            // Prepare Arkama payload with correct format
            $payload = $this->prepareArkamaPayload($transaction, $data);

            // Send request to Arkama
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'Authorization' => 'Bearer ' . $this->credentials->secret_key,
                ])
                ->post($this->gateway->api_url, $payload);

            $this->logRequest('create_transaction', $payload, $response->json());

            if (!$response->successful()) {
                throw new \Exception('Erro na API Arkama: ' . $response->body());
            }

            $responseData = $response->json();

            // Log the response for debugging
            $this->logRequest('arkama_response_debug', [], $responseData);

            // Update transaction with external ID and payment data
            $transaction->update([
                'external_id' => $responseData['id'] ?? $responseData['order_id'] ?? null,
                'payment_data' => $this->formatArkamaResponse($responseData, $transaction->payment_method),
            ]);

            // Dispatch webhook event for transaction.created
            $this->dispatchTransactionCreatedWebhook($transaction);

            return [
                'success' => true,
                'transaction' => $transaction,
                'gateway_response' => $this->formatArkamaResponse($responseData, $transaction->payment_method),
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
     * Prepare Arkama payload with correct format
     */
    private function prepareArkamaPayload(Transaction $transaction, array $data): array
    {
        $webhookUrl = config('app.url') . '/webhook/arkama';

        // Arkama expects specific field names
        $payload = [
            'value' => $transaction->amount, // Arkama uses 'value' instead of 'amount'
            'paymentMethod' => $this->mapPaymentMethod($transaction->payment_method), // Arkama uses camelCase
            'external_reference' => $transaction->transaction_id,
            'webhook_url' => $webhookUrl,
            'ip' => request()->ip() ?? '127.0.0.1', // Required field
            'items' => [ // Required field
                [
                    'title' => $data['description'] ?? 'Produto/Serviço',
                    'unitPrice' => $transaction->amount,
                    'quantity' => 1,
                    'isDigital' => true
                ]
            ],
            'customer' => [
                'name' => $data['customer']['name'],
                'email' => $data['customer']['email'],
                'document' => $this->formatDocument($data['customer']['document']),
            ],
            'shipping' => [
                'address' => [
                    'street' => 'Rua Principal',
                    'number' => '123',
                    'neighborhood' => 'Centro',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zipCode' => '01000000',
                    'country' => 'BR'
                ]
            ]
        ];

        // Add phone if provided
        if (!empty($data['customer']['phone'])) {
            $payload['customer']['phone'] = $this->formatPhone($data['customer']['phone']);
        }

        // Add installments for credit card
        if ($transaction->payment_method === 'credit_card') {
            $payload['installments'] = $data['installments'] ?? 1;
        }

        return $payload;
    }

    /**
     * Format Arkama response to match expected PIX format
     */
    private function formatArkamaResponse(array $responseData, string $paymentMethod): array
    {
        $formattedResponse = $responseData;
        
        // For PIX payments, ensure we have the correct structure
        if ($paymentMethod === 'pix') {
            // Check various possible locations for PIX data in Arkama response
            $pixPayload = null;
            $qrCodeUrl = null;
            
            // Try different possible response structures from Arkama
            if (isset($responseData['pix']['qr_code'])) {
                $pixPayload = $responseData['pix']['qr_code'];
            } elseif (isset($responseData['pix']['payload'])) {
                $pixPayload = $responseData['pix']['payload'];
            } elseif (isset($responseData['qr_code'])) {
                $pixPayload = $responseData['qr_code'];
            } elseif (isset($responseData['payload'])) {
                $pixPayload = $responseData['payload'];
            } elseif (isset($responseData['pix_code'])) {
                $pixPayload = $responseData['pix_code'];
            } elseif (isset($responseData['code'])) {
                $pixPayload = $responseData['code'];
            }
            
            // Try to find QR code URL
            if (isset($responseData['pix']['qr_code_url'])) {
                $qrCodeUrl = $responseData['pix']['qr_code_url'];
            } elseif (isset($responseData['qr_code_url'])) {
                $qrCodeUrl = $responseData['qr_code_url'];
            }
            
            // Ensure we have the PIX data in the expected format
            if ($pixPayload) {
                $formattedResponse['payment_data'] = [
                    'pix' => [
                        'payload' => $pixPayload,
                        'qrcode' => $pixPayload,
                        'qr_code' => $pixPayload,
                    ]
                ];
                
                if ($qrCodeUrl) {
                    $formattedResponse['payment_data']['pix']['qr_code_url'] = $qrCodeUrl;
                }
            } else {
                // Log if we can't find PIX data
                $this->logError('pix_data_not_found', 'PIX payload not found in Arkama response', $responseData);
            }
        }
        
        return $formattedResponse;
    }

    /**
     * Map payment method to Arkama format
     */
    private function mapPaymentMethod(string $paymentMethod): string
    {
        return match($paymentMethod) {
            'pix' => 'pix',
            'credit_card' => 'credit_card',
            'bank_slip' => 'boleto',
            default => 'pix'
        };
    }
}