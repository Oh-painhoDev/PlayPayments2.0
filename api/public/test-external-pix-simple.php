<?php
/**
 * Teste Simples - API PIX Externa
 * 
 * Ajuste as variáveis abaixo e acesse: http://seu-dominio.com/test-external-pix-simple.php
 */

// ============================================
// CONFIGURAÇÕES - AJUSTE AQUI
// ============================================
$API_BASE_URL = 'http://localhost/api/external-pix'; // URL da sua API
$API_SECRET_KEY = 'SK-playpayments-...'; // Token de autenticação da sua conta (OBRIGATÓRIO)
$EXTERNAL_API_URL = 'https://api.exemplo.com'; // URL da API PIX externa
$EXTERNAL_API_TOKEN = 'seu_token_aqui'; // Token da API externa
$AUTH_TYPE = 'bearer'; // 'bearer', 'basic', 'header'

// ============================================
// TESTE 1: CRIAR PIX (Formato PodPay)
// ============================================
echo "<h2>🧪 Teste 1: Criar PIX (Formato PodPay)</h2>";

$pixData = [
    'amount' => 1000, // em centavos (R$ 10,00)
    'currency' => 'BRL',
    'paymentMethod' => 'pix',
    'pix' => [
        'expiresIn' => 900, // 15 minutos em segundos
    ],
    'items' => [
        [
            'title' => 'Produto Teste',
            'unitPrice' => 1000,
            'quantity' => 1,
            'tangible' => false,
        ]
    ],
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900',
        'phone' => '11999999999',
    ],
    'externalRef' => 'TEST_' . time(),
    'metadata' => 'Teste de PIX',
    'api_url' => $EXTERNAL_API_URL,
    'api_token' => $EXTERNAL_API_TOKEN,
    'auth_type' => $AUTH_TYPE,
];

$ch = curl_init($API_BASE_URL . '/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pixData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $API_SECRET_KEY // OBRIGATÓRIO para identificar a conta
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

echo "<p><strong>Status HTTP:</strong> $httpCode</p>";

if ($result && isset($result['success']) && $result['success']) {
    echo "<p style='color: green;'>✅ PIX criado com sucesso!</p>";
    echo "<p><strong>Transaction ID:</strong> " . ($result['data']['transaction_id'] ?? 'N/A') . "</p>";
    echo "<p><strong>QR Code:</strong> " . ($result['data']['qrcode'] ?? 'N/A') . "</p>";
    
    $transactionId = $result['data']['transaction_id'] ?? null;
    
    // Exibir QR Code visual se tiver a biblioteca
    if (isset($result['data']['qrcode'])) {
        echo "<p><strong>QR Code (para copiar):</strong></p>";
        echo "<textarea style='width: 100%; height: 100px;'>" . htmlspecialchars($result['data']['qrcode']) . "</textarea>";
    }
    
    echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    
    // ============================================
    // TESTE 2: CONSULTAR STATUS
    // ============================================
    if ($transactionId) {
        echo "<hr>";
        echo "<h2>🔍 Teste 2: Consultar Status</h2>";
        
        $params = http_build_query([
            'api_url' => $EXTERNAL_API_URL,
            'api_token' => $EXTERNAL_API_TOKEN,
            'auth_type' => $AUTH_TYPE,
        ]);
        
        $statusUrl = $API_BASE_URL . '/status/' . urlencode($transactionId) . '?' . $params;
        
        $ch = curl_init($statusUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $API_SECRET_KEY // OBRIGATÓRIO para identificar a conta
        ]);
        
        $statusResponse = curl_exec($ch);
        $statusHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $statusResult = json_decode($statusResponse, true);
        
        echo "<p><strong>Status HTTP:</strong> $statusHttpCode</p>";
        
        if ($statusResult && isset($statusResult['success']) && $statusResult['success']) {
            $status = $statusResult['data']['status'] ?? 'unknown';
            $color = $status === 'paid' ? 'green' : ($status === 'pending' ? 'orange' : 'red');
            echo "<p style='color: $color;'><strong>Status:</strong> " . strtoupper($status) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Erro ao consultar status</p>";
        }
        
        echo "<pre>" . htmlspecialchars(json_encode($statusResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . "</pre>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Erro ao criar PIX</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";
echo "<h3>📋 Informações da Requisição</h3>";
echo "<pre>";
echo "URL: " . $API_BASE_URL . "/create\n";
echo "Método: POST\n";
echo "Payload:\n";
echo json_encode($pixData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "</pre>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    h2 {
        color: #333;
        border-bottom: 2px solid #667eea;
        padding-bottom: 10px;
    }
    pre {
        background: #fff;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto;
        border: 1px solid #ddd;
    }
    textarea {
        font-family: monospace;
        font-size: 12px;
    }
</style>

