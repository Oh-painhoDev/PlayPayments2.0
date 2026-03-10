@extends('layouts.admin')

@section('title', 'Taxas Globais')
@section('page-title', 'Taxas Globais do White Label')
@section('page-description', 'Configure as taxas padrão que serão aplicadas a todos os usuários')

@section('content')
<div class="p-6">
    <!-- Success/Error Messages -->
    <div id="alert-container"></div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Configuração de Taxas Globais</h2>
                
                <form id="globalFeesForm" class="space-y-6">
                    @csrf
                    
                    <!-- PIX Fees -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-4 flex items-center">
                            <div class="p-2 bg-green-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            Taxas do PIX
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="pix_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Percentual (%)
                                </label>
                                <input 
                                    id="pix_percentage" 
                                    name="pix_percentage" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="pix_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Fixa (R$)
                                </label>
                                <input 
                                    id="pix_fixed" 
                                    name="pix_fixed" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="pix_min" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Mínimo (R$)
                                </label>
                                <input 
                                    id="pix_min" 
                                    name="pix_min" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>

                            <div>
                                <label for="pix_max" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Máximo (R$)
                                </label>
                                <input 
                                    id="pix_max" 
                                    name="pix_max" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Opcional"
                                >
                                <p class="text-xs text-gray-500 mt-1">Deixe em branco para não definir um valor máximo</p>
                            </div>
                        </div>

                        <!-- Transaction Limits for PIX -->
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Limites de Transação PIX</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="pix_min_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Mínimo de Transação (R$)
                                    </label>
                                    <input 
                                        id="pix_min_transaction" 
                                        name="pix_min_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor mínimo que pode ser cobrado em uma transação</p>
                                </div>
                                
                                <div>
                                    <label for="pix_max_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Máximo de Transação (R$)
                                    </label>
                                    <input 
                                        id="pix_max_transaction" 
                                        name="pix_max_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor máximo que pode ser cobrado em uma transação</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credit Card Fees -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-4 flex items-center">
                            <div class="p-2 bg-blue-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            Taxas do Cartão de Crédito
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="card_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Percentual (%)
                                </label>
                                <input 
                                    id="card_percentage" 
                                    name="card_percentage" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="card_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Fixa (R$)
                                </label>
                                <input 
                                    id="card_fixed" 
                                    name="card_fixed" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="card_min" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Mínimo (R$)
                                </label>
                                <input 
                                    id="card_min" 
                                    name="card_min" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>

                            <div>
                                <label for="card_max" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Máximo (R$)
                                </label>
                                <input 
                                    id="card_max" 
                                    name="card_max" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Opcional"
                                >
                                <p class="text-xs text-gray-500 mt-1">Deixe em branco para não definir um valor máximo</p>
                            </div>
                        </div>

                        <!-- Transaction Limits for Credit Card -->
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Limites de Transação Cartão</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="card_min_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Mínimo de Transação (R$)
                                    </label>
                                    <input 
                                        id="card_min_transaction" 
                                        name="card_min_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor mínimo que pode ser cobrado em uma transação</p>
                                </div>
                                
                                <div>
                                    <label for="card_max_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Máximo de Transação (R$)
                                    </label>
                                    <input 
                                        id="card_max_transaction" 
                                        name="card_max_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor máximo que pode ser cobrado em uma transação</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Taxas por Parcela</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="card_2x" class="block text-xs font-medium text-gray-600 mb-2">
                                        2x
                                    </label>
                                    <input 
                                        id="card_2x" 
                                        name="card_2x" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        required 
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                </div>
                                
                                <div>
                                    <label for="card_3x" class="block text-xs font-medium text-gray-600 mb-2">
                                        3x
                                    </label>
                                    <input 
                                        id="card_3x" 
                                        name="card_3x" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        required 
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                </div>
                                
                                <div>
                                    <label for="card_4x" class="block text-xs font-medium text-gray-600 mb-2">
                                        4x
                                    </label>
                                    <input 
                                        id="card_4x" 
                                        name="card_4x" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        required 
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                </div>
                                
                                <div>
                                    <label for="card_5x" class="block text-xs font-medium text-gray-600 mb-2">
                                        5x
                                    </label>
                                    <input 
                                        id="card_5x" 
                                        name="card_5x" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        required 
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                </div>
                                
                                <div>
                                    <label for="card_6x" class="block text-xs font-medium text-gray-600 mb-2">
                                        6x
                                    </label>
                                    <input 
                                        id="card_6x" 
                                        name="card_6x" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        required 
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                </div>
                                
                                <div>
                                    <label for="card_12x" class="block text-xs font-medium text-gray-600 mb-2">
                                        12x
                                    </label>
                                    <input 
                                        id="card_12x" 
                                        name="card_12x" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        required 
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Slip Fees -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-4 flex items-center">
                            <div class="p-2 bg-orange-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            Taxas do Boleto Bancário
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="boleto_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Percentual (%)
                                </label>
                                <input 
                                    id="boleto_percentage" 
                                    name="boleto_percentage" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="boleto_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Fixa (R$)
                                </label>
                                <input 
                                    id="boleto_fixed" 
                                    name="boleto_fixed" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="boleto_min" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Mínimo (R$)
                                </label>
                                <input 
                                    id="boleto_min" 
                                    name="boleto_min" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>

                            <div>
                                <label for="boleto_max" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Máximo (R$)
                                </label>
                                <input 
                                    id="boleto_max" 
                                    name="boleto_max" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Opcional"
                                >
                                <p class="text-xs text-gray-500 mt-1">Deixe em branco para não definir um valor máximo</p>
                            </div>
                        </div>

                        <!-- Transaction Limits for Boleto -->
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Limites de Transação Boleto</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="boleto_min_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Mínimo de Transação (R$)
                                    </label>
                                    <input 
                                        id="boleto_min_transaction" 
                                        name="boleto_min_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor mínimo que pode ser cobrado em uma transação</p>
                                </div>
                                
                                <div>
                                    <label for="boleto_max_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Máximo de Transação (R$)
                                    </label>
                                    <input 
                                        id="boleto_max_transaction" 
                                        name="boleto_max_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor máximo que pode ser cobrado em uma transação</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Withdrawal Fees -->
                    <div>
                        <h3 class="text-md font-medium text-gray-900 mb-4 flex items-center">
                            <div class="p-2 bg-purple-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            Taxas de Saque
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="withdrawal_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Percentual (%)
                                </label>
                                <input 
                                    id="withdrawal_percentage" 
                                    name="withdrawal_percentage" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="withdrawal_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                    Taxa Fixa (R$)
                                </label>
                                <input 
                                    id="withdrawal_fixed" 
                                    name="withdrawal_fixed" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>
                            
                            <div>
                                <label for="withdrawal_min" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Mínimo (R$)
                                </label>
                                <input 
                                    id="withdrawal_min" 
                                    name="withdrawal_min" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    required 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                            </div>

                            <div>
                                <label for="withdrawal_max" class="block text-sm font-medium text-gray-700 mb-2">
                                    Valor Máximo (R$)
                                </label>
                                <input 
                                    id="withdrawal_max" 
                                    name="withdrawal_max" 
                                    type="number" 
                                    step="0.01" 
                                    min="0" 
                                    class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Opcional"
                                >
                                <p class="text-xs text-gray-500 mt-1">Deixe em branco para não definir um valor máximo</p>
                            </div>
                        </div>

                        <!-- Transaction Limits for Withdrawal -->
                        <div class="mt-4 border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Limites de Transação Saque</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="withdrawal_min_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Mínimo de Transação (R$)
                                    </label>
                                    <input 
                                        id="withdrawal_min_transaction" 
                                        name="withdrawal_min_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor mínimo que pode ser sacado</p>
                                </div>
                                
                                <div>
                                    <label for="withdrawal_max_transaction" class="block text-sm font-medium text-gray-700 mb-2">
                                        Valor Máximo de Transação (R$)
                                    </label>
                                    <input 
                                        id="withdrawal_max_transaction" 
                                        name="withdrawal_max_transaction" 
                                        type="number" 
                                        step="0.01" 
                                        min="0" 
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        placeholder="Opcional"
                                    >
                                    <p class="text-xs text-gray-500 mt-1">Valor máximo que pode ser sacado</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button 
                            type="button" 
                            onclick="saveGlobalFees()"
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            Salvar Taxas Globais
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Current Fees -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Taxas Atuais</h3>
                
                <div class="space-y-4">
                    <!-- PIX -->
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-300">
                        <div class="flex items-center mb-2">
                            <div class="p-1.5 bg-green-500/10 rounded-lg mr-2">
                                <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">PIX</h4>
                        </div>
                        
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Percentual:</span>
                                <span class="text-gray-900" id="current_pix_percentage">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa Fixa:</span>
                                <span class="text-gray-900" id="current_pix_fixed">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mínimo:</span>
                                <span class="text-gray-900" id="current_pix_min">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Máximo:</span>
                                <span class="text-gray-900" id="current_pix_max">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Mín:</span>
                                <span class="text-gray-900" id="current_pix_min_transaction">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Máx:</span>
                                <span class="text-gray-900" id="current_pix_max_transaction">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Credit Card -->
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-300">
                        <div class="flex items-center mb-2">
                            <div class="p-1.5 bg-blue-500/10 rounded-lg mr-2">
                                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">Cartão de Crédito</h4>
                        </div>
                        
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Percentual:</span>
                                <span class="text-gray-900" id="current_card_percentage">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa Fixa:</span>
                                <span class="text-gray-900" id="current_card_fixed">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mínimo:</span>
                                <span class="text-gray-900" id="current_card_min">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Máximo:</span>
                                <span class="text-gray-900" id="current_card_max">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Mín:</span>
                                <span class="text-gray-900" id="current_card_min_transaction">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Máx:</span>
                                <span class="text-gray-900" id="current_card_max_transaction">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Slip -->
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-300">
                        <div class="flex items-center mb-2">
                            <div class="p-1.5 bg-orange-500/10 rounded-lg mr-2">
                                <svg class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">Boleto Bancário</h4>
                        </div>
                        
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Percentual:</span>
                                <span class="text-gray-900" id="current_boleto_percentage">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa Fixa:</span>
                                <span class="text-gray-900" id="current_boleto_fixed">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mínimo:</span>
                                <span class="text-gray-900" id="current_boleto_min">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Máximo:</span>
                                <span class="text-gray-900" id="current_boleto_max">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Mín:</span>
                                <span class="text-gray-900" id="current_boleto_min_transaction">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Máx:</span>
                                <span class="text-gray-900" id="current_boleto_max_transaction">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Withdrawal -->
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-300">
                        <div class="flex items-center mb-2">
                            <div class="p-1.5 bg-purple-500/10 rounded-lg mr-2">
                                <svg class="w-3 h-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            <h4 class="text-sm font-medium text-gray-900">Saque</h4>
                        </div>
                        
                        <div class="space-y-1 text-xs">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Percentual:</span>
                                <span class="text-gray-900" id="current_withdrawal_percentage">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Taxa Fixa:</span>
                                <span class="text-gray-900" id="current_withdrawal_fixed">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Mínimo:</span>
                                <span class="text-gray-900" id="current_withdrawal_min">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Máximo:</span>
                                <span class="text-gray-900" id="current_withdrawal_max">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Mín:</span>
                                <span class="text-gray-900" id="current_withdrawal_min_transaction">-</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transação Máx:</span>
                                <span class="text-gray-900" id="current_withdrawal_max_transaction">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações</h3>
                
                <div class="space-y-3 text-sm text-gray-700">
                    <p>
                        As taxas globais são aplicadas a todos os usuários que não possuem taxas personalizadas.
                    </p>
                    
                    <p>
                        Você pode definir taxas personalizadas para cada usuário individualmente na página de edição do usuário.
                    </p>
                    
                    <div class="p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg mt-4">
                        <p class="text-blue-700 text-xs">
                            <strong>Dica:</strong> Para maximizar a receita, considere ajustar as taxas com base no volume de transações.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carregar taxas atuais
    loadCurrentFees();
    
    // Inicializar campos com valores padrão
    initializeFields();
});

function loadCurrentFees() {
    // Fazer requisição para obter as taxas globais atuais
    fetch('/admin/white-label/global-fees/get', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Preencher os campos do formulário
            document.getElementById('pix_percentage').value = data.fees.pix.percentage_fee;
            document.getElementById('pix_fixed').value = data.fees.pix.fixed_fee;
            document.getElementById('pix_min').value = data.fees.pix.min_amount;
            if (data.fees.pix.max_amount) {
                document.getElementById('pix_max').value = data.fees.pix.max_amount;
            }
            if (data.fees.pix.min_transaction_value) {
                document.getElementById('pix_min_transaction').value = data.fees.pix.min_transaction_value;
            }
            if (data.fees.pix.max_transaction_value) {
                document.getElementById('pix_max_transaction').value = data.fees.pix.max_transaction_value;
            }
            
            document.getElementById('card_percentage').value = data.fees.credit_card.percentage_fee;
            document.getElementById('card_fixed').value = data.fees.credit_card.fixed_fee;
            document.getElementById('card_min').value = data.fees.credit_card.min_amount;
            if (data.fees.credit_card.max_amount) {
                document.getElementById('card_max').value = data.fees.credit_card.max_amount;
            }
            if (data.fees.credit_card.min_transaction_value) {
                document.getElementById('card_min_transaction').value = data.fees.credit_card.min_transaction_value;
            }
            if (data.fees.credit_card.max_transaction_value) {
                document.getElementById('card_max_transaction').value = data.fees.credit_card.max_transaction_value;
            }
            
            document.getElementById('boleto_percentage').value = data.fees.bank_slip.percentage_fee;
            document.getElementById('boleto_fixed').value = data.fees.bank_slip.fixed_fee;
            document.getElementById('boleto_min').value = data.fees.bank_slip.min_amount;
            if (data.fees.bank_slip.max_amount) {
                document.getElementById('boleto_max').value = data.fees.bank_slip.max_amount;
            }
            if (data.fees.bank_slip.min_transaction_value) {
                document.getElementById('boleto_min_transaction').value = data.fees.bank_slip.min_transaction_value;
            }
            if (data.fees.bank_slip.max_transaction_value) {
                document.getElementById('boleto_max_transaction').value = data.fees.bank_slip.max_transaction_value;
            }
            
            document.getElementById('withdrawal_percentage').value = data.fees.withdrawal.percentage_fee;
            document.getElementById('withdrawal_fixed').value = data.fees.withdrawal.fixed_fee;
            document.getElementById('withdrawal_min').value = data.fees.withdrawal.min_amount;
            if (data.fees.withdrawal.max_amount) {
                document.getElementById('withdrawal_max').value = data.fees.withdrawal.max_amount;
            }
            if (data.fees.withdrawal.min_transaction_value) {
                document.getElementById('withdrawal_min_transaction').value = data.fees.withdrawal.min_transaction_value;
            }
            if (data.fees.withdrawal.max_transaction_value) {
                document.getElementById('withdrawal_max_transaction').value = data.fees.withdrawal.max_transaction_value;
            }
            
            // Preencher taxas de parcelamento
            if (data.fees.installments) {
                document.getElementById('card_2x').value = data.fees.installments['2x'] || (parseFloat(data.fees.credit_card.percentage_fee) + 0.60).toFixed(2);
                document.getElementById('card_3x').value = data.fees.installments['3x'] || (parseFloat(data.fees.credit_card.percentage_fee) + 1.20).toFixed(2);
                document.getElementById('card_4x').value = data.fees.installments['4x'] || (parseFloat(data.fees.credit_card.percentage_fee) + 1.80).toFixed(2);
                document.getElementById('card_5x').value = data.fees.installments['5x'] || (parseFloat(data.fees.credit_card.percentage_fee) + 2.40).toFixed(2);
                document.getElementById('card_6x').value = data.fees.installments['6x'] || (parseFloat(data.fees.credit_card.percentage_fee) + 3.00).toFixed(2);
                document.getElementById('card_12x').value = data.fees.installments['12x'] || (parseFloat(data.fees.credit_card.percentage_fee) + 6.00).toFixed(2);
            }
            
            // Atualizar informações de taxas atuais
            updateCurrentFeesDisplay(data.fees);
        } else {
            showAlert('Erro ao carregar taxas: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erro ao carregar taxas', 'error');
    });
}

