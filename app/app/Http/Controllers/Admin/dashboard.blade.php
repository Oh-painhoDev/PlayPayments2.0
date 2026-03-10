@extends('layouts.admin')

@section('title', 'Painel Administrativo')
@section('page-title', 'Dashboard Admin')
@section('page-description', 'Visão geral do sistema e estatísticas')

@section('content')
<div class="p-6 space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="bg-gray-950 rounded-lg p-6 border border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Total de Usuários</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="p-3 bg-blue-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-gray-950 rounded-lg p-6 border border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Total de Transações</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_transactions']) }}</p>
                </div>
                <div class="p-3 bg-green-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-gray-950 rounded-lg p-6 border border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Receita Total</p>
                    <p class="text-2xl font-bold text-white">R$ {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-purple-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Transactions -->
        <div class="bg-gray-950 rounded-lg p-6 border border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400">Transações Pendentes</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['pending_transactions']) }}</p>
                </div>
                <div class="p-3 bg-yellow-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Users -->
        <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Usuários Recentes</h3>
                <a href="{{ route('admin.users.index') }}" class="text-blue-400 hover:text-blue-300 text-sm">Ver todos</a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentUsers as $user)
                    <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg">
                        <div>
                            <p class="text-white font-medium">{{ $user->name }}</p>
                            <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-500">{{ $user->created_at->format('d/m/Y') }}</p>
                            @if($user->assignedGateway)
                                <span class="text-xs bg-blue-500/10 text-blue-400 px-2 py-1 rounded">{{ $user->assignedGateway->name }}</span>
                            @else
                                <span class="text-xs bg-gray-500/10 text-gray-400 px-2 py-1 rounded">Sem Gateway</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">Nenhum usuário encontrado</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Transações Recentes</h3>
                <a href="{{ route('admin.transactions.index') }}" class="text-blue-400 hover:text-blue-300 text-sm">Ver todas</a>
            </div>
            
            <div class="space-y-3">
                @forelse($recentTransactions as $transaction)
                    <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg">
                        <div>
                            <p class="text-white font-medium">{{ $transaction->formatted_amount }}</p>
                            <p class="text-gray-400 text-sm">{{ $transaction->user->name }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs px-2 py-1 rounded {{ $transaction->status_color }}">
                                {{ $transaction->status_label }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">{{ $transaction->created_at->format('d/m H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">Nenhuma transação encontrada</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection