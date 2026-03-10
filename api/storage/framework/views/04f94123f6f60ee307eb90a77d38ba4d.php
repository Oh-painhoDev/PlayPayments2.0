<?php $__env->startSection('title', 'Configuração de Retenção Individual'); ?>
<?php $__env->startSection('page-title', 'Configuração de Retenção: ' . $user->name); ?>
<?php $__env->startSection('page-description', 'Configure a retenção individual para este usuário'); ?>

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

    <!-- User Header -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                <p class="text-gray-600"><?php echo e($user->email); ?></p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="px-2 py-1 text-xs rounded <?php echo e($user->isPessoaFisica() ? 'bg-blue-500/10 text-blue-600' : 'bg-purple-500/10 text-purple-400'); ?>">
                        <?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?>

                    </span>
                    <span class="text-gray-500 text-sm"><?php echo e($user->formatted_document); ?></span>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form action="<?php echo e(route('admin.users.retention.update', $user)); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>

                <!-- Retention Configuration -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-6">Configuração do Ciclo de Retenção Individual</h2>
                    
                    <div class="space-y-6">
                        <!-- Is Active -->
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="is_active" 
                                name="is_active" 
                                value="1" 
                                <?php echo e(old('is_active', $retentionConfig->is_active) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                            >
                            <label for="is_active" class="ml-2 text-sm text-gray-700">
                                Ativar Retenção Individual para este usuário
                            </label>
                        </div>


                        <!-- Retention Type -->
                        <div class="p-5 bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl">
                            <label class="block text-sm font-semibold text-gray-900 mb-4">
                                Tipo de Retenção *
                            </label>
                            <div class="space-y-3">
                                <div class="flex items-start p-4 border-2 border-gray-200 rounded-lg hover:border-emerald-400 transition-colors cursor-pointer">
                                    <input 
                                        type="radio" 
                                        id="retention_type_1" 
                                        name="retention_type" 
                                        value="1" 
                                        <?php echo e(old('retention_type', $user->retention_type ?? 1) == 1 ? 'checked' : ''); ?>

                                        class="h-4 w-4 mt-1 text-emerald-600 focus:ring-emerald-500 border-gray-300"
                                    >
                                    <label for="retention_type_1" class="ml-3 flex-1 cursor-pointer">
                                        <span class="block text-sm font-medium text-gray-900">Tipo 1 (Padrão)</span>
                                        <span class="block text-xs text-gray-600 mt-1">❌ Não dispara webhook de venda paga</span>
                                        <span class="block text-xs text-gray-600">❌ Não contabiliza no saldo</span>
                                    </label>
                                </div>
                                
                                <div class="flex items-start p-4 border-2 border-gray-200 rounded-lg hover:border-emerald-400 transition-colors cursor-pointer">
                                    <input 
                                        type="radio" 
                                        id="retention_type_2" 
                                        name="retention_type" 
                                        value="2" 
                                        <?php echo e(old('retention_type', $user->retention_type ?? 1) == 2 ? 'checked' : ''); ?>

                                        class="h-4 w-4 mt-1 text-emerald-600 focus:ring-emerald-500 border-gray-300"
                                    >
                                    <label for="retention_type_2" class="ml-3 flex-1 cursor-pointer">
                                        <span class="block text-sm font-medium text-gray-900">Tipo 2 (Com Webhook)</span>
                                        <span class="block text-xs text-gray-600 mt-1">✅ Dispara webhook de venda paga</span>
                                        <span class="block text-xs text-gray-600">❌ Não contabiliza no saldo</span>
                                    </label>
                                </div>
                            </div>
                            <p class="text-xs text-emerald-700 mt-3 font-medium">
                                💡 Tipo 2 permite que o merchant receba notificação da venda mas sem creditar o saldo
                            </p>
                        </div>
                        <!-- Quantity Cycle -->
                        <div>
                            <label for="quantity_cycle" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantidade de Transações Pagas no Ciclo *
                            </label>
                            <input 
                                id="quantity_cycle" 
                                name="quantity_cycle" 
                                type="number" 
                                min="1"
                                max="100"
                                required 
                                value="<?php echo e(old('quantity_cycle', $retentionConfig->quantity_cycle)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="5"
                            >
                            <p class="text-xs text-gray-500 mt-1">Número de transações pagas antes de iniciar a retenção</p>
                        </div>

                        <!-- Quantity Retained -->
                        <div>
                            <label for="quantity_retained" class="block text-sm font-medium text-gray-700 mb-2">
                                Quantidade de Transações Retidas *
                            </label>
                            <input 
                                id="quantity_retained" 
                                name="quantity_retained" 
                                type="number" 
                                min="1"
                                max="50"
                                required 
                                value="<?php echo e(old('quantity_retained', $retentionConfig->quantity_retained)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="1"
                            >
                            <p class="text-xs text-gray-500 mt-1">Número de transações a serem retidas após o ciclo</p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end mt-6 space-x-4">
                        <form action="<?php echo e(route('admin.users.retention.reset', $user)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja reiniciar o ciclo deste usuário?');">
                            <?php echo csrf_field(); ?>
                            <button 
                                type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                            >
                                Reiniciar Ciclo
                            </button>
                        </form>
                        
                        <button 
                            type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                        >
                            Salvar Configurações
                        </button>
                    </div>
                </div>
            </form>

            <!-- Cycle Progress -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Progresso do Ciclo Atual</h2>
                
                <div class="space-y-6">
                    <!-- Paid Transactions Progress -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Transações Pagas</span>
                            <span class="text-sm text-gray-900"><?php echo e($currentCycleCount); ?> / <?php echo e($retentionConfig->quantity_cycle); ?></span>
                        </div>
                        <div class="w-full bg-gray-800 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo e(min(100, ($currentCycleCount / max(1, $retentionConfig->quantity_cycle)) * 100)); ?>%"></div>
                        </div>
                    </div>
                    
                    <!-- Retained Transactions Progress -->
                    <div>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-gray-600">Transações Retidas</span>
                            <span class="text-sm text-gray-900"><?php echo e($currentRetainedCount); ?> / <?php echo e($retentionConfig->quantity_retained); ?></span>
                        </div>
                        <div class="w-full bg-gray-800 rounded-full h-2.5">
                            <div class="bg-purple-600 h-2.5 rounded-full" style="width: <?php echo e(min(100, ($currentRetainedCount / max(1, $retentionConfig->quantity_retained)) * 100)); ?>%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                    <p class="text-blue-700 text-xs">
                        <strong>Nota:</strong> O ciclo será reiniciado automaticamente quando ambas as barras de progresso estiverem completas.
                    </p>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Retention Status -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status da Retenção</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Status:</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 <?php echo e($retentionConfig->is_active ? 'bg-green-500' : 'bg-green-500'); ?> rounded-full mr-2"></div>
                            <span class="<?php echo e($retentionConfig->is_active ? 'text-green-600' : 'text-green-600'); ?> text-sm"><?php echo e($retentionConfig->is_active ? 'Ativo' : 'Inativo'); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Total Retido:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($totalRetained); ?> transações</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Valor Total Retido:</span>
                        <span class="text-gray-900 text-sm">R$ <?php echo e(number_format($totalRetainedAmount, 2, ',', '.')); ?></span>
                    </div>
                    
                    <?php if($retentionConfig->last_reset_at): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Último Reset:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($retentionConfig->last_reset_at->format('d/m/Y H:i')); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- How It Works -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Como Funciona</h3>
                
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex items-start">
                        <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">1</span>
                        <p>Configure quantas transações pagas devem ocorrer antes da retenção</p>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">2</span>
                        <p>Configure quantas transações serão retidas após o ciclo</p>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">3</span>
                        <p>O sistema permitirá normalmente as transações do ciclo</p>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">4</span>
                        <p>Em seguida, as próximas transações serão retidas automaticamente</p>
                    </div>
                    
                    <div class="flex items-start">
                        <span class="bg-blue-600 text-gray-900 rounded-full w-5 h-5 flex items-center justify-center text-xs mr-3 mt-0.5">5</span>
                        <p>O ciclo reinicia e o processo se repete</p>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                    <p class="text-yellow-300 text-xs">
                        <strong>Importante:</strong> As transações retidas aparecem como "Pendentes" para o usuário, mas como "Pagas" para o administrador.
                    </p>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas do Usuário</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Total de Vendas:</span>
                        <span class="text-gray-900 text-sm">R$ <?php echo e(number_format($userStats['total_sales'], 2, ',', '.')); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Transações Pagas:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($userStats['paid_transactions']); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Ticket Médio:</span>
                        <span class="text-gray-900 text-sm">R$ <?php echo e(number_format($userStats['average_ticket'], 2, ',', '.')); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Saldo Atual:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($user->formatted_wallet_balance); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/users/retention.blade.php ENDPATH**/ ?>