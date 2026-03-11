<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DIRETO UTMIFY ===\n\n";

// Buscar transação específica
$transactionId = 'PXB_6913CEB0ADBF9_1762905776';
$transaction = Transaction::where('transaction_id', $transactionId)->first();

if (!$transaction) {
    echo "❌ Transação não encontrada: {$transactionId}\n";
    exit(1);
}

echo "📊 TRANSAÇÃO:\n";
echo "  ID: {$transaction->id}\n";
echo "  Transaction ID: {$transaction->transaction_id}\n";
echo "  User ID: {$transaction->user_id}\n";
echo "  Payment Method: {$transaction->payment_method}\n";
echo "  Status: {$transaction->status}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "  Created: {$transaction->created_at}\n";
echo "\n";

// Verificar integrações
echo "🔍 INTEGRAÇÕES UTMIFY:\n";
$integrations = UtmifyIntegration::where('user_id', $transaction->user_id)
    ->where('is_active', true)
    ->get();

if ($integrations->isEmpty()) {
    echo "  ❌ NENHUMA INTEGRAÇÃO ATIVA!\n";
    exit(1);
}

foreach ($integrations as $integration) {
    echo "  ✅ Integração ID: {$integration->id}\n";
    echo "     Nome: {$integration->name}\n";
    echo "     User ID: {$integration->user_id}\n";
    echo "     Ativo: " . ($integration->is_active ? 'SIM ✅' : 'NÃO ❌') . "\n";
    echo "     Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM ✅' : 'NÃO ❌') . "\n";
    echo "     Trigger Payment: " . ($integration->trigger_on_payment ? 'SIM ✅' : 'NÃO ❌') . "\n";
    echo "     API Token: " . substr($integration->api_token, 0, 20) . "...\n";
    echo "\n";
}

// Testar envio
echo "🚀 TESTANDO ENVIO PARA UTMIFY...\n\n";

try {
    $utmifyService = new UtmifyService();
    
    echo "Verificando filtros:\n";
    echo "  - Payment Method: {$transaction->payment_method} (é PIX? " . (strtolower($transaction->payment_method) === 'pix' ? 'SIM ✅' : 'NÃO ❌') . ")\n";
    echo "  - Status: {$transaction->status} (permitido? " . (in_array(strtolower($transaction->status), ['pending', 'paid', 'refunded', 'partially_refunded']) ? 'SIM ✅' : 'NÃO ❌') . ")\n";
    echo "  - Event: created (permitido? SIM ✅)\n";
    echo "\n";
    
    echo "Enviando transação para UTMify...\n";
    $result = $utmifyService->sendTransaction($transaction, 'created');
    
    if ($result) {
        echo "✅ ENVIO REALIZADO COM SUCESSO!\n";
    } else {
        echo "❌ FALHA NO ENVIO\n";
        echo "   O método retornou FALSE\n";
        echo "   Verifique os logs para mais detalhes\n";
    }
} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
    echo "   Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n";
echo "=== VERIFICAR LOGS ===\n";
echo "Acesse: storage/logs/laravel.log\n";
echo "Procure por: 'UTMify:' ou 'TransactionObserver:'\n";

