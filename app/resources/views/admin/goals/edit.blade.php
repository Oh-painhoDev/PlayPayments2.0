@extends('layouts.admin')

@section('title', 'Editar Meta')
@section('page-title', 'Editar Meta')
@section('page-description', 'Edite os detalhes da meta')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">✏️ Editar Meta</h1>
                <p class="text-purple-100">Edite os detalhes da meta: {{ $goal->name }}</p>
            </div>
            <a href="{{ route('admin.goals.index') }}" class="bg-white hover:bg-gray-100 text-purple-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Voltar
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl border-2 border-gray-200 overflow-hidden shadow-sm">
        <form action="{{ route('admin.goals.update', $goal) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Nome da Meta -->
            <div>
                <label for="name" class="block text-sm font-bold text-gray-900 mb-2">
                    Nome da Meta *
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $goal->name) }}"
                    required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                    placeholder="Ex: Faturamento Mensal"
                >
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Usuário -->
            <div>
                <label for="user_id" class="block text-sm font-bold text-gray-900 mb-2">
                    Usuário
                </label>
                <select 
                    id="user_id" 
                    name="user_id"
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                >
                    <option value="global" {{ old('user_id', $goal->user_id) === null ? 'selected' : '' }}>
                        🌍 Meta Global (Todos os usuários)
                    </option>
                    <option value="" disabled>─────────────────</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id', $goal->user_id) == $user->id ? 'selected' : '' }}>
                            👤 {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-500">
                    <strong>Meta Global:</strong> Visível para todos os usuários, mas o progresso é calculado individualmente para cada usuário baseado nas suas próprias transações.<br>
                    <strong>Meta Pessoal:</strong> Visível apenas para o usuário selecionado e baseada nas transações dele.
                </p>
                @error('user_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tipo -->
            <div>
                <label for="type" class="block text-sm font-bold text-gray-900 mb-2">
                    Tipo/Cargo *
                </label>
                <select 
                    id="type" 
                    name="type"
                    required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                >
                    <option value="">Selecione o tipo</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ old('type', $goal->type) == $type ? 'selected' : '' }}>
                            {{ ucfirst($type) }}
                        </option>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Valor da Meta -->
            <div>
                <label for="target_value" class="block text-sm font-bold text-gray-900 mb-2">
                    Valor da Meta (R$) *
                </label>
                <input 
                    type="number" 
                    id="target_value" 
                    name="target_value" 
                    value="{{ old('target_value', $goal->target_value) }}"
                    step="0.01"
                    min="0"
                    required
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                    placeholder="100000.00"
                >
                @error('target_value')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Período -->
            <div>
                <label for="period" class="block text-sm font-bold text-gray-900 mb-2">
                    Período *
                </label>
                <select 
                    id="period" 
                    name="period"
                    required
                    x-data="{ period: '{{ old('period', $goal->period) }}' }"
                    x-model="period"
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                >
                    <option value="monthly">Mensal</option>
                    <option value="yearly">Anual</option>
                    <option value="custom">Personalizado</option>
                </select>
                @error('period')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Datas Personalizadas -->
            <div x-show="document.getElementById('period').value === 'custom'">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-bold text-gray-900 mb-2">
                            Data de Início *
                        </label>
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="{{ old('start_date', $goal->start_date ? $goal->start_date->format('Y-m-d') : '') }}"
                            :required="document.getElementById('period').value === 'custom'"
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                        >
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="block text-sm font-bold text-gray-900 mb-2">
                            Data de Fim *
                        </label>
                        <input 
                            type="date" 
                            id="end_date" 
                            name="end_date" 
                            value="{{ old('end_date', $goal->end_date ? $goal->end_date->format('Y-m-d') : '') }}"
                            :required="document.getElementById('period').value === 'custom'"
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                        >
                        @error('end_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Ordem de Exibição -->
            <div>
                <label for="display_order" class="block text-sm font-bold text-gray-900 mb-2">
                    Ordem de Exibição
                </label>
                <input 
                    type="number" 
                    id="display_order" 
                    name="display_order" 
                    value="{{ old('display_order', $goal->display_order ?? '') }}"
                    min="1"
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                    placeholder="Deixe vazio para auto-numerar (começa do 1)"
                >
                <p class="mt-2 text-xs text-gray-500">
                    <strong>Ordem de Exibição:</strong> Define a ordem em que as metas aparecem no header do dashboard. Menor número aparece primeiro. Se deixar vazio, será automaticamente atribuído o próximo número disponível (começa do 1).
                </p>
                @error('display_order')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Descrição -->
            <div>
                <label for="description" class="block text-sm font-bold text-gray-900 mb-2">
                    Descrição
                </label>
                <textarea 
                    id="description" 
                    name="description" 
                    rows="3"
                    class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                    placeholder="Descrição opcional da meta"
                >{{ old('description', $goal->description) }}</textarea>
                @error('description')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Prêmio -->
            <div class="border-t border-gray-200 pt-6 mt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">🎁 Configuração de Prêmio</h3>
                
                <!-- Tipo de Prêmio -->
                <div class="mb-4">
                    <label for="reward_type" class="block text-sm font-bold text-gray-900 mb-2">
                        Tipo de Prêmio
                    </label>
                    <select 
                        id="reward_type" 
                        name="reward_type"
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                    >
                        <option value="">Sem prêmio</option>
                        <option value="cash" {{ old('reward_type', $goal->reward_type) == 'cash' ? 'selected' : '' }}>Dinheiro (R$)</option>
                        <option value="bonus" {{ old('reward_type', $goal->reward_type) == 'bonus' ? 'selected' : '' }}>Bônus</option>
                        <option value="discount" {{ old('reward_type', $goal->reward_type) == 'discount' ? 'selected' : '' }}>Desconto</option>
                        <option value="custom" {{ old('reward_type', $goal->reward_type) == 'custom' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                    @error('reward_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor do Prêmio -->
                <div class="mb-4">
                    <label for="reward_value" class="block text-sm font-bold text-gray-900 mb-2">
                        Valor do Prêmio (R$)
                    </label>
                    <input 
                        type="number" 
                        id="reward_value" 
                        name="reward_value" 
                        value="{{ old('reward_value', $goal->reward_value) }}"
                        step="0.01"
                        min="0"
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                        placeholder="100.00"
                    >
                    <p class="mt-2 text-xs text-gray-500">Valor que será creditado na wallet do usuário quando atingir a meta</p>
                    @error('reward_value')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição do Prêmio -->
                <div class="mb-4">
                    <label for="reward_description" class="block text-sm font-bold text-gray-900 mb-2">
                        Descrição do Prêmio
                    </label>
                    <textarea 
                        id="reward_description" 
                        name="reward_description" 
                        rows="2"
                        class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200"
                        placeholder="Ex: Bônus de R$ 100 por atingir a meta de faturamento"
                    >{{ old('reward_description', $goal->reward_description) }}</textarea>
                    @error('reward_description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prêmio Automático -->
                <div class="flex items-center mb-4">
                    <input 
                        type="checkbox" 
                        id="auto_reward" 
                        name="auto_reward" 
                        value="1"
                        {{ old('auto_reward', $goal->auto_reward) ? 'checked' : '' }}
                        class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2"
                    >
                    <label for="auto_reward" class="ml-2 text-sm font-bold text-gray-900">
                        Prêmio Automático
                    </label>
                </div>
                <p class="text-xs text-gray-500 mb-4">Se marcado, o prêmio será creditado automaticamente na wallet quando o usuário atingir a meta</p>
            </div>

            <!-- Status -->
            <div class="flex items-center">
                <input 
                    type="checkbox" 
                    id="is_active" 
                    name="is_active" 
                    value="1"
                    {{ old('is_active', $goal->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500 focus:ring-2"
                >
                <label for="is_active" class="ml-2 text-sm font-bold text-gray-900">
                    Meta Ativa
                </label>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.goals.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all">
                    Cancelar
                </a>
                <button type="submit" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl">
                    Atualizar Meta
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection

