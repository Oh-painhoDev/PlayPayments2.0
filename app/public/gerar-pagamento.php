<?php
/**
 * Script completo para gerar pagamentos via API
 * 
 * Suporta: PIX, Cartão de Crédito e Boleto
 * 
 * Acesse via: http://localhost:3000/gerar-pagamento.php
 * 
 * IMPORTANTE: Este script faz requisições para a API (subdomínio api na porta 8000)
 * 
 * ANTES DE USAR:
 * 1. Edite a variável $apiSecret abaixo e coloque seu token de API (Secret Key)
 * 2. Ajuste a variável $apiBaseUrl para apontar para o subdomínio da API
 * 3. Ajuste os dados do pagamento conforme necessário
 */

// ============================================
// CONFIGURAÇÕES
// ============================================
$apiBaseUrl = 'http://localhost:8000'; // URL base da API (subdomínio api)
$apiSecret = 'sk_SEU_TOKEN_AQUI'; // ⚠️ SUBSTITUA PELO SEU TOKEN DE API (Secret Key)

// ============================================
// DADOS DO PAGAMENTO
// ============================================
$paymentData = [
    // Valor do pagamento (obrigatório)
    'amount' => 100.00,
    
    // Método de pagamento: 'pix', 'credit_card' ou 'bank_slip' (obrigatório)
    'payment_method' => 'pix',
    
    // Nome do produto/venda (obrigatório)
    'sale_name' => 'Produto de Teste',
    
    // Descrição do produto (obrigatório)
    'description' => 'Descrição completa do produto ou serviço que está sendo vendido',
    
    // Dados do cliente (obrigatórios)
    'customer' => [
        'name' => 'João Silva',
        'email' => 'joao@example.com',
        'document' => '12345678900', // CPF ou CNPJ (apenas números)
        'phone' => '11999999999', // Telefone (opcional, apenas números)
    ],
    
    // ID externo (opcional - usado para rastreamento)
    'external_id' => 'PEDIDO_' . time() . '_' . rand(1000, 9999),
    
    // Configurações específicas para PIX
    'pix_expires_in_minutes' => 15, // Tempo de expiração do PIX em minutos (padrão: 15)
    
    // Configurações para cartão de crédito (quando payment_method = 'credit_card')
    'installments' => 1, // Número de parcelas (1 a 12)
    
    // Produtos (opcional - se não informar, usa sale_name e description)
    // 'products' => [
    //     [
    //         'title' => 'Produto 1',
    //         'name' => 'Produto 1',
    //         'description' => 'Descrição do Produto 1',
    //         'quantity' => 1,
    //         'unitPrice' => 50.00,
    //         'price' => 50.00,
    //     ],
    // ],
];

// ============================================
// FUNÇÃO PARA CRIAR TRANSAÇÃO
// ============================================
function criarPagamento($apiBaseUrl, $apiSecret, $paymentData) {
    // URL da API v1
    $apiUrl = $apiBaseUrl . '/api/v1/transactions';
    
    // Validar token
    if ($apiSecret === 'sk_SEU_TOKEN_AQUI') {
        return [
            'success' => false,
            'error' => 'Você precisa editar o arquivo e substituir "sk_SEU_TOKEN_AQUI" pelo seu token de API real!',
            'http_code' => 0,
        ];
    }
    
    // Preparar dados para envio
    $data = [
        'amount' => $paymentData['amount'],
        'payment_method' => $paymentData['payment_method'],
        'sale_name' => $paymentData['sale_name'],
        'description' => $paymentData['description'],
        'customer' => [
            'name' => $paymentData['customer']['name'],
            'email' => $paymentData['customer']['email'],
            'document' => preg_replace('/[^0-9]/', '', $paymentData['customer']['document']),
        ],
    ];
    
    // Adicionar telefone se fornecido
    if (isset($paymentData['customer']['phone']) && !empty($paymentData['customer']['phone'])) {
        $data['customer']['phone'] = preg_replace('/[^0-9]/', '', $paymentData['customer']['phone']);
    }
    
    // Adicionar external_id se fornecido
    if (isset($paymentData['external_id']) && !empty($paymentData['external_id'])) {
        $data['external_id'] = $paymentData['external_id'];
    }
    
    // Adicionar produtos se fornecidos
    if (isset($paymentData['products']) && is_array($paymentData['products']) && count($paymentData['products']) > 0) {
        $data['products'] = $paymentData['products'];
    }
    
    // Configurações específicas por método de pagamento
    if ($paymentData['payment_method'] === 'pix') {
        if (isset($paymentData['pix_expires_in_minutes'])) {
            $data['pix_expires_in_minutes'] = $paymentData['pix_expires_in_minutes'];
        }
    } elseif ($paymentData['payment_method'] === 'credit_card') {
        if (isset($paymentData['installments'])) {
            $data['installments'] = $paymentData['installments'];
        }
    }
    
    // Inicializar cURL
    $ch = curl_init();
    
    // Configurar opções do cURL
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $apiSecret,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
    ]);
    
    // Executar requisição
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Processar resposta
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro cURL: ' . $error,
            'http_code' => $httpCode,
        ];
    }
    
    $responseData = json_decode($response, true);
    
    return [
        'success' => isset($responseData['success']) && $responseData['success'],
        'data' => $responseData,
        'http_code' => $httpCode,
        'raw_response' => $response,
    ];
}

