@extends('layouts.admin')

@section('title', 'Gerenciar Usuários')
@section('page-title', 'Usuários')
@section('page-description', 'Gerencie usuários e suas configurações de gateway')

@section('content')
<div class="p-6 max-w-[1400px] mx-auto">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white mb-2 tracking-tight">Painel de Clientes</h2>
        <p class="text-gray-400 text-sm">Gerencie todos os usuários, limites e configurações de integração vinculadas.</p>
    </div>

    <!-- Filters -->
    <div class="bg-[#161616] border border-white/5 rounded-2xl p-5 mb-8 shadow-xl">
        <form method="GET" class="flex flex-wrap lg:flex-nowrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Busca Rápida</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Nome, e-mail ou CPF/CNPJ..."
                        value="{{ request('search') }}"
                        class="w-full pl-10 pr-4 py-2.5 bg-black/50 border border-white/10 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:border-[#D4AF37]/50 focus:ring-1 focus:ring-[#D4AF37]/50 transition-all text-sm"
                    >
                </div>
            </div>
            
            <div class="w-full sm:w-auto min-w-[160px]">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Gateway Adquirente</label>
                <select name="gateway" class="w-full px-4 py-2.5 bg-black/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-[#D4AF37]/50 focus:ring-1 focus:ring-[#D4AF37]/50 transition-all text-sm appearance-none">
                    <option value="" class="bg-[#161616]">Todos os Gateways</option>
                    @foreach($gateways as $gateway)
                        <option value="{{ $gateway->id }}" {{ request('gateway') == $gateway->id ? 'selected' : '' }} class="bg-[#161616] text-white">
                            {{ $gateway->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-auto min-w-[140px]">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Conta</label>
                <select name="account_type" class="w-full px-4 py-2.5 bg-black/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-[#D4AF37]/50 focus:ring-1 focus:ring-[#D4AF37]/50 transition-all text-sm appearance-none">
                    <option value="" class="bg-[#161616]">Visualizar Todas</option>
                    <option value="pessoa_fisica" {{ request('account_type') == 'pessoa_fisica' ? 'selected' : '' }} class="bg-[#161616]">Pessoa Física</option>
                    <option value="pessoa_juridica" {{ request('account_type') == 'pessoa_juridica' ? 'selected' : '' }} class="bg-[#161616]">Pessoa Jurídica</option>
                </select>
            </div>
            
            <div class="w-full sm:w-auto min-w-[140px]">
                <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Situação</label>
                <select name="status" class="w-full px-4 py-2.5 bg-black/50 border border-white/10 rounded-xl text-white focus:outline-none focus:border-[#D4AF37]/50 focus:ring-1 focus:ring-[#D4AF37]/50 transition-all text-sm appearance-none">
                    <option value="" class="bg-[#161616]">Todos os Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }} class="bg-[#161616]">Ativos</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }} class="bg-[#161616]">Bloqueados</option>
                </select>
            </div>
            
            <div class="w-full sm:w-auto">
                <button type="submit" class="w-full h-[42px] bg-[#D4AF37] hover:bg-[#c4a133] text-black font-bold px-6 rounded-xl text-sm transition-colors shadow-lg shadow-[#D4AF37]/20 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table Modernized -->
    <div class="bg-[#161616] rounded-2xl border border-white/5 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-black/20 text-[10px] uppercase tracking-wider text-gray-500 font-bold">
                        <th class="px-6 py-4 whitespace-nowrap">ID / Cliente</th>
                        <th class="px-6 py-4 whitespace-nowrap hidden md:table-cell">Documento</th>
                        <th class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">Integração</th>
                        <th class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">Kyc Status</th>
                        <th class="px-6 py-4 whitespace-nowrap text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($users as $user)
                        <tr class="hover:bg-white/[0.02] transition-colors {{ $user->isBlocked() ? 'opacity-60 bg-red-900/10' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#D4AF37] to-[#806B22] flex flex-shrink-0 items-center justify-center text-black font-bold shadow-lg shadow-[#D4AF37]/20">
                                        {{ substr(strtoupper($user->name), 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <h4 class="text-white font-medium text-sm truncate">{{ $user->name }}</h4>
                                            <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-sm {{ $user->isPessoaFisica() ? 'bg-blue-500/20 text-blue-400' : 'bg-purple-500/20 text-purple-400' }} tracking-wider">
                                                {{ $user->isPessoaFisica() ? 'PF' : 'PJ' }}
                                            </span>
                                            @if($user->isBlocked())
                                                <span class="px-1.5 py-0.5 text-[9px] font-bold rounded-sm bg-red-500/20 text-red-500 tracking-wider">BLOQUEADO</span>
                                            @endif
                                        </div>
                                        <p class="text-gray-500 text-xs truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <span class="text-gray-300 text-sm font-mono bg-black/30 px-2 py-1 rounded border border-white/5">{{ $user->formatted_document }}</span>
                                <div class="text-[10px] text-gray-600 mt-1">Desde {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 hidden lg:table-cell">
                                @if($user->assignedGateway)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 text-xs font-semibold border border-emerald-500/20">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full shadow-[0_0_5px_#10b981]"></span>
                                        {{ $user->assignedGateway->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/5 text-gray-400 text-xs border border-white/5">
                                        <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                                        Não Integrado
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 hidden sm:table-cell">
                                @if($user->documentVerification)
                                    @php
                                        $statusClass = $user->documentVerification->isApproved() ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                                                       ($user->documentVerification->isRejected() ? 'bg-red-500/10 text-red-400 border-red-500/20' : 
                                                       'bg-yellow-500/10 text-yellow-500 border-yellow-500/20');
                                    @endphp
                                    <span class="inline-flex px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border {{ $statusClass }}">
                                        {{ $user->documentVerification->status }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider border bg-white/5 text-gray-500 border-white/5">
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="loadUserDetails({{ $user->id }})" class="p-2 bg-white/5 hover:bg-white/10 text-gray-300 rounded-lg transition-colors border border-white/5" title="Inspeções/Dashboard">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                    <button onclick="openFeesModal({{ $user->id }})" class="p-2 bg-white/5 hover:bg-emerald-500/20 text-gray-300 hover:text-emerald-400 rounded-lg transition-colors border border-white/5" title="Configurar Taxas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" /></svg>
                                    </button>
                                    <button onclick="openChangeGatewayModal({{ $user->id }}, '{{ str_replace("'", "\'", $user->name) }}', {{ $user->assignedGateway ? $user->assignedGateway->id : 'null' }})" class="p-2 bg-white/5 hover:bg-[#D4AF37]/20 text-gray-300 hover:text-[#D4AF37] rounded-lg transition-colors border border-white/5" title="Alterar Gateway">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    </button>
                                    <button onclick="openUserActionsModal({{ $user->id }}, '{{ str_replace("'", "\'", $user->name) }}', {{ $user->isBlocked() ? 'true' : 'false' }})" class="p-2 bg-white/5 hover:bg-red-500/20 text-gray-300 hover:text-red-400 rounded-lg transition-colors border border-white/5" title="Ações Críticas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-white/10">
                                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                </div>
                                <p class="text-white font-medium text-lg">Nenhum usuário classificado</p>
                                <p class="text-gray-500 text-sm mt-1">O filtro aplicado não retornou resultados do banco de dados.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Injectable -->
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-white/5 bg-black/20">
                {{ $users->links('vendor.pagination.tailwind') }}
            </div>
        @endif
    </div>
</div>

<!-- User Details Modal -->
<div id="userModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900" id="userModalTitle">Detalhes do Usuário</h3>
                    <button onclick="closeUserModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div id="userModalContent" class="space-y-6">
                    <!-- Content will be loaded via AJAX -->
                    <div class="flex justify-center items-center py-12">
                        <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Actions Modal -->
<div id="userActionsModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900" id="userActionsTitle">Ações para Usuário</h3>
                    <button onclick="closeUserActionsModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3">
                    <a id="viewUserLink" href="#" class="block w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-3 rounded-lg text-sm text-center transition-colors">
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ver Perfil Completo
                    </a>
                    
                    <a id="editUserLink" href="#" class="block w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-3 rounded-lg text-sm text-center transition-colors">
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Editar Usuário
                    </a>
                    
                    <a id="loginAsUserLink" href="#" class="block w-full bg-orange-600 hover:bg-orange-700 text-gray-900 px-4 py-3 rounded-lg text-sm text-center transition-colors">
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Acessar Conta do Usuário
                    </a>
                    
                    <div id="blockUserContainer">
                        <button 
                            id="blockUserBtn"
                            onclick="openBlockUserModal()"
                            class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-3 rounded-lg text-sm text-center transition-colors"
                        >
                            <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Bloquear Usuário
                        </button>
                        
                        <form id="unblockUserForm" action="" method="POST" class="hidden">
                            @csrf
                            <button 
                                type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-3 rounded-lg text-sm text-center transition-colors"
                            >
                                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                </svg>
                                Desbloquear Usuário
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block User Modal -->
<div id="blockUserModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Bloquear Usuário</h3>
                    <button onclick="closeBlockUserModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-green-600 font-medium text-sm">ATENÇÃO!</h3>
                            <p class="text-green-700 text-sm mt-1">
                                Bloquear um usuário impedirá que ele acesse o sistema. Esta ação deve ser usada apenas em casos de violação dos termos de uso ou atividades suspeitas.
                            </p>
                        </div>
                    </div>
                </div>

                <form id="blockUserForm" action="" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="blocked_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo do Bloqueio *
                        </label>
                        <textarea 
                            id="blocked_reason" 
                            name="blocked_reason" 
                            rows="4" 
                            required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Informe o motivo do bloqueio"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeBlockUserModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Bloquear Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Gateway Modal -->
<div id="changeGatewayModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900" id="changeGatewayTitle">Trocar Adquirente</h3>
                    <button onclick="closeChangeGatewayModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="changeGatewayForm" action="" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="assigned_gateway_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Gateway Atribuído
                        </label>
                        <select 
                            id="assigned_gateway_id" 
                            name="assigned_gateway_id" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Nenhum gateway atribuído</option>
                            @foreach($gateways->where('is_active', true) as $gateway)
                                <option value="{{ $gateway->id }}">
                                    {{ $gateway->name }} {{ $gateway->is_default ? '(Padrão)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Escolha qual adquirente este usuário irá utilizar</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeChangeGatewayModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Custom Fees Modal -->
<div id="feesModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900" id="feesModalTitle">Taxas Personalizadas</h3>
                    <button onclick="closeFeesModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-3 mb-6">
                    <p class="text-blue-700 text-sm">Defina taxas personalizadas para este usuário. Deixe em branco para usar as taxas globais.</p>
                </div>

                <form id="feesForm" class="space-y-6">
                    <!-- PIX Section -->
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-4">PIX</h4>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa fixa (R$) *</label>
                                <input type="number" step="0.01" name="pix_fixed" id="pix_fixed" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa variável (%) *</label>
                                <input type="number" step="0.01" name="pix_variable" id="pix_variable" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor mínimo de transação (R$)</label>
                                <input type="number" step="0.01" name="pix_min_transaction" id="pix_min_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor máximo de transação (R$)</label>
                                <input type="number" step="0.01" name="pix_max_transaction" id="pix_max_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Boleto Section -->
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-4">Boleto Bancário</h4>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa fixa (R$) *</label>
                                <input type="number" step="0.01" name="boleto_fixed" id="boleto_fixed" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa variável (%) *</label>
                                <input type="number" step="0.01" name="boleto_variable" id="boleto_variable" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor mínimo de transação (R$)</label>
                                <input type="number" step="0.01" name="boleto_min_transaction" id="boleto_min_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor máximo de transação (R$)</label>
                                <input type="number" step="0.01" name="boleto_max_transaction" id="boleto_max_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Credit Card Section -->
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-4">Cartão de Crédito</h4>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa fixa (R$) *</label>
                                <input type="number" step="0.01" name="card_fixed" id="card_fixed" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa à vista (%) *</label>
                                <input type="number" step="0.01" name="card_1x" id="card_1x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">2 parcelas (%)</label>
                                <input type="number" step="0.01" name="card_2x" id="card_2x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">3 parcelas (%)</label>
                                <input type="number" step="0.01" name="card_3x" id="card_3x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">4 parcelas (%)</label>
                                <input type="number" step="0.01" name="card_4x" id="card_4x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">5 parcelas (%)</label>
                                <input type="number" step="0.01" name="card_5x" id="card_5x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">6 parcelas (%)</label>
                                <input type="number" step="0.01" name="card_6x" id="card_6x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor máximo (R$)</label>
                                <input type="number" step="0.01" name="card_max" id="card_max" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor mínimo de transação (R$)</label>
                                <input type="number" step="0.01" name="card_min_transaction" id="card_min_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor máximo de transação (R$)</label>
                                <input type="number" step="0.01" name="card_max_transaction" id="card_max_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Withdrawal Fee Section -->
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-4">Taxa de Saque (PIX OUT)</h4>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Taxa de Saque</label>
                            <select 
                                id="withdrawal_fee_type" 
                                name="withdrawal_fee_type" 
                                class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900"
                                onchange="toggleWithdrawalFeeInputs()"
                            >
                                <option value="global">Global (Usar taxa padrão do sistema)</option>
                                <option value="fixed">Valor Fixo (R$)</option>
                                <option value="percentage">Percentual (%)</option>
                                <option value="both">Ambos (R$ + %)</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Global:</strong> Usa taxa padrão do sistema<br>
                                <strong>Fixo:</strong> Cobra valor fixo em reais<br>
                                <strong>Percentual:</strong> Cobra porcentagem do valor<br>
                                <strong>Ambos:</strong> Cobra valor fixo + porcentagem
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4" id="withdrawalFeeInputs" style="display: none;">
                            <div id="withdrawalFixedContainer">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Fixa (R$)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="withdrawal_fixed_fee" 
                                    id="withdrawal_fixed_fee" 
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900"
                                    placeholder="0.00"
                                >
                            </div>
                            <div id="withdrawalPercentageContainer">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Percentual (%)</label>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    name="withdrawal_percentage_fee" 
                                    id="withdrawal_percentage_fee" 
                                    class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="saveFees()" class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors">
                            Salvar Taxas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentUserId = null;

// Obter taxas globais do sistema
const globalFees = {
    pix: {
        fixed: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.00 }},
        variable: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 1.99 }},
        max: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null' }},
        min_transaction: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->min_transaction_value ?? 'null' }},
        max_transaction: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->max_transaction_value ?? 'null' }}
    },
    boleto: {
        fixed: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 2.00 }},
        variable: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 2.49 }},
        max: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null' }},
        min_transaction: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->min_transaction_value ?? 'null' }},
        max_transaction: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->max_transaction_value ?? 'null' }}
    },
    card: {
        fixed: {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.39 }},
        max: {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null' }},
        min_transaction: {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->min_transaction_value ?? 'null' }},
        max_transaction: {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->max_transaction_value ?? 'null' }},
        '1x': {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99 }},
        '2x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 0.60 }},
        '3x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.20 }},
        '4x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.80 }},
        '5x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 2.40 }},
        '6x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 3.00 }}
    }
};
console.log("globalFees loaded:", globalFees);

