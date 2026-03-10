@extends('layouts.dashboard')

@section('title', 'Solicitar Saque')
@section('page-title', 'Solicitar Saque')
@section('page-description', 'Transfira seu saldo para sua conta bancária via PIX')

@section('content')
<div class="p-6 space-y-6">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-r-lg flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold mb-2">Erros encontrados:</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Solicitar Novo Saque</h1>
                <p class="text-green-100">Transfira seu saldo disponível via PIX</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-green-100 mb-1">Saldo Disponível</p>
                <p class="text-3xl font-bold">{{ $user->formatted_wallet_balance }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('wallet.store') }}" method="POST" id="withdrawalForm">
                @csrf

                <!-- Informações do Saque -->
                <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <span class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </span>
                            Informações do Saque
                        </h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Valor -->
                        <div>
                            <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                                Valor do Saque *
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-600 font-semibold">R$</span>
                                <input 
                                    id="amount" 
                                    name="amount" 
                                    type="number" 
                                    step="0.01"
                                    min="{{ $fee + 0.01 }}"
                                    max="{{ $user->wallet_balance }}"
                                    required 
                                    value="{{ old('amount', $user->wallet_balance) }}"
                                    class="w-full pl-12 pr-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    placeholder="0,00"
                                >
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Valor mínimo: R$ {{ number_format($fee + 0.01, 2, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dados PIX -->
                <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm">
                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-4">
                        <h2 class="text-lg font-bold text-white flex items-center">
                            <span class="w-8 h-8 bg-white/20 backdrop-blur-sm rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </span>
                            Dados PIX
                        </h2>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- Tipo PIX -->
                        <div>
                            <label for="pix_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipo de Chave PIX *
                            </label>
                            <select 
                                id="pix_type" 
                                name="pix_type" 
                                required
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            >
                                <option value="">Selecione o tipo de chave</option>
                                <option value="cpf" {{ old('pix_type') == 'cpf' ? 'selected' : '' }}>CPF</option>
                                <option value="email" {{ old('pix_type') == 'email' ? 'selected' : '' }}>E-mail</option>
                                <option value="phone" {{ old('pix_type') == 'phone' ? 'selected' : '' }}>Telefone</option>
                                <option value="random" {{ old('pix_type') == 'random' ? 'selected' : '' }}>Chave Aleatória</option>
                            </select>
                        </div>

                        <!-- Chave PIX -->
                        <div>
                            <label for="pix_key" class="block text-sm font-semibold text-gray-700 mb-2">
                                Chave PIX *
                            </label>
                            <input 
                                id="pix_key" 
                                name="pix_key" 
                                type="text" 
                                required 
                                value="{{ old('pix_key') }}"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="Digite sua chave PIX"
                            >
                            <p class="text-xs text-gray-500 mt-2" id="pix_key_help">Digite a chave PIX conforme o tipo selecionado</p>
                        </div>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('wallet.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold transition-all">
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-10 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center"
                        id="submitBtn"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                        </svg>
                        Solicitar Saque
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Resumo do Saque -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </span>
                    Calculadora
                </h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-600 text-sm font-medium">Saldo disponível:</span>
                        <span class="text-gray-900 font-bold">{{ $user->formatted_wallet_balance }}</span>
                    </div>
                    
                    <div class="flex justify-between p-4 bg-gradient-to-r from-emerald-500 to-green-500 rounded-xl border-2 border-emerald-400">
                        <span class="text-white text-sm font-semibold">Valor que você receberá:</span>
                        <span class="text-white font-bold text-xl" id="summary_amount">R$ 0,00</span>
                    </div>
                    
                    <div class="flex justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-600 text-sm font-medium">+ Taxa de saque:</span>
                        <span class="text-orange-600 font-bold" id="summary_fee">R$ {{ number_format($fee, 2, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between p-3 bg-red-50 rounded-xl border border-red-200">
                        <span class="text-red-700 text-sm font-semibold">= Total debitado da conta:</span>
                        <span class="text-red-700 font-bold" id="summary_total_debit">R$ 0,00</span>
                    </div>
                </div>
            </div>

            <!-- Tipo de Saque -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </span>
                    Tipo de Saque
                </h3>
                
                <div class="bg-white rounded-xl p-4">
                    <div class="flex items-center mb-3">
                        <div class="w-3 h-3 {{ $user->withdrawal_type === 'automatic' ? 'bg-emerald-500' : 'bg-emerald-500' }} rounded-full mr-3 animate-pulse"></div>
                        <span class="text-gray-900 font-bold">{{ $user->withdrawal_type === 'automatic' ? 'Automático' : 'Manual' }}</span>
                    </div>
                    
                    <p class="text-gray-600 text-sm">
                        @if($user->withdrawal_type === 'automatic')
                            Seus saques são processados automaticamente sem necessidade de aprovação.
                        @else
                            Seus saques precisam de aprovação manual do administrador.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Informações Importantes -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Importante
                </h3>
                
                <div class="space-y-3">
                    <div class="bg-white rounded-xl p-3 flex items-start">
                        <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <p class="text-gray-700 text-sm">
                            Certifique-se de que a chave PIX está correta.
                        </p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-3 flex items-start">
                        <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-700 text-sm">
                            @if($feeData['type'] === 'percentage')
                                A taxa de saque é de {{ $feeData['percentage_fee'] }}% do valor solicitado.
                            @elseif($feeData['type'] === 'fixed')
                                A taxa de saque é fixa de R$ {{ number_format($feeData['fixed_fee'], 2, ',', '.') }} por operação.
                            @elseif($feeData['type'] === 'both')
                                A taxa de saque é de R$ {{ number_format($feeData['fixed_fee'], 2, ',', '.') }} + {{ $feeData['percentage_fee'] }}% do valor.
                            @else
                                A taxa de saque é de R$ {{ number_format($fee, 2, ',', '.') }} por operação.
                            @endif
                        </p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-3 flex items-start">
                        <svg class="w-6 h-6 mr-3 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-gray-700 text-sm">
                            Saques são processados em até 24h úteis.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
    const feeData = @json($feeData);
    const maxAmount = {{ $user->wallet_balance }};
    
    // Atualizar resumo quando o valor mudar
    $('#amount').on('input', function() {
        updateSummary();
    });
    
    updateSummary();
    
    // Atualizar máscaras quando o tipo de PIX mudar
    $('#pix_type').on('change', function() {
        updatePixKeyMask();
    });
    
    updatePixKeyMask();
    
    function calculateFee(amount) {
        let totalFee = 0;
        
        if (feeData.type === 'fixed') {
            totalFee = feeData.fixed_fee || 0;
        } else if (feeData.type === 'percentage') {
            totalFee = (amount * (feeData.percentage_fee || 0)) / 100;
        } else if (feeData.type === 'both') {
            totalFee = (feeData.fixed_fee || 0) + ((amount * (feeData.percentage_fee || 0)) / 100);
        } else {
            // Global fee
            totalFee = feeData.fixed_fee || 0;
        }
        
        return totalFee;
    }
    
    function updateSummary() {
        const amount = parseFloat($('#amount').val()) || 0;
        const fee = calculateFee(amount);
        const totalToDebit = amount + fee; // Total debitado da carteira = valor + taxa
        
        // Valor que a pessoa VAI RECEBER (integral)
        $('#summary_amount').text('R$ ' + amount.toFixed(2).replace('.', ','));
        
        // Taxa que será cobrada
        $('#summary_fee').text('R$ ' + fee.toFixed(2).replace('.', ','));
        
        // Total que será DEBITADO da conta (valor + taxa)
        $('#summary_total_debit').text('R$ ' + totalToDebit.toFixed(2).replace('.', ','));
    }
    
    function updatePixKeyMask() {
        const pixType = $('#pix_type').val();
        const pixKeyInput = $('#pix_key');
        const pixKeyHelp = $('#pix_key_help');
        
        pixKeyInput.unmask();
        
        switch(pixType) {
            case 'cpf':
                pixKeyInput.mask('000.000.000-00');
                pixKeyHelp.html('Digite o CPF (apenas números)');
                break;
            case 'phone':
                pixKeyInput.mask('+55 (00) 00000-0000');
                pixKeyHelp.html('Digite o telefone com DDD');
                break;
            case 'email':
                pixKeyHelp.html('Digite o email completo');
                break;
            case 'random':
                pixKeyHelp.html('Digite a chave aleatória completa');
                break;
            default:
                pixKeyHelp.html('Selecione o tipo de chave PIX primeiro');
        }
    }
    
    $('#withdrawalForm').on('submit', function(e) {
        const amount = parseFloat($('#amount').val()) || 0;
        const fee = calculateFee(amount);
        const pixType = $('#pix_type').val();
        const pixKey = $('#pix_key').val();
        
        if (amount <= fee) {
            e.preventDefault();
            alert('O valor deve ser maior que a taxa de saque (R$ ' + fee.toFixed(2).replace('.', ',') + ')');
            return false;
        }
        
        if (amount > maxAmount) {
            e.preventDefault();
            alert('O valor não pode ser maior que seu saldo disponível');
            return false;
        }
        
        if (!pixType) {
            e.preventDefault();
            alert('Selecione o tipo de chave PIX');
            return false;
        }
        
        if (!pixKey) {
            e.preventDefault();
            alert('Digite a chave PIX');
            return false;
        }
        
        if (pixType === 'email' && !validateEmail(pixKey)) {
            e.preventDefault();
            alert('Digite um email válido');
            return false;
        }
        
        if (pixType === 'cpf' && !validateCPF(pixKey.replace(/\D/g, ''))) {
            e.preventDefault();
            alert('Digite um CPF válido');
            return false;
        }
        
        $('#submitBtn').prop('disabled', true).html(`
            <svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Processando...
        `);
        
        return true;
    });
    
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    function validateCPF(cpf) {
        if (cpf.length !== 11) return false;
        if (/^(\d)\1+$/.test(cpf)) return false;
        
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let remainder = 11 - (sum % 11);
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(9))) return false;
        
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        remainder = 11 - (sum % 11);
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(10))) return false;
        
        return true;
    }
});
</script>
@endpush
@endsection
