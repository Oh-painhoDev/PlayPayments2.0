

<?php $__env->startSection('title', 'Clientes'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 overflow-y-auto bg-[#000000]">
    <div class="container mx-auto px-4 py-6 md:px-6 lg:px-8">
        <!-- Header Desktop -->
        <div class="hidden md:flex items-center justify-between mb-6">
            <div class="flex flex-col gap-2.5 items-start justify-start">
                <h1 class="font-['Manrope'] font-medium text-[28px] tracking-[-0.56px] text-white">Clientes</h1>
                <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Total de <?php echo e($totalCustomers); ?> clientes cadastrados</p>
            </div>
            <div class="flex gap-2 items-center">
                <button onclick="toggleFilters()" class="bg-[#161616] flex gap-2.5 items-center justify-center px-4 py-2.5 rounded-md cursor-pointer hover:opacity-80 transition-opacity">
                    <div class="flex gap-1 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12.6667 1.33334H3.33337C2.80294 1.33334 2.29423 1.54405 1.91916 1.91912C1.54409 2.2942 1.33337 2.8029 1.33337 3.33334V4.11334C1.33328 4.38863 1.39002 4.66098 1.50004 4.91334V4.95334C1.59409 5.16739 1.72751 5.36188 1.89337 5.52667L6.00004 9.60667V14C5.99981 14.1133 6.02846 14.2248 6.08329 14.3239C6.13811 14.4231 6.2173 14.5066 6.31337 14.5667C6.41947 14.6324 6.54189 14.6671 6.66671 14.6667C6.77107 14.666 6.87383 14.6409 6.96671 14.5933L9.63337 13.26C9.74332 13.2046 9.83577 13.1198 9.90049 13.0151C9.96521 12.9104 9.99967 12.7898 10 12.6667V9.60667L14.08 5.52667C14.2459 5.36188 14.3793 5.16739 14.4734 4.95334V4.91334C14.5926 4.66296 14.6584 4.39052 14.6667 4.11334V3.33334C14.6667 2.8029 14.456 2.2942 14.0809 1.91912C13.7058 1.54405 13.1971 1.33334 12.6667 1.33334ZM8.86004 8.86C8.79825 8.9223 8.74937 8.99617 8.71619 9.0774C8.68302 9.15862 8.6662 9.2456 8.66671 9.33334V12.2533L7.33337 12.92V9.33334C7.33388 9.2456 7.31706 9.15862 7.28389 9.0774C7.25071 8.99617 7.20183 8.9223 7.14004 8.86L3.60671 5.33334H12.3934L8.86004 8.86ZM13.3334 4H2.66671V3.33334C2.66671 3.15652 2.73695 2.98696 2.86197 2.86193C2.98699 2.73691 3.15656 2.66667 3.33337 2.66667H12.6667C12.8435 2.66667 13.0131 2.73691 13.1381 2.86193C13.2631 2.98696 13.3334 3.15652 13.3334 3.33334V4Z" fill="#D4AF37"></path>
                        </svg>
                        <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-white">Mostrar Filtros</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down text-white w-2.5 h-2.5">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </button>
                <button onclick="openAddCustomerModal()" class="bg-[#D4AF37] flex gap-2 items-center justify-center px-4 py-2.5 rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus text-white w-3.5 h-3.5">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-white">Adicionar cliente</span>
                </button>
            </div>
        </div>
        
        <!-- Header Mobile -->
        <div class="flex md:hidden flex-col gap-4 mb-6">
            <div class="flex flex-col gap-2.5 items-start">
                <h1 class="font-['Manrope'] font-medium text-[28px] tracking-[-0.56px] text-white">Clientes</h1>
                <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Total de <?php echo e($totalCustomers); ?> clientes cadastrados</p>
            </div>
            <div class="flex gap-2 items-center">
                <button onclick="toggleFilters()" class="bg-[#161616] flex gap-2.5 items-center justify-center px-4 py-2.5 rounded-md cursor-pointer hover:opacity-80 transition-opacity flex-1">
                    <div class="flex gap-1 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12.6667 1.33334H3.33337C2.80294 1.33334 2.29423 1.54405 1.91916 1.91912C1.54409 2.2942 1.33337 2.8029 1.33337 3.33334V4.11334C1.33328 4.38863 1.39002 4.66098 1.50004 4.91334V4.95334C1.59409 5.16739 1.72751 5.36188 1.89337 5.52667L6.00004 9.60667V14C5.99981 14.1133 6.02846 14.2248 6.08329 14.3239C6.13811 14.4231 6.2173 14.5066 6.31337 14.5667C6.41947 14.6324 6.54189 14.6671 6.66671 14.6667C6.77107 14.666 6.87383 14.6409 6.96671 14.5933L9.63337 13.26C9.74332 13.2046 9.83577 13.1198 9.90049 13.0151C9.96521 12.9104 9.99967 12.7898 10 12.6667V9.60667L14.08 5.52667C14.2459 5.36188 14.3793 5.16739 14.4734 4.95334V4.91334C14.5926 4.66296 14.6584 4.39052 14.6667 4.11334V3.33334C14.6667 2.8029 14.456 2.2942 14.0809 1.91912C13.7058 1.54405 13.1971 1.33334 12.6667 1.33334ZM8.86004 8.86C8.79825 8.9223 8.74937 8.99617 8.71619 9.0774C8.68302 9.15862 8.6662 9.2456 8.66671 9.33334V12.2533L7.33337 12.92V9.33334C7.33388 9.2456 7.31706 9.15862 7.28389 9.0774C7.25071 8.99617 7.20183 8.9223 7.14004 8.86L3.60671 5.33334H12.3934L8.86004 8.86ZM13.3334 4H2.66671V3.33334C2.66671 3.15652 2.73695 2.98696 2.86197 2.86193C2.98699 2.73691 3.15656 2.66667 3.33337 2.66667H12.6667C12.8435 2.66667 13.0131 2.73691 13.1381 2.86193C13.2631 2.98696 13.3334 3.15652 13.3334 3.33334V4Z" fill="#D4AF37"></path>
                        </svg>
                        <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-white">Mostrar Filtros</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down text-white w-2.5 h-2.5">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </button>
                <button onclick="openAddCustomerModal()" class="bg-[#D4AF37] flex gap-2 items-center justify-center px-4 py-2.5 rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus text-white w-3.5 h-3.5">
                        <path d="M5 12h14"></path>
                        <path d="M12 5v14"></path>
                    </svg>
                    <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-white">Adicionar cliente</span>
                </button>
            </div>
        </div>
        
        <!-- Filtros (oculto por padrão) -->
        <div id="filtersPanel" class="hidden mb-6 p-4 bg-[#161616] rounded-lg">
            <form method="GET" action="<?php echo e(route('customers.index')); ?>" class="flex flex-col md:flex-row gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Buscar por nome, CPF, email ou telefone..." class="flex-1 px-4 py-2 rounded-md bg-[#1F1F1F] text-white border-0 h-[42px] text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] placeholder:text-[#707070]">
                <button type="submit" class="bg-[#D4AF37] text-white px-4 py-2 rounded-md hover:opacity-90 transition-opacity">Buscar</button>
                <?php if(request('search')): ?>
                    <a href="<?php echo e(route('customers.index')); ?>" class="bg-[#1F1F1F] text-[#707070] px-4 py-2 rounded-md hover:opacity-80 transition-opacity">Limpar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Lista de Clientes -->
        <div class="bg-[#161616] rounded-2xl p-5">
            <!-- Header da Tabela (Desktop) -->
            <div class="hidden md:block">
                <div class="flex items-center gap-6 px-4 py-3">
                    <div class="w-[300px]">
                        <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Nome</span>
                    </div>
                    <div class="w-[150px]">
                        <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">CPF</span>
                    </div>
                    <div class="w-[200px]">
                        <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Contato</span>
                    </div>
                    <div class="flex-1">
                        <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Localização</span>
                    </div>
                </div>
                <div class="h-px w-full bg-[#2A2A2A] mb-4"></div>
            </div>
            
            <!-- Lista de Clientes -->
            <div class="space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="bg-[#1F1F1F] rounded-lg p-4 cursor-pointer transition-all hover:opacity-80">
                        <!-- Mobile View -->
                        <div class="md:hidden">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="bg-[#161616] w-8 h-8 rounded-lg flex items-center justify-center">
                                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M9 9C11.0711 9 12.75 7.32107 12.75 5.25C12.75 3.17893 11.0711 1.5 9 1.5C6.92893 1.5 5.25 3.17893 5.25 5.25C5.25 7.32107 6.92893 9 9 9Z" stroke="#707070" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M15.75 16.5C15.75 13.6025 12.6975 11.25 9 11.25C5.3025 11.25 2.25 13.6025 2.25 16.5" stroke="#707070" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e($customer['name']); ?></span>
                                </div>
                                <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">
                                    <?php
                                        $doc = preg_replace('/[^0-9]/', '', $customer['document']);
                                        if(strlen($doc) == 11) {
                                            $doc = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
                                        } elseif(strlen($doc) == 14) {
                                            $doc = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
                                        }
                                    ?>
                                    <?php echo e($doc); ?>

                                </span>
                            </div>
                        </div>
                        
                        <!-- Desktop View -->
                        <div class="hidden md:flex items-center gap-6">
                            <div class="flex items-center gap-2 w-[300px]">
                                <div class="bg-[#161616] w-8 h-8 rounded-lg flex items-center justify-center">
                                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 9C11.0711 9 12.75 7.32107 12.75 5.25C12.75 3.17893 11.0711 1.5 9 1.5C6.92893 1.5 5.25 3.17893 5.25 5.25C5.25 7.32107 6.92893 9 9 9Z" stroke="#D4AF37" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        <path d="M15.75 16.5C15.75 13.6025 12.6975 11.25 9 11.25C5.3025 11.25 2.25 13.6025 2.25 16.5" stroke="#D4AF37" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                </div>
                                <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e($customer['name']); ?></span>
                            </div>
                            <div class="w-[150px]">
                                <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">
                                    <?php
                                        $doc = preg_replace('/[^0-9]/', '', $customer['document']);
                                        if(strlen($doc) == 11) {
                                            $doc = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $doc);
                                        } elseif(strlen($doc) == 14) {
                                            $doc = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $doc);
                                        }
                                    ?>
                                    <?php echo e($doc); ?>

                                </span>
                            </div>
                            <div class="w-[200px]">
                                <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]"><?php echo e($customer['phone'] ?? 'N/A'); ?></span>
                            </div>
                            <div class="flex-1">
                                <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">
                                    <?php if($customer['address']): ?>
                                        <?php echo e(Str::limit($customer['address'], 50)); ?>

                                    <?php else: ?>
                                        Não informado
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center py-12">
                        <p class="text-[14px] font-semibold text-[#707070]">Nenhum cliente encontrado</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Paginação -->
            <?php if($customers->hasPages()): ?>
                <div class="flex items-center justify-center w-full mt-6">
                    <div class="flex items-center gap-2">
                        <?php if($customers->onFirstPage()): ?>
                            <button disabled class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo e($customers->previousPageUrl()); ?>" class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </a>
                        <?php endif; ?>
                        
                        <div class="flex items-center gap-1">
                            <?php $__currentLoopData = $customers->getUrlRange(1, $customers->lastPage()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($page == $customers->currentPage()): ?>
                                    <div class="w-8 h-8 bg-[#D4AF37] rounded flex items-center justify-center">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white"><?php echo e($page); ?></span>
                                    </div>
                                <?php else: ?>
                                    <a href="<?php echo e($url); ?>" class="w-8 h-8 bg-[#1f1f1f] rounded flex items-center justify-center hover:bg-[#2a2a2a] transition-colors">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-[#aaaaaa]"><?php echo e($page); ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        
                        <?php if($customers->hasMorePages()): ?>
                            <a href="<?php echo e($customers->nextPageUrl()); ?>" class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white transition-colors">
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </a>
                        <?php else: ?>
                            <button disabled class="h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] disabled:opacity-50 disabled:cursor-not-allowed">
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

<!-- Modal: Adicionar Cliente -->
<div id="addCustomerModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="background-color: rgba(0, 0, 0, 0.5);">
    <div class="relative w-full max-w-lg rounded-2xl p-[30px] bg-[#161616] border-0">
        <div class="flex justify-between items-start mb-4">
            <div class="space-y-1.5">
                <h2 class="text-[20px] font-medium tracking-[-0.6px] text-white">Novo cliente</h2>
            </div>
            <button onclick="closeAddCustomerModal()" class="text-[#707070] hover:text-[#D4AF37] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-4 w-4">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
        </div>
        <form id="addCustomerForm" onsubmit="submitAddCustomer(event)" class="space-y-4">
            <!-- Tipo: Pessoa Física ou Jurídica -->
            <div role="radiogroup" class="gap-2 flex space-x-4 mb-4">
                <div class="flex items-center space-x-2">
                    <input type="radio" id="individual" name="type" value="individual" checked class="aspect-square h-4 w-4 rounded-full border-2 border-[#D4AF37] text-[#D4AF37] bg-[#161616] focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2">
                    <label for="individual" class="text-[12px] font-semibold tracking-[-0.24px] text-white cursor-pointer">Pessoa Física</label>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="radio" id="business" name="type" value="business" class="aspect-square h-4 w-4 rounded-full border-2 border-[#D4AF37] text-[#D4AF37] bg-[#161616] focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2">
                    <label for="business" class="text-[12px] font-semibold tracking-[-0.24px] text-white cursor-pointer">Pessoa Jurídica</label>
                </div>
            </div>
            
            <!-- CPF/CNPJ -->
            <div class="space-y-2">
                <label for="document" class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">CPF</label>
                <input type="text" id="document" name="document" placeholder="Digite o CPF" required class="flex w-full rounded-md px-3 py-2 h-[42px] bg-[#1F1F1F] border-0 text-white placeholder:text-[#707070] text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2 focus:ring-offset-[#161616]">
            </div>
            
            <!-- Nome -->
            <div class="space-y-2">
                <label for="name" class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Nome</label>
                <input type="text" id="name" name="name" placeholder="Digite o nome" required class="flex w-full rounded-md px-3 py-2 h-[42px] bg-[#1F1F1F] border-0 text-white placeholder:text-[#707070] text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2 focus:ring-offset-[#161616]">
            </div>
            
            <!-- E-mail -->
            <div class="space-y-2">
                <label for="email" class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">E-mail</label>
                <input type="email" id="email" name="email" placeholder="Digite um e-mail" required class="flex w-full rounded-md px-3 py-2 h-[42px] bg-[#1F1F1F] border-0 text-white placeholder:text-[#707070] text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2 focus:ring-offset-[#161616]">
            </div>
            
            <!-- Celular -->
            <div class="space-y-2">
                <label for="phone" class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Celular</label>
                <input type="text" id="phone" name="phone" placeholder="Digite um número de celular" required class="flex w-full rounded-md px-3 py-2 h-[42px] bg-[#1F1F1F] border-0 text-white placeholder:text-[#707070] text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] focus:outline-none focus:ring-2 focus:ring-[#D4AF37] focus:ring-offset-2 focus:ring-offset-[#161616]">
            </div>
            
            <!-- Botão Submit -->
            <button type="submit" class="inline-flex items-center justify-center gap-2 w-full h-[38px] bg-[#D4AF37] text-white text-[14px] font-semibold tracking-[-0.28px] rounded-lg hover:bg-[#D4AF37]/90 transition-all">Confirmar e continuar</button>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    panel.classList.toggle('hidden');
}

function openAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.remove('hidden');
    document.getElementById('addCustomerForm').reset();
    document.getElementById('individual').checked = true;
    updateDocumentLabel();
}

