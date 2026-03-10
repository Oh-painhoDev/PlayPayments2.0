@extends('layouts.dashboard')

@section('title', 'Configurações - Taxas')
@section('page-title', 'Taxas e Tarifas')
@section('page-description', 'Confira as taxas aplicadas para cada método de pagamento')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Taxas e Tarifas</h1>
                <p class="text-green-100">Confira as taxas aplicadas para cada método de pagamento</p>
            </div>
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Fees Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- PIX -->
        <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm hover:border-emerald-300 transition-all">
            <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <span class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white text-xs font-bold rounded-xl border border-white/30">
                        Mais Popular
                    </span>
                </div>
                <h3 class="text-2xl font-bold text-white mt-4">PIX</h3>
                <p class="text-green-100 text-sm">Transferência instantânea</p>
            </div>
            
            <div class="p-6 space-y-3">
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Taxa Percentual</span>
                        <span class="text-gray-900 font-bold">{{ number_format($formattedFees['pix']['percentage'], 2) }}%</span>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Taxa Fixa</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['pix']['fixed'], 2, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Valor Mínimo</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['pix']['min'], 2, ',', '.') }}</span>
                    </div>
                </div>
                
                @if(isset($formattedFees['pix']['max']) && $formattedFees['pix']['max'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Valor Máximo</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['pix']['max'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                @if(isset($formattedFees['pix']['min_transaction']) && $formattedFees['pix']['min_transaction'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Transação Mínima</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['pix']['min_transaction'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                @if(isset($formattedFees['pix']['max_transaction']) && $formattedFees['pix']['max_transaction'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Transação Máxima</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['pix']['max_transaction'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="px-6 pb-6">
                <div class="bg-green-50 border border-green-200 rounded-xl p-3">
                    <p class="text-xs text-green-700 font-medium text-center">
                        Aprovação instantânea • Disponível 24/7
                    </p>
                </div>
            </div>
        </div>

        <!-- Credit Card -->
        <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm hover:border-emerald-300 transition-all">
            <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white mt-4">Cartão de Crédito</h3>
                <p class="text-green-100 text-sm">Parcelamento disponível</p>
            </div>
            
            <div class="p-6 space-y-3">
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Taxa Percentual</span>
                        <span class="text-gray-900 font-bold">{{ number_format($formattedFees['credit_card']['percentage'], 2) }}%</span>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Taxa Fixa</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['credit_card']['fixed'], 2, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Valor Mínimo</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['credit_card']['min'], 2, ',', '.') }}</span>
                    </div>
                </div>
                
                @if(isset($formattedFees['credit_card']['max']) && $formattedFees['credit_card']['max'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Valor Máximo</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['credit_card']['max'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                @if(isset($formattedFees['credit_card']['min_transaction']) && $formattedFees['credit_card']['min_transaction'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Transação Mínima</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['credit_card']['min_transaction'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                @if(isset($formattedFees['credit_card']['max_transaction']) && $formattedFees['credit_card']['max_transaction'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Transação Máxima</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['credit_card']['max_transaction'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="px-6 pb-6">
                <div class="bg-green-50 border border-green-200 rounded-xl p-3">
                    <p class="text-xs text-green-700 font-medium text-center">
                        Até 12x sem juros • Aprovação em segundos
                    </p>
                </div>
            </div>
        </div>

        <!-- Bank Slip -->
        <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm hover:border-emerald-300 transition-all">
            <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-white mt-4">Boleto Bancário</h3>
                <p class="text-green-100 text-sm">Pagamento tradicional</p>
            </div>
            
            <div class="p-6 space-y-3">
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Taxa Percentual</span>
                        <span class="text-gray-900 font-bold">{{ number_format($formattedFees['bank_slip']['percentage'], 2) }}%</span>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Taxa Fixa</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['bank_slip']['fixed'], 2, ',', '.') }}</span>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Valor Mínimo</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['bank_slip']['min'], 2, ',', '.') }}</span>
                    </div>
                </div>
                
                @if(isset($formattedFees['bank_slip']['max']) && $formattedFees['bank_slip']['max'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Valor Máximo</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['bank_slip']['max'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                @if(isset($formattedFees['bank_slip']['min_transaction']) && $formattedFees['bank_slip']['min_transaction'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Transação Mínima</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['bank_slip']['min_transaction'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
                
                @if(isset($formattedFees['bank_slip']['max_transaction']) && $formattedFees['bank_slip']['max_transaction'])
                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-3">
                    <div class="flex justify-between items-center">
                        <span class="text-emerald-700 text-sm font-semibold">Transação Máxima</span>
                        <span class="text-gray-900 font-bold">R$ {{ number_format($formattedFees['bank_slip']['max_transaction'], 2, ',', '.') }}</span>
                    </div>
                </div>
                @endif
            </div>
            
            <div class="px-6 pb-6">
                <div class="bg-green-50 border border-green-200 rounded-xl p-3">
                    <p class="text-xs text-green-700 font-medium text-center">
                        Vencimento em 3 dias • Confirmação em 1-2 dias
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
