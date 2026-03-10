<?php

namespace App\Services\Gateways;

use App\Models\User;
use App\Models\Transaction;
use App\Services\BaaS\E2BankProvider;
use App\Services\RetentionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class E2BankGatewayService extends BaseGatewayService
{
    private E2BankProvider $e2bank;

    public function __construct($gateway)
    {
        parent::__construct($gateway);
        $this->e2bank = new E2BankProvider();
    }

    public function createTransaction(User $user, array $data): array
    {
        try {
            if (!$this->isConfigured()) {
                throw new \Exception('Gateway E2 Bank não está configurado');
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

            // Only PIX supported for now
            if ($data['payment_method'] !== 'pix') {
                throw new \Exception('E2 Bank suporta apenas PIX no momento');
            }

            // Create PIX QR Code via E2 Bank
            $result = $this->e2bank->createPixQRCode([
                'amount' => $transaction->amount,
                'transaction_id' => $transaction->transaction_id,
                'customer_name' => $data['customer']['name'] ?? null,
                'customer_document' => $data['customer']['document'] ?? null,
                'description' => $data['description'] ?? 'Pagamento PIX',
                'expiration' => 3600, // 1 hour
            ]);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Erro ao criar QR Code E2 Bank');
            }

            // NÃO gerar QR Code no backend - o frontend vai gerar usando JavaScript
            // Retornar apenas o código PIX para o frontend gerar o QR Code
            $qrCodeBase64 = null;

            // Update transaction with E2 Bank data
            $transaction->update([
                'external_id' => $result['txid'],
                'payment_data' => [
                    'payment_data' => [
                        'pix' => [
                            'qrcode' => $result['qr_code_text'],
                            'payload' => $result['qr_code_text'],
                            'encodedImage' => $qrCodeBase64,
                            'txid' => $result['txid'],
                            'expiration' => $result['expiration'],
                        ]
                    ],
                    'e2bank_response' => $result['raw_response'] ?? []
                ]
            ]);

            $this->logRequest('create_qrcode', [
                'transaction_id' => $transaction->transaction_id,
                'amount' => $transaction->amount
            ], $result);

            // Dispatch webhook event for transaction.created
            $this->dispatchTransactionCreatedWebhook($transaction);

            return [
                'success' => true,
                'transaction' => $transaction,
                'payment_data' => $transaction->payment_data,
            ];

        } catch (\Exception $e) {
            $this->logError('create_transaction', $e->getMessage(), $data);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function checkTransactionStatus(Transaction $transaction): array
    {
        try {
            if (!$transaction->external_id) {
                return [
                    'success' => false,
                    'error' => 'Transaction does not have external_id'
                ];
            }

            $result = $this->e2bank->getPixQRCodeStatus($transaction->external_id);

            if (!$result['success']) {
                return $result;
            }

            $this->logRequest('check_status', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id
            ], $result);

            // Map E2 Bank status to our status
            $e2bankStatus = $result['status'] ?? 'UNKNOWN';
            $mappedStatus = $this->mapE2BankPixInStatus($e2bankStatus);
            $oldStatus = $transaction->status;
            $statusChanged = $oldStatus !== $mappedStatus;

            // Update transaction if status changed
            if ($statusChanged) {
                $updateData = ['status' => $mappedStatus];
                
                // Set paid_at if status changed to paid
                if ($mappedStatus === 'paid' && $oldStatus !== 'paid') {
                    $updateData['paid_at'] = now();
                }
                
                $transaction->update($updateData);
                
                // Handle status changes (process wallet and dispatch webhooks immediately)
                try {
                    // Refresh transaction to get latest data
                    $transaction->refresh();
                    
                    // Call handleStatusChange to process wallet and dispatch webhooks
                    $webhookController = app(\App\Http\Controllers\WebhookController::class);
                    $webhookController->handleStatusChange($transaction, $oldStatus, $mappedStatus);
                    
                    $this->logRequest('status_change_handled_e2bank', [
                        'transaction_id' => $transaction->transaction_id,
                        'old_status' => $oldStatus,
                        'new_status' => $mappedStatus,
                        'webhook_dispatched' => true,
                    ], []);
                } catch (\Exception $e) {
                    $this->logError('status_change_handle_e2bank', 'Erro ao processar mudança de status: ' . $e->getMessage(), [
                        'transaction_id' => $transaction->transaction_id,
                        'old_status' => $oldStatus,
                        'new_status' => $mappedStatus,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            return [
                'success' => true,
                'status' => $mappedStatus,
                'paid' => $result['paid'],
                'data' => $result['data'],
                'status_changed' => $statusChanged,
            ];

        } catch (\Exception $e) {
            $this->logError('check_status', $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Map E2 Bank PIX IN status to our status
     */
    private function mapE2BankPixInStatus(string $status): string
    {
        return match(strtoupper($status)) {
            'CONCLUIDA', 'PAID', 'PAGO' => 'paid',
            'PENDENTE', 'PENDING', 'AGUARDANDO' => 'pending',
            'EXPIRADA', 'EXPIRED', 'VENCIDA' => 'expired',
            'CANCELADA', 'CANCELLED', 'CANCELADO' => 'cancelled',
            'FALHOU', 'FAILED', 'ERRO' => 'failed',
            default => 'pending',
        };
    }

    public function cancelTransaction(Transaction $transaction): array
    {
        try {
            // E2 Bank PIX QR Codes expire automatically
            $transaction->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Transaction cancelled successfully'
            ];

        } catch (\Exception $e) {
            $this->logError('cancel_transaction', $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function refundTransaction(Transaction $transaction): array
    {
        try {
            // Refunds would need to be handled via PIX OUT
            // For now, just mark as refunded
            $transaction->update([
                'status' => 'refunded',
                'refunded_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Transaction marked as refunded'
            ];

        } catch (\Exception $e) {
            $this->logError('refund_transaction', $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
