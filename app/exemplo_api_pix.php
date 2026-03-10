<?php
/**
 * EXEMPLO COMPLETO - API PIX
 * 
 * Este arquivo contém exemplos práticos de como usar a API PIX
 * Copie e cole o código que você precisa!
 */

// ============================================
// CONFIGURAÇÕES - ALTERE AQUI!
// ============================================
define('API_URL', 'https://seu-dominio.com/api/v1/transactions');
define('PUBLIC_KEY', 'PB-playpayments-sua-chave-publica-aqui');
define('PRIVATE_KEY', 'SK-playpayments-sua-chave-secreta-aqui');

// ============================================
// FUNÇÃO: Criar PIX
// ============================================
function criarPix($dados) {
    $url = API_URL;
    
    // Preparar dados
    $data = [
        'amount' => $dados['amount'],
        'payment_method' => 'pix',
        'customer' => [
            'name' => $dados['customer']['name'],
            'email' => $dados['customer']['email'],
            'document' => preg_replace('/[^0-9]/', '', $dados['customer']['document']), // Remove formatação
        ],
        'description' => $dados['description'] ?? 'Pagamento via PIX',
    ];
    
    // Adicionar telefone se fornecido
    if (!empty($dados['customer']['phone'])) {
        $data['customer']['phone'] = preg_replace('/[^0-9]/', '', $dados['customer']['phone']);
    }
    
    // Adicionar external_id se fornecido
    if (!empty($dados['external_id'])) {
        $data['external_id'] = $dados['external_id'];
    }
    
    // Adicionar tempo de expiração se fornecido
    if (!empty($dados['pix_expires_in_minutes'])) {
        $data['pix_expires_in_minutes'] = $dados['pix_expires_in_minutes'];
    }
    
    // Fazer requisição
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Public-Key: ' . PUBLIC_KEY,
        'X-Private-Key: ' . PRIVATE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para desenvolvimento
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro na requisição cURL: ' . $error,
            'http_code' => $httpCode
        ];
    }
    
    // Verificar se a resposta está vazia
    if (empty($response)) {
        return [
            'success' => false,
            'error' => 'Resposta vazia da API',
            'http_code' => $httpCode,
            'debug_info' => 'A API não retornou nenhuma resposta. Verifique se a URL está correta e se o servidor está acessível.'
        ];
    }
    
    // Limpar BOM e espaços no início/fim
    $response = trim($response);
    $response = preg_replace('/^\xEF\xBB\xBF/', '', $response); // Remove BOM UTF-8
    
    // Verificar se é HTML (geralmente significa erro 404 ou página de erro)
    $isHtml = stripos($response, '<html') !== false || 
              stripos($response, '<!DOCTYPE') !== false ||
              stripos($response, '<body') !== false;
    
    // Tentar decodificar JSON
    $result = json_decode($response, true);
    
    // Verificar se o JSON foi decodificado corretamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMsg = 'Erro ao decodificar JSON: ' . json_last_error_msg();
        
        if ($isHtml) {
            $errorMsg .= ' (A resposta é HTML, não JSON - provavelmente página de erro do servidor)';
        }
        
        return [
            'success' => false,
            'error' => $errorMsg,
            'http_code' => $httpCode,
            'raw_response' => $response, // Resposta completa
            'response_preview' => substr($response, 0, 1000),
            'response_length' => strlen($response),
            'is_html' => $isHtml,
            'debug_info' => [
                'url' => $url,
                'http_code' => $httpCode,
                'first_200_chars' => substr($response, 0, 200)
            ]
        ];
    }
    
    if ($httpCode !== 201) {
        return [
            'success' => false,
            'error' => $result['error'] ?? 'Erro desconhecido',
            'message' => $result['message'] ?? null,
            'errors' => $result['errors'] ?? null,
            'http_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    return $result;
}

// ============================================
// FUNÇÃO: Consultar Status do PIX
// ============================================
function consultarStatusPix($transactionId) {
    $url = API_URL . '/' . $transactionId;
    
    // Fazer requisição
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PRIVATE_KEY
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas para desenvolvimento
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'Erro na requisição cURL: ' . $error,
            'http_code' => $httpCode
        ];
    }
    
    // Verificar se a resposta está vazia
    if (empty($response)) {
        return [
            'success' => false,
            'error' => 'Resposta vazia da API',
            'http_code' => $httpCode
        ];
    }
    
    $result = json_decode($response, true);
    
    // Verificar se o JSON foi decodificado corretamente
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'Erro ao decodificar JSON: ' . json_last_error_msg(),
            'http_code' => $httpCode,
            'raw_response' => substr($response, 0, 500) // Primeiros 500 caracteres
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => $result['error'] ?? 'Erro desconhecido',
            'message' => $result['message'] ?? null,
            'errors' => $result['errors'] ?? null,
            'http_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    return $result;
}

// ============================================
// EXEMPLO 1: Criar um PIX Simples
// ============================================
function exemplo1_CriarPixSimples() {
    echo "=== EXEMPLO 1: Criar PIX Simples ===\n\n";
    
    $dados = [
        'amount' => 50.00,
        'customer' => [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'document' => '12345678900', // CPF (só números)
        ],
        'description' => 'Pagamento de produto',
    ];
    
    $resultado = criarPix($dados);
    
    if ($resultado['success']) {
        echo "✅ PIX criado com sucesso!\n";
        echo "Transaction ID: " . $resultado['data']['transaction_id'] . "\n";
        echo "QR Code: " . $resultado['data']['pix']['qr_code'] . "\n";
        echo "Status: " . $resultado['data']['status'] . "\n";
        echo "Expira em: " . $resultado['data']['expires_at'] . "\n";
        
        // Salvar o transaction_id para consultar depois
        $transactionId = $resultado['data']['transaction_id'];
        
        return $transactionId;
    } else {
        echo "❌ Erro ao criar PIX:\n";
        echo "Erro: " . $resultado['error'] . "\n";
        if (!empty($resultado['message'])) {
            echo "Mensagem: " . $resultado['message'] . "\n";
        }
        if (!empty($resultado['http_code'])) {
            echo "HTTP Code: " . $resultado['http_code'] . "\n";
        }
        if (!empty($resultado['errors'])) {
            echo "Detalhes dos erros:\n";
            print_r($resultado['errors']);
        }
        if (!empty($resultado['raw_response'])) {
            echo "\n═══════════════════════════════════════════════════\n";
            echo "📄 RESPOSTA BRUTA DA API (primeiros 2000 caracteres):\n";
            echo "═══════════════════════════════════════════════════\n";
            echo substr($resultado['raw_response'], 0, 2000) . "\n";
            echo "═══════════════════════════════════════════════════\n";
        }
        
        if (!empty($resultado['debug_info'])) {
            echo "\n🔍 INFORMAÇÕES DE DEBUG:\n";
            if (is_array($resultado['debug_info'])) {
                print_r($resultado['debug_info']);
            } else {
                echo $resultado['debug_info'] . "\n";
            }
        }
        
        if (!empty($resultado['is_html'])) {
            echo "\n⚠️ ATENÇÃO: A resposta parece ser HTML, não JSON!\n";
            echo "Isso geralmente significa:\n";
            echo "  - A URL está errada (retornando página 404)\n";
            echo "  - O servidor está retornando uma página de erro\n";
            echo "  - Há um problema de roteamento no servidor\n";
        }
        return null;
    }
}

// ============================================
// EXEMPLO 2: Criar PIX Completo
// ============================================
function exemplo2_CriarPixCompleto() {
    echo "\n=== EXEMPLO 2: Criar PIX Completo ===\n\n";
    
    $dados = [
        'amount' => 100.50,
        'customer' => [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'document' => '98765432100',
            'phone' => '11988887777',
        ],
        'description' => 'Pagamento de assinatura mensal',
        'external_id' => 'PEDIDO_' . time(), // ID único do seu sistema
        'pix_expires_in_minutes' => 30, // Expira em 30 minutos
    ];
    
    $resultado = criarPix($dados);
    
    if ($resultado['success']) {
        echo "✅ PIX criado com sucesso!\n";
        echo "Transaction ID: " . $resultado['data']['transaction_id'] . "\n";
        echo "External ID: " . $resultado['data']['external_id'] . "\n";
        echo "Valor: R$ " . number_format($resultado['data']['amount'], 2, ',', '.') . "\n";
        echo "QR Code: " . $resultado['data']['pix']['qr_code'] . "\n";
        echo "Status: " . $resultado['data']['status'] . "\n";
        
        return $resultado['data']['transaction_id'];
    } else {
        echo "❌ Erro ao criar PIX:\n";
        echo "Erro: " . $resultado['error'] . "\n";
        return null;
    }
}