// Load user details via AJAX
function loadUserDetails(userId) {
    currentUserId = userId;
    
    // Show modal with loading state
    document.getElementById('userModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
    
    // Fetch user details
    fetch(`/admin/users/${userId}/details`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('userModalContent').innerHTML = data.html;
        } else {
            document.getElementById('userModalContent').innerHTML = `
                <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                    <p class="text-green-600 text-center">Erro ao carregar detalhes do usuário: ${data.error || 'Erro desconhecido'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('userModalContent').innerHTML = `
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">
                <p class="text-green-600 text-center">Erro ao carregar detalhes do usuário</p>
            </div>
        `;
    });
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// User Actions Modal
function openUserActionsModal(userId, userName, isBlocked) {
    currentUserId = userId;
    
    // Set modal title
    document.getElementById('userActionsTitle').textContent = `Ações para ${userName}`;
    
    // Set links
    document.getElementById('viewUserLink').href = `/admin/users/${userId}`;
    document.getElementById('editUserLink').href = `/admin/users/${userId}/edit`;
    document.getElementById('loginAsUserLink').href = `/admin/users/${userId}/login`;
    document.getElementById('loginAsUserLink').onclick = function() {
        return confirm('Tem certeza que deseja acessar a conta deste usuário?');
    };
    
    // Set block/unblock button
    if (isBlocked) {
        document.getElementById('blockUserBtn').classList.add('hidden');
        document.getElementById('unblockUserForm').classList.remove('hidden');
        document.getElementById('unblockUserForm').action = `/admin/users/${userId}/unblock`;
    } else {
        document.getElementById('blockUserBtn').classList.remove('hidden');
        document.getElementById('unblockUserForm').classList.add('hidden');
    }
    
    // Show modal
    document.getElementById('userActionsModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeUserActionsModal() {
    document.getElementById('userActionsModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Block User Modal
function openBlockUserModal() {
    closeUserActionsModal();
    
    document.getElementById('blockUserForm').action = `/admin/users/${currentUserId}/block`;
    document.getElementById('blockUserModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeBlockUserModal() {
    document.getElementById('blockUserModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Change Gateway Modal
function openChangeGatewayModal(userId, userName, currentGatewayId) {
    currentUserId = userId;
    
    // Set modal title
    document.getElementById('changeGatewayTitle').textContent = `Trocar Adquirente para ${userName}`;
    
    // Set form action
    document.getElementById('changeGatewayForm').action = `/admin/users/${userId}`;
    
    // Set current gateway
    if (currentGatewayId) {
        document.getElementById('assigned_gateway_id').value = currentGatewayId;
    } else {
        document.getElementById('assigned_gateway_id').value = '';
    }
    
    // Show modal
    document.getElementById('changeGatewayModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeChangeGatewayModal() {
    document.getElementById('changeGatewayModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Fees Modal
function openFeesModal(userId) {
    currentUserId = userId;
    
    // Preencher o formulário com as taxas globais
    document.getElementById('pix_fixed').value = globalFees.pix.fixed.toFixed(2);
    document.getElementById('pix_variable').value = globalFees.pix.variable.toFixed(2);
    if (globalFees.pix.min_transaction !== null) {
        document.getElementById('pix_min_transaction').value = globalFees.pix.min_transaction.toFixed(2);
    }
    if (globalFees.pix.max_transaction !== null) {
        document.getElementById('pix_max_transaction').value = globalFees.pix.max_transaction.toFixed(2);
    }
    
    document.getElementById('boleto_fixed').value = globalFees.boleto.fixed.toFixed(2);
    document.getElementById('boleto_variable').value = globalFees.boleto.variable.toFixed(2);
    if (globalFees.boleto.min_transaction !== null) {
        document.getElementById('boleto_min_transaction').value = globalFees.boleto.min_transaction.toFixed(2);
    }
    if (globalFees.boleto.max_transaction !== null) {
        document.getElementById('boleto_max_transaction').value = globalFees.boleto.max_transaction.toFixed(2);
    }
    
    document.getElementById('card_fixed').value = globalFees.card.fixed.toFixed(2);
    if (globalFees.card.min_transaction !== null) {
        document.getElementById('card_min_transaction').value = globalFees.card.min_transaction.toFixed(2);
    }
    if (globalFees.card.max_transaction !== null) {
        document.getElementById('card_max_transaction').value = globalFees.card.max_transaction.toFixed(2);
    }
    
    document.getElementById('card_1x').value = globalFees.card['1x'].toFixed(2);
    document.getElementById('card_2x').value = globalFees.card['2x'].toFixed(2);
    document.getElementById('card_3x').value = globalFees.card['3x'].toFixed(2);
    document.getElementById('card_4x').value = globalFees.card['4x'].toFixed(2);
    document.getElementById('card_5x').value = globalFees.card['5x'].toFixed(2);
    document.getElementById('card_6x').value = globalFees.card['6x'].toFixed(2);
    
    // Fetch user's custom fees if available
    fetch(`/admin/users/${userId}/fees`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.fees) {
            // Update modal title with user name
            document.getElementById('feesModalTitle').textContent = `Taxas Personalizadas`;
            
            // Fill form with user's custom fees
            document.getElementById('pix_fixed').value = data.fees.pix.fixed.toFixed(2);
            document.getElementById('pix_variable').value = data.fees.pix.percentage.toFixed(2);
            if (data.fees.pix.min_transaction !== null) {
                document.getElementById('pix_min_transaction').value = data.fees.pix.min_transaction.toFixed(2);
            } else {
                document.getElementById('pix_min_transaction').value = '';
            }
            if (data.fees.pix.max_transaction !== null) {
                document.getElementById('pix_max_transaction').value = data.fees.pix.max_transaction.toFixed(2);
            } else {
                document.getElementById('pix_max_transaction').value = '';
            }
            
            document.getElementById('boleto_fixed').value = data.fees.bank_slip.fixed.toFixed(2);
            document.getElementById('boleto_variable').value = data.fees.bank_slip.percentage.toFixed(2);
            if (data.fees.bank_slip.min_transaction !== null) {
                document.getElementById('boleto_min_transaction').value = data.fees.bank_slip.min_transaction.toFixed(2);
            } else {
                document.getElementById('boleto_min_transaction').value = '';
            }
            if (data.fees.bank_slip.max_transaction !== null) {
                document.getElementById('boleto_max_transaction').value = data.fees.bank_slip.max_transaction.toFixed(2);
            } else {
                document.getElementById('boleto_max_transaction').value = '';
            }
            
            document.getElementById('card_fixed').value = data.fees.credit_card.fixed.toFixed(2);
            if (data.fees.credit_card.min_transaction !== null) {
                document.getElementById('card_min_transaction').value = data.fees.credit_card.min_transaction.toFixed(2);
            } else {
                document.getElementById('card_min_transaction').value = '';
            }
            if (data.fees.credit_card.max_transaction !== null) {
                document.getElementById('card_max_transaction').value = data.fees.credit_card.max_transaction.toFixed(2);
            } else {
                document.getElementById('card_max_transaction').value = '';
            }
            
            document.getElementById('card_1x').value = data.fees.credit_card.percentage.toFixed(2);
            
            // Fill installment fees if available
            if (data.fees.credit_card.installments) {
                const installments = data.fees.credit_card.installments;
                if (installments['2x']) document.getElementById('card_2x').value = installments['2x'].toFixed(2);
                if (installments['3x']) document.getElementById('card_3x').value = installments['3x'].toFixed(2);
                if (installments['4x']) document.getElementById('card_4x').value = installments['4x'].toFixed(2);
                if (installments['5x']) document.getElementById('card_5x').value = installments['5x'].toFixed(2);
                if (installments['6x']) document.getElementById('card_6x').value = installments['6x'].toFixed(2);
            }
            
            // Fill withdrawal fee data if available
            if (data.withdrawal_fees) {
                document.getElementById("withdrawal_fee_type").value = data.withdrawal_fees.fee_type || "global";
                toggleWithdrawalFeeInputs();
                
                if (data.withdrawal_fees.fixed_fee) {
                    document.getElementById("withdrawal_fixed_fee").value = data.withdrawal_fees.fixed_fee.toFixed(2);
                }
                if (data.withdrawal_fees.percentage_fee) {
                    document.getElementById("withdrawal_percentage_fee").value = data.withdrawal_fees.percentage_fee.toFixed(2);
                }
            }
        }
    })
    .catch(error => {
        console.error("Error fetching user fees:", error);
    });
    
    document.getElementById("feesModal").classList.remove("hidden");
    document.body.classList.add("modal-open");
}

function closeFeesModal() {
    document.getElementById('feesModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Toggle withdrawal fee inputs based on type
function toggleWithdrawalFeeInputs() {
    const type = document.getElementById("withdrawal_fee_type").value;
    const container = document.getElementById("withdrawalFeeInputs");
    const fixedContainer = document.getElementById("withdrawalFixedContainer");
    const percentageContainer = document.getElementById("withdrawalPercentageContainer");
    
    if (type === "global") {
        container.style.display = "none";
        document.getElementById("withdrawal_fixed_fee").value = "";
        document.getElementById("withdrawal_percentage_fee").value = "";
    } else if (type === "fixed") {
        container.style.display = "grid";
        fixedContainer.style.display = "block";
        percentageContainer.style.display = "none";
        document.getElementById("withdrawal_percentage_fee").value = "";
    } else if (type === "percentage") {
        container.style.display = "grid";
        fixedContainer.style.display = "none";
        percentageContainer.style.display = "block";
        document.getElementById("withdrawal_fixed_fee").value = "";
    } else if (type === "both") {
        container.style.display = "grid";
        fixedContainer.style.display = "block";
        percentageContainer.style.display = "block";
    }
}

function saveFees() {
    // Get form data
    const formData = {
        pix_fixed: parseFloat(document.getElementById('pix_fixed').value),
        pix_variable: parseFloat(document.getElementById('pix_variable').value),
        pix_min_transaction: document.getElementById('pix_min_transaction').value ? parseFloat(document.getElementById('pix_min_transaction').value) : null,
        pix_max_transaction: document.getElementById('pix_max_transaction').value ? parseFloat(document.getElementById('pix_max_transaction').value) : null,
        boleto_fixed: parseFloat(document.getElementById('boleto_fixed').value),
        boleto_variable: parseFloat(document.getElementById('boleto_variable').value),
        boleto_min_transaction: document.getElementById('boleto_min_transaction').value ? parseFloat(document.getElementById('boleto_min_transaction').value) : null,
        boleto_max_transaction: document.getElementById('boleto_max_transaction').value ? parseFloat(document.getElementById('boleto_max_transaction').value) : null,
        card_fixed: parseFloat(document.getElementById('card_fixed').value),
        card_min_transaction: document.getElementById('card_min_transaction').value ? parseFloat(document.getElementById('card_min_transaction').value) : null,
        card_max_transaction: document.getElementById('card_max_transaction').value ? parseFloat(document.getElementById('card_max_transaction').value) : null,
        card_1x: parseFloat(document.getElementById('card_1x').value),
        card_2x: parseFloat(document.getElementById('card_2x').value),
        card_3x: parseFloat(document.getElementById('card_3x').value),
        card_4x: parseFloat(document.getElementById('card_4x').value),
        card_5x: parseFloat(document.getElementById('card_5x').value),
        card_6x: parseFloat(document.getElementById('card_6x').value),
        withdrawal_fee_type: document.getElementById("withdrawal_fee_type").value,
        withdrawal_fixed_fee: document.getElementById("withdrawal_fixed_fee").value ? parseFloat(document.getElementById("withdrawal_fixed_fee").value) : null,
        withdrawal_percentage_fee: document.getElementById("withdrawal_percentage_fee").value ? parseFloat(document.getElementById("withdrawal_percentage_fee").value) : null,
    };
    
    // Send AJAX request
    fetch(`/admin/users/${currentUserId}/fees`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Taxas salvas com sucesso!');
            closeFeesModal();
        } else {
            alert('Erro ao salvar taxas: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao salvar taxas');
    });
}
</script>
@endpush
@endsection