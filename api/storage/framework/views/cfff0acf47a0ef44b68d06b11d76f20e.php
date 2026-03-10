<?php $__env->startSection('title', 'Gerenciar Saques'); ?>
<?php $__env->startSection('page-title', 'Gerenciar Saques'); ?>
<?php $__env->startSection('page-description', 'Visualize e aprove solicitações de saque dos usuários'); ?>

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

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <!-- Pending Withdrawals -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-yellow-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="<?php echo e(route('admin.withdrawals.index', ['status' => 'pending'])); ?>" class="text-xs text-yellow-500 hover:text-yellow-400">Ver todos</a>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($pendingCount)); ?></p>
            <p class="text-xs text-gray-600">Saques Pendentes</p>
        </div>

        <!-- Processing Withdrawals -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <a href="<?php echo e(route('admin.withdrawals.index', ['status' => 'processing'])); ?>" class="text-xs text-blue-500 hover:text-blue-600">Ver todos</a>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($processingCount)); ?></p>
            <p class="text-xs text-gray-600">Em Processamento</p>
        </div>

        <!-- Completed Withdrawals -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <a href="<?php echo e(route('admin.withdrawals.index', ['status' => 'completed'])); ?>" class="text-xs text-green-500 hover:text-green-600">Ver todos</a>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($completedCount)); ?></p>
            <p class="text-xs text-gray-600">Saques Concluídos</p>
        </div>

        <!-- Failed Withdrawals -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-green-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <a href="<?php echo e(route('admin.withdrawals.index', ['status' => 'failed'])); ?>" class="text-xs text-green-500 hover:text-green-600">Ver todos</a>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($failedCount)); ?></p>
            <p class="text-xs text-gray-600">Saques Falhos</p>
        </div>

        <!-- Total Amount -->
        <div class="bg-white rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-purple-500/10 rounded-lg">
                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <span class="text-xs text-purple-500">Total</span>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ <?php echo e(number_format($totalAmount, 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Valor Total Sacado</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Buscar por ID, usuário ou chave..."
                    value="<?php echo e(request('search')); ?>"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 text-sm"
                >
            </div>
            
            <div>
                <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm">
                    <option value="">Todos os Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pendente</option>
                    <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>Processando</option>
                    <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Concluído</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelado</option>
                    <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Falhou</option>
                </select>
            </div>
            
            <div>
                <select name="type" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm">
                    <option value="">Todos os Tipos</option>
                    <option value="manual" <?php echo e(request('type') == 'manual' ? 'selected' : ''); ?>>Manual</option>
                    <option value="automatic" <?php echo e(request('type') == 'automatic' ? 'selected' : ''); ?>>Automático</option>
                </select>
            </div>
            
            <div>
                <input 
                    type="date" 
                    name="date_from" 
                    value="<?php echo e(request('date_from')); ?>"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm"
                    placeholder="Data inicial"
                >
            </div>
            
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Withdrawals Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Chave PIX</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $withdrawals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdrawal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="text-gray-900 text-sm"><?php echo e($withdrawal->created_at->format('d/m/Y')); ?></div>
                                <div class="text-gray-600 text-xs"><?php echo e($withdrawal->created_at->format('H:i')); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-mono text-sm"><?php echo e(substr($withdrawal->withdrawal_id, 0, 12)); ?>...</span>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e($withdrawal->user->name); ?></p>
                                    <p class="text-gray-600 text-sm"><?php echo e($withdrawal->user->email); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 font-medium"><?php echo e($withdrawal->formatted_amount); ?></p>
                                    <p class="text-gray-600 text-sm">Taxa: <?php echo e($withdrawal->formatted_fee); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-gray-900 text-sm"><?php echo e($withdrawal->pix_type_label); ?></p>
                                    <p class="text-gray-600 text-xs"><?php echo e(Str::mask($withdrawal->pix_key, '*', 4)); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo e($withdrawal->user->withdrawal_type === 'automatic' ? 'bg-green-500/10 text-green-600 border-green-500/20' : 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20'); ?> border">
                                    <?php echo e($withdrawal->user->withdrawal_type === 'automatic' ? 'Automático' : 'Manual'); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                    $statusConfig = [
                                        'pending' => ['label' => 'Pendente', 'class' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'],
                                        'processing' => ['label' => 'Processando', 'class' => 'bg-blue-500/20 text-blue-600 border-blue-500/30'],
                                        'completed' => ['label' => 'Concluído', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                                        'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-gray-500/20 text-gray-600 border-gray-500/30'],
                                        'failed' => ['label' => 'Falhou', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                                    ];
                                    $config = $statusConfig[$withdrawal->status] ?? ['label' => ucfirst($withdrawal->status), 'class' => 'bg-gray-500/20 text-gray-600 border-gray-500/30'];
                                ?>
                                <span class="px-3 py-1 text-xs font-medium rounded-full border <?php echo e($config['class']); ?>">
                                    <?php echo e($config['label']); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="<?php echo e(route('admin.withdrawals.show', $withdrawal)); ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                                        Ver
                                    </a>
                                    <?php if($withdrawal->status === 'pending' && $withdrawal->user->withdrawal_type === 'manual'): ?>
                                        <button 
                                            onclick="openApprovalModal(<?php echo e($withdrawal->id); ?>)"
                                            class="text-green-600 hover:text-green-700 text-sm"
                                        >
                                            Aprovar
                                        </button>
                                        <button 
                                            onclick="openRejectionModal(<?php echo e($withdrawal->id); ?>)"
                                            class="text-green-600 hover:text-green-700 text-sm"
                                        >
                                            Rejeitar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-600">
                                Nenhum saque encontrado
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($withdrawals->hasPages()): ?>
            <div class="px-6 py-3 border-t border-gray-200">
                <?php echo e($withdrawals->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Aprovar Saque</h3>
                    <button onclick="closeApprovalModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <p class="text-gray-700 mb-4">
                    Tem certeza que deseja aprovar este saque? Esta ação irá processar o pagamento imediatamente.
                </p>

                <form id="approvalForm" action="" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeApprovalModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar Aprovação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectionModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Rejeitar Saque</h3>
                    <button onclick="closeRejectionModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="rejectionForm" action="" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    
                    <div>
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da Rejeição *
                        </label>
                        <textarea 
                            id="rejection_reason" 
                            name="rejection_reason" 
                            rows="3" 
                            required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Informe o motivo da rejeição"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeRejectionModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar Rejeição
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentWithdrawalId = null;

function openApprovalModal(withdrawalId) {
    currentWithdrawalId = withdrawalId;
    document.getElementById('approvalForm').action = `/admin/withdrawals/${withdrawalId}/approve`;
    document.getElementById('approvalModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openRejectionModal(withdrawalId) {
    currentWithdrawalId = withdrawalId;
    document.getElementById('rejectionForm').action = `/admin/withdrawals/${withdrawalId}/reject`;
    document.getElementById('rejectionModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeRejectionModal() {
    document.getElementById('rejectionModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/admin/withdrawals/index.blade.php ENDPATH**/ ?>