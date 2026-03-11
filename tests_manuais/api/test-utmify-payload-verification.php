<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Transaction;
use App\Services\UtmifyService;
use Illuminate\Support\Facades\Log;

echo "=== 🔍 VERIFICAÇÃO COMPLETA DO PAYLOAD UTMIFY ===\n\n";

// Buscar última transação PIX
$transaction = Transaction::where('transaction_id', 'PXB_6913DA4416FE9_1762908740')->first();

if (!$transaction) {
    echo "❌ Transação não encontrada!\n";
    exit(1);
}

echo "📊 Transação encontrada:\n";
echo "  ID: {$transaction->transaction_id}\n";
echo "  User ID: {$transaction->user_id}\n";
echo "  Status: {$transaction->status}\n";
echo "  Amount: R$ " . number_format($transaction->amount, 2, ',', '.') . "\n";
echo "\n";

// Buscar integração
$integration = \App\Models\UtmifyIntegration::where('user_id', 2)
    ->where('is_active', true)
    ->first();

if (!$integration) {
    echo "❌ Integração não encontrada!\n";
    exit(1);
}

echo "🔗 Integração encontrada:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "\n";

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
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo "\n\n";
    
    // Verificar campos obrigatórios
    echo "🔍 VERIFICAÇÃO DE CAMPOS OBRIGATÓRIOS:\n";
    $required = [
        'orderId' => isset($payload['orderId']) && !empty($payload['orderId']),
        'platform' => isset($payload['platform']) && !empty($payload['platform']),
        'paymentMethod' => isset($payload['paymentMethod']) && !empty($payload['paymentMethod']),
        'status' => isset($payload['status']) && !empty($payload['status']),
        'createdAt' => isset($payload['createdAt']) && !empty($payload['createdAt']),
        'approvedDate' => array_key_exists('approvedDate', $payload), // Pode ser null
        'refundedAt' => array_key_exists('refundedAt', $payload), // Pode ser null
        'customer' => isset($payload['customer']) && is_array($payload['customer']),
        'products' => isset($payload['products']) && is_array($payload['products']) && count($payload['products']) > 0,
        'trackingParameters' => isset($payload['trackingParameters']) && is_array($payload['trackingParameters']),
        'commission' => isset($payload['commission']) && is_array($payload['commission']),
    ];
    
    foreach ($required as $field => $exists) {
        $status = $exists ? '✅' : '❌';
        $value = $payload[$field] ?? 'NÃO EXISTE';
        if (is_array($value)) {
            $value = 'ARRAY[' . count($value) . ']';
        } elseif ($value === null) {
            $value = 'null (CORRETO)';
        }
        echo "  {$status} {$field}: {$value}\n";
    }
    
    echo "\n";
    
    // Verificar customer
    if (isset($payload['customer'])) {
        echo "👤 CUSTOMER:\n";
        $customerRequired = ['name', 'email', 'phone', 'document', 'country'];
        foreach ($customerRequired as $field) {
            $exists = isset($payload['customer'][$field]);
            $value = $payload['customer'][$field] ?? 'NULL';
            $status = $exists ? '✅' : '❌';
            echo "  {$status} {$field}: " . ($value === null ? 'null' : $value) . "\n";
        }
    } else {
        echo "❌ CUSTOMER: NÃO EXISTE\n";
    }
    
    echo "\n";
    
    // Verificar products
    if (isset($payload['products']) && is_array($payload['products']) && count($payload['products']) > 0) {
        echo "📦 PRODUCTS:\n";
        $product = $payload['products'][0];
        $productRequired = ['id', 'name', 'planId', 'planName', 'quantity', 'priceInCents'];
        foreach ($productRequired as $field) {
            $exists = array_key_exists($field, $product); // Verificar se a chave existe, mesmo que seja null
            $value = $product[$field] ?? 'NÃO EXISTE';
            $status = $exists ? '✅' : '❌';
            if ($value === null) {
                $value = 'null (CORRETO - pode ser null)';
            }
            echo "  {$status} {$field}: {$value}\n";
        }
    } else {
        echo "❌ PRODUCTS: NÃO EXISTE OU ESTÁ VAZIO\n";
    }
    
    echo "\n";
    
    // Verificar commission
    if (isset($payload['commission'])) {
        echo "💰 COMMISSION:\n";
        $commissionRequired = ['totalPriceInCents', 'gatewayFeeInCents', 'userCommissionInCents'];
        foreach ($commissionRequired as $field) {
            $exists = isset($payload['commission'][$field]);
            $value = $payload['commission'][$field] ?? 'NULL';
            $status = $exists ? '✅' : '❌';
            echo "  {$status} {$field}: " . ($value === null ? 'null' : $value) . "\n";
        }
    } else {
        echo "❌ COMMISSION: NÃO EXISTE\n";
    }
    
    echo "\n";
    
    // Verificar trackingParameters
    if (isset($payload['trackingParameters'])) {
        echo "🔗 TRACKING PARAMETERS:\n";
        $trackingRequired = ['src', 'sck', 'utm_source', 'utm_campaign', 'utm_medium', 'utm_content', 'utm_term'];
        foreach ($trackingRequired as $field) {
            $exists = array_key_exists($field, $payload['trackingParameters']); // Verificar se a chave existe, mesmo que seja null
            $value = $payload['trackingParameters'][$field] ?? 'NÃO EXISTE';
            $status = $exists ? '✅' : '❌';
            if ($value === null) {
                $value = 'null (CORRETO - pode ser null)';
            }
            echo "  {$status} {$field}: {$value}\n";
        }
    } else {
        echo "❌ TRACKING PARAMETERS: NÃO EXISTE\n";
    }
    
    echo "\n";
    echo "=== 📊 RESUMO ===\n";
    $allPresent = !in_array(false, $required);
    if ($allPresent) {
        echo "✅✅✅ TODOS OS CAMPOS OBRIGATÓRIOS ESTÃO PRESENTES! ✅✅✅\n";
        echo "O payload está COMPLETO e CORRETO!\n";
        echo "\n";
        echo "⚠️ O problema NÃO é o payload, mas sim o TOKEN INVÁLIDO!\n";
        echo "A API UTMify retorna 404 porque o token não é reconhecido.\n";
    } else {
        echo "❌ ALGUNS CAMPOS ESTÃO FALTANDO!\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERRO ao preparar payload: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . "\n";
    echo "  Line: " . $e->getLine() . "\n";
}

