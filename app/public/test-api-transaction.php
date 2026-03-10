<?php
/**
 * Script de teste para API de transações
 * 
 * IMPORTANTE: Este arquivo está em app/public/test-api-transaction.php
 * 
 * Uso:
 * 1. Via navegador: http://localhost:8000/test-api-transaction.php
 * 2. Via terminal: php app/public/test-api-transaction.php
 * 
 * ANTES DE USAR:
 * - Edite a variável $apiSecret abaixo e coloque seu token de API (Secret Key)
 * - A variável $baseUrl já está configurada para localhost:8000
 */

// Configurações
// Ajuste a URL base conforme seu ambiente
$baseUrl = 'http://localhost:8000'; // Para desenvolvimento local
// $baseUrl = 'https://api.seudominio.com'; // Para produção

$apiUrl = $baseUrl . '/api/test/transaction';
$apiSecret = 'sk_L8WAZ8nRRHRkN7ORe3Q5ykQtxYkwvx1wmCN3crATxONj9JtCVmx'; // ⚠️ SUBSTITUA PELO SEU TOKEN DE API (Secret Key)
// Encontre seu token em: http://localhost:3000/api-key (página de API Keys)

// Dados da transação de teste
$data = [
    'amount' => 150.75,
    'payment_method' => 'pix',
    'sale_name' => 'Assinatura Premium Mensal',
    'description' => 'Assinatura de 30 dias do plano premium com acesso a todos os recursos e suporte prioritário',
    'customer' => [
        'name' => 'Maria Oliveira',
        'email' => 'maria.oliveira@example.com',
        'document' => '10563796006', // CPF válido (será formatado como 105.637.960-06)
        'phone' => '21987654321'
    ],
    'pix_expires_in_minutes' => 30
];

// Inicializar cURL
$ch = curl_init();

// Configurar opções do cURL
curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $apiSecret,
    ],
    CURLOPT_SSL_VERIFYPEER => false, // Desabilitar verificação SSL para desenvolvimento
    CURLOPT_SSL_VERIFYHOST => false,
]);

// Executar requisição
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Verificar se é requisição via navegador ou CLI
$isBrowser = isset($_SERVER['HTTP_HOST']);

