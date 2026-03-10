<?php $__env->startSection('title', 'Suas adquirentes'); ?>
<?php $__env->startSection('page-title', 'Suas adquirentes'); ?>
<?php $__env->startSection('page-description', 'Aqui estão todas as suas adquirentes configuradas. Caso queira adicionar mais opções, entre em contato com nosso time.'); ?>

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

    <!-- Add Gateway Button and Status Tabs -->
    <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-2">
            <a href="<?php echo e(route('admin.gateways.index', ['status' => 'active'])); ?>" 
               class="px-4 py-2 rounded-lg text-sm transition-colors <?php echo e($status === 'active' ? 'bg-blue-600 text-gray-900' : 'bg-gray-700 text-gray-700 hover:bg-gray-600'); ?>">
                Gateways Ativos
            </a>
            <a href="<?php echo e(route('admin.gateways.index', ['status' => 'inactive'])); ?>" 
               class="px-4 py-2 rounded-lg text-sm transition-colors <?php echo e($status === 'inactive' ? 'bg-blue-600 text-gray-900' : 'bg-gray-700 text-gray-700 hover:bg-gray-600'); ?>">
                Gateways Inativos
            </a>
        </div>
        <button 
            onclick="openAddGatewayModal()"
            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors flex items-center"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Adicionar Gateway
        </button>
    </div>

    <!-- Gateways Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900"><?php echo e($gateway->name); ?></h3>
                    <div class="flex items-center space-x-2">
                        <?php if($gateway->is_default): ?>
                            <span class="px-2 py-1 bg-blue-500/10 text-blue-600 text-xs font-medium rounded-full border border-blue-500/20">
                                Padrão
                            </span>
                        <?php endif; ?>
                        <span class="px-2 py-1 <?php echo e(isset($credentials[$gateway->id]) ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-green-500/10 text-green-600 border-green-500/20'); ?> text-xs font-medium rounded-full border">
                            <?php echo e(isset($credentials[$gateway->id]) ? 'Configurado' : 'Não Configurado'); ?>

                        </span>
                    </div>
                </div>
                
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">API URL:</span>
                        <span class="text-gray-900"><?php echo e($gateway->api_url); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="text-gray-900"><?php echo e($gateway->is_active ? 'Ativo' : 'Inativo'); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Ambiente:</span>
                        <span class="text-gray-900">
                            <?php if(isset($credentials[$gateway->id])): ?>
                                <?php echo e($credentials[$gateway->id]->is_sandbox ? 'Sandbox' : 'Produção'); ?>

                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Usuários Ativos:</span>
                        <span class="text-gray-900"><?php echo e(\App\Models\User::where('assigned_gateway_id', $gateway->id)->count()); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tipo:</span>
                        <span class="text-gray-900"><?php echo e(ucfirst($gateway->getConfig('gateway_type', 'avivhub'))); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transações:</span>
                        <span class="text-gray-900"><?php echo e(\App\Models\Transaction::where('gateway_id', $gateway->id)->count()); ?></span>
                    </div>
                </div>
                
                <!-- Credentials Info -->
                <?php if(isset($credentials[$gateway->id]) && $credentials[$gateway->id]): ?>
                    <?php $cred = $credentials[$gateway->id]; ?>
                    <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-green-800">
                                <?php if($cred->user_id === null): ?>
                                    🌍 Credenciais Globais
                                <?php else: ?>
                                    👤 Credenciais do Admin
                                <?php endif; ?>
                            </span>
                            <span class="text-xs text-green-600">
                                <?php echo e($cred->is_sandbox ? 'Sandbox' : 'Produção'); ?>

                            </span>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <button 
                                onclick="editCredentials(<?php echo e($cred->id); ?>, <?php echo e($gateway->id); ?>, '<?php echo e($gateway->name); ?>')"
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs transition-colors"
                            >
                                ✏️ Editar
                            </button>
                            <form action="<?php echo e(route('admin.gateways.credentials.delete', $cred->id)); ?>" method="POST" class="inline flex-1" onsubmit="return confirm('Tem certeza que deseja excluir estas credenciais?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button 
                                    type="submit"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs transition-colors"
                                >
                                    🗑️ Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button 
                        onclick="openConfigureModal(<?php echo e($gateway->id); ?>, '<?php echo e($gateway->name); ?>', '<?php echo e($gateway->getConfig('gateway_type')); ?>')"
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-3 py-2 rounded-lg text-xs transition-colors"
                    >
                        <?php echo e(isset($credentials[$gateway->id]) && $credentials[$gateway->id] ? 'Atualizar' : 'Configurar'); ?>

                    </button>
                    
                    <button 
                        onclick="openEditModal(<?php echo e($gateway->id); ?>, '<?php echo e($gateway->name); ?>', '<?php echo e($gateway->api_url); ?>', <?php echo e($gateway->is_default ? 'true' : 'false'); ?>, <?php echo e($gateway->is_active ? 'true' : 'false'); ?>)"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-xs transition-colors"
                    >
                        Editar Gateway
                    </button>
                    
                    <a 
                        href="<?php echo e(route('admin.gateways.fees', $gateway->id)); ?>"
                        class="bg-green-600 hover:bg-green-700 text-gray-900 px-3 py-2 rounded-lg text-xs transition-colors text-center"
                    >
                        Configurar Taxas
                    </a>
                    
                    <?php if($status === 'active'): ?>
                        <form action="<?php echo e(route('admin.gateways.toggle-status', $gateway->id)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <button 
                                type="submit"
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-gray-900 px-3 py-2 rounded-lg text-xs transition-colors"
                                <?php echo e($gateway->is_default ? 'disabled' : ''); ?>

                                title="<?php echo e($gateway->is_default ? 'Não é possível desativar o gateway padrão' : 'Desativar Gateway'); ?>"
                            >
                                Desativar
                            </button>
                        </form>
                    <?php else: ?>
                        <form action="<?php echo e(route('admin.gateways.toggle-status', $gateway->id)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <button 
                                type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-3 py-2 rounded-lg text-xs transition-colors"
                            >
                                Ativar
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if($gateway->canBeDeleted()): ?>
                        <button 
                            onclick="confirmDelete(<?php echo e($gateway->id); ?>, '<?php echo e($gateway->name); ?>')"
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-3 py-2 rounded-lg text-xs transition-colors col-span-2"
                        >
                            Excluir Gateway
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Test Result -->
                <div id="test-result-<?php echo e($gateway->id); ?>" class="mt-4 hidden"></div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <?php if($gateways->isEmpty()): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6 col-span-full">
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">Nenhum gateway <?php echo e($status === 'active' ? 'ativo' : 'inativo'); ?> encontrado</p>
                    <?php if($status === 'inactive'): ?>
                        <p class="text-gray-500 text-sm mt-2">Gateways inativos não são exibidos para os usuários</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Gateway Modal -->
<div id="addGatewayModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Adicionar Gateway</h3>
                    <button onclick="closeAddGatewayModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="<?php echo e(route('admin.gateways.store')); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Gateway *
                        </label>
                        <input 
                            id="name" 
                            name="name" 
                            type="text" 
                            required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Ex: StrikeCash, SkalePay, etc."
                        >
                    </div>
                    
                    <div>
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                            Slug *
                        </label>
                        <input 
                            id="slug" 
                            name="slug" 
                            type="text" 
                            required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Ex: strikecash, skalepay, etc."
                        >
                        <p class="text-xs text-gray-500 mt-1">Identificador único, apenas letras minúsculas, números e hífens</p>
                    </div>
                    
                    <div>
                        <label for="gateway_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Gateway *
                        </label>
                        <select 
                            id="gateway_type" 
                            name="gateway_type" 
                            required
                            onchange="handleGatewayTypeChange()"
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        >
                            <option value="hopy">Hopy (SkalePay)</option>
                            <option value="splitwave">Splitwave (ReflowPay)</option>
                            <option value="sharkgateway">Sharkgateway (Payshark)</option>
                            <option value="arkama">Arkama</option>
                            <option value="versell">Versell</option>
                            <option value="getpay">GetPay</option>
                            <option value="e2bank">E2 Bank</option>
                            <option value="pluggou">Pluggou</option>
                        </select>
                    </div>
                    
                    <div id="api_url_field">
                        <label for="api_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL da API *
                        </label>
                        <input 
                            id="api_url" 
                            name="api_url" 
                            type="text" 
                            required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Ex: https://srv.strikecash.com.br"
                        >
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="is_default" 
                            name="is_default" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                        >
                        <label for="is_default" class="ml-2 text-sm text-gray-700">
                            Definir como gateway padrão
                        </label>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeAddGatewayModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Adicionar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Gateway Modal -->
