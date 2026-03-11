<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste PIX - UTMify</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">🧪 Teste PIX - UTMify</h1>
        
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Criar PIX de Teste</h2>
            
            <form id="testPixForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">User ID</label>
                    <input 
                        type="number" 
                        id="userId" 
                        name="user_id" 
                        value="2"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                        placeholder="ID do usuário"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Valor (R$)</label>
                    <input 
                        type="number" 
                        id="amount" 
                        name="amount" 
                        value="10.00"
                        step="0.01"
                        min="1"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                        placeholder="Valor do PIX"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium mb-2">Tempo de Expiração</label>
                    <select 
                        id="pixExpiresInMinutes" 
                        name="pix_expires_in_minutes"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white"
                    >
                        <option value="15">15 minutos</option>
                        <option value="30">30 minutos</option>
                        <option value="60">1 hora</option>
                        <option value="120">2 horas</option>
                        <option value="180">3 horas</option>
                        <option value="360">6 horas</option>
                        <option value="720">12 horas</option>
                        <option value="1440" selected>1 dia (24 horas)</option>
                        <option value="2880">2 dias</option>
                        <option value="4320">3 dias</option>
                        <option value="7200">5 dias</option>
                        <option value="10080">7 dias (1 semana)</option>
                        <option value="21600">15 dias</option>
                        <option value="43200">30 dias (1 mês)</option>
                        <option value="86400">60 dias (2 meses)</option>
                        <option value="129600">90 dias (3 meses) - máximo</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Tempo que o QR Code PIX ficará válido. Valores menores que 24 horas usam expiração em segundos (preciso). 
                        Valores de 1 dia ou mais usam expiração em dias inteiros (1-90 dias).
                    </p>
                </div>
                
                <button 
                    type="submit" 
                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 rounded-lg font-semibold transition-colors"
                >
                    🚀 Gerar PIX de Teste
                </button>
            </form>
        </div>
        
        <div id="result" class="hidden bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Resultado</h2>
            
            <div id="statusSummary" class="mb-4 p-4 rounded-lg"></div>
            
            <div class="mb-4">
                <button onclick="toggleJson()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm">
                    <span id="toggleText">Mostrar</span> JSON Completo
                </button>
            </div>
            
            <pre id="resultContent" class="bg-gray-900 p-4 rounded-lg overflow-auto text-sm hidden"></pre>
        </div>
        
        <div id="loading" class="hidden text-center py-8">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
            <p class="mt-2">Criando PIX...</p>
        </div>
    </div>
    
    <script>
        document.getElementById('testPixForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('userId').value;
            const amount = document.getElementById('amount').value;
            const pixExpiresInMinutes = document.getElementById('pixExpiresInMinutes').value;
            const resultDiv = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            const loadingDiv = document.getElementById('loading');
            
            resultDiv.classList.add('hidden');
            loadingDiv.classList.remove('hidden');
            
            try {
                const response = await fetch('/api/test-pix-simple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: parseInt(userId),
                        amount: parseFloat(amount),
                        pix_expires_in_minutes: parseInt(pixExpiresInMinutes),
                    }),
                });
                
                const data = await response.json();
                
                loadingDiv.classList.add('hidden');
                resultDiv.classList.remove('hidden');
                
                resultContent.textContent = JSON.stringify(data, null, 2);
                
                // Mostrar resumo do status
                const statusDiv = document.getElementById('statusSummary');
                let statusHtml = '';
                
                if (data.success) {
                    statusHtml += '<div class="bg-green-900/30 border border-green-500 rounded-lg p-4 mb-4">';
                    statusHtml += '<h3 class="text-green-400 font-semibold mb-2">✅ PIX Criado com Sucesso</h3>';
                    statusHtml += '<p class="text-sm text-gray-300">Transaction ID: <code class="bg-gray-800 px-2 py-1 rounded">' + data.transaction.transaction_id + '</code></p>';
                    statusHtml += '<p class="text-sm text-gray-300">Valor: R$ ' + parseFloat(data.transaction.amount).toFixed(2) + '</p>';
                    if (data.transaction.expires_at) {
                        const expiresDate = new Date(data.transaction.expires_at);
                        statusHtml += '<p class="text-sm text-gray-300">Expira em: ' + expiresDate.toLocaleString('pt-BR') + '</p>';
                    } else if (data.pix && data.pix.expires_in_minutes) {
                        const hours = Math.floor(data.pix.expires_in_minutes / 60);
                        const minutes = data.pix.expires_in_minutes % 60;
                        let expiresText = '';
                        if (hours > 0) {
                            expiresText = hours + (hours === 1 ? ' hora' : ' horas');
                            if (minutes > 0) {
                                expiresText += ' e ' + minutes + (minutes === 1 ? ' minuto' : ' minutos');
                            }
                        } else {
                            expiresText = minutes + (minutes === 1 ? ' minuto' : ' minutos');
                        }
                        statusHtml += '<p class="text-sm text-gray-300">Tempo de expiração: ' + expiresText + '</p>';
                    }
                    statusHtml += '</div>';
                    
                    // Status UTMify
                    if (data.utmify) {
                        if (data.utmify.sent) {
                            statusHtml += '<div class="bg-green-900/30 border border-green-500 rounded-lg p-4 mb-4">';
                            statusHtml += '<h3 class="text-green-400 font-semibold mb-2">✅ UTMify: Enviado com Sucesso</h3>';
                            statusHtml += '<p class="text-sm text-gray-300">Integração: ' + (data.utmify.integrations[0]?.name || 'N/A') + '</p>';
                            statusHtml += '</div>';
                        } else {
                            statusHtml += '<div class="bg-red-900/30 border border-red-500 rounded-lg p-4 mb-4">';
                            statusHtml += '<h3 class="text-red-400 font-semibold mb-2">❌ UTMify: Não Enviado</h3>';
                            
                            if (data.utmify.status === 'token_invalid' && data.utmify.help) {
                                statusHtml += '<p class="text-sm text-red-300 mb-2"><strong>Problema:</strong> ' + data.utmify.help.problem + '</p>';
                                statusHtml += '<div class="bg-gray-900 p-3 rounded mt-2">';
                                statusHtml += '<p class="text-xs text-gray-400 mb-2"><strong>Solução:</strong></p>';
                                statusHtml += '<ol class="list-decimal list-inside text-xs text-gray-300 space-y-1">';
                                data.utmify.help.solution.forEach(step => {
                                    statusHtml += '<li>' + step + '</li>';
                                });
                                statusHtml += '</ol>';
                                statusHtml += '</div>';
                            } else if (data.utmify.error) {
                                statusHtml += '<p class="text-sm text-red-300">Erro: ' + data.utmify.error + '</p>';
                            } else {
                                statusHtml += '<p class="text-sm text-red-300">Verifique os logs para mais detalhes</p>';
                            }
                            statusHtml += '</div>';
                        }
                    }
                } else {
                    statusHtml += '<div class="bg-red-900/30 border border-red-500 rounded-lg p-4 mb-4">';
                    statusHtml += '<h3 class="text-red-400 font-semibold mb-2">❌ Erro ao Criar PIX</h3>';
                    statusHtml += '<p class="text-sm text-red-300">' + (data.error || 'Erro desconhecido') + '</p>';
                    statusHtml += '</div>';
                }
                
                statusDiv.innerHTML = statusHtml;
                
                if (data.success) {
                    resultContent.classList.add('text-green-400');
                    resultContent.classList.remove('text-red-400');
                } else {
                    resultContent.classList.add('text-red-400');
                    resultContent.classList.remove('text-green-400');
                }
            } catch (error) {
                loadingDiv.classList.add('hidden');
                resultDiv.classList.remove('hidden');
                
                const statusDiv = document.getElementById('statusSummary');
                statusDiv.innerHTML = '<div class="bg-red-900/30 border border-red-500 rounded-lg p-4 mb-4">' +
                    '<h3 class="text-red-400 font-semibold mb-2">❌ Erro na Requisição</h3>' +
                    '<p class="text-sm text-red-300">' + error.message + '</p>' +
                    '</div>';
                
                resultContent.textContent = 'Erro: ' + error.message;
                resultContent.classList.add('text-red-400');
            }
        });
        
        function toggleJson() {
            const content = document.getElementById('resultContent');
            const toggleText = document.getElementById('toggleText');
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                toggleText.textContent = 'Ocultar';
            } else {
                content.classList.add('hidden');
                toggleText.textContent = 'Mostrar';
            }
        }
    </script>
</body>
</html>

