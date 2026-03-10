<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

echo "=== DEBUG COMPLETO UTMIFY ===\n\n";

// Buscar última transação PIX
$transaction = Transaction::where('payment_method', 'pix')
    ->where('status', 'pending')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$transaction) {
    echo "❌ Nenhuma transação PIX encontrada\n";
    exit(1);
}

echo "📊 TRANSAÇÃO:\n";
echo "  ID: {$transaction->id}\n";
echo "  Transaction ID: {$transaction->transaction_id}\n";
echo "  User ID: {$transaction->user_id}\n";
echo "  Payment Method: {$transaction->payment_method}\n";
echo "  Status: {$transaction->status}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "\n";

// Verificar integração
$integration = UtmifyIntegration::where('user_id', $transaction->user_id)
    ->where('is_active', true)
    ->first();

if (!$integration) {
    echo "❌ Nenhuma integração ativa encontrada para o usuário {$transaction->user_id}\n";
    exit(1);
}

echo "🔑 INTEGRAÇÃO:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "  User ID: {$integration->user_id}\n";
echo "  Ativo: " . ($integration->is_active ? 'SIM ✅' : 'NÃO ❌') . "\n";
echo "  Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM ✅' : 'NÃO ❌') . "\n";
echo "  API Token: " . substr($integration->api_token, 0, 30) . "...\n";
echo "  Token Length: " . strlen($integration->api_token) . "\n";
echo "\n";

// Testar UtmifyService
echo "🧪 TESTANDO UTMifyService...\n\n";

try {
    $utmifyService = new UtmifyService();
    
    // Verificar filtros
    echo "Verificando filtros:\n";
    echo "  - Payment Method: {$transaction->payment_method} (é PIX? " . (strtolower($transaction->payment_method) === 'pix' ? 'SIM ✅' : 'NÃO ❌') . ")\n";
    echo "  - Status: {$transaction->status} (permitido? " . (in_array(strtolower($transaction->status), ['pending', 'paid', 'refunded', 'partially_refunded']) ? 'SIM ✅' : 'NÃO ❌') . ")\n";
    echo "\n";
    
    echo "Chamando sendTransaction...\n";
    $result = $utmifyService->sendTransaction($transaction, 'created');
    
    echo "Resultado: " . ($result ? 'SUCESSO ✅' : 'FALHA ❌') . "\n";
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    echo "   Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "=== TESTE DIRETO DA API ===\n\n";

// Preparar payload manualmente
$customerData = $transaction->customer_data ?? [];
$metadata = $transaction->metadata ?? [];

// Carregar usuário
$transaction->load('user');

$customer = [
    'name' => $customerData['name'] ?? $transaction->user->name ?? 'Cliente',
    'email' => $customerData['email'] ?? $transaction->user->email ?? 'cliente@example.com',
    'country' => $customerData['country'] ?? 'BR',
];

if (!empty($customerData['phone'])) {
    $customer['phone'] = $customerData['phone'];
}
if (!empty($customerData['document'])) {
    $customer['document'] = $customerData['document'];
}

$product = [
    'id' => $transaction->transaction_id,
    'name' => $metadata['product_name'] ?? $metadata['description'] ?? 'Produto',
    'planId' => $metadata['plan_id'] ?? null,
    'planName' => $metadata['plan_name'] ?? null,
    'quantity' => isset($metadata['quantity']) ? (int)$metadata['quantity'] : 1,
    'priceInCents' => (int)round($transaction->amount * 100),
];

$trackingParams = [
    'src' => $metadata['src'] ?? null,
    'sck' => $metadata['sck'] ?? null,
    'utm_source' => $metadata['utm_source'] ?? null,
    'utm_campaign' => $metadata['utm_campaign'] ?? null,
    'utm_medium' => $metadata['utm_medium'] ?? null,
    'utm_content' => $metadata['utm_content'] ?? null,
    'utm_term' => $metadata['utm_term'] ?? null,
];

$totalPriceInCents = (int)round($transaction->amount * 100);
$gatewayFeeInCents = (int)round(($transaction->fee_amount ?? 0) * 100);
$userCommissionInCents = (int)round(($transaction->net_amount ?? 0) * 100);

if ($userCommissionInCents <= 0) {
    $userCommissionInCents = $totalPriceInCents;
}

$payload = [
    'orderId' => $transaction->transaction_id,
    'platform' => 'playpayments',
    'paymentMethod' => 'pix',
    'status' => 'waiting_payment',
    'createdAt' => $transaction->created_at->utc()->format('Y-m-d H:i:s'),
    'approvedDate' => null,
    'refundedAt' => null,
    'customer' => $customer,
    'products' => [$product],
    'trackingParameters' => $trackingParams,
    'commission' => [
        'totalPriceInCents' => $totalPriceInCents,
        'gatewayFeeInCents' => $gatewayFeeInCents,
        'userCommissionInCents' => $userCommissionInCents,
    ],
];

echo "📦 PAYLOAD:\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

echo "🚀 ENVIANDO PARA API UTMIFY...\n\n";

try {
    $response = Http::timeout(30)
        ->withHeaders([
            'x-api-token' => $integration->api_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
    
    echo "📡 RESPOSTA:\n";
    echo "Status: " . $response->status() . "\n";
    echo "Body: " . $response->body() . "\n\n";
    
    if ($response->successful()) {
        echo "✅ SUCESSO! Transação enviada para UTMify.\n";
    } else {
        echo "❌ ERRO! Status: " . $response->status() . "\n";
        $errorData = json_decode($response->body(), true);
        if ($errorData) {
            echo "Erro: " . json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n";
echo "=== VERIFICAR LOGS ===\n";
echo "Acesse: storage/logs/laravel.log\n";
echo "Procure por: 'UTMify:' ou 'TransactionObserver:'\n";

