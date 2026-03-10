<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;

class FixPixExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pix:fix-expiration {--transaction_id= : ID específico da transação} {--all : Corrigir todas as transações PIX pendentes} {--force : Não pedir confirmação}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige a expiração de transações PIX pendentes para 15 minutos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $transactionId = $this->option('transaction_id');
        $fixAll = $this->option('all');

        if ($transactionId) {
            // Corrigir transação específica
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->orWhere('external_id', $transactionId)
                ->orWhere('id', $transactionId)
                ->first();

            if (!$transaction) {
                $this->error("Transação não encontrada: {$transactionId}");
                return 1;
            }

            if ($transaction->payment_method !== 'pix') {
                $this->error("Transação não é PIX: {$transactionId}");
                return 1;
            }

            if ($transaction->status !== 'pending') {
                $this->warn("Transação não está pendente (status: {$transaction->status}): {$transactionId}");
            }

            $oldExpiresAt = $transaction->expires_at;
            $newExpiresAt = now()->addMinutes(15);

            $transaction->expires_at = $newExpiresAt;
            $transaction->save();

            $this->info("✅ Transação corrigida: {$transactionId}");
            $this->line("   Antes: {$oldExpiresAt}");
            $this->line("   Agora: {$newExpiresAt} (15 minutos)");

            return 0;
        }

        if ($fixAll) {
            // Corrigir todas as transações PIX pendentes com expires_at muito longe
            $this->info("Buscando transações PIX pendentes com expiração incorreta...");

            $transactions = Transaction::where('payment_method', 'pix')
                ->where('status', 'pending')
                ->where('expires_at', '>', now()->addMinutes(30)) // Mais de 30 minutos
                ->get();

            if ($transactions->isEmpty()) {
                $this->info("Nenhuma transação encontrada para corrigir.");
                return 0;
            }

            $this->info("Encontradas {$transactions->count()} transação(ões) para corrigir.");

            if (!$this->option('force') && !$this->confirm('Deseja continuar?')) {
                $this->info("Operação cancelada.");
                return 0;
            }

            $fixed = 0;
            foreach ($transactions as $transaction) {
                $oldExpiresAt = $transaction->expires_at;
                $newExpiresAt = now()->addMinutes(15);

                $transaction->expires_at = $newExpiresAt;
                $transaction->save();

                $this->line("✅ {$transaction->transaction_id}: {$oldExpiresAt} → {$newExpiresAt}");
                $fixed++;
            }

            $this->info("✅ {$fixed} transação(ões) corrigida(s)!");
            return 0;
        }

        $this->error("Use --transaction_id=ID ou --all para corrigir transações.");
        return 1;
    }
}
