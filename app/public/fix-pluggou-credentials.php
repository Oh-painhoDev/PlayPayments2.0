<?php
/**
 * Script para diagnosticar e corrigir credenciais corrompidas da Pluggou
 * 
 * Este script verifica se as credenciais estão corrompidas e fornece instruções
 * para re-salvá-las.
 * 
 * Acesse: https://seudominio.com/fix-pluggou-credentials.php?gateway_id=2
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use App\Models\UserGatewayCredential;
use App\Models\PaymentGateway;
use App\Models\User;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico e Correção de Credenciais Pluggou</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #1a1a1a;
            color: #fff;
        }
        .container {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .success { color: #4CAF50; }
        .error { color: #f44336; }
        .warning { color: #ff9800; }
        .info { color: #2196F3; }
        pre {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #444;
        }
        th {
            background: #333;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-danger {
            background: #f44336;
        }
        .btn-danger:hover {
            background: #da190b;
        }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico e Correção de Credenciais Pluggou</h1>
    
    <?php
    $gatewayId = $_GET['gateway_id'] ?? null;
    
    if (!$gatewayId) {
        echo '<div class="container">';
        echo '<h2>Selecione um Gateway</h2>';
        echo '<p>Adicione <code>?gateway_id=X</code> na URL, onde X é o ID do gateway Pluggou.</p>';
        
        $gateways = PaymentGateway::where('gateway_type', 'pluggou')->get();
        if ($gateways->isEmpty()) {
            echo '<p class="error">Nenhum gateway Pluggou encontrado.</p>';
        } else {
            echo '<table>';
            echo '<tr><th>ID</th><th>Nome</th><th>Slug</th><th>Ações</th></tr>';
            foreach ($gateways as $gateway) {
                echo '<tr>';
                echo '<td>' . $gateway->id . '</td>';
                echo '<td>' . htmlspecialchars($gateway->name) . '</td>';
                echo '<td>' . htmlspecialchars($gateway->slug) . '</td>';
                echo '<td><a href="?gateway_id=' . $gateway->id . '" class="btn">Diagnosticar</a></td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</div>';
        exit;
    }
    
    $gateway = PaymentGateway::find($gatewayId);
    if (!$gateway) {
        echo '<div class="container error">';
        echo '<p>Gateway não encontrado.</p>';
        echo '</div>';
        exit;
    }
    
    echo '<div class="container">';
    echo '<h2>Gateway: ' . htmlspecialchars($gateway->name) . ' (ID: ' . $gateway->id . ')</h2>';
    echo '<p><strong>Slug:</strong> ' . htmlspecialchars($gateway->slug) . '</p>';
    echo '<p><strong>Tipo:</strong> ' . htmlspecialchars($gateway->gateway_type) . '</p>';
    echo '</div>';
    
    // Buscar credenciais
    $adminUser = User::where('role', 'admin')->first();
    if (!$adminUser) {
        echo '<div class="container error">';
        echo '<p>Usuário admin não encontrado.</p>';
        echo '</div>';
        exit;
    }
    
    echo '<div class="container">';
    echo '<h2>Credenciais do Admin (ID: ' . $adminUser->id . ')</h2>';
    
    $credentials = UserGatewayCredential::where('user_id', $adminUser->id)
        ->where('gateway_id', $gatewayId)
        ->first();
    
    if (!$credentials) {
        echo '<p class="warning">Nenhuma credencial encontrada para este gateway e admin.</p>';
        echo '<p>Por favor, configure as credenciais no painel administrativo (Admin > Gateways > Configurar).</p>';
        echo '</div>';
        exit;
    }
    
    echo '<table>';
    echo '<tr><th>Campo</th><th>Valor</th><th>Status</th></tr>';
    
    // Verificar Public Key
    $publicKey = $credentials->public_key;
    $hasPublicKey = !empty($publicKey);
    echo '<tr>';
    echo '<td>Public Key</td>';
    echo '<td>' . ($hasPublicKey ? htmlspecialchars(substr($publicKey, 0, 20) . '...') : '<span class="error">VAZIO</span>') . '</td>';
    echo '<td>' . ($hasPublicKey ? '<span class="success">✓ OK</span>' : '<span class="error">✗ FALTANDO</span>') . '</td>';
    echo '</tr>';
    
    // Verificar Secret Key
    $rawSecretKey = $credentials->getRawSecretKey();
    $hasRawSecretKey = !empty($rawSecretKey);
    
    echo '<tr>';
    echo '<td>Secret Key (Raw/Encrypted)</td>';
    echo '<td>' . ($hasRawSecretKey ? htmlspecialchars(substr($rawSecretKey, 0, 30) . '...') : '<span class="error">VAZIO</span>') . '</td>';
    echo '<td>' . ($hasRawSecretKey ? '<span class="success">✓ EXISTE</span>' : '<span class="error">✗ FALTANDO</span>') . '</td>';
    echo '</tr>';
    
    // Tentar descriptografar Secret Key
    $secretKey = null;
    $decryptionError = null;
    try {
        if ($hasRawSecretKey) {
            $secretKey = Crypt::decryptString($rawSecretKey);
            if (empty(trim($secretKey))) {
                $decryptionError = 'Secret Key descriptografada está vazia';
            }
        } else {
            $decryptionError = 'Secret Key não existe (raw)';
        }
    } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
        $decryptionError = 'Erro de descriptografia: ' . $e->getMessage();
        $decryptionError .= ' (APP_KEY pode ter mudado ou credencial está corrompida)';
    } catch (\Exception $e) {
        $decryptionError = 'Erro ao descriptografar: ' . $e->getMessage();
    }
    
    echo '<tr>';
    echo '<td>Secret Key (Decrypted)</td>';
    if ($decryptionError) {
        echo '<td><span class="error">' . htmlspecialchars($decryptionError) . '</span></td>';
        echo '<td><span class="error">✗ ERRO</span></td>';
    } else {
        echo '<td>' . ($secretKey ? htmlspecialchars(substr($secretKey, 0, 20) . '...') : '<span class="error">VAZIO</span>') . '</td>';
        echo '<td>' . ($secretKey ? '<span class="success">✓ OK</span>' : '<span class="error">✗ VAZIO</span>') . '</td>';
    }
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>Is Active</td>';
    echo '<td>' . ($credentials->is_active ? 'SIM' : 'NÃO') . '</td>';
    echo '<td>' . ($credentials->is_active ? '<span class="success">✓ ATIVO</span>' : '<span class="warning">⚠ INATIVO</span>') . '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td>Is Sandbox</td>';
    echo '<td>' . ($credentials->is_sandbox ? 'SIM (Sandbox)' : 'NÃO (Produção)') . '</td>';
    echo '<td>' . ($credentials->is_sandbox ? '<span class="info">🧪 SANDBOX</span>' : '<span class="info">🚀 PRODUÇÃO</span>') . '</td>';
    echo '</tr>';
    
    echo '</table>';
    
    // Diagnóstico
    echo '<h3>📊 Diagnóstico</h3>';
    
    $issues = [];
    $fixes = [];
    
    if (!$hasPublicKey) {
        $issues[] = 'Public Key está vazia';
        $fixes[] = 'Configure a Public Key no painel administrativo';
    }
    
    if (!$hasRawSecretKey) {
        $issues[] = 'Secret Key (raw) está vazia';
        $fixes[] = 'Configure a Secret Key no painel administrativo';
    }
    
    if ($decryptionError) {
        $issues[] = 'Erro ao descriptografar Secret Key: ' . $decryptionError;
        $fixes[] = '<strong>SOLUÇÃO:</strong> Re-salve as credenciais no painel administrativo para re-criptografar com a APP_KEY atual';
        $fixes[] = '1. Vá para Admin > Gateways';
        $fixes[] = '2. Clique em "Editar" nas credenciais do gateway';
        $fixes[] = '3. Cole novamente a Public Key e Secret Key';
        $fixes[] = '4. Salve as credenciais';
    } else if (!$secretKey) {
        $issues[] = 'Secret Key descriptografada está vazia';
        $fixes[] = 'Re-salve as credenciais no painel administrativo';
    }
    
    if (!$credentials->is_active) {
        $issues[] = 'Credenciais estão inativas';
        $fixes[] = 'Ative as credenciais no painel administrativo';
    }
    
    if (empty($issues)) {
        echo '<p class="success">✓ Nenhum problema encontrado! As credenciais estão configuradas corretamente.</p>';
    } else {
        echo '<div class="error">';
        echo '<h4>⚠ Problemas Encontrados:</h4>';
        echo '<ul>';
        foreach ($issues as $issue) {
            echo '<li>' . htmlspecialchars($issue) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        
        echo '<div class="warning">';
        echo '<h4>🔧 Como Corrigir:</h4>';
        echo '<ol>';
        foreach ($fixes as $fix) {
            echo '<li>' . $fix . '</li>';
        }
        echo '</ol>';
        echo '</div>';
    }
    
    // Verificar credenciais globais
    echo '<h3>🌍 Credenciais Globais</h3>';
    $globalCredentials = UserGatewayCredential::whereNull('user_id')
        ->where('gateway_id', $gatewayId)
        ->first();
    
    if ($globalCredentials) {
        echo '<p class="info">Credenciais globais encontradas (user_id = NULL).</p>';
        echo '<table>';
        echo '<tr><th>Campo</th><th>Valor</th><th>Status</th></tr>';
        echo '<tr>';
        echo '<td>Is Active</td>';
        echo '<td>' . ($globalCredentials->is_active ? 'SIM' : 'NÃO') . '</td>';
        echo '<td>' . ($globalCredentials->is_active ? '<span class="success">✓ ATIVO</span>' : '<span class="warning">⚠ INATIVO</span>') . '</td>';
        echo '</tr>';
        echo '<tr>';
        echo '<td>Is Sandbox</td>';
        echo '<td>' . ($globalCredentials->is_sandbox ? 'SIM (Sandbox)' : 'NÃO (Produção)') . '</td>';
        echo '<td>' . ($globalCredentials->is_sandbox ? '<span class="info">🧪 SANDBOX</span>' : '<span class="info">🚀 PRODUÇÃO</span>') . '</td>';
        echo '</tr>';
        echo '</table>';
    } else {
        echo '<p class="info">Nenhuma credencial global encontrada.</p>';
    }
    
    echo '</div>';
    
    // Testar API (se credenciais estiverem OK)
    if (empty($issues) && $credentials->is_active && $secretKey && $publicKey) {
        echo '<div class="container">';
        echo '<h3>🧪 Testar API Pluggou</h3>';
        echo '<p>As credenciais parecem estar corretas. Você pode testar a API Pluggou usando o script:</p>';
        echo '<p><a href="test-pluggou-credentials.php?gateway_id=' . $gatewayId . '" class="btn">Testar API</a></p>';
        echo '</div>';
    }
    
    echo '<div class="container">';
    echo '<h3>📝 Informações Adicionais</h3>';
    echo '<pre>';
    echo 'Gateway ID: ' . $gatewayId . "\n";
    echo 'Admin User ID: ' . $adminUser->id . "\n";
    echo 'Credential ID: ' . $credentials->id . "\n";
    echo 'Created At: ' . $credentials->created_at . "\n";
    echo 'Updated At: ' . $credentials->updated_at . "\n";
    echo 'APP_KEY Hash: ' . substr(md5(config('app.key')), 0, 16) . '...' . "\n";
    echo '</pre>';
    echo '</div>';
    ?>
</body>
</html>




