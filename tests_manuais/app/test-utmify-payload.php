<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "=== TESTE PAYLOAD UTMIFY ===\n\n";

// Buscar transação específica
$transactionId = 'PXB_6913CF609A36E_1762905952';
$transaction = Transaction::where('transaction_id', $transactionId)->first();

if (!$transaction) {
    echo "❌ Transação não encontrada: {$transactionId}\n";
    exit(1);
}

// Buscar integração
$integration = UtmifyIntegration::where('user_id', $transaction->user_id)
    ->where('is_active', true)
    ->first();

if (!$integration) {
    echo "❌ Integração não encontrada\n";
    exit(1);
}

echo "📊 PREPARANDO PAYLOAD...\n\n";

// Preparar payload manualmente (seguindo exatamente a documentação)
$customerData = $transaction->customer_data ?? [];
$metadata = $transaction->metadata ?? [];

// Customer - REMOVER campos null (API UTMify não aceita null)
$customer = [
    'name' => $customerData['name'] ?? $transaction->user->name ?? 'Cliente',
    'email' => $customerData['email'] ?? $transaction->user->email ?? 'cliente@example.com',
    'country' => $customerData['country'] ?? 'BR',
];

// Adicionar apenas campos que não são null
if (!empty($customerData['phone'])) {
    $customer['phone'] = $customerData['phone'];
}
if (!empty($customerData['document'])) {
    $customer['document'] = $customerData['document'];
}
if (!empty($customerData['ip'])) {
    $customer['ip'] = $customerData['ip'];
}

// Products - planId e planName devem estar presentes (podem ser null)
$productName = $metadata['product_name'] ?? $metadata['description'] ?? 'Produto';
$product = [
    'id' => $transaction->transaction_id,
    'name' => $productName,
    'planId' => $metadata['plan_id'] ?? null,
    'planName' => $metadata['plan_name'] ?? null,
    'quantity' => isset($metadata['quantity']) ? (int)$metadata['quantity'] : 1,
    'priceInCents' => (int)round($transaction->amount * 100),
];

$products = [$product];

// Tracking Parameters - deve ser um objeto com todos os campos (podem ser null)
$trackingParams = [
    'src' => $metadata['src'] ?? null,
    'sck' => $metadata['sck'] ?? null,
    'utm_source' => $metadata['utm_source'] ?? null,
    'utm_campaign' => $metadata['utm_campaign'] ?? null,
    'utm_medium' => $metadata['utm_medium'] ?? null,
    'utm_content' => $metadata['utm_content'] ?? null,
    'utm_term' => $metadata['utm_term'] ?? null,
];

// Commission
$totalPriceInCents = (int)round($transaction->amount * 100);
$gatewayFeeInCents = (int)round(($transaction->fee_amount ?? 0) * 100);
$userCommissionInCents = (int)round(($transaction->net_amount ?? 0) * 100);

if ($userCommissionInCents <= 0) {
    $userCommissionInCents = $totalPriceInCents;
}

$commission = [
    'totalPriceInCents' => $totalPriceInCents,
    'gatewayFeeInCents' => $gatewayFeeInCents,
    'userCommissionInCents' => $userCommissionInCents,
];

// Payload completo (seguindo EXATAMENTE a documentação)
// IMPORTANTE: approvedDate, refundedAt, planId, planName devem estar presentes (podem ser null)
// Mas customer.ip, phone, document devem ser omitidos se null
$payload = [
    'orderId' => $transaction->transaction_id,
    'platform' => 'playpayments',
    'paymentMethod' => 'pix',
    'status' => 'waiting_payment',
    'createdAt' => $transaction->created_at->utc()->format('Y-m-d H:i:s'),
    'approvedDate' => $transaction->paid_at ? $transaction->paid_at->utc()->format('Y-m-d H:i:s') : null,
    'refundedAt' => $transaction->refunded_at ? $transaction->refunded_at->utc()->format('Y-m-d H:i:s') : null,
    'customer' => $customer,
    'products' => $products,
    'trackingParameters' => $trackingParams,
    'commission' => $commission,
];

echo "📦 PAYLOAD PREPARADO:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n";

echo "🔑 HEADERS:\n";
echo "x-api-token: " . substr($integration->api_token, 0, 20) . "...\n\n";

echo "🚀 ENVIANDO PARA API UTMIFY...\n\n";

try {
    $response = Http::timeout(30)
        ->withHeaders([
            'x-api-token' => $integration->api_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
    
    echo "📡 RESPOSTA DA API:\n";
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n\n";
    
    if ($response->successful()) {
        echo "✅ SUCESSO! Transação enviada para UTMify.\n";
    } else {
        echo "❌ ERRO! Status: " . $response->status() . "\n";
        echo "Resposta: " . $response->body() . "\n";
        
        // Tentar parsear erro
        $errorData = json_decode($response->body(), true);
        if ($errorData) {
            echo "\n📋 DETALHES DO ERRO:\n";
            echo json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

