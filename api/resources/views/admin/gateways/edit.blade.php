@extends('layouts.admin')

@section('title', 'Editar Gateway')
@section('page-title', 'Editar Gateway')
@section('page-description', 'Atualize as configurações do gateway ' . $gateway->name)

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
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
            <form action="{{ route('admin.gateways.update', $gateway->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Gateway Information -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Informações do Gateway</h2>
                    
                    <div class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome do Gateway *
                            </label>
                            <input 
                                id="name" 
                                name="name" 
                                type="text" 
                                required 
                                value="{{ old('name', $gateway->name) }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Ex: StrikeCash, SkalePay, etc."
                            >
                        </div>

                        <!-- API URL -->
                        <div>
                            <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">
                                URL da API *
                            </label>
                            <input 
                                id="api_url" 
                                name="api_url" 
                                type="text" 
                                required 
                                value="{{ old('api_url', $gateway->api_url) }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Ex: https://srv.strikecash.com.br"
                            >
                        </div>

                        <!-- Gateway Type (Read-only) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Gateway
                            </label>
                            <input 
                                type="text" 
                                value="{{ ucfirst($gateway->getConfig('gateway_type', 'avivhub')) }}"
                                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-600 focus:outline-none transition-all duration-200"
                                readonly
                                disabled
                            >
                            <p class="text-xs text-gray-500 mt-1">O tipo de gateway não pode ser alterado após a criação</p>
                        </div>

                        <!-- Status -->
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', $gateway->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                                {{ $gateway->is_default ? 'disabled' : '' }}
                            >
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Gateway ativo
                            </label>
                            @if($gateway->is_default)
                                <span class="ml-2 text-xs text-yellow-400">(O gateway padrão não pode ser desativado)</span>
                            @endif
                        </div>

                        <!-- Default Gateway -->
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="is_default" 
                                name="is_default" 
                                value="1" 
                                {{ old('is_default', $gateway->is_default) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                            >
                            <label for="is_default" class="ml-2 text-sm text-gray-700">
                                Definir como gateway padrão
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('admin.gateways.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-200">
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Gateway Info -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Gateway</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">ID:</span>
                        <p class="text-gray-900 font-medium">{{ $gateway->id }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Slug:</span>
                        <p class="text-gray-900 font-medium">{{ $gateway->slug }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Status:</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 {{ $gateway->is_active ? 'bg-green-500' : 'bg-green-500' }} rounded-full mr-2"></div>
                            <span class="{{ $gateway->is_active ? 'text-green-600' : 'text-green-600' }} text-sm">{{ $gateway->is_active ? 'Ativo' : 'Inativo' }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Gateway Padrão:</span>
                        <span class="text-gray-900 font-medium">{{ $gateway->is_default ? 'Sim' : 'Não' }}</span>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Criado em:</span>
                        <p class="text-gray-900 font-medium">{{ $gateway->created_at ? $gateway->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Última atualização:</span>
                        <p class="text-gray-900 font-medium">{{ $gateway->updated_at ? $gateway->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas de Uso</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Usuários:</span>
                        <span class="text-gray-900">{{ \App\Models\User::where('assigned_gateway_id', $gateway->id)->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Transações:</span>
                        <span class="text-gray-900">{{ \App\Models\Transaction::where('gateway_id', $gateway->id)->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Volume Total:</span>
                        <span class="text-gray-900">R$ {{ number_format(\App\Models\Transaction::where('gateway_id', $gateway->id)->sum('amount'), 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.gateways.index') }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar para Lista
                    </a>
                    
                    <a href="{{ route('admin.gateways.fees', $gateway->id) }}" class="block w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Configurar Taxas
                    </a>
                    
                    @if(!$gateway->is_default && \App\Models\User::where('assigned_gateway_id', $gateway->id)->count() == 0 && \App\Models\Transaction::where('gateway_id', $gateway->id)->count() == 0)
                        <form action="{{ route('admin.gateways.destroy', $gateway->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este gateway?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors">
                                Excluir Gateway
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection