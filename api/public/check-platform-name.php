<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO INTEGRAÇÃO UTMIFY ===\n\n";

$integration = DB::table('utmify_integrations')->where('id', 2)->first();

if ($integration) {
    echo "ID: {$integration->id}\n";
    echo "Nome: {$integration->name}\n";
    echo "Platform Name: " . ($integration->platform_name ?? 'NULL (usará padrão)') . "\n";
    echo "API Token: " . substr($integration->api_token, 0, 20) . "...\n";
    echo "Ativo: " . ($integration->is_active ? 'SIM' : 'NÃO') . "\n";
    echo "Trigger Creation: " . ($integration->trigger_on_creation ? 'SIM' : 'NÃO') . "\n";
    echo "Trigger Payment: " . ($integration->trigger_on_payment ? 'SIM' : 'NÃO') . "\n";
    echo "\n";
    echo "✅ Platform Name configurado: " . ($integration->platform_name ? 'SIM (' . $integration->platform_name . ')' : 'NÃO (usará playpayments)') . "\n";
} else {
    echo "❌ Integração não encontrada!\n";
}

