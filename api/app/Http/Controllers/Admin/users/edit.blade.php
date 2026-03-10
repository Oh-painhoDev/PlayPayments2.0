@extends('layouts.admin')

@section('title', 'Editar Usuário')
@section('page-title', 'Editar Usuário')
@section('page-description', 'Configure gateway e credenciais para ' . $user->name)

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Gateway Assignment -->
                <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Atribuição de Gateway</h3>
                    
                    <div>
                        <label for="assigned_gateway_id" class="block text-sm font-medium text-gray-300 mb-2">
                            Gateway Atribuído
                        </label>
                        <select 
                            id="assigned_gateway_id" 
                            name="assigned_gateway_id" 
                            class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Nenhum gateway atribuído</option>
                            @foreach($gateways as $gateway)
                                <option value="{{ $gateway->id }}" {{ $user->assigned_gateway_id == $gateway->id ? 'selected' : '' }}>
                                    {{ $gateway->name }} {{ $gateway->is_default ? '(Padrão)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Escolha qual adquirente este usuário irá utilizar</p>
                    </div>
                </div>

                <!-- Gateway Credentials -->
                <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Credenciais dos Gateways</h3>
                    
                    @foreach($gateways as $gateway)
                        <div class="mb-6 p-4 bg-gray-900 rounded-lg border border-gray-700">
                            <h4 class="text-md font-medium text-white mb-3 flex items-center">
                                {{ $gateway->name }}
                                @if($gateway->is_default)
                                    <span class="ml-2 px-2 py-1 text-xs bg-blue-500/10 text-blue-400 rounded">Padrão</span>
                                @endif
                            </h4>
                            
                            @php
                                $credential = $credentials->get($gateway->id);
                            @endphp
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Chave Pública
                                    </label>
                                    <input 
                                        type="text" 
                                        name="credentials[{{ $gateway->id }}][public_key]"
                                        value="{{ $credential ? $credential->public_key : '' }}"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 text-sm font-mono"
                                        placeholder="public_xxxxxxxxx"
                                    >
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">
                                        Chave Secreta
                                    </label>
                                    <input 
                                        type="password" 
                                        name="credentials[{{ $gateway->id }}][secret_key]"
                                        value="{{ $credential ? $credential->secret_key : '' }}"
                                        class="w-full px-3 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 text-sm font-mono"
                                        placeholder="secret_xxxxxxxxx"
                                    >
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        name="credentials[{{ $gateway->id }}][is_sandbox]"
                                        value="1"
                                        {{ $credential && $credential->is_sandbox ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                                    >
                                    <span class="ml-2 text-sm text-gray-300">Ambiente de Sandbox (Testes)</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Informações do Usuário</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-400">Nome:</span>
                        <p class="text-white font-medium">{{ $user->name }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-400">Email:</span>
                        <p class="text-white font-medium">{{ $user->email }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-400">Tipo:</span>
                        <p class="text-white font-medium">{{ $user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-400">Documento:</span>
                        <p class="text-white font-medium">{{ $user->formatted_document }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-400">Cadastro:</span>
                        <p class="text-white font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Current Gateway -->
            <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Gateway Atual</h3>
                
                @if($user->assignedGateway)
                    <div class="space-y-2">
                        <p class="text-white font-medium">{{ $user->assignedGateway->name }}</p>
                        <p class="text-gray-400 text-sm">{{ $user->assignedGateway->api_url }}</p>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-green-400 text-sm">Ativo</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <p class="text-gray-400 text-sm">Nenhum gateway atribuído</p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-gray-950 rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.users.show', $user) }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Ver Detalhes
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" class="block w-full bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar à Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection