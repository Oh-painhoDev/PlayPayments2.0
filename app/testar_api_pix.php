<?php
/**
 * TESTE RÁPIDO DA API PIX
 * 
 * Use este arquivo para testar e debugar a API
 * Ele mostra TODOS os detalhes da requisição e resposta
 */

// ============================================
// CONFIGURAÇÕES - ALTERE AQUI!
// ============================================
define('API_URL', 'https://seu-dominio.com/api/v1/transactions');
define('PUBLIC_KEY', 'PB-playpayments-sua-chave-publica-aqui');
define('PRIVATE_KEY', 'SK-playpayments-sua-chave-secreta-aqui');

// ============================================
// FUNÇÃO DE DEBUG
// ============================================
function debugCurl($ch, $url, $data = null) {
    echo "═══════════════════════════════════════════════════\n";
    echo "🔍 DEBUG DA REQUISIÇÃO\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    echo "📍 URL: $url\n";
    echo "🔑 Public Key: " . substr(PUBLIC_KEY, 0, 20) . "...\n";
    echo "🔑 Private Key: " . substr(PRIVATE_KEY, 0, 20) . "...\n\n";
    
    if ($data) {
        echo "📦 Dados enviados:\n";
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
    }
    
    // Executar requisição
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $curlInfo = curl_getinfo($ch);
    
    echo "═══════════════════════════════════════════════════\n";
    echo "📥 RESPOSTA DA API\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    echo "HTTP Code: $httpCode\n";
    
    if ($error) {
        echo "❌ Erro cURL: $error\n\n";
    }
    
    echo "Tamanho da resposta: " . strlen($response) . " bytes\n\n";
    
    if (empty($response)) {
        echo "⚠️ RESPOSTA VAZIA!\n";
        echo "Verifique:\n";
        echo "  - URL está correta?\n";
        echo "  - Servidor está acessível?\n";
        echo "  - Chaves estão corretas?\n";
        return null;
    }
    
    echo "Resposta bruta (primeiros 2000 caracteres):\n";
    echo "─────────────────────────────────────────────\n";
    echo substr($response, 0, 2000) . "\n";
    echo "─────────────────────────────────────────────\n\n";
    
    // Tentar decodificar JSON
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ ERRO AO DECODIFICAR JSON:\n";
        echo "Erro: " . json_last_error_msg() . "\n";
        echo "Código do erro: " . json_last_error() . "\n\n";
        return [
            'success' => false,
            'error' => 'Erro ao decodificar JSON: ' . json_last_error_msg(),
            'http_code' => $httpCode,
            'raw_response' => $response
        ];
    }
    
    echo "✅ JSON decodificado com sucesso!\n\n";
    echo "Resposta formatada:\n";
    echo "─────────────────────────────────────────────\n";
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
    echo "─────────────────────────────────────────────\n\n";
    
    // Informações adicionais
    echo "═══════════════════════════════════════════════════\n";
    echo "ℹ️ INFORMAÇÕES ADICIONAIS\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    echo "URL efetiva: " . $curlInfo['url'] . "\n";
    echo "Tempo total: " . $curlInfo['total_time'] . " segundos\n";
    echo "Tamanho do upload: " . $curlInfo['size_upload'] . " bytes\n";
    echo "Tamanho do download: " . $curlInfo['size_download'] . " bytes\n";
    
    if ($httpCode >= 400) {
        echo "\n❌ ERRO HTTP $httpCode\n";
        echo "Possíveis causas:\n";
        
        if ($httpCode === 401) {
            echo "  - Chaves de autenticação inválidas\n";
            echo "  - Public Key ou Private Key incorretas\n";
            echo "  - Para criar PIX precisa de AMBAS as chaves\n";
        } elseif ($httpCode === 422) {
            echo "  - Dados inválidos enviados\n";
            echo "  - Verifique os campos obrigatórios\n";
        } elseif ($httpCode === 404) {
            echo "  - Endpoint não encontrado\n";
            echo "  - Verifique a URL da API\n";
        } elseif ($httpCode === 500) {
            echo "  - Erro interno do servidor\n";
            echo "  - Entre em contato com o suporte\n";
        }
    }
    
    echo "\n";
    
    return $result;
}

// ============================================
// TESTE 1: Criar PIX
// ============================================
function testarCriarPix() {
    echo "\n";
    echo "╔═══════════════════════════════════════════════════╗\n";
    echo "║   TESTE 1: CRIAR PIX                              ║\n";
    echo "╚═══════════════════════════════════════════════════╝\n\n";
    
    $url = API_URL;
    
    $data = [
        'amount' => 10.00,
        'payment_method' => 'pix',
        'customer' => [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'document' => '12345678900',
        ],
        'description' => 'Teste de pagamento',
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Public-Key: ' . PUBLIC_KEY,
        'X-Private-Key: ' . PRIVATE_KEY,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true); // Modo verbose para mais detalhes
    
    $result = debugCurl($ch, $url, $data);
    
    curl_close($ch);
    
    return $result;
}

// ============================================
// TESTE 2: Consultar Status
// ============================================
function testarConsultarStatus($transactionId = null) {
    echo "\n";
    echo "╔═══════════════════════════════════════════════════╗\n";
    echo "║   TESTE 2: CONSULTAR STATUS                       ║\n";
    echo "╚═══════════════════════════════════════════════════╝\n\n";
    
    if (empty($transactionId)) {
        echo "⚠️ Transaction ID não fornecido. Use um ID válido.\n";
        return null;
    }
    
    $url = API_URL . '/' . $transactionId;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . PRIVATE_KEY
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $result = debugCurl($ch, $url);
    
    curl_close($ch);
    
    return $result;
}

