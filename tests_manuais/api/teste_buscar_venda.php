<?php

/**
 * TESTE RÁPIDO - Buscar/Consultar Venda
 * 
 * Este arquivo mostra como buscar uma venda específica
 */

// ═══════════════════════════════════════════════════
// CONFIGURAÇÕES - COLE SUAS CHAVES AQUI!
// ═══════════════════════════════════════════════════
$API_URL = 'https://playpayments.com/api/v1/transactions'; // URL base
$PUBLIC_KEY = 'PB-playpayments-1504-2132-1758'; // ← COLE SUA PUBLIC KEY AQUI (ou use Private Key)
$PRIVATE_KEY = 'SK-playpayments-1888-0831-6415'; // ← COLE SUA PRIVATE KEY AQUI

// ═══════════════════════════════════════════════════
// ID DA TRANSAÇÃO PARA BUSCAR
// ═══════════════════════════════════════════════════
// Cole aqui o transaction_id que você recebeu ao criar a venda
$TRANSACTION_ID = 'TXN_1234567890'; // ← COLE O ID DA TRANSAÇÃO AQUI

// ═══════════════════════════════════════════════════
// FUNÇÃO: Buscar Venda
// ═══════════════════════════════════════════════════
function buscarVenda($transactionId, $apiKey) {
    $url = 'https://playpayments.com/api/v1/transactions/' . $transactionId;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey, // Pode usar Public Key OU Private Key
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
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro na requisição cURL: ' . $error,
            'http_code' => $httpCode
        ];
    }
    
    if (empty($response)) {
        return [
            'success' => false,
            'error' => 'Resposta vazia da API',
            'http_code' => $httpCode
        ];
    }
    
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Erro ao decodificar JSON: ' . json_last_error_msg(),
            'http_code' => $httpCode,
            'raw_response' => substr($response, 0, 500)
        ];
    }
    
    return $result;
}

// ═══════════════════════════════════════════════════
// EXECUTAR BUSCA
// ═══════════════════════════════════════════════════

echo "🔍 BUSCAR VENDA - TESTE RÁPIDO\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📋 Configurações:\n";
echo "   URL: https://playpayments.com/api/v1/transactions/{$TRANSACTION_ID}\n";
echo "   Transaction ID: {$TRANSACTION_ID}\n";
echo "   Método: GET\n";
echo "   Autenticação: Bearer Token (Public Key ou Private Key)\n\n";

echo "📤 Fazendo requisição...\n\n";

// Usar Public Key ou Private Key (ambos funcionam para GET)
$resultado = buscarVenda($TRANSACTION_ID, $PUBLIC_KEY); // ou $PRIVATE_KEY

// ═══════════════════════════════════════════════════
// MOSTRAR RESULTADO
// ═══════════════════════════════════════════════════

if ($resultado['success'] ?? false) {
    $data = $resultado['data'];
    
    echo "✅ SUCESSO!\n\n";
    echo "📊 Dados da Venda:\n";
    echo "   Transaction ID: " . ($data['transaction_id'] ?? 'N/A') . "\n";
    echo "   External ID: " . ($data['external_id'] ?? 'N/A') . "\n";
    echo "   Valor: R$ " . number_format($data['amount'] ?? 0, 2, ',', '.') . "\n";
    echo "   Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "   Método de Pagamento: " . ($data['payment_method'] ?? 'N/A') . "\n";
    echo "   Descrição: " . ($data['description'] ?? 'N/A') . "\n";
    echo "   Criado em: " . ($data['created_at'] ?? 'N/A') . "\n";
    echo "   Expira em: " . ($data['expires_at'] ?? 'N/A') . "\n";
    
    if (isset($data['paid_at']) && $data['paid_at']) {
        echo "   Pago em: " . $data['paid_at'] . "\n";
    }
    
    if (isset($data['customer'])) {
        echo "\n👤 Cliente:\n";
        echo "   Nome: " . ($data['customer']['name'] ?? 'N/A') . "\n";
        echo "   Email: " . ($data['customer']['email'] ?? 'N/A') . "\n";
        echo "   Documento: " . ($data['customer']['document'] ?? 'N/A') . "\n";
    }
    
    if (isset($data['pix']) && $data['pix']) {
        echo "\n💳 Dados PIX:\n";
        echo "   QR Code: " . ($data['pix']['qr_code'] ?? 'N/A') . "\n";
        echo "   Payload: " . ($data['pix']['payload'] ?? 'N/A') . "\n";
        if (isset($data['pix']['end_to_end_id']) && $data['pix']['end_to_end_id']) {
            echo "   End to End ID: " . $data['pix']['end_to_end_id'] . "\n";
        }
        if (isset($data['pix']['txid']) && $data['pix']['txid']) {
            echo "   TXID: " . $data['pix']['txid'] . "\n";
        }
    }
    
    echo "\n📄 Resposta JSON Completa:\n";
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    
} else {
    echo "❌ ERRO!\n\n";
    echo "Erro: " . ($resultado['error'] ?? 'Erro desconhecido') . "\n";
    
    if (isset($resultado['message'])) {
        echo "Mensagem: " . $resultado['message'] . "\n";
    }
    
    if (isset($resultado['http_code'])) {
        echo "HTTP Code: " . $resultado['http_code'] . "\n";
    }
    
    if (isset($resultado['raw_response'])) {
        echo "\n📄 Resposta Bruta (primeiros 500 caracteres):\n";
        echo substr($resultado['raw_response'], 0, 500) . "\n";
    }
    
    echo "\n📄 Resposta JSON Completa:\n";
    echo json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

echo "\n\n═══════════════════════════════════════════════\n";
echo "💡 DICA: Para buscar uma venda, você precisa:\n";
echo "   1. Ter o transaction_id (recebido ao criar a venda)\n";
echo "   2. Fazer uma requisição GET para: /v1/transactions/{id}\n";
echo "   3. Usar Public Key OU Private Key no header Authorization\n";
echo "═══════════════════════════════════════════════\n";

?>








