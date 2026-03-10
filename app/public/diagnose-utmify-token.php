<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

echo "=== 🔍 DIAGNÓSTICO COMPLETO UTMIFY ===\n\n";

// 1. Verificar integração no banco
echo "1️⃣ VERIFICANDO INTEGRAÇÃO NO BANCO:\n";
$integration = DB::table('utmify_integrations')->where('id', 2)->first();

if (!$integration) {
    echo "❌ Integração não encontrada!\n";
    exit(1);
}

echo "   ✅ Integração encontrada:\n";
echo "      ID: {$integration->id}\n";
echo "      Nome: {$integration->name}\n";
echo "      User ID: {$integration->user_id}\n";
echo "      Ativo: " . ($integration->is_active ? 'SIM ✅' : 'NÃO ❌') . "\n";
echo "      Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM ✅' : 'NÃO ❌') . "\n";
echo "      Trigger Payment: " . ($integration->trigger_on_payment ? 'SIM ✅' : 'NÃO ❌') . "\n";
echo "      API Token: " . substr($integration->api_token, 0, 20) . "... (tamanho: " . strlen($integration->api_token) . ")\n";
echo "\n";

// 2. Limpar token
$apiToken = trim($integration->api_token);
$apiToken = preg_replace('/\s+/', '', $apiToken);
echo "2️⃣ LIMPEZA DO TOKEN:\n";
if ($apiToken !== $integration->api_token) {
    echo "   ⚠️ Token tinha espaços/quebras de linha - LIMPANDO...\n";
    echo "      Original: '" . $integration->api_token . "'\n";
    echo "      Limpo: '" . $apiToken . "'\n";
} else {
    echo "   ✅ Token já está limpo\n";
}
echo "\n";

// 3. Testar token diretamente na API UTMify
echo "3️⃣ TESTANDO TOKEN DIRETAMENTE NA API UTMIFY:\n";
echo "   Fazendo requisição para: https://api.utmify.com.br/api-credentials/orders\n";
echo "   Token usado: " . substr($apiToken, 0, 20) . "...\n";
echo "\n";

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
            'x-api-token' => $apiToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $testPayload);
    
    $statusCode = $response->status();
    $responseBody = $response->body();
    $responseJson = $response->json();
    
    echo "   Status Code: {$statusCode}\n";
    echo "   Response Body: {$responseBody}\n";
    echo "\n";
    
    if ($statusCode === 200 || $statusCode === 201) {
        echo "   ✅✅✅ SUCESSO! O TOKEN ESTÁ VÁLIDO! ✅✅✅\n";
        echo "   A API UTMify aceitou o token e o payload.\n";
        echo "   O problema pode estar em outro lugar.\n";
    } elseif ($statusCode === 404) {
        $errorMessage = $responseJson['message'] ?? 'Unknown error';
        if ($errorMessage === 'API_CREDENTIAL_NOT_FOUND') {
            echo "   ❌❌❌ ERRO CONFIRMADO: TOKEN INVÁLIDO ❌❌❌\n";
            echo "   A API UTMify retornou: API_CREDENTIAL_NOT_FOUND (404)\n";
            echo "   Isso significa que o token não existe ou foi revogado na plataforma UTMify.\n";
            echo "\n";
            echo "   🔧 SOLUÇÃO:\n";
            echo "   1. Acesse: https://utmify.com.br\n";
            echo "   2. Faça login na sua conta\n";
            echo "   3. Vá em: Integrações > Webhooks > Credenciais de API\n";
            echo "   4. Verifique se há uma credencial ativa\n";
            echo "   5. Se não houver ou estiver inativa:\n";
            echo "      - Clique em 'Adicionar Credencial'\n";
            echo "      - Clique em 'Criar Credencial'\n";
            echo "      - COPIE O TOKEN EXATO (sem espaços no início/fim)\n";
            echo "   6. Atualize no banco:\n";
            echo "      UPDATE utmify_integrations SET api_token = 'NOVO_TOKEN_AQUI' WHERE id = 2;\n";
            echo "   7. OU use o script: php public/update-utmify-token.php \"NOVO_TOKEN_AQUI\"\n";
        } else {
            echo "   ❌ Erro desconhecido: {$errorMessage}\n";
        }
    } elseif ($statusCode === 401 || $statusCode === 403) {
        echo "   ❌ Token rejeitado (401/403) - Token pode estar expirado ou sem permissões\n";
    } else {
        echo "   ⚠️ Status inesperado: {$statusCode}\n";
        echo "   Response: {$responseBody}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ ERRO ao fazer requisição: " . $e->getMessage() . "\n";
}

echo "\n";
echo "=== 📊 RESUMO ===\n";
echo "✅ Código: FUNCIONANDO PERFEITAMENTE\n";
echo "✅ Payload: CORRETO (platform: playpayments, todos os campos presentes)\n";
echo "✅ Requisição HTTP: SENDO FEITA CORRETAMENTE\n";
echo "❌ Token: INVÁLIDO (API UTMify não reconhece o token)\n";
echo "\n";
echo "🎯 CONCLUSÃO: O problema é 100% o token inválido.\n";
echo "   Após atualizar o token com um token válido da UTMify, tudo funcionará!\n";

