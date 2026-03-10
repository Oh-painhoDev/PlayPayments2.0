<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wallet;
use App\Models\Goal;
use App\Models\Transaction;
use App\Models\UserGoalAchievement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSpecificTestSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'douglass20345@gmail.com';
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->command->error("Usuário $email não encontrado! Vou tentar as outras variações...");
            $emails = [
                'douglas20345@gmail.com', 
                'douglas203455@gmail.com',
                'douglasssss20345@gmail.com'
            ];
            $user = User::whereIn('email', $emails)->first();
        }

        if (!$user) {
            $this->command->error("Nenhum usuário compatível encontrado.");
            return;
        }

        $this->command->info("Semeando dados reais para: " . $user->email);

        if (!$user->wallet) {
            Wallet::create(['user_id' => $user->id, 'balance' => 0]);
            $user->refresh();
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();

        // 1. Criar Metas de Elite para o Douglas
        $goal1 = Goal::updateOrCreate(
            ['name' => 'Meta Ouro: Faturamento Douglas'],
            [
                'type' => 'faturamento',
                'target_value' => 10000.00,
                'period' => 'monthly',
                'is_active' => true,
                'display_order' => 1,
                'reward_type' => 'cash',
                'reward_value' => 500.00,
                'auto_reward' => true,
                'reward_description' => 'Elite - Bônus Premium creditado!'
            ]
        );

        $goal2 = Goal::updateOrCreate(
            ['name' => 'Frequência de Vendas (Mensal)'],
            [
                'type' => 'transacoes',
                'target_value' => 30,
                'period' => 'monthly',
                'is_active' => true,
                'display_order' => 2,
                'reward_type' => 'bonus',
                'reward_value' => 0,
                'auto_reward' => false
            ]
        );

        // 2. Gerar transações para deixar Douglas com 92% da Meta Ouro
        // 10000 * 0.92 = 9200
        $targetFaturamento = 9200;
        $numTxns = 15;
        $avgAmount = $targetFaturamento / $numTxns;

        for ($i = 0; $i < $numTxns; $i++) {
            $amount = $avgAmount + rand(-100, 100);
            $this->createTestTransaction($user, max(10, $amount), 'paid', $startOfMonth->copy()->addDays(rand(1, 10)));
        }

        // 3. Adicionar transações pendentes e falhas para testar visualização
        $this->createTestTransaction($user, 1500, 'pending', $now);
        $this->createTestTransaction($user, 450, 'failed', $now->copy()->subHours(5));

        // 4. Forçar o processamento de conquistas
        Goal::active()->get()->each(function($goal) use ($user) {
            $goal->checkAndReward($user->id);
        });

        $this->command->info("Tudo pronto! O usuário $user->email agora tem R$ 9.200,00 em faturamento (92% da meta) e histórico de vendas.");
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
                'name' => 'Cliente Douglas VIP',
                'email' => 'comprador' . rand(1, 500) . '@test.com',
            ],
            'paid_at' => $status === 'paid' ? $date : null,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        if ($status === 'paid' && $user->wallet) {
            $user->wallet->addCredit(
                amount: $net,
                category: 'transaction',
                description: "Venda gerada para teste de dashboard",
                referenceId: 'TX_DGL_SPEC_' . Str::random(12)
            );
        }
    }
}
