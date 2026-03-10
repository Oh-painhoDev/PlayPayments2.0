@extends('layouts.admin')

@section('title', 'Detalhes de Lucro')
@section('page-title', 'Lucro: ' . $user->name)
@section('page-description', 'Detalhes completos de lucro e taxas da empresa')

@section('content')
<div class="p-6">
    <!-- Company Header -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h2>
                <p class="text-gray-600">{{ $user->email }}</p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="px-2 py-1 text-xs rounded {{ $user->isPessoaFisica() ? 'bg-blue-500/10 text-blue-600' : 'bg-purple-500/10 text-purple-400' }}">
                        {{ $user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica' }}
                    </span>
                    <span class="text-gray-500 text-sm">{{ $user->formatted_document }}</span>
                    <span class="text-gray-500 text-sm">Membro desde {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</span>
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
                <a href="{{ route('admin.profit.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <!-- Total Sales -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
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
        <div class="bg-white rounded-lg p-4 border border-gray-200">
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

        <!-- Total Fees -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totals['total_fees'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Taxas Cobradas</p>
        </div>

        <!-- Gateway Fees -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-green-600">R$ {{ number_format($totals['total_gateway_fees'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Taxas de Gateway</p>
        </div>

        <!-- Total Profit -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-emerald-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-emerald-400">R$ {{ number_format($totals['total_profit'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Lucro Total</p>
        </div>
    </div>

    <!-- Payment Method Breakdown -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Lucro por Método de Pagamento</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Transações</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Volume</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Taxas Cobradas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Taxas Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Lucro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Margem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($methodBreakdown as $method)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded 
                                    @if($method->payment_method == 'pix')
                                        bg-green-500/10 text-green-600
                                    @elseif($method->payment_method == 'credit_card')
                                        bg-blue-500/10 text-blue-600
                                    @elseif($method->payment_method == 'bank_slip')
                                        bg-orange-500/10 text-orange-400
                                    @else
                                        bg-gray-500/10 text-gray-600
                                    @endif
                                ">
                                    {{ strtoupper(str_replace('_', ' ', $method->payment_method)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-900">{{ number_format($method->count) }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-900 font-medium">R$ {{ number_format($method->total_amount, 2, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">R$ {{ number_format($method->total_fees, 2, ',', '.') }}</p>
                                    <p class="text-gray-600 text-xs">{{ number_format(($method->total_fees / max($method->total_amount, 0.01)) * 100, 2) }}%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-green-600 font-medium">R$ {{ number_format($method->gateway_fees, 2, ',', '.') }}</p>
                                    <p class="text-gray-600 text-xs">{{ number_format(($method->gateway_fees / max($method->total_amount, 0.01)) * 100, 2) }}%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-emerald-400 font-medium">R$ {{ number_format($method->profit, 2, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-900 font-medium">{{ number_format(($method->profit / max($method->total_fees, 0.01)) * 100, 2) }}%</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <h3 class="text-lg font-semibold text-gray-900 p-6 border-b border-gray-200">Transações e Lucro</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Taxa Cobrada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Taxa Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Lucro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Margem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm">{{ $transaction->created_at->format('d/m/Y') }}</div>
                                <div class="text-gray-600 text-xs">{{ $transaction->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-mono text-sm">{{ substr($transaction->transaction_id, 0, 12) }}...</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded 
                                    @if($transaction->payment_method == 'pix')
                                        bg-green-500/10 text-green-600
                                    @elseif($transaction->payment_method == 'credit_card')
                                        bg-blue-500/10 text-blue-600
                                    @elseif($transaction->payment_method == 'bank_slip')
                                        bg-orange-500/10 text-orange-400
                                    @else
                                        bg-gray-500/10 text-gray-600
                                    @endif
                                ">
                                    {{ strtoupper(str_replace('_', ' ', $transaction->payment_method)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $transaction->formatted_amount }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $transaction->formatted_fee_amount }}</p>
                                    <p class="text-gray-600 text-xs">{{ number_format(($transaction->fee_amount / $transaction->amount) * 100, 2) }}%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-green-600 font-medium">R$ {{ number_format($transaction->gateway_fee, 2, ',', '.') }}</p>
                                    <p class="text-gray-600 text-xs">{{ number_format(($transaction->gateway_fee / $transaction->amount) * 100, 2) }}%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-emerald-400 font-medium">R$ {{ number_format($transaction->transaction_profit, 2, ',', '.') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-900 font-medium">{{ number_format(($transaction->transaction_profit / max($transaction->fee_amount, 0.01)) * 100, 2) }}%</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma transação encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($transactions->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection