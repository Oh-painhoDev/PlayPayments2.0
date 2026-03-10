@extends('layouts.dashboard')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Visão geral do seu negócio')

@section('content')
<section class="bg-view">
    <!-- Header do Dashboard -->
    <div class="dashboard-header">
        <div class="dashboard-header-content">
            <div class="dashboard-header-left">
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="dashboard-date" id="dashboardDate">{{ \Carbon\Carbon::now()->locale('pt_BR')->translatedFormat('l, d \d\e F \d\e Y') }}</p>
            </div>
            <div class="dashboard-header-right">
                <button class="date-selector-btn" id="dateSelectorBtn" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calendar">
                        <path d="M8 2v4"></path>
                        <path d="M16 2v4"></path>
                        <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                        <path d="M3 10h18"></path>
            </svg>
                    <span class="date-selector-text" id="dateSelectorText">
                        {{ $startDate->format('d') }} de {{ strtolower($startDate->locale('pt_BR')->translatedFormat('M')) }} de {{ $startDate->format('Y') }} - {{ $endDate->format('d') }} de {{ strtolower($endDate->locale('pt_BR')->translatedFormat('M')) }} de {{ $endDate->format('Y') }}
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- Banner do Dashboard -->
    @if(isset($whiteLabelBanner) && $whiteLabelBanner)
        <div class="dashboard-banner-container" style="width: 100%; margin-top: 0; margin-bottom: 24px;">
            <img 
                src="{{ $whiteLabelBanner }}" 
                alt="Dashboard Banner" 
                class="dashboard-banner" 
                style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 12px; display: block;"
                onerror="this.style.display='none'"
            >
        </div>
    @endif

    <!-- Modal de Seleção de Data -->
    <div id="datePickerModal" class="date-picker-modal">
        <div class="date-picker-overlay"></div>
        <div class="date-picker-content">
            <div class="date-picker-sidebar">
                <div class="date-option-item active" data-option="today">
                    <div class="date-option-indicator"></div>
                    <span>Hoje</span>
                </div>
                <div class="date-option-item" data-option="yesterday">
                    <div class="date-option-indicator"></div>
                    <span>Ontem</span>
                </div>
                <div class="date-option-item" data-option="7days">
                    <div class="date-option-indicator"></div>
                    <span>7 dias</span>
                </div>
                <div class="date-option-item" data-option="30days">
                    <div class="date-option-indicator"></div>
                    <span>30 dias</span>
                </div>
                <div class="date-option-item" data-option="custom">
                    <div class="date-option-indicator"></div>
                    <span>Personalizado</span>
                </div>
                <div class="date-picker-divider"></div>
                <div class="date-option-item" data-option="clear">
                    <div class="date-option-indicator"></div>
                    <span>Limpar</span>
                </div>
            </div>
            <div class="date-picker-calendar">
                <div class="calendar-container" id="calendarContainer">
                    <!-- Calendário será gerado via JavaScript -->
                </div>
                <div class="date-picker-actions">
                    <button class="date-picker-cancel-btn" id="datePickerCancelBtn">Cancelar</button>
                    <button class="date-picker-apply-btn" id="datePickerApplyBtn" disabled>Aplicar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Métricas - Layout Otimizado -->
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Card 1: Valor Líquido -->
            <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 hover:border-[#D4AF37]/50 transition-all duration-300 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Valor Líquido</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="12" viewBox="0 0 16 12" fill="none" class="opacity-60">
                        <circle cx="4.5" cy="5.5" r="4.5" fill="#D4AF37"></circle>
                        <circle cx="10" cy="6" r="6" fill="#D4AF37" fill-opacity="0.26"></circle>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-[#D4AF37]">R$ {{ number_format($netValue ?? 0, 2, ',', '.') }}</p>
                <p class="text-xs text-[#707070] mt-2">Valor total aprovado</p>
            </div>

            <!-- Card 2: Faturamento -->
            <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 hover:border-[#FF9D3A]/50 transition-all duration-300 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Faturamento</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="12" viewBox="0 0 17 12" fill="none" class="opacity-60">
                        <circle cx="5.09998" cy="5.5" r="4.5" fill="#FF9D3A"></circle>
                        <circle cx="10.6" cy="6" r="6" fill="#FF9D3A" fill-opacity="0.42"></circle>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-[#FF9D3A]">R$ {{ number_format($totalSales ?? 0, 2, ',', '.') }}</p>
                <p class="text-xs text-[#707070] mt-2">Total de vendas</p>
            </div>

            <!-- Card 3: Vendas Pendentes -->
            <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 hover:border-[#FFE5A0]/50 transition-all duration-300 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Pendentes</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="12" viewBox="0 0 17 12" fill="none" class="opacity-60">
                        <circle cx="4.70001" cy="5.5" r="4.5" fill="#FFE5A0"></circle>
                        <circle cx="10.2" cy="6" r="6" fill="#FFE5A0" fill-opacity="0.45"></circle>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-[#FFE5A0]">R$ {{ number_format($pendingSales ?? 0, 2, ',', '.') }}</p>
                <p class="text-xs text-[#707070] mt-2">À processar</p>
            </div>

            <!-- Card 4: Ticket Médio -->
            <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 hover:border-[#FF8C5A]/50 transition-all duration-300 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Ticket Médio</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="12" viewBox="0 0 17 12" fill="none" class="opacity-60">
                        <circle cx="5.30005" cy="5.5" r="4.5" fill="#FF8C5A"></circle>
                        <circle cx="10.8" cy="6" r="6" fill="#FF8C5A" fill-opacity="0.45"></circle>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-[#FF8C5A]">R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}</p>
                <p class="text-xs text-[#707070] mt-2">Valor médio</p>
            </div>

            <!-- Card 5: Total de Transações -->
            <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 hover:border-[#D4AF37]/50 transition-all duration-300 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Transações</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="12" viewBox="0 0 17 12" fill="none" class="opacity-60">
                        <circle cx="4.90002" cy="5.5" r="4.5" fill="#2A2E33"></circle>
                        <circle cx="10.4" cy="6" r="6" fill="#2A2E33" fill-opacity="0.45"></circle>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-white">{{ $paidTransactions ?? 0 }}</p>
                <p class="text-xs text-[#707070] mt-2">Total aprovadas</p>
            </div>
        </div>
    </div>

    <!-- Cards de Ações Rápidas -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-white mb-4">Ações Rápidas</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card: Gerar PIX -->
            <a href="#" id="openDepositModalBtnGrid" class="group bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl border border-[#D4AF37]/30 hover:border-[#D4AF37] p-5 transition-all duration-300 shadow-lg hover:shadow-[#D4AF37]/20">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-[#D4AF37]/20 rounded-lg group-hover:scale-110 transition-transform">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#D4AF37" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                            <rect x="3" y="3" width="5" height="5" rx="1"></rect>
                            <rect x="16" y="3" width="5" height="5" rx="1"></rect>
                            <rect x="3" y="16" width="5" height="5" rx="1"></rect>
                            <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                            <path d="M21 21v.01"></path>
                            <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                            <path d="M3 12h.01"></path>
                            <path d="M12 3h.01"></path>
                            <path d="M12 16v.01"></path>
                            <path d="M16 12h1"></path>
                            <path d="M21 12v.01"></path>
                            <path d="M12 21v-1"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Gerar PIX</h4>
                        <p class="text-xs text-[#a1a1aa]">QR Code</p>
                    </div>
                </div>
                <svg fill="none" viewBox="0 0 24 24" class="w-4 h-4 text-[#D4AF37] group-hover:translate-x-1 transition-transform">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 4 8 8-8 8"></path>
                </svg>
            </a>

            <!-- Card: Solicitar Saque -->
            <a href="{{ route('wallet.index') }}" class="group bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl border border-[#21b3dd]/30 hover:border-[#21b3dd] p-5 transition-all duration-300 shadow-lg hover:shadow-[#21b3dd]/20">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-[#21b3dd]/20 rounded-lg group-hover:scale-110 transition-transform">
                        <svg viewBox="0 0 640 512" fill="#21b3dd" class="w-5 h-5">
                            <path d="M535 41c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l64 64c4.5 4.5 7 10.6 7 17s-2.5 12.5-7 17l-64 64c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l23-23-174-.2c-13.3 0-24-10.7-24-24s10.7-24 24-24h174.1zM105 377l-23 23h174c13.3 0 24 10.7 24 24s-10.7 24-24 24H81.9l23 23c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0L7 441c-4.5-4.5-7-10.6-7-17s2.5-12.5 7-17l64-64c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9zM96 64h241.9c-3.7 7.2-5.9 15.3-5.9 24 0 28.7 23.3 52 52 52h117.4c-4 17 .6 35.5 13.8 48.8 20.3 20.3 53.2 20.3 73.5 0l19.3-19.3V384c0 35.3-28.7 64-64 64H302.1c3.7-7.2 5.9-15.3 5.9-24 0-28.7-23.3-52-52-52H138.6c4-17-.6-35.5-13.8-48.8-20.3-20.3-53.2-20.3-73.5 0L32 342.5V128c0-35.3 28.7-64 64-64zm64 64H96v64c35.3 0 64-28.7 64-64zm384 192c-35.3 0-64 28.7-64 64h64zm-224 32a96 96 0 1 0 0-192 96 96 0 1 0 0 192z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Solicitar Saque</h4>
                        <p class="text-xs text-[#a1a1aa]">Via PIX</p>
                    </div>
                </div>
                <svg fill="none" viewBox="0 0 24 24" class="w-4 h-4 text-[#21b3dd] group-hover:translate-x-1 transition-transform">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 4 8 8-8 8"></path>
                </svg>
            </a>

            <!-- Card: Transações -->
            <a href="{{ route('transactions.index') }}" class="group bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl border border-[#10B981]/30 hover:border-[#10B981] p-5 transition-all duration-300 shadow-lg hover:shadow-[#10B981]/20">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-[#10B981]/20 rounded-lg group-hover:scale-110 transition-transform">
                        <svg viewBox="0 0 24 24" fill="#10B981" class="w-5 h-5">
                            <path d="M20 12v6a1 1 0 0 1-2 0V4a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v14c0 1.654 1.346 3 3 3h14c1.654 0 3-1.346 3-3v-6zm-6-1v2H6v-2zM6 9V7h8v2zm8 6v2h-3v-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Transações</h4>
                        <p class="text-xs text-[#a1a1aa]">Ver histórico</p>
                    </div>
                </div>
                <svg fill="none" viewBox="0 0 24 24" class="w-4 h-4 text-[#10B981] group-hover:translate-x-1 transition-transform">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 4 8 8-8 8"></path>
                </svg>
            </a>

            <!-- Card: Credenciais API -->
            <a href="{{ route('api-key') }}" class="group bg-gradient-to-br from-[#1a1a1a] to-[#161616] rounded-xl border border-[#EF4444]/30 hover:border-[#EF4444] p-5 transition-all duration-300 shadow-lg hover:shadow-[#EF4444]/20">
                <div class="flex items-center gap-4 mb-3">
                    <div class="p-3 bg-[#EF4444]/20 rounded-lg group-hover:scale-110 transition-transform">
                        <svg viewBox="0 0 24 24" fill="#EF4444" class="w-5 h-5">
                            <path d="m7.375 16.781 1.25-1.562L4.601 12l4.024-3.219-1.25-1.562-5 4a1 1 0 0 0 0 1.562zm9.25-9.562-1.25 1.562L19.399 12l-4.024 3.219 1.25 1.562 5-4a1 1 0 0 0 0-1.562zm-1.649-4.003-4 18-1.953-.434 4-18z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-white">Credenciais API</h4>
                        <p class="text-xs text-[#a1a1aa]">Gerenciar</p>
                    </div>
                </div>
                <svg fill="none" viewBox="0 0 24 24" class="w-4 h-4 text-[#EF4444] group-hover:translate-x-1 transition-transform">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m8 4 8 8-8 8"></path>
                </svg>
            </a>
        </div>
    </div>

    <!-- Gráfico e Globo 3D - Layout Lado a Lado -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Gráfico de Vendas -->
        <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-6 shadow-lg flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>📊</span>
                        <span>Análise de Vendas</span>
                    </h3>
                    <p class="text-xs text-[#a1a1aa] mt-1">Evolução das suas vendas no período</p>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-4 mb-4 pb-4 border-b border-[#333333]">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: #D4AF37;"></span>
                    <span class="text-sm text-[#a1a1aa]">Vendas</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: #FF9D3A;"></span>
                    <span class="text-sm text-[#a1a1aa]">Vendas Pendentes</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full" style="background-color: #ff6b6b;"></span>
                    <span class="text-sm text-[#a1a1aa]">Reembolsos</span>
                </div>
            </div>
            
            <div style="flex: 1; background: #0f0f0f; border-radius: 8px; padding: 12px; border: 1px solid #2a2a2a; min-height: 350px;">
                <canvas id="salesChart" style="width: 100%; max-height: 330px;"></canvas>
            </div>
        </div>

        <!-- Globo 3D - Vendas por Localização -->
        <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-6 shadow-lg flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>🌍</span>
                        <span>Vendas por Localização</span>
                    </h3>
                    <p class="text-xs text-[#a1a1aa] mt-1">Vendas ao redor do mundo</p>
                </div>
            </div>
            
            <div id="globe-container" style="width: 100%; background: #000000; border-radius: 8px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center; flex: 1; min-height: 350px; border: 1px solid #2a2a2a;">
                <div id="globeViz" style="width: 100%; height: 100%; position: relative; margin: 0; padding: 0;"></div>
            </div>
        </div>
    </div>

    <!-- Cards de Dados de Faturamento -->
    <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- Card Vendas Pendentes -->
        <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Vendas Pendentes</h3>
                    <span class="text-lg font-bold text-[#a1a1aa]">%</span>
                </div>
                <p class="text-2xl font-bold text-white">R$ {{ number_format($pendingSales ?? 0, 2, ',', '.') }}</p>
            </div>
            <div class="border-t border-[#333333] mt-4 pt-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-[#707070]">{{ \App\Models\Transaction::where('user_id', Auth::id())->whereIn('status', ['pending', 'waiting_payment'])->count() }} cobranças</p>
                    <p class="text-xs font-bold text-[#a1a1aa]">{{ number_format(($pendingSales ?? 0) > 0 && ($totalSales ?? 0) > 0 ? (($pendingSales / ($totalSales + $pendingSales)) * 100) : 0, 2, ',', '.') }}%</p>
                </div>
            </div>
        </div>

        <!-- Card Reembolsos -->
        <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Reembolsos</h3>
                    <span class="text-lg font-bold text-[#a1a1aa]">%</span>
                </div>
                <p class="text-2xl font-bold text-white">R$ {{ number_format($refundedAmount ?? 0, 2, ',', '.') }}</p>
            </div>
            <div class="border-t border-[#333333] mt-4 pt-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-[#707070]">{{ $refundedCount ?? 0 }} cobranças</p>
                    <p class="text-xs font-bold text-[#a1a1aa]">{{ number_format($refundedPercentage ?? 0, 2, ',', '.') }}%</p>
                </div>
            </div>
        </div>

        <!-- Card Chargebacks -->
        <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Chargebacks</h3>
                    <span class="text-lg font-bold text-[#a1a1aa]">%</span>
                </div>
                <p class="text-2xl font-bold text-white">R$ {{ number_format($chargebackAmount ?? 0, 2, ',', '.') }}</p>
            </div>
            <div class="border-t border-[#333333] mt-4 pt-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-[#707070]">{{ $chargebackCount ?? 0 }} cobranças</p>
                    <p class="text-xs font-bold text-[#a1a1aa]">{{ number_format($chargebackPercentage ?? 0, 2, ',', '.') }}%</p>
                </div>
            </div>
        </div>

        <!-- Card Cancelados -->
        <div class="bg-[#1a1a1a] rounded-xl border border-[#333333] p-5 shadow-lg flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-[#a1a1aa] uppercase tracking-wide">Cancelados</h3>
                    <span class="text-lg font-bold text-[#a1a1aa]">%</span>
                </div>
                <p class="text-2xl font-bold text-white">R$ {{ number_format($cancelledAmount ?? 0, 2, ',', '.') }}</p>
            </div>
            <div class="border-t border-[#333333] mt-4 pt-4">
                <div class="flex items-center justify-between">
                    <p class="text-xs text-[#707070]">{{ $cancelledCount ?? 0 }} cobranças</p>
                    <p class="text-xs font-bold text-[#a1a1aa]">{{ number_format($cancelledPercentage ?? 0, 2, ',', '.') }}%</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Seção de Transações Recentes -->
    <div class="rounded-2xl" style="background-color: rgb(22, 22, 22);">
        <div class="p-5">
            <div class="w-full flex flex-col gap-6">
                <div class="w-full flex flex-col gap-1">
                    <div class="w-5 h-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 16 12" class="w-5 h-5 text-[#707070]">
                            <path d="M6.65584 8.64422H0.916672C0.798339 8.64422 0.699172 8.6045 0.619172 8.52505C0.539172 8.44561 0.49945 8.34644 0.500006 8.22755C0.500561 8.10866 0.540283 8.0095 0.619172 7.93005C0.698061 7.85061 0.797228 7.81089 0.916672 7.81089H6.65584L4.37251 5.52755C4.29417 5.44978 4.25251 5.35478 4.24751 5.24255C4.24195 5.13144 4.28362 5.02978 4.37251 4.93755C4.46417 4.84589 4.56306 4.80005 4.66917 4.80005C4.77528 4.80005 4.87445 4.84589 4.96667 4.93755L7.78501 7.75672C7.85778 7.82894 7.90889 7.90311 7.93834 7.97922C7.96778 8.05533 7.98251 8.13811 7.98251 8.22755C7.98251 8.317 7.96778 8.39978 7.93834 8.47589C7.90889 8.552 7.85778 8.62616 7.78501 8.69839L4.96167 11.5226C4.88056 11.6037 4.78501 11.6462 4.67501 11.6501C4.56445 11.6539 4.46334 11.6098 4.37167 11.5176C4.28278 11.4259 4.23778 11.3276 4.23667 11.2226C4.23556 11.1176 4.28056 11.0195 4.37167 10.9284L6.65584 8.64422ZM9.34417 4.17339L11.6275 6.45672C11.7058 6.5345 11.7475 6.62922 11.7525 6.74089C11.7575 6.85255 11.7158 6.9545 11.6275 7.04672C11.5364 7.13839 11.4375 7.18422 11.3308 7.18422C11.2242 7.18422 11.1253 7.13839 11.0342 7.04672L8.21501 4.22755C8.14223 4.15533 8.09112 4.08116 8.06167 4.00505C8.03223 3.92894 8.01751 3.84616 8.01751 3.75672C8.01751 3.66728 8.03223 3.5845 8.06167 3.50839C8.09112 3.43228 8.14223 3.35783 8.21501 3.28505L11.0383 0.46172C11.1194 0.380609 11.2153 0.338109 11.3258 0.33422C11.4364 0.330332 11.5372 0.374498 11.6283 0.46672C11.7172 0.558387 11.7622 0.656442 11.7633 0.760887C11.765 0.865887 11.72 0.96422 11.6283 1.05589L9.34501 3.33922H15.0833C15.2022 3.33922 15.3014 3.37922 15.3808 3.45922C15.4603 3.53922 15.5 3.63839 15.5 3.75672C15.5 3.87505 15.4603 3.97422 15.3808 4.05422C15.3014 4.13422 15.2022 4.17394 15.0833 4.17339H9.34417Z" fill="currentColor"></path>
                        </svg>
                    </div>
                    <div class="font-['Manrope',_sans-serif] font-medium leading-[0] relative shrink-0 text-[14px] text-nowrap tracking-[-0.28px] text-white">
                        <p class="leading-[1.3] whitespace-pre">Transações recentes</p>
                    </div>
                    <div class="font-['Manrope',_sans-serif] font-medium leading-[0] relative shrink-0 text-[12px] text-nowrap tracking-[-0.24px] text-[#707070]">
                        <p class="leading-[1.3] whitespace-pre">Acompanhe suas últimas movimentações</p>
                    </div>
                </div>
                
                <!-- Desktop Table -->
                <div class="hidden md:block w-full overflow-x-auto">
                    <div class="w-full">
                        <div class="w-full px-4 py-5">
                            <div class="w-full flex items-center gap-6">
                                <div class="flex-1 min-w-[100px]">
                                    <div class="font-['Manrope',_sans-serif] font-medium text-[12px] tracking-[-0.24px] text-[#707070]">Data</div>
                                </div>
                                <div class="flex-[2] min-w-[200px]">
                                    <div class="font-['Manrope',_sans-serif] font-medium text-[12px] tracking-[-0.24px] text-[#707070]">Descrição</div>
                                </div>
                                <div class="flex-[1.5] min-w-[150px]">
                                    <div class="font-['Manrope',_sans-serif] font-medium text-[12px] tracking-[-0.24px] text-[#707070]">Forma de pagamento e valor</div>
                                </div>
                                <div class="flex-1 min-w-[80px]">
                                    <div class="font-['Manrope',_sans-serif] font-medium text-[12px] tracking-[-0.24px] text-[#707070]">Status</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full h-px mb-5 bg-[#1F1F1F]"></div>
                        <div class="w-full flex flex-col gap-3">
                            @if($recentTransactions && $recentTransactions->count() > 0)
                                @foreach($recentTransactions as $transaction)
                                    @php
                                        $customerData = is_string($transaction->customer_data) ? json_decode($transaction->customer_data, true) : $transaction->customer_data;
                                        $customerName = $customerData['name'] ?? 'N/A';
                                        $customerEmail = $customerData['email'] ?? 'N/A';
                                        $paymentMethod = ucfirst($transaction->payment_method ?? 'N/A');
                                        $status = ucfirst($transaction->status ?? 'N/A');
                                        
                                        // Cores do status
                                        $statusColors = [
                                            'paid' => '#22C672',
                                            'pago' => '#22C672',
                                            'pending' => '#ffa782',
                                            'pendente' => '#ffa782',
                                            'refunded' => '#ff6b6b',
                                            'reembolsado' => '#ff6b6b',
                                            'refund' => '#ff6b6b',
                                            'cancelled' => '#707070',
                                            'cancelado' => '#707070',
                                        ];
                                        $statusColor = $statusColors[strtolower($status)] ?? '#707070';
                                    @endphp
                                    <div class="w-full px-4 py-3">
                                        <div class="w-full flex items-center gap-6">
                                            <div class="flex-1 min-w-[100px]">
                                                <p class="text-xs font-semibold tracking-[-0.24px] text-white">
                                                    {{ $transaction->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <div class="flex-[2] min-w-[200px]">
                                                <p class="text-xs font-semibold tracking-[-0.24px] leading-[1.3] text-white">{{ $customerName }}</p>
                                                <p class="text-xs font-semibold tracking-[-0.24px] leading-[1.3] text-[#707070]">{{ $customerEmail }}</p>
                                            </div>
                                            <div class="flex-[1.5] min-w-[150px]">
                                                <div class="flex flex-col gap-1">
                                                    <div class="px-3 py-1 rounded inline-flex items-center gap-1 size-fit bg-[#161616]">
                                                        @if(strtolower($paymentMethod) == 'pix')
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code h-4 w-4 text-[#707070]">
                                                                <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                                                <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                                                <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                                                <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                                                <path d="M21 21v.01"></path>
                                                                <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                                                <path d="M3 12h.01"></path>
                                                                <path d="M12 3h.01"></path>
                                                                <path d="M12 16v.01"></path>
                                                                <path d="M16 12h1"></path>
                                                                <path d="M21 12v.01"></path>
                                                                <path d="M12 21v-1"></path>
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-4 w-4 text-[#707070]">
                                                                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                                                <line x1="2" x2="22" y1="10" y2="10"></line>
                                                            </svg>
                                                        @endif
                                                        <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">{{ $paymentMethod }}</span>
                                                    </div>
                                                    <p class="text-xs font-semibold tracking-[-0.28px] text-white">R$ {{ number_format($transaction->amount ?? 0, 2, ',', '.') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex-1 min-w-[80px]">
                                                <div class="px-3 py-1 rounded flex items-center justify-center bg-[#161616]">
                                                    <span class="text-xs font-semibold tracking-[-0.24px] text-center" style="color: {{ $statusColor }};">{{ $status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex flex-col items-center justify-center py-8">
                                    <span class="text-[14px] font-semibold font-['Manrope'] tracking-[-0.28px] text-[#707070]">Nenhuma transação encontrada</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Table -->
                <div class="md:hidden w-full">
                    <div class="w-full">
                        <div class="w-full px-4 py-3 border-b border-[#1F1F1F]">
                            <div class="w-full flex items-center gap-2 text-[10px]">
                                <div class="flex-1">
                                    <div class="font-['Manrope',_sans-serif] font-medium tracking-[-0.24px] text-[#707070]">Data</div>
                                </div>
                                <div class="flex-[2]">
                                    <div class="font-['Manrope',_sans-serif] font-medium tracking-[-0.24px] text-[#707070]">Descrição</div>
                                </div>
                                <div class="flex-[1.5]">
                                    <div class="font-['Manrope',_sans-serif] font-medium tracking-[-0.24px] text-[#707070]">Pagamento/Valor</div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-['Manrope',_sans-serif] font-medium tracking-[-0.24px] text-[#707070]">Status</div>
                                </div>
                            </div>
                        </div>
                        <div class="w-full flex flex-col">
                            @if($recentTransactions && $recentTransactions->count() > 0)
                                @foreach($recentTransactions as $transaction)
                                    @php
                                        $customerData = is_string($transaction->customer_data) ? json_decode($transaction->customer_data, true) : $transaction->customer_data;
                                        $customerName = $customerData['name'] ?? 'N/A';
                                        $customerEmail = $customerData['email'] ?? 'N/A';
                                        $paymentMethod = ucfirst($transaction->payment_method ?? 'N/A');
                                        $status = ucfirst($transaction->status ?? 'N/A');
                                        
                                        // Cores do status
                                        $statusColors = [
                                            'paid' => '#22C672',
                                            'pago' => '#22C672',
                                            'pending' => '#ffa782',
                                            'pendente' => '#ffa782',
                                            'refunded' => '#ff6b6b',
                                            'reembolsado' => '#ff6b6b',
                                            'refund' => '#ff6b6b',
                                            'cancelled' => '#707070',
                                            'cancelado' => '#707070',
                                        ];
                                        $statusColor = $statusColors[strtolower($status)] ?? '#707070';
                                    @endphp
                                    <div class="w-full px-4 py-3 border-b border-[#1F1F1F]">
                                        <div class="w-full flex items-center gap-2">
                                            <div class="flex-1">
                                                <p class="text-[10px] font-semibold tracking-[-0.24px] text-white">
                                                    {{ $transaction->created_at->format('d/m/Y') }}
                                                </p>
                                            </div>
                                            <div class="flex-[2] min-w-0">
                                                <p class="text-[10px] font-semibold tracking-[-0.24px] leading-[1.3] text-white truncate">{{ $customerName }}</p>
                                                <p class="text-[10px] font-semibold tracking-[-0.24px] leading-[1.3] text-[#707070] truncate">{{ $customerEmail }}</p>
                                            </div>
                                            <div class="flex-[1.5] min-w-0">
                                                <div class="flex flex-col gap-1">
                                                    <div class="px-2 py-0.5 rounded inline-flex items-center gap-1 size-fit bg-[#161616]">
                                                        @if(strtolower($paymentMethod) == 'pix')
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code h-3 w-3 text-[#707070]">
                                                                <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                                                <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                                                <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                                                <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                                                <path d="M21 21v.01"></path>
                                                                <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                                                <path d="M3 12h.01"></path>
                                                                <path d="M12 3h.01"></path>
                                                                <path d="M12 16v.01"></path>
                                                                <path d="M16 12h1"></path>
                                                                <path d="M21 12v.01"></path>
                                                                <path d="M12 21v-1"></path>
                                                            </svg>
                                                        @else
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-3 w-3 text-[#707070]">
                                                                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                                                <line x1="2" x2="22" y1="10" y2="10"></line>
                                                            </svg>
                                                        @endif
                                                        <span class="font-['Manrope'] font-semibold text-[10px] tracking-[-0.24px] text-[#707070]">{{ $paymentMethod }}</span>
                                                    </div>
                                                    <p class="text-[10px] font-semibold tracking-[-0.24px] text-white">R$ {{ number_format($transaction->amount ?? 0, 2, ',', '.') }}</p>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <div class="px-2 py-0.5 rounded flex items-center justify-center bg-[#161616]">
                                                    <span class="text-[10px] font-semibold tracking-[-0.24px] text-center" style="color: {{ $statusColor }};">{{ $status }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="flex flex-col items-center justify-center py-8">
                                    <span class="text-[12px] font-medium font-['Manrope'] tracking-[-0.28px] text-[#707070]">Nenhuma transação encontrada</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de Depósito PIX -->
@if(request()->routeIs('dashboard'))
<div id="depositModal" class="deposit-modal">
    <div class="deposit-modal-backdrop" id="depositModalBackdrop"></div>
    <div class="deposit-modal-content">
        <button id="closeDepositModalBtn" class="deposit-modal-close" title="Fechar">
            <svg width="1em" height="1em" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M400 145.49 366.51 112 256 222.51 145.49 112 112 145.49 222.51 256 112 366.51 145.49 400 256 289.49 366.51 400 400 366.51 289.49 256z"></path>
            </svg>
        </button>
        <div class="deposit-modal-header">
            <h2>
                <svg fill="none" viewBox="0 0 24 24" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" class="deposit-modal-icon">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 15v3m0 3v-3m0 0h-3m3 0h3"></path>
                    <path fill="currentColor" fill-rule="evenodd" d="M5 5a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3h7.083A6 6 0 0 1 12 18c0-1.148.322-2.22.881-3.131A3 3 0 0 1 9 12a3 3 0 1 1 5.869.881A5.97 5.97 0 0 1 18 12c1.537 0 2.939.578 4 1.528V8a3 3 0 0 0-3-3zm7 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z" clip-rule="evenodd"></path>
                </svg>
                Adicionar saldo
            </h2>
        </div>
        <div class="deposit-modal-body">
            <div id="depositForm">
                <form id="depositFormElement" class="flex flex-col justify-between gap-y-4">
                    <div class="deposit-form-group">
                        <label for="depositAmount" class="deposit-form-group label">Valor *</label>
                        <div class="deposit-input-wrapper">
                            <span>R$</span>
                            <input id="depositAmount" name="amount" type="tel" placeholder="0,00" inputmode="numeric" value="0,00" class="v-money3">
                        </div>
                        <div class="deposit-quick-buttons">
                            <button type="button" class="deposit-quick-btn" data-amount="10">+R$ 10,00</button>
                            <button type="button" class="deposit-quick-btn" data-amount="20">+R$ 20,00</button>
                            <button type="button" class="deposit-quick-btn" data-amount="50">+R$ 50,00</button>
                            <button type="button" class="deposit-quick-btn" data-amount="100">+R$ 100,00</button>
                        </div>
                        <div class="deposit-fee-info">*Taxa de transferência: <span>3.50%</span></div>
                        <div class="deposit-fee-info">*Taxa mínima: <span>R$ 0.80</span></div>
                    </div>
                    <div class="deposit-form-group">
                        <label for="depositDescription" class="deposit-form-group label">Descrição</label>
                        <div class="deposit-input-wrapper">
                            <input id="depositDescription" name="description" type="text" placeholder="Do que se trata?" class="text-sm">
                        </div>
                    </div>
                    <hr class="deposit-divider">
                    <button type="submit" class="deposit-btn-primary" id="generatePixBtn">
                        <svg width="1em" height="1em" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" class="deposit-btn-icon">
                            <path fill="currentColor" d="M5.25 4C4.56 4 4 4.56 4 5.25V8a1 1 0 0 1-2 0V5.25A3.25 3.25 0 0 1 5.25 2H8a1 1 0 0 1 0 2zm0 16C4.56 20 4 19.44 4 18.75V16a1 1 0 1 0-2 0v2.75A3.25 3.25 0 0 0 5.25 22H8a1 1 0 1 0 0-2zM20 5.25C20 4.56 19.44 4 18.75 4H16a1 1 0 1 1 0-2h2.75A3.25 3.25 0 0 1 22 5.25V8a1 1 0 1 1-2 0zM18.75 20c.69 0 1.25-.56 1.25-1.25V16a1 1 0 1 1 2 0v2.75A3.25 3.25 0 0 1 18.75 22H16a1 1 0 1 1 0-2zM7 7h3v3H7zm7 3h-4v4H7v3h3v-3h4v3h3v-3h-3zm0 0V7h3v3z"></path>
                        </svg>
                        Gerar QR Code
                    </button>
                </form>
            </div>
            
            <div id="depositQRCode" class="deposit-qr-hidden">
                <div class="deposit-qr-section">
                    <h2>Escaneie o QR Code abaixo</h2>
                    <div class="deposit-qr-wrapper">
                        <div class="deposit-qr-code">
                            <img id="qrCodeImage" src="" alt="QR Code PIX" class="deposit-qr-image">
                            <img src="{{ asset('favicon.svg') }}" class="deposit-qr-logo" alt="Logo" id="qrLogoFavicon">
                        </div>
                    </div>
                    <div class="deposit-amount-box">
                        <div class="deposit-amount-label">Valor do depósito:</div>
                        <strong class="deposit-amount-value" id="depositAmountDisplay">R$ 0,00</strong>
                        <div class="deposit-pix-code-wrapper">
                            <input id="pixCode" type="text" readonly placeholder="..." value="">
                            <button type="button" class="deposit-copy-btn" id="copyPixCodeBtn">
                                <svg viewBox="0 0 448 512" fill="currentColor" width="1em" height="1em" xmlns="http://www.w3.org/2000/svg" class="deposit-copy-icon">
                                    <path d="M208 0h124.1C344.8 0 357 5.1 366 14.1L433.9 82c9 9 14.1 21.2 14.1 33.9V336c0 26.5-21.5 48-48 48H208c-26.5 0-48-21.5-48-48V48c0-26.5 21.5-48 48-48zM48 128h80v64H64v256h192v-32h64v48c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V176c0-26.5 21.5-48 48-48z"></path>
                                </svg>
                                <span class="hidden sm:inline">Copiar</span>
                            </button>
                        </div>
                    </div>
                    <ol class="deposit-instructions">
                        <li>
                            <span>1</span>
                            <span>Abra o app do seu banco.</span>
                        </li>
                        <li>
                            <span>2</span>
                            <span>Selecione a opção de pagamento por QR Code.</span>
                        </li>
                        <li>
                            <span>3</span>
                            <span>Escaneie o QR Code acima.</span>
                        </li>
                        <li>
                            <span>4</span>
                            <span>Confirme o pagamento.</span>
                        </li>
                    </ol>
                    <button type="button" class="deposit-paid-btn" id="checkDepositStatusBtn">
                        Já paguei
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Ícone Flutuante WhatsApp -->
<a href="https://wa.me/5511999999999" target="_blank" class="whatsapp-float">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
    </svg>
</a>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-new.css') }}">
<link rel="stylesheet" href="{{ asset('css/deposit-modal.css') }}">
<style>
    .globe-filters {
        background: #161616;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid #2D2D2D;
    }
    
    .globe-filters label {
        user-select: none;
    }
    
    .globe-filters label:hover {
        background: #1f1f1f !important;
        border-color: #D4AF37 !important;
    }
    
    .globe-filters input[type="checkbox"] {
        cursor: pointer;
    }
    
    .globe-filters .date-selector-btn {
        margin: 0;
    }
    
    #globe-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        aspect-ratio: 1 / 1; /* Container totalmente quadrado */
        min-height: 500px; /* Altura mínima no desktop */
        margin: 0 auto; /* Centraliza o container */
        box-sizing: border-box;
        touch-action: none; /* Previne scroll no mobile */
        -webkit-touch-callout: none; /* Previne menu de contexto no iOS */
        -webkit-user-select: none; /* Previne seleção de texto */
        user-select: none;
    }
    
    #globeViz {
        touch-action: none; /* Previne scroll no mobile */
        -webkit-touch-callout: none;
    }
    
    /* Desktop: container maior para não cortar bordas */
    @media (min-width: 769px) {
        #globe-container {
            min-height: 600px;
            max-width: 800px; /* Limite maior no desktop */
        }
    }
    
    /* Tablet */
    @media (min-width: 481px) and (max-width: 768px) {
        #globe-container {
            min-height: 500px;
            max-width: 100%;
        }
    }
    
    #globeViz {
        width: 100% !important;
        height: 100% !important;
        position: relative;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }
    
    #globeViz canvas {
        display: block !important;
        margin: 0 auto !important;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 95% !important; /* 95% para não cortar bordas */
        height: 95% !important; /* 95% para não cortar bordas */
        max-width: 95% !important;
        max-height: 95% !important;
        object-fit: contain; /* Garante que o globo não seja cortado */
        box-sizing: border-box;
    }
    
    /* Mobile: ajuste para telas menores */
    @media (max-width: 480px) {
        #globe-container {
            max-width: 100%;
            aspect-ratio: 1 / 1;
            min-height: 300px;
        }
    }
</style>
@endpush

@push('scripts')
@if(request()->routeIs('dashboard'))
<script>
window.depositCreateRoute = '{{ route('deposit.create') }}';
window.depositStatusRoute = '{{ route('deposit.status', ['transaction' => ':id']) }}';
</script>
<script src="{{ asset('js/deposit-modal.js') }}"></script>
@endif
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/globe.gl"></script>
<script>
// Dados de localização das vendas (inicial)
window.salesLocations = {!! json_encode($salesLocations ?? []) !!};
window.globeFilters = {
    showPaid: true,
    showPending: true,
    dateFrom: '{{ $startDate->format('Y-m-d') }}',
    dateTo: '{{ $endDate->format('Y-m-d') }}'
};
// Inicializar datas do globo
window.globeSelectedStartDate = '{{ $startDate->format('Y-m-d') }}';
window.globeSelectedEndDate = '{{ $endDate->format('Y-m-d') }}';
console.log('Vendas carregadas:', window.salesLocations);
window.dashboardSalesData = {!! json_encode($salesChartData ?? []) !!};
window.dashboardStartDate = '{{ $startDate->format('Y-m-d') }}';
window.dashboardEndDate = '{{ $endDate->format('Y-m-d') }}';
window.dashboardRoute = '{{ route('dashboard') }}';

document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart');
    if (ctx) {
        const salesData = window.dashboardSalesData || {};
        const labels = salesData.labels || [];
        const sales = salesData.sales || [];
        const pending = salesData.pending || [];
        const refunded = salesData.refunded || [];
        
        // Criar gráfico com os 3 datasets
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Vendas',
                        data: sales,
                        borderColor: '#D4AF37',
                        backgroundColor: 'rgba(106, 0, 0, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        hidden: false
                    },
                    {
                        label: 'Vendas Pendentes',
                        data: pending,
                        borderColor: '#FF9D3A',
                        backgroundColor: 'rgba(255, 157, 58, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        hidden: false
                    },
                    {
                        label: 'Reembolsos',
                        data: refunded,
                        borderColor: '#ff6b6b',
                        backgroundColor: 'rgba(255, 107, 107, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        hidden: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false // Legenda customizada no HTML
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#707070',
                            borderDash: [3, 3]
                        },
                        ticks: {
                            color: '#707070',
                            font: {
                                size: 12,
                                weight: 450
                            },
                            callback: function(value) {
                                return 'R$ ' + value.toFixed(2).replace('.', ',');
                            }
                        },
                        border: {
                            color: '#1f1f1f'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#707070'
                        },
                        border: {
                            color: '#1f1f1f'
                        }
                    }
                }
            }
        });
        
        // Adicionar interatividade na legenda customizada
        const legendItems = document.querySelectorAll('.graphic-legend > div');
        let activeFilter = 'all'; // 'all', 'sales', 'pending', 'refunded'
        
        legendItems.forEach((item, index) => {
            item.style.cursor = 'pointer';
            item.style.opacity = '1';
            item.style.transition = 'opacity 0.2s';
            
            item.addEventListener('click', function() {
                const legendText = this.querySelector('span:last-child')?.textContent.trim();
                
                // Resetar opacidade de todos os itens
                legendItems.forEach(i => {
                    i.style.opacity = '1';
                });
                
                if (legendText === 'Vendas') {
                    if (activeFilter === 'sales') {
                        // Se já está mostrando apenas vendas, mostrar tudo
                        chart.data.datasets[0].hidden = false;
                        chart.data.datasets[1].hidden = false;
                        chart.data.datasets[2].hidden = false;
                        activeFilter = 'all';
                        this.style.opacity = '1';
                    } else {
                        // Mostrar todas as vendas (pagas, pendentes, reembolsos)
                        chart.data.datasets[0].hidden = false;
                        chart.data.datasets[1].hidden = false;
                        chart.data.datasets[2].hidden = false;
                        activeFilter = 'sales';
                        this.style.opacity = '0.6';
                    }
                } else if (legendText === 'Vendas Pendentes') {
                    if (activeFilter === 'pending') {
                        // Se já está mostrando apenas pendentes, mostrar tudo
                        chart.data.datasets[0].hidden = false;
                        chart.data.datasets[1].hidden = false;
                        chart.data.datasets[2].hidden = false;
                        activeFilter = 'all';
                        this.style.opacity = '1';
                    } else {
                        // Mostrar apenas pendentes
                        chart.data.datasets[0].hidden = true;
                        chart.data.datasets[1].hidden = false;
                        chart.data.datasets[2].hidden = true;
                        activeFilter = 'pending';
                        this.style.opacity = '0.6';
                    }
                } else if (legendText === 'Reembolsos') {
                    if (activeFilter === 'refunded') {
                        // Se já está mostrando apenas reembolsos, mostrar tudo
                        chart.data.datasets[0].hidden = false;
                        chart.data.datasets[1].hidden = false;
                        chart.data.datasets[2].hidden = false;
                        activeFilter = 'all';
                        this.style.opacity = '1';
                    } else {
                        // Mostrar apenas reembolsos
                        chart.data.datasets[0].hidden = true;
                        chart.data.datasets[1].hidden = true;
                        chart.data.datasets[2].hidden = false;
                        activeFilter = 'refunded';
                        this.style.opacity = '0.6';
                    }
                }
                
                chart.update();
            });
        });
        
        // Armazenar referência do gráfico globalmente para uso futuro
        window.salesChart = chart;
    }

    const depositBtn = document.getElementById('openDepositModalBtn');
    const depositBtnSecondary = document.getElementById('openDepositModalBtnSecondary');
    
    function openDepositModalHandler(e) {
        e.preventDefault();
        if (typeof openDepositModal === 'function') {
            openDepositModal();
        } else {
            const modal = document.getElementById('depositModal');
            if (modal) {
                modal.classList.add('show');
            }
        }
    }
    
    if (depositBtn) {
        depositBtn.addEventListener('click', openDepositModalHandler);
    }
    
    if (depositBtnSecondary) {
        depositBtnSecondary.addEventListener('click', openDepositModalHandler);
    }
    
    const depositBtnGrid = document.getElementById('openDepositModalBtnGrid');
    if (depositBtnGrid) {
        depositBtnGrid.addEventListener('click', openDepositModalHandler);
    }

    // Date Picker Modal
    const dateSelectorBtn = document.getElementById('dateSelectorBtn');
    const datePickerModal = document.getElementById('datePickerModal');
    const datePickerOverlay = datePickerModal?.querySelector('.date-picker-overlay');
    let selectedStartDate = null;
    let selectedEndDate = null;
    let isSelectingRange = false;

    // Função para formatar data em português
    function formatDatePT(date) {
        const months = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
        return `${date.getDate()} de ${months[date.getMonth()]} de ${date.getFullYear()}`;
    }

    // Função para gerar calendário
    function generateCalendar(containerElement, year, month) {
        const months = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
        const weekdays = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sab'];
        
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();
        
        let html = `
            <div class="calendar-month">
                <div class="calendar-header">
                    <h3>${months[month]} ${year}</h3>
                </div>
                <div class="calendar-weekdays">
                    ${weekdays.map(day => `<div class="calendar-weekday">${day}</div>`).join('')}
                </div>
                <div class="calendar-days">
        `;
        
        // Dias do mês anterior
        for (let i = firstDay - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            html += `<div class="calendar-day other-month">${day}</div>`;
        }
        
        // Dias do mês atual
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const date = new Date(year, month, day);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            date.setHours(0, 0, 0, 0);
            const isToday = date.getTime() === today.getTime();
            const isSelected = (selectedStartDate && dateStr === selectedStartDate) || (selectedEndDate && dateStr === selectedEndDate);
            const isInRange = selectedStartDate && selectedEndDate && dateStr >= selectedStartDate && dateStr <= selectedEndDate;
            
            let classes = 'calendar-day';
            if (isToday) classes += ' today';
            if (isSelected) classes += ' selected';
            if (isInRange) classes += ' in-range';
            
            html += `<div class="${classes}" data-date="${dateStr}">${day}</div>`;
        }
        
        // Dias do próximo mês
        const totalCells = firstDay + daysInMonth;
        const remainingCells = 42 - totalCells; // 6 semanas * 7 dias
        for (let day = 1; day <= remainingCells && day <= 14; day++) {
            html += `<div class="calendar-day other-month">${day}</div>`;
        }
        
        html += `
                </div>
            </div>
        `;
        
        containerElement.innerHTML = html;
        
        // Adicionar event listeners
        containerElement.querySelectorAll('.calendar-day:not(.other-month)').forEach(day => {
            day.addEventListener('click', function() {
                const dateStr = this.getAttribute('data-date');
                handleDateClick(dateStr);
            });
        });
    }


    // Função para atualizar calendário
    function updateCalendar() {
        const container = document.getElementById('calendarContainer');
        if (!container) return;
        
        const now = new Date();
        let currentMonth = now.getMonth();
        let currentYear = now.getFullYear();
        
        // Se temos datas selecionadas, mostrar o mês da data inicial
        if (selectedStartDate) {
            const startDate = new Date(selectedStartDate);
            currentMonth = startDate.getMonth();
            currentYear = startDate.getFullYear();
        }
        
        // Gerar dois meses
        container.innerHTML = '';
        const month1 = document.createElement('div');
        const month2 = document.createElement('div');
        month1.className = 'calendar-wrapper';
        month2.className = 'calendar-wrapper';
        
        generateCalendar(month1, currentYear, currentMonth);
        
        // Calcular próximo mês
        let nextMonth = currentMonth + 1;
        let nextYear = currentYear;
        if (nextMonth > 11) {
            nextMonth = 0;
            nextYear++;
        }
        generateCalendar(month2, nextYear, nextMonth);
        
        container.appendChild(month1);
        container.appendChild(month2);
    }

    // Opções rápidas de data
    const dateOptions = {
        'today': () => {
            const today = new Date();
            selectedStartDate = today.toISOString().split('T')[0];
            selectedEndDate = today.toISOString().split('T')[0];
            updateCalendar();
            updateDateSelectorText();
            applyDateFilter();
        },
        'yesterday': () => {
            const yesterday = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            selectedStartDate = yesterday.toISOString().split('T')[0];
            selectedEndDate = yesterday.toISOString().split('T')[0];
            updateCalendar();
            updateDateSelectorText();
            applyDateFilter();
        },
        '7days': () => {
            const end = new Date();
            const start = new Date();
            start.setDate(start.getDate() - 6);
            selectedStartDate = start.toISOString().split('T')[0];
            selectedEndDate = end.toISOString().split('T')[0];
            updateCalendar();
            updateDateSelectorText();
            applyDateFilter();
        },
        '30days': () => {
            const end = new Date();
            const start = new Date();
            start.setDate(start.getDate() - 29);
            selectedStartDate = start.toISOString().split('T')[0];
            selectedEndDate = end.toISOString().split('T')[0];
            updateCalendar();
            updateDateSelectorText();
            applyDateFilter();
        },
        'custom': () => {
            // Modo personalizado - usuário seleciona no calendário
            updateCalendar();
        },
        'clear': () => {
            selectedStartDate = null;
            selectedEndDate = null;
            const url = new URL(window.location.href);
            url.searchParams.delete('date_from');
            url.searchParams.delete('date_to');
            window.location.href = url.toString();
        }
    };

    // Atualizar texto do botão de seleção de data
    function updateDateSelectorText() {
        const dateSelectorText = document.getElementById('dateSelectorText');
        if (dateSelectorText && selectedStartDate && selectedEndDate) {
            const start = new Date(selectedStartDate);
            const end = new Date(selectedEndDate);
            const months = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'];
            const text = `${start.getDate()} de ${months[start.getMonth()]} de ${start.getFullYear()} - ${end.getDate()} de ${months[end.getMonth()]} de ${end.getFullYear()}`;
            dateSelectorText.textContent = text;
        }
    }

    // Aplicar filtro de data
    function applyDateFilter() {
        if (selectedStartDate && selectedEndDate) {
            // Atualizar datas do globo também
            window.globeSelectedStartDate = selectedStartDate;
            window.globeSelectedEndDate = selectedEndDate;
            
            // Dashboard principal - recarregar página (o globo será atualizado automaticamente com as novas datas)
            const url = new URL(window.location.href);
            url.searchParams.set('date_from', selectedStartDate);
            url.searchParams.set('date_to', selectedEndDate);
            window.location.href = url.toString();
        }
    }

    // Abrir modal - Dashboard principal (também atualiza o globo)
    if (dateSelectorBtn && datePickerModal) {
        dateSelectorBtn.addEventListener('click', function(e) {
            e.preventDefault();
            selectedStartDate = '{{ $startDate->format('Y-m-d') }}';
            selectedEndDate = '{{ $endDate->format('Y-m-d') }}';
            updateCalendar();
            updateApplyButton();
            datePickerModal.classList.add('active');
        });
    }

    // Fechar modal
    function closeDatePickerModal() {
        if (datePickerModal) {
            datePickerModal.classList.remove('active');
        }
    }

    if (datePickerOverlay) {
        datePickerOverlay.addEventListener('click', closeDatePickerModal);
    }

    // Botões de ação
    const cancelBtn = document.getElementById('datePickerCancelBtn');
    const applyBtn = document.getElementById('datePickerApplyBtn');

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeDatePickerModal);
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            if (selectedStartDate && selectedEndDate) {
                applyDateFilter();
            }
        });
    }

    // Opções rápidas
    document.querySelectorAll('.date-option-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.date-option-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            const option = this.getAttribute('data-option');
            if (dateOptions[option]) {
                dateOptions[option]();
            }
        });
    });

    // Atualizar calendário quando datas são selecionadas
    function handleDateClick(dateStr) {
        if (!selectedStartDate || (selectedStartDate && selectedEndDate)) {
            selectedStartDate = dateStr;
            selectedEndDate = null;
            isSelectingRange = true;
            // Ativar opção personalizado
            document.querySelectorAll('.date-option-item').forEach(i => i.classList.remove('active'));
            const customOption = document.querySelector('.date-option-item[data-option="custom"]');
            if (customOption) customOption.classList.add('active');
        } else if (isSelectingRange) {
            if (dateStr < selectedStartDate) {
                selectedEndDate = selectedStartDate;
                selectedStartDate = dateStr;
            } else {
                selectedEndDate = dateStr;
            }
            isSelectingRange = false;
            updateDateSelectorText();
        }
        updateCalendar();
        updateApplyButton();
    }

    // Atualizar estado do botão Aplicar
    function updateApplyButton() {
        const applyBtn = document.getElementById('datePickerApplyBtn');
        if (applyBtn) {
            if (selectedStartDate && selectedEndDate) {
                applyBtn.disabled = false;
            } else {
                applyBtn.disabled = true;
            }
        }
    }

    // Inicializar calendário e botão
    if (datePickerModal) {
        updateCalendar();
        updateApplyButton();
    }

    // Atualizar data do dashboard
    const dashboardDateEl = document.getElementById('dashboardDate');
    if (dashboardDateEl) {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            locale: 'pt-BR'
        };
        const formatter = new Intl.DateTimeFormat('pt-BR', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        dashboardDateEl.textContent = formatter.format(now);
    }

    // Inicializar Globo 3D (sempre, mesmo sem vendas)
    function initGlobe() {
        const globeContainer = document.getElementById('globeViz');
        if (!globeContainer) {
            console.error('Container do globo não encontrado');
            return;
        }
        
        if (typeof Globe === 'undefined') {
            console.error('Biblioteca Globe.gl não carregada - tentando novamente...');
            setTimeout(initGlobe, 200);
            return;
        }
        
        // Limpar container
        globeContainer.innerHTML = '';
        
        try {
            // Variável para controlar zoom (altitude)
            let currentAltitude = 5.0; // Altitude inicial (zoom padrão)
            
            // Inicializar globo com visual escuro (centralizado)
            const globe = Globe()(globeContainer)
                .globeImageUrl('//unpkg.com/three-globe/example/img/earth-dark.jpg')
                .showAtmosphere(false)
                .backgroundColor('rgba(0,0,0,1)')
                .pointOfView({ lat: 0, lng: 0, altitude: currentAltitude });
            
            // Garantir que o canvas fique centralizado e proporcional (sem cortar bordas)
            const updateCanvasSize = () => {
                const canvas = globeContainer.querySelector('canvas');
                const container = document.getElementById('globe-container');
                if (canvas && container) {
                    const containerSize = Math.min(container.offsetWidth, container.offsetHeight);
                    // Usar 95% do tamanho para não cortar as bordas do globo
                    const canvasSize = containerSize * 0.95;
                    canvas.style.display = 'block';
                    canvas.style.margin = '0 auto';
                    canvas.style.position = 'absolute';
                    canvas.style.top = '50%';
                    canvas.style.left = '50%';
                    canvas.style.transform = 'translate(-50%, -50%)';
                    canvas.style.width = canvasSize + 'px';
                    canvas.style.height = canvasSize + 'px';
                    canvas.style.objectFit = 'contain';
                    canvas.style.maxWidth = '95%';
                    canvas.style.maxHeight = '95%';
                }
            };
            
            // Atualizar tamanho inicial
            setTimeout(updateCanvasSize, 300);
            
            // Atualizar quando a janela redimensionar (incluindo zoom)
            window.addEventListener('resize', () => {
                setTimeout(updateCanvasSize, 100);
            });
            
            // Armazenar referência global
            window.globeInstance = globe;

            // Carregar países do mundo
            fetch('https://raw.githubusercontent.com/holtzy/D3-graph-gallery/master/DATA/world.geojson')
                .then(res => res.json())
                .then(countries => {
                    globe.polygonsData(countries.features)
                        .polygonAltitude(0.005)
                        .polygonCapColor(d => {
                            const countryName = d.properties.NAME || d.properties.NAME_LONG || d.properties.ADMIN || d.properties.name;
                            const hasSales = window.salesLocations && window.salesLocations.some(loc => {
                                const locCountry = loc.country || '';
                                return locCountry === countryName || 
                                       locCountry.includes(countryName) || 
                                       countryName.includes(locCountry) ||
                                       locCountry.toLowerCase() === countryName.toLowerCase();
                            });
                            return hasSales ? 'rgba(173, 255, 47, 0.6)' : 'rgba(30, 30, 30, 0.2)';
                        })
                        .polygonSideColor(d => {
                            const countryName = d.properties.NAME || d.properties.NAME_LONG || d.properties.ADMIN || d.properties.name;
                            const hasSales = window.salesLocations && window.salesLocations.some(loc => {
                                const locCountry = loc.country || '';
                                return locCountry === countryName || 
                                       locCountry.includes(countryName) || 
                                       countryName.includes(locCountry) ||
                                       locCountry.toLowerCase() === countryName.toLowerCase();
                            });
                            return hasSales ? 'rgba(173, 255, 47, 0.5)' : 'rgba(30, 30, 30, 0.1)';
                        })
                        .polygonStrokeColor(() => 'rgba(80, 80, 80, 0.15)');
                })
                .catch(err => {
                    console.error('Erro ao carregar países:', err);
                });

            // Preparar pontos de vendas
            let pointsData = [];
            
            if (window.salesLocations && window.salesLocations.length > 0) {
                pointsData = window.salesLocations.map(location => ({
                    lat: parseFloat(location.lat) || 0,
                    lng: parseFloat(location.lng) || 0,
                    size: parseFloat(location.size) || 5,
                    originalSize: parseFloat(location.size) || 5,
                    color: '#ADFF2F',
                    count: parseInt(location.count) || 0,
                    amount: parseFloat(location.amount) || 0,
                    country: location.country || '',
                    city: location.city || ''
                })).filter(p => p.lat !== 0 && p.lng !== 0); // Filtrar pontos inválidos
            } else {
                // Pontos de exemplo se não houver vendas (minúsculos)
                pointsData = [
                    { lat: -22.9068, lng: -43.1729, size: 0.15, originalSize: 0.15, color: '#ADFF2F', count: 0, amount: 0, country: 'Brasil', city: 'Rio de Janeiro' }
                ];
            }

            // Adicionar pontos no globo
            if (pointsData.length > 0) {
                globe.pointsData(pointsData)
                    .pointLat(d => d.lat)
                    .pointLng(d => d.lng)
                    .pointColor(d => d.color || '#22C672') // Verde (pagas), Amarelo (pendentes), Vermelho (estornadas)
                    .pointAltitude(0.005)
                    .pointRadius(d => d.size || 0.15)
                    .pointResolution(12)
                    .pointsMerge(true)
                    .pointLabel(d => {
                        if (d.count > 0) {
                            const statusText = d.status === 'paid' ? 'Pagas' : (d.status === 'pending' ? 'Pendentes' : (d.status === 'refunded' ? 'Estornadas' : 'Outras'));
                            const borderColor = d.color || '#22C672';
                            return `
                                <div style="background: rgba(0,0,0,0.95); padding: 10px; border-radius: 6px; color: white; font-size: 12px; border: 2px solid ${borderColor}; box-shadow: 0 0 20px ${borderColor}80;">
                                    <strong style="color: ${borderColor};">${d.city || d.country || 'Localização'}</strong><br>
                                    <span style="color: #22C672;">Pagas: ${d.paid_count || 0}</span><br>
                                    <span style="color: #FFD700;">Pendentes: ${d.pending_count || 0}</span><br>
                                    <span style="color: #D4AF37;">Estornadas: ${d.refunded_count || 0}</span><br>
                                    <span style="color: #FF9D3A;">Total: R$ ${d.amount.toFixed(2).replace('.', ',')}</span>
                                </div>
                            `;
                        }
                        return null;
                    });

                // Animação de pulso para pontos com muitas vendas
                let animationFrame;
                const animate = () => {
                    const time = Date.now() * 0.001;
                pointsData.forEach((point, i) => {
                    if (point.count > 5) {
                        const pulse = Math.sin(time * 2 + i) * 0.1 + 1; // Pulso bem menor
                        point.size = point.originalSize * pulse;
                    } else {
                        point.size = point.originalSize;
                    }
                });
                    globe.pointsData([...pointsData]);
                    animationFrame = requestAnimationFrame(animate);
                };
                animate();
            }

            // Rotação automática (pausa quando usuário está interagindo)
            let rotation = { lat: 0, lng: 0 };
            let isAutoRotating = true;
            let rotationInterval = null;
            let pauseTimeout = null;
            
            const startAutoRotation = () => {
                // NÃO limpar pauseTimeout aqui - ele deve ser respeitado!
                // Limpar apenas o intervalo anterior se existir
                if (rotationInterval) {
                    clearInterval(rotationInterval);
                    rotationInterval = null;
                }
                
                rotationInterval = setInterval(() => {
                    if (window.globeInstance && isAutoRotating && !isDragging) {
                        rotation.lng += 0.3;
                        window.globeInstance.pointOfView({
                            lat: rotation.lat,
                            lng: rotation.lng,
                            altitude: currentAltitude // Usa altitude atual (permite zoom)
                        }, 1000);
                    }
                }, 100);
            };
            
            const stopAutoRotation = () => {
                if (rotationInterval) {
                    clearInterval(rotationInterval);
                    rotationInterval = null;
                }
                if (pauseTimeout) {
                    clearTimeout(pauseTimeout);
                    pauseTimeout = null;
                }
            };

            // Controles de interação (MOUSE E TOUCH)
            let isDragging = false;
            let previousMousePosition = { x: 0, y: 0 };
            let previousTouchPosition = { x: 0, y: 0 };
            
            // Função para iniciar arrasto
            const startDrag = (x, y) => {
                isDragging = true;
                isAutoRotating = false; // Pausa rotação automática
                stopAutoRotation(); // Para completamente
                previousMousePosition = { x: x, y: y };
                previousTouchPosition = { x: x, y: y };
            };
            
            // Função para mover durante arrasto
            const moveDrag = (x, y) => {
                if (isDragging && window.globeInstance) {
                    const deltaX = x - previousMousePosition.x;
                    const deltaY = y - previousMousePosition.y;
                    
                    rotation.lng += deltaX * 0.5;
                    rotation.lat -= deltaY * 0.5;
                    rotation.lat = Math.max(-90, Math.min(90, rotation.lat));
                    
                    window.globeInstance.pointOfView({
                        lat: rotation.lat,
                        lng: rotation.lng,
                        altitude: currentAltitude // Usa altitude atual (permite zoom)
                    }, 0);
                    
                    previousMousePosition = { x: x, y: y };
                    previousTouchPosition = { x: x, y: y };
                }
            };
            
            // Função para parar arrasto
            const stopDrag = () => {
                if (isDragging) {
                    isDragging = false;
                    // Para completamente a rotação
                    stopAutoRotation();
                    // Limpar qualquer timeout anterior
                    if (pauseTimeout) {
                        clearTimeout(pauseTimeout);
                    }
                    // Espera 10 segundos ANTES de retomar rotação automática
                    pauseTimeout = setTimeout(() => {
                        if (!isDragging) { // Verificar se não está arrastando novamente
                            isAutoRotating = true;
                            startAutoRotation();
                        }
                    }, 10000); // 10 segundos
                }
            };
            
            // Eventos MOUSE (Desktop)
            globeContainer.addEventListener('mousedown', (e) => {
                e.preventDefault();
                startDrag(e.clientX, e.clientY);
            });

            globeContainer.addEventListener('mousemove', (e) => {
                if (isDragging) {
                    e.preventDefault();
                    moveDrag(e.clientX, e.clientY);
                }
            });

            globeContainer.addEventListener('mouseup', (e) => {
                e.preventDefault();
                stopDrag();
            });

            globeContainer.addEventListener('mouseleave', (e) => {
                stopDrag();
            });
            
            // Eventos TOUCH (Mobile)
            globeContainer.addEventListener('touchstart', (e) => {
                e.preventDefault();
                if (e.touches.length > 0) {
                    const touch = e.touches[0];
                    startDrag(touch.clientX, touch.clientY);
                }
            }, { passive: false });

            globeContainer.addEventListener('touchmove', (e) => {
                if (isDragging && e.touches.length > 0) {
                    e.preventDefault();
                    const touch = e.touches[0];
                    moveDrag(touch.clientX, touch.clientY);
                }
            }, { passive: false });

            globeContainer.addEventListener('touchend', (e) => {
                e.preventDefault();
                stopDrag();
            }, { passive: false });

            globeContainer.addEventListener('touchcancel', (e) => {
                e.preventDefault();
                stopDrag();
            }, { passive: false });
            
            // Iniciar rotação automática
            startAutoRotation();

            // Zoom HABILITADO - permite aumentar/diminuir o globo com scroll
            let zoomTimeout = null;
            globeContainer.addEventListener('wheel', (e) => {
                e.preventDefault();
                
                // Pausar rotação automática ao dar zoom
                isAutoRotating = false;
                stopAutoRotation();
                
                // Calcular novo zoom baseado no scroll
                const zoomSpeed = 0.1;
                const delta = e.deltaY > 0 ? zoomSpeed : -zoomSpeed;
                
                // Limitar zoom entre 1.5 (muito próximo) e 8.0 (muito afastado)
                currentAltitude = Math.max(1.5, Math.min(8.0, currentAltitude + delta));
                
                // Aplicar zoom imediatamente
                if (window.globeInstance) {
                    window.globeInstance.pointOfView({
                        lat: rotation.lat,
                        lng: rotation.lng,
                        altitude: currentAltitude
                    }, 200); // Animação suave de 200ms
                }
                
                // Limpar timeout anterior se existir
                if (zoomTimeout) {
                    clearTimeout(zoomTimeout);
                }
                
                // Após 10 segundos sem zoom, retomar rotação automática
                zoomTimeout = setTimeout(() => {
                    if (!isDragging) {
                        isAutoRotating = true;
                        startAutoRotation();
                    }
                }, 10000); // 10 segundos
            });
            
            // Função para recarregar vendas (sem filtros - todas as vendas com cores automáticas)
            window.reloadGlobeSales = function() {
                // Usar as datas do filtro principal do dashboard (vindas da URL)
                const urlParams = new URLSearchParams(window.location.search);
                let dateFrom = urlParams.get('date_from') || '{{ $startDate->format('Y-m-d') }}';
                let dateTo = urlParams.get('date_to') || '{{ $endDate->format('Y-m-d') }}';
                
                // Buscar dados (todas as vendas - cores automáticas)
                fetch(`{{ route('api.globe.sales') }}?date_from=${dateFrom}&date_to=${dateTo}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            window.salesLocations = data.locations || [];
                            
                            // Recarregar pontos no globo
                            let pointsData = [];
                            
            if (window.salesLocations && window.salesLocations.length > 0) {
                pointsData = window.salesLocations.map(location => ({
                    lat: parseFloat(location.lat) || 0,
                    lng: parseFloat(location.lng) || 0,
                    size: parseFloat(location.size) || 0.15,
                    originalSize: parseFloat(location.size) || 0.15,
                    color: location.color || '#22C672', // Verde (pagas), Amarelo (pendentes), Vermelho (estornadas)
                    count: parseInt(location.count) || 0,
                    amount: parseFloat(location.amount) || 0,
                    country: location.country || '',
                    city: location.city || '',
                    status: location.status || 'paid',
                    paid_count: parseInt(location.paid_count) || 0,
                    pending_count: parseInt(location.pending_count) || 0,
                    refunded_count: parseInt(location.refunded_count) || 0
                })).filter(p => p.lat !== 0 && p.lng !== 0);
                            } else {
                                // Se não houver vendas, limpar pontos
                                pointsData = [];
                            }
                            
                            // Atualizar pontos no globo
                            if (window.globeInstance) {
                                window.globeInstance.pointsData(pointsData)
                                    .pointLat(d => d.lat)
                                    .pointLng(d => d.lng)
                                    .pointColor(d => d.color || '#22C672') // Verde (pagas), Amarelo (pendentes), Vermelho (estornadas)
                                    .pointAltitude(0.005)
                                    .pointRadius(d => d.size || 0.15)
                                    .pointResolution(12)
                                    .pointsMerge(true)
                                    .pointLabel(d => {
                                        if (d.count > 0) {
                                            const borderColor = d.color || '#22C672';
                                            return `
                                                <div style="background: rgba(0,0,0,0.95); padding: 10px; border-radius: 6px; color: white; font-size: 12px; border: 2px solid ${borderColor}; box-shadow: 0 0 20px ${borderColor}80;">
                                                    <strong style="color: ${borderColor};">${d.city || d.country || 'Localização'}</strong><br>
                                                    <span style="color: #22C672;">Pagas: ${d.paid_count || 0}</span><br>
                                                    <span style="color: #FFD700;">Pendentes: ${d.pending_count || 0}</span><br>
                                                    <span style="color: #D4AF37;">Estornadas: ${d.refunded_count || 0}</span><br>
                                                    <span style="color: #FF9D3A;">Total: R$ ${d.amount.toFixed(2).replace('.', ',')}</span>
                                                </div>
                                            `;
                                        }
                                        return null;
                                    });
                                
                                // Recarregar países destacados (precisa recarregar o GeoJSON)
                                fetch('https://raw.githubusercontent.com/holtzy/D3-graph-gallery/master/DATA/world.geojson')
                                    .then(res => res.json())
                                    .then(countries => {
                                        if (window.globeInstance) {
                                            window.globeInstance
                                                .polygonsData(countries.features)
                                                .polygonCapColor(d => {
                                                    const countryName = d.properties.NAME || d.properties.NAME_LONG || d.properties.ADMIN || d.properties.name;
                                                    const hasSales = window.salesLocations && window.salesLocations.some(loc => {
                                                        const locCountry = loc.country || '';
                                                        return locCountry === countryName || 
                                                               locCountry.includes(countryName) || 
                                                               countryName.includes(locCountry) ||
                                                               locCountry.toLowerCase() === countryName.toLowerCase();
                                                    });
                                                    return hasSales ? 'rgba(173, 255, 47, 0.6)' : 'rgba(30, 30, 30, 0.2)';
                                                })
                                                .polygonSideColor(d => {
                                                    const countryName = d.properties.NAME || d.properties.NAME_LONG || d.properties.ADMIN || d.properties.name;
                                                    const hasSales = window.salesLocations && window.salesLocations.some(loc => {
                                                        const locCountry = loc.country || '';
                                                        return locCountry === countryName || 
                                                               locCountry.includes(countryName) || 
                                                               countryName.includes(locCountry) ||
                                                               locCountry.toLowerCase() === countryName.toLowerCase();
                                                    });
                                                    return hasSales ? 'rgba(173, 255, 47, 0.5)' : 'rgba(30, 30, 30, 0.1)';
                                                });
                                        }
                                    })
                                    .catch(err => {
                                        console.error('Erro ao recarregar países:', err);
                                    });
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao carregar vendas filtradas:', err);
                    });
            };
            
            // Recarregar vendas quando a página carregar (usa as datas da URL)
            window.reloadGlobeSales();
            
        } catch (error) {
            console.error('Erro ao inicializar globo:', error);
        }
    }
    
    // Inicializar globo quando tudo estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initGlobe, 1000);
        });
    } else {
        setTimeout(initGlobe, 1000);
    }
});
</script>
@endpush
@endsection
