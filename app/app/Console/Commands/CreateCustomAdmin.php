<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateCustomAdmin extends Command
{
    protected $signature = 'admin:create-custom {email} {password} {name?}';
    protected $description = 'Cria um usuário admin com email e senha customizados';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name') ?? 'Admin User';
        
        $this->info('🔐 Criando usuário admin customizado...');
        
        try {
            DB::beginTransaction();
            
            // Gerar chaves API
            $apiSecret = $this->generateSecureApiSecret();
            $apiPublicKey = $this->generateSecureApiPublicKey();
            
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'role' => 'admin',
                    'account_type' => 'pessoa_juridica',
                    'document_verified' => true,
                    'email_verified_at' => now(),
                    'terms_accepted' => true,
                    'api_secret' => $apiSecret,
                    'api_secret_created_at' => now(),
                    'api_public_key' => $apiPublicKey,
                    'api_public_key_created_at' => now(),
                ]
            );
            
            DB::commit();
            
            $this->info('✅ Admin criado com sucesso!');
            $this->info('');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('Email: ' . $email);
            $this->info('Senha: ' . $password);
            $this->info('Nome: ' . $name);
            $this->info('Role: admin');
            $this->info('');
            $this->info('🔑 API Keys:');
            $this->info('Public Key: ' . $apiPublicKey);
            $this->info('Secret: ' . substr($apiSecret, 0, 10) . '...');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erro: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    private function generateSecureApiSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    private function generateSecureApiPublicKey(): string
    {
        return 'pk_' . bin2hex(random_bytes(16));
    }
}
