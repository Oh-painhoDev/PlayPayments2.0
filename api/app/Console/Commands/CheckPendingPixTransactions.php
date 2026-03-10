<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckPendingPixTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pix:check-pending {--limit=100 : Número máximo de transações para verificar} {--daemon : Roda continuamente verificando a cada segundo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica automaticamente transações PIX pendentes e atualiza o status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $daemon = $this->option('daemon');
        
        if ($daemon) {
            $this->info("🔄 Modo daemon ativado - verificando a cada segundo...");
            $this->info("Pressione Ctrl+C para parar\n");
            
            while (true) {
                try {
                    $this->checkTransactions($limit, true); // true = modo silencioso
                    sleep(1); // Aguarda 1 segundo antes da próxima verificação
                } catch (\Exception $e) {
                    $this->error("Erro no loop: " . $e->getMessage());
                    sleep(1);
                }
            }
        } else {
            $this->checkTransactions($limit, false);
        }
        
        return 0;
    }
    
    /**
     * Verifica transações pendentes
     */
    private function checkTransactions($limit, $quiet = false)
    {
        if (!$quiet) {
            $this->info("🔍 Verificando transações PIX pendentes...");
        }
        
        // Buscar TODAS as transações PIX pendentes (sem filtro de data)
        // Excluir APENAS transações de "med fake" (sandbox/teste)
        $transactions = Transaction::where('payment_method', 'pix')
            ->where('status', 'pending')
            ->whereNotNull('external_id') // Precisa ter external_id para verificar
            ->whereNotNull('gateway_id') // Precisa ter gateway
            ->whereHas('gateway', function($query) {
                // Excluir APENAS gateways de teste/fake/sandbox
                $query->where(function($q) {
                    $q->where('name', 'not like', '%fake%')
                      ->where('name', 'not like', '%test%')
                      ->where('name', 'not like', '%sandbox%')
                      ->where('name', 'not like', '%med fake%')
                      ->where('name', 'not like', '%Med Fake%')
                      ->where('name', 'not like', '%MED FAKE%');
                })
                ->where('is_active', true); // Apenas gateways ativos
            })
            // REMOVIDO: Filtro de data - verifica TODAS as transações pendentes
            // REMOVIDO: Filtro de expiração - verifica mesmo as expiradas
            ->with('gateway')
            ->orderBy('created_at', 'desc') // Mais recentes primeiro
            ->limit($limit)
            ->get();
        
        if ($transactions->isEmpty()) {
            if (!$quiet) {
                $this->info("✅ Nenhuma transação PIX pendente encontrada para verificar.");
            }
            return;
        }
        
        if (!$quiet) {
            $this->info("📊 Encontradas {$transactions->count()} transação(ões) para verificar.");
        }
        
        $checked = 0;
        $updated = 0;
        $errors = 0;
        
        foreach ($transactions as $transaction) {
            try {
                $checked++;
                
                if (!$quiet) {
                    $this->line("  Verificando transação #{$transaction->transaction_id}...");
                }
                
                // Verificar se tem gateway configurado
                if (!$transaction->gateway) {
                    if (!$quiet) {
                        $this->warn("    ⚠️  Transação sem gateway, pulando...");
                    }
                    continue;
                }
                
                // Verificar status no gateway
                $paymentService = new PaymentGatewayService($transaction->gateway);
                $result = $paymentService->checkTransactionStatus($transaction);
                
                // Se for Pluggou e retornar note sobre webhook, não é erro
                $isPluggouWebhookNote = isset($result['note']) && strpos($result['note'], 'webhook') !== false;
                
                if (!$result['success'] && !$isPluggouWebhookNote) {
                    if (!$quiet) {
                        $this->error("    ❌ Erro ao verificar: " . ($result['error'] ?? 'Erro desconhecido'));
                    }
                    $errors++;
                    continue;
                }
                
                // Se for Pluggou sem endpoint GET, apenas logar e continuar (funciona via webhook)
                if ($isPluggouWebhookNote) {
                    if (!$quiet) {
                        $this->line("    ℹ️  Pluggou: atualização via webhook (sem endpoint GET)");
                    }
                    // Não contar como erro, apenas continuar
                    continue;
                }
                
                // Recarregar transação para ver se o status mudou
                $transaction->refresh();
                
                if (isset($result['status_changed']) && $result['status_changed']) {
                    $updated++;
                    if (!$quiet) {
                        $this->info("    ✅ Status atualizado: {$transaction->status}");
                    }
                    
                    Log::info('PIX Status atualizado automaticamente', [
                        'transaction_id' => $transaction->transaction_id,
                        'old_status' => $result['old_status'] ?? 'unknown',
                        'new_status' => $transaction->status,
                        'gateway' => $transaction->gateway->name,
                    ]);
                } else {
                    if (!$quiet) {
                        $this->line("    ℹ️  Status ainda pendente");
                    }
                }
                
                // Delay mínimo para não sobrecarregar a API do gateway
                usleep(50000); // 0.05 segundos (50ms) - reduzido para verificar mais rápido
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("    ❌ Erro: {$e->getMessage()}");
                
                Log::error('Erro ao verificar transação PIX pendente', [
                    'transaction_id' => $transaction->transaction_id ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        if (!$quiet) {
            $this->newLine();
            $this->info("📈 Resumo:");
            $this->line("   Verificadas: {$checked}");
            $this->line("   Atualizadas: {$updated}");
            $this->line("   Erros: {$errors}");
        } elseif ($updated > 0) {
            // Em modo quiet, só mostra se houver atualizações
            $this->line("[" . date('H:i:s') . "] ✅ {$updated} transação(ões) atualizada(s)");
        }
    }
}

