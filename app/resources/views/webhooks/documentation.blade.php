@extends('layouts.dashboard')

@section('title', 'Documentação - Webhooks')
@section('page-title', 'Documentação Webhooks')
@section('page-description', 'Aprenda como usar webhooks para receber notificações em tempo real')

@section('content')
<div class="container mx-auto p-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header -->
        <div class="space-y-2.5">
            <h1 class="text-[28px] font-medium tracking-[-0.56px] text-white">Documentação de Webhooks</h1>
            <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]">Receba notificações em tempo real sobre todas as suas transações</p>
        </div>

        <!-- O que são Webhooks -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">O que são Webhooks?</h2>
            <p class="text-[#AAAAAA] text-sm leading-relaxed mb-4">
                Webhooks são notificações HTTP enviadas automaticamente para sua aplicação quando eventos específicos ocorrem em sua conta. 
                Ao invés de você precisar consultar constantemente nossa API, nós enviamos as informações diretamente para o seu servidor.
            </p>
            <div class="bg-[#1f1f1f] rounded-lg p-4 mt-4 border-l-4 border-[#D4AF37]">
                <p class="text-white font-semibold mb-2 text-sm">⚠️ Importante:</p>
                <p class="text-[#AAAAAA] text-sm leading-relaxed">
                    <strong class="text-white">TODAS as transações disparam webhooks automaticamente:</strong>
                </p>
                <ul class="text-[#AAAAAA] text-sm mt-2 space-y-1 list-disc list-inside ml-2">
                    <li>Quando um PIX é gerado → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.created</code></li>
                    <li>Quando um pagamento é aprovado → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.paid</code></li>
                    <li>Quando há um reembolso → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.refunded</code></li>
                    <li>Quando há chargeback → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.chargeback</code></li>
                    <li>Quando é cancelada → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.cancelled</code></li>
                    <li>Quando falha → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.failed</code></li>
                    <li>Quando expira → <code class="bg-[#161616] px-1.5 py-0.5 rounded text-[#D4AF37] text-xs">transaction.expired</code></li>
                </ul>
                <p class="text-[#AAAAAA] text-sm mt-3">
                    Você não precisa fazer nada além de configurar o webhook - todas as mudanças de status são notificadas automaticamente!
                </p>
            </div>
        </div>

        <!-- Eventos Disponíveis -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Eventos Disponíveis</h2>
            <div class="space-y-3">
                <div class="border-l-4 border-[#D4AF37] pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.created</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando uma nova transação é criada (PIX gerado, boleto emitido, etc.)</p>
                </div>
                <div class="border-l-4 border-emerald-500 pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.paid</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando uma transação é paga e aprovada</p>
                </div>
                <div class="border-l-4 border-rose-500 pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.failed</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando uma transação é recusada ou falha</p>
                </div>
                <div class="border-l-4 border-amber-500 pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.expired</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando uma transação expira sem pagamento</p>
                </div>
                <div class="border-l-4 border-purple-500 pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.refunded</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando uma transação é reembolsada (estornada)</p>
                </div>
                <div class="border-l-4 border-orange-500 pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.chargeback</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando ocorre um chargeback na transação</p>
                </div>
                <div class="border-l-4 border-gray-500 pl-4 py-2">
                    <h3 class="text-white font-semibold mb-1">transaction.cancelled</h3>
                    <p class="text-[#AAAAAA] text-sm">Disparado quando uma transação é cancelada</p>
                </div>
            </div>
        </div>

        <!-- Como Configurar -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Como Configurar</h2>
            <ol class="space-y-4 list-decimal list-inside text-[#AAAAAA] text-sm">
                <li class="pl-2">
                    <span class="text-white font-semibold">Acesse a página de Webhooks</span> no menu lateral
                </li>
                <li class="pl-2">
                    <span class="text-white font-semibold">Clique em "Criar Webhook"</span> e preencha:
                    <ul class="list-disc list-inside ml-4 mt-2 space-y-1">
                        <li>URL do endpoint que receberá as notificações (deve ser HTTPS)</li>
                        <li>Descrição opcional para identificar o webhook</li>
                        <li>Selecione os eventos que deseja receber</li>
                    </ul>
                </li>
                <li class="pl-2">
                    <span class="text-white font-semibold">Salve o Secret Key</span> gerado automaticamente - você precisará dele para validar as requisições
                </li>
                <li class="pl-2">
                    <span class="text-white font-semibold">Configure seu servidor</span> para receber requisições POST no endpoint informado
                </li>
            </ol>
        </div>

        <!-- Estrutura do Payload -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Estrutura do Payload</h2>
            <p class="text-[#AAAAAA] text-sm mb-4">Cada webhook enviado contém as seguintes informações:</p>
            
            <div class="bg-[#1f1f1f] rounded-lg p-4 overflow-x-auto">
                <pre class="text-xs text-[#AAAAAA]"><code>{
  "event": "transaction.paid",
  "timestamp": "2025-01-10T21:39:00Z",
  "data": {
    "transaction_id": "PXB_ABC123",
    "external_id": "28258797",
    "amount": 2.00,
    "fee_amount": 0.10,
    "net_amount": 1.90,
    "currency": "BRL",
    "payment_method": "pix",
    "status": "paid",
    "created_at": "2025-01-10T19:17:00Z",
    "updated_at": "2025-01-10T21:39:00Z",
    "paid_at": "2025-01-10T19:18:00Z",
    "refunded_at": null,
    "customer": {
      "name": "João Silva",
      "email": "joao@exemplo.com"
    }
  }
}</code></pre>
            </div>
        </div>

        <!-- Validação de Assinatura -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Validação de Assinatura</h2>
            <p class="text-[#AAAAAA] text-sm mb-4">
                Para garantir a segurança, cada requisição inclui um header <code class="bg-[#1f1f1f] px-2 py-1 rounded text-[#D4AF37]">X-PixBolt-Signature</code> 
                com uma assinatura HMAC SHA-256. Você deve validar esta assinatura para confirmar que a requisição veio realmente de nós.
            </p>
            
            <div class="bg-[#1f1f1f] rounded-lg p-4 mb-4">
                <h3 class="text-white font-semibold mb-2 text-sm">Exemplo em PHP:</h3>
                <pre class="text-xs text-[#AAAAAA]"><code>$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_PIXBOLT_SIGNATURE'] ?? '';
