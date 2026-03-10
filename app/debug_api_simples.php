<?php
/**
 * DEBUG SIMPLES - Mostra TUDO que a API retorna
 */

// CONFIGURAÇÕES
$API_URL = 'https://seu-dominio.com/api/v1/transactions';
$PUBLIC_KEY = 'PB-playpayments-sua-chave-publica-aqui';
$PRIVATE_KEY = 'SK-playpayments-sua-chave-secreta-aqui';

// Dados do teste
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

echo "═══════════════════════════════════════════════════\n";
echo "🔍 DEBUG COMPLETO DA API PIX\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "📍 URL: $API_URL\n";
echo "🔑 Public Key: " . substr($PUBLIC_KEY, 0, 30) . "...\n";
echo "🔑 Private Key: " . substr($PRIVATE_KEY, 0, 30) . "...\n\n";

echo "📦 Dados enviados:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Fazer requisição
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
curl_setopt($ch, CURLOPT_VERBOSE, true); // Modo verbose

// Capturar output verbose
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$curlInfo = curl_getinfo($ch);

// Mostrar verbose
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

echo "═══════════════════════════════════════════════════\n";
echo "📥 INFORMAÇÕES DA RESPOSTA\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "HTTP Code: $httpCode\n";
echo "Content-Type: " . ($contentType ?: 'N/A') . "\n";
echo "Tamanho: " . strlen($response) . " bytes\n";

if ($error) {
    echo "❌ Erro cURL: $error\n";
}

echo "\n";

// Mostrar verbose se houver
if (!empty($verboseLog)) {
    echo "═══════════════════════════════════════════════════\n";
    echo "📋 LOG VERBOSE DO cURL\n";
    echo "═══════════════════════════════════════════════════\n";
    echo $verboseLog . "\n";
}

// Mostrar resposta
echo "═══════════════════════════════════════════════════\n";
echo "📄 RESPOSTA COMPLETA (primeiros 3000 caracteres)\n";
echo "═══════════════════════════════════════════════════\n";
echo substr($response, 0, 3000);
if (strlen($response) > 3000) {
    echo "\n... (truncado, total: " . strlen($response) . " caracteres)";
}
echo "\n\n";

// Verificar se é HTML
if (stripos($response, '<html') !== false || stripos($response, '<!DOCTYPE') !== false) {
    echo "⚠️ ATENÇÃO: A resposta é HTML, não JSON!\n";
    echo "Isso geralmente significa:\n";
    echo "  - URL incorreta (página 404)\n";
    echo "  - Servidor retornando página de erro\n";
    echo "  - Problema de roteamento\n\n";
}

// Tentar decodificar JSON
echo "═══════════════════════════════════════════════════\n";
echo "🔍 TENTANDO DECODIFICAR JSON\n";
echo "═══════════════════════════════════════════════════\n\n";

$result = json_decode($response, true);
$jsonError = json_last_error();

if ($jsonError === JSON_ERROR_NONE) {
    echo "✅ JSON válido!\n\n";
    echo "Resposta decodificada:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
} else {
    echo "❌ Erro ao decodificar JSON:\n";
    echo "  Erro: " . json_last_error_msg() . "\n";
    echo "  Código: $jsonError\n\n";
    
    // Mostrar onde está o erro (aproximadamente)
    $lines = explode("\n", substr($response, 0, 500));
    echo "Primeiras linhas da resposta:\n";
    foreach ($lines as $i => $line) {
        echo "  " . ($i + 1) . ": " . htmlspecialchars($line) . "\n";
    }
}

echo "\n";

// Análise do HTTP Code
echo "═══════════════════════════════════════════════════\n";
echo "💡 ANÁLISE DO HTTP CODE\n";
echo "═══════════════════════════════════════════════════\n\n";

switch ($httpCode) {
    case 200:
    case 201:
        echo "✅ Sucesso! Mas o JSON está inválido. Verifique a resposta acima.\n";
        break;
    case 401:
        echo "❌ Não autorizado!\n";
        echo "  - Verifique se as chaves (Public Key e Private Key) estão corretas\n";
        echo "  - Para criar PIX, você precisa de AMBAS as chaves\n";
        break;
    case 404:
        echo "❌ Endpoint não encontrado!\n";
        echo "  - Verifique se a URL está correta: $API_URL\n";
        echo "  - Verifique se o servidor está rodando\n";
        break;
    case 422:
        echo "❌ Dados inválidos!\n";
        echo "  - Verifique se todos os campos obrigatórios foram enviados\n";
        break;
    case 500:
        echo "❌ Erro interno do servidor!\n";
        echo "  - Entre em contato com o suporte\n";
        break;
    case 0:
        echo "❌ Não foi possível conectar ao servidor!\n";
        echo "  - Verifique se a URL está correta\n";
        echo "  - Verifique sua conexão com a internet\n";
        echo "  - Verifique se o servidor está acessível\n";
        break;
    default:
        echo "⚠️ HTTP Code: $httpCode\n";
        echo "  Verifique a resposta acima para mais detalhes\n";
}

echo "\n";

curl_close($ch);








