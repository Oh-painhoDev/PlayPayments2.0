<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;

echo "=== TESTE: Nome da Plataforma UTMify ===\n\n";

// Buscar última transação PIX
$transaction = Transaction::where('payment_method', 'pix')
    ->where('user_id', 2)
    ->orderBy('created_at', 'desc')
    ->first();

if (!$transaction) {
    echo "❌ Nenhuma transação PIX encontrada para o usuário 2\n";
    exit(1);
}

echo "📊 Transação encontrada:\n";
echo "  ID: {$transaction->transaction_id}\n";
echo "  User ID: {$transaction->user_id}\n";
echo "  Status: {$transaction->status}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "\n";

// Criar instância do serviço
$utmifyService = new UtmifyService();

// Usar reflection para testar o método preparePayload
$reflection = new ReflectionClass($utmifyService);
$method = $reflection->getMethod('preparePayload');
$method->setAccessible(true);

// Buscar integração
$integration = \App\Models\UtmifyIntegration::where('user_id', 2)
    ->where('is_active', true)
    ->first();

if (!$integration) {
    echo "❌ Nenhuma integração UTMify ativa encontrada\n";
    exit(1);
}

echo "🔗 Integração encontrada:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "  Platform Name (no banco): " . ($integration->platform_name ?? 'NULL') . "\n";
echo "\n";

// Preparar payload
try {
    $payload = $method->invoke($utmifyService, $transaction, $integration, 'created');
    
    echo "✅ Payload preparado:\n";
    echo "  Platform: " . ($payload['platform'] ?? 'NÃO DEFINIDO') . "\n";
    echo "\n";
    
    if (($payload['platform'] ?? '') === 'playpayments') {
        echo "✅ SUCESSO: Nome da plataforma está correto (playpayments)\n";
    } else {
        echo "❌ ERRO: Nome da plataforma está incorreto. Esperado: playpayments, Recebido: " . ($payload['platform'] ?? 'NÃO DEFINIDO') . "\n";
    }
} catch (\Exception $e) {
    echo "❌ ERRO ao preparar payload: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

