<?php $__env->startSection('title', 'Webhooks'); ?>
<?php $__env->startSection('page-title', 'Webhooks'); ?>
<?php $__env->startSection('page-description', 'Conecte e automatize notificações em tempo real'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-8">
    <div class="max-w-[1200px] mx-auto space-y-6">
        <div class="space-y-6" style="opacity: 1; transform: none;">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="space-y-2.5">
                    <h1 class="text-[28px] font-medium tracking-[-0.56px] text-white">Webhooks</h1>
                    <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]">Conecte e automatize notificações em tempo real.</p>
                </div>
                <div class="flex justify-start sm:justify-end gap-3">
                    <a href="<?php echo e(route('webhooks.documentation')); ?>" class="inline-flex items-center justify-center whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 transition-all duration-200 h-10 gap-2 px-4 py-2.5 text-[12px] font-semibold tracking-[-0.24px] rounded-none bg-[#1f1f1f] text-white hover:bg-[#2a2a2a] button-custom border border-[#2d2d2d]" style="border-radius: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book h-3.5 w-3.5">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"></path>
                        </svg>
                        Documentação
                    </a>
                    <button onclick="openAddWebhookModal()" class="inline-flex items-center justify-center whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 transition-all duration-200 h-10 gap-2 px-4 py-2.5 text-[12px] font-semibold tracking-[-0.24px] rounded-none bg-[#21b3dd] text-white hover:bg-[#d12a00] button-custom" style="border-radius: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus h-3.5 w-3.5">
                            <path d="M5 12h14"></path>
                            <path d="M12 5v14"></path>
                        </svg>
                        Criar Webhook
                    </button>
                </div>
            </div>

            <!-- Success/Error Messages -->
            <?php if(session('success')): ?>
                <div class="bg-emerald-500/10 border border-emerald-500/50 text-emerald-400 px-4 py-3 rounded-lg text-sm">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 px-4 py-3 rounded-lg text-sm">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="bg-rose-500/10 border border-rose-500/50 text-rose-400 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Webhooks Container -->
            <div class="rounded-2xl p-5 bg-[#161616]">
                <!-- Search Bar -->
                <div class="mb-6">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-[#AAAAAA]">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                        <input 
                            type="text" 
                            id="searchWebhooks" 
                            placeholder="Buscar Webhooks" 
                            class="flex h-10 w-full rounded-md border px-3 py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 pl-10 text-[12px] font-semibold tracking-[-0.24px] bg-[#1f1f1f] text-white border-[#2d2d2d] placeholder:text-[#AAAAAA]"
                            onkeyup="filterWebhooks(this.value)"
                        >
                    </div>
                </div>

                <!-- Webhooks List -->
                <ul class="space-y-4" id="webhooksList">
                    <?php if($webhooks->isEmpty()): ?>
                        <li class="text-center py-12">
                            <p class="text-[#AAAAAA] text-sm">Nenhum webhook configurado</p>
                        </li>
                    <?php else: ?>
                        <?php $__currentLoopData = $webhooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $webhook): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="webhook-item bg-[#1f1f1f] rounded-lg p-4 border border-[#2d2d2d] hover:border-[#21b3dd]/50 transition-colors" data-description="<?php echo e(strtolower($webhook->description ?? '')); ?>" data-url="<?php echo e(strtolower($webhook->url)); ?>">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-white text-sm font-semibold mb-1"><?php echo e($webhook->description ?: 'Webhook #' . $webhook->id); ?></h3>
                                        <p class="text-[#AAAAAA] text-xs font-mono truncate max-w-md mb-2"><?php echo e($webhook->url); ?></p>
                                        
                                        <!-- Secret Token -->
                                        <div class="bg-[#161616] rounded-lg p-3 border border-[#2d2d2d] mb-2">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex-1 min-w-0">
                                                    <label class="text-[10px] font-semibold text-[#707070] uppercase tracking-wide block mb-1">Secret Token</label>
                                                    <div class="flex items-center gap-2">
                                                        <code id="secret-<?php echo e($webhook->id); ?>" class="text-xs font-mono text-white break-all"><?php echo e($webhook->masked_secret); ?></code>
                                                        <button 
                                                            onclick="toggleSecret(<?php echo e($webhook->id); ?>, <?php echo e(json_encode($webhook->secret)); ?>, <?php echo e(json_encode($webhook->masked_secret)); ?>)"
                                                            class="flex-shrink-0 p-1.5 rounded hover:bg-[#2d2d2d] text-[#AAAAAA] hover:text-white transition-colors"
                                                            title="Mostrar/Ocultar Secret"
                                                            id="toggle-btn-<?php echo e($webhook->id); ?>"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                        </button>
                                                        <button 
                                                            onclick="copySecret(<?php echo e(json_encode($webhook->secret)); ?>, <?php echo e($webhook->id); ?>)"
                                                            class="flex-shrink-0 p-1.5 rounded hover:bg-[#2d2d2d] text-[#AAAAAA] hover:text-emerald-400 transition-colors"
                                                            title="Copiar Secret"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-xs text-[#707070]"><?php echo e($webhook->formatted_events); ?></span>
                                            <?php if($webhook->is_active): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                    Ativo
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-500/20 text-gray-400 border border-gray-500/30">
                                                    Inativo
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <button 
                                            onclick="openEditWebhookModal(<?php echo e($webhook->id); ?>, '<?php echo e(addslashes($webhook->url)); ?>', '<?php echo e(addslashes($webhook->description ?? '')); ?>', <?php echo e(json_encode($webhook->events)); ?>, <?php echo e($webhook->is_active ? 'true' : 'false'); ?>)"
                                            class="p-2 rounded hover:bg-[#2d2d2d] text-[#AAAAAA] hover:text-white transition-colors"
                                            title="Editar"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button 
                                            onclick="confirmDeleteWebhook(<?php echo e($webhook->id); ?>)"
                                            class="p-2 rounded hover:bg-[#2d2d2d] text-[#AAAAAA] hover:text-rose-400 transition-colors"
                                            title="Excluir"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                </ul>

                <!-- Pagination -->
                <?php if($webhooks->hasPages()): ?>
                    <div class="flex items-center justify-center w-full mt-6">
                        <div class="flex items-center gap-2">
                            <?php if($webhooks->onFirstPage()): ?>
                                <button class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] text-[#aaaaaa] button-custom" disabled>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                        <path d="m15 18-6-6 6-6"></path>
                                    </svg>
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                                </button>
                            <?php else: ?>
                                <a href="<?php echo e($webhooks->previousPageUrl()); ?>" class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                        <path d="m15 18-6-6 6-6"></path>
                                    </svg>
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                                </a>
                            <?php endif; ?>
                            
                            <div class="flex items-center gap-1">
                                <?php
                                    $currentPage = $webhooks->currentPage();
                                    $lastPage = $webhooks->lastPage();
                                    $start = max(1, $currentPage - 2);
                                    $end = min($lastPage, $currentPage + 2);
                                ?>
                                
                                <?php if($start > 1): ?>
                                    <a href="<?php echo e($webhooks->url(1)); ?>" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center text-[#aaaaaa] hover:text-white transition-colors">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">1</span>
                                    </a>
                                    <?php if($start > 2): ?>
                                        <span class="text-[#707070] px-2">...</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php $__currentLoopData = range($start, $end); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($page == $currentPage): ?>
                                        <div class="w-8 h-8 bg-[#21b3dd] rounded flex items-center justify-center">
                                            <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white"><?php echo e($page); ?></span>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?php echo e($webhooks->url($page)); ?>" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center text-[#aaaaaa] hover:text-white transition-colors">
                                            <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]"><?php echo e($page); ?></span>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                
                                <?php if($end < $lastPage): ?>
                                    <?php if($end < $lastPage - 1): ?>
                                        <span class="text-[#707070] px-2">...</span>
                                    <?php endif; ?>
                                    <a href="<?php echo e($webhooks->url($lastPage)); ?>" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center text-[#aaaaaa] hover:text-white transition-colors">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]"><?php echo e($lastPage); ?></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($webhooks->hasMorePages()): ?>
                                <a href="<?php echo e($webhooks->nextPageUrl()); ?>" class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom">
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                        <path d="m9 18 6-6-6-6"></path>
                                    </svg>
                                </a>
                            <?php else: ?>
                                <button class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] text-[#aaaaaa] button-custom" disabled>
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                        <path d="m9 18 6-6-6-6"></path>
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Webhook Modal -->
<div id="addWebhookModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="w-full max-w-lg p-[30px] rounded-2xl border-0 bg-[#161616] shadow-lg relative">
    <div class="flex flex-col gap-4 w-full">
        <div class="flex items-start justify-between w-full">
            <div class="flex flex-col gap-[7px]">
                <h2 class="text-[20px] font-medium tracking-[-0.6px] leading-[1.334] text-white">Cadastrar novo Webhook</h2>
                <p class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] w-full text-[#707070]">Adicione um novo endpoint para receber notificações em tempo real.</p>
            </div>
            <button type="button" onclick="closeAddWebhookModal()" class="absolute right-6 top-6 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-4 w-4">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                <span class="sr-only">Close</span>
            </button>
        </div>
        
        <form action="<?php echo e(route('webhooks.store')); ?>" method="POST" class="flex flex-col gap-2.5 w-full" onsubmit="return validateWebhookForm(this)">
            <?php echo csrf_field(); ?>
            
            <!-- Description -->
            <div class="flex flex-col gap-2 w-full">
                <label class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Descrição</label>
                <div class="p-2 rounded-lg w-full bg-[#1F1F1F]">
                    <div class="flex flex-col gap-1.5">
                        <input 
                            name="description" 
                            type="text" 
                            placeholder="Digite uma descrição para seu Webhook" 
                            class="flex h-10 rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-0 bg-transparent p-0 text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] placeholder:text-[#AAAAAA] w-full text-white"
                        >
                    </div>
                </div>
            </div>
            
            <!-- URL -->
            <div class="flex flex-col gap-2 w-full">
                <label class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">URL do Webhook</label>
                <div class="p-2 rounded-lg w-full bg-[#1F1F1F]">
                    <div class="flex flex-col gap-1.5">
                        <input 
                            name="url" 
                            type="url" 
                            required 
                            placeholder="https://" 
                            class="flex h-10 rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-0 bg-transparent p-0 text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] placeholder:text-[#AAAAAA] w-full text-white"
                        >
                    </div>
                </div>
            </div>
            
            <div class="h-px w-full bg-[#1F1F1F]"></div>
            
            <!-- Events -->
            <div class="flex flex-col gap-2.5 w-full">
                <!-- Vendas aprovadas -->
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Vendas aprovadas</span>
                    <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer bg-[#21b3dd] justify-end" data-event="transaction.paid">
                        <input type="checkbox" name="events[]" value="transaction.paid" class="sr-only" checked onchange="updateToggle(this)">
                        <div class="size-4 rounded-[100px] transition-all bg-white"></div>
                    </label>
                </div>
                
                <!-- Venda aguardando pagamento -->
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Venda aguardando pagamento</span>
                    <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer bg-[#21b3dd] justify-end" data-event="transaction.created">
                        <input type="checkbox" name="events[]" value="transaction.created" class="sr-only" checked onchange="updateToggle(this)">
                        <div class="size-4 rounded-[100px] transition-all bg-white"></div>
                    </label>
                </div>
                
                <!-- Venda recusada -->
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Venda recusada</span>
                    <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer bg-[#1F1F1F] justify-start" data-event="transaction.failed">
                        <input type="checkbox" name="events[]" value="transaction.failed" class="sr-only" onchange="updateToggle(this)">
                        <div class="size-4 rounded-[100px] transition-all bg-[#161616]"></div>
                    </label>
                </div>
                
                <!-- Venda chargeback -->
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Venda chargeback</span>
                    <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer bg-[#1F1F1F] justify-start" data-event="transaction.chargeback">
                        <input type="checkbox" name="events[]" value="transaction.chargeback" class="sr-only" onchange="updateToggle(this)">
                        <div class="size-4 rounded-[100px] transition-all bg-[#161616]"></div>
                    </label>
                </div>
                
                <!-- Venda estornadas -->
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Venda estornadas</span>
                    <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer bg-[#1F1F1F] justify-start" data-event="transaction.refunded">
                        <input type="checkbox" name="events[]" value="transaction.refunded" class="sr-only" onchange="updateToggle(this)">
                        <div class="size-4 rounded-[100px] transition-all bg-[#161616]"></div>
                    </label>
                </div>
                
                <!-- Venda canceladas -->
                <div class="flex items-center justify-between w-full">
                    <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Venda canceladas</span>
                    <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer bg-[#1F1F1F] justify-start" data-event="transaction.cancelled">
                        <input type="checkbox" name="events[]" value="transaction.cancelled" class="sr-only" onchange="updateToggle(this)">
                        <div class="size-4 rounded-[100px] transition-all bg-[#161616]"></div>
                    </label>
                </div>
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex flex-col gap-2.5 w-full mt-4">
                <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 transition-all duration-200 h-10 w-full px-4 py-2.5 text-[14px] font-semibold tracking-[-0.28px] leading-[1.3] bg-[#21b3dd] text-white border-0 rounded-none hover:bg-[#d12a00] button-custom" style="border-radius: 8px;">
                    Cadastrar
                </button>
                <button type="button" onclick="closeAddWebhookModal()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border-input hover:text-accent-foreground h-10 w-full px-4 py-2.5 rounded-lg text-[14px] font-semibold tracking-[-0.28px] leading-[1.3] border-0 bg-[#1F1F1F] text-[#707070] hover:bg-[#2D2D2D] button-custom">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<!-- Edit Webhook Modal -->
<div id="editWebhookModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="w-full max-w-lg p-[30px] rounded-2xl border-0 bg-[#161616] shadow-lg relative">
    <div class="flex flex-col gap-4 w-full">
        <div class="flex items-start justify-between w-full">
            <div class="flex flex-col gap-[7px]">
                <h2 class="text-[20px] font-medium tracking-[-0.6px] leading-[1.334] text-white">Editar Webhook</h2>
                <p class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] w-full text-[#707070]">Atualize as configurações do webhook.</p>
            </div>
            <button type="button" onclick="closeEditWebhookModal()" class="absolute right-6 top-6 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-4 w-4">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                <span class="sr-only">Close</span>
            </button>
        </div>
        
        <form id="editWebhookForm" action="" method="POST" class="flex flex-col gap-2.5 w-full" onsubmit="return validateWebhookForm(this)">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <!-- Description -->
            <div class="flex flex-col gap-2 w-full">
                <label class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">Descrição</label>
                <div class="p-2 rounded-lg w-full bg-[#1F1F1F]">
                    <div class="flex flex-col gap-1.5">
                        <input 
                            id="edit_description" 
                            name="description" 
                            type="text" 
                            placeholder="Digite uma descrição para seu Webhook" 
                            class="flex h-10 rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-0 bg-transparent p-0 text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] placeholder:text-[#AAAAAA] w-full text-white"
                        >
                    </div>
                </div>
            </div>
            
            <!-- URL -->
            <div class="flex flex-col gap-2 w-full">
                <label class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">URL do Webhook</label>
                <div class="p-2 rounded-lg w-full bg-[#1F1F1F]">
                    <div class="flex flex-col gap-1.5">
                        <input 
                            id="edit_url" 
                            name="url" 
                            type="url" 
                            required 
                            placeholder="https://" 
                            class="flex h-10 rounded-md border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-0 bg-transparent p-0 text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] placeholder:text-[#AAAAAA] w-full text-white"
                        >
                    </div>
                </div>
            </div>
            
            <div class="h-px w-full bg-[#1F1F1F]"></div>
            
            <!-- Events -->
            <div class="flex flex-col gap-2.5 w-full" id="editEventsContainer">
                <!-- Events will be populated by JavaScript -->
            </div>
            
            <!-- Submit Buttons -->
            <div class="flex flex-col gap-2.5 w-full mt-4">
                <button type="submit" class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 transition-all duration-200 h-10 w-full px-4 py-2.5 text-[14px] font-semibold tracking-[-0.28px] leading-[1.3] bg-[#21b3dd] text-white border-0 rounded-none hover:bg-[#d12a00] button-custom" style="border-radius: 8px;">
                    Atualizar
                </button>
                <button type="button" onclick="closeEditWebhookModal()" class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border-input hover:text-accent-foreground h-10 w-full px-4 py-2.5 rounded-lg text-[14px] font-semibold tracking-[-0.28px] leading-[1.3] border-0 bg-[#1F1F1F] text-[#707070] hover:bg-[#2D2D2D] button-custom">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Event mapping
const eventLabels = {
    'transaction.paid': 'Vendas aprovadas',
    'transaction.created': 'Venda aguardando pagamento',
    'transaction.failed': 'Venda recusada',
    'transaction.chargeback': 'Venda chargeback',
    'transaction.refunded': 'Venda estornadas',
    'transaction.cancelled': 'Venda canceladas',
};

// Update toggle appearance
function updateToggle(checkbox) {
    const label = checkbox.closest('label');
    const toggle = label.querySelector('div');
    if (checkbox.checked) {
        label.classList.remove('bg-[#1F1F1F]', 'justify-start');
        label.classList.add('bg-[#21b3dd]', 'justify-end');
        toggle.classList.remove('bg-[#161616]');
        toggle.classList.add('bg-white');
    } else {
        label.classList.remove('bg-[#21b3dd]', 'justify-end');
        label.classList.add('bg-[#1F1F1F]', 'justify-start');
        toggle.classList.remove('bg-white');
        toggle.classList.add('bg-[#161616]');
    }
}

// Validate webhook form
function validateWebhookForm(form) {
    const checkboxes = form.querySelectorAll('input[name="events[]"]:checked');
    if (checkboxes.length === 0) {
        alert('Selecione pelo menos um evento para o webhook.');
        return false;
    }
    return true;
}

// Handle toggle click
document.addEventListener('DOMContentLoaded', function() {
    // Add click handler to all toggle switches
    document.querySelectorAll('.toggle-switch').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            updateToggle(checkbox);
        });
    });
});

// Filter webhooks
function filterWebhooks(searchTerm) {
    const items = document.querySelectorAll('.webhook-item');
    const term = searchTerm.toLowerCase();
    
    items.forEach(item => {
        const description = item.getAttribute('data-description') || '';
        const url = item.getAttribute('data-url') || '';
        
        if (description.includes(term) || url.includes(term)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

// Modal functions
function openAddWebhookModal() {
    document.getElementById('addWebhookModal').classList.remove('hidden');
}

function closeAddWebhookModal() {
    document.getElementById('addWebhookModal').classList.add('hidden');
}

function openEditWebhookModal(id, url, description, events, isActive) {
    const modal = document.getElementById('editWebhookModal');
    const form = document.getElementById('editWebhookForm');
    const container = document.getElementById('editEventsContainer');
    
    form.action = '<?php echo e(route("webhooks.update", ":id")); ?>'.replace(':id', id);
    document.getElementById('edit_url').value = url;
    document.getElementById('edit_description').value = description || '';
    
    // Build events HTML
    let eventsHTML = '';
    const eventValues = ['transaction.paid', 'transaction.created', 'transaction.failed', 'transaction.chargeback', 'transaction.refunded', 'transaction.cancelled'];
    
    eventValues.forEach(eventValue => {
        const isChecked = Array.isArray(events) && events.includes(eventValue);
        eventsHTML += `
            <div class="flex items-center justify-between w-full">
                <span class="text-[12px] font-semibold tracking-[-0.24px] leading-[1.3] text-white">${eventLabels[eventValue]}</span>
                <label class="toggle-switch flex h-5 w-10 items-center rounded-[100px] p-[2px] transition-all cursor-pointer ${isChecked ? 'bg-[#21b3dd] justify-end' : 'bg-[#1F1F1F] justify-start'}" data-event="${eventValue}">
                    <input type="checkbox" name="events[]" value="${eventValue}" class="sr-only" ${isChecked ? 'checked' : ''} onchange="updateToggle(this)">
                    <div class="size-4 rounded-[100px] transition-all ${isChecked ? 'bg-white' : 'bg-[#161616]'}"></div>
                </label>
            </div>
        `;
    });
    
    container.innerHTML = eventsHTML;
    
    // Re-attach click handlers for new toggles
    container.querySelectorAll('.toggle-switch').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            updateToggle(checkbox);
        });
    });
    
    modal.classList.remove('hidden');
}

function closeEditWebhookModal() {
    document.getElementById('editWebhookModal').classList.add('hidden');
}

function confirmDeleteWebhook(id) {
    if (confirm('Tem certeza que deseja excluir este webhook?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("webhooks.destroy", ":id")); ?>'.replace(':id', id);
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '<?php echo e(csrf_token()); ?>';
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        
        form.appendChild(csrf);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }
}

// Toggle secret visibility
const secretStates = {};

function toggleSecret(webhookId, fullSecret, maskedSecret) {
    const secretElement = document.getElementById('secret-' + webhookId);
    const toggleBtn = document.getElementById('toggle-btn-' + webhookId);
    
    if (!secretStates[webhookId]) {
        // Show full secret
        secretElement.textContent = fullSecret;
        secretStates[webhookId] = true;
        toggleBtn.innerHTML = `
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
            </svg>
        `;
        toggleBtn.title = 'Ocultar Secret';
    } else {
        // Show masked secret
        secretElement.textContent = maskedSecret;
        secretStates[webhookId] = false;
        toggleBtn.innerHTML = `
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        `;
        toggleBtn.title = 'Mostrar Secret';
    }
}

// Copy secret to clipboard
function copySecret(secret, webhookId) {
    navigator.clipboard.writeText(secret).then(function() {
        showToast('Secret copiado!');
    }).catch(function(err) {
        console.error('Erro ao copiar:', err);
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = secret;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showToast('Secret copiado!');
        } catch (e) {
            alert('Erro ao copiar. Por favor, copie manualmente.');
        }
        document.body.removeChild(textarea);
    });
}

// Show toast notification
function showToast(message) {
    // Remove existing toast if any
    let toast = document.getElementById('copyToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'copyToast';
        toast.className = 'fixed bottom-4 right-4 bg-emerald-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 transform translate-y-20 opacity-0 transition-all duration-300';
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 2000);
}

// Close modals on ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddWebhookModal();
        closeEditWebhookModal();
    }
});

// Close modals on outside click
document.getElementById('addWebhookModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeAddWebhookModal();
    }
});

document.getElementById('editWebhookModal')?.addEventListener('click', function(event) {
    if (event.target === this) {
        closeEditWebhookModal();
    }
});

// Prevent modal content clicks from closing modal
document.querySelectorAll('#addWebhookModal > div, #editWebhookModal > div').forEach(modalContent => {
    modalContent.addEventListener('click', function(event) {
        event.stopPropagation();
    });
});

// Initialize toggles on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('#addWebhookModal input[type="checkbox"]').forEach(checkbox => {
        updateToggle(checkbox);
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/webhooks/index.blade.php ENDPATH**/ ?>