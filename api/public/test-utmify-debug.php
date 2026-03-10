<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG UTMIFY ===\n\n";

// Buscar a última transação PIX
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
echo "  Created: {$transaction->created_at}\n";
echo "\n";

// Verificar integrações do usuário
echo "🔍 INTEGRAÇÕES UTMIFY DO USUÁRIO {$transaction->user_id}:\n";
$integrations = UtmifyIntegration::where('user_id', $transaction->user_id)
    ->get();

if ($integrations->isEmpty()) {
    echo "  ❌ NENHUMA INTEGRAÇÃO ENCONTRADA PARA ESTE USUÁRIO!\n";
    echo "  💡 O usuário precisa criar uma integração UTMify na conta dele\n";
    echo "  💡 Acesse: /integracoes/utmfy\n";
} else {
    foreach ($integrations as $integration) {
        echo "  ✅ Integração ID: {$integration->id}\n";
        echo "     Nome: {$integration->name}\n";
        echo "     Ativo: " . ($integration->is_active ? 'SIM ✅' : 'NÃO ❌') . "\n";
        echo "     Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM ✅' : 'NÃO ❌') . "\n";
        echo "     Trigger Payment: " . ($integration->trigger_on_payment ? 'SIM ✅' : 'NÃO ❌') . "\n";
        echo "     API Token: " . substr($integration->api_token, 0, 20) . "...\n";
        echo "\n";
    }
}

// Verificar integrações ativas
echo "🔍 INTEGRAÇÕES ATIVAS DO USUÁRIO {$transaction->user_id}:\n";
$activeIntegrations = UtmifyIntegration::where('user_id', $transaction->user_id)
    ->where('is_active', true)
    ->get();

if ($activeIntegrations->isEmpty()) {
    echo "  ❌ NENHUMA INTEGRAÇÃO ATIVA!\n";
} else {
    echo "  ✅ {$activeIntegrations->count()} integração(ões) ativa(s)\n";
    foreach ($activeIntegrations as $integration) {
        echo "     - {$integration->name} (ID: {$integration->id})\n";
        if ($transaction->status === 'pending' && !$integration->trigger_on_creation) {
            echo "       ⚠️  trigger_on_creation está DESABILITADO\n";
        }
    }
}

echo "\n";
echo "=== TESTANDO ENVIO ===\n\n";

// Testar envio
try {
    $utmifyService = new \App\Services\UtmifyService();
    echo "Enviando transação para UTMify...\n";
    $result = $utmifyService->sendTransaction($transaction, 'created');
    
    if ($result) {
        echo "✅ ENVIO REALIZADO COM SUCESSO!\n";
    } else {
        echo "❌ FALHA NO ENVIO\n";
        echo "   Verifique os logs para mais detalhes\n";
    }
} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n";
echo "=== VERIFICAR LOGS ===\n";
echo "Acesse: storage/logs/laravel.log\n";
echo "Procure por: 'UTMify:' ou 'TransactionObserver:'\n";

