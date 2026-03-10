<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VersellGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via Versell
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway Versell não está configurado');
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

            // Prepare Versell payload baseado no backup funcional
            $payload = $this->prepareVersellPayload($transaction, $data);

            // Send request to Versell usando o endpoint correto do backup
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'vspi' => $this->credentials->public_key,
                    'vsps' => $this->credentials->secret_key,
                ])
                ->post($this->gateway->getApiUrl('/api/v1/gateway/request-qrcode'), $payload);

            $this->logRequest('create_transaction', $payload, $response->json());

            if (!$response->successful()) {
                throw new \Exception('Erro na API Versell: ' . $response->body());
            }

            $responseData = $response->json();

            // Check if response is OK (baseado no backup)
            if (!isset($responseData['response']) || $responseData['response'] !== 'OK') {
                throw new \Exception('Erro na resposta do gateway: ' . ($responseData['message'] ?? 'Resposta inválida'));
            }

            // Format response baseado no backup funcional
            $formattedResponse = $this->formatVersellResponse($responseData, $transaction);

            // Update transaction with external ID and payment data
            $transaction->update([
                'external_id' => $responseData['idTransaction'] ?? null,
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
     * Prepare Versell payload baseado no backup funcional
     */
    private function prepareVersellPayload(Transaction $transaction, array $data): array
    {
        $webhookUrl = config('app.url') . '/webhook/versell';
        
        // If we're in production, use the full domain URL
        if (app()->environment('production') || app()->environment('staging')) {
            $webhookUrl = 'https://app.brpix.com/webhook/versell';
        }

        $payload = [
            'requestNumber' => $transaction->transaction_id, // Use our internal transaction ID
            'amount' => $transaction->amount, // Valor em reais
            'callbackUrl' => $webhookUrl,
        ];

        return $payload;
    }

    /**
     * Format Versell response baseado no backup funcional
     */
    private function formatVersellResponse(array $responseData, Transaction $transaction): array
    {
        return [
            'gateway_transaction_id' => $responseData['idTransaction'] ?? null,
            'gateway_id' => $responseData['idTransaction'] ?? null,
            'status' => 'pending', // Versell sempre retorna pending inicialmente
            'payment_data' => [
                'pix' => [
                    'payload' => $responseData['paymentCode'] ?? null,
                    'qrcode' => $responseData['paymentCode'] ?? null,
                    'expirationDate' => null,
                ],
                'amount' => $transaction->amount,
                'installments' => 1,
                'customer' => $transaction->customer_data,
            ],
            'raw_response' => $responseData
        ];
    }
}