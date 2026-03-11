<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Http;

echo "=== TESTE UTMIFY COM TOKEN DO BANCO ===\n\n";

// Buscar integração
$integration = UtmifyIntegration::find(2);

if (!$integration) {
    echo "❌ Integração não encontrada\n";
    exit(1);
}

echo "📋 INTEGRAÇÃO:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "  User ID: {$integration->user_id}\n";
echo "  Token: {$integration->api_token}\n";
echo "  Token Length: " . strlen($integration->api_token) . "\n";
echo "  Ativo: " . ($integration->is_active ? 'SIM' : 'NÃO') . "\n";
echo "  Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM' : 'NÃO') . "\n";
echo "  Trigger Payment: " . ($integration->trigger_on_payment ? 'SIM' : 'NÃO') . "\n";
echo "\n";

// Buscar última transação PIX
$transaction = Transaction::where('payment_method', 'pix')
    ->where('user_id', 2)
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
echo "  Status: {$transaction->status}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "\n";

// Carregar dados do cliente
$transaction->load('user');
$customerData = $transaction->customer_data ?? [];
$metadata = $transaction->metadata ?? [];

// Preparar payload EXATO como o código faz
$customerName = $customerData['name'] ?? $transaction->user->name ?? 'Cliente';
$customerEmail = $customerData['email'] ?? $transaction->user->email ?? 'cliente@example.com';

$customer = [
    'name' => $customerName,
    'email' => $customerEmail,
    'country' => $customerData['country'] ?? 'BR',
    'phone' => $customerData['phone'] ?? null,
    'document' => $customerData['document'] ?? null,
];

if (!empty($customerData['ip'])) {
    $customer['ip'] = $customerData['ip'];
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

// Limpar token
$apiToken = trim($integration->api_token);
$apiToken = preg_replace('/\s+/', '', $apiToken);

echo "🔑 TOKEN (limpo):\n";
echo "  Token: {$apiToken}\n";
echo "  Length: " . strlen($apiToken) . "\n";
echo "\n";

// Testar com diferentes formatos de header
echo "🧪 TESTANDO DIFERENTES FORMATOS DE HEADER...\n\n";

// Teste 1: x-api-token (minúsculas)
echo "Teste 1: x-api-token (minúsculas)\n";
try {
    $response1 = Http::timeout(10)
        ->withHeaders([
            'x-api-token' => $apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
    
    echo "  Status: " . $response1->status() . "\n";
    echo "  Response: " . substr($response1->body(), 0, 200) . "\n";
    
    if ($response1->successful()) {
        echo "  ✅ SUCESSO!\n\n";
        exit(0);
    } else {
        $errorData = json_decode($response1->body(), true);
        if ($errorData && isset($errorData['message'])) {
            echo "  ❌ Erro: " . $errorData['message'] . "\n\n";
        }
    }
} catch (\Exception $e) {
    echo "  ❌ Exceção: " . $e->getMessage() . "\n\n";
}

// Teste 2: X-Api-Token (PascalCase)
echo "Teste 2: X-Api-Token (PascalCase)\n";
try {
    $response2 = Http::timeout(10)
        ->withHeaders([
            'X-Api-Token' => $apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
    
    echo "  Status: " . $response2->status() . "\n";
    echo "  Response: " . substr($response2->body(), 0, 200) . "\n";
    
    if ($response2->successful()) {
        echo "  ✅ SUCESSO!\n\n";
        exit(0);
    }
} catch (\Exception $e) {
    echo "  ❌ Exceção: " . $e->getMessage() . "\n\n";
}

// Teste 3: X-API-TOKEN (maiúsculas)
echo "Teste 3: X-API-TOKEN (maiúsculas)\n";
try {
    $response3 = Http::timeout(10)
        ->withHeaders([
            'X-API-TOKEN' => $apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
    
    echo "  Status: " . $response3->status() . "\n";
    echo "  Response: " . substr($response3->body(), 0, 200) . "\n";
    
    if ($response3->successful()) {
        echo "  ✅ SUCESSO!\n\n";
        exit(0);
    }
} catch (\Exception $e) {
    echo "  ❌ Exceção: " . $e->getMessage() . "\n\n";
}

echo "\n";
echo "=== DIAGNÓSTICO ===\n";
echo "Se todos os testes falharam com API_CREDENTIAL_NOT_FOUND:\n";
echo "1. O token pode estar inválido ou expirado na UTMify\n";
echo "2. A credencial pode ter sido desativada na UTMify\n";
echo "3. O token pode estar incorreto no banco de dados\n";
echo "\n";
echo "SOLUÇÃO:\n";
echo "1. Acesse https://utmify.com.br\n";
echo "2. Vá em: Integrações > Webhooks > Credenciais de API\n";
echo "3. Verifique se a credencial está ativa\n";
echo "4. Se necessário, crie uma nova credencial\n";
echo "5. Copie o token EXATO (sem espaços)\n";
echo "6. Atualize no banco: UPDATE utmify_integrations SET api_token = 'NOVO_TOKEN' WHERE id = 2;\n";

