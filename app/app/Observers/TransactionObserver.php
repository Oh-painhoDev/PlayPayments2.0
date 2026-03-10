<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;

class TransactionObserver
{
    protected $utmifyService;

    public function __construct(UtmifyService $utmifyService)
    {
        $this->utmifyService = $utmifyService;
    }

    /**
     * Handle the Transaction "created" event.
     */
    public function created(Transaction $transaction): void
    {
        // Enviar para UTMify quando transação for criada (se configurado)
        // APENAS PIX será enviado (filtrado no UtmifyService)
        // APENAS se o usuário tiver uma integração UTMify ativa na conta dele
        try {
            Log::info('🔵 TransactionObserver: Transação CRIADA - Verificando UTMify', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'amount' => $transaction->amount,
            ]);
            
            $result = $this->utmifyService->sendTransaction($transaction, 'created');
            
            Log::info('🔵 TransactionObserver: Resultado do envio UTMify', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
                'enviado' => $result ? 'SIM' : 'NÃO',
            ]);
        } catch (\Exception $e) {
            Log::error('🔴 TransactionObserver: ERRO ao enviar transação criada para UTMify', [
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle the Transaction "updated" event.
     */
    public function updated(Transaction $transaction): void
    {
        // Verificar se o status mudou para "paid"
        if ($transaction->isDirty('status')) {
            $oldStatus = $transaction->getOriginal('status');
            $newStatus = $transaction->status;

            // Verificar se mudou para pago
            // APENAS PIX será enviado (filtrado no UtmifyService)
            if ($transaction->isPaid() && !in_array(strtolower($oldStatus), ['paid', 'paid_out', 'paidout', 'completed', 'success', 'successful', 'approved', 'confirmed', 'settled', 'captured'])) {
                try {
                    Log::info('TransactionObserver: Status mudou para PAID, enviando para UTMify', [
                        'transaction_id' => $transaction->transaction_id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'payment_method' => $transaction->payment_method,
                    ]);
                    $this->utmifyService->sendTransaction($transaction, 'paid');
                } catch (\Exception $e) {
                    Log::error('TransactionObserver: Erro ao enviar transação paga para UTMify', [
                        'transaction_id' => $transaction->transaction_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            // Verificar se mudou para reembolsado
            // APENAS PIX será enviado (filtrado no UtmifyService)
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && 
                !in_array(strtolower($oldStatus), ['refunded', 'partially_refunded'])) {
                try {
                    Log::info('TransactionObserver: Status mudou para REFUNDED, enviando para UTMify', [
                        'transaction_id' => $transaction->transaction_id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'payment_method' => $transaction->payment_method,
                    ]);
                    $this->utmifyService->sendTransaction($transaction, 'refunded');
                } catch (\Exception $e) {
                    Log::error('TransactionObserver: Erro ao enviar transação reembolsada para UTMify', [
                        'transaction_id' => $transaction->transaction_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Transaction "deleted" event.
     */
    public function deleted(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "restored" event.
     */
    public function restored(Transaction $transaction): void
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     */
    public function forceDeleted(Transaction $transaction): void
    {
        //
    }
}