function initializeFields() {
    // Valores padrão caso a API falhe
    document.getElementById('pix_percentage').value = '1.99';
    document.getElementById('pix_fixed').value = '0.00';
    document.getElementById('pix_min').value = '0.01';
    document.getElementById('pix_min_transaction').value = '1.00';
    
    document.getElementById('card_percentage').value = '3.99';
    document.getElementById('card_fixed').value = '0.39';
    document.getElementById('card_min').value = '0.50';
    document.getElementById('card_min_transaction').value = '5.00';
    
    document.getElementById('boleto_percentage').value = '2.49';
    document.getElementById('boleto_fixed').value = '2.00';
    document.getElementById('boleto_min').value = '2.50';
    document.getElementById('boleto_min_transaction').value = '10.00';
    
    document.getElementById('withdrawal_percentage').value = '0.00';
    document.getElementById('withdrawal_fixed').value = '10.00';
    document.getElementById('withdrawal_min').value = '10.00';
    document.getElementById('withdrawal_min_transaction').value = '10.00';
    
    document.getElementById('card_2x').value = '4.59';
    document.getElementById('card_3x').value = '5.19';
    document.getElementById('card_4x').value = '5.79';
    document.getElementById('card_5x').value = '6.39';
    document.getElementById('card_6x').value = '6.99';
    document.getElementById('card_12x').value = '9.99';
}

