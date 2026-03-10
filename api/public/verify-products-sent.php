<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;

echo "=== 🔍 VERIFICAÇÃO: PRODUTO SENDO ENVIADO PARA UTMIFY ===\n\n";

// Buscar última transação PIX
$transaction = Transaction::where('payment_method', 'pix')
    ->where('user_id', 2)
    ->orderBy('created_at', 'desc')
    ->first();

if (!$transaction) {
    echo "❌ Nenhuma transação PIX encontrada\n";
    exit(1);
}

echo "📊 Transação encontrada:\n";
echo "  ID: {$transaction->transaction_id}\n";
echo "  Description: " . ($transaction->description ?? 'NULL') . "\n";
echo "  Metadata: " . json_encode($transaction->metadata ?? []) . "\n";
echo "\n";

// Buscar integração
$integration = \App\Models\UtmifyIntegration::where('user_id', 2)
    ->where('is_active', true)
    ->first();

if (!$integration) {
    echo "❌ Integração não encontrada\n";
    exit(1);
}

// Criar instância do serviço
$utmifyService = new UtmifyService();

// Usar reflection para testar o método preparePayload
$reflection = new ReflectionClass($utmifyService);
$method = $reflection->getMethod('preparePayload');
$method->setAccessible(true);

// Preparar payload
try {
    $payload = $method->invoke($utmifyService, $transaction, $integration, 'created');
    
    echo "✅ PAYLOAD PREPARADO:\n";
    echo "\n";
    
    // Verificar especificamente o campo products
    if (isset($payload['products']) && is_array($payload['products']) && count($payload['products']) > 0) {
        echo "✅✅✅ PRODUTO ESTÁ SENDO ENVIADO! ✅✅✅\n";
        echo "\n";
        echo "📦 DETALHES DO PRODUTO:\n";
        $product = $payload['products'][0];
        echo "  ID: " . ($product['id'] ?? 'NÃO DEFINIDO') . "\n";
        echo "  Nome: " . ($product['name'] ?? 'NÃO DEFINIDO') . "\n";
        echo "  Plan ID: " . ($product['planId'] ?? 'null') . "\n";
        echo "  Plan Name: " . ($product['planName'] ?? 'null') . "\n";
        echo "  Quantidade: " . ($product['quantity'] ?? 'NÃO DEFINIDO') . "\n";
        echo "  Preço (centavos): " . ($product['priceInCents'] ?? 'NÃO DEFINIDO') . "\n";
        echo "\n";
        
        echo "📋 JSON COMPLETO DO PRODUTO:\n";
        echo json_encode($product, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo "\n\n";
        
        echo "📋 JSON COMPLETO DO PAYLOAD (products destacado):\n";
        $payloadCopy = $payload;
        $payloadCopy['products'] = ['[PRODUTO ACIMA]'];
        echo json_encode($payloadCopy, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo "\n";
    } else {
        echo "❌❌❌ ERRO: PRODUTO NÃO ESTÁ SENDO ENVIADO! ❌❌❌\n";
        echo "  products existe: " . (isset($payload['products']) ? 'SIM' : 'NÃO') . "\n";
        echo "  products é array: " . (is_array($payload['products'] ?? null) ? 'SIM' : 'NÃO') . "\n";
        echo "  products count: " . (count($payload['products'] ?? [])) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERRO ao preparar payload: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

