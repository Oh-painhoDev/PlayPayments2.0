<?php $__env->startSection('title', 'Transações'); ?>
<?php $__env->startSection('page-title', 'Transações'); ?>
<?php $__env->startSection('page-description', 'Monitore todas as transações do sistema'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <!-- Filters -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <div>
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Buscar por ID, cliente..."
                    value="<?php echo e(request('search')); ?>"
                    class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm"
                >
            </div>
            
            <div>
                <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm">
                    <option value="">Todos os Status</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pendente</option>
                    <option value="processing" <?php echo e(request('status') == 'processing' ? 'selected' : ''); ?>>Processando</option>
                    <option value="authorized" <?php echo e(request('status') == 'authorized' ? 'selected' : ''); ?>>Autorizado</option>
                    <option value="paid" <?php echo e(request('status') == 'paid' ? 'selected' : ''); ?>>Pago</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelado</option>
                    <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expirado</option>
                    <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Falhou</option>
                    <option value="refunded" <?php echo e(request('status') == 'refunded' ? 'selected' : ''); ?>>Estornado</option>
                    <option value="partially_refunded" <?php echo e(request('status') == 'partially_refunded' ? 'selected' : ''); ?>>Estornado Parcial</option>
                    <option value="chargeback" <?php echo e(request('status') == 'chargeback' ? 'selected' : ''); ?>>Chargeback</option>
                </select>
            </div>
            
            <div>
                <select name="payment_method" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm">
                    <option value="">Todos os Métodos</option>
                    <option value="pix" <?php echo e(request('payment_method') == 'pix' ? 'selected' : ''); ?>>PIX</option>
                    <option value="credit_card" <?php echo e(request('payment_method') == 'credit_card' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                    <option value="bank_slip" <?php echo e(request('payment_method') == 'bank_slip' ? 'selected' : ''); ?>>Boleto</option>
                </select>
            </div>
            
            <div>
                <select name="gateway" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm">
                    <option value="">Todos os Gateways</option>
                    <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($gateway->id); ?>" <?php echo e(request('gateway') == $gateway->id ? 'selected' : ''); ?>>
                            <?php echo e($gateway->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Método</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50/50">
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
                                    <p class="text-gray-600 text-sm">Taxa: <?php echo e($transaction->formatted_fee_amount); ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs bg-gray-800 text-gray-700 rounded">
                                    <?php echo e(strtoupper(str_replace('_', ' ', $transaction->payment_method))); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 text-sm"><?php echo e($transaction->gateway ? $transaction->gateway->name : 'N/A'); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded <?php echo e($transaction->status_color); ?>">
                                    <?php echo e($transaction->status_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm">
                                <?php echo e($transaction->created_at->format('d/m/Y H:i')); ?>

                            </td>
                            <td class="px-6 py-4">
                                <a href="<?php echo e(route('admin.transactions.show', $transaction->transaction_id)); ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma transação encontrada
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($transactions->hasPages()): ?>
            <div class="px-6 py-3 border-t border-gray-200">
                <?php echo e($transactions->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/transactions/index.blade.php ENDPATH**/ ?>