<?php
/**
 * ==========================================
 * TESTE SHARKBANKING - WEB INTERFACE
 * ==========================================
 * 
 * Acesse via: http://localhost:8000/test-sharkbanking.php
 * 
 * Configure todos os parâmetros abaixo e clique em "Testar"
 */

// Inicializar Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\PaymentGatewayService;
use App\Models\PaymentGateway;

// Processar formulário
$result = null;
$error = null;
$showResults = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar usuário
        $userId = (int)$_POST['user_id'];
        $user = User::find($userId);
        
        if (!$user) {
            throw new \Exception('Usuário não encontrado!');
        }
        
        // Validar gateway
        $gatewayId = !empty($_POST['gateway_id']) ? (int)$_POST['gateway_id'] : null;
        if ($gatewayId) {
            $gateway = PaymentGateway::find($gatewayId);
            if (!$gateway) {
                throw new \Exception('Gateway não encontrado!');
            }
        } else {
            $gateway = $user->assignedGateway;
            if (!$gateway) {
                throw new \Exception('Usuário não possui gateway configurado!');
            }
        }
        
        // Preparar dados da transação
        $amount = (float)$_POST['amount'];
        $paymentMethod = $_POST['payment_method'];
        $saleName = $_POST['sale_name'];
        $description = $_POST['description'] ?? '';
        
        // PIX expiration
        $pixExpiresInDays = !empty($_POST['pix_expires_in_days']) ? (int)$_POST['pix_expires_in_days'] : null;
        $pixExpiresInMinutes = !empty($_POST['pix_expires_in_minutes']) ? (int)$_POST['pix_expires_in_minutes'] : 15;
        
        // Customer data
        $customerName = $_POST['customer_name'];
        $customerEmail = $_POST['customer_email'];
        $customerDocument = preg_replace('/[^0-9]/', '', $_POST['customer_document']);
        $customerPhone = preg_replace('/[^0-9]/', '', $_POST['customer_phone'] ?? '');
        
        // Build transaction data
        $transactionData = [
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'sale_name' => $saleName,
            'description' => $description,
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
                'document' => $customerDocument,
                'phone' => $customerPhone,
            ],
            'metadata' => [
                'created_via' => 'test_web_interface',
                'test' => true,
            ],
        ];
        
        // Add PIX expiration
        if ($paymentMethod === 'pix') {
            if ($pixExpiresInDays) {
                $days = (int)$pixExpiresInDays;
                $transactionData['pix_expires_in_minutes'] = $days * 1440; // Convert days to minutes
            } else {
                $minutes = (int)$pixExpiresInMinutes;
                $transactionData['pix_expires_in_minutes'] = $minutes;
            }
        }
        
        // Create payment service
        $paymentService = new PaymentGatewayService($gateway);
        
        // Create transaction
        $result = $paymentService->createTransaction($user, $transactionData);
        
        if (!$result['success']) {
            throw new \Exception($result['error'] ?? 'Erro ao criar transação!');
        }
        
        $showResults = true;
        
    } catch (\Exception $e) {
        $error = $e->getMessage();
    }
}

