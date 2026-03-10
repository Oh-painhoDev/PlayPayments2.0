<?php
/**
 * TESTE CORRETO - Criar PIX com headers corretos
 * 
 * Este arquivo mostra como enviar os headers CORRETAMENTE
 */

$API_URL = 'https://playpayments.com/api/v1/transactions';
$PUBLIC_KEY = 'PB-playpayments-1504-2132-1758';
$PRIVATE_KEY = 'SK-playpayments-1888-0831-6415';

echo "🚀 TESTE CRIAR PIX - Headers Corretos\n";
echo "═══════════════════════════════════════════════\n\n";

// Preparar dados
$data = [
    'amount' => 10.00,
    'payment_method' => 'pix',
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900'
    ],
    'description' => 'Teste PIX'
];

echo "📋 Dados:\n";
echo "   Valor: R$ " . number_format($data['amount'], 2, ',', '.') . "\n";
echo "   Cliente: " . $data['customer']['name'] . "\n\n";

// OPÇÃO 1: Headers separados (RECOMENDADO)
echo "📤 OPÇÃO 1: Enviando com headers separados (X-Public-Key e X-Private-Key)...\n\n";

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
curl_setopt($ch, CURLOPT_VERBOSE, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta:\n";
echo "   HTTP Code: $httpCode\n";

if ($error) {
    echo "   ❌ Erro cURL: $error\n";
}

$result = json_decode($response, true);

if (json_last_error() === JSON_ERROR_NONE) {
    if ($httpCode === 201 && ($result['success'] ?? false)) {
        echo "   ✅ SUCESSO! PIX criado!\n";
        echo "   Transaction ID: " . ($result['data']['transaction_id'] ?? 'N/A') . "\n";
        if (isset($result['data']['pix']['qr_code'])) {
            echo "   QR Code: " . substr($result['data']['pix']['qr_code'], 0, 50) . "...\n";
        }
    } else {
        echo "   ❌ ERRO!\n";
        echo "   " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    }
} else {
    echo "   ❌ Erro ao decodificar JSON\n";
    echo "   Resposta: " . substr($response, 0, 500) . "\n";
}

echo "\n\n";

// OPÇÃO 2: Header Authorization com dois tokens separados por ":"
echo "📤 OPÇÃO 2: Enviando com Authorization (Bearer public_key:private_key)...\n\n";

$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $PUBLIC_KEY . ':' . $PRIVATE_KEY,
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📥 Resposta:\n";
echo "   HTTP Code: $httpCode\n";

if ($error) {
    echo "   ❌ Erro cURL: $error\n";
}

$result = json_decode($response, true);

if (json_last_error() === JSON_ERROR_NONE) {
    if ($httpCode === 201 && ($result['success'] ?? false)) {
        echo "   ✅ SUCESSO! PIX criado!\n";
        echo "   Transaction ID: " . ($result['data']['transaction_id'] ?? 'N/A') . "\n";
    } else {
        echo "   ❌ ERRO!\n";
        if (isset($result['data']['debug'])) {
            echo "   Debug: " . json_encode($result['data']['debug'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
} else {
    echo "   ❌ Erro ao decodificar JSON\n";
}

echo "\n═══════════════════════════════════════════════\n";
echo "💡 Use a OPÇÃO 1 (headers separados) para garantir compatibilidade\n";
echo "═══════════════════════════════════════════════\n";

?>