<div id="editGatewayModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Editar Gateway</h3>
                    <button onclick="closeEditModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="editGatewayForm" action="" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Gateway *
                        </label>
                        <input 
                            id="edit_name" 
                            name="name" 
                            type="text" 
                            required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                    
                    <div>
                        <label for="edit_api_url" class="block text-sm font-medium text-gray-700 mb-2">
                            URL da API *
                        </label>
                        <input 
                            id="edit_api_url" 
                            name="api_url" 
                            type="text" 
                            required 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        >
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="edit_is_default" 
                            name="is_default" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                        >
                        <label for="edit_is_default" class="ml-2 text-sm text-gray-700">
                            Definir como gateway padrão
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input 
                            type="checkbox" 
                            id="edit_is_active" 
                            name="is_active" 
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                        >
                        <label for="edit_is_active" class="ml-2 text-sm text-gray-700">
                            Gateway ativo
                        </label>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeEditModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Configure Gateway Modal -->
<div id="configureGatewayModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900" id="configureModalTitle">Configurar Gateway</h3>
                    <button onclick="closeConfigureModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="<?php echo e(route('admin.gateways.configure')); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" id="configure_gateway_id" name="gateway_id" value="">
                    <input type="hidden" id="configure_gateway_type" name="gateway_type" value="">
                    
                    <!-- Global Credentials -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="is_global" 
                                name="is_global" 
                                value="1"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                            >
                            <span class="ml-2 text-sm font-semibold text-blue-900">
                                Credenciais Globais (Disponível para todos os usuários)
                            </span>
                        </label>
                        <p class="text-xs text-blue-700 mt-2 ml-6">
                            Se marcado, essas credenciais estarão disponíveis para todos os usuários do sistema. 
                            Caso contrário, apenas para o usuário admin.
                        </p>
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
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                                >
                                <span class="ml-2 text-sm text-gray-700">Sandbox (Testes)</span>
                            </label>
                            <label class="flex items-center">
                                <input 
                                    type="radio" 
                                    name="is_sandbox" 
                                    value="0" 
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800"
                                    checked
                                >
                                <span class="ml-2 text-sm text-gray-700">Produção</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Use Sandbox para testes e Produção para transações reais</p>
                    </div>

                    <!-- Public Key (conditional) -->
                    <div id="public_key_container">
                        <label for="public_key" class="block text-sm font-medium text-gray-700 mb-2">
                            Chave Pública (Public Key) *
                        </label>
                        <input 
                            id="public_key" 
                            name="public_key" 
                            type="text" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono"
                            placeholder="public_xxxxxxxxxxxxxxxxx"
                        >
                        <p class="text-xs text-gray-500 mt-1">Chave pública fornecida pelo gateway</p>
                    </div>

                    <!-- Secret Key -->
                    <div>
                        <label for="secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                            Chave Secreta (Secret Key) *
                        </label>
                        <div class="relative">
                            <input 
                                id="secret_key" 
                                name="secret_key" 
                                type="password" 
                                required 
                                class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 font-mono pr-12"
                                placeholder="secret_xxxxxxxxxxxxxxxxx"
                            >
                            <button 
                                type="button" 
                                onclick="toggleVisibility('secret_key')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-900 transition-colors"
                                title="Mostrar/Ocultar"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1" id="secret_key_help">Chave secreta fornecida pelo gateway</p>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeConfigureModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Salvar Credenciais
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Gateway Confirmation Modal -->
<div id="deleteGatewayModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Confirmar Exclusão</h3>
                    <button onclick="closeDeleteModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="text-gray-700 mb-4">
                    Tem certeza que deseja excluir o gateway <span id="deleteGatewayName" class="font-semibold"></span>? Esta ação não pode ser desfeita.
                </p>

                <form id="deleteGatewayForm" action="" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeDeleteModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Excluir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Add Gateway Modal
function openAddGatewayModal() {
    document.getElementById('addGatewayModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
    
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-')     // Replace spaces with hyphens
            .replace(/-+/g, '-');     // Replace multiple hyphens with single hyphen
        
        document.getElementById('slug').value = slug;
    });
}

