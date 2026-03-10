

<?php $__env->startSection('title', 'UTMify - Integrações'); ?>

<?php $__env->startSection('content'); ?>
<section class="bg-view">
    <div class="p-5">
        <div class="max-w-[1600px] mx-auto">
            <div class="bg-[#000000] rounded-2xl p-5 space-y-8">
                <div class="flex flex-col gap-6 mb-8">
                    <div class="flex items-center gap-4">
                        <a href="<?php echo e(route('integracoes')); ?>">
                            <button class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:text-accent-foreground h-10 px-4 py-2 rounded-[8px] group gap-2 hover:bg-white/5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left transition-transform group-hover:-translate-x-1">
                                    <path d="m12 19-7-7 7-7"></path>
                                    <path d="M19 12H5"></path>
                                </svg>
                                <span class="text-white">Voltar</span>
                            </button>
                        </a>
                    </div>
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0 w-16 h-16 rounded-xl p-3 bg-gradient-to-br from-gray-800/30 to-transparent">
                            <img alt="UTMify" loading="lazy" width="48" height="48" decoding="async" class="object-contain" src="https://utmify.com.br/logo/logo-white.png" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <div style="display: none; color: #21b3dd; font-weight: bold; font-size: 20px; text-align: center; line-height: 48px;">UTMify</div>
                        </div>
                        <div>
                            <h1 class="font-['Manrope'] font-medium text-[28px] tracking-[-0.56px] text-white">UTMify</h1>
                            <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Gerencie e acompanhe suas UTMs de forma simplificada</p>
                        </div>
                    </div>
                </div>

                <div class="text-card-foreground shadow-sm overflow-hidden bg-[#161616] border-0 rounded-2xl">
                    <div class="p-6 bg-[#161616]">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                            <h3 class="font-['Manrope'] font-medium text-[16px] tracking-[-0.32px] text-white">Suas UTM's</h3>
                            <div class="flex flex-row gap-3 w-full sm:w-auto">
                                <div class="relative flex-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 transform -translate-y-1/2 text-[#707070] pointer-events-none">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.3-4.3"></path>
                                    </svg>
                                    <input type="text" id="searchInput" class="flex px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10 w-full h-[42px] border transition-all rounded-lg bg-[#1F1F1F] border-[#2A2A2A] text-white placeholder:text-[#AAAAAA] focus:border-[#21b3dd]" placeholder="Buscar UTM..." value="">
                                </div>
                                <div class="flex-shrink-0">
                                    <button onclick="openModal()" class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 text-white transition-all duration-200 h-10 px-4 py-2 rounded-[8px] gap-2 hover:bg-[#21b3dd]/90" style="background-color: #21b3dd; color: white;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus">
                                            <path d="M5 12h14"></path>
                                            <path d="M12 5v14"></path>
                                        </svg>
                                        Nova UTM
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 pb-6 bg-[#161616]">
                        <div class="space-y-4">
                            <div class="rounded-2xl overflow-hidden bg-[#161616]">
                                <div class="relative w-full overflow-auto">
                                    <table class="w-full caption-bottom text-sm border-separate border-spacing-y-2">
                                        <thead class="[&_tr]:border-b">
                                            <tr class="transition-colors data-[state=selected]:bg-muted bg-[#161616] hover:bg-[#161616] border-0">
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[200px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Nome</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[150px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">API Token</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[120px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Status</th>
                                                <th class="h-12 text-left align-middle [&:has([role=checkbox])]:pr-0 w-[120px] py-3 px-6 font-['Manrope'] font-medium text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Data de criação</th>
                                                <th class="h-12 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 w-[50px] py-3 px-6"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="[&_tr:last-child]:border-0 pt-2" id="integrationsTableBody">
                                            <?php $__empty_1 = true; $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <tr class="bg-[#1F1F1F] hover:bg-[#252525] transition-colors rounded-lg" 
                                                    data-integration-id="<?php echo e($integration->id); ?>"
                                                    data-integration-name="<?php echo e($integration->name); ?>"
                                                    data-integration-token="<?php echo e($integration->api_token); ?>"
                                                    data-integration-platform-name="<?php echo e($integration->platform_name ?? ''); ?>"
                                                    data-integration-trigger-payment="<?php echo e($integration->trigger_on_payment ? '1' : '0'); ?>"
                                                    data-integration-trigger-creation="<?php echo e($integration->trigger_on_creation ? '1' : '0'); ?>"
                                                    data-integration-active="<?php echo e($integration->is_active ? '1' : '0'); ?>">
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white"><?php echo e($integration->name); ?></td>
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">
                                                        <?php echo e(substr($integration->api_token, 0, 10)); ?>...
                                                    </td>
                                                    <td class="py-3 px-6">
                                                        <?php if($integration->is_active): ?>
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Ativo</span>
                                                        <?php else: ?>
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">Inativo</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="py-3 px-6 font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">
                                                        <?php echo e($integration->created_at->format('d/m/Y')); ?>

                                                    </td>
                                                    <td class="py-3 px-6">
                                                        <div class="flex items-center gap-2">
                                                            <button onclick="editIntegration(this)" class="text-[#707070] hover:text-white transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                                </svg>
                                                            </button>
                                                            <form action="<?php echo e(route('integracoes.utmfy.destroy', $integration->id)); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta integração?');" class="inline">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                                <button type="submit" class="text-[#707070] hover:text-red-500 transition-colors">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path d="M3 6h18"></path>
                                                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <tr>
                                                    <td colspan="5" class="py-8 px-6 text-center">
                                                        <p class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-[#707070]">Nenhuma integração UTMify encontrada</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="utmifyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" onclick="closeModalOnBackdrop(event)">
    <div class="grid gap-4 sm:rounded-lg fixed left-[50%] top-[50%] z-50 w-[95%] max-w-[600px] -translate-x-1/2 -translate-y-1/2 rounded-2xl border p-6 shadow-lg duration-200 bg-[#161616] border-[#1f1f1f]" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between border-b pb-4 border-[#1f1f1f]">
            <h2 id="modalTitle" class="font-['Manrope'] font-semibold text-[20px] tracking-[-0.4px] text-white">Nova UTM</h2>
            <button type="button" onclick="closeModal()" class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-4 w-4">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                <span class="sr-only">Close</span>
            </button>
        </div>
        <form id="utmifyForm" method="POST" action="" class="mt-6 flex flex-col gap-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="integration_id" id="integrationId">
            
            <div class="space-y-2">
                <label class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Nome</label>
                <div class="flex flex-col gap-1.5">
                    <input name="name" id="integrationName" type="text" class="flex h-10 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-full rounded-xl border-2 bg-[#1f1f1f] border-[#2a2a2a] text-white placeholder:text-[#707070] focus:border-[#21b3dd]" placeholder="Nome do UTM" value="" required>
                </div>
            </div>

            <div class="space-y-2">
                <label class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">API Token</label>
                <div class="flex flex-col gap-1.5">
                    <input name="api_token" id="apiToken" type="text" class="flex h-10 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-full rounded-xl border-2 bg-[#1f1f1f] border-[#2a2a2a] text-white placeholder:text-[#707070] focus:border-[#21b3dd]" placeholder="Token da API UTMify" value="" required>
                    <p class="text-xs text-[#707070]">
                        <strong>Obrigatório:</strong> Obtenha seu token em: <a href="https://utmify.com.br/register" target="_blank" class="text-[#21b3dd] hover:underline">utmify.com.br</a><br>
                        <strong>Nota:</strong> Apenas o API Token é necessário para funcionar. Nenhum Pixel ID é necessário.
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Nome da Plataforma <span class="text-[#707070] text-xs">(Opcional)</span></label>
                <div class="flex flex-col gap-1.5">
                    <input name="platform_name" id="platformName" type="text" class="flex h-10 px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 w-full rounded-xl border-2 bg-[#1f1f1f] border-[#2a2a2a] text-white placeholder:text-[#707070] focus:border-[#21b3dd]" placeholder="Ex: playpayments, GlobalPay, MeuNegocio" value="">
                    <p class="text-xs text-[#707070]">
                        Nome da plataforma que será enviado na API UTMify. Se não informado, será usado "playpayments" por padrão.
                    </p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-xl p-4 bg-[#1f1f1f]">
                    <span class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Acionar no Pagamento</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="trigger_on_payment" id="triggerOnPayment" value="1" class="sr-only peer" checked>
                        <div class="w-10 h-5 bg-[#161616] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#21b3dd] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#AAAAAA] after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#21b3dd]"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between rounded-xl p-4 bg-[#1f1f1f]">
                    <span class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Acionar na Criação</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="trigger_on_creation" id="triggerOnCreation" value="1" class="sr-only peer" checked>
                        <div class="w-10 h-5 bg-[#161616] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#21b3dd] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#AAAAAA] after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#21b3dd]"></div>
                    </label>
                </div>
                <div class="flex items-center justify-between rounded-xl p-4 bg-[#1f1f1f]">
                    <span class="font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] text-white">Ativo</span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="isActive" value="1" class="sr-only peer" checked>
                        <div class="w-10 h-5 bg-[#161616] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#21b3dd] rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-[#AAAAAA] after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-[#21b3dd]"></div>
                    </label>
                </div>
            </div>

            <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 text-white transition-all duration-200 h-10 px-4 py-2 mt-4 w-full rounded-xl font-['Manrope'] font-semibold text-[14px] tracking-[-0.28px] hover:bg-[#21b3dd]/90" style="background-color: #21b3dd; color: white;">
                Confirmar e continuar
            </button>
        </form>
    </div>
</div>

<?php if(session('success')): ?>
    <div id="successMessage" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
        <?php echo e(session('success')); ?>

    </div>
    <script>
        setTimeout(() => {
            document.getElementById('successMessage')?.remove();
        }, 5000);
    </script>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function openModal(integrationData = null) {
        const modal = document.getElementById('utmifyModal');
        const form = document.getElementById('utmifyForm');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');
        const integrationIdInput = document.getElementById('integrationId');
        
        if (integrationData) {
            modalTitle.textContent = 'Editar UTM';
            formMethod.value = 'PUT';
            integrationIdInput.value = integrationData.id;
            document.getElementById('integrationName').value = integrationData.name || '';
            document.getElementById('apiToken').value = integrationData.token || '';
            document.getElementById('platformName').value = integrationData.platformName || '';
            document.getElementById('triggerOnPayment').checked = integrationData.triggerPayment === '1';
            document.getElementById('triggerOnCreation').checked = integrationData.triggerCreation === '1';
            document.getElementById('isActive').checked = integrationData.active === '1';
            form.action = `<?php echo e(url('integracoes/utmfy')); ?>/${integrationData.id}`;
        } else {
            modalTitle.textContent = 'Nova UTM';
            formMethod.value = 'POST';
            integrationIdInput.value = '';
            form.reset();
            document.getElementById('triggerOnPayment').checked = true;
            document.getElementById('triggerOnCreation').checked = true;
            document.getElementById('isActive').checked = true;
            form.action = '<?php echo e(route("integracoes.utmfy.store")); ?>';
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function closeModal() {
        const modal = document.getElementById('utmifyModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    
    function closeModalOnBackdrop(event) {
        if (event.target === event.currentTarget) {
            closeModal();
        }
    }
    
    function editIntegration(button) {
        const row = button.closest('tr');
        const integrationData = {
            id: row.getAttribute('data-integration-id'),
            name: row.getAttribute('data-integration-name'),
            token: row.getAttribute('data-integration-token'),
            platformName: row.getAttribute('data-integration-platform-name'),
            pixel: row.getAttribute('data-integration-pixel'),
            triggerPayment: row.getAttribute('data-integration-trigger-payment'),
            triggerCreation: row.getAttribute('data-integration-trigger-creation'),
            active: row.getAttribute('data-integration-active')
        };
        openModal(integrationData);
    }
    
    document.getElementById('searchInput')?.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#integrationsTableBody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/integracoes/utmfy/index.blade.php ENDPATH**/ ?>