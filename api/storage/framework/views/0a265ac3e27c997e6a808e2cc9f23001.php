<?php $__env->startSection('title', 'Gerenciar Metas'); ?>
<?php $__env->startSection('page-title', 'Gerenciar Metas'); ?>
<?php $__env->startSection('page-description', 'Crie e gerencie metas pessoais para usuários'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">🎯 Gerenciar Metas</h1>
                <p class="text-purple-100">Crie e gerencie metas pessoais e privadas de faturamento, vendas e mais para os usuários</p>
            </div>
            <a href="<?php echo e(route('admin.goals.create')); ?>" class="bg-white hover:bg-gray-100 text-purple-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Nova Meta
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-4 flex items-center">
            <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-green-800 font-semibold"><?php echo e(session('success')); ?></p>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-red-800 font-semibold"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4">
            <h4 class="font-semibold text-red-800 mb-2">Erros encontrados:</h4>
            <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-xl border-2 border-gray-200 p-4">
        <form method="GET" action="<?php echo e(route('admin.goals.index')); ?>" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-bold text-gray-900 mb-2">Usuário</label>
                <select name="user_id" class="w-full px-4 py-2 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todas as metas</option>
                    <option value="global" <?php echo e(request('user_id') === 'global' ? 'selected' : ''); ?>>
                        🌍 Apenas Metas Globais
                    </option>
                    <option value="" disabled>─────────────────</option>
                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>" <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                            👤 <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)
                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-bold text-gray-900 mb-2">Tipo</label>
                <select name="type" class="w-full px-4 py-2 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todos os tipos</option>
                    <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($type); ?>" <?php echo e(request('type') == $type ? 'selected' : ''); ?>>
                            <?php echo e(ucfirst($type)); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-bold text-gray-900 mb-2">Status</label>
                <select name="is_active" class="w-full px-4 py-2 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    <option value="">Todos</option>
                    <option value="1" <?php echo e(request('is_active') === '1' ? 'selected' : ''); ?>>Ativas</option>
                    <option value="0" <?php echo e(request('is_active') === '0' ? 'selected' : ''); ?>>Inativas</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl">
                    Filtrar
                </button>
                <a href="<?php echo e(route('admin.goals.index')); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-xl font-semibold transition-all">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Metas -->
    <div class="bg-white rounded-xl border-2 border-gray-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Atual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progresso</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prêmio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $goals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $goal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            // Para metas pessoais, calcular progresso do usuário da meta
                            // Para metas globais, não calcular progresso (é individual por usuário)
                            if ($goal->user_id) {
                                $currentValue = $goal->current_value;
                                $percentage = $goal->percentage;
                            } else {
                                // Meta global: progresso é individual, não mostramos no admin
                                $currentValue = null;
                                $percentage = null;
                            }
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900 text-center">
                                    <?php echo e($goal->display_order ?? 0); ?>

                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900"><?php echo e($goal->name); ?></div>
                                <?php if($goal->description): ?>
                                    <div class="text-xs text-gray-500"><?php echo e(Str::limit($goal->description, 50)); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($goal->user): ?>
                                    <div class="text-sm font-bold text-gray-900"><?php echo e($goal->user->name); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e($goal->user->email); ?></div>
                                <?php else: ?>
                                    <div class="text-sm font-bold text-purple-600">🌍 Meta Global</div>
                                    <div class="text-xs text-gray-500">Progresso individual por usuário</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e(ucfirst($goal->type)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($currentValue !== null): ?>
                                    <div class="text-sm font-bold text-gray-900">
                                        <?php if(in_array($goal->type, ['faturamento', 'vendas'])): ?>
                                            R$ <?php echo e(number_format($currentValue, 2, ',', '.')); ?>

                                        <?php else: ?>
                                            <?php echo e(number_format($currentValue, 0, ',', '.')); ?>

                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm font-bold text-gray-400 italic">
                                        Individual
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        Por usuário
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">
                                    <?php if(in_array($goal->type, ['faturamento', 'vendas'])): ?>
                                        R$ <?php echo e(number_format($goal->target_value, 2, ',', '.')); ?>

                                    <?php else: ?>
                                        <?php echo e(number_format($goal->target_value, 0, ',', '.')); ?>

                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($percentage !== null): ?>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 min-w-[100px]">
                                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" style="width: <?php echo e(min(100, $percentage)); ?>%;"></div>
                                        </div>
                                        <span class="text-sm font-bold text-gray-900"><?php echo e(number_format($percentage, 1)); ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm font-bold text-gray-400 italic">
                                        Individual
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($goal->reward_type && $goal->reward_value): ?>
                                    <div class="text-sm font-bold text-green-600">
                                        <?php if($goal->reward_type === 'cash'): ?>
                                            R$ <?php echo e(number_format($goal->reward_value, 2, ',', '.')); ?>

                                        <?php else: ?>
                                            <?php echo e(ucfirst($goal->reward_type)); ?>

                                        <?php endif; ?>
                                    </div>
                                    <?php if($goal->auto_reward): ?>
                                        <div class="text-xs text-gray-500">Automático</div>
                                    <?php else: ?>
                                        <div class="text-xs text-gray-500">Manual</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">Sem prêmio</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    <?php if($goal->period === 'monthly'): ?>
                                        Mensal
                                    <?php elseif($goal->period === 'yearly'): ?>
                                        Anual
                                    <?php else: ?>
                                        Personalizado
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form method="POST" action="<?php echo e(route('admin.goals.toggle-status', $goal)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="px-3 py-1 text-xs font-semibold rounded-full transition-all <?php echo e($goal->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'); ?>">
                                        <?php echo e($goal->is_active ? 'Ativa' : 'Inativa'); ?>

                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center gap-2">
                                    <?php if($goal->reward_type && $goal->reward_value && ($percentage !== null && $percentage >= 100)): ?>
                                        <?php if($goal->user): ?>
                                            <?php
                                                $hasAchieved = $goal->hasUserAchieved($goal->user->id);
                                            ?>
                                            <?php if(!$hasAchieved): ?>
                                                <form method="POST" action="<?php echo e(route('admin.goals.reward-user', $goal)); ?>" class="inline" onsubmit="return confirm('Tem certeza que deseja premiar o usuário <?php echo e($goal->user->name); ?>? O prêmio de R$ <?php echo e(number_format($goal->reward_value, 2, ',', '.')); ?> será creditado na wallet.');">
                                                    <?php echo csrf_field(); ?>
                                                    <input type="hidden" name="user_id" value="<?php echo e($goal->user->id); ?>">
                                                    <button type="submit" class="text-green-600 hover:text-green-900 transition-colors" title="Premiar Usuário">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-xs text-green-600" title="Já premiado">✓</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-xs text-purple-600 font-semibold" title="Meta Global - selecione usuário para premiar">🌍</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <a href="<?php echo e(route('admin.goals.edit', $goal)); ?>" class="text-purple-600 hover:text-purple-900 transition-colors" title="Editar">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <form method="POST" action="<?php echo e(route('admin.goals.destroy', $goal)); ?>" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta meta?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="text-red-600 hover:text-red-900 transition-colors" title="Excluir">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-semibold">Nenhuma meta encontrada</p>
                                <p class="text-sm mt-2">Crie uma nova meta para começar</p>
                                <a href="<?php echo e(route('admin.goals.create')); ?>" class="inline-block mt-4 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-xl font-semibold transition-all">
                                    Criar Meta
                                </a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if($goals->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($goals->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/goals/index.blade.php ENDPATH**/ ?>