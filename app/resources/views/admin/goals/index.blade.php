@extends('layouts.admin')

@section('title', 'Gerenciar Metas')
@section('page-title', 'Gerenciar Metas')
@section('page-description', 'Crie e gerencie metas pessoais para usuários')

@section('content')
<div class="p-6 space-y-8 max-w-[1600px] mx-auto animate-in fade-in duration-700">
    <!-- Header Card -->
    <div class="relative overflow-hidden group rounded-3xl border border-white/5 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-r from-[#D4AF37]/10 via-purple-500/10 to-transparent"></div>
        <div class="relative bg-[#121212]/80 backdrop-blur-xl p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-[#D4AF37] to-[#8a6d1d] flex items-center justify-center shadow-[0_0_20px_rgba(212,175,55,0.3)] group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-8 h-8 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight lowercase first-letter:uppercase">Gerenciar Metas</h1>
                    <p class="text-gray-400 font-medium max-w-md">Estratégias de crescimento e gamificação para sua base de usuários.</p>
                </div>
            </div>
            <a href="{{ route('admin.goals.create') }}" class="group/btn relative px-8 py-4 bg-[#D4AF37] hover:bg-[#b8962d] text-black font-bold rounded-2xl transition-all duration-300 shadow-[0_10px_30px_rgba(212,175,55,0.2)] hover:shadow-[0_15px_40px_rgba(212,175,55,0.4)] flex items-center overflow-hidden">
                <div class="absolute inset-0 w-full h-full bg-white/20 translate-x-[-100%] group-hover/btn:translate-x-[100%] transition-transform duration-700"></div>
                <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span class="relative z-10">Nova Meta de Sucesso</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success') || session('error') || $errors->any())
    <div class="grid grid-cols-1 gap-4 animate-in slide-in-from-top-4 duration-500">
        @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 backdrop-blur-md rounded-2xl p-4 flex items-center text-emerald-400">
            <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="font-bold">{{ session('success') }}</p>
        </div>
        @endif
        @if(session('error') || $errors->any())
        <div class="bg-red-500/10 border border-red-500/20 backdrop-blur-md rounded-2xl p-4 text-red-400">
            <div class="flex items-center mb-1">
                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p class="font-bold">{{ session('error') ?? 'Erros encontrados:' }}</p>
            </div>
            @if($errors->any())
            <ul class="list-disc list-inside text-sm opacity-80 mt-2 ml-9">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
            @endif
        </div>
        @endif
    </div>
    @endif

    <!-- Filters Section -->
    <div class="bg-[#121212]/60 backdrop-blur-xl border border-white/5 rounded-3xl p-6 shadow-xl">
        <form method="GET" action="{{ route('admin.goals.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
            <div class="space-y-2">
                <label class="text-[10px] uppercase font-bold tracking-widest text-gray-500 ml-1">Filtro por Usuário</label>
                <select name="user_id" class="w-full h-12 px-4 bg-white/[0.03] border border-white/10 rounded-xl text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50 focus:border-[#D4AF37] transition-all cursor-pointer">
                    <option value="" class="bg-[#1a1a1a]">Todas as metas</option>
                    <option value="global" {{ request('user_id') === 'global' ? 'selected' : '' }} class="bg-[#1a1a1a]">🌍 Apenas Metas Globais</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }} class="bg-[#1a1a1a]">👤 {{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] uppercase font-bold tracking-widest text-gray-500 ml-1">Tipo de Meta</label>
                <select name="type" class="w-full h-12 px-4 bg-white/[0.03] border border-white/10 rounded-xl text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50 focus:border-[#D4AF37] transition-all cursor-pointer">
                    <option value="" class="bg-[#1a1a1a]">Todos os tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }} class="bg-[#1a1a1a]">{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-2">
                <label class="text-[10px] uppercase font-bold tracking-widest text-gray-500 ml-1">Status Atuall</label>
                <select name="is_active" class="w-full h-12 px-4 bg-white/[0.03] border border-white/10 rounded-xl text-gray-300 focus:outline-none focus:ring-2 focus:ring-[#D4AF37]/50 focus:border-[#D4AF37] transition-all cursor-pointer">
                    <option value="" class="bg-[#1a1a1a]">Todos os Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }} class="bg-[#1a1a1a]">Ativas</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }} class="bg-[#1a1a1a]">Inativas</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 h-12 bg-white/5 hover:bg-white/10 text-white font-bold rounded-xl border border-white/10 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    Filtrar
                </button>
                <a href="{{ route('admin.goals.index') }}" class="w-12 h-12 bg-white/5 hover:bg-white/10 text-gray-400 rounded-xl border border-white/10 transition-all flex items-center justify-center" title="Limpar Filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Goals Table Area -->
    <div class="bg-[#121212]/80 backdrop-blur-xl border border-white/5 rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-white/10 scrollbar-track-transparent">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-white/[0.02]">
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500 text-center">Ordem</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500">Definição da Meta</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500">Usuário Alvo</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500">Métrica</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500">Progresso Atual</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500">Prêmiação</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500">Status</th>
                        <th class="px-6 py-5 text-[10px] font-black uppercase tracking-widest text-gray-500 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.03]">
                    @forelse($goals as $goal)
                        @php
                            if ($goal->user_id) {
                                $currentValue = $goal->current_value;
                                $percentage = $goal->percentage;
                            } else {
                                $currentValue = null;
                                $percentage = null;
                            }
                        @endphp
                        <tr class="group hover:bg-white/[0.02] transition-colors">
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 mx-auto bg-white/5 rounded-xl flex items-center justify-center text-sm font-bold text-[#D4AF37] border border-white/5">
                                    {{ $goal->display_order ?? 0 }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-white tracking-tight">{{ $goal->name }}</span>
                                    <span class="text-xs text-gray-500 mt-0.5 line-clamp-1 opacity-60 group-hover:opacity-100 transition-opacity">{{ $goal->description ?? 'Sem descrição' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($goal->user)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-400 font-bold text-xs">
                                            {{ strtoupper(substr($goal->user->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-300">{{ $goal->user->name }}</span>
                                            <span class="text-[10px] text-gray-500">{{ $goal->user->email }}</span>
                                        </div>
                                    </div>
                                @else
                                    <div class="inline-flex items-center px-2 py-1 bg-[#D4AF37]/10 border border-[#D4AF37]/20 rounded-md">
                                        <span class="text-[10px] font-black text-[#D4AF37] uppercase tracking-tighter">🌍 META GLOBAL</span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 tracking-widest">
                                    {{ $goal->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 min-w-[200px]">
                                @if($percentage !== null)
                                    <div class="space-y-2">
                                        <div class="flex justify-between items-center text-[10px] font-bold">
                                            <span class="text-gray-500">
                                                @if(in_array($goal->type, ['faturamento', 'vendas']))
                                                    R$ {{ number_format($currentValue, 2, ',', '.') }}
                                                @else
                                                    {{ number_format($currentValue, 0, ',', '.') }}
                                                @endif
                                            </span>
                                            <span class="{{ $percentage >= 100 ? 'text-emerald-400' : 'text-[#D4AF37]' }}">
                                                {{ number_format($percentage, 1) }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-white/5 rounded-full h-1.5 overflow-hidden border border-white/5">
                                            <div class="h-full bg-gradient-to-r {{ $percentage >= 100 ? 'from-emerald-600 to-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.3)]' : 'from-[#8a6d1d] via-[#D4AF37] to-[#f5de8a] shadow-[0_0_10px_rgba(212,175,55,0.3)]' }} transition-all duration-1000" 
                                                 style="width: {{ min(100, $percentage) }}%"></div>
                                        </div>
                                        <div class="text-[10px] text-gray-600 text-right font-medium">
                                            Alvo: {{ in_array($goal->type, ['faturamento', 'vendas']) ? 'R$ ' . number_format($goal->target_value, 2, ',', '.') : number_format($goal->target_value, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-600 italic font-medium">Individual p/ usuário</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($goal->reward_type && $goal->reward_value)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-black text-emerald-400 tracking-tight">
                                            {{ $goal->reward_type === 'cash' ? 'R$ ' . number_format($goal->reward_value, 2, ',', '.') : ucfirst($goal->reward_type) }}
                                        </span>
                                        <span class="text-[9px] font-black uppercase tracking-widest {{ $goal->auto_reward ? 'text-[#D4AF37]/60' : 'text-gray-500' }}">
                                            {{ $goal->auto_reward ? 'Auto-Credit' : 'Manual Approval' }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-[10px] font-bold text-gray-700 uppercase">Sem Prêmiação</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.goals.toggle-status', $goal) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 text-[10px] font-black uppercase rounded-full transition-all border {{ $goal->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20 hover:bg-danger-500/20' }}">
                                        {{ $goal->is_active ? 'Active' : 'Offline' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($goal->reward_type && $goal->reward_value && ($percentage !== null && $percentage >= 100))
                                        @if($goal->user)
                                            @php $hasAchieved = $goal->hasUserAchieved($goal->user->id); @endphp
                                            @if(!$hasAchieved)
                                                <form method="POST" action="{{ route('admin.goals.reward-user', $goal) }}" class="inline" onsubmit="return confirm('Premiar usuário?');">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $goal->user->id }}">
                                                    <button type="submit" class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 hover:bg-emerald-500 flex items-center justify-center transition-all border border-emerald-500/20 hover:text-black shadow-lg shadow-emerald-500/20">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" /></svg>
                                                    </button>
                                                </form>
                                            @else
                                                <div class="w-8 h-8 rounded-lg bg-emerald-500 text-black flex items-center justify-center shadow-lg shadow-emerald-500/20">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                                </div>
                                            @endif
                                        @endif
                                    @endif
                                    
                                    <a href="{{ route('admin.goals.edit', $goal) }}" class="w-8 h-8 rounded-lg bg-white/5 text-gray-400 hover:text-[#D4AF37] hover:bg-white/10 flex items-center justify-center transition-all border border-white/5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.goals.destroy', $goal) }}" class="inline" onsubmit="return confirm('Excluir meta?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white flex items-center justify-center transition-all border border-red-500/20">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-white/5 rounded-3xl flex items-center justify-center mb-4 text-gray-700">
                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-400">Silêncio no horizonte...</h3>
                                    <p class="text-gray-600 mt-2 text-sm max-w-xs">Nenhuma meta configurada para este filtro. Defina novos objetivos para expandir sua plataforma.</p>
                                    <a href="{{ route('admin.goals.create') }}" class="mt-8 px-8 py-3 bg-white/5 hover:bg-white/10 text-white font-bold rounded-xl border border-white/10 transition-all">Começar agora</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($goals->hasPages())
            <div class="px-6 py-4 bg-white/[0.01] border-t border-white/[0.03]">
                {{ $goals->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    /* Premium scrollbar for the table */
    .scrollbar-thin::-webkit-scrollbar { width: 6px; height: 6px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(212,175,55,0.3); }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(200%); }
    }
</style>
@endsection

