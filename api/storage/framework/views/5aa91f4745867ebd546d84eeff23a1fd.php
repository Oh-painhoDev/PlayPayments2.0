<?php $__env->startSection('title', 'Configuração de Retentativa'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Configuração de Retentativa</h1>
        <p class="text-gray-600 mt-1">Configure o sistema de retentativa automática de pagamento quando um gateway falhar</p>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-700 px-4 py-3 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="mb-6 bg-red-500/10 border border-red-500/20 text-red-700 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <form action="<?php echo e(route('admin.retry.update')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Retentativa Global</h3>
                        <p class="text-sm text-gray-600 mt-1">Quando ativado, todas as transações tentarão o gateway alternativo se o principal falhar</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="is_enabled" 
                            value="1"
                            <?php echo e(($retryConfig && $retryConfig->is_enabled) ? 'checked' : ''); ?>

                            class="sr-only peer"
                        >
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>

                <div class="p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg mb-4">
                    <p class="text-blue-700 text-sm">
                        <strong>Como funciona:</strong> Se um PIX falhar no gateway principal do usuário, o sistema tentará automaticamente gerar no gateway de retentativa selecionado abaixo, evitando perder a venda.
                    </p>
                </div>

                <div class="mb-4">
                    <label for="retry_gateway_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Gateway de Retentativa Global
                    </label>
                    <select 
                        id="retry_gateway_id" 
                        name="retry_gateway_id" 
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                    >
                        <option value="">Nenhum gateway selecionado</option>
                        <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($gateway->id); ?>" <?php echo e(($retryConfig && $retryConfig->retry_gateway_id == $gateway->id) ? 'selected' : ''); ?>>
                                <?php echo e($gateway->name); ?> <?php echo e($gateway->is_default ? '(Padrão)' : ''); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        Este gateway será usado como alternativa quando o gateway principal falhar
                    </p>
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição (opcional)
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        rows="3"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Adicione uma descrição ou notas sobre esta configuração..."
                    ><?php echo e($retryConfig->description ?? ''); ?></textarea>
                </div>

                <div class="p-4 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                    <p class="text-yellow-700 text-sm">
                        <strong>Importante:</strong> Usuários podem ter configuração individual de retentativa que sobrescreve esta configuração global. Configure isso na edição de cada usuário.
                    </p>
                </div>
            </div>

            <div class="flex justify-end">
                <button 
                    type="submit" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200"
                >
                    Salvar Configuração
                </button>
            </div>
        </form>
    </div>

    <!-- Status da Retentativa -->
    <div class="mt-6 bg-white rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Atual</h3>
        
        <div class="space-y-3">
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Retentativa Global:</span>
                <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo e(($retryConfig && $retryConfig->is_enabled) ? 'bg-green-500/10 text-green-700' : 'bg-gray-500/10 text-gray-700'); ?>">
                    <?php echo e(($retryConfig && $retryConfig->is_enabled) ? 'Ativada' : 'Desativada'); ?>

                </span>
            </div>
            
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Gateway de Retentativa:</span>
                <span class="text-gray-900 font-medium">
                    <?php echo e(($retryConfig && $retryConfig->retryGateway) ? $retryConfig->retryGateway->name : 'Nenhum'); ?>

                </span>
            </div>
            
            <div class="flex items-center justify-between py-2">
                <span class="text-gray-600">Última Atualização:</span>
                <span class="text-gray-900 font-medium">
                    <?php echo e($retryConfig && $retryConfig->updated_at ? $retryConfig->updated_at->format('d/m/Y H:i') : 'Nunca'); ?>

                </span>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/retry/index.blade.php ENDPATH**/ ?>