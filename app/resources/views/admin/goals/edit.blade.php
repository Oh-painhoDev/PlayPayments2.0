@extends('layouts.admin')

@section('title', 'Editar Meta')

@section('content')
<div class="p-6 space-y-8 max-w-[1000px] mx-auto animate-in fade-in duration-700" x-data="{ period: '{{ old('period', $goal->period) }}' }">
    <!-- Header Card -->
    <div class="relative overflow-hidden group rounded-3xl border border-white/5 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-[#D4AF37]/10 via-purple-500/10 to-transparent"></div>
        <div class="relative bg-[#121212]/80 backdrop-blur-xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#D4AF37] to-[#8a6d1d] flex items-center justify-center shadow-[0_0_20px_rgba(212,175,55,0.3)] group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">Editar Meta</h1>
                    <p class="text-gray-400 font-medium tracking-tight">Personalizando o objetivo de faturamento para {{ $goal->name }}</p>
                </div>
            </div>
            <a href="{{ route('admin.goals.index') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-bold rounded-2xl border border-white/10 transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                Voltar
            </a>
        </div>
    </div>

    <!-- Main Formulation -->
    <div class="bg-[#121212]/60 backdrop-blur-xl border border-white/5 rounded-3xl shadow-2xl overflow-hidden">
        <form action="{{ route('admin.goals.update', $goal) }}" method="POST" class="p-8 space-y-8">
            @csrf
            @method('PUT')

            <!-- Base Configuration -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Name -->
                <div class="space-y-2">
                    <label for="name" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37] ml-1">Identificação da Meta</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $goal->name) }}" required
                        class="w-full h-14 px-5 bg-white/[0.03] border border-white/10 rounded-2xl text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 focus:border-[#D4AF37] transition-all"
                        placeholder="Ex: Gold Member Milestone">
                    @error('name')<p class="text-xs text-red-500 font-bold ml-1 mt-2 text-glow-red">{{ $message }}</p>@enderror
                </div>

                <!-- User Target -->
                <div class="space-y-2">
                    <label for="user_id" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37] ml-1">Usuário Alvo</label>
                    <select id="user_id" name="user_id" class="w-full h-14 px-5 bg-white/[0.03] border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 focus:border-[#D4AF37] transition-all cursor-pointer">
                        <option value="global" {{ old('user_id', $goal->user_id) === null ? 'selected' : '' }} class="bg-[#1a1a1a]">🌍 Meta Global (Todos)</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $goal->user_id) == $user->id ? 'selected' : '' }} class="bg-[#1a1a1a]">👤 {{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Type Metric -->
                <div class="space-y-2">
                    <label for="type" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37] ml-1">Métrica Base</label>
                    <select id="type" name="type" required class="w-full h-14 px-5 bg-white/[0.03] border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 focus:border-[#D4AF37] transition-all cursor-pointer">
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ old('type', $goal->type) == $type ? 'selected' : '' }} class="bg-[#1a1a1a]">{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Value -->
                <div class="space-y-2">
                    <label for="target_value" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37] ml-1">Valor do Objetivo (R$)</label>
                    <div class="relative">
                        <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">R$</span>
                        <input type="number" id="target_value" name="target_value" value="{{ old('target_value', $goal->target_value) }}" step="0.01" min="0" required
                            class="w-full h-14 pl-12 pr-5 bg-white/[0.03] border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 focus:border-[#D4AF37] transition-all font-mono">
                    </div>
                </div>
            </div>

            <!-- Time Control -->
            <div class="space-y-6 bg-white/[0.02] p-6 rounded-3xl border border-white/5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="period" class="text-[10px] uppercase font-black tracking-widest text-gray-500 ml-1">Frequência/Período</label>
                        <select id="period" name="period" x-model="period" required class="w-full h-12 px-5 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 focus:border-[#D4AF37] transition-all cursor-pointer">
                            <option value="monthly" class="bg-[#1a1a1a]">Mensal</option>
                            <option value="yearly" class="bg-[#1a1a1a]">Anual</option>
                            <option value="custom" class="bg-[#1a1a1a]">Personalizado</option>
                        </select>
                    </div>

                    <div class="space-y-2 group">
                        <label for="display_order" class="text-[10px] uppercase font-black tracking-widest text-gray-500 ml-1">Ordem Prioritária</label>
                        <input type="number" id="display_order" name="display_order" value="{{ old('display_order', $goal->display_order) }}" min="1"
                            class="w-full h-12 px-5 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 transition-all" placeholder="1, 2, 3...">
                    </div>
                </div>

                <!-- Custom Dates -->
                <div x-show="period === 'custom'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="start_date" class="text-[10px] uppercase font-black tracking-widest text-emerald-500 ml-1">Início da Vigência</label>
                        <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $goal->start_date ? $goal->start_date->format('Y-m-d') : '') }}" :required="period === 'custom'"
                            class="w-full h-12 px-5 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:ring-[#D4AF37] transition-all invert brightness-90">
                    </div>
                    <div class="space-y-2">
                        <label for="end_date" class="text-[10px] uppercase font-black tracking-widest text-red-500 ml-1">Fim da Vigência</label>
                        <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $goal->end_date ? $goal->end_date->format('Y-m-d') : '') }}" :required="period === 'custom'"
                            class="w-full h-12 px-5 bg-white/[0.03] border border-white/10 rounded-xl text-white focus:outline-none focus:ring-[#D4AF37] transition-all invert brightness-90">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="text-[10px] uppercase font-black tracking-widest text-gray-500 ml-1">Contexto Interno (Descrição)</label>
                <textarea id="description" name="description" rows="3" class="w-full p-5 bg-white/[0.03] border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/30 transition-all resize-none">{{ old('description', $goal->description) }}</textarea>
            </div>

            <!-- Reward System Card -->
            <div class="relative p-8 bg-gradient-to-br from-[#D4AF37]/5 to-transparent border border-[#D4AF37]/10 rounded-[2rem] space-y-8 overflow-hidden">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-[#D4AF37] rounded-xl flex items-center justify-center text-black">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V4a1 1 0 011-1 1 1 0 011 1v2H9z" clip-rule="evenodd" /><path d="M9 11H3v5a2 2 0 002 2h4v-7zM11 18h4a2 2 0 002-2v-5h-6v7z" /></svg>
                    </div>
                    <h3 class="text-xl font-black text-white uppercase tracking-tighter">Motor de Premiação</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label for="reward_type" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37]/60 ml-1">Formato do Prêmio</label>
                        <select id="reward_type" name="reward_type" class="w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] cursor-pointer">
                            <option value="" class="bg-[#1a1a1a]">Sem prêmio</option>
                            <option value="cash" {{ old('reward_type', $goal->reward_type) == 'cash' ? 'selected' : '' }} class="bg-[#1a1a1a]">Dinheiro Base (Wallet)</option>
                            <option value="bonus" {{ old('reward_type', $goal->reward_type) == 'bonus' ? 'selected' : '' }} class="bg-[#1a1a1a]">Bônus de Volume</option>
                            <option value="discount" {{ old('reward_type', $goal->reward_type) == 'discount' ? 'selected' : '' }} class="bg-[#1a1a1a]">Desconto Operacional</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="reward_value" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37]/60 ml-1">Valor Recompensa (R$)</label>
                        <div class="relative">
                            <span class="absolute left-5 top-1/2 -translate-y-1/2 text-gray-500 font-bold">R$</span>
                            <input type="number" id="reward_value" name="reward_value" value="{{ old('reward_value', $goal->reward_value) }}" step="0.01" min="0"
                                class="w-full h-14 pl-12 pr-5 bg-white/5 border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37] font-mono">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="reward_description" class="text-[10px] uppercase font-black tracking-widest text-[#D4AF37]/60 ml-1">Anotação da Premiação (Público)</label>
                    <input type="text" id="reward_description" name="reward_description" value="{{ old('reward_description', $goal->reward_description) }}"
                        class="w-full h-14 px-5 bg-white/5 border border-white/10 rounded-2xl text-white focus:outline-none focus:ring-2 focus:ring-[#D4AF37]" placeholder="Ex: Parabéns! Você atingiu o volume necessário.">
                </div>

                <div class="flex flex-col md:flex-row gap-8 items-center pt-4">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" id="auto_reward" name="auto_reward" value="1" {{ old('auto_reward', $goal->auto_reward) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-14 h-7 bg-white/5 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-gray-500 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-[#D4AF37]/20 peer-checked:after:bg-[#D4AF37] border border-white/10"></div>
                        <span class="ml-4 text-xs font-black uppercase tracking-widest text-gray-400 group-hover:text-white transition-colors">Liberação Automática do Cache</span>
                    </label>

                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $goal->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-14 h-7 bg-white/5 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-gray-500 after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500/20 peer-checked:after:bg-emerald-500 border border-white/10"></div>
                        <span class="ml-4 text-xs font-black uppercase tracking-widest text-gray-400 group-hover:text-white transition-colors">Meta Ativa no Painel</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col md:flex-row justify-end gap-4 pt-8">
                <a href="{{ route('admin.goals.index') }}" class="px-10 py-4 bg-white/5 hover:bg-white/10 text-gray-400 font-bold rounded-2xl border border-white/10 transition-all text-center">Descartar Alterações</a>
                <button type="submit" class="px-12 py-4 bg-[#D4AF37] hover:bg-[#b8962d] text-black font-black uppercase tracking-widest rounded-2xl transition-all shadow-[0_10px_30px_rgba(212,175,55,0.2)]">Atualizar Diretriz</button>
            </div>
        </form>
    </div>
</div>

<style>
    .text-glow-red { text-shadow: 0 0 10px rgba(239, 68, 68, 0.5); }
    [x-cloak] { display: none !important; }
</style>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