function updateCurrentFeesDisplay(fees) {
    // Atualizar exibição das taxas atuais
    document.getElementById('current_pix_percentage').textContent = fees.pix.percentage_fee + '%';
    document.getElementById('current_pix_fixed').textContent = 'R$ ' + parseFloat(fees.pix.fixed_fee).toFixed(2);
    document.getElementById('current_pix_min').textContent = 'R$ ' + parseFloat(fees.pix.min_amount).toFixed(2);
    document.getElementById('current_pix_max').textContent = fees.pix.max_amount ? 'R$ ' + parseFloat(fees.pix.max_amount).toFixed(2) : 'Sem limite';
    document.getElementById('current_pix_min_transaction').textContent = fees.pix.min_transaction_value ? 'R$ ' + parseFloat(fees.pix.min_transaction_value).toFixed(2) : 'Sem limite';
    document.getElementById('current_pix_max_transaction').textContent = fees.pix.max_transaction_value ? 'R$ ' + parseFloat(fees.pix.max_transaction_value).toFixed(2) : 'Sem limite';
    
    document.getElementById('current_card_percentage').textContent = fees.credit_card.percentage_fee + '%';
    document.getElementById('current_card_fixed').textContent = 'R$ ' + parseFloat(fees.credit_card.fixed_fee).toFixed(2);
    document.getElementById('current_card_min').textContent = 'R$ ' + parseFloat(fees.credit_card.min_amount).toFixed(2);
    document.getElementById('current_card_max').textContent = fees.credit_card.max_amount ? 'R$ ' + parseFloat(fees.credit_card.max_amount).toFixed(2) : 'Sem limite';
    document.getElementById('current_card_min_transaction').textContent = fees.credit_card.min_transaction_value ? 'R$ ' + parseFloat(fees.credit_card.min_transaction_value).toFixed(2) : 'Sem limite';
    document.getElementById('current_card_max_transaction').textContent = fees.credit_card.max_transaction_value ? 'R$ ' + parseFloat(fees.credit_card.max_transaction_value).toFixed(2) : 'Sem limite';
    
    document.getElementById('current_boleto_percentage').textContent = fees.bank_slip.percentage_fee + '%';
    document.getElementById('current_boleto_fixed').textContent = 'R$ ' + parseFloat(fees.bank_slip.fixed_fee).toFixed(2);
    document.getElementById('current_boleto_min').textContent = 'R$ ' + parseFloat(fees.bank_slip.min_amount).toFixed(2);
    document.getElementById('current_boleto_max').textContent = fees.bank_slip.max_amount ? 'R$ ' + parseFloat(fees.bank_slip.max_amount).toFixed(2) : 'Sem limite';
    document.getElementById('current_boleto_min_transaction').textContent = fees.bank_slip.min_transaction_value ? 'R$ ' + parseFloat(fees.bank_slip.min_transaction_value).toFixed(2) : 'Sem limite';
    document.getElementById('current_boleto_max_transaction').textContent = fees.bank_slip.max_transaction_value ? 'R$ ' + parseFloat(fees.bank_slip.max_transaction_value).toFixed(2) : 'Sem limite';
    
    document.getElementById('current_withdrawal_percentage').textContent = fees.withdrawal.percentage_fee + '%';
    document.getElementById('current_withdrawal_fixed').textContent = 'R$ ' + parseFloat(fees.withdrawal.fixed_fee).toFixed(2);
    document.getElementById('current_withdrawal_min').textContent = 'R$ ' + parseFloat(fees.withdrawal.min_amount).toFixed(2);
    document.getElementById('current_withdrawal_max').textContent = fees.withdrawal.max_amount ? 'R$ ' + parseFloat(fees.withdrawal.max_amount).toFixed(2) : 'Sem limite';
    document.getElementById('current_withdrawal_min_transaction').textContent = fees.withdrawal.min_transaction_value ? 'R$ ' + parseFloat(fees.withdrawal.min_transaction_value).toFixed(2) : 'Sem limite';
    document.getElementById('current_withdrawal_max_transaction').textContent = fees.withdrawal.max_transaction_value ? 'R$ ' + parseFloat(fees.withdrawal.max_transaction_value).toFixed(2) : 'Sem limite';
}

function saveGlobalFees() {
    // Obter dados do formulário
    const formData = {
        pix: {
            percentage_fee: parseFloat(document.getElementById('pix_percentage').value),
            fixed_fee: parseFloat(document.getElementById('pix_fixed').value),
            min_amount: parseFloat(document.getElementById('pix_min').value),
            max_amount: document.getElementById('pix_max').value ? parseFloat(document.getElementById('pix_max').value) : null,
            min_transaction_value: document.getElementById('pix_min_transaction').value ? parseFloat(document.getElementById('pix_min_transaction').value) : null,
            max_transaction_value: document.getElementById('pix_max_transaction').value ? parseFloat(document.getElementById('pix_max_transaction').value) : null
        },
        credit_card: {
            percentage_fee: parseFloat(document.getElementById('card_percentage').value),
            fixed_fee: parseFloat(document.getElementById('card_fixed').value),
            min_amount: parseFloat(document.getElementById('card_min').value),
            max_amount: document.getElementById('card_max').value ? parseFloat(document.getElementById('card_max').value) : null,
            min_transaction_value: document.getElementById('card_min_transaction').value ? parseFloat(document.getElementById('card_min_transaction').value) : null,
            max_transaction_value: document.getElementById('card_max_transaction').value ? parseFloat(document.getElementById('card_max_transaction').value) : null
        },
        bank_slip: {
            percentage_fee: parseFloat(document.getElementById('boleto_percentage').value),
            fixed_fee: parseFloat(document.getElementById('boleto_fixed').value),
            min_amount: parseFloat(document.getElementById('boleto_min').value),
            max_amount: document.getElementById('boleto_max').value ? parseFloat(document.getElementById('boleto_max').value) : null,
            min_transaction_value: document.getElementById('boleto_min_transaction').value ? parseFloat(document.getElementById('boleto_min_transaction').value) : null,
            max_transaction_value: document.getElementById('boleto_max_transaction').value ? parseFloat(document.getElementById('boleto_max_transaction').value) : null
        },
        withdrawal: {
            percentage_fee: parseFloat(document.getElementById('withdrawal_percentage').value),
            fixed_fee: parseFloat(document.getElementById('withdrawal_fixed').value),
            min_amount: parseFloat(document.getElementById('withdrawal_min').value),
            max_amount: document.getElementById('withdrawal_max').value ? parseFloat(document.getElementById('withdrawal_max').value) : null,
            min_transaction_value: document.getElementById('withdrawal_min_transaction').value ? parseFloat(document.getElementById('withdrawal_min_transaction').value) : null,
            max_transaction_value: document.getElementById('withdrawal_max_transaction').value ? parseFloat(document.getElementById('withdrawal_max_transaction').value) : null
        },
        installments: {
            '2x': parseFloat(document.getElementById('card_2x').value),
            '3x': parseFloat(document.getElementById('card_3x').value),
            '4x': parseFloat(document.getElementById('card_4x').value),
            '5x': parseFloat(document.getElementById('card_5x').value),
            '6x': parseFloat(document.getElementById('card_6x').value),
            '12x': parseFloat(document.getElementById('card_12x').value)
        }
    };
    
    // Enviar dados para o servidor
    fetch('/admin/white-label/global-fees/update', {
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
            showAlert('Taxas globais atualizadas com sucesso!', 'success');
            // Atualizar informações de taxas atuais
            updateCurrentFeesDisplay(data.fees);
        } else {
            showAlert('Erro ao salvar taxas: ' + data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erro ao salvar taxas', 'error');
    });
}

function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    const alertClass = type === 'success' 
        ? 'bg-green-500/10 border-green-500/20 text-green-600' 
        : 'bg-green-500/10 border-green-500/20 text-green-600';
    
    const alertHTML = `
        <div class="mb-6 ${alertClass} px-4 py-3 rounded-lg alert">
            ${message}
        </div>
    `;
    
    alertContainer.innerHTML = alertHTML;
    
    // Auto-hide alert after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
}
</script>
@endpush
@endsection