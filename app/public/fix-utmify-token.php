<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Http;

echo "=== VERIFICAR E CORRIGIR TOKEN UTMIFY ===\n\n";

// Buscar integração do usuário 2
$integration = UtmifyIntegration::where('user_id', 2)
    ->where('is_active', true)
    ->first();

if (!$integration) {
    echo "❌ Nenhuma integração ativa encontrada para o usuário 2\n";
    exit(1);
}

echo "📋 INTEGRAÇÃO ENCONTRADA:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "  User ID: {$integration->user_id}\n";
echo "  Token (original): " . $integration->api_token . "\n";
echo "  Token Length: " . strlen($integration->api_token) . "\n";
echo "\n";

// Limpar token (remover espaços, quebras de linha, etc.)
$cleanedToken = trim($integration->api_token);
$cleanedToken = preg_replace('/\s+/', '', $cleanedToken);

echo "🧹 TOKEN LIMPO:\n";
echo "  Token: {$cleanedToken}\n";
echo "  Length: " . strlen($cleanedToken) . "\n";
echo "\n";

// Testar token com uma requisição simples
echo "🧪 TESTANDO TOKEN COM API UTMIFY...\n\n";

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
        'country' => 'BR',
    ],
    'products' => [
        [
            'id' => 'TEST_PRODUCT',
            'name' => 'Produto Teste',
            'planId' => null,
            'planName' => null,
            'quantity' => 1,
            'priceInCents' => 100,
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
        'totalPriceInCents' => 100,
        'gatewayFeeInCents' => 0,
        'userCommissionInCents' => 100,
    ],
];

try {
    // Testar com token original
    echo "Testando com token ORIGINAL...\n";
    $response1 = Http::timeout(10)
        ->withHeaders([
            'x-api-token' => $integration->api_token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $testPayload);
    
    echo "  Status: " . $response1->status() . "\n";
    echo "  Response: " . $response1->body() . "\n\n";
    
    if ($response1->successful()) {
        echo "✅ TOKEN ORIGINAL FUNCIONA!\n\n";
    } else {
        echo "❌ TOKEN ORIGINAL NÃO FUNCIONA\n\n";
        
        // Testar com token limpo
        if ($cleanedToken !== $integration->api_token) {
            echo "Testando com token LIMPO...\n";
            $response2 = Http::timeout(10)
                ->withHeaders([
                    'x-api-token' => $cleanedToken,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api.utmify.com.br/api-credentials/orders', $testPayload);
            
            echo "  Status: " . $response2->status() . "\n";
            echo "  Response: " . $response2->body() . "\n\n";
            
            if ($response2->successful()) {
                echo "✅ TOKEN LIMPO FUNCIONA! Atualizando no banco...\n";
                $integration->api_token = $cleanedToken;
                $integration->save();
                echo "✅ Token atualizado no banco de dados!\n\n";
            } else {
                echo "❌ TOKEN LIMPO TAMBÉM NÃO FUNCIONA\n\n";
            }
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n";
echo "=== INSTRUÇÕES ===\n";
echo "1. Acesse https://utmify.com.br\n";
echo "2. Vá em: Integrações > Webhooks > Credenciais de API\n";
echo "3. Verifique se a credencial está ativa\n";
echo "4. Se necessário, crie uma nova credencial\n";
echo "5. Copie o token e atualize na integração\n";
echo "\n";

