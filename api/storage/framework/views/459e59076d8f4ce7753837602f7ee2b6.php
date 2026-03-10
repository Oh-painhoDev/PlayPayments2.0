<?php $__env->startSection('title', 'Visão Geral de Retenções'); ?>
<?php $__env->startSection('page-title', 'Visão Geral de Retenções'); ?>
<?php $__env->startSection('page-description', 'Visualize todas as configurações de retenção e transações retidas'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <!-- Users with Retention -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['users_with_retention'])); ?></p>
            <p class="text-xs text-gray-600">Usuários com Retenção</p>
        </div>

        <!-- Individual Configs -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['individual_configs'])); ?></p>
            <p class="text-xs text-gray-600">Configurações Individuais</p>
        </div>

        <!-- Total Retained -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['total_retained'])); ?></p>
            <p class="text-xs text-gray-600">Transações Retidas</p>
        </div>

        <!-- Total Amount -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-yellow-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ <?php echo e(number_format($stats['total_amount'], 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Valor Líquido Retido</p>
        </div>

        <!-- Active Configs -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-emerald-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['active_configs'])); ?></p>
            <p class="text-xs text-gray-600">Configurações Ativas</p>
        </div>
    </div>

    <!-- Users with Retention Table -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Usuários com Retenção Ativa</h3>
            <div class="flex space-x-2">
                <button 
                    onclick="openGlobalConfigModal()"
                    class="bg-gray-600 hover:bg-gray-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                >
                    Configuração Global
                </button>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors">
                    Gerenciar Usuários
                </a>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo Config.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Configuração</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Progresso Ciclo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Progresso Retenção</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Total Retido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Valor Líquido</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Saldo Atual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $usersWithRetention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $user = $userData['user'];
                            $config = $userData['config'];
                            $progress = $userData['progress'];
                        ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e($user->name); ?></p>
                                    <p class="text-gray-600 text-sm"><?php echo e($user->email); ?></p>
                                    <p class="text-gray-500 text-xs"><?php echo e($user->formatted_document); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-blue-500/10 text-blue-600 border border-blue-500/20">
                                    Individual
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm">
                                    <?php echo e($config->quantity_cycle); ?> de <?php echo e($config->quantity_cycle); ?>

                                </div>
                                <div class="text-gray-600 text-xs">
                                    Reter <?php echo e($config->quantity_retained); ?> a cada <?php echo e($config->quantity_cycle); ?> pagas
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm"><?php echo e($progress['paid_count']); ?>/<?php echo e($config->quantity_cycle); ?></div>
                                <div class="w-full bg-gray-800 rounded-full h-1.5 mt-1">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo e($progress['cycle_progress']); ?>%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm"><?php echo e($progress['retained_count']); ?>/<?php echo e($config->quantity_retained); ?></div>
                                <div class="w-full bg-gray-800 rounded-full h-1.5 mt-1">
                                    <div class="bg-purple-600 h-1.5 rounded-full" style="width: <?php echo e($progress['retained_progress']); ?>%"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium"><?php echo e($userData['total_retained']); ?></div>
                                <div class="text-gray-600 text-xs">transações</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-green-600 font-medium">R$ <?php echo e(number_format($userData['total_retained_amount'], 2, ',', '.')); ?></div>
                                <div class="text-gray-600 text-xs">valor líquido</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium"><?php echo e($user->formatted_wallet_balance); ?></div>
                                <div class="text-gray-600 text-xs">saldo atual</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                                        Ver Perfil
                                    </a>
                                    <a href="<?php echo e(route('admin.users.retention', $user)); ?>" class="text-purple-400 hover:text-purple-300 text-sm">
                                        Configurar
                                    </a>
                                    <form action="<?php echo e(route('admin.users.retention.reset', $user)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja reiniciar o ciclo deste usuário?');">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-green-600 hover:text-green-700 text-sm">
                                            Reset
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-600">
                                Nenhum usuário com retenção ativa encontrado
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Retained Transactions -->
    <div class="bg-white rounded-lg border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Transações Retidas</h3>
        </div>

        <!-- Filters -->
        <div class="bg-gray-50 rounded-lg border border-gray-300 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="Buscar por ID, usuário..."
                        value="<?php echo e(request('search')); ?>"
                        class="w-full px-3 py-2 bg-white border border-gray-600 rounded-lg text-gray-900 placeholder-gray-400 text-sm"
                    >
                </div>
                
                <div>
                    <select name="user_id" class="w-full px-3 py-2 bg-white border border-gray-600 rounded-lg text-gray-900 text-sm">
                        <option value="">Todos os Usuários</option>
                        <?php $__currentLoopData = $usersWithRetention; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userData): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($userData['user']->id); ?>" <?php echo e(request('user_id') == $userData['user']->id ? 'selected' : ''); ?>>
                                <?php echo e($userData['user']->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                
                <div>
                    <input 
                        type="date" 
                        name="date_from" 
                        value="<?php echo e(request('date_from')); ?>"
                        class="w-full px-3 py-2 bg-white border border-gray-600 rounded-lg text-gray-900 text-sm"
                        placeholder="Data inicial"
                    >
                </div>
                
                <div>
                    <input 
                        type="date" 
                        name="date_to" 
                        value="<?php echo e(request('date_to')); ?>"
                        class="w-full px-3 py-2 bg-white border border-gray-600 rounded-lg text-gray-900 text-sm"
                        placeholder="Data final"
                    >
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Retained Transactions Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $retainedTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm"><?php echo e($transaction->created_at->format('d/m/Y')); ?></div>
                                <div class="text-gray-600 text-xs"><?php echo e($transaction->created_at->format('H:i')); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-mono text-sm"><?php echo e(substr($transaction->transaction_id, 0, 12)); ?>...</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e($transaction->user->name); ?></p>
                                    <p class="text-gray-600 text-sm"><?php echo e($transaction->user->email); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e($transaction->formatted_amount); ?></p>
                                    <p class="text-gray-600 text-sm">Líquido: <?php echo e($transaction->formatted_net_amount); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-gray-800 text-gray-700 rounded">
                                    <?php echo e(strtoupper(str_replace('_', ' ', $transaction->payment_method))); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-green-500/20 text-green-600 border-green-500/30">
                                    Pago
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded bg-yellow-500/20 text-yellow-400 border-yellow-500/30">
                                    Pendente
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="<?php echo e(route('admin.setup.retained-sales.details', $transaction->transaction_id)); ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                                        Ver
                                    </a>
                                    <form action="<?php echo e(route('admin.setup.retained-sales.return', $transaction->transaction_id)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja devolver esta venda para o usuário?');">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-green-600 hover:text-green-700 text-sm">
                                            Devolver
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma transação retida encontrada
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($retainedTransactions->hasPages()): ?>
            <div class="px-6 py-3 border-t border-gray-200 mt-4">
                <?php echo e($retainedTransactions->appends(request()->query())->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Global Config Modal -->
<div id="globalConfigModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Configuração Global</h3>
                    <button onclick="closeGlobalConfigModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-2">Configuração</h4>
                        <p class="text-gray-700 text-sm"><?php echo e($globalConfig ? $globalConfig->quantity_cycle . ' de ' . $globalConfig->quantity_cycle : 'Não configurado'); ?></p>
                        <p class="text-gray-600 text-xs"><?php echo e($globalConfig ? 'Reter ' . $globalConfig->quantity_retained . ' a cada ' . $globalConfig->quantity_cycle . ' pagas' : ''); ?></p>
                    </div>
                    
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-2">Modo de Seleção</h4>
                        <p class="text-gray-700 text-sm">
                            <?php if($globalConfig): ?>
                                <?php if($globalConfig->selection_mode === 'all'): ?>
                                    Usuários Selecionados
                                <?php elseif($globalConfig->selection_mode === 'selected'): ?>
                                    Usuários Selecionados
                                <?php else: ?>
                                    Todos Exceto Selecionados
                                <?php endif; ?>
                            <?php else: ?>
                                Não configurado
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-2">Usuários Selecionados</h4>
                        <p class="text-gray-700 text-sm"><?php echo e($globalConfig ? count($globalConfig->selected_users ?? []) : 0); ?></p>
                    </div>
                    
                    <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                        <h4 class="text-gray-900 font-medium mb-2">Último Reset</h4>
                        <p class="text-gray-700 text-sm"><?php echo e($globalConfig && $globalConfig->last_reset_at ? $globalConfig->last_reset_at->format('d/m/Y H:i') : 'Nunca'); ?></p>
                    </div>
                </div>
                
                <div class="flex justify-end mt-6">
                    <button 
                        onclick="closeGlobalConfigModal()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openGlobalConfigModal() {
    document.getElementById('globalConfigModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeGlobalConfigModal() {
    document.getElementById('globalConfigModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/admin/setup/retention-overview.blade.php ENDPATH**/ ?>