<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dispute;
use App\Models\Wallet;
use App\Services\WebhookService;
use Carbon\Carbon;

class ProcessExpiredDisputes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'disputes:process-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa disputas vencidas (2 dias) sem resposta - reembolso automático';

    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        parent::__construct();
        $this->webhookService = $webhookService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Verificando disputas vencidas...');

        // Busca disputas pendentes que venceram (2 dias sem resposta)
        $expiredDisputes = Dispute::where('status', 'pending')
            ->whereNotNull('dispute_deadline')
            ->where('dispute_deadline', '<', Carbon::now()->toDateString())
            ->get();

        if ($expiredDisputes->isEmpty()) {
            $this->info('Nenhuma disputa vencida encontrada.');
            return 0;
        }

        $this->info("Encontradas {$expiredDisputes->count()} disputa(s) vencida(s).");

        foreach ($expiredDisputes as $dispute) {
            try {
                $this->processExpiredDispute($dispute);
                $this->info("✓ Disputa {$dispute->dispute_id} processada com sucesso.");
            } catch (\Exception $e) {
                $this->error("✗ Erro ao processar disputa {$dispute->dispute_id}: {$e->getMessage()}");
            }
        }

        $this->info('Processamento concluído!');
        return 0;
    }

    private function processExpiredDispute(Dispute $dispute)
    {
        $wallet = Wallet::where('user_id', $dispute->user_id)->first();

        if (!$wallet) {
            throw new \Exception('Carteira não encontrada para o usuário.');
        }

        // Desconta do saldo do usuário
        $wallet->balance -= $dispute->amount;
        
        // Remove do saldo bloqueado
        $wallet->blocked_balance -= $dispute->amount;
        
        $wallet->save();

        // Atualiza status da disputa
        $dispute->status = 'refunded';
        $dispute->refunded_at = now();
        $dispute->admin_notes = 'Reembolso automático por vencimento de prazo (48h sem resposta)';
        $dispute->save();

        // Envia webhook de reembolso
        $this->webhookService->send($dispute->user_id, 'transaction.refunded', [
            'dispute_id' => $dispute->dispute_id,
            'transaction_id' => $dispute->transaction->transaction_id ?? null,
            'amount' => $dispute->amount,
            'reason' => 'Reembolso automático por vencimento de prazo',
            'refunded_at' => $dispute->refunded_at->toIso8601String(),
        ]);
    }
}
