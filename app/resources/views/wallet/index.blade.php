@extends('layouts.dashboard')

@section('title', 'Carteira')

@section('content')
<div class="container mx-auto p-4 sm:p-6 lg:p-4 max-w-[1600px] font-['Poppins']">
    <div class="space-y-6">
        <!-- Header com Aesthetic Premium -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-[#161616] to-transparent p-6 rounded-2xl border border-white/5 backdrop-blur-md">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-white mb-1">CARTEIRA</h1>
                <p class="text-gray-400 text-sm">Gerencie seu saldo, acompanhe movimentações e realize saques com agilidade.</p>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <button onclick="openPixWithdrawalModal()" class="flex-1 md:flex-none px-6 py-2.5 bg-gradient-to-r from-[#8a6d1d] to-[#D4AF37] text-white rounded-xl font-bold text-sm hover:scale-105 transition-transform shadow-[0_0_20px_rgba(212,175,55,0.2)]">
                    SAQUE PIX
                </button>
                <button onclick="openCryptoWithdrawalModal()" class="flex-1 md:flex-none px-6 py-2.5 bg-[#1F1F1F] border border-[#D4AF37]/30 text-[#D4AF37] rounded-xl font-bold text-sm hover:bg-[#D4AF37]/10 transition-colors">
                    SAQUE USDT
                </button>
            </div>
        </div>
        
        <!-- Cards de Saldo e Gráfico -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
            <!-- Balanços -->
            <div class="lg:col-span-1 space-y-4">
                <!-- Disponível -->
                <div class="balance-card group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-emerald-500/10 rounded-xl relative">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="absolute top-0 right-0 flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-2 py-1 rounded-full uppercase tracking-wider">Disponível</span>
                    </div>
                    <div class="text-gray-400 text-xs font-semibold mb-1 uppercase tracking-tighter">Saldo Total</div>
                    <div class="text-2xl font-black text-white tracking-tight">
                        <span class="animate-number">R$ {{ number_format($pixBalance ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- A Receber -->
                <div class="balance-card group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-500/10 rounded-xl">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-blue-500 bg-blue-500/10 px-2 py-1 rounded-full uppercase tracking-wider">A Receber</span>
                    </div>
                    <div class="text-gray-400 text-xs font-semibold mb-1 uppercase tracking-tighter">Previsão</div>
                    <div class="text-2xl font-black text-white tracking-tight">
                        <span class="animate-number">R$ {{ number_format($pendingAmount ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Reserva -->
                <div class="balance-card group">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-yellow-500/10 rounded-xl">
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        </div>
                        <span class="text-[10px] font-bold text-yellow-500 bg-yellow-500/10 px-2 py-1 rounded-full uppercase tracking-wider">Reserva</span>
                    </div>
                    <div class="text-gray-400 text-xs font-semibold mb-1 uppercase tracking-tighter">Garantia</div>
                    <div class="text-2xl font-black text-white tracking-tight">
                        <span class="animate-number">R$ {{ number_format($reservedBalance ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Performance -->
            <div class="lg:col-span-3 bg-[#161616] p-6 rounded-2xl border border-white/5 relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-64 h-64 bg-[#D4AF37]/5 blur-[80px] rounded-full pointer-events-none group-hover:bg-[#D4AF37]/10 transition-all duration-700"></div>
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-white font-bold text-lg">Desempenho da Carteira</h3>
                        <p class="text-gray-500 text-xs">Volume de saques e recebimentos nos últimos 7 dias.</p>
                    </div>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-1.5 text-[10px] text-emerald-500 font-bold bg-emerald-500/10 px-3 py-1 rounded-full uppercase">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Recebido
                        </span>
                        <span class="flex items-center gap-1.5 text-[10px] text-red-500 font-bold bg-red-500/10 px-3 py-1 rounded-full uppercase">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> Saques
                        </span>
                    </div>
                </div>
                <!-- Gráfico de Performance Real (ApexCharts) -->
                <div class="relative w-full h-[260px] mt-4">
                    <div id="walletChart"></div>
                </div>

                @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var options = {
                        series: [{
                            name: 'Recebido',
                            data: {!! json_encode($chart['dataReceived'] ?? [0,0,0,0,0,0,0]) !!}
                        }, {
                            name: 'Saques (Saída)',
                            data: {!! json_encode($chart['dataWithdrawals'] ?? [0,0,0,0,0,0,0]) !!}
                        }],
                        chart: {
                            type: 'area', // Or 'bar', using area looks very premium
                            height: 260,
                            toolbar: { show: false },
                            background: 'transparent',
                            zoom: { enabled: false }
                        },
                        colors: ['#10b981', '#ef4444'], // Emerald and Red
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        xaxis: {
                            categories: {!! json_encode($chart['labels'] ?? ['S','T','Q','Q','S','S','D']) !!},
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: {
                                style: { colors: '#6B7280', fontSize: '10px', fontWeight: 'bold' }
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function (value) {
                                    return "R$ " + value.toLocaleString('pt-BR', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                                },
                                style: { colors: '#6B7280', fontSize: '10px' }
                            }
                        },
                        grid: {
                            borderColor: 'rgba(255, 255, 255, 0.05)',
                            strokeDashArray: 4,
                            xaxis: { lines: { show: true } },
                            yaxis: { lines: { show: true } },
                            padding: { top: 0, right: 0, bottom: 0, left: 10 }
                        },
                        legend: { show: false },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: function (val) {
                                    return "R$ " + val.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                            }
                        }
                    };

                    var chart = new ApexCharts(document.querySelector("#walletChart"), options);
                    chart.render();
                });
                </script>
                @endpush
            </div>
        </div>

        <!-- Analytical Insights (Novas informações solicitadas) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-[#161616] p-4 rounded-xl border border-white/5 flex items-center gap-4 group">
                <div class="p-2.5 bg-[#D4AF37]/10 rounded-lg text-[#D4AF37]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest flex items-center gap-2">
                        Faturamento Hoje
                        @if(($stats['growth'] ?? 0) != 0)
                            <span class="text-[9px] {{ $stats['growth'] > 0 ? 'text-emerald-500' : 'text-red-500' }} font-bold">
                                {{ $stats['growth'] > 0 ? '↑' : '↓' }} {{ abs(round($stats['growth'])) }}%
                            </span>
                        @endif
                    </div>
                    <div class="text-lg font-black text-white">R$ {{ number_format($stats['revenue_today'] ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="bg-[#161616] p-4 rounded-xl border border-white/5 flex items-center gap-4 group">
                <div class="p-2.5 bg-blue-500/10 rounded-lg text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Ticket Médio</div>
                    <div class="text-lg font-black text-white">R$ {{ number_format($stats['avg_ticket'] ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>
            <div class="bg-[#161616] p-4 rounded-xl border border-white/5 flex items-center gap-4 group">
                <div class="p-2.5 bg-emerald-500/10 rounded-lg text-emerald-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Taxa Conversão</div>
                    <div class="text-lg font-black text-white">{{ number_format($stats['conversion_rate'] ?? 0, 1, ',', '.') }}%</div>
                </div>
            </div>
            <div class="bg-[#161616] p-4 rounded-xl border border-white/5 flex items-center gap-4 group">
                <div class="p-2.5 bg-purple-500/10 rounded-lg text-purple-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Vendas Hoje</div>
                    <div class="text-lg font-black text-white">{{ $stats['paid_today_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Movimentações e Filtros -->
        <div class="bg-[#161616] rounded-2xl border border-white/5 overflow-hidden">
            <!-- Tabs e Filtros Avançados -->
            <div class="p-6 border-b border-white/5">
                <form action="{{ route('wallet.index') }}" method="GET" class="space-y-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div class="flex p-1 bg-black/40 rounded-xl overflow-x-auto">
                            <button type="button" onclick="switchTab('transactions')" id="tab-transactions-trigger" class="tab-trigger px-6 py-2 rounded-lg text-xs font-bold transition-all data-[state=active]:bg-[#1F1F1F] data-[state=active]:text-[#D4AF37] text-gray-500 relative whitespace-nowrap" data-state="active">
                                TRANSAÇÕES
                            </button>
                            <button type="button" onclick="switchTab('withdrawals')" id="tab-withdrawals-trigger" class="tab-trigger px-6 py-2 rounded-lg text-xs font-bold transition-all data-[state=active]:bg-[#1F1F1F] data-[state=active]:text-[#D4AF37] text-gray-500 relative whitespace-nowrap" data-state="inactive">
                                SAQUES
                            </button>
                            <button type="button" onclick="switchTab('pix-keys')" id="tab-pix-keys-trigger" class="tab-trigger px-6 py-2 rounded-lg text-xs font-bold transition-all data-[state=active]:bg-[#1F1F1F] data-[state=active]:text-[#D4AF37] text-gray-500 relative whitespace-nowrap" data-state="inactive">
                                CHAVES PIX
                            </button>
                            <button type="button" onclick="switchTab('med')" id="tab-med-trigger" class="tab-trigger px-6 py-2 rounded-lg text-xs font-bold transition-all data-[state=active]:bg-[#1F1F1F] data-[state=active]:text-[#D4AF37] text-gray-500 relative whitespace-nowrap" data-state="inactive">
                                ESTORNOS
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            @if(count($filters) > 0)
                                <a href="{{ route('wallet.index') }}" class="text-[10px] font-black text-red-500 hover:text-red-400 transition-colors uppercase tracking-widest">Limpar Filtros</a>
                            @endif
                            <button type="submit" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-xs font-bold transition-colors border border-white/5">
                                APLICAR FILTROS
                            </button>
                        </div>
                    </div>

                    <!-- Grid de Filtros -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 bg-black/20 p-4 rounded-xl border border-white/5">
                        <div class="relative">
                            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="ID ou Nome..." class="w-full bg-[#1F1F1F] border border-white/5 rounded-lg py-2.5 px-4 pl-10 text-[11px] text-white placeholder:text-gray-600 focus:outline-none focus:border-[#D4AF37]/50">
                            <svg class="w-4 h-4 text-gray-600 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        
                        <div>
                            <select name="status" class="w-full bg-[#1F1F1F] border border-white/5 rounded-lg py-2.5 px-4 text-[11px] text-white focus:outline-none focus:border-[#D4AF37]/50 appearance-none">
                                <option value="all">Status: Todos</option>
                                <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Pago</option>
                                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pendente</option>
                                <option value="failed" {{ ($filters['status'] ?? '') === 'failed' ? 'selected' : '' }}>Falhou</option>
                            </select>
                        </div>

                        <div>
                            <select name="method" class="w-full bg-[#1F1F1F] border border-white/5 rounded-lg py-2.5 px-4 text-[11px] text-white focus:outline-none focus:border-[#D4AF37]/50 appearance-none">
                                <option value="all">Método: Todos</option>
                                <option value="pix" {{ ($filters['method'] ?? '') === 'pix' ? 'selected' : '' }}>PIX</option>
                                <option value="credit_card" {{ ($filters['method'] ?? '') === 'credit_card' ? 'selected' : '' }}>Cartão</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="date" name="date_start" value="{{ $filters['date_start'] ?? '' }}" class="flex-1 bg-[#1F1F1F] border border-white/5 rounded-lg py-2 px-3 text-[11px] text-white focus:outline-none focus:border-[#D4AF37]/50">
                            <span class="text-gray-600 font-bold text-[10px]">ATÉ</span>
                            <input type="date" name="date_end" value="{{ $filters['date_end'] ?? '' }}" class="flex-1 bg-[#1F1F1F] border border-white/5 rounded-lg py-2 px-3 text-[11px] text-white focus:outline-none focus:border-[#D4AF37]/50">
                        </div>

                        <div class="flex items-center gap-2 text-gray-500">
                             <!-- Placeholder para alinhar grid se necessário -->
                        </div>
                    </div>
                </form>
            </div>

            <!-- Listas de Conteúdo -->
            <div class="p-0">
                <!-- Tab: Transações -->
                <div id="tab-transactions" class="tab-content" data-state="active">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-black/20">
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Data / Hora</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Descrição / Cliente</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Método</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest text-right">Valor Líquido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($transactions as $transaction)
                                    <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer group">
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-white text-xs font-bold">{{ $transaction->created_at->format('d/m/Y') }}</span>
                                                <span class="text-gray-500 text-[10px]">{{ $transaction->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#1F1F1F] to-black flex items-center justify-center text-[10px] font-bold text-[#D4AF37] border border-white/5">
                                                    {{ substr($transaction->customer_data['name'] ?? 'N', 0, 1) }}
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-white text-xs font-bold group-hover:text-[#D4AF37] transition-colors text-ellipsis overflow-hidden max-w-[200px] whitespace-nowrap">
                                                        {{ $transaction->customer_data['name'] ?? 'Consumidor Final' }}
                                                    </span>
                                                    <span class="text-gray-600 text-[10px]">#{{ substr($transaction->transaction_id, -8) }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-1 bg-white/5 rounded text-[10px] font-black text-gray-400 uppercase">
                                                {{ $transaction->payment_method ?? 'PIX' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $status = $transaction->status ?? 'paid';
                                                $statusMap = [
                                                    'paid' => ['status-paid', 'Pago'],
                                                    'pending' => ['status-pending', 'Pendente'],
                                                    'failed' => ['status-failed', 'Falhou'],
                                                ];
                                                $curr = $statusMap[$status] ?? $statusMap['paid'];
                                            @endphp
                                            <span class="px-2.5 py-1 {{ $curr[0] }} rounded-full text-[10px] font-black uppercase tracking-tighter">
                                                {{ $curr[1] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="text-white text-xs font-black">
                                                R$ {{ number_format($transaction->net_amount ?? $transaction->amount, 2, ',', '.') }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center gap-3 opacity-30">
                                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                <span class="text-white text-sm font-bold uppercase tracking-widest">Nenhuma transação encontrada</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->hasPages())
                        <div class="p-6 border-t border-white/5">
                            {{ $transactions->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                <!-- Tab: Saques -->
                <div id="tab-withdrawals" class="tab-content hidden p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-black/20">
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">ID / Conta</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Data</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest">Status</th>
                                    <th class="px-6 py-4 text-[10px] font-black text-gray-500 uppercase tracking-widest text-right">Valor Líquido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                @forelse($withdrawals ?? [] as $withdrawal)
                                    <tr class="hover:bg-white/[0.02] transition-colors cursor-pointer group" onclick="window.location.href='{{ route('wallet.show', $withdrawal->id) }}'">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#1F1F1F] to-black flex items-center justify-center text-[#D4AF37] border border-white/5">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-white text-xs font-bold group-hover:text-[#D4AF37] transition-colors">
                                                        {{ $withdrawal->withdrawal_id ?? 'Saque' }}
                                                    </span>
                                                    <span class="text-gray-600 text-[10px] uppercase font-bold">{{ $withdrawal->pix_type }} - {{ $withdrawal->pix_key }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col">
                                                <span class="text-white text-xs font-bold">{{ $withdrawal->created_at->format('d/m/Y') }}</span>
                                                <span class="text-gray-500 text-[10px]">{{ $withdrawal->created_at->format('H:i') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @php
                                                $status = $withdrawal->status ?? 'pending';
                                                $statusMap = [
                                                    'completed' => ['status-paid', 'Concluído'],
                                                    'approved' => ['status-paid', 'Aprovado'],
                                                    'processing' => ['status-pending', 'Processando'],
                                                    'pending' => ['status-pending', 'Pendente'],
                                                    'failed' => ['status-failed', 'Falha'],
                                                    'rejected' => ['status-failed', 'Rejeitado'],
                                                    'canceled' => ['status-failed', 'Cancelado'],
                                                ];
                                                $curr = $statusMap[$status] ?? ['bg-gray-500/20 text-gray-400', ucfirst($status)];
                                            @endphp
                                            <span class="px-2.5 py-1 {{ $curr[0] }} rounded-full text-[10px] font-black uppercase tracking-tighter">
                                                {{ $curr[1] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-white text-xs font-black">
                                                    - R$ {{ number_format($withdrawal->net_amount ?? $withdrawal->amount, 2, ',', '.') }}
                                                </span>
                                                @if($withdrawal->fee > 0)
                                                    <span class="text-gray-600 text-[10px]">Taxa: R$ {{ number_format($withdrawal->fee, 2, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center gap-3 opacity-30">
                                                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path></svg>
                                                <span class="text-white text-sm font-bold uppercase tracking-widest">Nenhum saque encontrado</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if(isset($withdrawals) && $withdrawals->hasPages())
                        <div class="p-6 border-t border-white/5">
                            {{ $withdrawals->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                <!-- Tab: Chaves PIX -->
                <div id="tab-pix-keys" class="tab-content hidden p-6">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-white font-bold text-lg">Minhas Chaves PIX</h2>
                            <p class="text-gray-500 text-xs">Gerencie suas pontas de recebimento.</p>
                        </div>
                        <button onclick="openNewPixKeyModal()" class="px-4 py-2 bg-[#D4AF37]/10 text-[#D4AF37] border border-[#D4AF37]/50 rounded-xl text-xs font-bold hover:bg-[#D4AF37] hover:text-white transition-all">
                            + NOVA CHAVE
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse($pixKeys ?? [] as $pixKey)
                            <div class="bg-black/40 border border-white/5 p-5 rounded-2xl group hover:border-[#D4AF37]/50 transition-all">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="px-2 py-1 bg-[#D4AF37]/10 rounded text-[10px] font-black text-[#D4AF37]">
                                        {{ $pixKey->type_label ?? 'EVP' }}
                                    </div>
                                    <div class="flex gap-2">
                                        <button onclick="editPixKey({{ $pixKey->id }})" class="p-1.5 text-gray-600 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>
                                        <button onclick="copyToClipboard('{{ $pixKey->key }}')" class="p-1.5 text-gray-600 hover:text-[#D4AF37] transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="text-white font-bold text-sm mb-1 break-all">{{ $pixKey->key }}</div>
                                <div class="text-gray-600 text-[10px] font-bold uppercase tracking-widest">{{ $pixKey->description ?? 'Chave de Recebimento' }}</div>
                            </div>
                        @empty
                            <div class="col-span-full py-12 text-center text-gray-600 font-bold uppercase tracking-widest text-xs">
                                Nenhuma chave cadastrada
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Tab: MED (Estornos) -->
                <div id="tab-med" class="tab-content hidden p-6">
                    <div class="text-center py-20">
                        <div class="w-20 h-20 bg-yellow-500/10 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-white font-black text-lg uppercase tracking-tight mb-2">Monitoramento de MEDs</h3>
                        <p class="text-gray-500 text-sm max-w-md mx-auto">Não há solicitações de estorno (MED) registradas para sua conta no momento.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estilos Customizados -->
@push('styles')
<style>
@keyframes countUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.balance-card {
    background: #161616;
    border: 1px solid rgba(255,255,255,0.05);
    padding: 24px;
    border-radius: 20px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.balance-card:hover {
    border-color: #D4AF3780;
    transform: translateY(-8px);
    background: rgba(212, 175, 55, 0.03);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 20px rgba(212,175,55,0.1);
}

.balance-card::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(212,175,55,0.1) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.5s;
    pointer-events: none;
}

.balance-card:hover::before {
    opacity: 1;
}

.animate-number {
    display: inline-block;
    animation: countUp 0.8s ease-out forwards;
}

/* Gradients for Status */
.status-paid { background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.2) 100%); color: #34d399; }
.status-pending { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(217, 119, 6, 0.2) 100%); color: #fbbf24; }
.status-failed { background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(185, 28, 28, 0.2) 100%); color: #f87171; }

/* Custom Tab Active Bar */
.tab-trigger[data-state=active]::after {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 50%;
    transform: translateX(-50%);
    width: 20px;
    height: 3px;
    background: #D4AF37;
    border-radius: 10px;
    box-shadow: 0 0 10px #D4AF37;
}

/* Pagination Custom */
.pagination { display: flex; gap: 8px; justify-content: center; }
.pagination .page-item .page-link {
    background: #1F1F1F;
    border: 1px solid rgba(255,255,255,0.05);
    color: #707070;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: bold;
    transition: all 0.2s;
}
.pagination .page-item.active .page-link {
    background: #D4AF37;
    color: white;
    border-color: #D4AF37;
}

/* Hide tab contents */
.tab-content.hidden { display: none; }
</style>
@endpush

<!-- Reutilizando os Modais da Versão Anterior (Com CSS novo) -->
@include('wallet.modals')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Reveal values with animation
    const numbers = document.querySelectorAll('.animate-number');
    numbers.forEach((num, index) => {
        num.style.animationDelay = `${index * 0.1}s`;
    });
});

function switchTab(tab) {
    // Buttons
    document.querySelectorAll('[id$="-trigger"]').forEach(btn => {
        btn.setAttribute('data-state', 'inactive');
        btn.classList.remove('text-[#D4AF37]');
        btn.classList.add('text-gray-500');
    });
    const activeBtn = document.getElementById('tab-' + tab + '-trigger');
    activeBtn.setAttribute('data-state', 'active');
    activeBtn.classList.remove('text-gray-500');
    activeBtn.classList.add('text-[#D4AF37]');

    // Contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    document.getElementById('tab-' + tab).classList.remove('hidden');
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Usar toast se disponível, senão fallback
        if (typeof showToast === 'function') {
            showToast('Copiado para a área de transferência!', 'success');
        } else {
            alert('Copiado!');
        }
    });
}
</script>
@endpush
@endsection
