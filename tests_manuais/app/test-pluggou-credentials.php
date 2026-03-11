<?php

/**
 * Script para testar credenciais do Pluggou
 * 
 * Este script testa se as credenciais do Pluggou estão configuradas corretamente
 * e se conseguem se autenticar na API.
 * 
 * Acesso: http://seu-dominio.com/test-pluggou-credentials.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\UserGatewayCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Credenciais Pluggou</title>
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
        .key-preview {
            font-family: monospace;
            background: #f4f4f4;
            padding: 5px;
            border-radius: 3px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Teste de Credenciais Pluggou</h1>
        
        <?php
        echo "<div class='section info'>";
        echo "<h2>1. Verificando Gateway Pluggou</h2>";
        
        $gateway = PaymentGateway::where('slug', 'pluggou')
            ->orWhere('name', 'like', '%pluggou%')
            ->first();
        
        if ($gateway) {
            echo "<p class='success'>✓ Gateway encontrado: <strong>{$gateway->name}</strong></p>";
            echo "<p>ID: {$gateway->id}</p>";
            echo "<p>Slug: {$gateway->slug}</p>";
            echo "<p>API URL: {$gateway->api_url}</p>";
            echo "<p>Ativo: " . ($gateway->is_active ? 'Sim' : 'Não') . "</p>";
        } else {
            echo "<p class='error'>✗ Gateway Pluggou não encontrado!</p>";
            echo "</div></div></body></html>";
            exit;
        }
        echo "</div>";
        
        echo "<div class='section info'>";
        echo "<h2>2. Verificando Usuário Admin</h2>";
        
        $adminUser = User::where('role', 'admin')->first();
        
        if ($adminUser) {
            echo "<p class='success'>✓ Usuário admin encontrado: <strong>{$adminUser->name}</strong></p>";
            echo "<p>Email: {$adminUser->email}</p>";
            echo "<p>ID: {$adminUser->id}</p>";
        } else {
            echo "<p class='error'>✗ Usuário admin não encontrado!</p>";
            echo "</div></div></body></html>";
            exit;
        }
        echo "</div>";
        
        echo "<div class='section info'>";
        echo "<h2>3. Verificando Credenciais</h2>";
        
        $credential = UserGatewayCredential::where('user_id', $adminUser->id)
            ->where('gateway_id', $gateway->id)
            ->first();
        
        if ($credential) {
            echo "<p class='success'>✓ Credenciais encontradas</p>";
            echo "<p>ID: {$credential->id}</p>";
            echo "<p>Ativa: " . ($credential->is_active ? 'Sim' : 'Não') . "</p>";
            echo "<p>Sandbox: " . ($credential->is_sandbox ? 'Sim' : 'Não') . "</p>";
            
            // Verificar Public Key
            $publicKey = $credential->public_key;
            if (!empty($publicKey)) {
                $publicKey = trim($publicKey);
                echo "<p class='success'>✓ Public Key existe</p>";
                echo "<p>Tamanho: " . strlen($publicKey) . " caracteres</p>";
                echo "<p>Preview: <span class='key-preview'>" . htmlspecialchars(substr($publicKey, 0, 20) . '...') . "</span></p>";
            } else {
                echo "<p class='error'>✗ Public Key está vazia!</p>";
            }
            
            // Verificar Secret Key
            $secretKey = null;
            try {
                $secretKey = $credential->secret_key;
                if (!empty($secretKey)) {
                    $secretKey = trim($secretKey);
                    echo "<p class='success'>✓ Secret Key existe e foi descriptografada</p>";
                    echo "<p>Tamanho: " . strlen($secretKey) . " caracteres</p>";
                    echo "<p>Preview: <span class='key-preview'>***" . htmlspecialchars(substr($secretKey, -10)) . "</span></p>";
                } else {
                    echo "<p class='error'>✗ Secret Key está vazia após descriptografia!</p>";
                }
            } catch (\Exception $e) {
                echo "<p class='error'>✗ Erro ao descriptografar Secret Key: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='error'>✗ Credenciais não encontradas para o usuário admin!</p>";
            echo "</div></div></body></html>";
            exit;
        }
        echo "</div>";
        
        if (empty($publicKey) || empty($secretKey)) {
            echo "<div class='section error'>";
            echo "<h2>❌ Erro</h2>";
            echo "<p>As credenciais não estão completas. Por favor, configure as credenciais no painel administrativo.</p>";
            echo "</div>";
            echo "</div></body></html>";
            exit;
        }
        
        echo "<div class='section info'>";
        echo "<h2>4. Testando Autenticação na API Pluggou</h2>";
        
        // Testar endpoint de balance (que requer autenticação)
        $apiUrl = rtrim($gateway->api_url, '/') . '/withdrawals/balance';
        
        echo "<p>URL: <code>{$apiUrl}</code></p>";
        echo "<p>Enviando requisição...</p>";
        
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Public-Key' => $publicKey,
                    'X-Secret-Key' => $secretKey,
                ])
                ->get($apiUrl);
            
            $statusCode = $response->status();
            $responseBody = $response->body();
            $responseJson = $response->json();
            
            echo "<p>Status Code: <strong>{$statusCode}</strong></p>";
            
            if ($response->successful()) {
                echo "<div class='section success'>";
                echo "<h3>✓ Autenticação bem-sucedida!</h3>";
                echo "<p>A API Pluggou aceitou as credenciais.</p>";
                echo "<pre>" . htmlspecialchars(json_encode($responseJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
                echo "</div>";
            } else {
                echo "<div class='section error'>";
                echo "<h3>✗ Autenticação falhou!</h3>";
                echo "<p>Status: {$statusCode}</p>";
                
                if ($statusCode === 401) {
                    echo "<p><strong>Erro 401: Não autorizado</strong></p>";
                    echo "<p>Possíveis causas:</p>";
                    echo "<ul>";
                    echo "<li>As credenciais estão incorretas</li>";
                    echo "<li>As credenciais estão inativas no painel da Pluggou</li>";
                    echo "<li>As credenciais não têm permissões adequadas</li>";
                    echo "<li>As credenciais são do ambiente errado (sandbox vs produção)</li>";
                    echo "</ul>";
                }
                
                echo "<p>Resposta da API:</p>";
                echo "<pre>" . htmlspecialchars($responseBody) . "</pre>";
                
                if (is_array($responseJson)) {
                    echo "<p>Resposta JSON:</p>";
                    echo "<pre>" . htmlspecialchars(json_encode($responseJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
                }
                echo "</div>";
            }
        } catch (\Exception $e) {
            echo "<div class='section error'>";
            echo "<h3>✗ Erro ao testar conexão</h3>";
            echo "<p>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        echo "</div>";
        
        echo "<div class='section warning'>";
        echo "<h2>⚠️ Informações Importantes</h2>";
        echo "<ul>";
        echo "<li>Este script testa apenas a autenticação, não cria transações reais</li>";
        echo "<li>Verifique se as credenciais estão corretas no painel da Pluggou</li>";
        echo "<li>Certifique-se de que as credenciais estão ativas</li>";
        echo "<li>Verifique se está usando o ambiente correto (sandbox vs produção)</li>";
        echo "</ul>";
        echo "</div>";
        ?>
    </div>
</body>
</html>




