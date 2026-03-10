@extends('layouts.admin')

@section('title', 'Painel Administrativo')
@section('page-title', 'Dashboard Admin')
@section('page-description', 'Visão geral do sistema e estatísticas')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header com Período -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-2">
        <div>
            <h1 class="text-3xl font-bold text-white">Dashboard Administrativo</h1>
            <p class="text-[#a1a1aa] mt-1">Período: {{ $startDate->format('d/m/Y') }} até {{ $endDate->format('d/m/Y') }} ({{ $endDate->diffInDays($startDate) + 1 }} dias)</p>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 shadow-lg">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex items-center">
                <label for="date_from" class="text-sm text-[#a1a1aa] mr-3 font-medium">De:</label>
                <input 
                    type="date" 
                    id="date_from" 
                    name="date_from" 
                    value="{{ $startDate->format('Y-m-d') }}"
                    class="bg-[#0f0f0f] border border-[#333333] rounded-lg text-white text-sm px-3 py-2 focus:border-[#D4AF37] focus:outline-none transition-colors"
                >
            </div>
            
            <div class="flex items-center">
                <label for="date_to" class="text-sm text-[#a1a1aa] mr-3 font-medium">Até:</label>
                <input 
                    type="date" 
                    id="date_to" 
                    name="date_to" 
                    value="{{ $endDate->format('Y-m-d') }}"
                    class="bg-[#0f0f0f] border border-[#333333] rounded-lg text-white text-sm px-3 py-2 focus:border-[#D4AF37] focus:outline-none transition-colors"
                >
            </div>
            
            <button type="submit" class="bg-gradient-to-r from-[#D4AF37] to-[#FFE5A0] hover:shadow-lg hover:shadow-[#D4AF37]/20 text-black px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                Filtrar
            </button>
            
            <div class="ml-auto"></div>
        </form>
    </div>

    <!-- Key Performance Indicators (KPI) - Top Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-5 border border-[#D4AF37]/30 hover:border-[#D4AF37] transition-all duration-300 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-[#D4AF37]/20 rounded-lg">
                    <svg class="w-5 h-5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <span class="text-xs text-[#D4AF37] font-semibold bg-[#D4AF37]/20 px-2 py-1 rounded-full">Receita</span>
            </div>
            <p class="text-2xl font-bold text-[#D4AF37]">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
            <p class="text-xs text-[#a1a1aa] mt-2">{{ $stats['total_transactions'] }} transações</p>
        </div>

        <!-- Net Profit -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-5 border border-[#10B981]/30 hover:border-[#10B981] transition-all duration-300 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-[#10B981]/20 rounded-lg">
                    <svg class="w-5 h-5 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs text-[#10B981] font-semibold bg-[#10B981]/20 px-2 py-1 rounded-full">Lucro</span>
            </div>
            <p class="text-2xl font-bold text-[#10B981]">R$ {{ number_format($stats['profit_details']['total_profit'], 2, ',', '.') }}</p>
            <p class="text-xs text-[#a1a1aa] mt-2">{{ number_format(($stats['profit_details']['total_profit'] / max($stats['total_revenue'], 1)) * 100, 1) }}% de margem</p>
        </div>

        <!-- Total Users -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-5 border border-[#D4AF37]/30 hover:border-[#D4AF37] transition-all duration-300 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-[#D4AF37]/20 rounded-lg">
                    <svg class="w-5 h-5 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
                <span class="text-xs text-[#D4AF37] font-semibold bg-[#D4AF37]/20 px-2 py-1 rounded-full">Usuários</span>
            </div>
            <p class="text-2xl font-bold text-[#D4AF37]">{{ number_format($stats['total_users']) }}</p>
            <p class="text-xs text-[#a1a1aa] mt-2">Usuários ativos</p>
        </div>

        <!-- Conversion Rate -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-5 border border-[#FFE5A0]/30 hover:border-[#FFE5A0] transition-all duration-300 shadow-lg">
            <div class="flex items-center justify-between mb-3">
                <div class="p-2 bg-[#FFE5A0]/20 rounded-lg">
                    <svg class="w-5 h-5 text-[#FFE5A0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <span class="text-xs text-[#FFE5A0] font-semibold bg-[#FFE5A0]/20 px-2 py-1 rounded-full">Conversão</span>
            </div>
            <p class="text-2xl font-bold text-[#FFE5A0]">{{ number_format($stats['paid_transactions'] / max($stats['total_transactions'], 1) * 100, 1) }}%</p>
            <p class="text-xs text-[#a1a1aa] mt-2">Taxa de conversão</p>
        </div>
    </div>

    <!-- Withdrawal Control Cards - Prominent Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Fees Earned -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-6 border border-[#D4AF37]/30 hover:border-[#D4AF37] transition-all duration-300 shadow-lg hover:shadow-[#D4AF37]/20">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-[#D4AF37]/20 rounded-lg">
                    <svg class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-xs text-[#D4AF37] font-semibold bg-[#D4AF37]/20 px-3 py-1 rounded-full">💰 Ganhos</span>
                </div>
            </div>
            <div>
                <p class="text-3xl font-bold text-[#D4AF37]">R$ {{ number_format($stats['withdrawal_stats']['total_fees_earned'], 2, ',', '.') }}</p>
                <p class="text-sm text-[#a1a1aa] mt-2 font-medium">Taxas Ganhas</p>
                <p class="text-xs text-[#707070] mt-1">{{ $stats['withdrawal_stats']['total_approved_withdrawals'] }} saques aprovados</p>
            </div>
        </div>

        <!-- Total Withdrawn -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-6 border border-[#D4AF37]/30 hover:border-[#D4AF37] transition-all duration-300 shadow-lg hover:shadow-[#D4AF37]/20">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-[#D4AF37]/20 rounded-lg">
                    <svg class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-xs text-[#D4AF37] font-semibold bg-[#D4AF37]/20 px-3 py-1 rounded-full">💳 Pago</span>
                </div>
            </div>
            <div>
                <p class="text-3xl font-bold text-[#D4AF37]">R$ {{ number_format($stats['withdrawal_stats']['total_withdrawn'], 2, ',', '.') }}</p>
                <p class="text-sm text-[#a1a1aa] mt-2 font-medium">Total Pago aos Usuários</p>
                <p class="text-xs text-[#707070] mt-1">Valor retirado pelos usuários</p>
            </div>
        </div>

        <!-- Available Balance -->
        <div class="bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl p-6 border border-[#10B981]/30 hover:border-[#10B981] transition-all duration-300 shadow-lg hover:shadow-[#10B981]/20">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-[#10B981]/20 rounded-lg">
                    <svg class="w-6 h-6 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-xs text-[#10B981] font-semibold bg-[#10B981]/20 px-3 py-1 rounded-full">✓ Disponível</span>
                </div>
            </div>
            <div>
                <p class="text-3xl font-bold text-[#10B981]">
                    R$ {{ number_format($stats['withdrawal_stats']['available_balance'], 2, ',', '.') }}
                </p>
                <p class="text-sm text-[#a1a1aa] mt-2 font-medium">Saldo Disponível</p>
                <p class="text-xs text-[#707070] mt-1">
                    {{ $stats['withdrawal_stats']['total_pending_withdrawals'] }} saques pendentes
                </p>
                <div class="mt-4 flex gap-2">
                    <button onclick="openAdjustModal('add')" class="flex-1 bg-[#10B981] hover:bg-[#059669] text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all duration-200">
                        + Adicionar
                    </button>
                    <button onclick="openAdjustModal('remove')" class="flex-1 bg-[#EF4444] hover:bg-[#DC2626] text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all duration-200">
                        - Remover
                    </button>
                    <a href="{{ route('wallet.create') }}" class="flex-1 bg-[#D4AF37] hover:bg-[#0891b2] text-white text-xs font-semibold px-3 py-2 rounded-lg transition-all duration-200 text-center">
                        Sacar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Gateway Fees -->
        <div class="bg-[#1a1a1a] rounded-xl p-4 border border-[#333333] hover:border-[#D4AF37] transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-[#D4AF37] font-semibold">Custos</span>
            </div>
            <p class="text-xl font-bold text-[#D4AF37]">R$ {{ number_format($stats['profit_details']['gateway_fees'], 2, ',', '.') }}</p>
            <p class="text-xs text-[#a1a1aa] mt-1">Taxas de Gateway ({{ number_format(($stats['profit_details']['gateway_fees'] / max($stats['total_revenue'], 1)) * 100, 1) }}%)</p>
        </div>

        <!-- Average Ticket -->
        <div class="bg-[#1a1a1a] rounded-xl p-4 border border-[#333333] hover:border-[#FFE5A0] transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-[#FFE5A0] font-semibold">Ticket Médio</span>
            </div>
            <p class="text-xl font-bold text-[#FFE5A0]">R$ {{ number_format($stats['average_ticket'], 2, ',', '.') }}</p>
            <p class="text-xs text-[#a1a1aa] mt-1">Valor médio por transação</p>
        </div>

        <!-- Refund Rate -->
        <div class="bg-[#1a1a1a] rounded-xl p-4 border border-[#333333] hover:border-[#EF4444] transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-[#EF4444] font-semibold">Reembolsos</span>
            </div>
            <p class="text-xl font-bold text-[#EF4444]">{{ number_format($stats['refund_info']['total_refund_percentage'], 1) }}%</p>
            <p class="text-xs text-[#a1a1aa] mt-1">R$ {{ number_format($stats['refund_info']['total_refund_amount'], 2, ',', '.') }} reembolsados</p>
        </div>
    </div>

    <!-- Analytics Section - Charts & Reports -->
    <div class="bg-[#1a1a1a] rounded-xl p-6 border border-[#333333] shadow-lg">
        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <span>📊</span>
            <span>Análise de Vendas</span>
        </h3>
        <div class="h-96 bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
            <canvas id="salesChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Location Analytics & Profit Details - Side by Side -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales by Location -->
        <div class="lg:col-span-1 bg-[#1a1a1a] rounded-xl p-6 border border-[#333333] shadow-lg">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>🌍</span>
                        <span>Top Localizações</span>
                    </h3>
                </div>
            </div>
            
            <!-- Location List Container with Fixed Height -->
            <div class="h-96 overflow-y-auto space-y-2 pr-2">
                @if($salesByLocation && count($salesByLocation) > 0)
                    @foreach($salesByLocation as $index => $location)
                        @if($index < 8)
                        <div class="bg-[#0f0f0f] rounded-lg p-3 border border-[#333333] hover:border-[#D4AF37]/80 transition-all duration-300">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center justify-center w-5 h-5 bg-gradient-to-r from-[#D4AF37] to-[#FFE5A0] rounded text-xs font-bold text-black">{{ $index + 1 }}</span>
                                        <h4 class="text-xs font-semibold text-white">{{ $location['country'] }}</h4>
                                    </div>
                                    <p class="text-xs text-[#707070] ml-7">{{ $location['city'] ?? '--' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-[#D4AF37]">R$ {{ number_format($location['amount'], 2, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2 text-xs">
                                @if($location['paid_count'] > 0)
                                    <span class="px-2 py-0.5 bg-[#10B981]/15 text-[#10B981] rounded text-xs font-medium">✓ {{ $location['paid_count'] }}</span>
                                @endif
                                @if($location['pending_count'] > 0)
                                    <span class="px-2 py-0.5 bg-[#FFE5A0]/15 text-[#FFE5A0] rounded text-xs font-medium">⏳ {{ $location['pending_count'] }}</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>

        <!-- Profit Details & Refunds -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Profit Breakdown -->
            <div class="bg-[#1a1a1a] rounded-xl p-6 border border-[#333333] shadow-lg">
                <h3 class="text-lg font-bold text-white mb-4">💰 Detalhes do Lucro</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Taxas Cobradas</p>
                        <p class="text-lg font-bold text-[#D4AF37]">R$ {{ number_format($stats['profit_details']['total_user_fees'], 2, ',', '.') }}</p>
                    </div>
                    
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Taxas Pagas</p>
                        <p class="text-lg font-bold text-[#D4AF37]">R$ {{ number_format($stats['profit_details']['gateway_fees'], 2, ',', '.') }}</p>
                    </div>
                    
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Reembolsos</p>
                        <p class="text-lg font-bold text-[#EF4444]">R$ {{ number_format($stats['refund_info']['total_refund_amount'], 2, ',', '.') }}</p>
                    </div>
                    
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Lucro/Transação</p>
                        <p class="text-lg font-bold text-[#10B981]">R$ {{ number_format($stats['profit_details']['transaction_count'] > 0 ? $stats['profit_details']['total_profit'] / $stats['profit_details']['transaction_count'] : 0, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Refund Details -->
            <div class="bg-[#1a1a1a] rounded-xl p-6 border border-[#333333] shadow-lg">
                <h3 class="text-lg font-bold text-white mb-4">🔄 Detalhes de Reembolsos</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Chargebacks</p>
                        <p class="text-lg font-bold text-[#10B981]">R$ {{ number_format($stats['refund_info']['chargeback_amount'], 2, ',', '.') }}</p>
                        <p class="text-xs text-[#707070] mt-1">{{ $stats['refund_info']['chargeback_count'] }} transações</p>
                    </div>
                    
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Reembolsos Manuais</p>
                        <p class="text-lg font-bold text-[#FFE5A0]">R$ {{ number_format($stats['refund_info']['manual_refund_amount'], 2, ',', '.') }}</p>
                        <p class="text-xs text-[#707070] mt-1">{{ $stats['refund_info']['manual_refund_count'] }} transações</p>
                    </div>
                    
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Taxa de Chargeback</p>
                        <p class="text-lg font-bold text-[#D4AF37]">{{ number_format($stats['refund_info']['chargeback_percentage'], 2) }}%</p>
                    </div>
                    
                    <div class="bg-[#0f0f0f] rounded-lg p-4 border border-[#333333]">
                        <p class="text-xs text-[#a1a1aa] mb-2">Taxa Total de Reembolso</p>
                        <p class="text-lg font-bold text-[#D4AF37]">{{ number_format($stats['refund_info']['total_refund_percentage'], 2) }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const gradient = salesCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(212, 175, 55, 0.3)');
    gradient.addColorStop(1, 'rgba(212, 175, 55, 0.01)');
    
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['sales_labels']) !!},
            datasets: [{
                label: 'Vendas',
                data: {!! json_encode($chartData['sales_data']) !!},
                borderColor: '#D4AF37',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#D4AF37',
                pointBorderColor: '#FFE5A0',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    labels: {
                        color: '#a1a1aa',
                        font: { weight: 'bold', size: 12 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    borderColor: '#D4AF37',
                    borderWidth: 1,
                    titleColor: '#D4AF37',
                    bodyColor: '#FFE5A0',
                    padding: 12,
                    titleFont: { weight: 'bold' },
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('pt-BR', { 
                                    style: 'currency', 
                                    currency: 'BRL' 
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(51, 51, 51, 0.2)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#707070',
                        font: { size: 11 }
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(51, 51, 51, 0.3)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#707070',
                        font: { size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('pt-BR', { 
                                style: 'currency', 
                                currency: 'BRL',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });

    // Date range validation
    const dateForm = document.querySelector('form[action*="dashboard"]');
    if (dateForm) {
        dateForm.addEventListener('submit', function(e) {
            const dateFrom = new Date(document.getElementById('date_from').value);
            const dateTo = new Date(document.getElementById('date_to').value);
            
            if (dateFrom > dateTo) {
                e.preventDefault();
                alert('A data inicial não pode ser maior que a data final');
            }
        });
    }
});

function openAdjustModal(type) {
    const modal = document.getElementById('adjustModal');
    const title = document.getElementById('modalTitle');
    const submitButton = document.getElementById('submitButton');
    const typeInput = document.getElementById('adjustType');
    
    typeInput.value = type;
    
    if (type === 'add') {
        title.textContent = '💰 Adicionar Saldo';
        submitButton.textContent = '✓ Adicionar';
    } else {
        title.textContent = '💸 Remover Saldo';
        submitButton.textContent = '✓ Remover';
    }
    
    modal.classList.remove('hidden');
    document.getElementById('adjustAmount').focus();
}

function closeAdjustModal() {
    document.getElementById('adjustModal').classList.add('hidden');
    document.getElementById('adjustForm').reset();
}

// Fechar modal ao clicar fora
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('adjustModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAdjustModal();
            }
        });
    }
});
</script>
@endpush

<!-- Modal para ajustar saldo -->
<div id="adjustModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
    <div class="bg-gradient-to-br from-[#1a1a1a] to-[#0f0f0f] rounded-xl p-6 max-w-md w-full border border-[#333333] shadow-2xl">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-white" id="modalTitle">Ajustar Saldo</h3>
            <button onclick="closeAdjustModal()" class="text-[#a1a1aa] hover:text-white transition-colors p-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="adjustForm" method="POST" action="{{ route('admin.adjust-wallet-balance') }}">
            @csrf
            <input type="hidden" name="type" id="adjustType">
            
            <div class="mb-5">
                <label for="adjustAmount" class="block text-sm font-semibold text-white mb-3">Valor (R$)</label>
                <input 
                    type="number" 
                    id="adjustAmount" 
                    name="amount" 
                    step="0.01" 
                    min="0.01" 
                    required
                    class="w-full px-4 py-2.5 bg-[#0f0f0f] border border-[#333333] rounded-lg text-white focus:border-[#D4AF37] focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/20 transition-all"
                    placeholder="0.00"
                >
            </div>
            
            <div class="mb-6">
                <label for="adjustDescription" class="block text-sm font-semibold text-white mb-3">Descrição (opcional)</label>
                <textarea 
                    id="adjustDescription" 
                    name="description" 
                    rows="3"
                    class="w-full px-4 py-2.5 bg-[#0f0f0f] border border-[#333333] rounded-lg text-white focus:border-[#D4AF37] focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/20 transition-all resize-none"
                    placeholder="Motivo do ajuste..."
                ></textarea>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closeAdjustModal()" class="flex-1 bg-[#333333] hover:bg-[#444444] text-white px-4 py-2.5 rounded-lg font-semibold transition-all duration-200">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 bg-gradient-to-r from-[#D4AF37] to-[#FFE5A0] hover:shadow-lg hover:shadow-[#D4AF37]/20 text-black px-4 py-2.5 rounded-lg font-semibold transition-all duration-200" id="submitButton">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection