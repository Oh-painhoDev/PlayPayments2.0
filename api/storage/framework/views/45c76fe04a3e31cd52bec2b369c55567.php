<?php $__env->startSection('page-title', 'Personalização White Label'); ?>
<?php $__env->startSection('page-description', 'Configure favicon, cores e banners do sistema'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <?php if(isset($migration_warning) && $migration_warning): ?>
        <div class="bg-yellow-900/20 border border-yellow-700 text-yellow-400 px-4 py-3 rounded-lg mb-4">
            <strong>Atenção:</strong> As tabelas necessárias não existem. Execute as migrations: <code class="bg-yellow-900/30 px-2 py-1 rounded">php artisan migrate</code>
        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div class="bg-green-900/20 border border-green-700 text-green-400 px-4 py-3 rounded-lg mb-4">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-red-900/20 border border-red-700 text-red-400 px-4 py-3 rounded-lg mb-4">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 bg-[#0f0f0f]">
            <h2 class="text-xl font-semibold text-white">Configurações de Personalização</h2>
            <p class="text-sm text-[#6B7280] mt-1">Personalize a aparência do sistema</p>
        </div>

        <form action="<?php echo e(route('admin.white-label.branding.update')); ?>" method="POST" enctype="multipart/form-data" class="p-6">
            <?php echo csrf_field(); ?>

            <!-- Cor Principal -->
            <div class="mb-6">
                <label for="primary_color" class="block text-sm font-medium text-white mb-2">
                    Cor Principal
                </label>
                <div class="flex items-center gap-4">
                    <input 
                        type="color" 
                        id="primary_color" 
                        name="primary_color" 
                        value="<?php echo e($primary_color); ?>" 
                        class="h-10 w-20 rounded border border-gray-700 bg-[#1a1a1a] cursor-pointer"
                    >
                    <input 
                        type="text" 
                        value="<?php echo e($primary_color); ?>" 
                        class="flex-1 px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#21b3dd] focus:border-[#21b3dd]"
                        readonly
                        id="primary_color_text"
                    >
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Cor utilizada em botões, links e elementos de destaque</p>
            </div>

            <!-- Favicon -->
            <div class="mb-6">
                <label for="favicon" class="block text-sm font-medium text-white mb-2">
                    Favicon
                </label>
                <?php if($favicon): ?>
                    <div class="mb-3">
                        <img src="<?php echo e($favicon); ?>" alt="Favicon atual" class="h-16 w-16 border border-gray-700 rounded" onerror="this.style.display='none'">
                        <p class="text-xs text-[#6B7280] mt-1">Favicon atual (<?php echo e($favicon_type === 'url' ? 'URL' : 'Arquivo'); ?>)</p>
                    </div>
                <?php endif; ?>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Enviar arquivo:</label>
                        <input 
                            type="file" 
                            id="favicon_file" 
                            name="favicon_file" 
                            accept=".ico,.png,.svg,.webp"
                            class="block w-full text-sm text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#21b3dd]/20 file:text-[#21b3dd] hover:file:bg-[#21b3dd]/30"
                        >
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-[#1a1a1a] text-[#6B7280]">OU</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Usar URL:</label>
                        <input 
                            type="url" 
                            id="favicon_url" 
                            name="favicon_url" 
                            value="<?php echo e($favicon && $favicon_type === 'url' ? $favicon : ''); ?>"
                            placeholder="https://exemplo.com/favicon.ico"
                            class="block w-full px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#21b3dd] focus:border-[#21b3dd] text-sm"
                        >
                    </div>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Formato: ICO, PNG, SVG ou WebP. Tamanho máximo: 2MB (arquivo) ou URL válida</p>
            </div>

            <!-- Banner do Dashboard -->
            <div class="mb-6">
                <label for="dashboard_banner" class="block text-sm font-medium text-white mb-2">
                    Banner do Dashboard
                </label>
                <?php if($dashboard_banner): ?>
                    <div class="mb-3">
                        <img src="<?php echo e($dashboard_banner); ?>" alt="Banner atual" class="max-w-full h-auto border border-gray-700 rounded-lg" style="max-height: 200px;" onerror="this.style.display='none'">
                        <p class="text-xs text-[#6B7280] mt-1">Banner atual (<?php echo e($dashboard_banner_type === 'url' ? 'URL' : 'Arquivo'); ?>)</p>
                    </div>
                <?php endif; ?>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Enviar arquivo:</label>
                        <input 
                            type="file" 
                            id="dashboard_banner_file" 
                            name="dashboard_banner_file" 
                            accept="image/jpeg,image/jpg,image/png,image/webp"
                            class="block w-full text-sm text-[#6B7280] file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#21b3dd]/20 file:text-[#21b3dd] hover:file:bg-[#21b3dd]/30"
                        >
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-700"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-[#1a1a1a] text-[#6B7280]">OU</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-[#6B7280] mb-1">Usar URL:</label>
                        <input 
                            type="url" 
                            id="dashboard_banner_url" 
                            name="dashboard_banner_url" 
                            value="<?php echo e($dashboard_banner && $dashboard_banner_type === 'url' ? $dashboard_banner : ''); ?>"
                            placeholder="https://exemplo.com/banner.jpg"
                            class="block w-full px-3 py-2 border border-gray-700 bg-[#1a1a1a] text-white rounded-lg focus:ring-2 focus:ring-[#21b3dd] focus:border-[#21b3dd] text-sm"
                        >
                    </div>
                </div>
                <p class="text-xs text-[#6B7280] mt-1">Formato: JPEG, JPG, PNG ou WebP. Tamanho máximo: 5MB (arquivo) ou URL válida</p>
            </div>

            <!-- Botões -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="px-4 py-2 text-sm font-medium text-white bg-[#1a1a1a] border border-gray-700 rounded-lg hover:bg-[#1E1E1E] transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-[#21b3dd] rounded-lg hover:bg-[#7A0000] transition-colors">
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


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/admin/white-label/branding.blade.php ENDPATH**/ ?>