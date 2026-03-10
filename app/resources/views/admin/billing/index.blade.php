@extends('layouts.admin')

@section('title', 'Faturamento por Empresas')
@section('page-title', 'Faturamento por Empresas')
@section('page-description', 'Visualize o faturamento de todas as empresas cadastradas no sistema')

@section('content')
<div class="p-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Companies -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">{{ number_format($totals['companies']) }}</p>
            <p class="text-xs text-gray-600">Empresas Cadastradas</p>
        </div>

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
            <p class="text-xs text-gray-600">Vendas Totais</p>
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

        <!-- Total Withdrawals -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-orange-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totals['total_withdrawn'], 2, ',', '.') }}</p>
            <p class="text-xs text-gray-600">Saques Concluídos</p>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Vendas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Transações</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Saques</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Saldo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($companies as $company)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">{{ $company->name }}</p>
                                    <p class="text-gray-600 text-sm">{{ $company->email }}</p>
                                    <p class="text-gray-500 text-xs">{{ $company->formatted_document }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded {{ $company->isPessoaFisica() ? 'bg-blue-500/10 text-blue-600' : 'bg-purple-500/10 text-purple-400' }}">
                                    {{ $company->isPessoaFisica() ? 'PF' : 'PJ' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">R$ {{ number_format($company->total_sales ?? 0, 2, ',', '.') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900">{{ number_format($company->total_transactions ?? 0) }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">R$ {{ number_format($company->total_withdrawn ?? 0, 2, ',', '.') }}</p>
                                    <p class="text-gray-600 text-xs">{{ number_format($company->total_withdrawals ?? 0) }} saques</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">R$ {{ number_format(($company->total_sales ?? 0) - ($company->total_withdrawn ?? 0), 2, ',', '.') }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.billing.show', $company) }}" class="text-blue-600 hover:text-blue-700 text-sm">
                                        Ver detalhes
                                    </a>
                                    <a href="{{ route('admin.users.show', $company) }}" class="text-green-600 hover:text-green-700 text-sm">
                                        Perfil
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma empresa encontrada
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($companies->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $companies->links() }}
            </div>
        @endif
    </div>
</div>
@endsection