<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Models\PaymentGateway;

class TestSharkBankingTransaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:sharkbanking {--user_id= : ID do usuário} {--amount=100.00 : Valor da transação} {--payment_method=pix : Método de pagamento (pix, credit_card, bank_slip)} {--sale_name=Produto Teste : Nome da venda/produto} {--description=Descrição do produto de teste : Descrição da venda} {--pix_expires_in_minutes=15 : Tempo de expiração em minutos (para PIX < 1 dia)} {--pix_expires_in_days= : Tempo de expiração em dias (para PIX >= 1 dia, 1-90)} {--customer_name=João Silva : Nome do cliente} {--customer_email=joao@example.com : Email do cliente} {--customer_document=12345678900 : CPF/CNPJ do cliente} {--customer_phone=11999999999 : Telefone do cliente} {--gateway_id= : ID do gateway (opcional, usa gateway padrão do usuário)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste completo da integração SharkBanking com todos os parâmetros configuráveis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🐊 ==========================================');
        $this->info('🐊 TESTE SHARKBANKING - TRANSACTION');
        $this->info('🐊 ==========================================');
        $this->newLine();

        // Get user
        $userId = $this->option('user_id');
        if (!$userId) {
            $userId = $this->ask('Digite o ID do usuário');
        }

        $user = User::find($userId);
        if (!$user) {
            $this->error('❌ Usuário não encontrado!');
            return 1;
        }

        $this->info("✅ Usuário encontrado: {$user->name} (ID: {$user->id})");
        $this->newLine();

        // Get gateway
        $gatewayId = $this->option('gateway_id');
        if ($gatewayId) {
            $gateway = PaymentGateway::find($gatewayId);
            if (!$gateway) {
                $this->error('❌ Gateway não encontrado!');
                return 1;
            }
        } else {
            $gateway = $user->assignedGateway;
            if (!$gateway) {
                $this->error('❌ Usuário não possui gateway configurado!');
                return 1;
            }
        }

        $this->info("✅ Gateway: {$gateway->name} (ID: {$gateway->id})");
        $this->info("   Tipo: {$gateway->getConfig('gateway_type', 'N/A')}");
        $this->newLine();

        // Collect transaction data
        $amount = (float)$this->option('amount');
        $paymentMethod = $this->option('payment_method');
        $saleName = $this->option('sale_name');
        $description = $this->option('description');
        
        // PIX expiration
        $pixExpiresInDays = $this->option('pix_expires_in_days');
        $pixExpiresInMinutes = $this->option('pix_expires_in_minutes');
        
        // Customer data
        $customerName = $this->option('customer_name');
        $customerEmail = $this->option('customer_email');
        $customerDocument = $this->option('customer_document');
        $customerPhone = $this->option('customer_phone');

        // Build transaction data
        $transactionData = [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'sale_name' => $saleName,
            'description' => $description,
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
                'document' => $customerDocument,
                'phone' => preg_replace('/[^0-9]/', '', $customerPhone),
            ],
            'metadata' => [
                'created_via' => 'test_command',
                'test' => true,
            ],
        ];

        // Add PIX expiration
        if ($paymentMethod === 'pix') {
            if ($pixExpiresInDays) {
                $days = (int)$pixExpiresInDays;
                $transactionData['pix_expires_in_minutes'] = $days * 1440; // Convert days to minutes
                $this->info("⏰ Expiração PIX: {$days} dias ({$transactionData['pix_expires_in_minutes']} minutos)");
            } else {
                $minutes = (int)$pixExpiresInMinutes;
                $transactionData['pix_expires_in_minutes'] = $minutes;
                
                if ($minutes < 1440) {
                    $this->info("⏰ Expiração PIX: {$minutes} minutos (usará expiresIn em segundos)");
                } else {
                    $days = floor($minutes / 1440);
                    $this->info("⏰ Expiração PIX: {$minutes} minutos ({$days} dias - usará expiresInDays)");
                }
            }
        }

        // Display transaction summary
        $this->newLine();
        $this->info('📋 RESUMO DA TRANSAÇÃO:');
        $this->info('==========================================');
        $this->table(
            ['Campo', 'Valor'],
            [
                ['💰 Valor', 'R$ ' . number_format($amount, 2, ',', '.')],
                ['💳 Método', strtoupper($paymentMethod)],
                ['📦 Nome do Produto', $saleName],
                ['📝 Descrição', $description ?: '(vazio)'],
                ['👤 Cliente', $customerName],
                ['📧 Email', $customerEmail],
                ['🆔 Documento', $customerDocument],
                ['📱 Telefone', $customerPhone],
                ['⏰ Expiração PIX', $paymentMethod === 'pix' 
                    ? ($pixExpiresInDays 
                        ? "{$pixExpiresInDays} dias" 
                        : "{$pixExpiresInMinutes} minutos")
                    : 'N/A'],
            ]
        );
        $this->newLine();

        // Confirm
        if (!$this->confirm('Deseja criar esta transação?', true)) {
            $this->info('❌ Operação cancelada.');
            return 0;
        }

        $this->newLine();
        $this->info('🔄 Criando transação...');
        $this->newLine();

        try {
            // Create payment service
            $paymentService = new PaymentGatewayService($gateway);
            
            // Create transaction
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success']) {
                $this->error('❌ Erro ao criar transação!');
                $this->error('Erro: ' . $result['error']);
                return 1;
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'] ?? [];

            // Display success
            $this->info('✅ Transação criada com sucesso!');
            $this->newLine();
            $this->info('📊 DETALHES DA TRANSAÇÃO:');
            $this->info('==========================================');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['🆔 ID Interno', $transaction->transaction_id],
                    ['🆔 ID Externo', $transaction->external_id ?? 'N/A'],
                    ['💰 Valor', 'R$ ' . number_format($transaction->amount, 2, ',', '.')],
                    ['💳 Método', strtoupper($transaction->payment_method)],
                    ['📊 Status', strtoupper($transaction->status)],
                    ['📅 Criado em', $transaction->created_at->format('d/m/Y H:i:s')],
                    ['⏰ Expira em', $transaction->expires_at 
                        ? $transaction->expires_at->format('d/m/Y H:i:s') 
                        : 'N/A'],
                ]
            );

            // Display PIX data if available
            if ($paymentMethod === 'pix' && isset($gatewayResponse['payment_data']['pix'])) {
                $pixData = $gatewayResponse['payment_data']['pix'];
                $this->newLine();
                $this->info('🔐 DADOS DO PIX:');
                $this->info('==========================================');
                
                if (isset($pixData['qrcode'])) {
                    $this->info('QR Code:');
                    $this->line($pixData['qrcode']);
                    $this->newLine();
                }
                
                if (isset($pixData['payload'])) {
                    $this->info('Payload (Copia e Cola):');
                    $this->line($pixData['payload']);
                    $this->newLine();
                }
                
                if (isset($pixData['expirationDate'])) {
                    $this->info('Data de Expiração: ' . $pixData['expirationDate']);
                    $this->newLine();
                }
            }

            // Display gateway response
            if (isset($result['gateway_response']['raw_response'])) {
                $this->newLine();
                $this->info('📡 RESPOSTA DO GATEWAY:');
                $this->info('==========================================');
                $this->line(json_encode($result['gateway_response']['raw_response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $this->newLine();
            }

            $this->info('✅ Teste concluído com sucesso!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erro ao criar transação!');
            $this->error('Mensagem: ' . $e->getMessage());
            $this->error('Arquivo: ' . $e->getFile());
            $this->error('Linha: ' . $e->getLine());
            $this->newLine();
            $this->error('Stack Trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}

