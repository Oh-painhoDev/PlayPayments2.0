<?php
/**
 * Script para Limpar Cache do Laravel - Hostinger
 * 
 * ⚠️ IMPORTANTE: Delete este arquivo após usar por segurança!
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Limpar Cache - Laravel</title>
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
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border: 1px solid #dee2e6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #21b3dd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #8B0000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧹 Limpar Cache do Laravel</h1>
        
        <?php
        try {
            // Carregar o Laravel
            require __DIR__.'/../vendor/autoload.php';
            
            $app = require_once __DIR__.'/../bootstrap/app.php';
            $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
            
            echo '<div class="success">';
            echo '<strong>✅ Laravel carregado com sucesso!</strong><br>';
            echo '</div>';
            
            // Limpar cache de configuração
            try {
                Artisan::call('config:clear');
                echo '<div class="success">';
                echo '<strong>✅ Cache de configuração limpo!</strong><br>';
                echo '<pre>' . htmlspecialchars(Artisan::output()) . '</pre>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ Erro ao limpar cache de configuração:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
            // Limpar cache de aplicação
            try {
                Artisan::call('cache:clear');
                echo '<div class="success">';
                echo '<strong>✅ Cache de aplicação limpo!</strong><br>';
                echo '<pre>' . htmlspecialchars(Artisan::output()) . '</pre>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ Erro ao limpar cache de aplicação:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
            // Limpar cache de rotas
            try {
                Artisan::call('route:clear');
                echo '<div class="success">';
                echo '<strong>✅ Cache de rotas limpo!</strong><br>';
                echo '<pre>' . htmlspecialchars(Artisan::output()) . '</pre>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>⚠️ Aviso ao limpar cache de rotas:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
            // Limpar cache de views
            try {
                Artisan::call('view:clear');
                echo '<div class="success">';
                echo '<strong>✅ Cache de views limpo!</strong><br>';
                echo '<pre>' . htmlspecialchars(Artisan::output()) . '</pre>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>⚠️ Aviso ao limpar cache de views:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
            // Recriar cache de configuração
            try {
                Artisan::call('config:cache');
                echo '<div class="success">';
                echo '<strong>✅ Cache de configuração recriado!</strong><br>';
                echo '<pre>' . htmlspecialchars(Artisan::output()) . '</pre>';
                echo '</div>';
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>⚠️ Aviso ao recriar cache de configuração:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
            }
            
            echo '<div class="success">';
            echo '<strong>✅ Processo concluído!</strong><br>';
            echo 'Agora teste a conexão com o banco de dados usando o arquivo <code>test-db-hostinger.php</code>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Erro ao inicializar o Laravel:</strong><br>';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            
            echo '<div class="warning">';
            echo '<strong>⚠️ Possíveis causas:</strong><br>';
            echo '<ul>';
            echo '<li>Arquivo <code>.env</code> não existe ou está incorreto</li>';
            echo '<li>Arquivo <code>composer.json</code> não encontrado</li>';
            echo '<li>Dependências não foram instaladas (<code>composer install</code>)</li>';
            echo '</ul>';
            echo '</div>';
        }
        ?>
        
        <div class="warning">
            <strong>⚠️ IMPORTANTE:</strong><br>
            Delete este arquivo (<code>clear-cache-hostinger.php</code>) após usar por segurança!
        </div>
        
        <div style="margin-top: 20px;">
            <a href="test-db-hostinger.php" class="btn">Testar Conexão com Banco</a>
            <a href="index.php" class="btn">Voltar para o Site</a>
        </div>
    </div>
</body>
</html>




