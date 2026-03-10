<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MassiveDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Iniciando geração massiva de dados para os dashboards...");

        // 1. Limpar dados anteriores (opcional, mas não faremos para não perder os já feitos)
        // Apenas vamos criar novos.
        
        // 2. Criar ou pegar usuários base (inclusive o admin para ganhar as taxas)
        $users = User::where('role', 'user')->limit(5)->get();
        if ($users->isEmpty()) {
            User::factory(5)->create(['role' => 'user']);
            $users = User::where('role', 'user')->limit(5)->get();
        }

        $douglas = User::where('email', 'douglass20345@gmail.com')->first();
        if ($douglas && !$users->contains('id', $douglas->id)) {
            $users->push($douglas);
        }

        // Tentar obter um gateway
        $gateway = PaymentGateway::first();
        $gatewayId = $gateway ? $gateway->id : null;

        // Cidades representativas para o mapa (lat, long serão calculadas baseadas na string no frontend)
        $locations = [
            ['country' => 'Brasil', 'city' => 'São Paulo'],
            ['country' => 'Brasil', 'city' => 'Rio de Janeiro'],
            ['country' => 'Brasil', 'city' => 'Belo Horizonte'],
            ['country' => 'Brasil', 'city' => 'Curitiba'],
            ['country' => 'Brasil', 'city' => 'Salvador'],
            ['country' => 'USA', 'city' => 'New York'],
            ['country' => 'Portugal', 'city' => 'Lisboa'],
        ];

        // 3. Gerar Transações nos últimos 30 dias
        $now = Carbon::now();
        
        $totalTransactions = 0;
        
        foreach ($users as $user) {
            $this->command->info("Gerando transações para o usuário: " . $user->email);
            
            if (!$user->wallet) {
                Wallet::create(['user_id' => $user->id, 'balance' => 0]);
                $user->refresh();
            }

            // Para cada dia nos últimos 30 dias
            for ($daysBack = 30; $daysBack >= 0; $daysBack--) {
                $currentDate = $now->copy()->subDays($daysBack);
                
                // 1 a 5 transações por dia
                $numTxns = rand(1, 5);
                
                for ($i = 0; $i < $numTxns; $i++) {
                    $statusRoll = rand(1, 100);
                    $status = 'paid';
                    
                    if ($statusRoll <= 5) $status = 'chargeback';
                    elseif ($statusRoll <= 10) $status = 'refunded';
                    elseif ($statusRoll <= 20) $status = 'pending';
                    elseif ($statusRoll <= 25) $status = 'failed';

                    $paymentMethod = rand(1, 100) > 20 ? 'pix' : 'credit_card';
                    
                    $amount = rand(50, 1000) + (rand(0, 99) / 100); // 50.00 a 1000.99
                    $fee = $amount * 0.05;
                    $net = $amount - $fee;

                    // Ajustar a hora para ser aleatória durante o dia
                    $txnDate = $currentDate->copy()->setHour(rand(8, 22))->setMinute(rand(0, 59));
                    
                    $location = $locations[array_rand($locations)];

                    $txn = Transaction::create([
                        'user_id' => $user->id,
                        'gateway_id' => $gatewayId,
                        'transaction_id' => 'TXN_MASS_' . Str::random(12),
                        'external_id' => 'EXT_' . Str::random(8),
                        'amount' => $amount,
                        'fee_amount' => $fee,
                        'net_amount' => $net,
                        'status' => $status,
                        'payment_method' => $paymentMethod,
                        'customer_data' => [
                            'name' => 'Cliente Random ' . Str::random(4),
                            'email' => strtolower(Str::random(5)) . '@example.com',
                            'country' => $location['country'],
                            'city' => $location['city'],
                        ],
                        'metadata' => [
                            'user_ip' => rand(1, 255) . '.' . rand(0, 255) . '.1.1'
                        ],
                        'paid_at' => ($status === 'paid' || $status === 'refunded' || $status === 'chargeback') ? $txnDate : null,
                        'created_at' => $txnDate,
                        'updated_at' => $txnDate,
                    ]);

                    if ($status === 'paid' && $user->wallet) {
                        try {
                            $user->wallet->addCredit(
                                amount: $net,
                                category: 'transaction',
                                description: "Venda gerada massiva",
                                referenceId: 'TX_CR_' . $txn->id
                            );
                        } catch (\Exception $e) {}
                    }
                    
                    $totalTransactions++;
                }
            }
        }
        $this->command->info("Transações concluídas. Total: {$totalTransactions}");

        // 4. Gerar Saques (Withdrawals) para popular métricas de Admin
        $this->command->info("Gerando dados de Saques...");
        foreach ($users as $user) {
            // Se o usuário tem saldo, vamos simular alguns saques
            if ($user->wallet && $user->wallet->balance > 100) {
                // Fazer 1 ou 2 saques nos últimos 15 dias
                $numSaques = rand(1, 2);
                for ($i = 0; $i < $numSaques; $i++) {
                    $amount = 50 + rand(10, 50);
                    $fee = 5; // Taxa de saque fixa (o admin ganha isso)
                    $netAmount = $amount - $fee;
                    
                    $withdrawDate = $now->copy()->subDays(rand(1, 15))->setHour(rand(10, 18));
                    
                    $user->wallet->balance -= $amount;
                    $user->wallet->total_withdrawn += $amount;
                    $user->wallet->save();
                    
                    Withdrawal::create([
                        'user_id' => $user->id,
                        'withdrawal_id' => 'WD_' . strtoupper(Str::random(10)),
                        'amount' => $amount,
                        'fee' => $fee,
                        'net_amount' => $netAmount,
                        'pix_type' => 'email',
                        'pix_key' => $user->email,
                        'status' => 'completed',
                        'completed_at' => $withdrawDate,
                        'created_at' => $withdrawDate,
                        'updated_at' => $withdrawDate,
                    ]);
                }
            }
        }

        $this->command->info("Geração massiva concluída com sucesso! Verifique os dashboards.");
    }
}