// ============================================
// EXEMPLO 3: Consultar Status
// ============================================
function exemplo3_ConsultarStatus($transactionId) {
    echo "\n=== EXEMPLO 3: Consultar Status ===\n\n";
    
    if (empty($transactionId)) {
        echo "❌ Transaction ID não fornecido\n";
        return;
    }
    
    $resultado = consultarStatusPix($transactionId);
    
    if ($resultado['success']) {
        $data = $resultado['data'];
        echo "✅ Status consultado com sucesso!\n";
        echo "Transaction ID: " . $data['transaction_id'] . "\n";
        echo "Status: " . $data['status'] . "\n";
        echo "Valor: R$ " . number_format($data['amount'], 2, ',', '.') . "\n";
        
        if ($data['status'] === 'paid') {
            echo "✅ PAGAMENTO CONFIRMADO!\n";
            echo "Pago em: " . $data['paid_at'] . "\n";
        } elseif ($data['status'] === 'pending') {
            echo "⏳ Aguardando pagamento...\n";
        } elseif ($data['status'] === 'expired') {
            echo "⏰ PIX expirado\n";
        }
    } else {
        echo "❌ Erro ao consultar status:\n";
        echo "Erro: " . $resultado['error'] . "\n";
    }
}

// ============================================
// EXEMPLO 4: Fluxo Completo (Criar + Verificar)
// ============================================
function exemplo4_FluxoCompleto() {
    echo "\n=== EXEMPLO 4: Fluxo Completo ===\n\n";
    
    // 1. Criar PIX
    echo "1️⃣ Criando PIX...\n";
    $dados = [
        'amount' => 25.00,
        'customer' => [
            'name' => 'Pedro Costa',
            'email' => 'pedro@example.com',
            'document' => '11122233344',
        ],
        'description' => 'Teste de pagamento',
    ];
    
    $resultado = criarPix($dados);
    
    if (!$resultado['success']) {
        echo "❌ Erro ao criar PIX: " . $resultado['error'] . "\n";
        return;
    }
    
    $transactionId = $resultado['data']['transaction_id'];
    $qrCode = $resultado['data']['pix']['qr_code'];
    
    echo "✅ PIX criado!\n";
    echo "Transaction ID: $transactionId\n";
    echo "QR Code: $qrCode\n\n";
    
    // 2. Aguardar um pouco (simulando)
    echo "2️⃣ Aguardando pagamento...\n";
    sleep(2);
    
    // 3. Consultar status
    echo "3️⃣ Consultando status...\n";
    $status = consultarStatusPix($transactionId);
    
    if ($status['success']) {
        echo "Status atual: " . $status['data']['status'] . "\n";
        
        if ($status['data']['status'] === 'paid') {
            echo "✅ PAGAMENTO CONFIRMADO!\n";
        } else {
            echo "⏳ Ainda aguardando pagamento...\n";
            echo "💡 Dica: Consulte o status periodicamente (a cada 5-10 segundos)\n";
        }
    }
}

