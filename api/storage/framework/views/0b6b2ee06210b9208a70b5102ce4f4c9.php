<?php $__env->startSection('page-title', 'Avisos do Sistema'); ?>
<?php $__env->startSection('page-description', 'Gerencie avisos e notificações para os usuários'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <?php if(isset($migration_warning) && $migration_warning): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4">
            <strong>Atenção:</strong> As tabelas necessárias não existem. Execute as migrations: <code class="bg-yellow-100 px-2 py-1 rounded">php artisan migrate</code>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <!-- Criar Novo Aviso -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Criar Novo Aviso</h2>
        </div>

        <form action="<?php echo e(route('admin.white-label.announcements.store')); ?>" method="POST" class="p-6">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Título *
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ex: Manutenção Programada"
                    >
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo *
                    </label>
                    <select 
                        id="type" 
                        name="type" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="info">Informação</option>
                        <option value="success">Sucesso</option>
                        <option value="warning">Aviso</option>
                        <option value="error">Erro</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Mensagem *
                </label>
                <textarea 
                    id="message" 
                    name="message" 
                    rows="4"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Digite a mensagem do aviso..."
                ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="starts_at" class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Início
                    </label>
                    <input 
                        type="datetime-local" 
                        id="starts_at" 
                        name="starts_at"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                </div>

                <div>
                    <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-2">
                        Data de Fim
                    </label>
                    <input 
                        type="datetime-local" 
                        id="ends_at" 
                        name="ends_at"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                </div>
            </div>

            <div class="mb-4">
                <label class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1"
                        checked
                        class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                    >
                    <span class="ml-2 text-sm text-gray-700">Ativo</span>
                </label>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    Criar Aviso
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Avisos -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Avisos Cadastrados</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($announcement->title); ?></div>
                                <div class="text-xs text-gray-500"><?php echo e(Str::limit($announcement->message, 50)); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                    $typeColors = [
                                        'info' => 'bg-blue-100 text-blue-800',
                                        'success' => 'bg-green-100 text-green-800',
                                        'warning' => 'bg-yellow-100 text-yellow-800',
                                        'error' => 'bg-red-100 text-red-800',
                                    ];
                                    $typeLabels = [
                                        'info' => 'Informação',
                                        'success' => 'Sucesso',
                                        'warning' => 'Aviso',
                                        'error' => 'Erro',
                                    ];
                                ?>
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($typeColors[$announcement->type] ?? 'bg-gray-100 text-gray-800'); ?>">
                                    <?php echo e($typeLabels[$announcement->type] ?? $announcement->type); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($announcement->is_active): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php if($announcement->starts_at || $announcement->ends_at): ?>
                                    <div>
                                        <?php if($announcement->starts_at): ?>
                                            <div>Início: <?php echo e(\Carbon\Carbon::parse($announcement->starts_at)->format('d/m/Y H:i')); ?></div>
                                        <?php endif; ?>
                                        <?php if($announcement->ends_at): ?>
                                            <div>Fim: <?php echo e(\Carbon\Carbon::parse($announcement->ends_at)->format('d/m/Y H:i')); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">Sem período definido</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="<?php echo e(route('admin.white-label.announcements.destroy', $announcement->id)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este aviso?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                Nenhum aviso cadastrado
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/white-label/announcements.blade.php ENDPATH**/ ?>