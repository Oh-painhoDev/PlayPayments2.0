@extends('layouts.dashboard')

@section('title', 'Detalhes da Venda')
@section('page-title', 'Detalhes do Pedido')
@section('page-description', 'Comprovante e informações da transação')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Vendas
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Principal (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Comprovante de Pagamento -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <!-- Header Verde -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white">Comprovante de Pagamento</h2>
                        @php
                            $displayStatus = $transaction->is_retained ? 'pending' : $transaction->status;
                            $statusConfig = [
                                'pending' => ['label' => 'Aguardando Pagamento', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'paid' => ['label' => 'Pago', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'cancelled' => ['label' => 'Cancelado', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                'expired' => ['label' => 'Expirado', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ];
                            $config = $statusConfig[$displayStatus] ?? ['label' => ucfirst($displayStatus), 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'];
                        @endphp
                        <div class="flex items-center text-white">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $config['icon'] }}"/>
                            </svg>
                            <span class="font-semibold">{{ $config['label'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Valor Principal -->
                <div class="px-6 py-8 border-b border-gray-200">
                    <div class="text-center">
                        <div class="text-sm text-gray-600 mb-2">Valor Total</div>
                        <div class="text-4xl font-bold text-gray-900">{{ $transaction->formatted_amount }}</div>
                    </div>
                </div>

                <!-- Detalhes da Transação -->
                <div class="px-6 py-6 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Detalhes da Transação
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">ID da Transação</div>
                            <div class="text-sm font-mono font-semibold text-gray-900">{{ $transaction->external_id ?: $transaction->transaction_id }}</div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Método de Pagamento</div>
                            <div class="text-sm font-semibold text-gray-900">{{ strtoupper($transaction->payment_method) }}</div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Taxa de Intermediação</div>
                            <div class="text-sm font-semibold text-gray-900">{{ $transaction->formatted_fee_amount }}</div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Valor Líquido</div>
                            <div class="text-sm font-semibold text-green-600">{{ $transaction->formatted_net_amount }}</div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Data de Criação</div>
                            <div class="text-sm font-semibold text-gray-900">{{ $transaction->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        
                        @if($transaction->expires_at)
                        <div class="bg-white rounded-lg p-4">
                            <div class="text-sm text-gray-600 mb-1">Vencimento</div>
                            <div class="text-sm font-semibold text-gray-900">{{ $transaction->expires_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Informações do Cliente -->
                <div class="px-6 py-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Informações do Cliente
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-start">
                            <div class="text-sm text-gray-600 w-24">Nome:</div>
                            <div class="text-sm font-semibold text-gray-900 flex-1">{{ $transaction->customer_data['name'] ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="text-sm text-gray-600 w-24">E-mail:</div>
                            <div class="text-sm font-semibold text-gray-900 flex-1">{{ $transaction->customer_data['email'] ?? 'N/A' }}</div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="text-sm text-gray-600 w-24">CPF/CNPJ:</div>
                            <div class="text-sm font-semibold text-gray-900 flex-1">{{ $transaction->customer_data['document'] ?? 'N/A' }}</div>
                        </div>
                        
                        @if(isset($transaction->customer_data['phone']) && $transaction->customer_data['phone'])
                        <div class="flex items-start">
                            <div class="text-sm text-gray-600 w-24">Telefone:</div>
                            <div class="text-sm font-semibold text-gray-900 flex-1">{{ $transaction->customer_data['phone'] }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar (1/3) -->
        <div class="space-y-6">
            <!-- Ações Rápidas -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
                
                <div class="space-y-3">
                    <!-- Botão Visualizar PIX -->
                    @php
                        // Suportar ambas as estruturas: payment_data['payment_data']['pix'] e payment_data['pix']
                        $pixPayload = $transaction->payment_data['payment_data']['pix']['payload'] ?? 
                                      $transaction->payment_data['payment_data']['pix']['qrcode'] ?? 
                                      $transaction->payment_data['payment_data']['pix']['emv'] ??
                                      $transaction->payment_data['pix']['payload'] ?? 
                                      $transaction->payment_data['pix']['qrcode'] ?? 
                                      $transaction->payment_data['pix']['emv'] ?? null;
                    @endphp
                    @if($transaction->payment_method === 'pix' && $pixPayload)
                    <button 
                        onclick="showPixModal()"
                        class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-4 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Visualizar PIX
                    </button>
                    @endif
                    
                    <button 
                        onclick="refreshTransaction()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-semibold transition-all flex items-center justify-center"
                        id="refreshBtn"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Atualizar Status
                    </button>
                </div>
            </div>

            <!-- Resumo do Pedido -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo do Pedido</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $transaction->metadata['description'] ?? 'Produto/Serviço' }}
                            </div>
                            <div class="text-xs text-gray-600">Quantidade: 1</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $transaction->formatted_amount }}
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-lg font-bold text-gray-900 pt-2">
                        <span>Total:</span>
                        <span class="text-green-600">{{ $transaction->formatted_amount }}</span>
                    </div>
                </div>
            </div>

            <!-- Saldo Atual -->
            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-md p-6 border-2 border-green-200">
                <h3 class="text-sm font-semibold text-green-800 mb-2">Saldo Disponível</h3>
                <div class="text-3xl font-bold text-green-600">{{ auth()->user()->formatted_wallet_balance }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal PIX -->
@php
    // Suportar ambas as estruturas: payment_data['payment_data']['pix'] e payment_data['pix']
    $pixPayload = $transaction->payment_data['payment_data']['pix']['payload'] ?? 
                  $transaction->payment_data['payment_data']['pix']['qrcode'] ?? 
                  $transaction->payment_data['payment_data']['pix']['emv'] ??
                  $transaction->payment_data['pix']['payload'] ?? 
                  $transaction->payment_data['pix']['qrcode'] ?? 
                  $transaction->payment_data['pix']['emv'] ?? null;
    $pixEncodedImage = $transaction->payment_data['payment_data']['pix']['encodedImage'] ?? 
                       $transaction->payment_data['pix']['encodedImage'] ?? null;
    $pixQrCodeUrl = $transaction->payment_data['payment_data']['pix']['qr_code_url'] ?? 
                    $transaction->payment_data['pix']['qr_code_url'] ?? null;
@endphp
@if($transaction->payment_method === 'pix' && $pixPayload)
<div id="pixModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closePixModal()">
    <div class="bg-white rounded-2xl max-w-lg w-full shadow-2xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Pagamento PIX</h3>
            <button onclick="closePixModal()" class="text-white hover:text-gray-200 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6">
            <!-- QR Code -->
            @if($pixEncodedImage && !empty($pixEncodedImage))
            <div class="text-center">
                <div class="text-sm font-semibold text-gray-900 mb-3">Escaneie o QR Code</div>
                <div class="flex justify-center bg-white p-4 rounded-lg border-2 border-gray-200">
                    @php
                        $qrCode = $pixEncodedImage;
                        $decodedQr = base64_decode($qrCode);
                        $isSvg = str_contains($decodedQr, '<svg') || str_contains($decodedQr, '<?xml');
                        // Sanitize SVG to prevent XSS - only allow safe SVG elements
                        if ($isSvg) {
                            $decodedQr = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $decodedQr);
                            $decodedQr = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $decodedQr);
                            $decodedQr = preg_replace('/javascript:/i', '', $decodedQr);
                        }
                    @endphp
                    
                    <div class="w-64 h-64 flex items-center justify-center bg-white">
                        {!! $decodedQr !!}
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-3">Abra o app do seu banco e escaneie o código</p>
            </div>
            @elseif($pixQrCodeUrl && !empty($pixQrCodeUrl))
            <div class="text-center">
                <div class="text-sm font-semibold text-gray-900 mb-3">Escaneie o QR Code</div>
                <div class="flex justify-center bg-white p-4 rounded-lg border-2 border-gray-200">
                    <img src="{{ $pixQrCodeUrl }}" 
                         alt="QR Code PIX" 
                         class="w-64 h-64 object-contain">
                </div>
                <p class="text-xs text-gray-600 mt-3">Abra o app do seu banco e escaneie o código</p>
            </div>
            @else
            <div class="text-center bg-gray-50 p-6 rounded-lg">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                <p class="text-gray-600 text-sm">QR Code não disponível</p>
                <p class="text-gray-500 text-xs mt-1">Use o código copia e cola abaixo</p>
            </div>
            @endif
            
            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-3 bg-white text-gray-600">ou</span>
                </div>
            </div>
            
            <!-- Código Copia e Cola -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-900">Código Copia e Cola</span>
                    <button 
                        onclick="copyPixCode('{{ $pixPayload }}')"
                        id="copyPixBtn"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all flex items-center"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Copiar
                    </button>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-300 max-h-32 overflow-y-auto">
                    <div class="text-xs text-gray-600 font-mono break-all">
                        {{ $pixPayload }}
                    </div>
                </div>
                <p class="text-xs text-gray-600 mt-2">Cole este código no app do seu banco na opção PIX Copia e Cola</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-2xl">
            <button 
                onclick="closePixModal()"
                class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-semibold transition-all"
            >
                Fechar
            </button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
function showPixModal() {
    document.getElementById('pixModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closePixModal() {
    document.getElementById('pixModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function copyPixCode(text) {
    navigator.clipboard.writeText(text).then(function() {
        const btn = document.getElementById('copyPixBtn');
        const originalHTML = btn.innerHTML;
        
        btn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Copiado!
        `;
        btn.classList.remove('bg-green-600', 'hover:bg-green-700');
        btn.classList.add('bg-green-500');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('bg-green-500');
            btn.classList.add('bg-green-600', 'hover:bg-green-700');
        }, 2000);
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
        alert('Erro ao copiar código PIX');
    });
}

function refreshTransaction() {
    const btn = document.getElementById('refreshBtn');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = `
        <svg class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        Atualizando...
    `;
    
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Auto-refresh for pending transactions
@if(($transaction->status === 'pending' && !$transaction->is_retained) || ($transaction->is_retained))
    setInterval(() => {
        location.reload();
    }, 30000);
@endif

// Close modal with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePixModal();
    }
});
</script>
@endpush
@endsection
