<?php

/**
 * Script de diagnóstico para verificar credenciais do gateway Pluggou
 * Acesse: https://seu-dominio.com/check-gateway-credentials.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnóstico de Credenciais do Gateway</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        pre { background: #0f0f0f; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h2 { margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Diagnóstico de Credenciais do Gateway Pluggou</h1>
    
    <?php
    try {
        // 1. Verificar se existe usuário admin
        $adminUser = \App\Models\User::where('role', 'admin')->first();
        
        if (!$adminUser) {
            echo '<p class="error">❌ Usuário admin não encontrado!</p>';
            exit;
        }
        
        echo '<p class="success">✅ Usuário admin encontrado: ID ' . $adminUser->id . ' (' . $adminUser->email . ')</p>';
        
        // 2. Verificar se existe gateway Pluggou
        $gateway = \App\Models\PaymentGateway::where('slug', 'pluggou')
            ->orWhere('name', 'like', '%pluggou%')
            ->orWhere('name', 'like', '%Pluggou%')
            ->first();
        
        if (!$gateway) {
            echo '<p class="error">❌ Gateway Pluggou não encontrado!</p>';
            echo '<p class="info">Gateways disponíveis:</p>';
            echo '<pre>';
            foreach (\App\Models\PaymentGateway::all() as $g) {
                echo "ID: {$g->id} | Nome: {$g->name} | Slug: {$g->slug} | Ativo: " . ($g->is_active ? 'Sim' : 'Não') . PHP_EOL;
            }
            echo '</pre>';
            exit;
        }
        
        echo '<p class="success">✅ Gateway Pluggou encontrado: ID ' . $gateway->id . ' (' . $gateway->name . ')</p>';
        echo '<p class="info">📋 Configurações do Gateway:</p>';
        echo '<pre>';
        echo "ID: {$gateway->id}\n";
        echo "Nome: {$gateway->name}\n";
        echo "Slug: {$gateway->slug}\n";
        echo "API URL: {$gateway->api_url}\n";
        echo "Ativo: " . ($gateway->is_active ? 'Sim' : 'Não') . "\n";
        echo "Tipo: " . ($gateway->getConfig('gateway_type') ?? 'N/A') . "\n";
        echo '</pre>';
        
        // 3. Verificar credenciais
        $credentials = \App\Models\UserGatewayCredential::where('user_id', $adminUser->id)
            ->where('gateway_id', $gateway->id)
            ->first();
        
        if (!$credentials) {
            echo '<p class="error">❌ Credenciais do gateway Pluggou não encontradas para o usuário admin!</p>';
            echo '<p class="warning">⚠️ Você precisa configurar as credenciais no painel administrativo (Admin > Gateways > Configurar)</p>';
            exit;
        }
        
        echo '<p class="success">✅ Credenciais encontradas: ID ' . $credentials->id . '</p>';
        echo '<p class="info">📋 Status das Credenciais:</p>';
        echo '<pre>';
        echo "ID: {$credentials->id}\n";
        echo "User ID: {$credentials->user_id}\n";
        echo "Gateway ID: {$credentials->gateway_id}\n";
        echo "Ativo: " . ($credentials->is_active ? 'Sim' : 'Não') . "\n";
        echo "Sandbox: " . ($credentials->is_sandbox ? 'Sim' : 'Não') . "\n";
        echo "Public Key existe: " . (!empty($credentials->public_key) ? 'Sim (' . strlen($credentials->public_key) . ' caracteres)' : 'Não') . "\n";
        
        // Tentar descriptografar secret key
        $secretKeyDecrypted = null;
        $decryptError = null;
        try {
            $secretKeyDecrypted = $credentials->secret_key;
            echo "Secret Key existe: " . (!empty($secretKeyDecrypted) ? 'Sim (' . strlen($secretKeyDecrypted) . ' caracteres)' : 'Não') . "\n";
        } catch (\Exception $e) {
            $decryptError = $e->getMessage();
            echo "Secret Key existe: Erro ao descriptografar\n";
            echo "Erro de descriptografia: {$decryptError}\n";
        }
        
        // Verificar raw secret key
        $rawSecretKey = $credentials->getRawSecretKey();
        echo "Raw Secret Key (criptografado) existe: " . (!empty($rawSecretKey) ? 'Sim (' . strlen($rawSecretKey) . ' caracteres)' : 'Não') . "\n";
        echo '</pre>';
        
        // 4. Verificar se está configurado corretamente
        if (!$credentials->is_active) {
            echo '<p class="error">❌ Credenciais estão inativas! Ative as credenciais no painel administrativo.</p>';
        }
        
        if (empty($credentials->public_key)) {
            echo '<p class="error">❌ Public Key está vazia! Configure a Public Key no painel administrativo.</p>';
        }
        
        if (empty($secretKeyDecrypted)) {
            if ($decryptError) {
                echo '<p class="error">❌ Erro ao descriptografar Secret Key: ' . htmlspecialchars($decryptError) . '</p>';
                echo '<p class="warning">⚠️ Isso pode indicar que a chave de criptografia do Laravel mudou. Tente salvar as credenciais novamente.</p>';
            } else {
                echo '<p class="error">❌ Secret Key está vazia ou não pôde ser descriptografada! Configure a Secret Key no painel administrativo.</p>';
            }
        }
        
        // 5. Testar serviço de gateway
        if ($credentials->is_active && !empty($credentials->public_key) && !empty($secretKeyDecrypted)) {
            echo '<p class="success">✅ Credenciais estão configuradas corretamente!</p>';
            
            // Testar se o serviço está configurado
            try {
                $paymentService = new \App\Services\PaymentGatewayService($gateway);
                if ($paymentService->isConfigured()) {
                    echo '<p class="success">✅ Serviço de gateway está configurado corretamente!</p>';
                } else {
                    echo '<p class="error">❌ Serviço de gateway não está configurado (isConfigured() retornou false)</p>';
                }
            } catch (\Exception $e) {
                echo '<p class="error">❌ Erro ao testar serviço de gateway: ' . htmlspecialchars($e->getMessage()) . '</p>';
            }
        } else {
            echo '<p class="error">❌ Credenciais não estão configuradas corretamente. Corrija os problemas acima.</p>';
        }
        
        // 6. Mostrar primeiros e últimos caracteres das chaves (para verificação)
        if (!empty($credentials->public_key)) {
            $publicKeyPreview = substr($credentials->public_key, 0, 10) . '...' . substr($credentials->public_key, -10);
            echo '<p class="info">🔑 Public Key (preview): ' . htmlspecialchars($publicKeyPreview) . '</p>';
        }
        
        if (!empty($secretKeyDecrypted)) {
            $secretKeyPreview = substr($secretKeyDecrypted, 0, 10) . '...' . substr($secretKeyDecrypted, -10);
            echo '<p class="info">🔑 Secret Key (preview): ' . htmlspecialchars($secretKeyPreview) . '</p>';
        }
        
    } catch (\Exception $e) {
        echo '<p class="error">❌ Erro: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
    ?>
    
    <h2>Próximos Passos</h2>
    <ol>
        <li>Se as credenciais não existem ou estão vazias, configure-as no painel administrativo: <strong>Admin > Gateways > Configurar</strong></li>
        <li>Certifique-se de que a Public Key e Secret Key estão preenchidas corretamente</li>
        <li>Verifique se as credenciais estão marcadas como <strong>Ativas</strong></li>
        <li>Se houver erro de descriptografia, tente salvar as credenciais novamente</li>
    </ol>
</body>
</html>




