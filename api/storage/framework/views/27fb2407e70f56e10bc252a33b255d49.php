<?php $__env->startSection('title', 'Lucro por Empresas'); ?>
<?php $__env->startSection('page-title', 'Lucro por Empresas'); ?>
<?php $__env->startSection('page-description', 'Visualize o lucro gerado por cada empresa cadastrada no sistema'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <!-- Total Companies -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($totals['companies'])); ?></p>
            <p class="text-xs text-gray-600">Empresas Cadastradas</p>
        </div>

        <!-- Total Sales -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ <?php echo e(number_format($totals['total_sales'], 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Vendas Totais</p>
        </div>

        <!-- Total Fees -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ <?php echo e(number_format($totals['total_fees'], 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Taxas Cobradas</p>
        </div>

        <!-- Gateway Fees -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-green-600">R$ <?php echo e(number_format($totals['total_gateway_fees'], 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Taxas de Gateway</p>
        </div>

        <!-- Total Profit -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-emerald-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-emerald-400">R$ <?php echo e(number_format($totals['total_profit'], 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Lucro Total</p>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Vendas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Taxas Cobradas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Taxas Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Lucro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Margem</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $companies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e($company->name); ?></p>
                                    <p class="text-gray-600 text-sm"><?php echo e($company->email); ?></p>
                                    <p class="text-gray-500 text-xs"><?php echo e($company->formatted_document); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded <?php echo e($company->isPessoaFisica() ? 'bg-blue-500/10 text-blue-600' : 'bg-purple-500/10 text-purple-400'); ?>">
                                    <?php echo e($company->isPessoaFisica() ? 'PF' : 'PJ'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">R$ <?php echo e(number_format($company->total_sales ?? 0, 2, ',', '.')); ?></p>
                                    <p class="text-gray-600 text-xs"><?php echo e(number_format($company->total_transactions ?? 0)); ?> transações</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium">R$ <?php echo e(number_format($company->total_fees ?? 0, 2, ',', '.')); ?></p>
                                    <p class="text-gray-600 text-xs"><?php echo e(number_format(($company->total_fees / max($company->total_sales, 0.01)) * 100, 2)); ?>%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-green-600 font-medium">R$ <?php echo e(number_format($company->gateway_fees ?? 0, 2, ',', '.')); ?></p>
                                    <p class="text-gray-600 text-xs"><?php echo e(number_format(($company->gateway_fees / max($company->total_sales, 0.01)) * 100, 2)); ?>%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-emerald-400 font-medium">R$ <?php echo e(number_format($company->profit ?? 0, 2, ',', '.')); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e(number_format(($company->profit / max($company->total_fees, 0.01)) * 100, 2)); ?>%</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="<?php echo e(route('admin.profit.show', $company)); ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                                        Ver detalhes
                                    </a>
                                    <a href="<?php echo e(route('admin.users.show', $company)); ?>" class="text-green-600 hover:text-green-700 text-sm">
                                        Perfil
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma empresa encontrada
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($companies->hasPages()): ?>
            <div class="px-6 py-3 border-t border-gray-200">
                <?php echo e($companies->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/admin/profit/index.blade.php ENDPATH**/ ?>