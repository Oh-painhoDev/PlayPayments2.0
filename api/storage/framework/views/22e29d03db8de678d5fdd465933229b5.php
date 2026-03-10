<?php $__env->startSection('title', 'Configuração BaaS'); ?>
<?php $__env->startSection('page-title', 'Configuração BaaS'); ?>
<?php $__env->startSection('page-description', 'Configure as credenciais dos serviços de Banking as a Service'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- BaaS Configuration -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <!-- StrikeCash BaaS -->
        <div class="bg-white rounded-lg border <?php echo e(isset($baasCredentials['strikecash']) && $baasCredentials['strikecash']->is_active ? 'border-green-800' : 'border-gray-200'); ?> p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">StrikeCash BaaS</h2>
                    <p class="text-gray-600 text-sm mt-1">Configure as credenciais para processamento de saques via StrikeCash</p>
                </div>
                <div class="flex items-center space-x-2">
                    <?php
                        $strikeCashCredentials = $baasCredentials['strikecash'] ?? null;
                    ?>
                    <span class="px-2 py-1 <?php echo e($strikeCashCredentials ? ($strikeCashCredentials->is_sandbox ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' : 'bg-green-500/10 text-green-600 border-green-500/20') : 'bg-green-500/10 text-green-600 border-green-500/20'); ?> text-xs font-medium rounded-full border">
                        <?php if($strikeCashCredentials): ?>
                            <?php echo e($strikeCashCredentials->is_sandbox ? 'Sandbox' : 'Produção'); ?>

                        <?php else: ?>
                            Não Configurado
                        <?php endif; ?>
                    </span>
                    
                    <?php if($strikeCashCredentials): ?>
                        <form action="<?php echo e(route('admin.baas.toggle-active')); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="gateway" value="strikecash">
                            <button 
                                type="submit"
                                class="px-2 py-1 <?php echo e($strikeCashCredentials->is_active ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-gray-500/10 text-gray-600 border-gray-500/20'); ?> text-xs font-medium rounded-full border"
                            >
                                <?php echo e($strikeCashCredentials->is_active ? 'Ativo' : 'Inativo'); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <form action="<?php echo e(route('admin.baas.update')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="gateway" value="strikecash">

                <!-- Environment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Ambiente
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                name="is_sandbox" 
                                value="1" 
                                <?php echo e(old('is_sandbox', $strikeCashCredentials ? $strikeCashCredentials->is_sandbox : true) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                            >
                            <span class="ml-2 text-sm text-gray-700">Sandbox (Testes)</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                name="is_sandbox" 
                                value="0" 
                                <?php echo e(old('is_sandbox', $strikeCashCredentials ? $strikeCashCredentials->is_sandbox : true) ? '' : 'checked'); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                            >
                            <span class="ml-2 text-sm text-gray-700">Produção</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Use Sandbox para testes e Produção para transações reais</p>
                </div>

                <!-- Public Key -->
                <div>
                    <label for="strikecash_public_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Chave Pública (Public Key) *
                    </label>
                    <input 
                        id="strikecash_public_key" 
                        name="public_key" 
                        type="text" 
                        required 
                        value="<?php echo e(old('public_key', $strikeCashCredentials ? $strikeCashCredentials->public_key : '')); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono"
                        placeholder="public_xxxxxxxxxxxxxxxxx"
                    >
                    <p class="text-xs text-gray-500 mt-1">Chave pública fornecida pelo StrikeCash para processamento de saques</p>
                </div>

                <!-- Secret Key -->
                <div>
                    <label for="strikecash_secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Chave Secreta (Secret Key) *
                    </label>
                    <div class="relative">
                        <input 
                            id="strikecash_secret_key" 
                            name="secret_key" 
                            type="password" 
                            required 
                            value="<?php echo e(old('secret_key', $strikeCashCredentials ? $strikeCashCredentials->secret_key : '')); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono pr-12"
                            placeholder="secret_xxxxxxxxxxxxxxxxx"
                        >
                        <button 
                            type="button" 
                            onclick="toggleVisibility('strikecash_secret_key')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-900 transition-colors"
                            title="Mostrar/Ocultar"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Chave secreta fornecida pelo StrikeCash para processamento de saques</p>
                </div>

                <!-- Default BaaS -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="strikecash_is_default" 
                        name="is_default" 
                        value="1" 
                        <?php echo e(old('is_default', $strikeCashCredentials && $strikeCashCredentials->is_active) ? 'checked' : ''); ?>

                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                    >
                    <label for="strikecash_is_default" class="ml-2 text-sm text-gray-700">
                        Definir como BaaS padrão
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <?php if($strikeCashCredentials): ?>
                        <button 
                            type="button" 
                            onclick="testConnection('strikecash')"
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                            id="test-strikecash-btn"
                        >
                            Testar Conexão
                        </button>
                    <?php endif; ?>
                    
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        <?php echo e($strikeCashCredentials ? 'Atualizar Credenciais' : 'Salvar Credenciais'); ?>

                    </button>
                </div>
            </form>

            <!-- Test Result -->
            <div id="test-strikecash-result" class="mt-4 hidden"></div>
        </div>

        <!-- Cashtime BaaS -->
        <div class="bg-white rounded-lg border <?php echo e(isset($baasCredentials['cashtime']) && $baasCredentials['cashtime']->is_active ? 'border-green-800' : 'border-gray-200'); ?> p-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Cashtime BaaS</h2>
                <p class="text-gray-600 text-sm mt-1">Configure as credenciais para PIX IN e PIX OUT via Cashtime</p>
            </div>
            <div class="flex items-center space-x-2">
                <?php
                    $cashtimeCredentials = $baasCredentials['cashtime'] ?? null;
                ?>
                <span class="px-2 py-1 <?php echo e($cashtimeCredentials ? ($cashtimeCredentials->is_sandbox ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' : 'bg-green-500/10 text-green-600 border-green-500/20') : 'bg-green-500/10 text-green-600 border-green-500/20'); ?> text-xs font-medium rounded-full border">
                    <?php if($cashtimeCredentials): ?>
                        <?php echo e($cashtimeCredentials->is_sandbox ? 'Sandbox' : 'Produção'); ?>

                    <?php else: ?>
                        Não Configurado
                    <?php endif; ?>
                </span>
                
                <?php if($cashtimeCredentials): ?>
                    <form action="<?php echo e(route('admin.baas.toggle-active')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="gateway" value="cashtime">
                        <button 
                            type="submit"
                            class="px-2 py-1 <?php echo e($cashtimeCredentials->is_active ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-gray-500/10 text-gray-600 border-gray-500/20'); ?> text-xs font-medium rounded-full border"
                        >
                            <?php echo e($cashtimeCredentials->is_active ? 'Ativo' : 'Inativo'); ?>

                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <form action="<?php echo e(route('admin.baas.update')); ?>" method="POST" class="space-y-6 mt-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="gateway" value="cashtime">
                <!-- Public Key (Optional for Cashtime) -->
                <div>
                    <label for="cashtime_public_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Chave Pública (x-store-key) - Opcional
                    </label>
                    <input 
                        id="cashtime_public_key" 
                        name="public_key" 
                        type="text" 
                        value="<?php echo e(old('public_key', $cashtimeCredentials && $cashtimeCredentials->public_key != 'dummy_public_key' ? $cashtimeCredentials->public_key : '')); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono"
                        placeholder="Deixe em branco se não possui chave pública"
                    >
                    <p class="text-xs text-gray-500 mt-1">Apenas preencha se sua conta Cashtime possui chave pública configurada</p>
                </div>

                <!-- Environment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Ambiente
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                name="is_sandbox" 
                                value="1" 
                                <?php echo e(old('is_sandbox', $cashtimeCredentials ? $cashtimeCredentials->is_sandbox : true) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                            >
                            <span class="ml-2 text-sm text-gray-700">Sandbox (Testes)</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                name="is_sandbox" 
                                value="0" 
                                <?php echo e(old('is_sandbox', $cashtimeCredentials ? $cashtimeCredentials->is_sandbox : true) ? '' : 'checked'); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                            >
                            <span class="ml-2 text-sm text-gray-700">Produção</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Use Sandbox para testes e Produção para transações reais</p>
                </div>

                <!-- Secret Key -->
                <div>
                    <label for="cashtime_secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Chave de Autorização (x-authorization-key) *
                    </label>
                    <div class="relative">
                        <input 
                            id="cashtime_secret_key" 
                            name="secret_key" 
                            type="password" 
                            required 
                            value="<?php echo e(old('secret_key', $cashtimeCredentials ? $cashtimeCredentials->secret_key : '')); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono pr-12"
                            placeholder="sk_live_xxxxxxxxxxxxxxxxx"
                        >
                        <button 
                            type="button" 
                            onclick="toggleVisibility('cashtime_secret_key')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-900 transition-colors"
                            title="Mostrar/Ocultar"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Chave de autorização fornecida pela Cashtime para processamento de saques</p>
                </div>

                <!-- Default BaaS -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="cashtime_is_default" 
                        name="is_default" 
                        value="1" 
                        <?php echo e(old('is_default', $cashtimeCredentials && $cashtimeCredentials->is_active) ? 'checked' : ''); ?>

                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                    >
                    <label for="cashtime_is_default" class="ml-2 text-sm text-gray-700">
                        Definir como BaaS padrão
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <?php if($cashtimeCredentials): ?>
                        <button 
                            type="button" 
                            onclick="testConnection('cashtime')"
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                            id="test-cashtime-btn"
                        >
                            Testar Conexão
                        </button>
                    <?php endif; ?>
                    
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        <?php echo e($cashtimeCredentials ? 'Atualizar Credenciais' : 'Salvar Credenciais'); ?>

                    </button>
                </div>
            </form>

            <!-- Test Result -->
            <div id="test-cashtime-result" class="mt-4 hidden"></div>
        </div>

        <!-- E2 Bank BaaS -->
        <div class="bg-white rounded-lg border <?php echo e(isset($baasCredentials['e2bank']) && $baasCredentials['e2bank']->is_active ? 'border-green-800' : 'border-gray-200'); ?> p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">E2 Bank BaaS</h2>
                    <p class="text-gray-600 text-sm mt-1">Configure as credenciais para PIX IN (QR Code) e PIX OUT via E2 Bank</p>
                </div>
                <div class="flex items-center space-x-2">
                    <?php
                        $e2bankCredentials = $baasCredentials['e2bank'] ?? null;
                    ?>
                    <span class="px-2 py-1 <?php echo e($e2bankCredentials ? ($e2bankCredentials->is_sandbox ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20' : 'bg-green-500/10 text-green-600 border-green-500/20') : 'bg-green-500/10 text-green-600 border-green-500/20'); ?> text-xs font-medium rounded-full border">
                        <?php if($e2bankCredentials): ?>
                            <?php echo e($e2bankCredentials->is_sandbox ? 'Sandbox' : 'Produção'); ?>

                        <?php else: ?>
                            Não Configurado
                        <?php endif; ?>
                    </span>
                    
                    <?php if($e2bankCredentials): ?>
                        <form action="<?php echo e(route('admin.baas.toggle-active')); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="gateway" value="e2bank">
                            <button 
                                type="submit"
                                class="px-2 py-1 <?php echo e($e2bankCredentials->is_active ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-gray-500/10 text-gray-600 border-gray-500/20'); ?> text-xs font-medium rounded-full border"
                            >
                                <?php echo e($e2bankCredentials->is_active ? 'Ativo' : 'Inativo'); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <form action="<?php echo e(route('admin.baas.update')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="gateway" value="e2bank">

                <!-- Environment -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Ambiente
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                name="is_sandbox" 
                                value="1" 
                                <?php echo e(old('is_sandbox', $e2bankCredentials ? $e2bankCredentials->is_sandbox : true) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                            >
                            <span class="ml-2 text-sm text-gray-700">Sandbox (Testes)</span>
                        </label>
                        <label class="flex items-center">
                            <input 
                                type="radio" 
                                name="is_sandbox" 
                                value="0" 
                                <?php echo e(old('is_sandbox', $e2bankCredentials ? $e2bankCredentials->is_sandbox : true) ? '' : 'checked'); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                            >
                            <span class="ml-2 text-sm text-gray-700">Produção</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Use Sandbox para testes e Produção para transações reais</p>
                </div>

                <!-- Public Key -->
                <div>
                    <label for="e2bank_public_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Client ID (QR Code API) *
                    </label>
                    <input 
                        id="e2bank_public_key" 
                        name="public_key" 
                        type="text" 
                        required 
                        value="<?php echo e(old('public_key', $e2bankCredentials ? $e2bankCredentials->public_key : '')); ?>"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono"
                        placeholder="E2BANK_QRCODE_CLIENT_ID"
                    >
                    <p class="text-xs text-gray-500 mt-1">Client ID fornecido pelo E2 Bank para API de QR Code (PIX IN)</p>
                </div>

                <!-- Secret Key -->
                <div>
                    <label for="e2bank_secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                        Client Secret (QR Code API) *
                    </label>
                    <div class="relative">
                        <input 
                            id="e2bank_secret_key" 
                            name="secret_key" 
                            type="password" 
                            required 
                            value="<?php echo e(old('secret_key', $e2bankCredentials ? $e2bankCredentials->secret_key : '')); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono pr-12"
                            placeholder="E2BANK_QRCODE_CLIENT_SECRET"
                        >
                        <button 
                            type="button" 
                            onclick="toggleVisibility('e2bank_secret_key')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-900 transition-colors"
                            title="Mostrar/Ocultar"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Client Secret fornecido pelo E2 Bank para API de QR Code</p>
                </div>

                <!-- Default BaaS -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="e2bank_is_default" 
                        name="is_default" 
                        value="1" 
                        <?php echo e(old('is_default', $e2bankCredentials && $e2bankCredentials->is_active) ? 'checked' : ''); ?>

                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                    >
                    <label for="e2bank_is_default" class="ml-2 text-sm text-gray-700">
                        Definir como BaaS padrão
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <?php if($e2bankCredentials): ?>
                        <button 
                            type="button" 
                            onclick="testConnection('e2bank')"
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                            id="test-e2bank-btn"
                        >
                            Testar Conexão
                        </button>
                    <?php endif; ?>
                    
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        <?php echo e($e2bankCredentials ? 'Atualizar Credenciais' : 'Salvar Credenciais'); ?>

                    </button>
                </div>
            </form>

            <!-- Test Result -->
            <div id="test-e2bank-result" class="mt-4 hidden"></div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- How to Get Credentials -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Como Obter as Credenciais</h3>
            
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">1</span>
                    <p>Acesse o painel do provedor BaaS</p>
                </div>
                
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">2</span>
                    <p>Vá em "Configurações" → "API"</p>
                </div>
                
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">3</span>
                    <p>Copie suas chaves de API</p>
                </div>
                
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">4</span>
                    <p>Cole as chaves nos campos acima</p>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                <p class="text-blue-700 text-xs">
                    💡 <strong>Dica:</strong> Estas credenciais serão usadas para processar saques automáticos e manuais.
                </p>
            </div>
        </div>

        <!-- Withdrawal Process -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Processo de Saque</h3>
            
            <div class="space-y-3 text-sm text-gray-700">
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">1</span>
                    <p>Usuário solicita saque via painel</p>
                </div>
                
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">2</span>
                    <p>Sistema verifica tipo de saque (manual/automático)</p>
                </div>
                
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">3</span>
                    <p>Saques automáticos são processados imediatamente</p>
                </div>
                
                <div class="flex items-start">
                    <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">4</span>
                    <p>Saques manuais aguardam aprovação do admin</p>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                <p class="text-yellow-300 text-xs">
                    ⚠️ <strong>Atenção:</strong> Certifique-se de que as credenciais estão corretas antes de processar saques.
                </p>
            </div>
        </div>

        <!-- System Statistics -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas do Sistema</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Saques Pendentes:</span>
                    <span class="text-gray-900"><?php echo e(\App\Models\Withdrawal::where('status', 'pending')->count()); ?></span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Saques Processando:</span>
                    <span class="text-gray-900"><?php echo e(\App\Models\Withdrawal::where('status', 'processing')->count()); ?></span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Saques Concluídos:</span>
                    <span class="text-gray-900"><?php echo e(\App\Models\Withdrawal::where('status', 'completed')->count()); ?></span>
                </div>
                
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Sacado:</span>
                    <span class="text-gray-900">R$ <?php echo e(number_format(\App\Models\Withdrawal::where('status', 'completed')->sum('amount'), 2, ',', '.')); ?></span>
                </div>
            </div>
            
            <div class="mt-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                <p class="text-green-700 text-xs">
                    ✅ Sistema operacional e processando saques normalmente.
                </p>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleVisibility(elementId) {
    const element = document.getElementById(elementId);
    const type = element.type === 'password' ? 'text' : 'password';
    element.type = type;
    
    // Update icon
    const button = event.target.closest('button');
    const icon = button.querySelector('svg');
    
    if (type === 'text') {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />';
    } else {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}

function testConnection(gateway) {
    const btn = document.getElementById(`test-${gateway}-btn`);
    const result = document.getElementById(`test-${gateway}-result`);
    
    btn.disabled = true;
    btn.textContent = 'Testando...';
    
    fetch('<?php echo e(route("admin.baas.test")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            gateway: gateway
        })
    })
    .then(response => response.json())
    .then(data => {
        result.classList.remove('hidden');
        
        if (data.success) {
            result.innerHTML = `
                <div class="bg-green-500/10 border border-green-500/20 text-green-600 px-3 py-2 rounded-lg text-sm">
                    ✅ Conexão bem-sucedida! Gateway funcionando corretamente.
                </div>
            `;
        } else {
            result.innerHTML = `
                <div class="bg-green-500/10 border border-green-500/20 text-green-600 px-3 py-2 rounded-lg text-sm">
                    ❌ Erro: ${data.error || 'Falha na conexão com o gateway'}
                </div>
            `;
        }
    })
    .catch(error => {
        result.classList.remove('hidden');
        result.innerHTML = `
            <div class="bg-green-500/10 border border-green-500/20 text-green-600 px-3 py-2 rounded-lg text-sm">
                ❌ Erro de conexão com o servidor
            </div>
        `;
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Testar Conexão';
    });
}

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.bg-green-500\\/10, .bg-green-500\\/10');
    alerts.forEach(alert => {
        if (!alert.id) { // Don't hide test results
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/baas/index.blade.php ENDPATH**/ ?>