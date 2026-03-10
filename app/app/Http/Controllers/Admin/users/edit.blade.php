@extends('layouts.admin')

@section('title', 'Editar Usuário')
@section('page-title', 'Editar Usuário')
@section('page-description', 'Configure gateway para ' . $user->name)

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
            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- User Role -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Cargo do Usuário</h3>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-[#6B7280] mb-2">
                            Cargo
                        </label>
                        <select 
                            id="role" 
                            name="role" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @if(!auth()->user()->isAdmin()) disabled @endif
                        >
                            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuário</option>
                            <option value="gerente" {{ $user->role === 'gerente' ? 'selected' : '' }}>Gerente</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @if(!auth()->user()->isAdmin())
                            <input type="hidden" name="role" value="{{ $user->role }}">
                            <p class="text-xs text-yellow-400 mt-1">
                                ⚠️ Apenas administradores podem alterar o cargo de um usuário.
                            </p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Usuário:</strong> Acesso normal ao sistema<br>
                                <strong>Gerente:</strong> Acesso ao painel administrativo<br>
                                <strong>Administrador:</strong> Acesso completo ao sistema
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Gateway Assignment -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Atribuição de Gateway</h3>
                    
                    <div>
                        <label for="assigned_gateway_id" class="block text-sm font-medium text-[#6B7280] mb-2">
                            Gateway Atribuído
                        </label>
                        <select 
                            id="assigned_gateway_id" 
                            name="assigned_gateway_id" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
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

                    <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                        <p class="text-blue-700 text-xs">
                            💡 <strong>Importante:</strong> O usuário utilizará as credenciais configuradas pelo administrador no gateway selecionado.
                        </p>
                    </div>
                </div>

                <!-- Withdrawal Type -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Configuração de Saque</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#6B7280] mb-3">Tipo de Saque</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="withdrawal_manual" 
                                    name="withdrawal_type" 
                                    value="manual" 
                                    {{ $user->withdrawal_type !== 'automatic' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                                >
                                <label for="withdrawal_manual" class="ml-2 text-sm text-[#6B7280]">
                                    Manual (requer aprovação do administrador)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="withdrawal_automatic" 
                                    name="withdrawal_type" 
                                    value="automatic" 
                                    {{ $user->withdrawal_type === 'automatic' ? 'checked' : '' }}
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                                >
                                <label for="withdrawal_automatic" class="ml-2 text-sm text-[#6B7280]">
                                    Automático (sem aprovação)
                                </label>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Define se os saques do usuário serão processados automaticamente ou se precisarão de aprovação manual.
                        </p>
                    </div>

                    <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                        <p class="text-yellow-300 text-xs">
                            ⚠️ <strong>Atenção:</strong> Saques automáticos são processados imediatamente sem revisão. Use com cuidado.
                        </p>
                    </div>

                    <!-- BaaS Selection for Automatic Withdrawals -->
                    <div id="baas-selection" class="mt-6" style="{{ $user->withdrawal_type === 'automatic' ? '' : 'display: none;' }}">
                        <label for="assigned_baas_id" class="block text-sm font-medium text-[#6B7280] mb-2">
                            Provedor BaaS para Saques Automáticos
                        </label>
                        <select 
                            id="assigned_baas_id" 
                            name="assigned_baas_id" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Usar BaaS padrão</option>
                            @foreach($activeBaas as $baas)
                                <option value="{{ $baas->id }}" {{ $user->assigned_baas_id == $baas->id ? 'selected' : '' }}>
                                    {{ ucfirst($baas->gateway) }} {{ $baas->is_default ? '(Padrão)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Selecione qual provedor BaaS este usuário utilizará para saques automáticos
                        </p>
                    </div>

                    <!-- Retry Gateway Selection -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-[#6B7280]">
                                Gateway de Retentativa Individual
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="retry_enabled" 
                                    value="1"
                                    {{ $user->retry_enabled ? 'checked' : '' }}
                                    class="sr-only peer"
                                    id="retry_enabled"
                                >
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        
                        <select 
                            id="retry_gateway_id" 
                            name="retry_gateway_id" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Usar configuração global</option>
                            @foreach($gateways as $gateway)
                                <option value="{{ $gateway->id }}" {{ $user->retry_gateway_id == $gateway->id ? 'selected' : '' }}>
                                    {{ $gateway->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Se o gateway principal falhar, tentará automaticamente neste gateway alternativo
                        </p>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const withdrawalTypeInputs = document.querySelectorAll('input[name="withdrawal_type"]');
                        const baasSelection = document.getElementById('baas-selection');
                        
                        withdrawalTypeInputs.forEach(input => {
                            input.addEventListener('change', function() {
                                if (this.value === 'automatic') {
                                    baasSelection.style.display = 'block';
                                } else {
                                    baasSelection.style.display = 'none';
                                }
                            });
                        });
                    });
                </script>

                <!-- Access User Account Button -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Acessar Conta do Usuário</h3>
                    
                    <a 
                        href="{{ route('admin.users.login.as.user', $user) }}" 
                        class="w-full bg-orange-600/80 hover:bg-orange-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-200 block text-center"
                        onclick="return confirm('Tem certeza que deseja acessar a conta deste usuário?');"
                    >
                        Acessar Conta do Usuário
                    </a>
                    
                    <p class="text-xs text-gray-500 mt-2">
                        Acesse a conta deste usuário para visualizar o painel como ele vê e resolver problemas.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="bg-[#21b3dd] hover:bg-[#7A0000] text-white px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Informações do Usuário</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Nome:</span>
                        <p class="text-white font-medium">{{ $user->name }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Email:</span>
                        <p class="text-white font-medium">{{ $user->email }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Cargo:</span>
                        <p class="text-white font-medium">
                            @if($user->role === 'admin')
                                Administrador
                            @elseif($user->role === 'gerente')
                                Gerente
                            @else
                                Usuário
                            @endif
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Tipo:</span>
                        <p class="text-white font-medium">{{ $user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica' }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Documento:</span>
                        <p class="text-white font-medium">{{ $user->formatted_document }}</p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Cadastro:</span>
                        <p class="text-white font-medium">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Current Gateway -->
            <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Gateway Atual</h3>
                
                @if($user->assignedGateway)
                    <div class="space-y-2">
                        <p class="text-white font-medium">{{ $user->assignedGateway->name }}</p>
                        <p class="text-gray-600 text-sm">{{ $user->assignedGateway->api_url }}</p>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-green-600 text-sm">Ativo</span>
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <p class="text-gray-600 text-sm">Nenhum gateway atribuído</p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <button 
                        type="button"
                        onclick="openFeesModal({{ $user->id }})"
                        class="block w-full bg-[#21b3dd] hover:bg-[#7A0000] text-white px-4 py-2 rounded-lg text-sm text-center transition-colors"
                    >
                        Configurar Taxas
                    </button>
                    
                    <a href="{{ route('admin.users.withdrawal-fees.edit', $user) }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Taxas de Saque
                    </a>
                    
                    <a href="{{ route('admin.users.show', $user) }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-[#6B7280] px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Ver Detalhes
                    </a>
                    
                    <a href="{{ route('admin.users.index') }}" class="block w-full bg-gray-200 hover:bg-gray-300 text-[#6B7280] px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar à Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fees Modal -->
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor máximo de taxa (R$)</label>
                                <input type="number" step="0.01" name="pix_max" id="pix_max" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valor máximo de taxa (R$)</label>
                                <input type="number" step="0.01" name="boleto_max" id="boleto_max" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Opcional">
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
                        <button type="button" onclick="saveFees()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg text-sm transition-colors">
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
let currentUserId = {{ $user->id }};

// Obter taxas globais do sistema
const globalFees = {
    pix: {
        fixed: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.00 }},
        variable: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 1.99 }},
        max: {{ \App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null' }}
    },
    boleto: {
        fixed: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 2.00 }},
        variable: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 2.49 }},
        max: {{ \App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null' }}
    },
    card: {
        fixed: {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.39 }},
        max: {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null' }},
        '1x': {{ \App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99 }},
        '2x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 0.60 }},
        '3x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.20 }},
        '4x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.80 }},
        '5x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 2.40 }},
        '6x': {{ (\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 3.00 }}
    }
};

function openFeesModal(userId) {
    if (userId) {
        currentUserId = userId;
    }
    
    // Preencher o formulário com as taxas globais
    document.getElementById('pix_fixed').value = globalFees.pix.fixed.toFixed(2);
    document.getElementById('pix_variable').value = globalFees.pix.variable.toFixed(2);
    if (globalFees.pix.max !== null && globalFees.pix.max !== 'null') {
        document.getElementById('pix_max').value = globalFees.pix.max.toFixed(2);
    } else {
        document.getElementById('pix_max').value = '';
    }
    
    document.getElementById('boleto_fixed').value = globalFees.boleto.fixed.toFixed(2);
    document.getElementById('boleto_variable').value = globalFees.boleto.variable.toFixed(2);
    if (globalFees.boleto.max !== null && globalFees.boleto.max !== 'null') {
        document.getElementById('boleto_max').value = globalFees.boleto.max.toFixed(2);
    } else {
        document.getElementById('boleto_max').value = '';
    }
    
    document.getElementById('card_fixed').value = globalFees.card.fixed.toFixed(2);
    if (globalFees.card.max !== null && globalFees.card.max !== 'null') {
        document.getElementById('card_max').value = globalFees.card.max.toFixed(2);
    } else {
        document.getElementById('card_max').value = '';
    }
    
    document.getElementById('card_1x').value = globalFees.card['1x'].toFixed(2);
    document.getElementById('card_2x').value = globalFees.card['2x'].toFixed(2);
    document.getElementById('card_3x').value = globalFees.card['3x'].toFixed(2);
    document.getElementById('card_4x').value = globalFees.card['4x'].toFixed(2);
    document.getElementById('card_5x').value = globalFees.card['5x'].toFixed(2);
    document.getElementById('card_6x').value = globalFees.card['6x'].toFixed(2);
    
    // Inicializar taxas de saque como global
    document.getElementById('withdrawal_fee_type').value = 'global';
    toggleWithdrawalFeeInputs();
    document.getElementById('withdrawal_fixed_fee').value = '';
    document.getElementById('withdrawal_percentage_fee').value = '';
    
    // Verificar se o usuário já tem taxas personalizadas
    fetch(`/admin/users/${currentUserId}/fees`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.fees) {
            // Preencher com as taxas personalizadas do usuário
            document.getElementById('pix_fixed').value = data.fees.pix.fixed.toFixed(2);
            document.getElementById('pix_variable').value = data.fees.pix.percentage.toFixed(2);
            if (data.fees.pix.max !== null) {
                document.getElementById('pix_max').value = data.fees.pix.max.toFixed(2);
            } else {
                document.getElementById('pix_max').value = '';
            }
            
            document.getElementById('boleto_fixed').value = data.fees.bank_slip.fixed.toFixed(2);
            document.getElementById('boleto_variable').value = data.fees.bank_slip.percentage.toFixed(2);
            if (data.fees.bank_slip.max !== null) {
                document.getElementById('boleto_max').value = data.fees.bank_slip.max.toFixed(2);
            } else {
                document.getElementById('boleto_max').value = '';
            }
            
            document.getElementById('card_fixed').value = data.fees.credit_card.fixed.toFixed(2);
            if (data.fees.credit_card.max !== null) {
                document.getElementById('card_max').value = data.fees.credit_card.max.toFixed(2);
            } else {
                document.getElementById('card_max').value = '';
            }
            
            document.getElementById('card_1x').value = data.fees.credit_card.percentage.toFixed(2);
            
            // Preencher parcelas se disponíveis
            if (data.fees.credit_card.installments) {
                const installments = data.fees.credit_card.installments;
                if (installments['2x']) document.getElementById('card_2x').value = installments['2x'].toFixed(2);
                if (installments['3x']) document.getElementById('card_3x').value = installments['3x'].toFixed(2);
                if (installments['4x']) document.getElementById('card_4x').value = installments['4x'].toFixed(2);
                if (installments['5x']) document.getElementById('card_5x').value = installments['5x'].toFixed(2);
                if (installments['6x']) document.getElementById('card_6x').value = installments['6x'].toFixed(2);
            }
            
            // Preencher taxas de saque se disponíveis
            if (data.withdrawal_fees) {
                const withdrawalFees = data.withdrawal_fees;
                const feeType = withdrawalFees.fee_type || 'global';
                document.getElementById('withdrawal_fee_type').value = feeType;
                toggleWithdrawalFeeInputs();
                
                if (withdrawalFees.fixed_fee !== null && withdrawalFees.fixed_fee !== undefined) {
                    document.getElementById('withdrawal_fixed_fee').value = parseFloat(withdrawalFees.fixed_fee).toFixed(2);
                } else {
                    document.getElementById('withdrawal_fixed_fee').value = '';
                }
                
                if (withdrawalFees.percentage_fee !== null && withdrawalFees.percentage_fee !== undefined) {
                    document.getElementById('withdrawal_percentage_fee').value = parseFloat(withdrawalFees.percentage_fee).toFixed(2);
                } else {
                    document.getElementById('withdrawal_percentage_fee').value = '';
                }
            }
        }
    })
    .catch(error => {
        console.error('Error fetching user fees:', error);
    });
    
    document.getElementById('feesModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function toggleWithdrawalFeeInputs() {
    const feeType = document.getElementById('withdrawal_fee_type').value;
    const inputsContainer = document.getElementById('withdrawalFeeInputs');
    const fixedContainer = document.getElementById('withdrawalFixedContainer');
    const percentageContainer = document.getElementById('withdrawalPercentageContainer');
    
    if (feeType === 'global') {
        inputsContainer.style.display = 'none';
    } else {
        inputsContainer.style.display = 'grid';
        
        if (feeType === 'fixed') {
            fixedContainer.style.display = 'block';
            percentageContainer.style.display = 'none';
        } else if (feeType === 'percentage') {
            fixedContainer.style.display = 'none';
            percentageContainer.style.display = 'block';
        } else if (feeType === 'both') {
            fixedContainer.style.display = 'block';
            percentageContainer.style.display = 'block';
        }
    }
}

function closeFeesModal() {
    document.getElementById('feesModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function saveFees() {
    // Get form data
    const formData = {
        pix_fixed: parseFloat(document.getElementById('pix_fixed').value),
        pix_variable: parseFloat(document.getElementById('pix_variable').value),
        pix_max: document.getElementById('pix_max').value ? parseFloat(document.getElementById('pix_max').value) : null,
        boleto_fixed: parseFloat(document.getElementById('boleto_fixed').value),
        boleto_variable: parseFloat(document.getElementById('boleto_variable').value),
        boleto_max: document.getElementById('boleto_max').value ? parseFloat(document.getElementById('boleto_max').value) : null,
        card_fixed: parseFloat(document.getElementById('card_fixed').value),
        card_max: document.getElementById('card_max').value ? parseFloat(document.getElementById('card_max').value) : null,
        card_1x: parseFloat(document.getElementById('card_1x').value),
        card_2x: parseFloat(document.getElementById('card_2x').value),
        card_3x: parseFloat(document.getElementById('card_3x').value),
        card_4x: parseFloat(document.getElementById('card_4x').value),
        card_5x: parseFloat(document.getElementById('card_5x').value),
        card_6x: parseFloat(document.getElementById('card_6x').value),
        withdrawal_fee_type: document.getElementById('withdrawal_fee_type').value,
        withdrawal_fixed_fee: document.getElementById('withdrawal_fixed_fee').value ? parseFloat(document.getElementById('withdrawal_fixed_fee').value) : null,
        withdrawal_percentage_fee: document.getElementById('withdrawal_percentage_fee').value ? parseFloat(document.getElementById('withdrawal_percentage_fee').value) : null,
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