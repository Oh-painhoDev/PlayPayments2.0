<?php
/**
 * TESTE RÁPIDO - Mostra TUDO que acontece
 */

// ═══════════════════════════════════════════════════
// CONFIGURAÇÕES - COLE SUAS CHAVES AQUI!
// ═══════════════════════════════════════════════════
$API_URL = 'https://seu-dominio.com/api/v1/transactions'; // ← COLE A URL AQUI
$PUBLIC_KEY = 'PB-playpayments-XXXXXXXXXXXX'; // ← COLE SUA PUBLIC KEY AQUI
$PRIVATE_KEY = 'SK-playpayments-XXXXXXXXXXXX'; // ← COLE SUA PRIVATE KEY AQUI

// ═══════════════════════════════════════════════════
// DADOS DO TESTE
// ═══════════════════════════════════════════════════
$data = [
    'amount' => 10.00,
    'payment_method' => 'pix',
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900',
    ],
    'description' => 'Teste',
];

// ═══════════════════════════════════════════════════
// FAZER REQUISIÇÃO
// ═══════════════════════════════════════════════════
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Public-Key: ' . $PUBLIC_KEY,
    'X-Private-Key: ' . $PRIVATE_KEY,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// ═══════════════════════════════════════════════════
// MOSTRAR RESULTADO
// ═══════════════════════════════════════════════════
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste API PIX</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            line-height: 1.6;
        }
        .box {
            background: #252526;
            padding: 20px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #007acc;
        }
        .success { border-left-color: #4ec9b0; }
        .error { border-left-color: #f48771; }
        .warning { border-left-color: #dcdcaa; }
        pre {
            background: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        h1, h2 { color: #4ec9b0; }
        .label { color: #9cdcfe; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🔍 TESTE API PIX - RESULTADO COMPLETO</h1>
    
    <div class="box">
        <h2>📋 Configurações</h2>
        <p><span class="label">URL:</span> <?php echo htmlspecialchars($API_URL); ?></p>
        <p><span class="label">Public Key:</span> <?php echo htmlspecialchars(substr($PUBLIC_KEY, 0, 30)) . '...'; ?></p>
        <p><span class="label">Private Key:</span> <?php echo htmlspecialchars(substr($PRIVATE_KEY, 0, 30)) . '...'; ?></p>
    </div>
    
    <div class="box <?php echo $httpCode == 201 ? 'success' : 'error'; ?>">
        <h2>📥 Resposta da API</h2>
        <p><span class="label">HTTP Code:</span> <strong><?php echo $httpCode; ?></strong></p>
        <p><span class="label">Content-Type:</span> <?php echo htmlspecialchars($contentType ?: 'N/A'); ?></p>
        <p><span class="label">Tamanho:</span> <?php echo strlen($response); ?> bytes</p>
        
        <?php if ($error): ?>
            <p class="error"><span class="label">Erro cURL:</span> <?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="box">
        <h2>📄 Resposta Bruta (COMPLETA)</h2>
        <pre><?php echo htmlspecialchars($response ?: '(VAZIA)'); ?></pre>
    </div>
    
    <?php
    // Verificar se é HTML
    $isHtml = stripos($response, '<html') !== false || 
              stripos($response, '<!DOCTYPE') !== false ||
              stripos($response, '<body') !== false;
    
    if ($isHtml):
    ?>
    <div class="box warning">
        <h2>⚠️ ATENÇÃO: Resposta é HTML, não JSON!</h2>
        <p>Isso significa que:</p>
        <ul>
            <li>A URL pode estar errada (retornando página 404)</li>
            <li>O servidor está retornando uma página de erro</li>
            <li>Há um problema de roteamento</li>
        </ul>
        <p><strong>Verifique se a URL está correta!</strong></p>
    </div>
    <?php endif; ?>
    
    <?php
    // Tentar decodificar JSON
    $result = json_decode($response, true);
    $jsonError = json_last_error();
    
    if ($jsonError === JSON_ERROR_NONE):
    ?>
    <div class="box success">
        <h2>✅ JSON Válido!</h2>
        <pre><?php echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
        
        <?php if (isset($result['success']) && $result['success']): ?>
            <p style="color: #4ec9b0; font-size: 18px; font-weight: bold;">✅ SUCESSO! PIX criado!</p>
            <?php if (isset($result['data']['pix']['qr_code'])): ?>
                <p><span class="label">QR Code:</span> <?php echo htmlspecialchars($result['data']['pix']['qr_code']); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p style="color: #f48771; font-size: 18px; font-weight: bold;">❌ Erro na resposta</p>
            <?php if (isset($result['error'])): ?>
                <p><span class="label">Erro:</span> <?php echo htmlspecialchars($result['error']); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="box error">
        <h2>❌ Erro ao decodificar JSON</h2>
        <p><span class="label">Erro:</span> <?php echo json_last_error_msg(); ?></p>
        <p><span class="label">Código:</span> <?php echo $jsonError; ?></p>
        
        <h3>Primeiros 500 caracteres da resposta:</h3>
        <pre><?php echo htmlspecialchars(substr($response, 0, 500)); ?></pre>
    </div>
    <?php endif; ?>
    
    <div class="box">
        <h2>💡 Análise do HTTP Code</h2>
        <?php
        switch ($httpCode) {
            case 201:
                echo '<p style="color: #4ec9b0;">✅ 201 - Criado com sucesso!</p>';
                break;
            case 200:
                echo '<p style="color: #4ec9b0;">✅ 200 - OK (mas deveria ser 201)</p>';
                break;
            case 401:
                echo '<p style="color: #f48771;">❌ 401 - Não autorizado!</p>';
                echo '<ul>';
                echo '<li>Verifique se as chaves (Public Key e Private Key) estão corretas</li>';
                echo '<li>Para criar PIX, você precisa de AMBAS as chaves</li>';
                echo '<li>Verifique se não há espaços extras nas chaves</li>';
                echo '</ul>';
                break;
            case 404:
                echo '<p style="color: #f48771;">❌ 404 - Endpoint não encontrado!</p>';
                echo '<ul>';
                echo '<li>Verifique se a URL está correta: <strong>' . htmlspecialchars($API_URL) . '</strong></li>';
                echo '<li>Verifique se o servidor está rodando</li>';
                echo '<li>Verifique se a rota /api/v1/transactions existe</li>';
                echo '</ul>';
                break;
            case 422:
                echo '<p style="color: #dcdcaa;">⚠️ 422 - Dados inválidos!</p>';
                echo '<p>Verifique se todos os campos obrigatórios foram enviados</p>';
                break;
            case 500:
                echo '<p style="color: #f48771;">❌ 500 - Erro interno do servidor!</p>';
                echo '<p>Entre em contato com o suporte</p>';
                break;
            case 0:
                echo '<p style="color: #f48771;">❌ 0 - Não foi possível conectar!</p>';
                echo '<ul>';
                echo '<li>Verifique se a URL está correta</li>';
                echo '<li>Verifique sua conexão com a internet</li>';
                echo '<li>Verifique se o servidor está acessível</li>';
                echo '</ul>';
                break;
            default:
                echo '<p style="color: #dcdcaa;">⚠️ HTTP Code: ' . $httpCode . '</p>';
                echo '<p>Verifique a resposta acima para mais detalhes</p>';
        }
        ?>
    </div>
    
    <div class="box">
        <h2>📤 Dados Enviados</h2>
        <pre><?php echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE); ?></pre>
    </div>
</body>
</html>








