@extends('layouts.admin')

@section('title', 'Nova Infração')
@section('page-title', 'Criar Nova Infração')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 via-orange-500 to-yellow-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">⚠️ Criar Nova Infração</h1>
                <p class="text-red-100">Registre uma nova infração para uma transação específica</p>
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

    <!-- Formulário -->
    <form method="POST" action="{{ route('admin.setup.disputes.store') }}" class="space-y-6">
        @csrf
        
        <!-- Card de Dados da Infração -->
        <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
            <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <span class="w-8 h-8 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </span>
                    Dados da Infração
                </h3>
            </div>

            <div class="p-6 space-y-6">
                <!-- Usuário -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        👤 Usuário
                    </label>
                    <select name="user_id" 
                            id="user_id"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        <option value="">Selecione um usuário</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- ID da Transação -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        🔖 ID da Transação
                    </label>
                    <input type="text" 
                           name="transaction_id" 
                           value="{{ old('transaction_id') }}"
                           required
                           placeholder="Digite o ID da transação"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                    <p class="mt-1 text-xs text-gray-500">Informe o ID da transação que será contestada</p>
                </div>

                <!-- Valor -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        💰 Valor da Infração
                    </label>
                    <input type="number" 
                           name="amount" 
                           value="{{ old('amount') }}"
                           step="0.01"
                           min="0.01"
                           required
                           placeholder="0.00"
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                    <p class="mt-1 text-xs text-gray-500">Valor que será bloqueado (se risco MED) ou contestado</p>
                </div>

                <!-- Tipo de Infração -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        ⚠️ Tipo de Infração
                    </label>
                    <select name="dispute_type" 
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        <option value="">Selecione o tipo</option>
                        <option value="chargeback" {{ old('dispute_type') == 'chargeback' ? 'selected' : '' }}>Chargeback</option>
                        <option value="fraud" {{ old('dispute_type') == 'fraud' ? 'selected' : '' }}>Fraude</option>
                        <option value="unauthorized" {{ old('dispute_type') == 'unauthorized' ? 'selected' : '' }}>Não autorizado</option>
                        <option value="not_received" {{ old('dispute_type') == 'not_received' ? 'selected' : '' }}>Produto não recebido</option>
                        <option value="defective" {{ old('dispute_type') == 'defective' ? 'selected' : '' }}>Produto defeituoso</option>
                        <option value="other" {{ old('dispute_type') == 'other' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>

                <!-- Nível de Risco -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        🎯 Nível de Risco
                    </label>
                    <select name="risk_level" 
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                        <option value="">Selecione o nível</option>
                        <option value="LOW" {{ old('risk_level') == 'LOW' ? 'selected' : '' }}>🟢 BAIXO - Apenas notificação</option>
                        <option value="MED" {{ old('risk_level') == 'MED' ? 'selected' : '' }}>🟡 MÉDIO - Bloqueia saldo automaticamente</option>
                        <option value="HIGH" {{ old('risk_level') == 'HIGH' ? 'selected' : '' }}>🔴 ALTO - Risco elevado</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Nível MED aplica bloqueio cautelar automaticamente</p>
                </div>
            </div>
        </div>

        <!-- Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 border-2 border-orange-200 rounded-2xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-orange-600 font-semibold">Bloqueio Cautelar</p>
                        <p class="text-sm text-orange-800">Risco MED bloqueia saldo</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-50 to-red-100 border-2 border-red-200 rounded-2xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-red-600 font-semibold">Notificação</p>
                        <p class="text-sm text-red-800">Usuário será notificado</p>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border-2 border-yellow-200 rounded-2xl p-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-yellow-600 font-semibold">Defesa</p>
                        <p class="text-sm text-yellow-800">Usuário pode contestar</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.setup.disputes') }}" class="px-8 py-3 border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-orange-600 to-red-600 text-white rounded-xl font-semibold hover:from-orange-700 hover:to-red-700 transition-all shadow-lg hover:shadow-xl">
                Criar Infração
            </button>
        </div>
    </form>
</div>

<script>
// Carregar transações do usuário selecionado (AJAX)
document.getElementById('user_id').addEventListener('change', function() {
    const userId = this.value;
    if (userId) {
        // Aqui você pode adicionar uma chamada AJAX para carregar as transações do usuário
        // e preencher dinamicamente um campo select de transações
        console.log('Usuário selecionado:', userId);
    }
});
</script>
@endsection
