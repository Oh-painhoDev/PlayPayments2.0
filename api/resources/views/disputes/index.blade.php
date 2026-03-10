@extends('layouts.dashboard')

@section('title', 'Infrações')
@section('page-title', 'Infrações')
@section('page-description', 'Gerencie infrações e disputas de transações')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header Section com Gradiente Verde BRPIX -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Infrações e Disputas</h1>
                <p class="text-green-100">Gerencie infrações, defenda transações ou realize reembolsos</p>
            </div>
            <div class="bg-white/20 backdrop-blur-sm px-6 py-3 rounded-xl">
                <span class="text-sm font-medium text-white">Saldo Bloqueado:</span>
                <p class="text-2xl font-bold">R$ {{ number_format($wallet->blocked_balance ?? 0, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Cards de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total de Infrações -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Total Infrações</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_disputes'] }}</p>
        </div>

        <!-- Pendentes -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Pendentes</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
        </div>

        <!-- Defendidas -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Defendidas</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['defended'] }}</p>
        </div>

        <!-- Reembolsadas -->
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Reembolsadas</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['refunded'] }}</p>
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

    <!-- Lista de Infrações -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
                Histórico de Infrações
            </h3>
        </div>

        @if($disputes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID Infração</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Transação</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Risco</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Data Criação</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Prazo Limite</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($disputes as $dispute)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold text-gray-900">{{ $dispute->dispute_id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-600">{{ $dispute->transaction->transaction_id ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-gray-900">R$ {{ number_format($dispute->amount, 2, ',', '.') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($dispute->risk_level === 'LOW')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Baixo</span>
                                    @elseif($dispute->risk_level === 'MED')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Médio</span>
                                    @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Alto</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($dispute->status === 'pending')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                    @elseif($dispute->status === 'responded')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Respondido</span>
                                    @elseif($dispute->status === 'defended')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Defendido</span>
                                    @elseif($dispute->status === 'defense_rejected')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Defesa Rejeitada</span>
                                    @elseif($dispute->status === 'refunded')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Reembolsado</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $dispute->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($dispute->dispute_deadline)
                                        @php
                                            $isExpired = $dispute->isExpired();
                                            $remainingDays = $dispute->getRemainingDays();
                                        @endphp
                                        @if($isExpired)
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800">
                                                Vencido em {{ \Carbon\Carbon::parse($dispute->dispute_deadline)->format('d/m/Y') }}
                                            </span>
                                        @elseif($remainingDays <= 1)
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-800 animate-pulse">
                                                {{ \Carbon\Carbon::parse($dispute->dispute_deadline)->format('d/m/Y H:i') }} ({{ $remainingDays }}d)
                                            </span>
                                        @else
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ \Carbon\Carbon::parse($dispute->dispute_deadline)->format('d/m/Y H:i') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($dispute->canDefend())
                                        <button onclick="openDefenseModal({{ $dispute->id }}, '{{ $dispute->dispute_id }}')" 
                                                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all mr-2">
                                            Defender
                                        </button>
                                    @endif
                                    @if($dispute->canRefund())
                                        <form action="{{ route('refunds.refund', $dispute) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja reembolsar esta infração? O valor será debitado do seu saldo.')">
                                            @csrf
                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all">
                                                Reembolsar
                                            </button>
                                        </form>
                                    @endif
                                    @if($dispute->status !== 'pending')
                                        <button onclick="openDisputeDetailsModal({{ $dispute->id }})" class="text-green-600 hover:text-green-700 text-sm font-semibold ml-2">
                                            Ver Detalhes
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t-2 border-gray-200">
                <x-pagination :paginator="$disputes" />
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-xl font-semibold text-gray-600 mb-2">Nenhuma infração encontrada</p>
                <p class="text-gray-500">Você não possui infrações no momento.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Defesa -->
<div id="defenseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 rounded-t-2xl">
            <h3 class="text-2xl font-bold text-white flex items-center">
                Defender Infração
            </h3>
            <p class="text-green-100 mt-2">Apresente sua defesa com detalhes e anexe um documento PDF</p>
        </div>
        <form id="defenseForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            <input type="hidden" id="disputeIdField" name="dispute_id">
            
            <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4">
                <p class="text-sm text-yellow-800 font-semibold">
                    <strong>Atenção:</strong> Sua defesa deve conter no mínimo 50 caracteres e você deve anexar um arquivo PDF com as evidências.
                </p>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Detalhes da Defesa *</label>
                <textarea name="defense_details" rows="6" required minlength="50"
                          class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                          placeholder="Descreva detalhadamente os motivos da sua defesa, incluindo provas, evidências e justificativas..."></textarea>
                <p class="text-xs text-gray-500 mt-1">Mínimo de 50 caracteres</p>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Anexar Documento PDF *</label>
                <input type="file" name="defense_file" accept=".pdf" required
                       class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                <p class="text-xs text-gray-500 mt-1">Tamanho máximo: 10MB | Formato: PDF</p>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-bold transition-all">
                    Enviar Defesa
                </button>
                <button type="button" onclick="closeDefenseModal()" class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-bold transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Detalhes da Infração -->
<div id="disputeDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6 rounded-t-2xl">
            <h3 class="text-2xl font-bold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Detalhes da Infração
            </h3>
            <p class="text-green-100 mt-2">Informações completas sobre a infração</p>
        </div>
        
        <div id="disputeDetailsContent" class="p-6 space-y-6">
            <!-- Conteúdo será preenchido via JavaScript -->
        </div>
        
        <div class="p-6 bg-gray-50 rounded-b-2xl border-t border-gray-200">
            <button type="button" onclick="closeDisputeDetailsModal()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold transition-all">
                Fechar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const disputes = @json($disputes);

function openDefenseModal(disputeId, disputeCode) {
    const modal = document.getElementById('defenseModal');
    const form = document.getElementById('defenseForm');
    form.action = `/disputes/${disputeId}/defend`;
    modal.classList.remove('hidden');
}

function closeDefenseModal() {
    const modal = document.getElementById('defenseModal');
    modal.classList.add('hidden');
    document.getElementById('defenseForm').reset();
}

function openDisputeDetailsModal(disputeId) {
    const dispute = disputes.data.find(d => d.id === disputeId);
    if (!dispute) return;
    
    const statusLabels = {
        'pending': 'Pendente',
        'responded': 'Respondido',
        'defended': 'Defendido',
        'defense_rejected': 'Defesa Rejeitada',
        'refunded': 'Reembolsado'
    };
    
    const riskLabels = {
        'LOW': 'Baixo',
        'MED': 'Médio',
        'HIGH': 'Alto'
    };
    
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'responded': 'bg-blue-100 text-blue-800',
        'defended': 'bg-green-100 text-green-800',
        'defense_rejected': 'bg-red-100 text-red-800',
        'refunded': 'bg-gray-100 text-gray-800'
    };
    
    const content = `
        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-bold text-green-700 uppercase mb-1">ID da Infração</p>
                    <p class="text-2xl font-bold text-gray-900">${dispute.dispute_id}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold text-green-700 uppercase mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 text-sm font-bold rounded-full ${statusColors[dispute.status]}">
                        ${statusLabels[dispute.status]}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-600 uppercase mb-1">Transação</p>
                <p class="text-lg font-semibold text-gray-900">${dispute.transaction?.transaction_id || 'N/A'}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-600 uppercase mb-1">Valor</p>
                <p class="text-lg font-bold text-green-600">R$ ${parseFloat(dispute.amount).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-600 uppercase mb-1">Tipo</p>
                <p class="text-lg font-semibold text-gray-900">${dispute.dispute_type.replace('_', ' ')}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
                <p class="text-xs font-bold text-gray-600 uppercase mb-1">Nível de Risco</p>
                <p class="text-lg font-semibold text-gray-900">${riskLabels[dispute.risk_level]}</p>
            </div>
        </div>
        
        ${dispute.reason ? `
        <div class="bg-yellow-50 border-2 border-yellow-200 rounded-xl p-4 mb-6">
            <p class="text-xs font-bold text-yellow-800 uppercase mb-2">Motivo da Infração</p>
            <p class="text-sm text-gray-700">${dispute.reason}</p>
        </div>
        ` : ''}
        
        ${dispute.defense_details ? `
        <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-4 mb-6">
            <p class="text-xs font-bold text-blue-800 uppercase mb-2">Sua Defesa</p>
            <p class="text-sm text-gray-700 whitespace-pre-line">${dispute.defense_details}</p>
        </div>
        ` : ''}
        
        ${dispute.admin_response ? `
        <div class="bg-purple-50 border-2 border-purple-200 rounded-xl p-4 mb-6">
            <p class="text-xs font-bold text-purple-800 uppercase mb-2">Resposta do Admin</p>
            <p class="text-sm text-gray-700">${dispute.admin_response}</p>
        </div>
        ` : ''}
        
        <div class="bg-gray-50 rounded-xl p-4">
            <p class="text-xs font-bold text-gray-600 uppercase mb-2">Data de Criação</p>
            <p class="text-sm text-gray-700">${new Date(dispute.created_at).toLocaleString('pt-BR')}</p>
        </div>
    `;
    
    document.getElementById('disputeDetailsContent').innerHTML = content;
    document.getElementById('disputeDetailsModal').classList.remove('hidden');
}

function closeDisputeDetailsModal() {
    document.getElementById('disputeDetailsModal').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDefenseModal();
        closeDisputeDetailsModal();
    }
});

// Close modals on outside click
document.getElementById('defenseModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDefenseModal();
    }
});

document.getElementById('disputeDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDisputeDetailsModal();
    }
});
</script>
@endpush
@endsection
