<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;

echo "=== TESTE DE ENVIO UTMIFY ===\n\n";

// Buscar a última transação PIX do usuário 2
$transaction = Transaction::where('user_id', 2)
    ->where('payment_method', 'pix')
    ->where('status', 'pending')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$transaction) {
    echo "❌ Nenhuma transação PIX pendente encontrada para o usuário 2\n";
    exit(1);
}

echo "Transação encontrada:\n";
echo "  ID: {$transaction->id}\n";
echo "  Transaction ID: {$transaction->transaction_id}\n";
echo "  User ID: {$transaction->user_id}\n";
echo "  Payment Method: {$transaction->payment_method}\n";
echo "  Status: {$transaction->status}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "  Created: {$transaction->created_at}\n";
echo "\n";

// Testar envio
echo "Enviando para UTMify...\n";
$utmifyService = new UtmifyService();
$result = $utmifyService->sendTransaction($transaction, 'created');

if ($result) {
    echo "✅ Envio realizado com sucesso!\n";
} else {
    echo "❌ Falha no envio. Verifique os logs.\n";
}

echo "\nVerifique os logs em: storage/logs/laravel.log\n";
echo "Procure por: 'UTMify:'\n";

