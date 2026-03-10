<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\UtmifyService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPendingTransactionsToUtmify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'utmify:send-pending {--user-id= : User ID específico} {--limit=100 : Limite de transações}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia transações PIX pendentes para UTMify';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        $limit = (int)$this->option('limit');

        $this->info('Buscando transações PIX pendentes...');

        $query = Transaction::where('payment_method', 'pix')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $transactions = $query->limit($limit)->get();

        if ($transactions->isEmpty()) {
            $this->warn('Nenhuma transação PIX pendente encontrada.');
            return 0;
        }

        $this->info("Encontradas {$transactions->count()} transações.");

        $utmifyService = new UtmifyService();
        $successCount = 0;
        $errorCount = 0;

        foreach ($transactions as $transaction) {
            $this->info("Processando: {$transaction->transaction_id} (User: {$transaction->user_id}, Amount: R$ {$transaction->amount})");
            
            try {
                $result = $utmifyService->sendTransaction($transaction, 'created');
                if ($result) {
                    $successCount++;
                    $this->info("  ✅ Enviado com sucesso");
                } else {
                    $errorCount++;
                    $this->error("  ❌ Falha no envio");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("  ❌ Erro: " . $e->getMessage());
            }
        }

        $this->info("\nResumo:");
        $this->info("  Total: {$transactions->count()}");
        $this->info("  Sucesso: {$successCount}");
        $this->info("  Erro: {$errorCount}");

        return 0;
    }
}

