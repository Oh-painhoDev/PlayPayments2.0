<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncWalletBalance extends Command
{
    protected $signature = 'wallet:sync-balance {--user-id= : Sincronizar apenas para um usuário específico} {--dry-run : Apenas mostrar o que seria feito, sem salvar}';
    protected $description = 'Sincroniza o saldo da wallet baseado nas transações pagas';

    public function handle()
    {
        $this->info('🔄 Sincronizando saldo das wallets...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $userId = $this->option('user-id');

        try {
            DB::beginTransaction();

            // Buscar usuários
            $usersQuery = User::query();
            if ($userId) {
                $usersQuery->where('id', $userId);
            }
            $users = $usersQuery->get();

            $this->info("📊 Processando " . $users->count() . " usuário(s)...");
            $this->newLine();

            $totalProcessed = 0;
            $totalCreated = 0;
            $totalUpdated = 0;

            foreach ($users as $user) {
                $this->line("👤 Processando usuário #{$user->id} - {$user->email}");

                // Garantir que o usuário tem uma wallet
                $wallet = $user->wallet;
                if (!$wallet) {
                    $this->warn("   ⚠️  Wallet não encontrada, criando...");
                    $wallet = Wallet::create([
                        'user_id' => $user->id,
                        'balance' => 0,
                        'pending_balance' => 0,
                        'reserved_balance' => 0,
                        'blocked_balance' => 0,
                        'total_received' => 0,
                        'total_withdrawn' => 0,
                        'currency' => 'BRL',
                        'is_active' => true,
                    ]);
                    $totalCreated++;
                    $this->info("   ✅ Wallet criada (ID: {$wallet->id})");
                }

                // Calcular saldo baseado nas transações
                $balanceBefore = $wallet->balance;

                // Buscar todas as transações pagas que não foram retidas
                $paidTransactions = Transaction::where('user_id', $user->id)
                    ->whereIn('status', ['paid', 'paid_out', 'paidout', 'completed', 'success', 'successful', 'approved', 'confirmed', 'settled', 'captured'])
                    ->where('is_retained', false)
                    ->get();

                // Buscar transações de reembolso e chargeback
                $refundedTransactions = Transaction::where('user_id', $user->id)
                    ->whereIn('status', ['refunded', 'partially_refunded', 'chargeback'])
                    ->get();

                // Calcular créditos (transações pagas)
                $totalCredits = $paidTransactions->sum('net_amount');

                // Calcular débitos (reembolsos e chargebacks)
                $totalDebits = $refundedTransactions->sum('net_amount');

                // Calcular saldo esperado
                $expectedBalance = $totalCredits - $totalDebits;

                // Buscar débitos já registrados na wallet (saques, etc)
                $walletDebits = WalletTransaction::where('wallet_id', $wallet->id)
                    ->where('type', 'debit')
                    ->whereIn('category', ['withdrawal', 'refund', 'chargeback'])
                    ->sum('amount');

                // Saldo final esperado
                $finalExpectedBalance = $expectedBalance - $walletDebits;

                // Verificar se precisa atualizar
                if (abs($wallet->balance - $finalExpectedBalance) > 0.01) {
                    $this->warn("   ⚠️  Saldo desatualizado!");
                    $this->line("      Saldo atual: R$ " . number_format($wallet->balance, 2, ',', '.'));
                    $this->line("      Saldo esperado: R$ " . number_format($finalExpectedBalance, 2, ',', '.'));

                    if (!$dryRun) {
                        // Atualizar saldo
                        $wallet->balance = $finalExpectedBalance;
                        $wallet->total_received = $totalCredits;
                        
                        // Recalcular total_withdrawn baseado nas wallet_transactions
                        $wallet->total_withdrawn = WalletTransaction::where('wallet_id', $wallet->id)
                            ->where('type', 'debit')
                            ->where('category', 'withdrawal')
                            ->sum('amount');

                        $wallet->save();

                        // Verificar se todas as transações pagas têm wallet_transaction correspondente
                        foreach ($paidTransactions as $transaction) {
                            $existingWalletTransaction = WalletTransaction::where('wallet_id', $wallet->id)
                                ->where('reference_id', $transaction->transaction_id)
                                ->where('type', 'credit')
                                ->where('category', 'payment_received')
                                ->first();

                            if (!$existingWalletTransaction) {
                                // Criar wallet_transaction para esta transação
                                WalletTransaction::create([
                                    'wallet_id' => $wallet->id,
                                    'transaction_id' => 'TXN_' . strtoupper(uniqid()),
                                    'type' => 'credit',
                                    'category' => 'payment_received',
                                    'amount' => $transaction->net_amount,
                                    'balance_before' => 0, // Será recalculado
                                    'balance_after' => 0, // Será recalculado
                                    'description' => "Pagamento recebido - {$transaction->transaction_id}",
                                    'metadata' => [
                                        'transaction_id' => $transaction->transaction_id,
                                        'external_id' => $transaction->external_id,
                                        'payment_method' => $transaction->payment_method,
                                    ],
                                    'reference_id' => $transaction->transaction_id,
                                    'status' => 'completed',
                                    'processed_at' => $transaction->paid_at ?? $transaction->created_at,
                                ]);
                                $this->line("      ✅ Criada wallet_transaction para transação {$transaction->transaction_id}");
                            }
                        }

                        $totalUpdated++;
                        $this->info("   ✅ Saldo atualizado!");
                    } else {
                        $this->line("      [DRY RUN] Saldo seria atualizado");
                    }
                } else {
                    $this->info("   ✅ Saldo já está correto");
                }

                $totalProcessed++;
                $this->newLine();
            }

            if ($dryRun) {
                $this->warn("⚠️  MODO DRY RUN - Nenhuma alteração foi salva");
                DB::rollBack();
            } else {
                DB::commit();
            }

            $this->newLine();
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("✅ Sincronização concluída!");
            $this->info("   Processados: {$totalProcessed}");
            $this->info("   Wallets criadas: {$totalCreated}");
            $this->info("   Wallets atualizadas: {$totalUpdated}");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Erro: " . $e->getMessage());
            $this->error("   Trace: " . $e->getTraceAsString());
            Log::error('Erro ao sincronizar saldo das wallets', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}


