<?php

/**
 * Script para atualizar o token UTMify no banco de dados
 * 
 * USO:
 * 1. Obtenha o token válido da UTMify (https://utmify.com.br > Integrações > Webhooks > Credenciais de API)
 * 2. Execute: php public/update-utmify-token.php "SEU_TOKEN_AQUI"
 * 
 * OU edite este arquivo e coloque o token na variável $newToken
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\UtmifyIntegration;
use Illuminate\Support\Facades\Http;

// Obter token do argumento da linha de comando ou da variável
$newToken = $argv[1] ?? '';

// Se não foi passado como argumento, você pode editar aqui:
// $newToken = 'SEU_TOKEN_AQUI';

if (empty($newToken)) {
    echo "❌ ERRO: Token não fornecido!\n\n";
    echo "USO:\n";
    echo "  php public/update-utmify-token.php \"SEU_TOKEN_AQUI\"\n\n";
    echo "OU edite o arquivo e coloque o token na variável \$newToken\n\n";
    exit(1);
}

// Limpar token
$newToken = trim($newToken);
$newToken = preg_replace('/\s+/', '', $newToken);

if (strlen($newToken) < 20) {
    echo "❌ ERRO: Token muito curto! Verifique se copiou o token completo.\n";
    exit(1);
}

echo "=== ATUALIZAR TOKEN UTMIFY ===\n\n";

// Buscar integração
$integration = UtmifyIntegration::find(2);

if (!$integration) {
    echo "❌ Integração não encontrada (ID: 2)\n";
    exit(1);
}

echo "📋 INTEGRAÇÃO ATUAL:\n";
echo "  ID: {$integration->id}\n";
echo "  Nome: {$integration->name}\n";
echo "  User ID: {$integration->user_id}\n";
echo "  Token Antigo: " . substr($integration->api_token, 0, 10) . "...\n";
echo "\n";

echo "🔑 NOVO TOKEN:\n";
echo "  Token: " . substr($newToken, 0, 10) . "...\n";
echo "  Length: " . strlen($newToken) . "\n";
echo "\n";

// Testar token antes de salvar
echo "🧪 TESTANDO TOKEN ANTES DE SALVAR...\n\n";

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
        'phone' => null,
        'document' => null,
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
    $response = Http::timeout(10)
        ->withHeaders([
            'x-api-token' => $newToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
        ->post('https://api.utmify.com.br/api-credentials/orders', $testPayload);
    
    $status = $response->status();
    $body = $response->body();
    
    echo "  Status: {$status}\n";
    echo "  Response: " . substr($body, 0, 200) . "\n\n";
    
    if ($response->successful()) {
        echo "✅ TOKEN VÁLIDO! Atualizando no banco...\n\n";
        
        $integration->api_token = $newToken;
        $integration->save();
        
        echo "✅ TOKEN ATUALIZADO COM SUCESSO!\n";
        echo "  Integração ID: {$integration->id}\n";
        echo "  Novo Token: " . substr($integration->api_token, 0, 10) . "...\n";
        echo "\n";
        echo "🎉 Agora as transações PIX serão enviadas automaticamente para UTMify!\n";
    } else {
        $errorData = json_decode($body, true);
        if ($errorData && isset($errorData['message'])) {
            if ($errorData['message'] === 'API_CREDENTIAL_NOT_FOUND') {
                echo "❌ TOKEN INVÁLIDO: A API UTMify não reconheceu este token.\n\n";
                echo "VERIFICAÇÕES:\n";
                echo "1. O token está correto? (copie EXATAMENTE da UTMify)\n";
                echo "2. A credencial está ativa na UTMify?\n";
                echo "3. Você copiou o token completo? (sem cortes)\n";
                echo "4. Não há espaços ou quebras de linha no token?\n\n";
                echo "AÇÃO:\n";
                echo "1. Acesse https://utmify.com.br\n";
                echo "2. Vá em: Integrações > Webhooks > Credenciais de API\n";
                echo "3. Verifique se a credencial está ativa\n";
                echo "4. Se necessário, crie uma nova credencial\n";
                echo "5. Copie o token EXATO (sem espaços)\n";
                echo "6. Execute este script novamente com o token correto\n";
            } else {
                echo "⚠️ TOKEN PODE ESTAR VÁLIDO, mas há outro erro:\n";
                echo "   Erro: " . $errorData['message'] . "\n";
                echo "   Isso pode ser um problema com o payload de teste.\n";
                echo "   Deseja atualizar o token mesmo assim? (S/N)\n";
                echo "   (Pressione Enter para cancelar)\n";
                
                // Em modo não-interativo, não atualizar
                echo "\n⚠️ Modo não-interativo: Não atualizando token automaticamente.\n";
                echo "   Se você tem certeza que o token está correto, pode atualizar manualmente:\n";
                echo "   UPDATE utmify_integrations SET api_token = '{$newToken}' WHERE id = 2;\n";
            }
        } else {
            echo "❌ ERRO DESCONHECIDO: {$body}\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ EXCEÇÃO: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n";

