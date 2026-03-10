<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== INTEGRAÇÕES UTMIFY ===\n\n";

$integrations = \App\Models\UtmifyIntegration::all();

if ($integrations->isEmpty()) {
    echo "❌ NENHUMA INTEGRAÇÃO UTMIFY ENCONTRADA!\n";
    echo "Crie uma integração no admin: /admin/white-label/utmify\n\n";
} else {
    foreach ($integrations as $integration) {
        echo "ID: {$integration->id}\n";
        echo "Nome: {$integration->name}\n";
        echo "User ID: " . ($integration->user_id ?? 'GLOBAL') . "\n";
        echo "Ativo: " . ($integration->is_active ? 'SIM ✅' : 'NÃO ❌') . "\n";
        echo "Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM ✅' : 'NÃO ❌') . "\n";
        echo "Trigger Payment: " . ($integration->trigger_on_payment ? 'SIM ✅' : 'NÃO ❌') . "\n";
        echo "API Token: " . substr($integration->api_token, 0, 20) . "...\n";
        echo "---\n\n";
    }
}

echo "\n=== ÚLTIMAS TRANSAÇÕES ===\n\n";

$transactions = \App\Models\Transaction::orderBy('created_at', 'desc')->limit(5)->get();

foreach ($transactions as $transaction) {
    echo "Transaction ID: {$transaction->transaction_id}\n";
    echo "User ID: {$transaction->user_id}\n";
    echo "Status: {$transaction->status}\n";
    echo "Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
    echo "Created: {$transaction->created_at}\n";
    echo "---\n\n";
}

