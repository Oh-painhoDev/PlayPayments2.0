<?php $__env->startSection('title', 'Templates de Infrações'); ?>
<?php $__env->startSection('page-title', 'Templates de Infrações'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 via-pink-500 to-red-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">📋 Templates de Infrações</h1>
                <p class="text-purple-100">Gerencie templates para envio em massa de infrações</p>
            </div>
            <a href="<?php echo e(route('admin.setup.dispute-templates.create')); ?>" class="bg-white hover:bg-gray-100 text-purple-700 px-6 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Novo Template
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

    <!-- Cards de Métricas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-purple-700 uppercase tracking-wide">Total</span>
                <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-green-700 uppercase tracking-wide">Ativos</span>
                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['active']); ?></p>
        </div>

        <div class="bg-gradient-to-br from-gray-50 to-slate-50 border-2 border-gray-200 rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold text-gray-700 uppercase tracking-wide">Inativos</span>
                <div class="w-10 h-10 bg-gray-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['inactive']); ?></p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm p-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                    placeholder="Buscar por nome ou descrição..."
                    class="w-full px-4 py-2 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
            </div>

            <div class="min-w-[180px]">
                <select name="dispute_type" 
                    class="w-full px-4 py-2 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">Todos os Tipos</option>
                    <option value="chargeback" <?php echo e(request('dispute_type') == 'chargeback' ? 'selected' : ''); ?>>Chargeback</option>
                    <option value="fraud" <?php echo e(request('dispute_type') == 'fraud' ? 'selected' : ''); ?>>Fraude</option>
                    <option value="unauthorized" <?php echo e(request('dispute_type') == 'unauthorized' ? 'selected' : ''); ?>>Não Autorizada</option>
                    <option value="not_received" <?php echo e(request('dispute_type') == 'not_received' ? 'selected' : ''); ?>>Não Recebido</option>
                    <option value="defective" <?php echo e(request('dispute_type') == 'defective' ? 'selected' : ''); ?>>Defeituoso</option>
                    <option value="other" <?php echo e(request('dispute_type') == 'other' ? 'selected' : ''); ?>>Outro</option>
                </select>
            </div>

            <div class="min-w-[150px]">
                <select name="risk_level" 
                    class="w-full px-4 py-2 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                    <option value="">Todos os Níveis</option>
                    <option value="LOW" <?php echo e(request('risk_level') == 'LOW' ? 'selected' : ''); ?>>Baixo</option>
                    <option value="MED" <?php echo e(request('risk_level') == 'MED' ? 'selected' : ''); ?>>Médio</option>
                    <option value="HIGH" <?php echo e(request('risk_level') == 'HIGH' ? 'selected' : ''); ?>>Alto</option>
                </select>
            </div>

            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-xl font-semibold transition-all">
                Filtrar
            </button>

            <?php if(request()->hasAny(['search', 'dispute_type', 'risk_level'])): ?>
            <a href="<?php echo e(route('admin.setup.dispute-templates.index')); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-xl font-semibold transition-all">
                Limpar
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Lista de Templates -->
    <div class="bg-white rounded-2xl border-2 border-gray-200 shadow-sm overflow-hidden">
        <?php if($templates->count() > 0): ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gradient-to-r from-purple-50 to-pink-50 border-b-2 border-purple-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Risco</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-purple-700 uppercase tracking-wider">Criado em</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-purple-700 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900"><?php echo e($template->name); ?></p>
                                    <?php if($template->description): ?>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo e(Str::limit($template->description, 50)); ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    <?php echo e($template->getDisputeTypeLabel()); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php
                                    $riskColors = [
                                        'LOW' => 'bg-green-100 text-green-800',
                                        'MED' => 'bg-orange-100 text-orange-800',
                                        'HIGH' => 'bg-red-100 text-red-800',
                                    ];
                                ?>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full <?php echo e($riskColors[$template->risk_level] ?? 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($template->getRiskLevelLabel()); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick="toggleStatus(<?php echo e($template->id); ?>, this)" 
                                    class="px-3 py-1 text-xs font-semibold rounded-full <?php echo e($template->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($template->is_active ? 'Ativo' : 'Inativo'); ?>

                                </button>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo e($template->created_at->format('d/m/Y H:i')); ?>

                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="<?php echo e(route('admin.setup.dispute-templates.edit', $template)); ?>" 
                                    class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-all text-xs font-semibold">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </a>
                                <form action="<?php echo e(route('admin.setup.dispute-templates.destroy', $template)); ?>" method="POST" class="inline-block" 
                                    onsubmit="return confirm('Tem certeza que deseja excluir este template?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" 
                                        class="inline-flex items-center px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-all text-xs font-semibold">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Excluir
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($templates->links()); ?>

            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum template encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Comece criando um novo template de infração.</p>
                <div class="mt-6">
                    <a href="<?php echo e(route('admin.setup.dispute-templates.create')); ?>" 
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-all">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Criar Template
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleStatus(templateId, button) {
    fetch(`/admin/setup/dispute-templates/${templateId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_active) {
                button.className = 'px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800';
                button.textContent = 'Ativo';
            } else {
                button.className = 'px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800';
                button.textContent = 'Inativo';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao alternar status');
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/admin/dispute-templates/index.blade.php ENDPATH**/ ?>