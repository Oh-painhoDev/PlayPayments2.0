<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

echo "=== 🔧 ATUALIZAR TOKEN UTMIFY ===\n\n";

// Verificar se foi passado um token
$newToken = $argv[1] ?? null;

if (!$newToken) {
    echo "❌ ERRO: Token não fornecido!\n";
    echo "\n";
    echo "Uso: php public/update-utmify-token-simple.php \"SEU_TOKEN_AQUI\"\n";
    echo "\n";
    echo "Exemplo:\n";
    echo "  php public/update-utmify-token-simple.php \"KVRxalfMiBfm8Rm1nP5YxfwYzArNsA0VLeWC\"\n";
    exit(1);
}

// Limpar token
$newToken = trim($newToken);
$newToken = preg_replace('/\s+/', '', $newToken);

echo "📋 Token recebido: " . substr($newToken, 0, 20) . "... (tamanho: " . strlen($newToken) . ")\n";
echo "\n";

// Buscar integração
$integration = DB::table('utmify_integrations')->where('id', 2)->first();

if (!$integration) {
    echo "❌ Integração ID 2 não encontrada!\n";
    exit(1);
}

echo "🔍 Integração encontrada:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "  Token atual: " . substr($integration->api_token, 0, 20) . "...\n";
echo "\n";

// Testar token antes de atualizar
echo "🧪 Testando token na API UTMify...\n";

$testPayload = [
    'orderId' => 'TEST_' . time(),
    'platform' => 'playpayments',
    'paymentMethod' => 'pix',
    'status' => 'waiting_payment',
    'createdAt' => date('Y-m-d H:i:s'),
    'approvedDate' => null,
    'refundedAt' => null,
    'customer' => [
        'name' => 'Teste',
        'email' => 'teste@teste.com',
        'phone' => null,
        'document' => null,
        'country' => 'BR',
    ],
    'products' => [
        [
            'id' => 'TEST_PRODUCT',
            'name' => 'Produto de Teste',
            'planId' => null,
            'planName' => null,
            'quantity' => 1,
            'priceInCents' => 1000,
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
        'totalPriceInCents' => 1000,
        'gatewayFeeInCents' => 230,
        'userCommissionInCents' => 770,
    ],
];

try {
    $response = Http::timeout(10)
        ->withHeaders([
            'x-api-token' => $newToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $testPayload);
    
    $statusCode = $response->status();
    $responseBody = $response->body();
    $responseJson = $response->json();
    
    echo "  Status Code: {$statusCode}\n";
    
    if ($statusCode === 200 || $statusCode === 201) {
        echo "  ✅✅✅ TOKEN VÁLIDO! ✅✅✅\n";
        echo "\n";
        
        // Atualizar no banco
        echo "💾 Atualizando token no banco de dados...\n";
        DB::table('utmify_integrations')
            ->where('id', 2)
            ->update(['api_token' => $newToken]);
        
        echo "  ✅ Token atualizado com sucesso!\n";
        echo "\n";
        echo "🎉 PRONTO! Agora você pode testar criando um novo PIX.\n";
        echo "   Acesse: http://localhost:8000/test-pix-api.php\n";
        
    } elseif ($statusCode === 404) {
        $errorMessage = $responseJson['message'] ?? 'Unknown error';
        if ($errorMessage === 'API_CREDENTIAL_NOT_FOUND') {
            echo "  ❌❌❌ TOKEN INVÁLIDO! ❌❌❌\n";
            echo "  A API UTMify retornou: API_CREDENTIAL_NOT_FOUND (404)\n";
            echo "\n";
            echo "  ⚠️ Este token não é válido na plataforma UTMify.\n";
            echo "  Por favor, verifique:\n";
            echo "  1. O token foi copiado corretamente?\n";
            echo "  2. O token está ativo na plataforma UTMify?\n";
            echo "  3. Você está usando a conta correta da UTMify?\n";
            echo "\n";
            echo "  Não atualizei o token no banco porque ele é inválido.\n";
        } else {
            echo "  ❌ Erro desconhecido: {$errorMessage}\n";
        }
    } else {
        echo "  ⚠️ Status inesperado: {$statusCode}\n";
        echo "  Response: {$responseBody}\n";
        echo "\n";
        echo "  Deseja atualizar mesmo assim? (s/n): ";
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        fclose($handle);
        
        if (trim(strtolower($line)) === 's') {
            DB::table('utmify_integrations')
                ->where('id', 2)
                ->update(['api_token' => $newToken]);
            echo "  ✅ Token atualizado (mesmo com status inesperado).\n";
        } else {
            echo "  ❌ Token não atualizado.\n";
        }
    }
} catch (\Exception $e) {
    echo "  ❌ ERRO ao testar token: " . $e->getMessage() . "\n";
    echo "\n";
    echo "  Deseja atualizar mesmo assim? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) === 's') {
        DB::table('utmify_integrations')
            ->where('id', 2)
            ->update(['api_token' => $newToken]);
        echo "  ✅ Token atualizado (mesmo com erro no teste).\n";
    } else {
        echo "  ❌ Token não atualizado.\n";
    }
}

echo "\n";

