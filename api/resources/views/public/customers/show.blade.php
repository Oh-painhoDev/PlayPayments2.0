@extends('layouts.app')

@section('content')
<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="margin: 0; color: #333;">👤 Detalhes do Cliente</h1>
        <a href="{{ route('customers.index') }}" style="color: #667eea; text-decoration: none; font-weight: bold;">← Voltar</a>
    </div>

    {{-- INFORMAÇÕES DO CLIENTE --}}
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h2 style="margin-top: 0; color: #333; margin-bottom: 20px;">Informações Pessoais</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div>
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Nome</div>
                <div style="color: #333; font-size: 18px; font-weight: bold;">{{ $cliente->nome }}</div>
            </div>
            <div>
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">E-mail</div>
                <div style="color: #333; font-size: 18px;">{{ $cliente->email }}</div>
            </div>
            <div>
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">CPF</div>
                <div style="color: #333; font-size: 18px;">{{ $cliente->cpf ?? '-' }}</div>
            </div>
            <div>
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Telefone</div>
                <div style="color: #333; font-size: 18px;">{{ $cliente->telefone ?? '-' }}</div>
            </div>
        </div>
        @if($cliente->endereco)
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <div style="color: #666; font-size: 14px; margin-bottom: 5px;">Endereço</div>
                <div style="color: #333; font-size: 16px;">{{ $cliente->endereco }}</div>
            </div>
        @endif
    </div>

    {{-- TRANSAÇÕES DO CLIENTE --}}
    <div style="background: white; padding: 30px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #333; margin-bottom: 20px;">Transações</h2>
        
        @if($cliente->transacoes && $cliente->transacoes->count() > 0)
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
                        @foreach($cliente->transacoes as $transacao)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; color: #333;">
                                    {{ \Carbon\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td style="padding: 12px; font-weight: bold; color: #333;">
                                    R$ {{ number_format($transacao->valor, 2, ',', '.') }}
                                </td>
                                <td style="padding: 12px;">
                                    @if($transacao->status == 'pago')
                                        <span style="color: #28a745; font-weight: bold;">✅ Pago</span>
                                    @elseif($transacao->status == 'pendente')
                                        <span style="color: #ffc107; font-weight: bold;">⏳ Pendente</span>
                                    @elseif($transacao->status == 'reembolsado')
                                        <span style="color: #dc3545; font-weight: bold;">↩️ Reembolsado</span>
                                    @else
                                        <span style="color: #666;">{{ ucfirst($transacao->status) }}</span>
                                    @endif
                                </td>
                                <td style="padding: 12px;">
                                    @if($transacao->venda)
                                        <a href="{{ route('transactions.show', $transacao->venda->id) }}" 
                                           style="color: #667eea; text-decoration: none; font-weight: bold;">
                                            Ver →
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: #666; text-align: center; padding: 40px;">Nenhuma transação encontrada para este cliente.</p>
        @endif
    </div>
</div>
@endsection

