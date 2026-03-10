@extends('layouts.app')

@section('content')
<div class="container">
    <h1 style="margin-bottom: 30px; color: #333;">💳 Carteira</h1>

    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #28a745;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            @foreach($errors->all() as $error)
                ❌ {{ $error }}<br>
            @endforeach
        </div>
    @endif

    {{-- SALDO ATUAL --}}
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 10px 0; font-size: 18px; opacity: 0.9;">Saldo Disponível</h2>
        <p style="font-size: 48px; font-weight: bold; margin: 0;">R$ {{ number_format($user->saldo ?? 0, 2, ',', '.') }}</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        {{-- ADICIONAR SALDO --}}
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">➕</span> Adicionar Saldo
            </h3>
            <form action="{{ route('add.balance') }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 500;">Valor (mínimo R$ 5,00)</label>
                    <input type="number" name="valor" placeholder="0.00" min="5" step="0.01" required 
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
                <button type="submit" style="width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s;">
                    Gerar PIX
                </button>
            </form>
        </div>

        {{-- SACAR DINHEIRO --}}
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">💸</span> Sacar Dinheiro
            </h3>
            <form action="{{ route('wallet.sacar') }}" method="POST">
                @csrf
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; color: #666; font-weight: 500;">Valor (mínimo R$ 10,00)</label>
                    <input type="number" name="valor" placeholder="0.00" min="10" step="0.01" required 
                           style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
                <div style="background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; color: #666;">
                    <strong>Taxa:</strong> R$ {{ number_format($taxaUsuario->saque_pix_fixo ?? 5.00, 2, ',', '.') }} + {{ number_format($taxaUsuario->saque_pix_percentual ?? 1.00, 2, ',', '.') }}%
                </div>
                <button type="submit" style="width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s;">
                    Solicitar Saque
                </button>
            </form>
        </div>
    </div>

    {{-- HISTÓRICO DE SAQUES --}}
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #333;">📊 Histórico de Saques</h3>
        
        @if($saques && $saques->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Data</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Valor</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Taxa</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; color: #666;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($saques as $saque)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px; color: #333;">
                                    {{ \Carbon\Carbon::parse($saque->criado_em)->format('d/m/Y H:i') }}
                                </td>
                                <td style="padding: 12px; font-weight: bold; color: #333;">
                                    R$ {{ number_format($saque->valor, 2, ',', '.') }}
                                </td>
                                <td style="padding: 12px; color: #666;">
                                    R$ {{ number_format(($saque->taxa_fixa ?? 5.00) + ($saque->valor * (($saque->taxa_percentual ?? 1.00) / 100)), 2, ',', '.') }}
                                </td>
                                <td style="padding: 12px;">
                                    @if($saque->status == 'pago')
                                        <span style="color: #28a745; font-weight: bold;">✅ Pago</span>
                                    @elseif($saque->status == 'aprovado')
                                        <span style="color: #17a2b8; font-weight: bold;">⏳ Aprovado</span>
                                    @elseif($saque->status == 'analise')
                                        <span style="color: #ffc107; font-weight: bold;">🔍 Em Análise</span>
                                    @elseif($saque->status == 'rejeitado')
                                        <span style="color: #dc3545; font-weight: bold;">❌ Rejeitado</span>
                                    @else
                                        <span style="color: #666; font-weight: bold;">📝 Solicitado</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: #666; text-align: center; padding: 40px;">Nenhum saque realizado ainda.</p>
        @endif
    </div>
</div>
@endsection
