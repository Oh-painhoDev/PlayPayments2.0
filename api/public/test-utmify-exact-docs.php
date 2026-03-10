<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Http;

echo "=== TESTE UTMIFY - EXATAMENTE COMO NA DOCUMENTAÇÃO ===\n\n";

// Buscar última transação
$transaction = Transaction::where('transaction_id', 'PXB_6913D561E5EEC_1762907489')->first();

if (!$transaction) {
    echo "❌ Transação não encontrada\n";
    exit(1);
}

// Buscar integração
$integration = UtmifyIntegration::find(2);

if (!$integration) {
    echo "❌ Integração não encontrada\n";
    exit(1);
}

echo "📊 TRANSAÇÃO:\n";
echo "  ID: {$transaction->transaction_id}\n";
echo "  User ID: {$transaction->user_id}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "\n";

// Carregar dados
$transaction->load('user');
$customerData = $transaction->customer_data ?? [];
$metadata = $transaction->metadata ?? [];

// Preparar payload EXATAMENTE como na documentação (incluindo ip se disponível)
$customer = [
    'name' => $customerData['name'] ?? $transaction->user->name ?? 'Cliente',
    'email' => $customerData['email'] ?? $transaction->user->email ?? 'cliente@example.com',
    'phone' => $customerData['phone'] ?? null,
    'document' => $customerData['document'] ?? null,
    'country' => $customerData['country'] ?? 'BR',
];

// Adicionar ip apenas se estiver disponível (documentação mostra ip presente)
if (!empty($customerData['ip'])) {
    $customer['ip'] = $customerData['ip'];
}

// Payload EXATAMENTE como na documentação
$payload = [
    'orderId' => $transaction->transaction_id,
    'platform' => 'playpayments',
    'paymentMethod' => 'pix',
    'status' => 'waiting_payment',
    'createdAt' => $transaction->created_at->utc()->format('Y-m-d H:i:s'),
    'approvedDate' => null,
    'refundedAt' => null,
    'customer' => $customer,
    'products' => [
        [
            'id' => $transaction->transaction_id,
            'name' => $metadata['product_name'] ?? 'Produto',
            'planId' => null,
            'planName' => null,
            'quantity' => 1,
            'priceInCents' => (int)round($transaction->amount * 100),
        ],
    ],
    'trackingParameters' => [
        'src' => null,
        'sck' => null,
        'utm_source' => null,
        'utm_campaign' => null,
        'utm_medium' => null,
        'utm_content' => null,
        'utm_term' => null,
    ],
    'commission' => [
        'totalPriceInCents' => (int)round($transaction->amount * 100),
        'gatewayFeeInCents' => (int)round(($transaction->fee_amount ?? 0) * 100),
        'userCommissionInCents' => (int)round(($transaction->net_amount ?? 0) * 100),
    ],
];

// Ajustar userCommissionInCents se necessário
if ($payload['commission']['userCommissionInCents'] <= 0) {
    $payload['commission']['userCommissionInCents'] = $payload['commission']['totalPriceInCents'];
}

echo "📦 PAYLOAD (exatamente como na documentação):\n";
echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Limpar token
$apiToken = trim($integration->api_token);
$apiToken = preg_replace('/\s+/', '', $apiToken);

echo "🔑 TOKEN:\n";
echo "  Token: {$apiToken}\n";
echo "  Length: " . strlen($apiToken) . "\n";
echo "\n";

echo "🚀 ENVIANDO PARA API UTMIFY...\n\n";

try {
    $response = Http::timeout(30)
        ->withHeaders([
            'x-api-token' => $apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $payload);
    
    $status = $response->status();
    $body = $response->body();
    
    echo "📡 RESPOSTA:\n";
    echo "Status: {$status}\n";
    echo "Body: {$body}\n\n";
    
    if ($response->successful()) {
        echo "✅ SUCESSO! Transação enviada para UTMify.\n";
    } else {
        $errorData = json_decode($body, true);
        if ($errorData) {
            echo "❌ ERRO:\n";
            echo json_encode($errorData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
            
            if ($errorData['message'] === 'API_CREDENTIAL_NOT_FOUND') {
                echo "🔴 PROBLEMA: Token inválido!\n\n";
                echo "SOLUÇÃO:\n";
                echo "1. Acesse https://utmify.com.br\n";
                echo "2. Vá em: Integrações > Webhooks > Credenciais de API\n";
                echo "3. Verifique se a credencial está ativa\n";
                echo "4. Se necessário, crie uma nova credencial\n";
                echo "5. Copie o token EXATO\n";
                echo "6. Atualize: UPDATE utmify_integrations SET api_token = 'NOVO_TOKEN' WHERE id = 2;\n";
            } elseif (isset($errorData['data'])) {
                echo "🔴 PROBLEMA: Erro de validação do payload\n";
                echo "Campos com erro:\n";
                foreach ($errorData['data'] as $field => $message) {
                    echo "  - {$field}: {$message}\n";
                }
            }
        } else {
            echo "❌ ERRO DESCONHECIDO: {$body}\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n";