function closeAddCustomerModal() {
    document.getElementById('addCustomerModal').classList.add('hidden');
    document.getElementById('addCustomerForm').reset();
}

function updateDocumentLabel() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const label = document.querySelector('label[for="document"]');
    const input = document.getElementById('document');
    
    if (type === 'business') {
        label.textContent = 'CNPJ';
        input.placeholder = 'Digite o CNPJ';
    } else {
        label.textContent = 'CPF';
        input.placeholder = 'Digite o CPF';
    }
}

// Atualizar label quando o tipo mudar
document.addEventListener('DOMContentLoaded', function() {
    const typeInputs = document.querySelectorAll('input[name="type"]');
    typeInputs.forEach(input => {
        input.addEventListener('change', updateDocumentLabel);
    });
});

function submitAddCustomer(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    fetch('<?php echo e(route("customers.store")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: formData.get('type'),
            document: formData.get('document'),
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone')
        })
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Erro ao cadastrar cliente');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert(data.message || 'Cliente cadastrado com sucesso!');
            closeAddCustomerModal();
            location.reload();
        } else {
            alert(data.message || 'Erro ao cadastrar cliente');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Erro ao cadastrar cliente');
    });
}

// Fechar modal ao clicar fora
document.addEventListener('click', function(event) {
    if (event.target.id === 'addCustomerModal') {
        closeAddCustomerModal();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/customers/index.blade.php ENDPATH**/ ?>