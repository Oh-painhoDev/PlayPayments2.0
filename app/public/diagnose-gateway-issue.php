<?php

/**
 * Script para diagnosticar problemas de gateway
 * 
 * Este script ajuda a identificar por que um gateway não está funcionando
 * 
 * Acesso: http://seu-dominio.com/diagnose-gateway-issue.php?user_id=2
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\PaymentGateway;
use App\Models\UserGatewayCredential;

header('Content-Type: text/html; charset=utf-8');

$userId = $_GET['user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Gateway</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        pre {
            background: #f4f4f4;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Diagnóstico de Gateway</h1>
        
        <form method="GET" style="margin-bottom: 20px;">
            <label>User ID:</label>
            <input type="number" name="user_id" value="<?php echo htmlspecialchars($userId ?? ''); ?>" required>
            <button type="submit">Diagnosticar</button>
        </form>
        
        <?php
        if (!$userId) {
            echo "<div class='section warning'>";
            echo "<p>Por favor, forneça um User ID para diagnosticar.</p>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            echo "<div class='section error'>";
            echo "<h2>❌ Erro</h2>";
            echo "<p>Usuário não encontrado com ID: {$userId}</p>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        echo "<div class='section info'>";
        echo "<h2>1. Informações do Usuário</h2>";
        echo "<p><strong>Nome:</strong> {$user->name}</p>";
        echo "<p><strong>Email:</strong> {$user->email}</p>";
        echo "<p><strong>ID:</strong> {$user->id}</p>";
        echo "<p><strong>Role:</strong> {$user->role}</p>";
        echo "<p><strong>Gateway Atribuído:</strong> " . ($user->assigned_gateway_id ? "ID {$user->assigned_gateway_id}" : "Nenhum") . "</p>";
        echo "</div>";
        
        if (!$user->assigned_gateway_id) {
            echo "<div class='section error'>";
            echo "<h2>❌ Erro Crítico</h2>";
            echo "<p>O usuário não tem um gateway atribuído!</p>";
            echo "<p>Por favor, atribua um gateway ao usuário no painel administrativo.</p>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        $gateway = $user->assignedGateway;
        
        if (!$gateway) {
            echo "<div class='section error'>";
            echo "<h2>❌ Erro Crítico</h2>";
            echo "<p>O gateway atribuído (ID: {$user->assigned_gateway_id}) não foi encontrado!</p>";
            echo "<p>Por favor, verifique se o gateway existe no banco de dados.</p>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        echo "<div class='section info'>";
        echo "<h2>2. Informações do Gateway</h2>";
        echo "<p><strong>Nome:</strong> {$gateway->name}</p>";
        echo "<p><strong>Slug:</strong> {$gateway->slug}</p>";
        echo "<p><strong>ID:</strong> {$gateway->id}</p>";
        echo "<p><strong>Ativo:</strong> " . ($gateway->is_active ? 'Sim' : 'Não') . "</p>";
        echo "<p><strong>API URL:</strong> {$gateway->api_url}</p>";
        echo "<p><strong>Tipo:</strong> " . ($gateway->getConfig('gateway_type') ?? 'N/A') . "</p>";
        echo "</div>";
        
        if (!$gateway->is_active) {
            echo "<div class='section warning'>";
            echo "<h2>⚠️ Aviso</h2>";
            echo "<p>O gateway está inativo. Ative o gateway no painel administrativo.</p>";
            echo "</div>";
        }
        
        echo "<div class='section info'>";
        echo "<h2>3. Verificando Credenciais</h2>";
        
        $adminUser = User::where('role', 'admin')->first();
        
        if (!$adminUser) {
            echo "<div class='section error'>";
            echo "<p>❌ Nenhum usuário admin encontrado!</p>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        echo "<p><strong>Admin User:</strong> {$adminUser->name} (ID: {$adminUser->id})</p>";
        
        // Buscar credenciais
        $credentials = UserGatewayCredential::where('user_id', $adminUser->id)
            ->where('gateway_id', $gateway->id)
            ->first();
        
        $credentialsActive = UserGatewayCredential::where('user_id', $adminUser->id)
            ->where('gateway_id', $gateway->id)
            ->where('is_active', true)
            ->first();
        
        $credentialsInactive = UserGatewayCredential::where('user_id', $adminUser->id)
            ->where('gateway_id', $gateway->id)
            ->where('is_active', false)
            ->first();
        
        echo "<table>";
        echo "<tr><th>Item</th><th>Status</th><th>Detalhes</th></tr>";
        echo "<tr><td>Credenciais Existem</td><td>" . ($credentials ? "✅ Sim" : "❌ Não") . "</td><td>" . ($credentials ? "ID: {$credentials->id}" : "Nenhuma credencial encontrada") . "</td></tr>";
        echo "<tr><td>Credenciais Ativas</td><td>" . ($credentialsActive ? "✅ Sim" : "❌ Não") . "</td><td>" . ($credentialsActive ? "ID: {$credentialsActive->id}" : "Nenhuma credencial ativa") . "</td></tr>";
        echo "<tr><td>Credenciais Inativas</td><td>" . ($credentialsInactive ? "⚠️ Sim" : "✅ Não") . "</td><td>" . ($credentialsInactive ? "ID: {$credentialsInactive->id} - Ative no painel administrativo" : "Nenhuma credencial inativa") . "</td></tr>";
        echo "</table>";
        
        if ($credentials) {
            echo "<h3>Detalhes das Credenciais:</h3>";
            echo "<table>";
            echo "<tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>ID</td><td>{$credentials->id}</td></tr>";
            echo "<tr><td>Ativa</td><td>" . ($credentials->is_active ? 'Sim' : 'Não') . "</td></tr>";
            echo "<tr><td>Sandbox</td><td>" . ($credentials->is_sandbox ? 'Sim' : 'Não') . "</td></tr>";
            echo "<tr><td>Public Key Existe</td><td>" . (!empty($credentials->public_key) ? 'Sim (' . strlen($credentials->public_key) . ' caracteres)' : 'Não') . "</td></tr>";
            
            $secretKey = null;
            try {
                $secretKey = $credentials->secret_key;
                echo "<tr><td>Secret Key Existe</td><td>" . (!empty($secretKey) ? 'Sim (' . strlen($secretKey) . ' caracteres)' : 'Não') . "</td></tr>";
            } catch (\Exception $e) {
                echo "<tr><td>Secret Key</td><td>❌ Erro ao descriptografar: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "<div class='section error'>";
            echo "<h3>❌ Problema Encontrado</h3>";
            echo "<p>Nenhuma credencial encontrada para este gateway!</p>";
            echo "<p><strong>Solução:</strong></p>";
            echo "<ol>";
            echo "<li>Acesse o painel administrativo: <code>/admin/gateways</code></li>";
            echo "<li>Encontre o gateway: <strong>{$gateway->name}</strong> (ID: {$gateway->id})</li>";
            echo "<li>Clique em 'Configurar'</li>";
            echo "<li>Preencha as credenciais (Public Key e Secret Key)</li>";
            echo "<li>Marque como 'Ativa'</li>";
            echo "<li>Salve as alterações</li>";
            echo "</ol>";
            echo "</div>";
        }
        echo "</div>";
        
        // Listar todos os gateways disponíveis
        echo "<div class='section info'>";
        echo "<h2>4. Gateways Disponíveis</h2>";
        $allGateways = PaymentGateway::all();
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Slug</th><th>Ativo</th><th>Tipo</th><th>API URL</th></tr>";
        foreach ($allGateways as $g) {
            $isAssigned = $g->id == $user->assigned_gateway_id ? '✅' : '';
            echo "<tr>";
            echo "<td>{$g->id} {$isAssigned}</td>";
            echo "<td>{$g->name}</td>";
            echo "<td>{$g->slug}</td>";
            echo "<td>" . ($g->is_active ? 'Sim' : 'Não') . "</td>";
            echo "<td>" . ($g->getConfig('gateway_type') ?? 'N/A') . "</td>";
            echo "<td>{$g->api_url}</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "</div>";
        
        // Verificar se há credenciais para outros gateways
        echo "<div class='section info'>";
        echo "<h2>5. Credenciais Configuradas (Admin User)</h2>";
        $allCredentials = UserGatewayCredential::where('user_id', $adminUser->id)->get();
        if ($allCredentials->isEmpty()) {
            echo "<p>Nenhuma credencial configurada para o admin user.</p>";
        } else {
            echo "<table>";
            echo "<tr><th>ID</th><th>Gateway ID</th><th>Gateway Nome</th><th>Ativa</th><th>Sandbox</th><th>Public Key</th><th>Secret Key</th></tr>";
            foreach ($allCredentials as $cred) {
                $g = PaymentGateway::find($cred->gateway_id);
                $gatewayName = $g ? $g->name : 'N/A';
                $hasPublicKey = !empty($cred->public_key) ? 'Sim' : 'Não';
                $hasSecretKey = 'N/A';
                try {
                    $sk = $cred->secret_key;
                    $hasSecretKey = !empty($sk) ? 'Sim' : 'Não';
                } catch (\Exception $e) {
                    $hasSecretKey = 'Erro';
                }
                echo "<tr>";
                echo "<td>{$cred->id}</td>";
                echo "<td>{$cred->gateway_id}</td>";
                echo "<td>{$gatewayName}</td>";
                echo "<td>" . ($cred->is_active ? 'Sim' : 'Não') . "</td>";
                echo "<td>" . ($cred->is_sandbox ? 'Sim' : 'Não') . "</td>";
                echo "<td>{$hasPublicKey}</td>";
                echo "<td>{$hasSecretKey}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</div>";
        ?>
    </div>
</body>
</html>




