@extends('layouts.dashboard')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <div class="bg-[#000000] rounded-2xl space-y-6">
            <!-- Header -->
            <div class="content-stretch flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between relative size-full p-5 gap-6 md:gap-0">
                <div class="content-stretch flex flex-col gap-2.5 items-start justify-start leading-[0] relative shrink-0 text-nowrap">
                    <div class="font-['Manrope:Regular',_sans-serif] font-normal relative shrink-0 text-[28px] tracking-[-0.56px]">
                        <h1 class="leading-[1.2] text-nowrap whitespace-pre text-white">Extrato</h1>
                    </div>
                    <div class="font-['Manrope:SemiBold',_sans-serif] font-regular relative shrink-0 text-[12px] tracking-[-0.24px]">
                        <p class="leading-[1.3] text-nowrap whitespace-pre text-[#AAAAAA]">Histórico completo de entradas e saídas</p>
                    </div>
                </div>
                
                <!-- Botão Exportar -->
                <div class="flex gap-3">
                    <a href="{{ route('revenues.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-semibold transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" x2="12" y1="15" y2="3"></line>
                        </svg>
                        Exportar CSV
                    </a>
                </div>
            </div>
            
            <!-- Filtro de Data -->
            <div class="box-border flex flex-col gap-2.5 items-start justify-start p-[20px] relative rounded-2xl shrink-0 w-full bg-[#161616]">
                <form method="GET" action="{{ route('revenues.index') }}" class="flex flex-col md:flex-row gap-4 w-full">
                    <div class="flex items-center gap-2">
                        <label class="text-[12px] font-semibold text-[#AAAAAA] whitespace-nowrap">Período:</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="px-3 py-2 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                        <span class="text-[#AAAAAA] text-sm">até</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="px-3 py-2 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#D4AF37]">
                        <button type="submit" class="px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37] text-white rounded-lg text-sm font-medium transition-colors">
                            Filtrar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Resumo -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-5">
                <div class="p-5 rounded-2xl bg-[#161616]">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet text-[#D4AF37]">
                            <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path>
                            <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path>
                        </svg>
                        <span class="text-[12px] font-semibold text-[#AAAAAA]">Saldo</span>
                    </div>
                    <p class="text-[24px] font-medium text-white">R$ {{ number_format($availableBalance, 2, ',', '.') }}</p>
                </div>
                
                <div class="p-5 rounded-2xl bg-[#161616]">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock text-yellow-500">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span class="text-[12px] font-semibold text-[#AAAAAA]">Pendentes</span>
                    </div>
                    <p class="text-[24px] font-medium text-yellow-500">R$ {{ number_format($pendingBalance, 2, ',', '.') }}</p>
                </div>
                
                <div class="p-5 rounded-2xl bg-[#161616]">
                    <div class="flex items-center gap-2 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-circle text-green-500">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="8 12 12 16 16 12"></polyline>
                            <line x1="12" x2="12" y1="8" y2="16"></line>
                        </svg>
                        <span class="text-[12px] font-semibold text-[#AAAAAA]">Total de Entradas</span>
                    </div>
                    <p class="text-[24px] font-medium text-white">R$ {{ number_format($totalEntries, 2, ',', '.') }}</p>
                </div>
            </div>
            
            <!-- Tabela Desktop -->
            <div class="hidden md:block overflow-x-auto p-5">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-[#2d2d2d]">
                            <th class="text-left py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Data</th>
                            <th class="text-left py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Tipo</th>
                            <th class="text-left py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Descrição</th>
                            <th class="text-left py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Método</th>
                            <th class="text-right py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Valor Bruto</th>
                            <th class="text-right py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Taxa</th>
                            <th class="text-right py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Valor Líquido</th>
                            <th class="text-left py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginator as $movement)
                            <tr class="border-b border-[#1f1f1f] hover:bg-[#161616] transition-colors">
                                <td class="py-3 px-4 text-[12px] font-semibold text-white">
                                    {{ $movement['date']->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded text-[11px] font-semibold {{ $movement['type'] == 'entry' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500' }}">
                                        {{ $movement['type_label'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-[12px] font-semibold text-white">
                                    {{ $movement['description'] }}
                                </td>
                                <td class="py-3 px-4 text-[12px] font-semibold text-[#AAAAAA]">
                                    {{ strtoupper($movement['payment_method']) }}
                                </td>
                                <td class="py-3 px-4 text-[12px] font-semibold text-white text-right">
                                    R$ {{ number_format($movement['amount'], 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-[12px] font-semibold text-[#AAAAAA] text-right">
                                    R$ {{ number_format($movement['fee'], 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-[12px] font-semibold {{ $movement['net_amount'] >= 0 ? 'text-green-500' : 'text-red-500' }} text-right">
                                    {{ $movement['net_amount'] >= 0 ? '+' : '' }}R$ {{ number_format(abs($movement['net_amount']), 2, ',', '.') }}
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $statusColors = [
                                            'paid' => 'bg-green-500/20 text-green-500',
                                            'completed' => 'bg-green-500/20 text-green-500',
                                            'pending' => 'bg-yellow-500/20 text-yellow-500',
                                            'waiting_payment' => 'bg-yellow-500/20 text-yellow-500',
                                            'refunded' => 'bg-red-500/20 text-red-500',
                                            'defended' => 'bg-blue-500/20 text-blue-500',
                                        ];
                                        $statusColor = $statusColors[$movement['status']] ?? 'bg-gray-500/20 text-gray-500';
                                    @endphp
                                    <span class="px-2 py-1 rounded text-[11px] font-semibold {{ $statusColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $movement['status'])) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-[14px] font-semibold text-[#AAAAAA]">
                                    Nenhuma movimentação encontrada
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Cards Mobile -->
            <div class="md:hidden space-y-4 p-5">
                @forelse($paginator as $movement)
                    <div class="p-4 rounded-2xl bg-[#161616] space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[14px] font-semibold text-white">{{ $movement['description'] }}</p>
                                <p class="text-[12px] font-semibold text-[#AAAAAA]">{{ $movement['date']->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-[11px] font-semibold {{ $movement['type'] == 'entry' ? 'bg-green-500/20 text-green-500' : 'bg-red-500/20 text-red-500' }}">
                                {{ $movement['type_label'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-[#1f1f1f]">
                            <div>
                                <p class="text-[12px] font-semibold text-[#AAAAAA]">Método</p>
                                <p class="text-[12px] font-semibold text-white">{{ strtoupper($movement['payment_method']) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[12px] font-semibold text-[#AAAAAA]">Valor Líquido</p>
                                <p class="text-[14px] font-semibold {{ $movement['net_amount'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $movement['net_amount'] >= 0 ? '+' : '' }}R$ {{ number_format(abs($movement['net_amount']), 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-[14px] font-semibold text-[#AAAAAA]">
                        Nenhuma movimentação encontrada
                    </div>
                @endforelse
            </div>
            
            <!-- Paginação -->
            @if($paginator->hasPages())
                <div class="flex items-center justify-center w-full p-5">
                    <div class="flex items-center gap-2">
                        @if($paginator->onFirstPage())
                            <button disabled class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </button>
                        @else
                            <a href="{{ $paginator->previousPageUrl() }}" class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </a>
                        @endif
                        
                        <div class="flex items-center gap-1">
                            @foreach($paginator->getUrlRange(1, $paginator->lastPage()) as $page => $url)
                                @if($page == $paginator->currentPage())
                                    <div class="w-8 h-8 bg-[#D4AF37] rounded flex items-center justify-center">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white">{{ $page }}</span>
                                    </div>
                                @else
                                    <a href="{{ $url }}" class="w-8 h-8 bg-[#1f1f1f] rounded flex items-center justify-center hover:bg-[#2a2a2a] transition-colors">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-[#aaaaaa]">{{ $page }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                        
                        @if($paginator->hasMorePages())
                            <a href="{{ $paginator->nextPageUrl() }}" class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white transition-colors">
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </a>
                        @else
                            <button disabled class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

