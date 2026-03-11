<?php
/**
 * TESTE WEBHOOK PLUGGOU
 * 
 * Este arquivo simula um webhook da Pluggou para testar se a rota está funcionando
 */

$webhookUrl = 'https://playpayments.com/webhook/pluggou';

// Simular webhook da Pluggou (conforme documentação)
$webhookData = [
    'id' => 'test-webhook-' . time(),
    'event_type' => 'transaction',
    'data' => [
        'id' => 'f5ef6662-b893-4080-8xxx', // ID da transação na Pluggou
        'payment_method' => 'pix',
        'amount' => 300, // em centavos (R$ 3,00)
        'platform_tax' => 206, // em centavos
        'liquid_amount' => 95, // em centavos
        'status' => 'paid',
        'paid_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s'),
    ]
];

echo "🧪 TESTE WEBHOOK PLUGGOU\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📋 Configurações:\n";
echo "   URL: $webhookUrl\n";
echo "   Método: POST\n\n";

echo "📤 Enviando webhook simulado...\n\n";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
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

echo "📥 Resposta:\n";
echo "   HTTP Code: $httpCode\n\n";

if ($error) {
    echo "❌ Erro cURL: $error\n\n";
}

$result = json_decode($response, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ Resposta JSON válida:\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
} else {
    echo "❌ Erro ao decodificar JSON: " . json_last_error_msg() . "\n";
    echo "\n📄 Resposta bruta:\n";
    echo substr($response, 0, 500) . "\n";
}

echo "\n═══════════════════════════════════════════════\n";
echo "💡 URL do Webhook para configurar na Pluggou:\n";
echo "   $webhookUrl\n";
echo "\n💡 Alternativas (se a primeira não funcionar):\n";
echo "   $webhookUrl/\n";
echo "   https://www.playpayments.com/webhook/pluggou\n";
echo "═══════════════════════════════════════════════\n";

?>