if ($isBrowser) {
    // Exibir resultado formatado no navegador
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Teste API - Transação</title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #000;
                color: #fff;
                padding: 20px;
                max-width: 1200px;
                margin: 0 auto;
            }
            .container {
                background: #161616;
                border-radius: 12px;
                padding: 30px;
                margin-bottom: 20px;
            }
            h1 {
                color: #21b3dd;
                margin-top: 0;
            }
            h2 {
                color: #21b3dd;
                border-bottom: 2px solid #21b3dd;
                padding-bottom: 10px;
            }
            .info {
                background: #1f1f1f;
                padding: 15px;
                border-radius: 8px;
                margin: 10px 0;
            }
            .success {
                background: #1a3a2a;
                border-left: 4px solid #10b981;
            }
            .error {
                background: #3a1a1a;
                border-left: 4px solid #ef4444;
            }
            pre {
                background: #1f1f1f;
                padding: 15px;
                border-radius: 8px;
                overflow-x: auto;
                border: 1px solid #2d2d2d;
            }
            code {
                color: #21b3dd;
            }
            .label {
                color: #707070;
                font-weight: bold;
                display: inline-block;
                min-width: 150px;
            }
            .value {
                color: #fff;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: #21b3dd;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
                margin-top: 10px;
                font-weight: bold;
            }
            .btn:hover {
                background: #1a9bb8;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🧪 Teste API - Criar Transação</h1>
            
            <?php if ($apiSecret === 'sk_SEU_TOKEN_AQUI'): ?>
                <div class="info error">
                    <h2>⚠️ TOKEN NÃO CONFIGURADO</h2>
                    <p><strong>Você precisa editar este arquivo e substituir <code>sk_SEU_TOKEN_AQUI</code> pelo seu token de API real!</strong></p>
                    <p><strong>Como obter seu token:</strong></p>
                    <ol style="margin-left: 20px; margin-top: 10px;">
                        <li>Acesse: <a href="http://localhost:3000/api-key" target="_blank" style="color: #21b3dd;">http://localhost:3000/api-key</a></li>
                        <li>Copie sua <strong>Chave Secreta</strong> (Secret Key)</li>
                        <li>Cole no arquivo <code>test-api-transaction.php</code> na linha 22</li>
                    </ol>
                </div>
            <?php endif; ?>
            
            <div class="info">
                <span class="label">URL da API:</span>
                <span class="value"><?= htmlspecialchars($apiUrl) ?></span>
            </div>
            
            <div class="info">
                <span class="label">Método:</span>
                <span class="value">POST</span>
            </div>
            
            <div class="info">
                <span class="label">HTTP Code:</span>
                <span class="value"><?= $httpCode ?></span>
            </div>
            
            <?php if ($error): ?>
                <div class="info error">
                    <h2>❌ Erro cURL</h2>
                    <p><?= htmlspecialchars($error) ?></p>
                </div>
            <?php endif; ?>
            
            <h2>📤 Dados Enviados</h2>
            <pre><?= json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
            
            <h2>📥 Resposta da API</h2>
            <?php
            $responseData = json_decode($response, true);
            if ($responseData):
                $isSuccess = isset($responseData['success']) && $responseData['success'];
            ?>
                <div class="info <?= $isSuccess ? 'success' : 'error' ?>">
                    <h2><?= $isSuccess ? '✅ Sucesso' : '❌ Erro' ?></h2>
                    <?php if ($isSuccess): ?>
                        <p><strong>Mensagem:</strong> <?= htmlspecialchars($responseData['message'] ?? 'N/A') ?></p>
                        <?php if (isset($responseData['transaction'])): ?>
                            <p><strong>Transaction ID:</strong> <?= htmlspecialchars($responseData['transaction']['transaction_id'] ?? 'N/A') ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($responseData['transaction']['status'] ?? 'N/A') ?></p>
                            <p><strong>Valor:</strong> R$ <?= number_format($responseData['transaction']['amount'] ?? 0, 2, ',', '.') ?></p>
                            <p><strong>Nome do Produto:</strong> <?= htmlspecialchars($responseData['transaction']['product_name'] ?? 'N/A') ?></p>
                            <p><strong>Descrição do Produto:</strong> <?= htmlspecialchars($responseData['transaction']['product_description'] ?? 'N/A') ?></p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><strong>Erro:</strong> <?= htmlspecialchars($responseData['error'] ?? $responseData['message'] ?? 'Erro desconhecido') ?></p>
                        <?php if ($httpCode === 401): ?>
                            <div style="margin-top: 15px; padding: 15px; background: #3a1a1a; border-radius: 8px; border-left: 4px solid #ef4444;">
                                <p><strong>🔒 Erro de Autenticação (401)</strong></p>
                                <p>O token de API não foi aceito. Verifique:</p>
                                <ul style="margin-left: 20px; margin-top: 10px;">
                                    <li>O token está correto? (Chave Secreta completa começando com <code>sk_</code>)</li>
                                    <li>O token foi copiado sem espaços extras?</li>
                                    <li>Você está usando a <strong>Chave Secreta</strong> e não a Chave Pública?</li>
                                    <li>O token foi gerado para o usuário correto?</li>
                                </ul>
                                <p style="margin-top: 10px;">
                                    <a href="http://localhost:3000/api-key" target="_blank" style="color: #21b3dd; text-decoration: underline;">Obter novo token →</a>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($responseData['errors'])): ?>
                            <h3>Erros de Validação:</h3>
                            <pre><?= json_encode($responseData['errors'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <pre><?= htmlspecialchars($response) ?></pre>
            
            <?php if ($httpCode === 401): ?>
                <div class="info" style="margin-top: 20px;">
                    <h3>🔧 Debug - Informações da Requisição</h3>
                    <p><strong>Token usado:</strong> <code><?= htmlspecialchars(substr($apiSecret, 0, 20)) ?>...</code></p>
                    <p><strong>URL da API:</strong> <code><?= htmlspecialchars($apiUrl) ?></code></p>
                    <p><strong>Header Authorization:</strong> <code>Bearer <?= htmlspecialchars(substr($apiSecret, 0, 20)) ?>...</code></p>
                    <p style="margin-top: 10px;"><strong>⚠️ IMPORTANTE:</strong> Certifique-se de usar a <strong>Chave Secreta</strong> (começa com <code>sk_</code>), não a Chave Pública!</p>
                </div>
            <?php endif; ?>
            
            <a href="javascript:location.reload()" class="btn">🔄 Testar Novamente</a>
        </div>
    </body>
    </html>
    <?php
} else {
    // Exibir resultado no terminal
    echo "🧪 Teste API - Criar Transação\n";
    echo "================================\n\n";
    echo "URL: $apiUrl\n";
    echo "Método: POST\n";
    echo "HTTP Code: $httpCode\n\n";
    
    if ($error) {
        echo "❌ Erro cURL: $error\n\n";
    }
    
    echo "📤 Dados Enviados:\n";
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    
    echo "📥 Resposta da API:\n";
    $responseData = json_decode($response, true);
    if ($responseData) {
        if (isset($responseData['success']) && $responseData['success']) {
            echo "✅ Sucesso!\n";
            echo "Mensagem: " . ($responseData['message'] ?? 'N/A') . "\n";
            if (isset($responseData['transaction'])) {
                echo "Transaction ID: " . ($responseData['transaction']['transaction_id'] ?? 'N/A') . "\n";
                echo "Status: " . ($responseData['transaction']['status'] ?? 'N/A') . "\n";
                echo "Valor: R$ " . number_format($responseData['transaction']['amount'] ?? 0, 2, ',', '.') . "\n";
                echo "Nome do Produto: " . ($responseData['transaction']['product_name'] ?? 'N/A') . "\n";
                echo "Descrição do Produto: " . ($responseData['transaction']['product_description'] ?? 'N/A') . "\n";
            }
        } else {
            echo "❌ Erro!\n";
            echo "Erro: " . ($responseData['error'] ?? 'Erro desconhecido') . "\n";
            if (isset($responseData['errors'])) {
                echo "\nErros de Validação:\n";
                print_r($responseData['errors']);
            }
        }
    } else {
        echo $response . "\n";
    }
    
    echo "\n\nResposta Completa (JSON):\n";
    echo json_encode(json_decode($response, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
?>

