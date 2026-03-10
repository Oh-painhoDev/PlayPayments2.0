@extends('layouts.admin')

@section('title', 'Editar Taxas de Saque - ' . $user->name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Configurar Taxas de Saque</h1>
        </div>
        <p class="text-gray-600">Usuário: <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p class="font-medium">Erro ao salvar:</p>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.users.withdrawal-fees.update', $user) }}" method="POST">
            @csrf
            
            <!-- Fee Type -->
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-3">Tipo de Taxa de Saque</label>
                
                <div class="space-y-3">
                    <!-- Global -->
                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'global' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input 
                            type="radio" 
                            name="withdrawal_fee_type" 
                            value="global" 
                            {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'global' ? 'checked' : '' }}
                            class="mt-1 mr-3"
                            onchange="updateFeeFields()"
                        >
                        <div>
                            <div class="font-medium text-gray-900">Taxa Global</div>
                            <div class="text-sm text-gray-600">Usar a taxa global do sistema</div>
                        </div>
                    </label>

                    <!-- Fixed -->
                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'fixed' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input 
                            type="radio" 
                            name="withdrawal_fee_type" 
                            value="fixed" 
                            {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'fixed' ? 'checked' : '' }}
                            class="mt-1 mr-3"
                            onchange="updateFeeFields()"
                        >
                        <div>
                            <div class="font-medium text-gray-900">Taxa Fixa (R$)</div>
                            <div class="text-sm text-gray-600">Cobrar um valor fixo em reais</div>
                        </div>
                    </label>

                    <!-- Percentage -->
                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'percentage' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input 
                            type="radio" 
                            name="withdrawal_fee_type" 
                            value="percentage" 
                            {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'percentage' ? 'checked' : '' }}
                            class="mt-1 mr-3"
                            onchange="updateFeeFields()"
                        >
                        <div>
                            <div class="font-medium text-gray-900">Taxa Percentual (%)</div>
                            <div class="text-sm text-gray-600">Cobrar uma porcentagem do valor do saque</div>
                        </div>
                    </label>

                    <!-- Both -->
                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:bg-gray-50 {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'both' ? 'border-blue-500 bg-blue-50' : 'border-gray-200' }}">
                        <input 
                            type="radio" 
                            name="withdrawal_fee_type" 
                            value="both" 
                            {{ old('withdrawal_fee_type', $user->withdrawal_fee_type) === 'both' ? 'checked' : '' }}
                            class="mt-1 mr-3"
                            onchange="updateFeeFields()"
                        >
                        <div>
                            <div class="font-medium text-gray-900">Taxa Fixa + Percentual</div>
                            <div class="text-sm text-gray-600">Combinar valor fixo e percentual</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Fixed Fee Input -->
            <div id="fixedFeeInput" class="mb-6" style="display: none;">
                <label for="withdrawal_fee_fixed" class="block text-gray-700 font-semibold mb-2">Taxa Fixa (R$)</label>
                <input 
                    type="number" 
                    name="withdrawal_fee_fixed" 
                    id="withdrawal_fee_fixed"
                    value="{{ old('withdrawal_fee_fixed', $user->withdrawal_fee_fixed) }}"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="0.00"
                >
                <p class="mt-1 text-sm text-gray-500">Exemplo: R$ 5,00</p>
            </div>

            <!-- Percentage Fee Input -->
            <div id="percentageFeeInput" class="mb-6" style="display: none;">
                <label for="withdrawal_fee_percentage" class="block text-gray-700 font-semibold mb-2">Taxa Percentual (%)</label>
                <input 
                    type="number" 
                    name="withdrawal_fee_percentage" 
                    id="withdrawal_fee_percentage"
                    value="{{ old('withdrawal_fee_percentage', $user->withdrawal_fee_percentage) }}"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="0.00"
                >
                <p class="mt-1 text-sm text-gray-500">Exemplo: 2,50%</p>
            </div>

            <!-- BaaS Fee Info -->
            @php
                $baasGateway = null;
                if ($user->assigned_baas_id) {
                    $baasGateway = \App\Models\BaasCredential::find($user->assigned_baas_id);
                }
                if (!$baasGateway) {
                    $baasGateway = \App\Models\BaasCredential::where('is_default', true)->where('is_active', true)->first();
                }
                if (!$baasGateway) {
                    $baasGateway = \App\Models\BaasCredential::where('is_active', true)->first();
                }
            @endphp
            
            @if($baasGateway && $baasGateway->withdrawal_fee > 0)
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Taxa do BaaS ({{ ucfirst($baasGateway->gateway) }}):</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p><strong>R$ {{ number_format($baasGateway->withdrawal_fee, 2, ',', '.') }}</strong> por saque</p>
                            <p class="mt-1 text-xs">Esta taxa será adicionada à taxa do usuário configurada acima.</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Important Info -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Como funciona a taxa de saque:</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <ul class="list-disc list-inside space-y-1">
                                <li>A taxa do usuário + taxa do BaaS são <strong>descontadas do saldo da carteira</strong></li>
                                <li>O valor <strong>integral solicitado</strong> é enviado ao usuário</li>
                                @if($baasGateway && $baasGateway->withdrawal_fee > 0)
                                <li>Exemplo: Saque de R$ 30 com taxa do usuário R$ 0 + taxa do BaaS R$ {{ number_format($baasGateway->withdrawal_fee, 2, ',', '.') }} = Debita R$ {{ number_format(30 + $baasGateway->withdrawal_fee, 2, ',', '.') }} da carteira, envia R$ 30 ao usuário</li>
                                @else
                                <li>Exemplo: Saque de R$ 100 com taxa de R$ 5 = Debita R$ 105 da carteira, envia R$ 100 ao usuário</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Salvar Taxas
                </button>
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function updateFeeFields() {
    const feeType = document.querySelector('input[name="withdrawal_fee_type"]:checked').value;
    const fixedInput = document.getElementById('fixedFeeInput');
    const percentageInput = document.getElementById('percentageFeeInput');
    
    // Hide all inputs first
    fixedInput.style.display = 'none';
    percentageInput.style.display = 'none';
    
    // Show relevant inputs
    if (feeType === 'fixed' || feeType === 'both') {
        fixedInput.style.display = 'block';
    }
    
    if (feeType === 'percentage' || feeType === 'both') {
        percentageInput.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateFeeFields();
});
</script>
@endsection
