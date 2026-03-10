@extends('layouts.dashboard')

@section('title', 'Saques')
@section('page-title', 'Saques')
@section('page-description', 'Gerencie seus saques e transferências')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Section com Gradiente Verde BRPIX -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Gerenciar Saques</h1>
                <p class="text-green-100">Acompanhe suas solicitações de saque e transferências</p>
            </div>
            <a href="{{ route('wallet.create') }}" class="bg-white hover:bg-gray-100 text-green-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Solicitar Saque
            </a>
        </div>
    </div>

    <!-- Cards de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Saldo Disponível -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Saldo Disponível</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $user->formatted_wallet_balance }}</p>
        </div>

        <!-- Total de Saques -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Total de Saques</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $withdrawals->total() }}</p>
        </div>

        <!-- Saques Pendentes -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Pendentes</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $withdrawals->where('status', 'pending')->count() }}</p>
        </div>

        <!-- Saques Concluídos -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Concluídos</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $withdrawals->where('status', 'completed')->count() }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </span>
            Filtros
        </h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Status -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <option value="">Todos os Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processando</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluído</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falhou</option>
                </select>
            </div>

            <!-- Período -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Período</label>
                <select name="period" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <option value="">Todos os Períodos</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hoje</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>Últimos 7 dias</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Últimos 30 dias</option>
                </select>
            </div>

            <!-- Botões -->
            <div class="flex items-end space-x-3">
                <button type="submit" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg flex-1">
                    Filtrar
                </button>
                <button type="button" onclick="clearFilters()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all">
                    Limpar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Saques em Cards -->
    @if($withdrawals->isEmpty())
        <div class="bg-white rounded-2xl border-2 border-dashed border-gray-300 p-16 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Nenhum saque encontrado</h3>
            <p class="text-gray-500 mb-6">Comece criando seu primeiro saque para transferir seu saldo</p>
            <a href="{{ route('wallet.create') }}" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Solicitar Primeiro Saque
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6">
            @foreach($withdrawals as $withdrawal)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                    <!-- Withdrawal Header com Status Colorido -->
                    @php
                        $statusConfig = [
                            'pending' => ['label' => 'Pendente', 'from' => 'green-500', 'to' => 'emerald-500'],
                            'processing' => ['label' => 'Processando', 'from' => 'green-500', 'to' => 'emerald-500'],
                            'completed' => ['label' => 'Concluído', 'from' => 'emerald-500', 'to' => 'green-500'],
                            'cancelled' => ['label' => 'Cancelado', 'from' => 'green-500', 'to' => 'emerald-500'],
                            'failed' => ['label' => 'Falhou', 'from' => 'green-500', 'to' => 'emerald-500'],
                        ];
                        $config = $statusConfig[$withdrawal->status] ?? ['label' => ucfirst($withdrawal->status), 'from' => 'green-500', 'to' => 'emerald-500'];
                    @endphp
                    <div class="bg-gradient-to-r from-{{ $config['from'] }} to-{{ $config['to'] }} p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4 flex-1">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-white text-xl font-bold">{{ $withdrawal->formatted_amount }}</h3>
                                    <div class="flex items-center mt-1">
                                        <code class="text-white/90 text-sm font-mono">{{ substr($withdrawal->withdrawal_id, 0, 16) }}...</code>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl text-white font-semibold text-sm">
                                    {{ $config['label'] }}
                                </span>
                                <a href="{{ route('wallet.show', $withdrawal->withdrawal_id) }}" class="bg-white/20 backdrop-blur-sm hover:bg-white/30 p-3 rounded-xl transition-all" title="Ver detalhes">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Withdrawal Body -->
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Data -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide block mb-2">Data</span>
                                <p class="text-sm text-gray-900 font-semibold">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            
                            <!-- Taxa -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide block mb-2">Taxa</span>
                                <p class="text-sm text-gray-900 font-semibold">{{ $withdrawal->formatted_fee }}</p>
                            </div>
                            
                            <!-- Tipo PIX -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide block mb-2">Tipo PIX</span>
                                <p class="text-sm text-gray-900 font-semibold">{{ $withdrawal->pix_type_label }}</p>
                            </div>
                            
                            <!-- Chave PIX -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide block mb-2">Chave PIX</span>
                                <p class="text-sm text-gray-900 font-semibold font-mono">{{ Str::mask($withdrawal->pix_key, '*', 4) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($withdrawals->hasPages())
            <div class="flex justify-center mt-6">
                <x-pagination :paginator="$withdrawals->appends(request()->query())" />
            </div>
        @endif
    @endif
</div>

@push('scripts')
<script>
function clearFilters() {
    window.location.href = '{{ route('wallet.index') }}';
}
</script>
@endpush
@endsection
