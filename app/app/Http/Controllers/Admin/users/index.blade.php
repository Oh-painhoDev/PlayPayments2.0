@extends('layouts.admin')

@section('title', 'Gerenciar Usuários')
@section('page-title', 'Usuários')
@section('page-description', 'Gerencie usuários e suas configurações de gateway')

@section('content')
<div class="p-6">
    <!-- Filters -->
    <div class="bg-gray-950 rounded-lg border border-gray-800 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Buscar por nome, email ou documento..."
                    value="{{ request('search') }}"
                    class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-400 text-sm"
                >
            </div>
            
            <div>
                <select name="gateway" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm">
                    <option value="">Todos os Gateways</option>
                    @foreach($gateways as $gateway)
                        <option value="{{ $gateway->id }}" {{ request('gateway') == $gateway->id ? 'selected' : '' }}>
                            {{ $gateway->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <select name="account_type" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white text-sm">
                    <option value="">Todos os Tipos</option>
                    <option value="pessoa_fisica" {{ request('account_type') == 'pessoa_fisica' ? 'selected' : '' }}>Pessoa Física</option>
                    <option value="pessoa_juridica" {{ request('account_type') == 'pessoa_juridica' ? 'selected' : '' }}>Pessoa Jurídica</option>
                </select>
            </div>
            
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-gray-950 rounded-lg border border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Documentos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Cadastro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-900/50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-white font-medium">{{ $user->name }}</p>
                                    <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                                    <p class="text-gray-500 text-xs">{{ $user->formatted_document }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded {{ $user->isPessoaFisica() ? 'bg-blue-500/10 text-blue-400' : 'bg-purple-500/10 text-purple-400' }}">
                                    {{ $user->isPessoaFisica() ? 'PF' : 'PJ' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->assignedGateway)
                                    <span class="px-2 py-1 text-xs bg-green-500/10 text-green-400 rounded">
                                        {{ $user->assignedGateway->name }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-500/10 text-gray-400 rounded">
                                        Não atribuído
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($user->documentVerification)
                                    <span class="px-2 py-1 text-xs rounded {{ $user->documentVerification->isApproved() ? 'bg-green-500/10 text-green-400' : ($user->documentVerification->isRejected() ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }}">
                                        {{ ucfirst($user->documentVerification->status) }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-500/10 text-gray-400 rounded">
                                        Pendente
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-400 text-sm">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-blue-400 hover:text-blue-300 text-sm">
                                        Ver
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-green-400 hover:text-green-300 text-sm">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                Nenhum usuário encontrado
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-6 py-3 border-t border-gray-800">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection