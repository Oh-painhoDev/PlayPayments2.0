@extends('layouts.dashboard')

@section('title', 'Nova Venda')
@section('page-title', 'Nova Venda')
@section('page-description', 'Crie uma nova venda e gere o pagamento')

@section('content')
<div class="p-6 space-y-6">
    <!-- Success/Error Messages -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6" id="saleFormContainer">
            <form action="{{ route('transactions.store') }}" method="POST" id="saleForm">
                @csrf

                <!-- Sale Information -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Informações da Venda</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor da Venda *
                            </label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">R$</span>
                                <input 
                                    id="amount" 
                                    name="amount" 
                                    type="number" 
                                    step="0.01"
                                    min="0.01"
                                    max="999999.99"
                                    required 
                                    value="{{ old('amount') }}"
                                    class="w-full pl-12 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    placeholder="0,00"
                                >
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-2">
                                Método de Pagamento *
                            </label>
                            <select 
                                id="payment_method" 
                                name="payment_method" 
                                required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            >
                                <option value="">Selecione o método</option>
                                <option value="pix" {{ old('payment_method') == 'pix' ? 'selected' : '' }}>PIX</option>
                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                                <option value="bank_slip" {{ old('payment_method') == 'bank_slip' ? 'selected' : '' }}>Boleto Bancário</option>
                            </select>
                        </div>

                        <!-- Sale Name/Title -->
                        <div class="md:col-span-2">
                            <label for="sale_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome da Venda *
                            </label>
                            <input 
                                id="sale_name" 
                                name="sale_name" 
                                type="text" 
                                required
                                maxlength="255"
                                value="{{ old('sale_name', 'Venda') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="Nome/título da venda (aparece nos itens)"
                            >
                            <p class="mt-1 text-xs text-gray-500">
                                Nome ou título da venda que aparecerá nos itens da transação.
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição
                            </label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="3"
                                maxlength="500"
                                value="{{ old('description') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all resize-none"
                                placeholder="Descrição detalhada da venda (opcional)"
                            >{{ old('description') }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">
                                Descrição adicional da venda ou informações relevantes.
                            </p>
                        </div>

                        <!-- Installments (only for credit card) -->
                        <div id="installments-field" class="hidden">
                            <label for="installments" class="block text-sm font-medium text-gray-700 mb-2">
                                Parcelas
                            </label>
                            <select 
                                id="installments" 
                                name="installments"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            >
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ old('installments', 1) == $i ? 'selected' : '' }}>
                                        {{ $i }}x {{ $i == 1 ? 'à vista' : 'de R$ 0,00' }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <!-- PIX Expiration (only for PIX) -->
                        <div id="pix-expiration-field" class="hidden md:col-span-2">
                            <label for="pix_expires_in_days" class="block text-sm font-medium text-gray-700 mb-2">
                                Tempo de Expiração do PIX *
                            </label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Tipo</label>
                                    <select 
                                        id="pix_expiration_type" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    >
                                        <option value="minutes">Minutos/Horas</option>
                                        <option value="days">Dias</option>
                                    </select>
                                </div>
                                <div id="pix-minutes-field">
                                    <label for="pix_expires_in_minutes" class="block text-xs text-gray-600 mb-1">Tempo (minutos)</label>
                                    <select 
                                        id="pix_expires_in_minutes" 
                                        name="pix_expires_in_minutes"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                    >
                                        <option value="15" {{ old('pix_expires_in_minutes', 15) == 15 ? 'selected' : '' }}>15 minutos</option>
                                        <option value="30" {{ old('pix_expires_in_minutes', 15) == 30 ? 'selected' : '' }}>30 minutos</option>
                                        <option value="60" {{ old('pix_expires_in_minutes', 15) == 60 ? 'selected' : '' }}>1 hora</option>
                                        <option value="120" {{ old('pix_expires_in_minutes', 15) == 120 ? 'selected' : '' }}>2 horas</option>
                                        <option value="180" {{ old('pix_expires_in_minutes', 15) == 180 ? 'selected' : '' }}>3 horas</option>
                                        <option value="360" {{ old('pix_expires_in_minutes', 15) == 360 ? 'selected' : '' }}>6 horas</option>
                                        <option value="720" {{ old('pix_expires_in_minutes', 15) == 720 ? 'selected' : '' }}>12 horas</option>
                                        <option value="1440" {{ old('pix_expires_in_minutes', 15) == 1440 ? 'selected' : '' }}>1 dia (24 horas)</option>
                                    </select>
                                </div>
                                <div id="pix-days-field" class="hidden">
                                    <label for="pix_expires_in_days" class="block text-xs text-gray-600 mb-1">Tempo (dias)</label>
                                    <input 
                                        type="number" 
                                        id="pix_expires_in_days" 
                                        name="pix_expires_in_days"
                                        min="1"
                                        max="90"
                                        value="{{ old('pix_expires_in_days', 1) }}"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                        placeholder="1-90 dias"
                                    >
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                <strong>Para SharkBanking:</strong> Valores menores que 24 horas usam <code>expiresIn</code> (segundos). 
                                Valores de 1 dia ou mais usam <code>expiresInDays</code> (dias inteiros, de 1 a 90 dias).
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Dados do Cliente</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer Name -->
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nome Completo *
                            </label>
                            <input 
                                id="customer_name" 
                                name="customer_name" 
                                type="text" 
                                required 
                                maxlength="255"
                                value="{{ old('customer_name') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="Nome do cliente"
                            >
                        </div>

                        <!-- Customer Email -->
                        <div>
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-2">
                                E-mail *
                            </label>
                            <input 
                                id="customer_email" 
                                name="customer_email" 
                                type="email" 
                                required 
                                maxlength="255"
                                value="{{ old('customer_email') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="email@cliente.com"
                            >
                        </div>

                        <!-- Customer Document -->
                        <div>
                            <label for="customer_document" class="block text-sm font-medium text-gray-700 mb-2">
                                CPF/CNPJ *
                            </label>
                            <input 
                                id="customer_document" 
                                name="customer_document" 
                                type="text" 
                                required 
                                value="{{ old('customer_document') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="000.000.000-00"
                            >
                        </div>

                        <!-- Customer Phone -->
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Telefone
                            </label>
                            <input 
                                id="customer_phone" 
                                name="customer_phone" 
                                type="text" 
                                value="{{ old('customer_phone') }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                                placeholder="(11) 99999-9999"
                            >
                        </div>
                    </div>
                </div>

                <!-- Advanced Options -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900">Opções Avançadas</h2>
                    </div>
                    
                    <div>
                        <label for="redirect_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL de Redirecionamento
                        </label>
                        <input 
                            id="redirect_url" 
                            name="redirect_url" 
                            type="url" 
                            value="{{ old('redirect_url') }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            placeholder="https://seusite.com/obrigado"
                        >
                        <p class="text-xs text-gray-500 mt-2">URL para onde o cliente será redirecionado após o pagamento (opcional)</p>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('transactions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-200 border border-gray-300">
                        Cancelar
                    </a>
                    <button 
                        type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 flex items-center"
                        id="submitBtn"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Criar Venda
                    </button>
                </div>
            </form>
        </div>

        <!-- Payment Result (Hidden initially) -->
        <div class="lg:col-span-2 space-y-6 hidden" id="paymentResult">
            <!-- Success Message -->
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="font-semibold mb-1">Venda criada com sucesso!</h4>
                        <p class="text-sm">Compartilhe as informações de pagamento com o cliente.</p>
                    </div>
                </div>
            </div>

            <!-- Transaction Details -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes da Venda</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">Valor:</span>
                        <span class="text-gray-900 font-bold text-xl" id="resultAmount">R$ 0,00</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">Método:</span>
                        <span class="text-gray-900 font-semibold" id="resultMethod">-</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">ID da Transação:</span>
                        <span class="text-gray-900 font-mono text-sm" id="resultTransactionId">-</span>
                    </div>
                </div>
                
                <div class="mt-6">
                    <a href="#" id="resultDetailsUrl" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200">
                        Ver Detalhes Completos
                    </a>
                </div>
            </div>

            <!-- PIX Payment (Hidden initially) -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm hidden" id="pixPayment">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Pagamento PIX</h3>
                
                <!-- QR Code -->
                <div id="pixQrCode" class="hidden mb-6">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-300 text-center">
                        <div class="text-sm font-medium text-gray-900 mb-3">QR Code PIX</div>
                        <div class="flex justify-center">
                            <img id="qrCodeImage" src="" alt="QR Code PIX" class="w-64 h-64 bg-white p-2 rounded-lg border border-gray-200">
                        </div>
                        <p class="text-xs text-gray-600 mt-3">Escaneie este QR Code com o app do seu banco</p>
                    </div>
                </div>
                
                <!-- Copy/Paste Code -->
                <div id="pixCopyPaste" class="hidden">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-300">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-900">Código Copia e Cola</span>
                            <button 
                                onclick="copyPixCode()"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs transition-colors"
                            >
                                Copiar
                            </button>
                        </div>
                        <div class="mt-2 p-3 bg-white border border-gray-200 rounded font-mono text-xs break-all" id="pixPayload">
                            -
                        </div>
                        <p class="text-xs text-gray-600 mt-2">Ou copie e cole este código no app do seu banco</p>
                    </div>
                </div>
            </div>

            <!-- New Sale Button -->
            <div class="flex justify-center">
                <button 
                    onclick="location.reload()" 
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow-md"
                >
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Criar Nova Venda
                </button>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Fee Calculator -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Calculadora de Taxas</h3>
                </div>
                
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">Valor da venda:</span>
                        <span class="text-gray-900 font-semibold" id="calc-amount">R$ 0,00</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 text-sm">Taxa estimada:</span>
                        <span class="text-red-600 font-semibold" id="calc-fee">R$ 0,00</span>
                    </div>
                    
                    <div class="flex justify-between items-center border-t border-gray-200 pt-4">
                        <span class="text-gray-700 font-medium">Você recebe:</span>
                        <span class="text-green-600 font-bold text-xl" id="calc-net">R$ 0,00</span>
                    </div>
                </div>
                
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-blue-800 text-xs">
                            <strong>Dica:</strong> As taxas variam por método de pagamento. PIX tem as menores taxas.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payment Methods Info -->
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Métodos Disponíveis</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-gray-900 font-medium text-sm">PIX</span>
                        </div>
                        <span class="text-green-700 text-xs font-medium">Instantâneo</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                            <span class="text-gray-900 font-medium text-sm">Cartão de Crédito</span>
                        </div>
                        <span class="text-blue-700 text-xs font-medium">Até 12x</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-orange-50 border border-orange-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-orange-500 rounded-full mr-3"></div>
                            <span class="text-gray-900 font-medium text-sm">Boleto</span>
                        </div>
                        <span class="text-orange-700 text-xs font-medium">3 dias úteis</span>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-200 p-6">
                <div class="flex items-start">
                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center mr-3 flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 mb-2">Dicas Rápidas</h4>
                        <ul class="space-y-2 text-sm text-gray-700">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>PIX possui aprovação instantânea</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Cartão aceita parcelamento até 12x</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                <span>Boleto compensa em até 3 dias úteis</span>
                            </li>
                        </ul>
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
    // Apply masks
    $('#customer_document').mask('000.000.000-00');
    $('#customer_phone').mask('(00) 00000-0000');
    
    // Fee calculation data (simplified)
    const fees = {
        pix: { percentage: 1.99, fixed: 0.00 },
        credit_card: { percentage: 3.99, fixed: 0.39 },
        bank_slip: { percentage: 2.49, fixed: 2.00 }
    };
    
    // Update fee calculation
    function updateFeeCalculation() {
        const amount = parseFloat($('#amount').val()) || 0;
        const method = $('#payment_method').val();
        
        $('#calc-amount').text('R$ ' + amount.toFixed(2).replace('.', ','));
        
        if (amount > 0 && method && fees[method]) {
            const fee = fees[method];
            const feeAmount = (amount * fee.percentage / 100) + fee.fixed;
            const netAmount = amount - feeAmount;
            
            $('#calc-fee').text('R$ ' + feeAmount.toFixed(2).replace('.', ','));
            $('#calc-net').text('R$ ' + netAmount.toFixed(2).replace('.', ','));
        } else {
            $('#calc-fee').text('R$ 0,00');
            $('#calc-net').text('R$ 0,00');
        }
    }
    
    // Update installment options
    function updateInstallments() {
        const amount = parseFloat($('#amount').val()) || 0;
        const method = $('#payment_method').val();
        
        if (method === 'credit_card') {
            $('#installments-field').removeClass('hidden');
            
            // Update installment values
            for (let i = 1; i <= 12; i++) {
                const installmentValue = amount / i;
                const text = i === 1 ? 'à vista' : `de R$ ${installmentValue.toFixed(2).replace('.', ',')}`;
                $(`#installments option[value="${i}"]`).text(`${i}x ${text}`);
            }
        } else {
            $('#installments-field').addClass('hidden');
        }
    }
    
    // Update PIX expiration field visibility
    function updatePixExpiration() {
        const method = $('#payment_method').val();
        
        if (method === 'pix') {
            $('#pix-expiration-field').removeClass('hidden');
        } else {
            $('#pix-expiration-field').addClass('hidden');
        }
    }
    
    // Toggle between minutes and days for PIX expiration
    $('#pix_expiration_type').on('change', function() {
        const type = $(this).val();
        
        if (type === 'days') {
            $('#pix-minutes-field').addClass('hidden');
            $('#pix-days-field').removeClass('hidden');
            $('#pix_expires_in_minutes').removeAttr('required');
            $('#pix_expires_in_days').attr('required', 'required');
        } else {
            $('#pix-minutes-field').removeClass('hidden');
            $('#pix-days-field').addClass('hidden');
            $('#pix_expires_in_days').removeAttr('required');
            $('#pix_expires_in_minutes').attr('required', 'required');
        }
    });
    
    // Event listeners
    $('#amount, #payment_method').on('input change', function() {
        updateFeeCalculation();
        updateInstallments();
        updatePixExpiration();
    });
    
    // Document type detection
    $('#customer_document').on('input', function() {
        const value = $(this).val().replace(/\D/g, '');
        
        if (value.length <= 11) {
            // CPF
            $(this).mask('000.000.000-00');
        } else {
            // CNPJ
            $(this).mask('00.000.000/0000-00');
        }
    });
    
    // Form submission via AJAX
    $('#saleForm').on('submit', function(e) {
        e.preventDefault();
        
        // Convert days to minutes if days field is visible and filled
        const expirationType = $('#pix_expiration_type').val();
        if (expirationType === 'days' && $('#pix-days-field').is(':visible')) {
            const days = parseInt($('#pix_expires_in_days').val()) || 1;
            const minutes = days * 1440; // Convert days to minutes
            $('#pix_expires_in_minutes').val(minutes);
            $('#pix_expires_in_days').removeAttr('name'); // Remove name to not send it
        } else {
            $('#pix_expires_in_days').removeAttr('name');
        }
        
        $('#submitBtn').prop('disabled', true).html(`
            <svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Criando...
        `);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    const transaction = response.transaction;
                    
                    // Hide form and show payment details
                    $('#saleFormContainer').hide();
                    $('#paymentResult').show();
                    
                    // Display transaction info
                    $('#resultAmount').text(transaction.formatted_amount);
                    $('#resultMethod').text(transaction.payment_method.toUpperCase());
                    $('#resultTransactionId').text(transaction.transaction_id);
                    $('#resultDetailsUrl').attr('href', transaction.details_url);
                    
                    // Show PIX payment if applicable
                    if (transaction.payment_method === 'pix' && transaction.payment_data?.pix) {
                        $('#pixPayment').show();
                        
                        // Show QR Code if available
                        if (transaction.payment_data.pix.encodedImage) {
                            $('#pixQrCode').show();
                            $('#qrCodeImage').attr('src', transaction.payment_data.pix.encodedImage);
                        }
                        
                        // Show copy/paste code
                        if (transaction.payment_data.pix.payload) {
                            $('#pixCopyPaste').show();
                            $('#pixPayload').text(transaction.payment_data.pix.payload);
                        }
                    }
                    
                    // Scroll to result
                    $('html, body').animate({
                        scrollTop: $('#paymentResult').offset().top - 100
                    }, 500);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Erro ao criar venda. Tente novamente.';
                
                if (xhr.responseJSON?.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON?.error) {
                    errorMessage = xhr.responseJSON.error;
                }
                
                alert(errorMessage);
                
                $('#submitBtn').prop('disabled', false).html(`
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Gerar Venda
                `);
            }
        });
    });
    
    // Copy PIX code function
    window.copyPixCode = function() {
        const pixCode = $('#pixPayload').text();
        
        navigator.clipboard.writeText(pixCode).then(function() {
            alert('Código PIX copiado com sucesso!');
        }).catch(function(err) {
            console.error('Erro ao copiar: ', err);
            
            // Fallback method
            const tempInput = document.createElement('textarea');
            tempInput.value = pixCode;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('Código PIX copiado com sucesso!');
        });
    };
    
    // Initial calculation
    updateFeeCalculation();
    updateInstallments();
    updatePixExpiration();
});
</script>
@endpush
@endsection
