@extends('layouts.app')

@section('content')
<h1 style="margin-bottom: 30px; color: #333;">📊 Dashboard</h1>

{{-- MÉTRICAS PRINCIPAIS --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Valor Líquido (Hoje)</div>
        <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">R$ {{ number_format($metricas['valor_liquido'] ?? 0, 2, ',', '.') }}</div>
        <div style="font-size: 12px; opacity: 0.8;">Período: Hoje</div>
    </div>
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faturamento Total</div>
        <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">R$ {{ number_format($metricas['faturamento'] ?? 0, 2, ',', '.') }}</div>
        <div style="font-size: 12px; opacity: 0.8;">Total acumulado</div>
    </div>
    <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faturamento Previsto</div>
        <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">R$ {{ number_format($metricas['faturamento_previsto'] ?? 0, 2, ',', '.') }}</div>
        <div style="font-size: 12px; opacity: 0.8;">Pendente de recebimento</div>
    </div>
    <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Vendas Pendentes</div>
        <div style="font-size: 32px; font-weight: bold; margin-bottom: 5px;">R$ {{ number_format($metricas['vendas_pendentes'] ?? 0, 2, ',', '.') }}</div>
        <div style="font-size: 12px; opacity: 0.8;">Aguardando pagamento</div>
    </div>
</div>

{{-- MÉTRICAS SECUNDÁRIAS --}}
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Ticket Médio</div>
        <div style="font-size: 24px; font-weight: bold; color: #333;">R$ {{ number_format($metricas['ticket_medio'] ?? 0, 2, ',', '.') }}</div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Nº de Cobranças</div>
        <div style="font-size: 24px; font-weight: bold; color: #333;">{{ number_format($metricas['numero_cobrancas'] ?? 0, 0, ',', '.') }}</div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Reembolsos</div>
        <div style="font-size: 24px; font-weight: bold; color: #dc3545;">R$ {{ number_format($metricas['reembolsos'] ?? 0, 2, ',', '.') }}</div>
        <div style="font-size: 12px; color: #999;">{{ number_format($metricas['reembolsos_count'] ?? 0, 0, ',', '.') }} cobranças</div>
    </div>
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="font-size: 14px; color: #666; margin-bottom: 8px;">Cancelados</div>
        <div style="font-size: 24px; font-weight: bold; color: #dc3545;">R$ {{ number_format($metricas['cancelados'] ?? 0, 2, ',', '.') }}</div>
        <div style="font-size: 12px; color: #999;">{{ number_format($metricas['cancelados_count'] ?? 0, 0, ',', '.') }} cobranças</div>
    </div>
</div>

{{-- SALDO E ADICIONAR SALDO --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
    {{-- SALDO ATUAL --}}
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 10px 0; font-size: 18px; opacity: 0.9;">Saldo Disponível</h2>
        <p style="font-size: 48px; font-weight: bold; margin: 0;">R$ {{ number_format(auth()->user()->saldo ?? 0, 2, ',', '.') }}</p>
    </div>

    {{-- ADICIONAR SALDO --}}
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #333;">💳 Adicionar Saldo</h3>
        <form action="{{ route('add.balance') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <input type="number" name="valor" placeholder="Valor mínimo: R$ 5,00" min="5" step="0.01" required 
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            <button type="submit" style="width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer;">
                Gerar PIX
            </button>
        </form>
    </div>
</div>

{{-- MENSAGENS DE STATUS --}}
@if(session('status'))
    <div style="background: {{ session('status') == 'sucesso' ? '#d4edda' : '#f8d7da' }}; color: {{ session('status') == 'sucesso' ? '#155724' : '#721c24' }}; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid {{ session('status') == 'sucesso' ? '#28a745' : '#dc3545' }};">
        <strong>{{ session('status') == 'sucesso' ? '✅ Sucesso' : '❌ Erro' }}:</strong> 
        {{ session('message') }}
    </div>
@endif

{{-- PIX GERADO --}}
@php
    $ultimaVenda = auth()->user()->vendas()->orderBy('criado_em', 'desc')->first();
    $ultimaTransacao = $ultimaVenda ? $ultimaVenda->transacao : null;
@endphp

@if($ultimaTransacao && $ultimaTransacao->status == 'pendente' && $ultimaTransacao->chave_pix)
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="margin-top: 0; color: #333;">📱 Pagamento PIX</h3>
        <p style="color: #666; margin-bottom: 20px;">Escaneie o QR Code com o app do seu banco ou copie o código PIX abaixo:</p>
        
        @if($ultimaTransacao->qr_code)
            <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                <img src="data:image/png;base64,{{ $ultimaTransacao->qr_code }}" 
                     alt="QR Code PIX" 
                     style="max-width: 300px; width: 100%; height: auto; border: 2px solid #ddd; border-radius: 8px; padding: 10px; background: white;">
            </div>
        @endif

        @if($ultimaTransacao->chave_pix)
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">Código PIX (Copiar e Colar):</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" 
                           id="pixCode" 
                           value="{{ $ultimaTransacao->chave_pix }}" 
                           readonly 
                           style="flex: 1; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 14px; font-family: monospace; background: #f8f9fa;">
                    <button onclick="copiarPix()" 
                            style="padding: 12px 20px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                        📋 Copiar
                    </button>
                </div>
            </div>
        @endif

        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px;">
            <p style="margin: 5px 0;"><strong>Valor:</strong> R$ {{ number_format($ultimaTransacao->valor, 2, ',', '.') }}</p>
            <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #ff9900; font-weight: bold;">{{ ucfirst($ultimaTransacao->status) }}</span></p>
        </div>
    </div>
@endif

{{-- HISTÓRICO RECENTE --}}
<div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; color: #333;">📊 Transações Recentes</h3>
        <a href="{{ route('cobrancas.index') }}" style="color: #667eea; text-decoration: none; font-weight: bold;">Ver todas →</a>
    </div>

    @php
        $transacoes = auth()->user()->vendas()->orderBy('criado_em', 'desc')->limit(5)->get();
    @endphp

    @if($transacoes->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Data</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Valor</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Status</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transacoes as $venda)
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 12px; color: #333;">{{ \Carbon\Carbon::parse($venda->criado_em)->format('d/m/Y H:i') }}</td>
                            <td style="padding: 12px; font-weight: bold; color: #333;">R$ {{ number_format($venda->valor_bruto, 2, ',', '.') }}</td>
                            <td style="padding: 12px;">
                                @if($venda->status == 'pago')
                                    <span style="color: #28a745; font-weight: bold;">✅ Pago</span>
                                @elseif($venda->status == 'pendente')
                                    <span style="color: #ffc107; font-weight: bold;">⏳ Pendente</span>
                                @elseif($venda->status == 'falhou')
                                    <span style="color: #dc3545; font-weight: bold;">❌ Falhou</span>
                                @endif
                            </td>
                            <td style="padding: 12px;">
                                <a href="{{ route('transactions.show', $venda->id) }}" style="color: #667eea; text-decoration: none; font-weight: bold;">Ver →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: #666; text-align: center; padding: 40px;">Nenhuma transação encontrada.</p>
    @endif
</div>

<script>
function copiarPix() {
    const pixCode = document.getElementById('pixCode');
    pixCode.select();
    pixCode.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        alert('✅ Código PIX copiado com sucesso!');
    } catch (err) {
        navigator.clipboard.writeText(pixCode.value).then(function() {
            alert('✅ Código PIX copiado com sucesso!');
        }, function(err) {
            alert('❌ Erro ao copiar. Tente selecionar e copiar manualmente.');
        });
    }
}
</script>
@endsection
