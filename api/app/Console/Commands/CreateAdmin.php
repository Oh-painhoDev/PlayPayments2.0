<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Cria o usuário admin brpixoficial@gmail.com';

    public function handle()
    {
        $this->info('🔐 Criando usuário admin...');
        
        try {
            DB::beginTransaction();
            
            $user = User::updateOrCreate(
                ['email' => 'brpixoficial@gmail.com'],
                [
                    'name' => 'Admin Brpix',
                    'password' => Hash::make('@Davib0110'),
                    'document_verified' => true,
                ]
            );
            
            DB::commit();
            
            $this->info('✅ Admin criado com sucesso!');
            $this->info('');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            $this->info('Email: brpixoficial@gmail.com');
            $this->info('Senha: @Davib0110');
            $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Erro: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