// Get users for dropdown
$users = User::orderBy('name')->get();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐊 Teste SharkBanking - Web Interface</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        pre {
            background: #1f2937;
            color: #f3f4f6;
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
        }
        .qr-code {
            max-width: 300px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">🐊 Teste SharkBanking</h1>
            <p class="text-gray-600">Interface web para testar a integração SharkBanking com todos os parâmetros</p>
        </div>

        <?php if ($error): ?>
        <!-- Error Message -->
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium"><strong>Erro:</strong> <?= htmlspecialchars($error) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="lg:col-span-2">
                <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
                    <!-- User Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Usuário * <span class="text-gray-500 text-xs">(ID do usuário)</span>
                        </label>
                        <select name="user_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Selecione o usuário</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u->id ?>" <?= (isset($_POST['user_id']) && $_POST['user_id'] == $u->id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($u->name) ?> (ID: <?= $u->id ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Gateway ID (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Gateway ID <span class="text-gray-500 text-xs">(opcional - usa gateway padrão se não informado)</span>
                        </label>
                        <input type="number" name="gateway_id" value="<?= htmlspecialchars($_POST['gateway_id'] ?? '') ?>" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Deixe vazio para usar gateway padrão">
                    </div>

                    <!-- Transaction Info -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Informações da Transação</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$) *</label>
                                <input type="number" name="amount" step="0.01" min="0.01" required 
                                       value="<?= htmlspecialchars($_POST['amount'] ?? '100.00') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Método de Pagamento *</label>
                                <select name="payment_method" required 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="pix" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'pix') ? 'selected' : 'selected' ?>>PIX</option>
                                    <option value="credit_card" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'credit_card') ? 'selected' : '' ?>>Cartão de Crédito</option>
                                    <option value="bank_slip" <?= (isset($_POST['payment_method']) && $_POST['payment_method'] == 'bank_slip') ? 'selected' : '' ?>>Boleto Bancário</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">📦 Informações do Produto/Venda</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome da Venda/Produto *</label>
                                <input type="text" name="sale_name" maxlength="255" required 
                                       value="<?= htmlspecialchars($_POST['sale_name'] ?? 'Produto Teste') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Nome que aparecerá nos itens">
                                <p class="text-xs text-gray-500 mt-1">Nome ou título da venda que aparecerá nos itens da transação</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                                <textarea name="description" rows="3" maxlength="500"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                          placeholder="Descrição detalhada do produto"><?= htmlspecialchars($_POST['description'] ?? 'Descrição do produto de teste') ?></textarea>
                                <p class="text-xs text-gray-500 mt-1">Descrição adicional da venda ou informações relevantes</p>
                            </div>
                        </div>
                    </div>

                    <!-- PIX Expiration -->
                    <div class="border-t pt-6" id="pix-expiration-section">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">⏰ Configurações PIX</h3>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <p class="text-sm text-blue-800">
                                <strong>⚠️ IMPORTANTE:</strong> Para SharkBanking:
                                <ul class="list-disc list-inside mt-2 space-y-1 text-xs">
                                    <li>Valores < 24 horas: usa <code>expiresIn</code> (em segundos)</li>
                                    <li>Valores ≥ 1 dia: usa <code>expiresInDays</code> (dias inteiros, 1-90)</li>
                                </ul>
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Tipo de Expiração
                                </label>
                                <select id="pix_expiration_type" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="minutes" <?= (isset($_POST['pix_expires_in_days']) && !empty($_POST['pix_expires_in_days'])) ? '' : 'selected' ?>>Minutos/Horas</option>
                                    <option value="days" <?= (isset($_POST['pix_expires_in_days']) && !empty($_POST['pix_expires_in_days'])) ? 'selected' : '' ?>>Dias</option>
                                </select>
                            </div>

                            <div id="pix-minutes-field">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tempo (minutos)</label>
                                <select name="pix_expires_in_minutes" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="15" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 15) ? 'selected' : 'selected' ?>>15 minutos</option>
                                    <option value="30" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 30) ? 'selected' : '' ?>>30 minutos</option>
                                    <option value="60" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 60) ? 'selected' : '' ?>>1 hora</option>
                                    <option value="120" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 120) ? 'selected' : '' ?>>2 horas</option>
                                    <option value="180" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 180) ? 'selected' : '' ?>>3 horas</option>
                                    <option value="360" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 360) ? 'selected' : '' ?>>6 horas</option>
                                    <option value="720" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 720) ? 'selected' : '' ?>>12 horas</option>
                                    <option value="1440" <?= (isset($_POST['pix_expires_in_minutes']) && $_POST['pix_expires_in_minutes'] == 1440) ? 'selected' : '' ?>>1 dia (24 horas)</option>
                                </select>
                            </div>

                            <div id="pix-days-field" style="display: none;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tempo (dias - 1 a 90)</label>
                                <input type="number" name="pix_expires_in_days" min="1" max="90" 
                                       value="<?= htmlspecialchars($_POST['pix_expires_in_days'] ?? '') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Ex: 7, 30, 90">
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Dados do Cliente</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                                <input type="text" name="customer_name" required 
                                       value="<?= htmlspecialchars($_POST['customer_name'] ?? 'João Silva') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" name="customer_email" required 
                                       value="<?= htmlspecialchars($_POST['customer_email'] ?? 'joao@example.com') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">CPF/CNPJ * <span class="text-gray-500 text-xs">(apenas números)</span></label>
                                <input type="text" name="customer_document" required 
                                       value="<?= htmlspecialchars($_POST['customer_document'] ?? '12345678900') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="12345678900">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telefone <span class="text-gray-500 text-xs">(apenas números)</span></label>
                                <input type="text" name="customer_phone" 
                                       value="<?= htmlspecialchars($_POST['customer_phone'] ?? '11999999999') ?>"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="11999999999">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="border-t pt-6">
                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            🐊 Testar Transação SharkBanking
                        </button>
                    </div>
                </form>
            </div>

            <!-- Results / Info Sidebar -->
            <div class="space-y-6">
                <?php if ($showResults && $result): ?>
                    <!-- Success Results -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">✅ Resultado</h3>
                        
                        <?php 
                        $transaction = $result['transaction'];
                        $gatewayResponse = $result['gateway_response'] ?? [];
                        ?>
                        
                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-gray-600">ID Interno:</span>
                                <code class="block mt-1"><?= htmlspecialchars($transaction->transaction_id) ?></code>
                            </div>
                            
                            <?php if ($transaction->external_id): ?>
                            <div>
                                <span class="text-gray-600">ID Externo:</span>
                                <code class="block mt-1"><?= htmlspecialchars($transaction->external_id) ?></code>
                            </div>
                            <?php endif; ?>
                            
                            <div>
                                <span class="text-gray-600">Valor:</span>
                                <strong class="block mt-1">R$ <?= number_format($transaction->amount, 2, ',', '.') ?></strong>
                            </div>
                            
                            <div>
                                <span class="text-gray-600">Status:</span>
                                <strong class="block mt-1 uppercase"><?= htmlspecialchars($transaction->status) ?></strong>
                            </div>
                            
                            <?php if ($transaction->expires_at): ?>
                            <div>
                                <span class="text-gray-600">Expira em:</span>
                                <strong class="block mt-1"><?= $transaction->expires_at->format('d/m/Y H:i:s') ?></strong>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- PIX Data -->
                        <?php if ($transaction->payment_method === 'pix' && isset($gatewayResponse['payment_data']['pix'])): ?>
                            <?php $pixData = $gatewayResponse['payment_data']['pix']; ?>
                            
                            <div class="mt-6 pt-6 border-t">
                                <h4 class="font-semibold text-gray-900 mb-3">🔐 Dados do PIX</h4>
                                
                                <?php if (isset($pixData['qrcode'])): ?>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">QR Code</label>
                                        <div class="qr-code bg-gray-100 p-4 rounded-lg">
                                            <?php 
                                            // QR Code pode ser URL ou base64
                                            $qrcodeUrl = $pixData['qrcode'];
                                            if (filter_var($qrcodeUrl, FILTER_VALIDATE_URL)) {
                                                // É uma URL
                                                echo '<img src="' . htmlspecialchars($qrcodeUrl) . '" alt="QR Code" class="w-full">';
                                            } elseif (strpos($qrcodeUrl, 'data:image') === 0) {
                                                // Já está em formato data URI
                                                echo '<img src="' . htmlspecialchars($qrcodeUrl) . '" alt="QR Code" class="w-full">';
                                            } else {
                                                // Tentar como base64
                                                echo '<img src="data:image/png;base64,' . htmlspecialchars($qrcodeUrl) . '" alt="QR Code" class="w-full">';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($pixData['payload'])): ?>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Payload (Copia e Cola)</label>
                                        <textarea readonly rows="4" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded text-xs font-mono"><?= htmlspecialchars($pixData['payload']) ?></textarea>
                                        <button onclick="copyToClipboard('<?= htmlspecialchars(addslashes($pixData['payload'])) ?>')" 
                                                class="mt-2 text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                            Copiar
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($pixData['expirationDate'])): ?>
                                    <div>
                                        <span class="text-gray-600 text-sm">Data de Expiração:</span>
                                        <strong class="block mt-1"><?= htmlspecialchars($pixData['expirationDate']) ?></strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Raw Response -->
                        <div class="mt-6 pt-6 border-t">
                            <h4 class="font-semibold text-gray-900 mb-3">📡 Resposta da API</h4>
                            <pre class="text-xs overflow-auto"><?= json_encode($result['gateway_response']['raw_response'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></pre>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Info Sidebar -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">ℹ️ Informações</h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <p><strong>URL:</strong> <code><?= $_SERVER['HTTP_HOST'] ?>/test-sharkbanking.php</code></p>
                            <p>Configure todos os parâmetros acima e clique em "Testar" para criar uma transação de teste.</p>
                            
                            <div class="mt-4 pt-4 border-t">
                                <p class="font-semibold text-gray-900 mb-2">📋 Parâmetros Configuráveis:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Valor da transação</li>
                                    <li>Método de pagamento</li>
                                    <li><strong>Nome do produto</strong></li>
                                    <li><strong>Descrição</strong></li>
                                    <li><strong>Tempo de expiração PIX</strong></li>
                                    <li>Dados do cliente</li>
                                </ul>
                            </div>
                            
                            <div class="mt-4 pt-4 border-t">
                                <p class="font-semibold text-gray-900 mb-2">⏰ Expiração PIX:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>&lt; 24 horas: usa <code>expiresIn</code> (segundos)</li>
                                    <li>≥ 1 dia: usa <code>expiresInDays</code> (dias, 1-90)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Toggle PIX expiration fields
        const expirationType = document.getElementById('pix_expiration_type');
        const minutesField = document.getElementById('pix-minutes-field');
        const daysField = document.getElementById('pix-days-field');
        const paymentMethod = document.querySelector('select[name="payment_method"]');
        const pixSection = document.getElementById('pix-expiration-section');

        expirationType.addEventListener('change', function() {
            if (this.value === 'days') {
                minutesField.style.display = 'none';
                daysField.style.display = 'block';
                document.querySelector('select[name="pix_expires_in_minutes"]').removeAttribute('required');
                document.querySelector('input[name="pix_expires_in_days"]').setAttribute('required', 'required');
            } else {
                minutesField.style.display = 'block';
                daysField.style.display = 'none';
                document.querySelector('input[name="pix_expires_in_days"]').removeAttribute('required');
                document.querySelector('select[name="pix_expires_in_minutes"]').setAttribute('required', 'required');
            }
        });

        // Show/hide PIX section based on payment method
        paymentMethod.addEventListener('change', function() {
            if (this.value === 'pix') {
                pixSection.style.display = 'block';
            } else {
                pixSection.style.display = 'none';
            }
        });

        // Initialize
        if (paymentMethod.value !== 'pix') {
            pixSection.style.display = 'none';
        }

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copiado para a área de transferência!');
            }).catch(function() {
                // Fallback
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Copiado para a área de transferência!');
            });
        }
    </script>
</body>
</html>

