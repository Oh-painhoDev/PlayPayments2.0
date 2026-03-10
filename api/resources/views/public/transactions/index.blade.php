@extends('layouts.app')

@section('content')
<div class="container">
    <h1 style="margin-bottom: 30px; color: #333;">💳 Cobranças</h1>

    {{-- MÉTRICAS --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Valor Líquido (Hoje)</div>
            <div style="font-size: 28px; font-weight: bold;">R$ {{ number_format($metricas['valor_liquido'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faturamento Total</div>
            <div style="font-size: 28px; font-weight: bold;">R$ {{ number_format($metricas['faturamento'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Faturamento Previsto</div>
            <div style="font-size: 28px; font-weight: bold;">R$ {{ number_format($metricas['faturamento_previsto'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Vendas Pendentes</div>
            <div style="font-size: 28px; font-weight: bold;">R$ {{ number_format($metricas['vendas_pendentes'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Ticket Médio</div>
            <div style="font-size: 28px; font-weight: bold;">R$ {{ number_format($metricas['ticket_medio'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Nº de Cobranças</div>
            <div style="font-size: 28px; font-weight: bold;">{{ number_format($metricas['numero_cobrancas'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- FILTROS --}}
    <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <form method="GET" action="{{ route('cobrancas.index') }}" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 15px; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 500; font-size: 14px;">Buscar</label>
                <input type="text" 
                       name="search" 
                       placeholder="Nome, E-mail, CPF, ID..." 
                       value="{{ request('search') }}"
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 500; font-size: 14px;">Status</label>
                <select name="status" style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                    <option value="">Todos</option>
                    <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="pago" {{ request('status') == 'pago' ? 'selected' : '' }}>Pago</option>
                    <option value="falhou" {{ request('status') == 'falhou' ? 'selected' : '' }}>Falhou</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 500; font-size: 14px;">Data Início</label>
                <input type="date" 
                       name="data_inicio" 
                       value="{{ request('data_inicio') }}"
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 500; font-size: 14px;">Data Fim</label>
                <input type="date" 
                       name="data_fim" 
                       value="{{ request('data_fim') }}"
                       style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
            </div>
            <div style="display: flex; gap: 10px;">
                <button type="submit" 
                        style="flex: 1; padding: 12px; background: #667eea; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                    🔍 Filtrar
                </button>
                @if(request()->hasAny(['search', 'status', 'data_inicio', 'data_fim']))
                    <a href="{{ route('cobrancas.index') }}" 
                       style="padding: 12px 20px; background: #6c757d; color: white; border: none; border-radius: 8px; text-decoration: none; font-weight: bold;">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABELA DE COBRANÇAS --}}
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        @if($vendas && $vendas->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">ID</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Cliente</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Tipo</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Valor</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Líquido</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Status</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Data</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vendas as $venda)
                            <tr style="border-bottom: 1px solid #eee; cursor: pointer;" onclick="window.location.href='{{ route('transactions.show', $venda->id) }}'">
                                <td style="padding: 12px; color: #333; font-family: monospace; font-size: 13px;">
                                    #{{ $venda->id }}
                                </td>
                                <td style="padding: 12px; color: #333;">
                                    {{ $venda->transacao->cliente->nome ?? 'N/A' }}
                                </td>
                                <td style="padding: 12px; color: #666;">
                                    <span style="background: #e7f3ff; color: #1976D2; padding: 4px 12px; border-radius: 12px; font-size: 12px;">
                                        PIX
                                    </span>
                                </td>
                                <td style="padding: 12px; font-weight: bold; color: #333;">
                                    R$ {{ number_format($venda->valor_bruto, 2, ',', '.') }}
                                </td>
                                <td style="padding: 12px; color: #666;">
                                    @if($venda->valor_liquido)
                                        R$ {{ number_format($venda->valor_liquido, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 12px;">
                                    @if($venda->status == 'pago')
                                        <span style="color: #28a745; font-weight: bold;">✅ Pago</span>
                                    @elseif($venda->status == 'pendente')
                                        <span style="color: #ffc107; font-weight: bold;">⏳ Pendente</span>
                                    @elseif($venda->status == 'falhou')
                                        <span style="color: #dc3545; font-weight: bold;">❌ Falhou</span>
                                    @else
                                        <span style="color: #666;">{{ ucfirst($venda->status) }}</span>
                                    @endif
                                </td>
                                <td style="padding: 12px; color: #666;">
                                    {{ \Carbon\Carbon::parse($venda->criado_em)->format('d/m/Y H:i') }}
                                </td>
                                <td style="padding: 12px;">
                                    <a href="{{ route('transactions.show', $venda->id) }}" 
                                       style="color: #667eea; text-decoration: none; font-weight: bold;">
                                        Ver →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINAÇÃO --}}
            @if(method_exists($vendas, 'links'))
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $vendas->links() }}
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <div style="font-size: 64px; margin-bottom: 20px;">📭</div>
                <h3 style="color: #666; margin: 0 0 10px 0;">Nenhuma cobrança encontrada</h3>
                <p style="color: #999;">Comece a processar pagamentos para ver suas cobranças aqui.</p>
            </div>
        @endif
    </div>
</div>
@endsection

