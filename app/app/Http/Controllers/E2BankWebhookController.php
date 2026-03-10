<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Services\WebhookService;

class E2BankWebhookController extends Controller
{
    /**
     * Handle E2 Bank PIX IN webhook (QR Code payments)
     */
    public function pixIn(Request $request)
    {
        try {
            Log::info('E2 Bank PIX IN Webhook recebido', [
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validate required fields
            if (empty($data['txid'])) {
                Log::warning('E2 Bank webhook inválido - txid ausente', $data);
                return response()->json(['error' => 'txid ausente'], 400);
            }

            // Find transaction by external_id (txid)
            $transaction = Transaction::where('external_id', $data['txid'])->first();

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook E2 Bank', [
                    'txid' => $data['txid'],
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Map E2 Bank status to our status
            $newStatus = $this->mapE2BankPixInStatus($data['status'] ?? 'UNKNOWN');
            $oldStatus = $transaction->status;

            Log::info('Status de transação mapeado', [
                'original_status' => $data['status'] ?? 'UNKNOWN',
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
                'webhook_data' => $data,
            ];

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = now();
            }

            $transaction->update($updateData);

            // Handle status changes (credit wallet if paid)
            if ($newStatus !== $oldStatus) {
                $this->handleStatusChange($transaction, $oldStatus, $newStatus);
            }

            // Dispatch webhook to merchant if configured
            if ($transaction->metadata['postbackUrl'] ?? null) {
                $webhookService = new WebhookService();
                $webhookService->dispatch($transaction);
            }

            Log::info('Webhook E2 Bank PIX IN processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook E2 Bank PIX IN: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle E2 Bank PIX OUT webhook (withdrawals)
     */
    public function pixOut(Request $request)
    {
        try {
            Log::info('E2 Bank PIX OUT Webhook recebido', [
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validate required fields
            if (empty($data['idempotencyKey'])) {
                Log::warning('E2 Bank webhook inválido - idempotencyKey ausente', $data);
                return response()->json(['error' => 'idempotencyKey ausente'], 400);
            }

            // Find withdrawal by our internal ID
            $withdrawalId = $data['idempotencyKey'];
            $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)->first();

            if (!$withdrawal) {
                Log::warning('Saque não encontrado para webhook E2 Bank', [
                    'withdrawal_id' => $withdrawalId,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Saque não encontrado'], 404);
            }

            // Map E2 Bank status to our status
            $newStatus = $this->mapE2BankPixOutStatus($data['status'] ?? 'UNKNOWN');
            $oldStatus = $withdrawal->status;

            Log::info('Status de saque mapeado', [
                'original_status' => $data['status'] ?? 'UNKNOWN',
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'withdrawal_id' => $withdrawal->withdrawal_id
            ]);

            // Update withdrawal
            $updateData = [
                'status' => $newStatus,
                'webhook_data' => $data,
            ];

            // Update external_id if provided
            if (isset($data['id']) && $data['id'] !== $withdrawal->external_id) {
                $updateData['external_id'] = $data['id'];
            }

            // Set completed_at if status changed to completed
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $updateData['completed_at'] = now();
            }

            $withdrawal->update($updateData);

            // Handle status changes (refund if failed, update total_withdrawn if completed)
            if ($newStatus !== $oldStatus) {
                $this->handleWithdrawalStatusChange($withdrawal, $oldStatus, $newStatus);
            }

            Log::info('Webhook E2 Bank PIX OUT processado com sucesso', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook E2 Bank PIX OUT: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Map E2 Bank PIX IN status to our status
     */
    protected function mapE2BankPixInStatus(string $status): string
    {
        return match(strtoupper($status)) {
            'ATIVA', 'PENDENTE', 'EM_PROCESSAMENTO' => 'pending',
            'CONCLUIDA', 'PAGA' => 'paid',
            'CANCELADA' => 'cancelled',
            'EXPIRADA' => 'expired',
            'REMOVIDA_PELO_PSP', 'REMOVIDA_PELO_USUARIO_RECEBEDOR' => 'cancelled',
            default => 'pending',
        };
    }

    /**
     * Map E2 Bank PIX OUT status to our status
     */
    protected function mapE2BankPixOutStatus(string $status): string
    {
        return match(strtoupper($status)) {
            'PENDING', 'PROCESSING', 'EM_PROCESSAMENTO' => 'processing',
            'COMPLETED', 'APPROVED', 'CONCLUIDO', 'PAGO' => 'completed',
            'CANCELLED', 'CANCELED', 'CANCELADO' => 'cancelled',
            'FAILED', 'FALHOU', 'ERRO' => 'failed',
            default => 'processing',
        };
    }

    /**
     * Handle transaction status changes
     */
    protected function handleStatusChange(Transaction $transaction, string $oldStatus, string $newStatus): void
    {
        $user = $transaction->user;
        
        if (!$user) {
            Log::error('User not found for transaction', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id
            ]);
            return;
        }

        $wallet = $user->wallet;
        
        if (!$wallet) {
            Log::error('Wallet não encontrada para usuário', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id
            ]);
            return;
        }

        // If transaction is paid, credit wallet (unless it's retained)
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            // Check if transaction should be retained
            if (!$transaction->is_retained) {
                // Credit the net amount to user's wallet
                $wallet->addCredit(
                    $transaction->net_amount,
                    'transaction',
                    "Pagamento PIX recebido - {$transaction->transaction_id}",
                    [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $transaction->external_id,
                        'amount' => $transaction->amount,
                        'fee' => $transaction->fee_amount,
                        'net_amount' => $transaction->net_amount,
                    ],
                    $transaction->transaction_id
                );

                Log::info('Carteira creditada com pagamento E2 Bank', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->transaction_id,
                    'net_amount' => $transaction->net_amount,
                ]);
            } else {
                Log::info('Transação retida - carteira não creditada', [
                    'transaction_id' => $transaction->transaction_id,
                    'retention_type' => $transaction->retention_type
                ]);
            }
        }
    }

    /**
     * Handle withdrawal status changes (reuse from WithdrawalController logic)
     */
    protected function handleWithdrawalStatusChange(Withdrawal $withdrawal, string $oldStatus, string $newStatus): void
    {
        $user = $withdrawal->user;
        
        if (!$user) {
            Log::error('User not found for withdrawal', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'user_id' => $withdrawal->user_id
            ]);
            return;
        }
        
        $wallet = $user->wallet;
        
        if (!$wallet) {
            Log::error('Wallet não encontrada para usuário em atualização de saque', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->withdrawal_id
            ]);
            return;
        }
        
        // If status changed to failed or cancelled, refund the amount
        if (($newStatus === 'failed' || $newStatus === 'cancelled') && 
            ($oldStatus === 'pending' || $oldStatus === 'processing')) {
            
            // Check if refund already exists
            $existingRefund = \App\Models\WalletTransaction::where('reference_id', $withdrawal->withdrawal_id . '_refund')
                ->where('type', 'credit')
                ->where('category', 'refund')
                ->first();
                
            if ($existingRefund) {
                Log::warning('Refund already exists for withdrawal', [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'existing_refund_id' => $existingRefund->id
                ]);
                return;
            }
            
            // Refund the amount to wallet
            $wallet->addCredit(
                $withdrawal->amount,
                'refund',
                "Estorno de saque {$newStatus} - {$withdrawal->withdrawal_id}",
                [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
                $withdrawal->withdrawal_id . '_refund'
            );
            
            Log::info('Saque E2 Bank estornado para a wallet', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount' => $withdrawal->amount,
            ]);
        }
        
        // If status changed to completed, update total_withdrawn
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();
            
            Log::info('Saque E2 Bank concluído e total_withdrawn atualizado', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount' => $withdrawal->amount,
                'total_withdrawn' => $wallet->total_withdrawn,
            ]);
        }
    }
}
