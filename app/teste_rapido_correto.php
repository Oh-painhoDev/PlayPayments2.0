<?php
/**
 * Brpix Pix API - Teste rápido CORRETO
 * Mostra tudo em JSON
 * 
 * IMPORTANTE: Para criar PIX (POST), você precisa enviar AMBOS os tokens:
 * - Public Key (PB-playpayments-...)
 * - Private Key (SK-playpayments-...)
 */

// ═══════════════════════════════════════════════════
// CONFIGURAÇÕES
// ═══════════════════════════════════════════════════
$API_URL = 'https://playpayments.com/api/v1/transactions';
$PUBLIC_KEY = 'PB-playpayments-1504-2132-1758';
$PRIVATE_KEY = 'SK-playpayments-1888-0831-6415';

// ═══════════════════════════════════════════════════
// DADOS DO PAGAMENTO
// ═══════════════════════════════════════════════════
$data = [
    'amount' => 3.00,
    'payment_method' => 'pix', // IMPORTANTE: Especificar método de pagamento
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900',
        'phone' => '11999999999'
    ],
    'external_id' => 'teste_001',
    'description' => 'Pagamento de teste',
    'expires_in' => 3600
];

// ═══════════════════════════════════════════════════
// FAZER REQUISIÇÃO CURL
// ═══════════════════════════════════════════════════

// OPÇÃO 1: Headers separados (RECOMENDADO - mais claro)
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Public-Key: ' . $PUBLIC_KEY,      // ← Public Key
    'X-Private-Key: ' . $PRIVATE_KEY,    // ← Private Key
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

// ═══════════════════════════════════════════════════
// MOSTRAR RESULTADO EM JSON
// ═══════════════════════════════════════════════════
header('Content-Type: application/json; charset=utf-8');

$output = [
    'http_code' => $httpCode,
    'method' => 'POST',
    'endpoint' => '/api/v1/transactions',
    'authentication' => 'X-Public-Key + X-Private-Key (headers separados)',
];

if ($error) {
    $output['success'] = false;
    $output['error'] = $error;
} else {
    $decoded = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $output['success'] = true;
        $output['data'] = $decoded;
        
        // Adicionar informações úteis se sucesso
        if (isset($decoded['data']['transaction_id'])) {
            $output['transaction_id'] = $decoded['data']['transaction_id'];
            $output['status'] = $decoded['data']['status'] ?? 'unknown';
            if (isset($decoded['data']['pix']['qr_code'])) {
                $output['has_qr_code'] = true;
                $output['qr_code_preview'] = substr($decoded['data']['pix']['qr_code'], 0, 50) . '...';
            }
        }
    } else {
        $output['success'] = false;
        $output['error'] = 'Erro ao decodificar JSON: ' . json_last_error_msg();
        $output['raw_response'] = substr($response, 0, 500);
    }
}

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

/* 
 * ═══════════════════════════════════════════════════
 * ALTERNATIVA: Usar Authorization header
 * ═══════════════════════════════════════════════════
 * 
 * Se preferir usar apenas o header Authorization, use:
 * 
 * 'Authorization: Bearer ' . $PUBLIC_KEY . ':' . $PRIVATE_KEY,
 * 
 * Exemplo completo:
 * 
 * curl_setopt($ch, CURLOPT_HTTPHEADER, [
 *     'Authorization: Bearer ' . $PUBLIC_KEY . ':' . $PRIVATE_KEY,
 *     'Content-Type: application/json',
 *     'Accept: application/json'
 * ]);
 * 
 * ═══════════════════════════════════════════════════
 * NOTAS IMPORTANTES:
 * ═══════════════════════════════════════════════════
 * 
 * 1. Para CRIAR PIX (POST): Precisa de AMBOS os tokens
 *    - Public Key (PB-playpayments-...)
 *    - Private Key (SK-playpayments-...)
 * 
 * 2. Para CONSULTAR (GET): Pode usar apenas UM token
 *    - Public Key OU Private Key
 * 
 * 3. Sempre inclua 'payment_method' => 'pix' no body
 * 
 * 4. O campo 'expires_in' é em segundos (3600 = 1 hora)
 */

?>








