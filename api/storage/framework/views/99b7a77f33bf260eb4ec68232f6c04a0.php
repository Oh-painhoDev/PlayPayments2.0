

<?php $__env->startSection('page-title', 'Gerenciar UTMify'); ?>
<?php $__env->startSection('page-description', 'Gerencie as integrações UTMify dos usuários'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
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

    <?php if($errors->any()): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Criar Nova Integração -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Adicionar Integração UTMify</h2>
            <p class="text-sm text-gray-600 mt-1">Configure uma nova integração UTMify para um usuário</p>
        </div>

        <form action="<?php echo e(route('admin.white-label.utmify.store')); ?>" method="POST" class="p-6">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Usuário
                    </label>
                    <select 
                        id="user_id" 
                        name="user_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="global">🌍 Global (Todos os PIX do servidor)</option>
                        <option value="" disabled>─────────────────</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>">👤 <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>Global:</strong> Captura TODOS os PIX gerados no servidor, de qualquer usuário.<br>
                        <strong>Usuário específico:</strong> Apenas para transações desse usuário.
                    </p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome da Integração *
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="Ex: Integração Principal"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="api_token" class="block text-sm font-medium text-gray-700 mb-2">
                        API Token *
                    </label>
                    <input 
                        type="text" 
                        id="api_token" 
                        name="api_token" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm"
                        placeholder="Token da API UTMify"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>Obrigatório:</strong> Obtenha o token em: <a href="https://utmify.com.br/register" target="_blank" class="text-green-600 hover:underline">utmify.com.br</a><br>
                        <strong>Nota:</strong> Apenas o API Token é necessário. O Pixel ID é opcional e não é necessário para o funcionamento.
                    </p>
                </div>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="trigger_on_creation" 
                            value="1"
                            checked
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Acionar na Criação</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Envia quando PIX é gerado</p>
                </div>

                <div>
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="trigger_on_payment" 
                            value="1"
                            checked
                            class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Acionar no Pagamento</span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1">Envia quando PIX é pago</p>
                </div>

                <div>
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
                    <p class="text-xs text-gray-500 mt-1">Integração habilitada</p>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    Adicionar Integração
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Integrações -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Integrações UTMify</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Token</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Configurações</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($integration->isGlobal()): ?>
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🌍</span>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">Global</div>
                                            <div class="text-xs text-gray-500">Todos os usuários</div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($integration->user?->name ?? 'N/A'); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($integration->user?->email ?? 'N/A'); ?></div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($integration->name); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-mono text-gray-600"><?php echo e(substr($integration->api_token, 0, 20)); ?>...</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1">
                                    <?php if($integration->trigger_on_creation): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Criação
                                        </span>
                                    <?php endif; ?>
                                    <?php if($integration->trigger_on_payment): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Pagamento
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($integration->is_active): ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo e($integration->created_at->format('d/m/Y H:i')); ?>

                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button 
                                    onclick="openEditModal(<?php echo e($integration->id); ?>, '<?php echo e(addslashes($integration->name)); ?>', '<?php echo e($integration->api_token); ?>', <?php echo e($integration->trigger_on_creation ? 'true' : 'false'); ?>, <?php echo e($integration->trigger_on_payment ? 'true' : 'false'); ?>, <?php echo e($integration->is_active ? 'true' : 'false'); ?>, <?php echo e($integration->isGlobal() ? 'true' : 'false'); ?>, <?php echo e($integration->user_id ?? 'null'); ?>)"
                                    class="text-green-600 hover:text-green-900 mr-3"
                                >
                                    Editar
                                </button>
                                <form action="<?php echo e(route('admin.white-label.utmify.destroy', $integration->id)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta integração?');">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                Nenhuma integração UTMify cadastrada
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($integrations->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($integrations->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Editar Integração UTMify</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="editForm" action="" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <div class="mb-4">
                    <label for="edit_user_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de Integração
                    </label>
                    <select 
                        id="edit_user_id" 
                        name="user_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    >
                        <option value="global">🌍 Global (Todos os PIX do servidor)</option>
                        <option value="" disabled>─────────────────</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>">👤 <?php echo e($user->name); ?> (<?php echo e($user->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>Global:</strong> Captura TODOS os PIX gerados no servidor.<br>
                        <strong>Usuário específico:</strong> Apenas para transações desse usuário.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome da Integração *
                        </label>
                        <input 
                            type="text" 
                            id="edit_name" 
                            name="name" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        >
                    </div>

                </div>

                <div class="mb-4">
                    <label for="edit_api_token" class="block text-sm font-medium text-gray-700 mb-2">
                        API Token *
                    </label>
                    <input 
                        type="text" 
                        id="edit_api_token" 
                        name="api_token" 
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        <strong>Nota:</strong> Apenas o API Token é necessário. Nenhum Pixel ID é necessário para o funcionamento.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="edit_trigger_on_creation" 
                                name="trigger_on_creation" 
                                value="1"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">Acionar na Criação</span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="edit_trigger_on_payment" 
                                name="trigger_on_payment" 
                                value="1"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">Acionar no Pagamento</span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                id="edit_is_active" 
                                name="is_active" 
                                value="1"
                                class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                            >
                            <span class="ml-2 text-sm text-gray-700">Ativo</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openEditModal(id, name, apiToken, triggerOnCreation, triggerOnPayment, isActive, isGlobal, userId) {
    document.getElementById('editForm').action = '<?php echo e(route("admin.white-label.utmify.update", ":id")); ?>'.replace(':id', id);
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_api_token').value = apiToken;
    document.getElementById('edit_trigger_on_creation').checked = triggerOnCreation;
    document.getElementById('edit_trigger_on_payment').checked = triggerOnPayment;
    document.getElementById('edit_is_active').checked = isActive;
    
    // Configurar user_id - se for global, usar 'global', senão usar o user_id
    if (isGlobal || userId === null) {
        document.getElementById('edit_user_id').value = 'global';
    } else {
        document.getElementById('edit_user_id').value = userId;
    }
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

// Close modal on outside click
document.getElementById('editModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeEditModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/admin/white-label/utmify.blade.php ENDPATH**/ ?>