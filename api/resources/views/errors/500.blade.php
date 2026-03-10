<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro do Servidor - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #0d0d0d 0%, #1a1a1a 100%);
            color: #f4f4f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 40px;
            background: #1a1a1a;
            border-radius: 12px;
            border: 1px solid #2c2c2e;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #f4f4f5;
        }
        .error-message {
            font-size: 16px;
            color: #a1a1aa;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
        }
        .btn-primary {
            background: #dc2626;
            color: #ffffff;
        }
        .btn-primary:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #2c2c2e;
            color: #f4f4f5;
            border: 1px solid #3c3c3e;
        }
        .btn-secondary:hover {
            background: #3c3c3e;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1 class="error-title">Erro Interno do Servidor</h1>
        <p class="error-message">
            {{ $message ?? 'Ocorreu um erro inesperado. Nossa equipe foi notificada e está trabalhando para resolver o problema.' }}
        </p>
        <div class="error-actions">
            <a href="{{ url('/') }}" class="btn btn-primary">Voltar ao Início</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
</body>
</html>