function closeAddGatewayModal() {
    document.getElementById('addGatewayModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function handleGatewayTypeChange() {
    const gatewayType = document.getElementById('gateway_type').value;
    const apiUrlField = document.getElementById('api_url_field');
    const apiUrlInput = document.getElementById('api_url');
    
    if (gatewayType === 'e2bank') {
        // Esconde o campo e define URL fixa para E2 Bank
        apiUrlField.style.display = 'none';
        apiUrlInput.value = 'https://api.pix.bancoe2.com.br';
        apiUrlInput.required = false;
    } else if (gatewayType === 'pluggou') {
        // Preenche automaticamente a URL da API para Pluggou
        apiUrlField.style.display = 'block';
        apiUrlInput.required = true;
        apiUrlInput.value = 'https://api.pluggoutech.com/api';
        apiUrlInput.readOnly = false;
    } else {
        // Mostra o campo para outros gateways
        apiUrlField.style.display = 'block';
        apiUrlInput.required = true;
        apiUrlInput.value = '';
        apiUrlInput.readOnly = false;
    }
    
    // Atualiza campos baseado no tipo
    updateGatewayTypeFields(gatewayType);
}


// Edit Gateway Modal
function openEditModal(id, name, apiUrl, isDefault, isActive) {
    document.getElementById('editGatewayForm').action = `/admin/gateways/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_api_url').value = apiUrl;
    document.getElementById('edit_is_default').checked = isDefault;
    document.getElementById('edit_is_active').checked = isActive;
    
    document.getElementById('editGatewayModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeEditModal() {
    document.getElementById('editGatewayModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Configure Gateway Modal
function openConfigureModal(id, name, gatewayType = null) {
    document.getElementById('configureModalTitle').textContent = `Configurar ${name}`;
    document.getElementById('configure_gateway_id').value = id;
    
    // Get existing credentials if available
    fetch(`/admin/gateways/${id}/credentials`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const type = gatewayType || data.gateway.config?.gateway_type || 'avivhub';
            document.getElementById('configure_gateway_type').value = type;
            
            // Show/hide fields based on gateway type
            if (type === 'splitwave') {
                document.getElementById('public_key_container').style.display = 'none';
                document.getElementById('secret_key_help').textContent = 'Chave de autorização (x-authorization-key)';
            } else if (type === 'ocean') {
                document.getElementById('public_key_container').style.display = 'none';
                document.getElementById('secret_key_help').textContent = 'Chave de API (Bearer Token)';
            } else if (type === 'versell') {
                document.getElementById('public_key_container').style.display = 'block';
                document.getElementById('secret_key_help').textContent = 'Chave pública (vspi) e chave secreta (vsps) fornecidas pela Versell';
            } else if (type === 'pluggou') {
                document.getElementById('public_key_container').style.display = 'block';
                document.getElementById('secret_key_help').textContent = 'Chave pública (X-Public-Key) e chave secreta (X-Secret-Key) fornecidas pela Pluggou';
            } else {
                document.getElementById('public_key_container').style.display = 'block';
                document.getElementById('secret_key_help').textContent = 'Chave secreta fornecida pelo gateway';
            }
            
            if (data.credentials) {
                document.querySelector('input[name="is_sandbox"][value="1"]').checked = data.credentials.is_sandbox;
                document.querySelector('input[name="is_sandbox"][value="0"]').checked = !data.credentials.is_sandbox;
                document.getElementById('public_key').value = data.credentials.public_key || '';
                document.getElementById('secret_key').value = data.credentials.secret_key || '';
                document.getElementById('is_global').checked = data.credentials.user_id === null;
            } else {
                // Reset form if no credentials
                document.querySelector('input[name="is_sandbox"][value="1"]').checked = false;
                document.querySelector('input[name="is_sandbox"][value="0"]').checked = true;
                document.getElementById('public_key').value = '';
                document.getElementById('secret_key').value = '';
                document.getElementById('is_global').checked = false;
            }
        } else {
            // Reset form on error
            document.querySelector('input[name="is_sandbox"][value="1"]').checked = false;
            document.querySelector('input[name="is_sandbox"][value="0"]').checked = true;
            document.getElementById('public_key').value = '';
            document.getElementById('secret_key').value = '';
            document.getElementById('is_global').checked = false;
        }
    })
    .catch(error => {
        console.error('Error fetching credentials:', error);
        // Reset form on error
        document.querySelector('input[name="is_sandbox"][value="1"]').checked = false;
        document.querySelector('input[name="is_sandbox"][value="0"]').checked = true;
        document.getElementById('public_key').value = '';
        document.getElementById('secret_key').value = '';
        document.getElementById('is_global').checked = false;
    });
    
    document.getElementById('configureGatewayModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

// Edit Credentials
function editCredentials(credentialId, gatewayId, gatewayName) {
    fetch(`/admin/gateways/credentials/${credentialId}/edit`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Open configure modal with existing data
            openConfigureModal(gatewayId, gatewayName, data.gateway.gateway_type);
            
            // Wait a bit for modal to open, then fill data
            setTimeout(() => {
                document.getElementById('configure_gateway_id').value = gatewayId;
                document.getElementById('configure_gateway_type').value = data.gateway.gateway_type;
                document.getElementById('public_key').value = data.credential.public_key || '';
                document.getElementById('secret_key').value = data.credential.secret_key || '';
                document.querySelector('input[name="is_sandbox"][value="1"]').checked = data.credential.is_sandbox;
                document.querySelector('input[name="is_sandbox"][value="0"]').checked = !data.credential.is_sandbox;
                document.getElementById('is_global').checked = data.credential.is_global;
                
                // Update fields based on gateway type
                const type = data.gateway.gateway_type;
                if (type === 'pluggou') {
                    document.getElementById('public_key_container').style.display = 'block';
                    document.getElementById('secret_key_help').textContent = 'Chave pública (X-Public-Key) e chave secreta (X-Secret-Key) fornecidas pela Pluggou';
                }
            }, 100);
        } else {
            alert('Erro ao carregar credenciais: ' + (data.error || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Error fetching credentials:', error);
        alert('Erro ao carregar credenciais');
    });
}

function closeConfigureModal() {
    document.getElementById('configureGatewayModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Delete Gateway Modal
function confirmDelete(id, name) {
    document.getElementById('deleteGatewayName').textContent = name;
    document.getElementById('deleteGatewayForm').action = `/admin/gateways/${id}`;
    
    document.getElementById('deleteGatewayModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeDeleteModal() {
    document.getElementById('deleteGatewayModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Test Connection
function testConnection(gatewayId) {
    const btn = document.getElementById(`test-btn-${gatewayId}`);
    const result = document.getElementById(`test-result-${gatewayId}`);
    
    btn.disabled = true;
    btn.textContent = 'Testando...';
    
    fetch('<?php echo e(route("admin.gateways.test")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify({
            gateway_id: gatewayId
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

// Gateway type change handler
document.addEventListener('DOMContentLoaded', function() {
    const gatewayTypeSelect = document.getElementById('gateway_type');
    if (gatewayTypeSelect) {
        gatewayTypeSelect.addEventListener('change', function() {
            updateGatewayTypeFields(this.value);
        });
    }
});

function updateGatewayTypeFields(gatewayType) {
    // Update API URL placeholder based on gateway type
    const apiUrlInput = document.getElementById('api_url');
    if (apiUrlInput) {
        if (gatewayType === 'hopy') {
            apiUrlInput.placeholder = 'Ex: https://api.skalepay.com.br';
        } else if (gatewayType === 'splitwave') {
            apiUrlInput.placeholder = 'Ex: https://api.reflowpay.com/v1';
        } else if (gatewayType === 'sharkgateway') {
            apiUrlInput.placeholder = 'Ex: https://api.paysharkgateway.com.br';
        } else if (gatewayType === 'arkama') {
            apiUrlInput.placeholder = 'Ex: https://app.arkama.com.br/api/v1/orders';
        } else if (gatewayType === 'getpay') {
            apiUrlInput.placeholder = 'Ex: https://hub.getpay.store';
        } else if (gatewayType === 'versell') {
            apiUrlInput.placeholder = 'Ex: https://api.versell.com.br';
        } else if (gatewayType === 'pluggou') {
            apiUrlInput.placeholder = 'Ex: https://api.pluggoutech.com/api';
            apiUrlInput.value = 'https://api.pluggoutech.com/api';
        } else if (gatewayType === 'e2bank') {
            apiUrlInput.placeholder = 'URL configurada automaticamente';
            apiUrlInput.value = '';
            apiUrlInput.readOnly = true;
        } else {
            apiUrlInput.readOnly = false;
        }
    }
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
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/gateways/index.blade.php ENDPATH**/ ?>