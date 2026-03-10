@extends('layouts.dashboard')

@section('title', 'Vendas')
@section('page-title', 'Vendas')
@section('page-description', 'Gerencie suas transações de pagamento')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header com Saldo e Ações -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <!-- Saldo -->
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Saldo Disponível</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $user->formatted_wallet_balance }}</div>
                    </div>
                </div>
                
                <!-- Separador -->
                <div class="h-12 w-px bg-gray-200"></div>
                
                <!-- Total de Vendas Pagas -->
                <div>
                    <div class="text-sm text-gray-600 mb-1">Vendas Pagas</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format($paidTransactionsCount ?? 0) }}</div>
                </div>
                
                <!-- Total de Registros -->
                <div>
                    <div class="text-sm text-gray-600 mb-1">Total de Transações</div>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalTransactions) }}</div>
                </div>
            </div>
            
            <!-- Botão Nova Venda -->
            <a href="{{ route('transactions.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Nova Venda</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Sales -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-right">
                    <h3 class="text-gray-600 text-sm font-medium mb-1">Total</h3>
                    <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($totalAmount, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Paid Sales -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <h3 class="text-gray-600 text-sm font-medium mb-1">Pago</h3>
                    <p class="text-2xl font-bold text-green-600">R$ {{ number_format($paidAmount, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Sales -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <h3 class="text-gray-600 text-sm font-medium mb-1">Pendente</h3>
                    <p class="text-2xl font-bold text-yellow-600">R$ {{ number_format($pendingAmount, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Refund Percentage -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </div>
                <div class="text-right">
                    <h3 class="text-gray-600 text-sm font-medium mb-1">Reembolsos</h3>
                    <p class="text-2xl font-bold {{ $refundPercentage > 5 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($refundPercentage, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
            <button type="button" onclick="clearFilters()" class="text-sm text-gray-600 hover:text-gray-900 transition-colors">
                Limpar Filtros
            </button>
        </div>
        
        <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4" id="filterForm">
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="ID, cliente, e-mail..."
                    value="{{ request('search') }}"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processando</option>
                    <option value="authorized" {{ request('status') == 'authorized' ? 'selected' : '' }}>Autorizado</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pago</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expirado</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falhou</option>
                    <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Estornado</option>
                    <option value="partially_refunded" {{ request('status') == 'partially_refunded' ? 'selected' : '' }}>Estornado Parcial</option>
                    <option value="chargeback" {{ request('status') == 'chargeback' ? 'selected' : '' }}>Chargeback</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Método</label>
                <select name="payment_method" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <option value="">Todos</option>
                    <option value="pix" {{ request('payment_method') == 'pix' ? 'selected' : '' }}>PIX</option>
                    <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                    <option value="bank_slip" {{ request('payment_method') == 'bank_slip' ? 'selected' : '' }}>Boleto</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                <input 
                    type="date" 
                    name="date_from" 
                    value="{{ request('date_from', \Carbon\Carbon::now()->subDays(6)->format('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                >
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
                <input 
                    type="date" 
                    name="date_to" 
                    value="{{ request('date_to', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                >
            </div>

            <div class="lg:col-span-6 flex justify-end">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 shadow-sm hover:shadow-md">
                    Aplicar Filtros
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Transações -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Transações</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <!-- Data -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i') }}</div>
                            </td>

                            <!-- ID -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900">
                                    {{ substr($transaction->external_id ?: $transaction->transaction_id, 0, 12) }}...
                                </div>
                            </td>

                            <!-- Cliente -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $transaction->customer_data['name'] ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">{{ $transaction->customer_data['email'] ?? 'N/A' }}</div>
                            </td>

                            <!-- Valor -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">{{ $transaction->formatted_amount }}</div>
                                <div class="text-xs text-gray-500">Taxa: R$ {{ number_format($transaction->fee_amount, 2, ',', '.') }}</div>
                            </td>

                            <!-- Método -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ strtoupper($transaction->payment_method) }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusConfig = [
                                        'pending' => ['label' => 'Pendente', 'class' => 'bg-yellow-100 text-yellow-700 border-yellow-200'],
                                        'paid' => ['label' => 'Pago', 'class' => 'bg-green-100 text-green-700 border-green-200'],
                                        'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-gray-100 text-gray-700 border-gray-200'],
                                        'expired' => ['label' => 'Expirado', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                                        'failed' => ['label' => 'Falhou', 'class' => 'bg-red-100 text-red-700 border-red-200'],
                                        'refunded' => ['label' => 'Estornado', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
                                        'partially_refunded' => ['label' => 'Estornado Parcial', 'class' => 'bg-purple-100 text-purple-700 border-purple-200'],
                                        'chargeback' => ['label' => 'Chargeback', 'class' => 'bg-orange-100 text-orange-700 border-orange-200'],
                                    ];
                                    $config = $statusConfig[$transaction->status] ?? ['label' => ucfirst($transaction->status), 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
                                    
                                    // For retained transactions, always show as pending
                                    if ($transaction->is_retained) {
                                        $config = $statusConfig['pending'];
                                    }
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full border {{ $config['class'] }}">
                                    {{ $config['label'] }}
                                </span>
                            </td>

                            <!-- Ações -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <a href="{{ route('transactions.show', $transaction->transaction_id) }}" class="text-green-600 hover:text-green-700 text-sm font-medium transition-colors">
                                        Ver detalhes
                                    </a>
                                    
                                    @if($transaction->payment_method === 'pix' && in_array($transaction->status, ['pending', 'processing']))
                                        @php
                                            $pixPayload = $transaction->payment_data['payment_data']['pix']['payload'] ?? $transaction->payment_data['payment_data']['pix']['qrcode'] ?? null;
                                            $pixQrCode = $transaction->payment_data['payment_data']['pix']['encodedImage'] ?? $transaction->payment_data['payment_data']['pix']['qr_code_url'] ?? null;
                                        @endphp
                                        
                                        @if($pixPayload)
                                            <button 
                                                onclick="openPixModal('{{ $pixQrCode }}', '{{ $pixPayload }}')"
                                                class="inline-flex items-center space-x-1 text-green-600 hover:text-green-700 text-sm font-medium transition-colors"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                                </svg>
                                                <span>PIX</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="text-gray-900 text-lg font-medium mb-1">Nenhuma transação encontrada</p>
                                    <p class="text-gray-500 text-sm">Suas vendas aparecerão aqui quando forem criadas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <x-pagination :paginator="$transactions->appends(request()->query())" />
            </div>
        @endif
    </div>
</div>

<!-- Modal PIX -->
<div id="pixModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-md w-full shadow-2xl" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 rounded-t-xl">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <h3 class="text-lg font-bold text-white">Pagamento PIX</h3>
                </div>
                <button onclick="closePixModal()" class="text-white hover:text-gray-200 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- QR Code Section -->
            <div class="text-center">
                <p class="text-sm font-medium text-gray-700 mb-3">Escaneie o QR Code</p>
                <div class="flex justify-center mb-4">
                    <div id="qrCodeContainer" class="bg-white p-3 rounded-lg border-2 border-gray-200"></div>
                </div>
            </div>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">ou</span>
                </div>
            </div>

            <!-- Copy Paste Code -->
            <div>
                <p class="text-sm font-medium text-gray-700 mb-3 text-center">Código Copia e Cola</p>
                <div class="relative">
                    <div id="pixCodeDisplay" class="bg-gray-50 border border-gray-300 rounded-lg p-3 pr-12 text-xs font-mono text-gray-700 break-all max-h-24 overflow-y-auto"></div>
                    <button 
                        onclick="copyPixCode()" 
                        class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-green-500 hover:bg-green-600 text-white p-2 rounded-lg transition-all duration-200 hover:scale-105"
                        id="copyButton"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2 text-center">Copie e cole o código no seu aplicativo de pagamento</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 rounded-b-xl">
            <button 
                onclick="closePixModal()" 
                class="w-full bg-gray-600 hover:bg-gray-700 text-white py-2.5 rounded-lg font-medium transition-colors duration-200"
            >
                Fechar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Variável global para armazenar o código PIX atual
let currentPixCode = '';

function openPixModal(qrCodeUrl, pixCode) {
    currentPixCode = pixCode;
    const modal = document.getElementById('pixModal');
    const qrContainer = document.getElementById('qrCodeContainer');
    const pixDisplay = document.getElementById('pixCodeDisplay');
    
    // Limpar container anterior
    qrContainer.innerHTML = '';
    
    // Se tiver QR Code (imagem base64 ou URL)
    if (qrCodeUrl && qrCodeUrl !== 'null' && qrCodeUrl.trim() !== '') {
        const img = document.createElement('img');
        img.src = qrCodeUrl.startsWith('data:') ? qrCodeUrl : 'data:image/png;base64,' + qrCodeUrl;
        img.alt = 'QR Code PIX';
        img.className = 'w-64 h-64 object-contain';
        qrContainer.appendChild(img);
    } else {
        // Se não tiver QR Code, mostrar mensagem
        qrContainer.innerHTML = '<p class="text-gray-500 text-sm">QR Code não disponível</p>';
    }
    
    // Exibir código PIX
    pixDisplay.textContent = pixCode;
    
    // Mostrar modal
    modal.classList.remove('hidden');
    
    // Adicionar listener para fechar com ESC
    document.addEventListener('keydown', handleEscKey);
    
    // Fechar ao clicar fora
    modal.addEventListener('click', handleOutsideClick);
}

function closePixModal() {
    const modal = document.getElementById('pixModal');
    modal.classList.add('hidden');
    
    // Remover listeners
    document.removeEventListener('keydown', handleEscKey);
    modal.removeEventListener('click', handleOutsideClick);
}

function handleEscKey(e) {
    if (e.key === 'Escape') {
        closePixModal();
    }
}

function handleOutsideClick(e) {
    if (e.target.id === 'pixModal') {
        closePixModal();
    }
}

function copyPixCode() {
    if (!currentPixCode) {
        alert('Código PIX não disponível');
        return;
    }
    
    navigator.clipboard.writeText(currentPixCode).then(function() {
        const button = document.getElementById('copyButton');
        const originalHTML = button.innerHTML;
        
        // Feedback visual
        button.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
        button.classList.add('bg-green-600');
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.classList.remove('bg-green-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
        alert('Erro ao copiar código PIX');
    });
}

function clearFilters() {
    // Limpar todos os campos do formulário
    const form = document.getElementById('filterForm');
    const inputs = form.querySelectorAll('input');
    const selects = form.querySelectorAll('select');
    
    // Limpar inputs
    inputs.forEach(input => {
        if (input.type === 'date') {
            if (input.name === 'date_from') {
                input.value = '{{ \Carbon\Carbon::now()->subDays(6)->format('Y-m-d') }}';
            } else if (input.name === 'date_to') {
                input.value = '{{ \Carbon\Carbon::now()->format('Y-m-d') }}';
            } else {
                input.value = '';
            }
        } else {
            input.value = '';
        }
    });
    
    // Limpar selects
    selects.forEach(select => {
        select.selectedIndex = 0;
    });
    
    // Submeter o formulário
    form.submit();
}

function copyPix(pixCode) {
    if (!pixCode) {
        alert('Código PIX não disponível');
        return;
    }
    
    navigator.clipboard.writeText(pixCode).then(function() {
        alert('Código PIX copiado!');
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
        alert('Erro ao copiar código PIX');
    });
}

// Fechar menus ao clicar fora
document.addEventListener('click', function(event) {
    if (!event.target.closest('[onclick^="toggleMenu"]') && !event.target.closest('[id^="menu-"]')) {
        document.querySelectorAll('[id^="menu-"]').forEach(menu => {
            menu.classList.add('hidden');
        });
    }
});

// Set default date range on page load if not already set
document.addEventListener('DOMContentLoaded', function() {
    const dateFromInput = document.querySelector('input[name="date_from"]');
    const dateToInput = document.querySelector('input[name="date_to"]');
    
    if (!dateFromInput.value) {
        dateFromInput.value = '{{ \Carbon\Carbon::now()->subDays(6)->format('Y-m-d') }}';
    }
    
    if (!dateToInput.value) {
        dateToInput.value = '{{ \Carbon\Carbon::now()->format('Y-m-d') }}';
    }
});
</script>
@endpush
@endsection