// ============================================
// EXEMPLO 5: Verificar Pagamento em Loop
// ============================================
function exemplo5_VerificarPagamentoLoop($transactionId, $maxTentativas = 10) {
    echo "\n=== EXEMPLO 5: Verificar Pagamento em Loop ===\n\n";
    
    if (empty($transactionId)) {
        echo "❌ Transaction ID não fornecido\n";
        return;
    }
    
    echo "Verificando pagamento (máximo $maxTentativas tentativas)...\n\n";
    
    for ($i = 1; $i <= $maxTentativas; $i++) {
        echo "Tentativa $i/$maxTentativas...\n";
        
        $resultado = consultarStatusPix($transactionId);
        
        if ($resultado['success']) {
            $status = $resultado['data']['status'];
            echo "Status: $status\n";
            
            if ($status === 'paid') {
                echo "\n✅ PAGAMENTO CONFIRMADO!\n";
                echo "Valor: R$ " . number_format($resultado['data']['amount'], 2, ',', '.') . "\n";
                echo "Pago em: " . $resultado['data']['paid_at'] . "\n";
                return true;
            } elseif ($status === 'expired') {
                echo "\n⏰ PIX expirado\n";
                return false;
            } elseif ($status === 'cancelled') {
                echo "\n❌ PIX cancelado\n";
                return false;
            }
        } else {
            echo "Erro: " . $resultado['error'] . "\n";
        }
        
        // Aguardar 5 segundos antes da próxima tentativa
        if ($i < $maxTentativas) {
            echo "Aguardando 5 segundos...\n\n";
            sleep(5);
        }
    }
    
    echo "\n⏰ Tempo esgotado. Pagamento não confirmado.\n";
    return false;
}

// ============================================
// EXEMPLO 6: Gerar QR Code (HTML)
// ============================================
function exemplo6_GerarQRCodeHTML($qrCode) {
    if (empty($qrCode)) {
        return "❌ QR Code não fornecido";
    }
    
    // Usar biblioteca externa para gerar QR Code
    // Exemplo usando API do Google Charts
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrCode);
    
    $html = "<!DOCTYPE html>
<html>
<head>
    <title>QR Code PIX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .qrcode-container {
            text-align: center;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            background: white;
        }
        .qrcode-container img {
            border: 1px solid #ccc;
            padding: 10px;
            background: white;
        }
        .info {
            margin-top: 20px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class='qrcode-container'>
        <h2>Escaneie o QR Code para pagar</h2>
        <img src='$qrCodeUrl' alt='QR Code PIX'>
        <div class='info'>
            <p>Ou copie o código PIX:</p>
            <textarea readonly style='width: 100%; height: 100px; font-size: 12px;'>$qrCode</textarea>
        </div>
    </div>
</body>
</html>";
    
    return $html;
}

// ============================================
// EXECUTAR EXEMPLOS
// ============================================
if (php_sapi_name() === 'cli') {
    // Executar via linha de comando
    
    echo "🚀 EXEMPLOS DE USO DA API PIX\n";
    echo "==============================\n\n";
    
    // Exemplo 1: Criar PIX simples
    $transactionId = exemplo1_CriarPixSimples();
    
    // Exemplo 2: Criar PIX completo
    // exemplo2_CriarPixCompleto();
    
    // Exemplo 3: Consultar status
    if ($transactionId) {
        exemplo3_ConsultarStatus($transactionId);
    }
    
    // Exemplo 4: Fluxo completo
    // exemplo4_FluxoCompleto();
    
    // Exemplo 5: Verificar em loop (descomente para usar)
    // if ($transactionId) {
    //     exemplo5_VerificarPagamentoLoop($transactionId, 10);
    // }
    
} else {
    // Executar via web (exemplo simples)
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>API PIX - Exemplo</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            input, textarea {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            button {
                background: #007bff;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            button:hover {
                background: #0056b3;
            }
            .result {
                margin-top: 20px;
                padding: 15px;
                border-radius: 4px;
            }
            .success {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
            }
            .error {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
            }
        </style>
    </head>
    <body>
        <h1>🚀 API PIX - Criar Pagamento</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'amount' => floatval($_POST['amount']),
                'customer' => [
                    'name' => $_POST['customer_name'],
                    'email' => $_POST['customer_email'],
                    'document' => $_POST['customer_document'],
                    'phone' => $_POST['customer_phone'] ?? '',
                ],
                'description' => $_POST['description'] ?? '',
                'external_id' => $_POST['external_id'] ?? '',
                'pix_expires_in_minutes' => intval($_POST['pix_expires_in_minutes'] ?? 15),
            ];
            
            $resultado = criarPix($dados);
            
            if ($resultado['success']) {
                $data = $resultado['data'];
                $qrCode = $data['pix']['qr_code'];
                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrCode);
                
                echo "<div class='result success'>";
                echo "<h2>✅ PIX criado com sucesso!</h2>";
                echo "<p><strong>Transaction ID:</strong> " . $data['transaction_id'] . "</p>";
                echo "<p><strong>Status:</strong> " . $data['status'] . "</p>";
                echo "<p><strong>Valor:</strong> R$ " . number_format($data['amount'], 2, ',', '.') . "</p>";
                echo "<p><strong>Expira em:</strong> " . $data['expires_at'] . "</p>";
                echo "<hr>";
                echo "<h3>QR Code:</h3>";
                echo "<img src='$qrCodeUrl' alt='QR Code PIX' style='border: 1px solid #ccc; padding: 10px; background: white;'>";
                echo "<p><strong>Código PIX:</strong></p>";
                echo "<textarea readonly style='width: 100%; height: 100px; font-size: 12px;'>$qrCode</textarea>";
                echo "<hr>";
                echo "<p><a href='?consultar=" . $data['transaction_id'] . "'>Consultar Status</a></p>";
                echo "</div>";
            } else {
                echo "<div class='result error'>";
                echo "<h2>❌ Erro ao criar PIX</h2>";
                echo "<p><strong>Erro:</strong> " . $resultado['error'] . "</p>";
                if (!empty($resultado['errors'])) {
                    echo "<pre>" . print_r($resultado['errors'], true) . "</pre>";
                }
                echo "</div>";
            }
        } elseif (isset($_GET['consultar'])) {
            $transactionId = $_GET['consultar'];
            $resultado = consultarStatusPix($transactionId);
            
            if ($resultado['success']) {
                $data = $resultado['data'];
                $statusClass = $data['status'] === 'paid' ? 'success' : 'error';
                echo "<div class='result $statusClass'>";
                echo "<h2>Status da Transação</h2>";
                echo "<p><strong>Transaction ID:</strong> " . $data['transaction_id'] . "</p>";
                echo "<p><strong>Status:</strong> " . $data['status'] . "</p>";
                echo "<p><strong>Valor:</strong> R$ " . number_format($data['amount'], 2, ',', '.') . "</p>";
                if ($data['status'] === 'paid' && !empty($data['paid_at'])) {
                    echo "<p><strong>Pago em:</strong> " . $data['paid_at'] . "</p>";
                }
                echo "<p><a href='?'>Voltar</a></p>";
                echo "</div>";
            } else {
                echo "<div class='result error'>";
                echo "<h2>❌ Erro ao consultar status</h2>";
                echo "<p>" . $resultado['error'] . "</p>";
                echo "<p><a href='?'>Voltar</a></p>";
                echo "</div>";
            }
        } else {
            ?>
            <form method="POST">
                <div class="form-group">
                    <label>Valor (R$):</label>
                    <input type="number" name="amount" step="0.01" min="0.01" required value="50.00">
                </div>
                
                <div class="form-group">
                    <label>Nome do Cliente:</label>
                    <input type="text" name="customer_name" required value="João Silva">
                </div>
                
                <div class="form-group">
                    <label>Email do Cliente:</label>
                    <input type="email" name="customer_email" required value="joao@example.com">
                </div>
                
                <div class="form-group">
                    <label>CPF/CNPJ (só números):</label>
                    <input type="text" name="customer_document" required value="12345678900">
                </div>
                
                <div class="form-group">
                    <label>Telefone (opcional, só números):</label>
                    <input type="text" name="customer_phone" value="11988887777">
                </div>
                
                <div class="form-group">
                    <label>Descrição:</label>
                    <input type="text" name="description" value="Pagamento de produto">
                </div>
                
                <div class="form-group">
                    <label>External ID (opcional):</label>
                    <input type="text" name="external_id" value="">
                </div>
                
                <div class="form-group">
                    <label>Expira em (minutos):</label>
                    <input type="number" name="pix_expires_in_minutes" min="1" value="15">
                </div>
                
                <button type="submit">Criar PIX</button>
            </form>
            <?php
        }
        ?>
    </body>
    </html>
    <?php
}
?>

