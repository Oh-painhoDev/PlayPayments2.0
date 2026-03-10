<?php

/**
 * EXEMPLO: Buscar/Consultar Transação via API
 * 
 * Este arquivo mostra como buscar uma transação específica
 * A API sempre retorna os dados mais atualizados do banco de dados
 */

// ═══════════════════════════════════════════════════
// CONFIGURAÇÕES - ALTERE AQUI!
// ═══════════════════════════════════════════════════
$API_URL = 'https://playpayments.com/api/v1/transactions';
$PUBLIC_KEY = 'PB-playpayments-1504-2132-1758'; // Public Key OU Private Key (qualquer um funciona para GET)
$TRANSACTION_ID = 'PXB_69144E0B55F40_1762938379'; // ID da transação (transaction_id ou external_id)

// ═══════════════════════════════════════════════════
// FUNÇÃO: Buscar Transação
// ═══════════════════════════════════════════════════
function buscarTransacao($transactionId, $apiKey) {
    $url = 'https://playpayments.com/api/v1/transactions/' . $transactionId;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey, // Pode usar Public Key OU Private Key
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
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

echo "🔍 BUSCAR TRANSAÇÃO VIA API\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📋 Configurações:\n";
echo "   URL: {$API_URL}/{$TRANSACTION_ID}\n";
echo "   Transaction ID: {$TRANSACTION_ID}\n";
echo "   Método: GET\n";
echo "   Autenticação: Bearer Token (Public Key ou Private Key)\n\n";

echo "📤 Fazendo requisição...\n\n";

$resultado = buscarTransacao($TRANSACTION_ID, $PUBLIC_KEY);

// ═══════════════════════════════════════════════════
// MOSTRAR RESULTADO
// ═══════════════════════════════════════════════════

if ($resultado['success'] ?? false) {
    $data = $resultado['data'];
    
    echo "✅ SUCESSO!\n\n";
    echo "📊 Dados da Transação:\n";
    echo "   Transaction ID: " . ($data['transaction_id'] ?? 'N/A') . "\n";
    echo "   External ID: " . ($data['external_id'] ?? 'N/A') . "\n";
    echo "   Valor: R$ " . number_format($data['amount'] ?? 0, 2, ',', '.') . "\n";
    echo "   Status: " . ($data['status'] ?? 'N/A') . "\n";
    echo "   Método de Pagamento: " . ($data['payment_method'] ?? 'N/A') . "\n";
    echo "   Descrição: " . ($data['description'] ?? 'N/A') . "\n";
    echo "   Criado em: " . ($data['created_at'] ?? 'N/A') . "\n";
    echo "   Atualizado em: " . ($data['updated_at'] ?? 'N/A') . "\n";
    
    if (isset($data['expires_at']) && $data['expires_at']) {
        echo "   Expira em: " . $data['expires_at'] . "\n";
    }
    
    if (isset($data['paid_at']) && $data['paid_at']) {
        echo "   ✅ PAGO em: " . $data['paid_at'] . "\n";
    }
    
    if (isset($data['refunded_at']) && $data['refunded_at']) {
        echo "   ⚠️  Reembolsado em: " . $data['refunded_at'] . "\n";
    }
    
    if (isset($data['customer'])) {
        echo "\n👤 Cliente:\n";
        echo "   Nome: " . ($data['customer']['name'] ?? 'N/A') . "\n";
        echo "   Email: " . ($data['customer']['email'] ?? 'N/A') . "\n";
        echo "   Documento: " . ($data['customer']['document'] ?? 'N/A') . "\n";
        if (isset($data['customer']['phone'])) {
            echo "   Telefone: " . $data['customer']['phone'] . "\n";
        }
    }
    
    if (isset($data['pix']) && $data['pix']) {
        echo "\n💳 Dados PIX:\n";
        if (isset($data['pix']['qr_code'])) {
            echo "   QR Code: " . $data['pix']['qr_code'] . "\n";
        }
        if (isset($data['pix']['payload'])) {
            echo "   Payload: " . $data['pix']['payload'] . "\n";
        }
        if (isset($data['pix']['end_to_end_id']) && $data['pix']['end_to_end_id']) {
            echo "   End to End ID: " . $data['pix']['end_to_end_id'] . "\n";
        }
        if (isset($data['pix']['txid']) && $data['pix']['txid']) {
            echo "   TXID: " . $data['pix']['txid'] . "\n";
        }
        if (isset($data['pix']['expiration_date']) && $data['pix']['expiration_date']) {
            echo "   Expira em: " . $data['pix']['expiration_date'] . "\n";
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
echo "💡 IMPORTANTE:\n";
echo "   - A API sempre retorna os dados mais atualizados\n";
echo "   - Se a transação estiver pendente, o sistema verifica\n";
echo "     o status no gateway antes de retornar\n";
echo "   - O comando automático atualiza o status a cada segundo\n";
echo "   - Use Public Key OU Private Key (qualquer um funciona)\n";
echo "═══════════════════════════════════════════════\n";

?>








