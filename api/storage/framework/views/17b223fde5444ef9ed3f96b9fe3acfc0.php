<?php $__env->startSection('title', 'Infrações'); ?>
<?php $__env->startSection('page-title', 'Gerenciar Infrações'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">⚠️ Gerenciar Infrações</h1>
                <p class="text-green-100">Analise defesas, aceite ou rejeite disputas de clientes</p>
            </div>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('admin.setup.disputes.bulk-create')); ?>" class="bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Disparar em Massa
                </a>
                <a href="<?php echo e(route('admin.setup.disputes.create')); ?>" class="bg-white hover:bg-gray-100 text-green-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Nova Infração
                </a>
            </div>
        </div>
    </div>

    <!-- Cards de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Total</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Pendentes</span>
                <div class="w-10 h-10 bg-yellow-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['pending']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Respondidas</span>
                <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['responded']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Defendidas</span>
                <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['defended']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Rejeitadas</span>
                <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['rejected']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Reembolsadas</span>
                <div class="w-10 h-10 bg-gray-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['refunded']); ?></p>
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

    <?php if($errors->any()): ?>
        <div class="bg-red-50 border-2 border-red-200 rounded-2xl p-4">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <p class="text-red-800 font-semibold"><?php echo e($error); ?></p>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 shadow-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
            <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
            </span>
            Filtros
        </h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                <select name="status" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
                    <option value="">Todos os Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pendente</option>
                    <option value="responded" <?php echo e(request('status') == 'responded' ? 'selected' : ''); ?>>Respondido</option>
                    <option value="defended" <?php echo e(request('status') == 'defended' ? 'selected' : ''); ?>>Defendido</option>
                    <option value="defense_rejected" <?php echo e(request('status') == 'defense_rejected' ? 'selected' : ''); ?>>Rejeitado</option>
                    <option value="refunded" <?php echo e(request('status') == 'refunded' ? 'selected' : ''); ?>>Reembolsado</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="ID, usuário, email..." class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all">
            </div>

            <div class="flex items-end space-x-3 md:col-span-2">
                <button type="submit" class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg flex-1">
                    Filtrar
                </button>
                <a href="<?php echo e(route('admin.setup.disputes')); ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-semibold transition-all">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Lista de Infrações -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-gray-50 to-white border-b-2 border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <span class="w-8 h-8 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </span>
                Todas as Infrações
            </h3>
        </div>

        <?php if($disputes->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b-2 border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">ID Infração</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Usuário</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Transação</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Risco</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $disputes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dispute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm font-semibold text-gray-900"><?php echo e($dispute->dispute_id); ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo e($dispute->user->name); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo e($dispute->user->email); ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-mono text-sm text-gray-600"><?php echo e($dispute->transaction->transaction_id ?? 'N/A'); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-bold text-gray-900">R$ <?php echo e(number_format($dispute->amount, 2, ',', '.')); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($dispute->risk_level === 'LOW'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Baixo</span>
                                    <?php elseif($dispute->risk_level === 'MED'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Médio</span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Alto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($dispute->status === 'pending'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">🕐 Pendente</span>
                                    <?php elseif($dispute->status === 'responded'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">📝 Respondido</span>
                                    <?php elseif($dispute->status === 'defended'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">✅ Defendido</span>
                                    <?php elseif($dispute->status === 'defense_rejected'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">❌ Defesa Rejeitada</span>
                                    <?php elseif($dispute->status === 'refunded'): ?>
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">💸 Reembolsado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="<?php echo e(route('admin.setup.disputes.show', $dispute)); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition-all inline-block">
                                        👁️ Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t-2 border-gray-200">
                <?php echo e($disputes->links()); ?>

            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-xl font-semibold text-gray-600 mb-2">Nenhuma infração encontrada</p>
                <p class="text-gray-500">Não há infrações registradas no sistema.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/disputes/index.blade.php ENDPATH**/ ?>