<?php
/**
 * Teste de Conexão com Banco de Dados - Hostinger
 * 
 * Este arquivo testa a conexão com o banco de dados MySQL na Hostinger.
 * 
 * ⚠️ IMPORTANTE: Delete este arquivo após o teste por segurança!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Conexão - Banco de Dados</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #21b3dd;
            padding-bottom: 10px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dc3545;
            margin: 20px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
            margin: 20px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #dee2e6;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Teste de Conexão - Banco de Dados MySQL</h1>
        
        <?php
        // Carregar o Laravel
        require __DIR__.'/../vendor/autoload.php';
        
        $app = require_once __DIR__.'/../bootstrap/app.php';
        
        try {
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Erro ao inicializar o Laravel:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            echo '</div></body></html>';
            exit;
        }
        
        // Obter configurações do banco (sem exibir senha)
        $dbConfig = config('database.connections.mysql');
        $dbHost = $dbConfig['host'] ?? 'N/A';
        $dbPort = $dbConfig['port'] ?? 'N/A';
        $dbDatabase = $dbConfig['database'] ?? 'N/A';
        $dbUsername = $dbConfig['username'] ?? 'N/A';
        $dbPassword = !empty($dbConfig['password']) ? '*** (oculta)' : 'vazia';
        $dbConnection = config('database.default');
        
        echo '<div class="info">';
        echo '<strong>📋 Configuração Atual:</strong><br>';
        echo '<pre>';
        echo "Conexão: " . htmlspecialchars($dbConnection) . "\n";
        echo "Host: " . htmlspecialchars($dbHost) . "\n";
        echo "Porta: " . htmlspecialchars($dbPort) . "\n";
        echo "Banco de Dados: " . htmlspecialchars($dbDatabase) . "\n";
        echo "Usuário: " . htmlspecialchars($dbUsername) . "\n";
        echo "Senha: " . htmlspecialchars($dbPassword) . "\n";
        echo '</pre>';
        echo '</div>';
        
        // Testar conexão
        try {
            $start = microtime(true);
            $pdo = DB::connection()->getPdo();
            $time = round((microtime(true) - $start) * 1000, 2);
            
            echo '<div class="success">';
            echo '<strong>✅ Conexão estabelecida com sucesso!</strong><br>';
            echo "Tempo de conexão: {$time}ms";
            echo '</div>';
            
            // Obter informações do banco
            try {
                $version = $pdo->query('SELECT VERSION()')->fetchColumn();
                echo '<div class="info">';
                echo '<strong>📊 Informações do Banco:</strong><br>';
                echo "Versão do MySQL: " . htmlspecialchars($version) . "<br>";
                echo '</div>';
                
                // Verificar se a tabela users existe
                try {
                    $userCount = DB::table('users')->count();
                    echo '<div class="info">';
                    echo '<strong>👥 Usuários no Banco:</strong><br>';
                    echo "Total de usuários: " . number_format($userCount, 0, ',', '.');
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="warning">';
                    echo '<strong>⚠️ Tabela users não encontrada:</strong><br>';
                    echo "Execute as migrations: <code>php artisan migrate --force</code>";
                    echo '</div>';
                }
                
            } catch (Exception $e) {
                echo '<div class="warning">';
                echo '<strong>⚠️ Não foi possível obter informações adicionais:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Erro de conexão:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            // Diagnóstico detalhado do erro
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
            
            echo '<div class="warning">';
            echo '<strong>🔍 Diagnóstico do Erro:</strong><br>';
            
            if (strpos($errorMessage, 'Access denied') !== false || $errorCode == 1045) {
                echo '<p><strong>Erro 1045 - Access Denied:</strong></p>';
                echo '<ul>';
                echo '<li>❌ <strong>Senha incorreta</strong> - Verifique a senha no arquivo <code>.env</code></li>';
                echo '<li>❌ <strong>Usuário incorreto</strong> - Verifique o nome do usuário no arquivo <code>.env</code></li>';
                echo '<li>❌ <strong>Usuário sem permissões</strong> - Verifique as permissões do usuário no painel da Hostinger</li>';
                echo '</ul>';
                echo '<p><strong>✅ Solução:</strong></p>';
                echo '<ol>';
                echo '<li>Vá no painel da Hostinger → Banco de Dados MySQL</li>';
                echo '<li>Clique em "Gerenciar" no seu banco</li>';
                echo '<li>Anote o <strong>nome do usuário</strong> e a <strong>senha</strong> corretos</li>';
                echo '<li>Atualize o arquivo <code>.env</code> com as credenciais corretas</li>';
                echo '<li>Verifique se o usuário tem <strong>todas as permissões</strong> (SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP, INDEX)</li>';
                echo '<li>Limpe o cache: <code>php artisan config:clear</code></li>';
                echo '</ol>';
            } elseif (strpos($errorMessage, 'Unknown database') !== false || $errorCode == 1049) {
                echo '<p><strong>Erro 1049 - Unknown Database:</strong></p>';
                echo '<ul>';
                echo '<li>❌ <strong>Nome do banco incorreto</strong> - Verifique o nome do banco no arquivo <code>.env</code></li>';
                echo '<li>❌ <strong>Banco não existe</strong> - O banco de dados não foi criado no painel da Hostinger</li>';
                echo '</ul>';
                echo '<p><strong>✅ Solução:</strong></p>';
                echo '<ol>';
                echo '<li>Vá no painel da Hostinger → Banco de Dados MySQL</li>';
                echo '<li>Verifique se o banco de dados existe</li>';
                echo '<li>Se não existir, crie um novo banco de dados</li>';
                echo '<li>Atualize o arquivo <code>.env</code> com o nome correto do banco</li>';
                echo '</ol>';
            } elseif (strpos($errorMessage, "Can't connect") !== false || $errorCode == 2002) {
                echo '<p><strong>Erro 2002 - Can\'t connect to MySQL server:</strong></p>';
                echo '<ul>';
                echo '<li>❌ <strong>Host incorreto</strong> - Verifique se está usando <code>localhost</code></li>';
                echo '<li>❌ <strong>Porta incorreta</strong> - Verifique se a porta está como <code>3306</code></li>';
                echo '<li>❌ <strong>Serviço MySQL inativo</strong> - Entre em contato com o suporte da Hostinger</li>';
                echo '</ul>';
                echo '<p><strong>✅ Solução:</strong></p>';
                echo '<ol>';
                echo '<li>Verifique se no <code>.env</code> está: <code>DB_HOST=localhost</code></li>';
                echo '<li>Verifique se no <code>.env</code> está: <code>DB_PORT=3306</code></li>';
                echo '<li>Se ainda não funcionar, entre em contato com o suporte da Hostinger</li>';
                echo '</ol>';
            } else {
                echo '<p><strong>Erro desconhecido:</strong></p>';
                echo '<p>Entre em contato com o suporte com a mensagem de erro completa.</p>';
            }
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>🔧 Checklist de Verificação:</strong><br>';
            echo '<ul>';
            echo '<li>✅ Arquivo <code>.env</code> existe na raiz do projeto (não dentro de <code>public</code>)</li>';
            echo '<li>✅ <code>DB_CONNECTION=mysql</code> está configurado</li>';
            echo '<li>✅ <code>DB_HOST=localhost</code> está correto</li>';
            echo '<li>✅ <code>DB_PORT=3306</code> está correto</li>';
            echo '<li>✅ <code>DB_DATABASE</code> está com o nome correto do banco</li>';
            echo '<li>✅ <code>DB_USERNAME</code> está com o nome correto do usuário</li>';
            echo '<li>✅ <code>DB_PASSWORD</code> está com a senha correta (sem espaços ou aspas desnecessárias)</li>';
            echo '<li>✅ Cache foi limpo após alterar o <code>.env</code></li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div class="warning">
            <strong>⚠️ IMPORTANTE:</strong><br>
            Delete este arquivo (<code>test-db-hostinger.php</code>) após o teste por segurança!
        </div>
    </div>
</body>
</html>