// ============================================
// EXECUTAR REQUISIÇÃO
// ============================================
$result = criarPagamento($apiBaseUrl, $apiSecret, $paymentData);

// ============================================
// EXIBIR RESULTADO
// ============================================
$isBrowser = isset($_SERVER['HTTP_HOST']);

if ($isBrowser) {
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gerar Pagamento via API</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: #000;
                color: #fff;
                padding: 20px;
                line-height: 1.6;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
            }
            .header {
                background: linear-gradient(135deg, #21b3dd 0%, #1a9bb8 100%);
                padding: 30px;
                border-radius: 12px;
                margin-bottom: 30px;
                text-align: center;
            }
            .header h1 {
                font-size: 2em;
                margin-bottom: 10px;
            }
            .card {
                background: #161616;
                border-radius: 12px;
                padding: 30px;
                margin-bottom: 20px;
                border: 1px solid #2d2d2d;
            }
            .card h2 {
                color: #21b3dd;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 2px solid #21b3dd;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 15px;
                margin-bottom: 20px;
            }
            .info-item {
                background: #1f1f1f;
                padding: 15px;
                border-radius: 8px;
            }
            .info-label {
                color: #707070;
                font-size: 0.9em;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .info-value {
                color: #fff;
                font-size: 1.1em;
            }
            .success {
                background: #1a3a2a;
                border-left: 4px solid #10b981;
            }
            .error {
                background: #3a1a1a;
                border-left: 4px solid #ef4444;
            }
            .warning {
                background: #3a2a1a;
                border-left: 4px solid #f59e0b;
            }
            pre {
                background: #1f1f1f;
                padding: 20px;
                border-radius: 8px;
                overflow-x: auto;
                border: 1px solid #2d2d2d;
                font-size: 0.9em;
            }
            code {
                color: #21b3dd;
                background: #1f1f1f;
                padding: 2px 6px;
                border-radius: 4px;
            }
            .btn {
                display: inline-block;
                padding: 12px 24px;
                background: #21b3dd;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
                font-weight: bold;
                margin-top: 10px;
                transition: background 0.3s;
            }
            .btn:hover {
                background: #1a9bb8;
            }
            .pix-code {
                background: #1f1f1f;
                padding: 15px;
                border-radius: 8px;
                border: 1px solid #21b3dd;
                word-break: break-all;
                font-family: monospace;
                font-size: 0.9em;
                margin-top: 10px;
            }
            .badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 0.85em;
                font-weight: bold;
            }
            .badge-success {
                background: #10b981;
                color: #fff;
            }
            .badge-pending {
                background: #f59e0b;
                color: #fff;
            }
            .badge-error {
                background: #ef4444;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>💳 Gerador de Pagamentos via API</h1>
                <p>Script completo para criar transações PIX, Cartão de Crédito e Boleto</p>
            </div>
            
            <?php if ($apiSecret === 'sk_SEU_TOKEN_AQUI'): ?>
                <div class="card warning">
                    <h2>⚠️ ATENÇÃO</h2>
                    <p>Você precisa editar este arquivo e substituir <code>sk_SEU_TOKEN_AQUI</code> pelo seu token de API real!</p>
                    <p><strong>Encontre seu token em:</strong> Configurações → API Keys → Chave Secreta</p>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>📤 Dados Enviados</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Valor</div>
                        <div class="info-value">R$ <?= number_format($paymentData['amount'], 2, ',', '.') ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Método de Pagamento</div>
                        <div class="info-value"><?= strtoupper($paymentData['payment_method']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Nome do Produto</div>
                        <div class="info-value"><?= htmlspecialchars($paymentData['sale_name']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Cliente</div>
                        <div class="info-value"><?= htmlspecialchars($paymentData['customer']['name']) ?></div>
                    </div>
                </div>
                <details style="margin-top: 20px;">
                    <summary style="cursor: pointer; color: #21b3dd; font-weight: bold;">Ver JSON completo</summary>
                    <pre><?= json_encode($paymentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                </details>
            </div>
            
            <div class="card <?= $result['success'] ? 'success' : 'error' ?>">
                <h2><?= $result['success'] ? '✅ Pagamento Criado com Sucesso!' : '❌ Erro ao Criar Pagamento' ?></h2>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Status HTTP</div>
                        <div class="info-value"><?= $result['http_code'] ?></div>
                    </div>
                    <?php if ($result['success'] && isset($result['data']['data'])): 
                        $transaction = $result['data']['data'];
                    ?>
                        <div class="info-item">
                            <div class="info-label">Transaction ID</div>
                            <div class="info-value"><?= htmlspecialchars($transaction['transaction_id'] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="badge badge-<?= strtolower($transaction['status'] ?? 'pending') === 'paid' ? 'success' : 'pending' ?>">
                                    <?= strtoupper($transaction['status'] ?? 'PENDING') ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Valor</div>
                            <div class="info-value">R$ <?= number_format($transaction['amount'] ?? 0, 2, ',', '.') ?></div>
                        </div>
                        <?php if (isset($transaction['expires_at'])): ?>
                            <div class="info-item">
                                <div class="info-label">Expira em</div>
                                <div class="info-value"><?= htmlspecialchars($transaction['expires_at']) ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <?php if ($result['success'] && isset($result['data']['data']['pix'])): 
                    $pix = $result['data']['data']['pix'];
                ?>
                    <div style="margin-top: 20px;">
                        <h3 style="color: #21b3dd; margin-bottom: 10px;">🔷 Código PIX (Copia e Cola)</h3>
                        <div class="pix-code"><?= htmlspecialchars($pix['code'] ?? $pix['emv'] ?? $pix['payload'] ?? 'N/A') ?></div>
                        <?php if (isset($pix['qr_code_base64'])): ?>
                            <div style="margin-top: 15px;">
                                <h3 style="color: #21b3dd; margin-bottom: 10px;">📱 QR Code</h3>
                                <img src="data:image/png;base64,<?= $pix['qr_code_base64'] ?>" alt="QR Code PIX" style="max-width: 300px; border: 2px solid #21b3dd; border-radius: 8px; padding: 10px; background: #fff;">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$result['success']): ?>
                    <div style="margin-top: 20px;">
                        <p><strong>Erro:</strong> <?= htmlspecialchars($result['error'] ?? $result['data']['error'] ?? 'Erro desconhecido') ?></p>
                        <?php if (isset($result['data']['errors'])): ?>
                            <h3 style="color: #ef4444; margin-top: 15px;">Erros de Validação:</h3>
                            <pre><?= json_encode($result['data']['errors'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></pre>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <details style="margin-top: 20px;">
                    <summary style="cursor: pointer; color: #21b3dd; font-weight: bold;">Ver Resposta Completa (JSON)</summary>
                    <pre><?= htmlspecialchars($result['raw_response']) ?></pre>
                </details>
            </div>
            
            <div class="card">
                <h2>📋 Informações</h2>
                <p><strong>URL da API:</strong> <code><?= htmlspecialchars($apiBaseUrl) ?>/api/v1/transactions</code></p>
                <p><strong>Método:</strong> POST</code></p>
                <p><strong>Autenticação:</strong> Bearer Token (Secret Key)</p>
                <p style="margin-top: 15px;">
                    <a href="javascript:location.reload()" class="btn">🔄 Testar Novamente</a>
                </p>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // Saída para terminal
    echo "💳 Gerador de Pagamentos via API\n";
    echo str_repeat("=", 50) . "\n\n";
    
    if ($apiSecret === 'sk_SEU_TOKEN_AQUI') {
        echo "⚠️  ATENÇÃO: Você precisa editar este arquivo e substituir 'sk_SEU_TOKEN_AQUI' pelo seu token de API!\n\n";
    }
    
    echo "📤 Dados do Pagamento:\n";
    echo "  Valor: R$ " . number_format($paymentData['amount'], 2, ',', '.') . "\n";
    echo "  Método: " . strtoupper($paymentData['payment_method']) . "\n";
    echo "  Produto: " . $paymentData['sale_name'] . "\n";
    echo "  Cliente: " . $paymentData['customer']['name'] . "\n\n";
    
    echo "📥 Resposta da API:\n";
    echo "  Status HTTP: " . $result['http_code'] . "\n";
    
    if ($result['success']) {
        echo "  ✅ Sucesso!\n";
        if (isset($result['data']['data'])) {
            $transaction = $result['data']['data'];
            echo "  Transaction ID: " . ($transaction['transaction_id'] ?? 'N/A') . "\n";
            echo "  Status: " . strtoupper($transaction['status'] ?? 'PENDING') . "\n";
            echo "  Valor: R$ " . number_format($transaction['amount'] ?? 0, 2, ',', '.') . "\n";
            
            if (isset($result['data']['data']['pix'])) {
                $pix = $result['data']['data']['pix'];
                echo "\n  🔷 Código PIX:\n";
                echo "  " . ($pix['code'] ?? $pix['emv'] ?? $pix['payload'] ?? 'N/A') . "\n";
            }
        }
    } else {
        echo "  ❌ Erro!\n";
        echo "  " . ($result['error'] ?? $result['data']['error'] ?? 'Erro desconhecido') . "\n";
        if (isset($result['data']['errors'])) {
            echo "\n  Erros de Validação:\n";
            print_r($result['data']['errors']);
        }
    }
    
    echo "\n\nResposta Completa (JSON):\n";
    echo json_encode(json_decode($result['raw_response'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
?>

