<?php $__env->startSection('title', 'Multi-Liquidante'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 overflow-auto">
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Multi-Liquidante</h1>
            <p class="text-sm text-gray-600 mt-1">Configure múltiplos gateways para processar pagamentos simultaneamente</p>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                <ul class="list-disc list-inside mb-0">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <form action="<?php echo e(route('admin.multi-gateway.update')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" id="is_enabled" name="is_enabled" value="1" <?php echo e($config->is_enabled ? 'checked' : ''); ?> class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                <span class="ml-2 font-semibold text-gray-900">Ativar Multi-Liquidante</span>
                            </label>
                            <p class="text-sm text-gray-600 mt-1 ml-6">Gera PIX em múltiplos gateways simultaneamente para aumentar conversão</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="mode-section">
                            <div>
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Modo de Aplicação</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" name="mode" id="mode" <?php echo e(!$config->is_enabled ? 'disabled' : ''); ?>>
                                    <option value="global" <?php echo e($config->mode === 'global' ? 'selected' : ''); ?>>Global (todos os usuários)</option>
                                    <option value="specific_users" <?php echo e($config->mode === 'specific_users' ? 'selected' : ''); ?>>Usuários Específicos</option>
                                    <option value="all_except" <?php echo e($config->mode === 'all_except' ? 'selected' : ''); ?>>Todos Exceto...</option>
                                </select>
                            </div>
                            
                            <div id="users-section" style="display: <?php echo e(in_array($config->mode, ['specific_users', 'all_except']) ? 'block' : 'none'); ?>">
                                <label class="block text-sm font-semibold text-gray-900 mb-2">Selecionar Usuários</label>
                                <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" name="selected_users[]" id="selected_users" multiple size="5" <?php echo e(!$config->is_enabled ? 'disabled' : ''); ?>>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($user->id); ?>" <?php echo e(in_array($user->id, $config->selected_users ?? []) ? 'selected' : ''); ?>>
                                            <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <p class="text-sm text-gray-600 mt-1">Segure Ctrl (Cmd no Mac) para selecionar múltiplos usuários</p>
                            </div>
                        </div>
                        
                        <div id="gateways-section">
                            <label class="block text-sm font-semibold text-gray-900 mb-2">Selecionar Gateways</label>
                            <p class="text-sm text-gray-600 mb-3">Selecione os gateways que serão usados no multi-liquidante</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <label class="flex items-center cursor-pointer p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="selected_gateways[]" value="<?php echo e($gateway->id); ?>" id="gateway_<?php echo e($gateway->id); ?>" <?php echo e(in_array($gateway->id, $config->selected_gateways ?? []) ? 'checked' : ''); ?> <?php echo e(!$config->is_enabled ? 'disabled' : ''); ?> class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                                        <span class="ml-2 text-sm text-gray-900"><?php echo e($gateway->name); ?></span>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Salvar Configuração
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h5 class="font-semibold text-gray-900">Como Funciona o Multi-Liquidante</h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h6 class="text-green-600 font-semibold mb-3">Para Gateways de Pagamento (PIX IN)</h6>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Gera QR Code PIX em <strong>todos os gateways selecionados</strong> simultaneamente</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Aumenta conversão: se um gateway falhar, outro pode processar</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Cliente vê múltiplos QR Codes para escolher</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Qualquer gateway que receber o pagamento confirma a transação</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <h6 class="text-green-600 font-semibold mb-3">Para BaaS (PIX OUT - Saques)</h6>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Distribui saques automaticamente entre BaaS ativos</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Sistema round-robin: alterna entre provedores</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Previne sobrecarga em um único BaaS</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>Configuração automática quando múltiplos BaaS estão ativos</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('is_enabled').addEventListener('change', function() {
    const enabled = this.checked;
    document.getElementById('mode').disabled = !enabled;
    document.getElementById('selected_users').disabled = !enabled;
    document.querySelectorAll('input[name="selected_gateways[]"]').forEach(el => {
        el.disabled = !enabled;
    });
});

document.getElementById('mode').addEventListener('change', function() {
    const usersSection = document.getElementById('users-section');
    if (this.value === 'specific_users' || this.value === 'all_except') {
        usersSection.style.display = 'block';
    } else {
        usersSection.style.display = 'none';
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/multi-gateway/index.blade.php ENDPATH**/ ?>