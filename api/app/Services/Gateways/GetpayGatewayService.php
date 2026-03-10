<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GetpayGatewayService extends BaseGatewayService
{
    /**
     * Create transaction via GetPay
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway GetPay não está configurado');
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

            // Step 1: Login to GetPay
            $token = $this->getGetPayToken();
            if (!$token) {
                throw new \Exception('Falha na autenticação GetPay');
            }

            // Prepare customer data
            $customer = $transaction->customer_data;
            $document = preg_replace('/[^0-9]/', '', $customer['document']);
            $phone = preg_replace('/[^0-9]/', '', $customer['phone'] ?? '');
            
            // Generate unique external ID with timestamp to avoid conflicts
            $externalId = 'PXB_' . time() . '_' . strtoupper(bin2hex(random_bytes(8)));
            
            $identification = $phone !== '' ? $phone : substr($externalId, 0, 12);

            // Step 2: Create payment
            $paymentPayload = [
                'externalId' => $externalId,
                'amount' => (float)$transaction->amount,
                'document' => $document,
                'name' => $customer['name'],
                'identification' => $identification,
                'expire' => 3600, // 1 hour
                'description' => $data['description'] ?? 'Pagamento via PixBolt'
            ];

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'User-Agent' => 'PixBolt-API/1.0',
                ])
                ->post('https://hub.getpay.store/api/create-payment', $paymentPayload);

            $this->logRequest('create_payment', $paymentPayload, $response->json());

            if (!$response->successful()) {
                throw new \Exception('Falha na criação do pagamento GetPay: ' . $response->body());
            }

            $paymentData = $response->json();

            if (empty($paymentData['success'])) {
                throw new \Exception('Erro ao processar pagamento GetPay: ' . ($paymentData['message'] ?? 'Erro desconhecido'));
            }

            $responseData = $paymentData['data'] ?? [];

            // Format response based on backup
            $formattedResponse = [
                'gateway_transaction_id' => $responseData['uuid'] ?? null,
                'gateway_id' => $responseData['uuid'] ?? null,
                'status' => 'pending',
                'payment_data' => [
                    'pix' => [
                        'payload' => $responseData['pix'] ?? null,
                        'qr_code' => $responseData['pix'] ?? null,
                        'expirationDate' => now()->addSeconds(3600)->toISOString(),
                    ],
                    'getpay_data' => $responseData
                ],
                'raw_response' => $paymentData
            ];

            // Update transaction with external ID and payment data
            $transaction->update([
                'external_id' => $responseData['uuid'] ?? null,
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
     * Get GetPay authentication token (baseado no backup)
     */
    private function getGetPayToken(): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                ])
                ->post('https://hub.getpay.store/api/login', [
                    'email' => $this->credentials->public_key, // Using public_key as email
                    'password' => $this->credentials->secret_key, // Using secret_key as password
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!empty($data['success']) && !empty($data['token'])) {
                    return $data['token'];
                }
            }

            $this->logError('get_token', 'Login failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            $this->logError('get_token', $e->getMessage());
            return null;
        }
    }
}