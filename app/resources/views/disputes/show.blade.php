@extends('layouts.dashboard')

@section('title', 'Detalhes da Infração')
@section('page-title', 'Detalhes da Infração')
@section('page-description', 'Informações completas sobre a infração')

@section('content')
<div class="p-6 space-y-6">
    <!-- Botão Voltar -->
    <div>
        <a href="{{ route('refunds.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Infrações
        </a>
    </div>

    <!-- Header com Status -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $dispute->dispute_id }}</h1>
                <p class="text-green-100">Infração tipo: {{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}</p>
            </div>
            <div class="text-right">
                @if($dispute->status === 'pending')
                    <span class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-full bg-yellow-500/20 text-white border-2 border-white/30">
                        Pendente
                    </span>
                @elseif($dispute->status === 'responded')
                    <span class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-full bg-blue-500/20 text-white border-2 border-white/30">
                        Respondido
                    </span>
                @elseif($dispute->status === 'defended')
                    <span class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-full bg-green-500/20 text-white border-2 border-white/30">
                        Defendido
                    </span>
                @elseif($dispute->status === 'defense_rejected')
                    <span class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-full bg-red-500/20 text-white border-2 border-white/30">
                        Defesa Rejeitada
                    </span>
                @elseif($dispute->status === 'refunded')
                    <span class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-full bg-gray-500/20 text-white border-2 border-white/30">
                        Reembolsado
                    </span>
                @endif
                <p class="text-2xl font-bold mt-2">R$ {{ number_format($dispute->amount, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Coluna Principal -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Informações da Infração -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Informações da Infração
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                            <label class="text-xs font-bold text-gray-600 uppercase">ID Infração</label>
                            <p class="text-sm font-mono font-bold text-gray-900 mt-1">{{ $dispute->dispute_id }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                            <label class="text-xs font-bold text-gray-600 uppercase">Transação</label>
                            <p class="text-sm font-mono font-semibold text-gray-900 mt-1">{{ $dispute->transaction->transaction_id ?? 'N/A' }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                            <label class="text-xs font-bold text-gray-600 uppercase">Tipo</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                            <label class="text-xs font-bold text-gray-600 uppercase">Nível de Risco</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">
                                @if($dispute->risk_level === 'LOW')
                                    <span class="text-green-600">🟢 Baixo</span>
                                @elseif($dispute->risk_level === 'MED')
                                    <span class="text-yellow-600">🟡 Médio</span>
                                @else
                                    <span class="text-red-600">🔴 Alto</span>
                                @endif
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                            <label class="text-xs font-bold text-gray-600 uppercase">Data de Criação</label>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $dispute->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border-2 border-gray-200">
                            <label class="text-xs font-bold text-gray-600 uppercase">Valor</label>
                            <p class="text-lg font-bold text-green-600 mt-1">R$ {{ number_format($dispute->amount, 2, ',', '.') }}</p>
                        </div>
                    </div>

                    @if($dispute->dispute_details)
                        <div class="bg-yellow-50 rounded-xl p-4 border-2 border-yellow-200">
                            <label class="text-xs font-bold text-yellow-800 uppercase">Detalhes da Infração</label>
                            <p class="text-sm text-gray-900 mt-2 whitespace-pre-wrap">{{ $dispute->dispute_details }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Defesa (se existir) -->
            @if($dispute->defense_details || $dispute->defense_file)
            <div class="bg-white rounded-2xl border-2 border-green-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-green-50 to-white border-b-2 border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                        Sua Defesa
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    @if($dispute->defense_details)
                        <div>
                            <label class="text-sm font-bold text-gray-700 mb-2 block">Detalhes da Defesa:</label>
                            <div class="bg-green-50 rounded-xl p-4 border-2 border-green-200">
                                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $dispute->defense_details }}</p>
                            </div>
                        </div>
                    @endif

                    @if($dispute->defense_file)
                        <div>
                            <label class="text-sm font-bold text-gray-700 mb-2 block">Documento Anexado:</label>
                            <a href="{{ $dispute->defense_file }}" target="_blank" 
                                class="inline-flex items-center px-4 py-3 bg-blue-50 border-2 border-blue-300 text-blue-700 rounded-xl hover:bg-blue-100 transition-all font-semibold">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Visualizar PDF
                            </a>
                        </div>
                    @endif

                    @if($dispute->responded_at)
                        <p class="text-xs text-gray-500">Respondida em: {{ $dispute->responded_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Notas do Admin (se existir) -->
            @if($dispute->admin_notes)
            <div class="bg-white rounded-2xl border-2 border-purple-200 shadow-sm overflow-hidden">
                <div class="p-6 bg-gradient-to-r from-purple-50 to-white border-b-2 border-purple-200">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <span class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                        </span>
                        Resposta do Administrador
                    </h3>
                </div>
                <div class="p-6">
                    <div class="bg-purple-50 rounded-xl p-4 border-2 border-purple-200">
                        <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $dispute->admin_notes }}</p>
                    </div>
                    @if($dispute->defended_at)
                        <p class="text-xs text-gray-500 mt-3">Aceita em: {{ $dispute->defended_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Ações -->
            @if($dispute->canDefend() || $dispute->canRefund())
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Ações Disponíveis</h3>
                <div class="space-y-3">
                    @if($dispute->canDefend())
                        <button onclick="openDefenseModal({{ $dispute->id }}, '{{ $dispute->dispute_id }}')" 
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-4 py-3 rounded-xl font-semibold transition-all flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Defender Infração
                        </button>
                    @endif
                    @if($dispute->canRefund())
                        <form action="{{ route('refunds.refund', $dispute) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja reembolsar? O valor será debitado do seu saldo.')">
                            @csrf
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-3 rounded-xl font-semibold transition-all flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                                </svg>
                                Reembolsar Agora
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            <!-- Informações da Transação -->
            @if($dispute->transaction)
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Transação Relacionada</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-bold text-gray-600 uppercase">ID Transação</label>
                        <p class="text-sm font-mono font-semibold text-gray-900">{{ $dispute->transaction->transaction_id }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-600 uppercase">Método</label>
                        <p class="text-sm font-semibold text-gray-900">{{ strtoupper($dispute->transaction->payment_method) }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-600 uppercase">Status da Transação</label>
                        <p class="text-sm font-semibold text-gray-900">{{ ucfirst($dispute->transaction->status) }}</p>
                    </div>
                    <a href="{{ route('transactions.show', $dispute->transaction->transaction_id) }}" 
                       class="inline-flex items-center text-green-600 hover:text-green-700 text-sm font-semibold transition-colors">
                        Ver Transação Completa
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
            @endif

            <!-- Timeline -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Linha do Tempo</h3>
                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-gray-400 rounded-full mt-1.5 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Infração Criada</p>
                            <p class="text-xs text-gray-600">{{ $dispute->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @if($dispute->responded_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Defesa Enviada</p>
                            <p class="text-xs text-gray-600">{{ $dispute->responded_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($dispute->defended_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Defesa Aceita</p>
                            <p class="text-xs text-gray-600">{{ $dispute->defended_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($dispute->refunded_at)
                    <div class="flex items-start">
                        <div class="w-2 h-2 bg-gray-500 rounded-full mt-1.5 mr-3"></div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">Reembolsado</p>
                            <p class="text-xs text-gray-600">{{ $dispute->refunded_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir o modal de defesa (mesmo da index) -->
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
            
            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Descrição da Defesa *</label>
                <textarea name="defense_details" rows="5" required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                    placeholder="Descreva detalhadamente sua defesa..."></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Motivo da Defesa *</label>
                <select name="defense_reason" required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <option value="">Selecione o motivo</option>
                    <option value="unauthorized">Transação não autorizada</option>
                    <option value="product_not_received">Produto não recebido</option>
                    <option value="product_different">Produto diferente do anunciado</option>
                    <option value="duplicate">Cobrança duplicada</option>
                    <option value="technical_error">Erro técnico</option>
                    <option value="other">Outro motivo</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-900 mb-2">Documento PDF (obrigatório) *</label>
                <input type="file" name="defense_file" accept=".pdf" required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                <p class="text-xs text-gray-600 mt-2">Anexe um PDF com comprovantes, prints ou documentos que sustentem sua defesa. Máximo: 5MB</p>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-bold transition-all">
                    Enviar Defesa
                </button>
                <button type="button" onclick="closeDefenseModal()" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-xl font-bold transition-all">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openDefenseModal(disputeId, disputeIdText) {
    const form = document.getElementById('defenseForm');
    form.action = `/disputes/${disputeId}/defend`;
    document.getElementById('defenseModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDefenseModal() {
    document.getElementById('defenseModal').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>
@endpush
@endsection
