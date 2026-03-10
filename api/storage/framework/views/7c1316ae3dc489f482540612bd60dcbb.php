<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Não Autorizado - API</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            padding: 50px 40px;
            text-align: center;
        }
        
        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(255, 107, 107, 0);
            }
        }
        
        .icon svg {
            width: 60px;
            height: 60px;
            color: white;
        }
        
        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
        }
        
        .subtitle {
            font-size: 18px;
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .message {
            background: #f7fafc;
            border-left: 4px solid #ff6b6b;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .message p {
            color: #4a5568;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 10px;
        }
        
        .message p:last-child {
            margin-bottom: 0;
        }
        
        .code-block {
            background: #2d3748;
            color: #68d391;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            text-align: left;
            margin: 15px 0;
            overflow-x: auto;
        }
        
        .code-block code {
            color: #68d391;
        }
        
        .info-box {
            background: #edf2f7;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .info-box h3 {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 15px;
        }
        
        .info-box ul {
            list-style: none;
            text-align: left;
        }
        
        .info-box li {
            color: #4a5568;
            padding: 8px 0;
            padding-left: 25px;
            position: relative;
        }
        
        .info-box li:before {
            content: "→";
            position: absolute;
            left: 0;
            color: #667eea;
            font-weight: bold;
        }
        
        .button {
            display: inline-block;
            margin-top: 30px;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        @media (max-width: 640px) {
            .container {
                padding: 30px 20px;
            }
            
            h1 {
                font-size: 24px;
            }
            
            .subtitle {
                font-size: 16px;
            }
            
            .icon {
                width: 100px;
                height: 100px;
            }
            
            .icon svg {
                width: 50px;
                height: 50px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>
        
        <h1>Acesso Não Autorizado</h1>
        <p class="subtitle">Você precisa estar autenticado para acessar esta API</p>
        
        <div class="message">
            <p><strong>Erro 401:</strong> Não autorizado</p>
            <p>Esta API requer autenticação via tokens. Você precisa fornecer suas credenciais de API para acessar os recursos.</p>
        </div>
        
        <div class="info-box">
            <h3>Como autenticar:</h3>
            <ul>
                <li>Use sua <strong>Public Key</strong> ou <strong>Private Key</strong> no header Authorization</li>
                <li>Para criar PIX, você precisa de <strong>ambos</strong> os tokens</li>
                <li>Formato: <code>Authorization: Bearer SEU_TOKEN_AQUI</code></li>
            </ul>
        </div>
        
        <div class="code-block">
            <code>
# Exemplo de requisição autenticada:<br>
curl -X GET "<?php echo e(url('/api/v1/transactions')); ?>" \<br>
&nbsp;&nbsp;-H "Authorization: Bearer PB-playpayments-SEU-TOKEN-AQUI"
            </code>
        </div>
        
        <a href="<?php echo e(url('/')); ?>" class="button">Voltar ao Início</a>
    </div>
</body>
</html>

<?php /**PATH C:\xampp\htdocs\resources\views/api/unauthorized.blade.php ENDPATH**/ ?>