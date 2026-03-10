<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\UserGoalAchievement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@playpayments.com'],
            [
                'name' => 'Admin PlayPayments',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'account_type' => 'pessoa_juridica',
                'terms_accepted' => true,
            ]
        );

        if (!$admin->wallet) {
            Wallet::create(['user_id' => $admin->id, 'balance' => 10000.00]);
        }

        // 2. Create Global Goals
        $globalGoal1 = Goal::firstOrCreate(
            ['name' => 'Faturamento Mensal Bronze'],
            [
                'type' => 'faturamento',
                'target_value' => 5000.00,
                'period' => 'monthly',
                'is_active' => true,
                'display_order' => 1,
                'description' => 'Atingir R$ 5.000,00 em faturamento mensal.',
                'reward_type' => 'cash',
                'reward_value' => 50.00,
                'auto_reward' => true,
                'reward_description' => 'Parabéns! Você ganhou R$ 50,00 de bônus.'
            ]
        );

        $globalGoal2 = Goal::firstOrCreate(
            ['name' => 'Milestone de 100 Vendas'],
            [
                'type' => 'vendas',
                'target_value' => 100,
                'period' => 'monthly',
                'is_active' => true,
                'display_order' => 2,
                'description' => 'Realizar 100 vendas no mês.',
                'reward_type' => 'bonus',
                'reward_value' => 0,
                'auto_reward' => false,
                'reward_description' => 'Upgrade de conta liberado!'
            ]
        );

        // 3. Create 5 Test Users with different scenarios
        $testUsers = [
            [
                'name' => 'Ana Silva',
                'email' => 'ana@test.com',
                'scenario' => 'high_performer', // 100% goals
            ],
            [
                'name' => 'Bruno Oliveira',
                'email' => 'bruno@test.com',
                'scenario' => 'medium_performer', // 50% goals
            ],
            [
                'name' => 'Carlos Souza',
                'email' => 'carlos@test.com',
                'scenario' => 'new_user', // 0% goals
            ],
            [
                'name' => 'Daniela Ramos',
                'email' => 'daniela@test.com',
                'scenario' => 'mixed_results', // One goal hit, one not
            ],
            [
                'name' => 'Eduardo Lima',
                'email' => 'eduardo@test.com',
                'scenario' => 'near_goal', // 90% goals
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'account_type' => 'pessoa_fisica',
                    'terms_accepted' => true,
                    'document' => rand(100000000, 999999999) . rand(10, 99),
                ]
            );

            if (!$user->wallet) {
                Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            }

            // Create Transactions based on scenario
            $this->createScenarioData($user, $userData['scenario'], $globalGoal1, $globalGoal2);
            
            // Trigger Goal Check
            Goal::active()->get()->each(function($goal) use ($user) {
                $goal->checkAndReward($user->id);
            });
        }

        $this->command->info('Dados de teste gerados com sucesso!');
    }

    private function createScenarioData($user, $scenario, $goal1, $goal2)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        switch ($scenario) {
            case 'high_performer':
                // High volume of transactions to exceed goals
                for ($i = 0; $i < 20; $i++) {
                    $this->createTestTransaction($user, rand(300, 1000), 'paid', $startOfMonth->copy()->addDays(rand(0, 5)));
                }
                break;

            case 'medium_performer':
                // Enough to be around 50%
                for ($i = 0; $i < 5; $i++) {
                    $this->createTestTransaction($user, 500, 'paid', $startOfMonth->copy()->addDays(rand(0, 5)));
                }
                break;

            case 'new_user':
                // Just a few pending/failed transactions
                $this->createTestTransaction($user, 100, 'pending', $now);
                $this->createTestTransaction($user, 100, 'failed', $now);
                break;

            case 'mixed_results':
                // Reach Goal 1 but not Goal 2
                for ($i = 0; $i < 10; $i++) {
                    $this->createTestTransaction($user, 600, 'paid', $startOfMonth->copy()->addDays(rand(0, 2)));
                }
                break;

            case 'near_goal':
                // Stay just below a goal
                $amountNeeded = $goal1->target_value * 0.9;
                $this->createTestTransaction($user, $amountNeeded, 'paid', $now->copy()->subDays(1));
                break;
        }
    }

    private function createTestTransaction($user, $amount, $status, $date)
    {
        $fee = $amount * 0.05;
        $net = $amount - $fee;

        Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => Transaction::generateTransactionId(),
            'amount' => $amount,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'status' => $status,
            'payment_method' => 'pix',
            'customer_data' => [
                'name' => 'Cliente de Teste',
                'email' => 'cliente' . rand(1, 1000) . '@gmail.com',
            ],
            'paid_at' => $status === 'paid' ? $date : null,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
        
        // If paid, we should also manually update the wallet if the system doesn't do it automatically in the transaction creation
        // Usually there is a service for this, but for seeder we can be direct if needed.
        // Based on Transaction.php boot, it only creates referral commissions.
        // Based on Wallet.php, it has addCredit.
        if ($status === 'paid' && $user->wallet) {
             $user->wallet->addCredit(
                amount: $net,
                category: 'transaction',
                description: "Venda aprovada - Teste",
                referenceId: 'TX_TEST_' . Str::random(10)
            );
        }
    }
}
