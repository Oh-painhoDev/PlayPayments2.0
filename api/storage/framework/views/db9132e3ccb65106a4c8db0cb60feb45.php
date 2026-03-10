<?php $__env->startSection('page-title', 'Personalização White Label'); ?>
<?php $__env->startSection('page-description', 'Configure favicon, cores e banners do sistema'); ?>

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

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Configurações de Personalização</h2>
            <p class="text-sm text-gray-600 mt-1">Personalize a aparência do sistema</p>
        </div>

        <form action="<?php echo e(route('admin.white-label.branding.update')); ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <?php echo csrf_field(); ?>

            <!-- Cor Principal -->
            <div class="mb-6">
                <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">
                    Cor Principal
                </label>
                <div class="flex items-center gap-4">
                    <input 
                        type="color" 
                        id="primary_color" 
                        name="primary_color" 
                        value="<?php echo e($primary_color); ?>" 
                        class="h-10 w-20 rounded border border-gray-300 cursor-pointer"
                    >
                    <input 
                        type="text" 
                        value="<?php echo e($primary_color); ?>" 
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        readonly
                        id="primary_color_text"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">Cor utilizada em botões, links e elementos de destaque</p>
            </div>

            <!-- Favicon -->
            <div class="mb-6">
                <label for="favicon" class="block text-sm font-medium text-gray-700 mb-2">
                    Favicon
                </label>
                <?php if($favicon): ?>
                    <div class="mb-3">
                        <img src="<?php echo e(asset('storage/' . $favicon)); ?>" alt="Favicon atual" class="h-16 w-16 border border-gray-300 rounded">
                    </div>
                <?php endif; ?>
                <input 
                    type="file" 
                    id="favicon" 
                    name="favicon" 
                    accept=".ico,.png,.svg"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                >
                <p class="text-xs text-gray-500 mt-1">Formato: ICO, PNG ou SVG. Tamanho máximo: 2MB</p>
            </div>

            <!-- Banner do Dashboard -->
            <div class="mb-6">
                <label for="dashboard_banner" class="block text-sm font-medium text-gray-700 mb-2">
                    Banner do Dashboard
                </label>
                <?php if($dashboard_banner): ?>
                    <div class="mb-3">
                        <img src="<?php echo e(asset('storage/' . $dashboard_banner)); ?>" alt="Banner atual" class="max-w-full h-auto border border-gray-300 rounded-lg" style="max-height: 200px;">
                    </div>
                <?php endif; ?>
                <input 
                    type="file" 
                    id="dashboard_banner" 
                    name="dashboard_banner" 
                    accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                >
                <p class="text-xs text-gray-500 mt-1">Formato: JPEG, JPG, PNG ou WebP. Tamanho máximo: 5MB</p>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorInput = document.getElementById('primary_color');
    const colorText = document.getElementById('primary_color_text');
    
    colorInput.addEventListener('input', function() {
        colorText.value = this.value;
    });
    
    colorText.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            colorInput.value = this.value;
        }
    });
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/white-label/branding.blade.php ENDPATH**/ ?>