$secret = 'seu_secret_key_aqui';

$expectedSignature = hash_hmac('sha256', $payload, $secret);

if (hash_equals($expectedSignature, $signature)) {
    // Requisição válida
    $data = json_decode($payload, true);
    // Processar webhook...
} else {
    // Requisição inválida - rejeitar
    http_response_code(401);
    exit;
}</code></pre>
            </div>

            <div class="bg-[#1f1f1f] rounded-lg p-4">
                <h3 class="text-white font-semibold mb-2 text-sm">Exemplo em Node.js:</h3>
                <pre class="text-xs text-[#AAAAAA]"><code>const crypto = require('crypto');

app.post('/webhook', (req, res) => {
  const payload = JSON.stringify(req.body);
  const signature = req.headers['x-pixbolt-signature'];
  const secret = 'seu_secret_key_aqui';
  
  const expectedSignature = crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');
  
  if (signature === expectedSignature) {
    // Requisição válida
    const event = req.body.event;
    const data = req.body.data;
    // Processar webhook...
    res.status(200).send('OK');
  } else {
    // Requisição inválida
    res.status(401).send('Unauthorized');
  }
});</code></pre>
            </div>
        </div>

        <!-- Headers da Requisição -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Headers da Requisição</h2>
            <div class="bg-[#1f1f1f] rounded-lg p-4">
                <table class="w-full text-sm text-[#AAAAAA]">
                    <thead>
                        <tr class="border-b border-[#2d2d2d]">
                            <th class="text-left py-2 text-white font-semibold">Header</th>
                            <th class="text-left py-2 text-white font-semibold">Descrição</th>
                        </tr>
                    </thead>
                    <tbody class="space-y-2">
                        <tr class="border-b border-[#2d2d2d]">
                            <td class="py-2"><code class="bg-[#161616] px-2 py-1 rounded text-[#D4AF37]">Content-Type</code></td>
                            <td class="py-2">application/json</td>
                        </tr>
                        <tr class="border-b border-[#2d2d2d]">
                            <td class="py-2"><code class="bg-[#161616] px-2 py-1 rounded text-[#D4AF37]">User-Agent</code></td>
                            <td class="py-2">PixBolt-Webhook/1.0</td>
                        </tr>
                        <tr>
                            <td class="py-2"><code class="bg-[#161616] px-2 py-1 rounded text-[#D4AF37]">X-PixBolt-Signature</code></td>
                            <td class="py-2">Assinatura HMAC SHA-256 do payload</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Resposta Esperada -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Resposta Esperada</h2>
            <p class="text-[#AAAAAA] text-sm mb-4">
                Seu endpoint deve responder com status HTTP <code class="bg-[#1f1f1f] px-2 py-1 rounded text-[#D4AF37]">200 OK</code> 
                dentro de <strong class="text-white">10 segundos</strong>. Se não recebermos uma resposta válida, 
                tentaremos reenviar o webhook até 3 vezes.
            </p>
            <p class="text-[#AAAAAA] text-sm">
                Após 10 falhas consecutivas, o webhook será automaticamente desativado para evitar spam.
            </p>
        </div>

        <!-- Transações Retidas -->
        <div class="rounded-2xl p-6 bg-[#161616] border-l-4 border-amber-500">
            <h2 class="text-xl font-semibold text-white mb-4">⚠️ Transações Retidas</h2>
            <p class="text-[#AAAAAA] text-sm mb-4">
                Transações que estão em período de retenção (garantia) também disparam webhooks, mas com valores zerados:
            </p>
            <div class="bg-[#1f1f1f] rounded-lg p-4">
                <pre class="text-xs text-[#AAAAAA]"><code>{
  "event": "transaction.paid",
  "data": {
    "amount": 0,
    "fee_amount": 0,
    "net_amount": 0,
    "original_amount": 100.00,
    "retention_type": 2
  }
}</code></pre>
            </div>
            <p class="text-[#AAAAAA] text-sm mt-4">
                Isso permite que você seja notificado sobre a transação, mas não deve creditar o valor na conta do cliente 
                até que a retenção seja liberada.
            </p>
        </div>

        <!-- Boas Práticas -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Boas Práticas</h2>
            <ul class="space-y-2 text-[#AAAAAA] text-sm list-disc list-inside">
                <li>Sempre valide a assinatura antes de processar o webhook</li>
                <li>Implemente idempotência - o mesmo evento pode ser enviado múltiplas vezes</li>
                <li>Responda rapidamente (menos de 10 segundos) para evitar reenvios</li>
                <li>Use HTTPS obrigatoriamente para seus endpoints</li>
                <li>Mantenha logs de todos os webhooks recebidos para debug</li>
                <li>Não processe operações críticas diretamente no handler - use filas</li>
            </ul>
        </div>

        <!-- Testando Webhooks -->
        <div class="rounded-2xl p-6 bg-[#161616]">
            <h2 class="text-xl font-semibold text-white mb-4">Testando Webhooks</h2>
            <p class="text-[#AAAAAA] text-sm mb-4">
                Você pode testar seus webhooks usando ferramentas como:
            </p>
            <ul class="space-y-2 text-[#AAAAAA] text-sm list-disc list-inside mb-4">
                <li><a href="https://webhook.site" target="_blank" class="text-[#D4AF37] hover:underline">webhook.site</a> - Receba webhooks temporários para teste</li>
                <li><a href="https://ngrok.com" target="_blank" class="text-[#D4AF37] hover:underline">ngrok</a> - Exponha seu servidor local para receber webhooks</li>
            </ul>
            <p class="text-[#AAAAAA] text-sm">
                Ou use o botão "Testar Agora" na página de webhooks para disparar um webhook de teste.
            </p>
        </div>

        <!-- Voltar -->
        <div class="flex justify-start">
            <a href="{{ route('webhooks.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold tracking-[-0.24px] bg-[#1f1f1f] hover:bg-[#2a2a2a] text-white rounded-lg transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"></path>
                </svg>
                Voltar para Webhooks
            </a>
        </div>
    </div>
</div>
@endsection
