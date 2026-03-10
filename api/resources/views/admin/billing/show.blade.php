@extends('layouts.admin')

@section('title', 'Detalhes de Faturamento')
@section('page-title', 'Faturamento: ' . $user->name)
@section('page-description', 'Detalhes completos de faturamento e saques da empresa')

@section('content')
<div class="p-6">
    <!-- Company Header -->
    <div class="bg-white rounded-lg border border-gray-300 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-gray-600">{{ $user->email }}</p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="px-2 py-1 text-xs rounded {{ $user->isPessoaFisica() ? 'bg-blue-500/10 text-blue-600' : 'bg-purple-500/10 text-purple-400' }}">
                        {{ $user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica' }}
                    </span>
                    <span class="text-gray-600 text-sm">{{ $user->formatted_document }}</span>
                    <span class="text-gray-600 text-sm">Membro desde {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a 
                    href="{{ route('admin.users.login.as.user', $user) }}" 
                    class="bg-orange-600 hover:bg-orange-700 text-gray-900 px-4 py-2 rounded-lg text-sm"
                    onclick="return confirm('Tem certeza que deseja acessar a conta deste usuário?');"
                >
                    Acessar Conta
                </a>
                <a href="{{ route('admin.users.show', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                    Ver Perfil
                </a>
                <a href="{{ route('admin.billing.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Sales -->
        <div class="bg-white rounded-lg p-4 border border-gray-300">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totals['total_sales'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Total em Vendas</p>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white rounded-lg p-4 border border-gray-300">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ number_format($totals['total_transactions']) }}</p>
            <p class="text-xs text-gray-600">Transações Pagas</p>
        </div>

        <!-- Total Withdrawals -->
        <div class="bg-white rounded-lg p-4 border border-gray-300">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-orange-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totals['total_withdrawn'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">{{ number_format($totals['total_withdrawals']) }} Saques Concluídos</p>
        </div>

        <!-- Net Balance -->
        <div class="bg-white rounded-lg p-4 border border-gray-300">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totals['net_balance'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Saldo Líquido</p>
        </div>
    </div>

    <!-- Transactions and Withdrawals Tabs -->
    <div x-data="{ activeTab: 'transactions' }" class="bg-white rounded-lg border border-gray-300 p-6">
        <!-- Tab Navigation -->
        <div class="flex border-b border-gray-300 mb-6">
            <button 
                @click="activeTab = 'transactions'" 
                :class="{ 'border-blue-500 text-blue-500': activeTab === 'transactions', 'border-transparent text-gray-600 hover:text-gray-800': activeTab !== 'transactions' }"
                class="px-4 py-2 border-b-2 font-medium text-sm focus:outline-none transition-colors"
            >
                Transações
            </button>
            <button 
                @click="activeTab = 'withdrawals'" 
                :class="{ 'border-blue-500 text-blue-500': activeTab === 'withdrawals', 'border-transparent text-gray-600 hover:text-gray-800': activeTab !== 'withdrawals' }"
                class="px-4 py-2 border-b-2 font-medium text-sm focus:outline-none transition-colors ml-4"
            >
                Saques
            </button>
        </div>

        <!-- Transactions Tab -->
        <div x-show="activeTab === 'transactions'">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transações Pagas</h3>
            
            @if($transactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Método</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 text-sm">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                        <div class="text-gray-600 text-xs">{{ $transaction->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-900 font-mono text-sm">{{ substr($transaction->transaction_id, 0, 12) }}...</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-gray-900 font-medium">{{ $transaction->customer_data['name'] ?? 'N/A' }}</p>
                                            <p class="text-gray-600 text-sm">{{ $transaction->customer_data['email'] ?? 'N/A' }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-gray-900 font-medium">{{ $transaction->formatted_amount }}</p>
                                            <p class="text-gray-600 text-sm">Taxa: {{ $transaction->formatted_fee_amount }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded">
                                            {{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.transactions.show', $transaction->transaction_id) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($transactions->hasPages())
                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">Nenhuma transação encontrada</p>
                </div>
            @endif
        </div>

        <!-- Withdrawals Tab -->
        <div x-show="activeTab === 'withdrawals'" x-cloak>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Saques Concluídos</h3>
            
            @if($withdrawals->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Valor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Taxa</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Chave PIX</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($withdrawals as $withdrawal)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 text-sm">{{ $withdrawal->created_at->format('d/m/Y') }}</div>
                                        <div class="text-gray-600 text-xs">{{ $withdrawal->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-gray-900 font-mono text-sm">{{ substr($withdrawal->withdrawal_id, 0, 12) }}...</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 font-medium">{{ $withdrawal->formatted_amount }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-600 text-sm">{{ $withdrawal->formatted_fee }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-gray-900 text-sm">{{ $withdrawal->pix_type_label }}</div>
                                        <div class="text-gray-600 text-xs">{{ Str::mask($withdrawal->pix_key, '*', 4) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.withdrawals.show', $withdrawal) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($withdrawals->hasPages())
                    <div class="mt-4">
                        {{ $withdrawals->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">Nenhum saque encontrado</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ensure Alpine.js is loaded
document.addEventListener('alpine:init', () => {
    // Alpine.js is ready
});
</script>
@endpush
@endsection