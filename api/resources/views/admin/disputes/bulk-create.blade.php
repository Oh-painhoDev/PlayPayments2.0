@extends('layouts.admin')

@section('title', 'Disparar Infrações em Massa')
@section('page-title', 'Disparar Infrações em Massa')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 via-orange-500 to-yellow-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">⚠️ Disparar Infrações em Massa</h1>
                <p class="text-red-100">Crie infrações automaticamente para vendas pagas com base em filtros</p>
            </div>
            <a href="{{ route('admin.setup.disputes') }}" class="bg-white hover:bg-gray-100 text-red-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-4 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <p class="text-green-800 font-semibold">{{ session('success') }}</p>
                @if(session('summary'))
                    <div class="mt-2 text-sm text-green-700">
                        <p><strong>Resumo da operação:</strong></p>
                        <p>✓ Infrações criadas: {{ session('summary')['created'] }}</p>
                        <p>✓ Bloqueios aplicados: {{ session('summary')['blocked'] }}</p>
                        <p>✓ Total de vendas processadas: {{ session('summary')['transactions'] }}</p>
                    </div>
                @endif
            </div>
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

    <!-- Formulário -->
    <form method="POST" action="{{ route('admin.setup.disputes.bulk-store') }}" class="space-y-6">
        @csrf
        
        <!-- Card de Configuração -->
        <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </span>
                    Configuração dos Filtros
                </h3>
            </div>

            <div class="p-6 space-y-6">
                <!-- Período -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            📅 Data Início
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_from" value="{{ old('date_from') }}" required
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            📅 Data Fim
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_to" value="{{ old('date_to') }}" required
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Adquirente (Gateway) -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        🏦 Adquirente (Gateway)
                    </label>
                    <select name="gateway_id" 
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">Todas as Adquirentes</option>
                        @foreach($gateways as $gateway)
                            <option value="{{ $gateway->id }}" {{ old('gateway_id') == $gateway->id ? 'selected' : '' }}>
                                {{ $gateway->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Deixe vazio para incluir todas as adquirentes</p>
                </div>

                <!-- Usuário -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        👤 Usuário Específico
                    </label>
                    <select name="user_id" id="userSelect"
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">Todos os Usuários</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Deixe vazio para aplicar a todos os usuários</p>
                </div>

                <!-- Template -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        📋 Template (opcional)
                    </label>
                    <select name="template_id" id="templateSelect"
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">Configurar manualmente</option>
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" 
                                data-type="{{ $template->dispute_type }}" 
                                data-risk="{{ $template->risk_level }}"
                                {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                {{ $template->name }} ({{ $template->getDisputeTypeLabel() }} - {{ $template->getRiskLevelLabel() }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Selecione um template para preencher automaticamente tipo e risco</p>
                </div>

                <!-- Tipo de Infração -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        🏷️ Tipo de Infração
                        <span class="text-red-500">*</span>
                    </label>
                    <select name="dispute_type" id="disputeTypeSelect" required
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="chargeback" {{ old('dispute_type') == 'chargeback' ? 'selected' : '' }}>Chargeback</option>
                        <option value="fraud" {{ old('dispute_type') == 'fraud' ? 'selected' : '' }}>Fraude</option>
                        <option value="unauthorized" {{ old('dispute_type') == 'unauthorized' ? 'selected' : '' }}>Não Autorizado</option>
                        <option value="not_received" {{ old('dispute_type') == 'not_received' ? 'selected' : '' }}>Não Recebido</option>
                        <option value="defective" {{ old('dispute_type') == 'defective' ? 'selected' : '' }}>Defeituoso</option>
                        <option value="other" {{ old('dispute_type') == 'other' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>

                <!-- Nível de Risco -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        ⚡ Nível de Risco
                        <span class="text-red-500">*</span>
                    </label>
                    <select name="risk_level" id="riskLevelSelect" required
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="LOW" {{ old('risk_level') == 'LOW' ? 'selected' : '' }}>Baixo (LOW)</option>
                        <option value="MED" {{ old('risk_level') == 'MED' ? 'selected' : '' }}>Médio (MED) - Aplica Bloqueio Cautelar</option>
                        <option value="HIGH" {{ old('risk_level') == 'HIGH' ? 'selected' : '' }}>Alto (HIGH)</option>
                    </select>
                    <p class="text-xs text-orange-600 mt-1 font-semibold">⚠️ Nível MED aplica bloqueio cautelar automaticamente</p>
                </div>

                <!-- Quantidade Máxima -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        🔢 Quantidade Máxima de Infrações
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="max_disputes" value="{{ old('max_disputes', 10) }}" min="1" max="1000" required
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                    <p class="text-xs text-gray-500 mt-1">Máximo: 1000 infrações por vez</p>
                </div>
            </div>
        </div>

        <!-- Card de Aviso -->
        <div class="bg-gradient-to-br from-orange-50 to-red-50 border-2 border-orange-300 rounded-2xl p-6">
            <div class="flex items-start">
                <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center mr-4 flex-shrink-0">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h4 class="text-lg font-bold text-orange-900 mb-2">⚠️ Atenção!</h4>
                    <ul class="text-sm text-orange-800 space-y-1">
                        <li>✓ Esta ação criará infrações para vendas com status <strong>PAID</strong></li>
                        <li>✓ Se selecionar risco <strong>MED</strong>, será aplicado bloqueio cautelar automático</li>
                        <li>✓ Os usuários afetados verão as infrações na área "Infrações"</li>
                        <li>✓ O saldo bloqueado não poderá ser sacado até resolução</li>
                        <li>✓ Esta operação não pode ser desfeita facilmente</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.setup.disputes') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-4 rounded-xl font-bold transition-all">
                Cancelar
            </a>
            <button type="submit" class="bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white px-8 py-4 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl">
                🚀 Disparar Infrações
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('templateSelect');
    const disputeTypeSelect = document.getElementById('disputeTypeSelect');
    const riskLevelSelect = document.getElementById('riskLevelSelect');

    templateSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const disputeType = selectedOption.getAttribute('data-type');
            const riskLevel = selectedOption.getAttribute('data-risk');
            
            if (disputeType) {
                disputeTypeSelect.value = disputeType;
            }
            
            if (riskLevel) {
                riskLevelSelect.value = riskLevel;
            }
        }
    });
});
</script>
@endpush
@endsection
