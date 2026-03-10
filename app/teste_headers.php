<?php
/**
 * TESTE DE HEADERS - Verificar se os headers estão sendo enviados corretamente
 */

$API_URL = 'https://playpayments.com/api/v1/transactions';
$PUBLIC_KEY = 'PB-playpayments-1504-2132-1758';
$PRIVATE_KEY = 'SK-playpayments-1888-0831-6415';

echo "🔍 TESTE DE HEADERS - API PIX\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📋 Configurações:\n";
echo "   URL: $API_URL\n";
echo "   Public Key: $PUBLIC_KEY\n";
echo "   Private Key: " . substr($PRIVATE_KEY, 0, 20) . "...\n\n";

// Preparar dados
$data = [
    'amount' => 10.00,
    'payment_method' => 'pix',
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900'
    ],
    'description' => 'Teste de headers'
];

// Fazer requisição com headers explícitos
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Public-Key: ' . $PUBLIC_KEY,
    'X-Private-Key: ' . $PRIVATE_KEY,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true); // Ativar modo verbose para ver headers enviados

// Capturar verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

// Ler verbose output
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

curl_close($ch);

echo "📤 Headers Enviados:\n";
echo "   X-Public-Key: $PUBLIC_KEY\n";
echo "   X-Private-Key: " . substr($PRIVATE_KEY, 0, 20) . "...\n";
echo "   Content-Type: application/json\n\n";

echo "📥 Resposta da API:\n";
echo "   HTTP Code: $httpCode\n\n";

if ($error) {
    echo "❌ Erro cURL: $error\n\n";
}

if (!empty($verboseLog)) {
    echo "📋 Log Verbose (Headers Enviados):\n";
    echo "═══════════════════════════════════════════════\n";
    // Extrair apenas as linhas relevantes
    $lines = explode("\n", $verboseLog);
    foreach ($lines as $line) {
        if (stripos($line, 'X-Public-Key') !== false || 
            stripos($line, 'X-Private-Key') !== false || 
            stripos($line, 'Content-Type') !== false ||
            stripos($line, 'POST') !== false ||
            stripos($line, 'HTTP') !== false) {
            echo "   " . trim($line) . "\n";
        }
    }
    echo "═══════════════════════════════════════════════\n\n";
}

if (empty($response)) {
    echo "❌ Resposta vazia da API\n";
    exit;
}

$result = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erro ao decodificar JSON: " . json_last_error_msg() . "\n";
    echo "\n📄 Resposta Bruta (primeiros 500 caracteres):\n";
    echo substr($response, 0, 500) . "\n";
    exit;
}

echo "📄 Resposta JSON:\n";
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

if (isset($result['data']['debug'])) {
    echo "🔍 Debug Info:\n";
    echo json_encode($result['data']['debug'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}

if ($httpCode === 401) {
    echo "❌ ERRO 401 - Não autorizado\n";
    echo "\n💡 Possíveis causas:\n";
    echo "   1. Headers não estão sendo enviados corretamente\n";
    echo "   2. Tokens estão incorretos\n";
    echo "   3. Headers estão sendo normalizados/modificados pelo servidor\n";
    echo "   4. Problema de case-sensitivity nos nomes dos headers\n";
} elseif ($httpCode === 201) {
    echo "✅ SUCESSO! PIX criado com sucesso!\n";
} else {
    echo "⚠️ HTTP Code: $httpCode\n";
}

echo "\n═══════════════════════════════════════════════\n";

?>








