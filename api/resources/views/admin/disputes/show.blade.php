@extends('layouts.admin')

@section('title', 'Detalhes da Infração')
@section('page-title', 'Detalhes da Infração')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header com Gradiente -->
    <div class="bg-gradient-to-r from-red-600 via-orange-500 to-yellow-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">⚠️ Infração #{{ substr($dispute->dispute_id, 4, 8) }}</h1>
                <p class="text-red-100">Detalhes completos da disputa e defesa do cliente</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.setup.disputes') }}" class="bg-white hover:bg-gray-100 text-red-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-4 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-green-800 font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    @foreach($errors->all() as $error)
                        <p class="text-red-800 font-semibold">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Status da Infração</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-gray-600">Status Atual:</span>
                        @php
                            $statusConfig = [
                                'pending' => ['label' => 'Pendente', 'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'border' => 'border-yellow-300'],
                                'responded' => ['label' => 'Respondida', 'bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300'],
                                'defended' => ['label' => 'Defendida', 'bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-300'],
                                'defense_rejected' => ['label' => 'Defesa Rejeitada', 'bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-300'],
                                'refunded' => ['label' => 'Reembolsada', 'bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300'],
                            ];
                            $config = $statusConfig[$dispute->status] ?? ['label' => 'Desconhecido', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-300'];
                        @endphp
                        <span class="px-4 py-2 text-sm font-bold rounded-xl border-2 {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                            {{ $config['label'] }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-semibold text-gray-600">Nível de Risco:</span>
                        @php
                            $riskConfig = [
                                'LOW' => ['label' => 'Baixo', 'bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-300'],
                                'MED' => ['label' => 'Médio', 'bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'border' => 'border-orange-300'],
                                'HIGH' => ['label' => 'Alto', 'bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-300'],
                            ];
                            $riskConfigItem = $riskConfig[$dispute->risk_level] ?? ['label' => 'N/A', 'bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-300'];
                        @endphp
                        <span class="px-4 py-2 text-sm font-bold rounded-xl border-2 {{ $riskConfigItem['bg'] }} {{ $riskConfigItem['text'] }} {{ $riskConfigItem['border'] }}">
                            {{ $riskConfigItem['label'] }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-600">Tipo de Disputa:</span>
                        @php
                            $typeLabels = [
                                'chargeback' => 'Chargeback',
                                'fraud' => 'Fraude',
                                'unauthorized' => 'Não Autorizada',
                                'not_received' => 'Não Recebido',
                                'defective' => 'Defeituoso',
                                'other' => 'Outro',
                            ];
                        @endphp
                        <span class="px-4 py-2 text-sm font-bold rounded-xl border-2 bg-indigo-100 text-indigo-800 border-indigo-300">
                            {{ $typeLabels[$dispute->dispute_type] ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Informações da Transação -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-blue-50 to-white border-b-2 border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </span>
                        Detalhes da Transação
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-600">ID da Transação:</span>
                        <span class="text-sm text-gray-900 font-mono">{{ $dispute->transaction->transaction_id }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-600">Valor:</span>
                        <span class="text-lg font-bold text-green-600">R$ {{ number_format($dispute->amount, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-600">Data da Transação:</span>
                        <span class="text-sm text-gray-900">{{ $dispute->transaction->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm font-semibold text-gray-600">Gateway:</span>
                        <span class="text-sm text-gray-900">{{ $dispute->transaction->gateway->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Defesa do Cliente -->
            @if($dispute->defense_details || $dispute->defense_file)
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-green-50 to-white border-b-2 border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        Defesa do Cliente
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($dispute->defense_details)
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Detalhes da Defesa:</label>
                            <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $dispute->defense_details }}</p>
                            </div>
                        </div>
                    @endif

                    @if($dispute->defense_file)
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Arquivo Anexado:</label>
                            <a href="{{ $dispute->defense_file }}" target="_blank" 
                                class="inline-flex items-center px-4 py-3 bg-blue-50 border-2 border-blue-300 text-blue-700 rounded-xl hover:bg-blue-100 transition-all">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download PDF
                            </a>
                        </div>
                    @endif

                    @if($dispute->responded_at)
                        <div class="text-xs text-gray-500">
                            Respondida em: {{ $dispute->responded_at->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Ações do Admin -->
            @if($dispute->status === 'responded')
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-purple-50 to-white border-b-2 border-purple-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </span>
                        Ações do Administrador
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <form method="POST" action="{{ route('admin.setup.disputes.accept-defense', $dispute) }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Notas do Administrador (opcional):</label>
                            <textarea name="admin_notes" rows="3" 
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="Adicione notas sobre a decisão..."></textarea>
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" 
                                class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Aceitar Defesa
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('admin.setup.disputes.reject-defense', $dispute) }}" onsubmit="return confirm('Tem certeza que deseja rejeitar esta defesa?')">
                        @csrf
                        <button type="submit" 
                            class="w-full bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Rejeitar Defesa
                        </button>
                    </form>
                </div>
            </div>
            @endif

            <!-- Notas do Admin (somente leitura) -->
            @if($dispute->admin_notes && $dispute->status !== 'responded')
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900">Notas do Administrador</h3>
                </div>
                <div class="p-6">
                    <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $dispute->admin_notes }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Coluna Lateral -->
        <div class="space-y-6">
            <!-- Informações do Usuário -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-indigo-50 to-white border-b-2 border-indigo-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        Informações do Usuário
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nome</span>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $dispute->user->name }}</p>
                    </div>
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $dispute->user->email }}</p>
                    </div>
                    <div class="pb-3 border-b border-gray-100">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Documento</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $dispute->user->document }}</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.users.show', $dispute->user) }}" 
                            class="mt-3 block w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-4 py-2 rounded-xl font-semibold transition-all text-center text-sm">
                            Ver Perfil Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-teal-50 to-white border-b-2 border-teal-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Timeline
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-red-500 rounded-full"></div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-semibold text-gray-900">Infração Criada</p>
                                <p class="text-xs text-gray-500">{{ $dispute->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($dispute->responded_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-blue-500 rounded-full"></div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-semibold text-gray-900">Cliente Respondeu</p>
                                <p class="text-xs text-gray-500">{{ $dispute->responded_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($dispute->defended_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-green-500 rounded-full"></div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-semibold text-gray-900">Defesa Aceita</p>
                                <p class="text-xs text-gray-500">{{ $dispute->defended_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($dispute->refunded_at)
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-2 h-2 mt-2 bg-purple-500 rounded-full"></div>
                            <div class="ml-4 flex-1">
                                <p class="text-sm font-semibold text-gray-900">Reembolsada</p>
                                <p class="text-xs text-gray-500">{{ $dispute->refunded_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Resumo Financeiro -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-emerald-50 to-white border-b-2 border-emerald-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-green-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Resumo Financeiro
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-600">Valor da Disputa:</span>
                        <span class="text-lg font-bold text-red-600">R$ {{ number_format($dispute->amount, 2, ',', '.') }}</span>
                    </div>
                    @if($dispute->risk_level === 'MED' && in_array($dispute->status, ['pending', 'responded']))
                    <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-600">Valor Bloqueado:</span>
                        <span class="text-lg font-bold text-orange-600">R$ {{ number_format($dispute->amount, 2, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-3">
                        <p class="text-xs text-blue-800">
                            <strong>Nota:</strong> 
                            @if($dispute->risk_level === 'MED')
                                Este valor está bloqueado na carteira do usuário até a resolução da disputa.
                            @elseif($dispute->risk_level === 'HIGH')
                                Esta é uma disputa de alto risco. Tome ações imediatas.
                            @else
                                Esta é uma disputa de baixo risco.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