// ============================================
// TESTE 3: Verificar Configuração
// ============================================
function testarConfiguracao() {
    echo "\n";
    echo "╔═══════════════════════════════════════════════════╗\n";
    echo "║   TESTE 3: VERIFICAR CONFIGURAÇÃO                 ║\n";
    echo "╚═══════════════════════════════════════════════════╝\n\n";
    
    $problemas = [];
    
    // Verificar URL
    if (empty(API_URL) || API_URL === 'https://seu-dominio.com/api/v1/transactions') {
        $problemas[] = "❌ URL da API não configurada (ainda está com o valor padrão)";
    } else {
        echo "✅ URL configurada: " . API_URL . "\n";
    }
    
    // Verificar Public Key
    if (empty(PUBLIC_KEY) || strpos(PUBLIC_KEY, 'PB-playpayments-') !== 0) {
        $problemas[] = "❌ Public Key não configurada ou formato inválido (deve começar com PB-playpayments-)";
    } else {
        echo "✅ Public Key configurada: " . substr(PUBLIC_KEY, 0, 20) . "...\n";
    }
    
    // Verificar Private Key
    if (empty(PRIVATE_KEY) || strpos(PRIVATE_KEY, 'SK-playpayments-') !== 0) {
        $problemas[] = "❌ Private Key não configurada ou formato inválido (deve começar com SK-playpayments-)";
    } else {
        echo "✅ Private Key configurada: " . substr(PRIVATE_KEY, 0, 20) . "...\n";
    }
    
    // Verificar cURL
    if (!function_exists('curl_init')) {
        $problemas[] = "❌ Extensão cURL não está instalada no PHP";
    } else {
        echo "✅ Extensão cURL disponível\n";
    }
    
    // Verificar JSON
    if (!function_exists('json_encode')) {
        $problemas[] = "❌ Função JSON não disponível no PHP";
    } else {
        echo "✅ Função JSON disponível\n";
    }
    
    echo "\n";
    
    if (!empty($problemas)) {
        echo "⚠️ PROBLEMAS ENCONTRADOS:\n";
        foreach ($problemas as $problema) {
            echo "  $problema\n";
        }
        echo "\n";
        return false;
    } else {
        echo "✅ Todas as configurações estão OK!\n\n";
        return true;
    }
}

// ============================================
// EXECUTAR TESTES
// ============================================
if (php_sapi_name() === 'cli') {
    echo "\n";
    echo "╔═══════════════════════════════════════════════════╗\n";
    echo "║   TESTE COMPLETO DA API PIX                       ║\n";
    echo "╚═══════════════════════════════════════════════════╝\n";
    
    // Teste 1: Verificar configuração
    $configOk = testarConfiguracao();
    
    if (!$configOk) {
        echo "\n❌ Corrija os problemas acima antes de continuar!\n";
        exit(1);
    }
    
    // Teste 2: Criar PIX
    $resultado = testarCriarPix();
    
    if ($resultado && isset($resultado['success']) && $resultado['success']) {
        echo "\n✅ TESTE CONCLUÍDO COM SUCESSO!\n";
        $transactionId = $resultado['data']['transaction_id'] ?? null;
        
        if ($transactionId) {
            echo "\n💡 Para consultar o status, execute:\n";
            echo "   testarConsultarStatus('$transactionId');\n";
        }
    } else {
        echo "\n❌ TESTE FALHOU!\n";
        echo "Verifique os erros acima.\n";
    }
    
} else {
    // Modo web
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Teste API PIX - Debug</title>
        <style>
            body {
                font-family: 'Courier New', monospace;
                background: #1e1e1e;
                color: #d4d4d4;
                padding: 20px;
                line-height: 1.6;
            }
            pre {
                background: #252526;
                padding: 15px;
                border-radius: 5px;
                overflow-x: auto;
                border: 1px solid #3e3e42;
            }
            .success { color: #4ec9b0; }
            .error { color: #f48771; }
            .warning { color: #dcdcaa; }
            button {
                background: #007acc;
                color: white;
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin: 5px;
            }
            button:hover { background: #005a9e; }
        </style>
    </head>
    <body>
        <h1>🔍 Teste e Debug da API PIX</h1>
        
        <div>
            <button onclick="location.reload()">🔄 Recarregar</button>
            <button onclick="testarConfig()">⚙️ Verificar Config</button>
            <button onclick="testarCriar()">➕ Criar PIX</button>
        </div>
        
        <pre id="output"><?php
        if (isset($_GET['test'])) {
            ob_start();
            
            if ($_GET['test'] === 'config') {
                testarConfiguracao();
            } elseif ($_GET['test'] === 'criar') {
                testarCriarPix();
            }
            
            $output = ob_get_clean();
            echo htmlspecialchars($output);
        } else {
            echo "Clique em um dos botões acima para começar...";
        }
        ?></pre>
        
        <script>
            function testarConfig() {
                window.location.href = '?test=config';
            }
            function testarCriar() {
                window.location.href = '?test=criar';
            }
        </script>
    </body>
    </html>
    <?php
}
?>








