@extends('layouts.app')

@section('content')
<div class="container">
    <h1 style="margin-bottom: 30px; color: #333;">💰 Suas Taxas</h1>

    <div style="background: #e7f3ff; padding: 20px; border-radius: 12px; margin-bottom: 30px; border-left: 4px solid #2196F3;">
        <p style="margin: 0; color: #1976D2;">
            ℹ️ Estas são as taxas aplicadas nas suas transações. As taxas podem variar conforme o tipo de operação.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
        {{-- TAXAS DE SAQUE PIX --}}
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">💸</span> Saque PIX
            </h3>
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                    <span style="color: #666;">Taxa Fixa:</span>
                    <strong style="color: #333;">R$ {{ number_format($taxaUsuario->saque_pix_fixo ?? 5.00, 2, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <span style="color: #666;">Taxa Percentual:</span>
                    <strong style="color: #333;">{{ number_format($taxaUsuario->saque_pix_percentual ?? 1.00, 2, ',', '.') }}%</strong>
                </div>
            </div>
        </div>

        {{-- TAXAS DE SAQUE CRIPTO --}}
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">₿</span> Saque Cripto
            </h3>
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                    <span style="color: #666;">Taxa Fixa:</span>
                    <strong style="color: #333;">R$ {{ number_format($taxaUsuario->saque_cripto_fixo ?? 25.00, 2, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <span style="color: #666;">Taxa Percentual:</span>
                    <strong style="color: #333;">{{ number_format($taxaUsuario->saque_cripto_percentual ?? 10.00, 2, ',', '.') }}%</strong>
                </div>
            </div>
        </div>

        {{-- TAXAS DE PIX PAGO --}}
        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="margin-top: 0; color: #333; display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">💳</span> PIX Pago
            </h3>
            <div style="margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                    <span style="color: #666;">Taxa Fixa:</span>
                    <strong style="color: #333;">R$ {{ number_format($taxaUsuario->pix_pago_fixo ?? 1.95, 2, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <span style="color: #666;">Taxa Percentual:</span>
                    <strong style="color: #333;">{{ number_format($taxaUsuario->pix_pago_percentual ?? 3.50, 2, ',', '.') }}%</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- EXEMPLO DE CÁLCULO --}}
    <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-top: 0; color: #333;">📊 Exemplo de Cálculo</h3>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 15px;">
            <p style="margin: 0 0 10px 0; color: #666;"><strong>Exemplo de Saque PIX:</strong></p>
            <p style="margin: 5px 0; color: #333;">Valor solicitado: <strong>R$ 100,00</strong></p>
            <p style="margin: 5px 0; color: #333;">Taxa fixa: <strong>R$ {{ number_format($taxaUsuario->saque_pix_fixo ?? 5.00, 2, ',', '.') }}</strong></p>
            <p style="margin: 5px 0; color: #333;">Taxa percentual ({{ number_format($taxaUsuario->saque_pix_percentual ?? 1.00, 2, ',', '.') }}%): <strong>R$ {{ number_format(100 * (($taxaUsuario->saque_pix_percentual ?? 1.00) / 100), 2, ',', '.') }}</strong></p>
            <p style="margin: 15px 0 0 0; padding-top: 15px; border-top: 2px solid #ddd; color: #333; font-size: 18px;">
                <strong>Total debitado: R$ {{ number_format(100 + ($taxaUsuario->saque_pix_fixo ?? 5.00) + (100 * (($taxaUsuario->saque_pix_percentual ?? 1.00) / 100)), 2, ',', '.') }}</strong>
            </p>
        </div>
    </div>
</div>
@endsection

