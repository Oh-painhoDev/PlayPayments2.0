@extends('layouts.admin')

@section('title', 'Vendas Retidas')
@section('page-title', 'Vendas Retidas')
@section('page-description', 'Visualize todas as transações retidas pelo sistema')

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Retained -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ number_format($totalRetained) }}</p>
            <p class="text-xs text-gray-600">Total de Transações Retidas</p>
        </div>

        <!-- Total Amount -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totalRetainedAmount, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Valor Total Retido</p>
        </div>
        
        <!-- Total Refunded -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-green-600">R$ {{ number_format($totalRefundedAmount, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">{{ number_format($totalRefunded) }} Reembolsos</p>
        </div>
        
        <!-- Gateway Fees -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-purple-400">R$ {{ number_format($totalGatewayFees, 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Taxas de Gateway</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Buscar por ID, usuário..."
                    value="{{ request('search') }}"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 text-sm"
                >
            </div>
            
            <div>
                <input 
                    type="date" 
                    name="date_from" 
                    value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm"
                    placeholder="Data inicial"
                >
            </div>
            
            <div>
                <input 
                    type="date" 
                    name="date_to" 
                    value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm"
                    placeholder="Data final"
                >
            </div>
            
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Retained Sales Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($retainedSales as $transaction)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                <div class="text-gray-600 text-xs">{{ $transaction->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-mono text-sm">{{ substr($transaction->transaction_id, 0, 12) }}...</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $transaction->user->name }}</p>
                                    <p class="text-gray-600 text-sm">{{ $transaction->user->email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $transaction->formatted_amount }}</p>
                                    <p class="text-gray-600 text-sm">Taxa: {{ $transaction->formatted_fee_amount }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-gray-800 text-gray-700 rounded">
                                    {{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-600 border-green-500/30">
                                    Pago
                                </span>
                                @if($transaction->status === 'refunded' || $transaction->status === 'partially_refunded' || $transaction->status === 'chargeback')
                                <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-600 border-green-500/30 ml-1">
                                    {{ $transaction->status === 'refunded' ? 'Reembolsado' : ($transaction->status === 'partially_refunded' ? 'Reemb. Parcial' : 'Chargeback') }}
                                </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-yellow-500/20 text-yellow-400 border-yellow-500/30">
                                    Pendente
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.setup.retained-sales.details', $transaction->transaction_id) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                                        Ver
                                    </a>
                                    <form action="{{ route('admin.setup.retained-sales.return', $transaction->transaction_id) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja devolver esta venda para o usuário? Isso irá liberar o valor para a carteira do usuário e enviar uma notificação de pagamento.');">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-700 text-sm">
                                            Devolver
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma transação retida encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($retainedSales->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $retainedSales->links() }}
            </div>
        @endif
    </div>
</div>
@endsection