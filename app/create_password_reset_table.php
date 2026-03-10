<?php
/**
 * Script para criar a tabela password_reset_tokens
 * Execute este arquivo uma vez: php create_password_reset_table.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Verificar se a tabela já existe
    if (Schema::hasTable('password_reset_tokens')) {
        echo "✓ A tabela 'password_reset_tokens' já existe.\n";
        exit(0);
    }

    // Criar a tabela
    DB::statement("
        CREATE TABLE `password_reset_tokens` (
            `email` varchar(255) NOT NULL,
            `token` varchar(255) NOT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "✓ Tabela 'password_reset_tokens' criada com sucesso!\n";
    echo "✓ Agora você pode usar a recuperação de senha.\n";
    
} catch (\Exception $e) {
    echo "✗ Erro ao criar tabela: " . $e->getMessage() . "\n";
    echo "\nTente executar o SQL manualmente:\n";
    echo file_get_contents(__DIR__ . '/database/create_password_reset_tokens_now.sql');
    exit(1);
}






