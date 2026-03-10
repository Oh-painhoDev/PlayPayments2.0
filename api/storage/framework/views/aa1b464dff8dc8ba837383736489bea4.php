<?php $__env->startSection('title', 'Transações'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <div class="bg-[#000000] rounded-2xl space-y-6">
            <!-- Header -->
            <div class="content-stretch flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between relative size-full p-5 gap-6 md:gap-0">
                <div class="content-stretch flex flex-col gap-2.5 items-start justify-start leading-[0] relative shrink-0 text-nowrap">
                    <div class="font-['Manrope:Regular',_sans-serif] font-normal relative shrink-0 text-[28px] tracking-[-0.56px]">
                        <h1 class="leading-[1.2] text-nowrap whitespace-pre text-white">Transações</h1>
                    </div>
                    <div class="font-['Manrope:SemiBold',_sans-serif] font-regular relative shrink-0 text-[12px] tracking-[-0.24px]">
                        <p class="leading-[1.3] text-nowrap whitespace-pre text-[#AAAAAA]">Gerencie e acompanhe todas as suas transações</p>
                    </div>
                </div>
                
                <!-- Filtro de Data -->
                <div class="box-border content-stretch flex flex-col gap-2.5 items-start justify-center px-4 py-2.5 relative rounded-lg shrink-0 w-full md:w-auto">
                    <div class="grid gap-2 w-full">
                        <form method="GET" action="<?php echo e(route('transactions.index')); ?>" class="flex gap-2">
                            <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                            <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                            <input type="hidden" name="payment_method" value="<?php echo e(request('payment_method')); ?>">
                            <input type="date" name="date_from" value="<?php echo e(request('date_from', \Carbon\Carbon::now()->subDays(30)->format('Y-m-d'))); ?>" class="px-3 py-2 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#21b3dd]">
                            <span class="px-2 py-2 text-[#AAAAAA] text-sm">até</span>
                            <input type="date" name="date_to" value="<?php echo e(request('date_to', \Carbon\Carbon::now()->format('Y-m-d'))); ?>" class="px-3 py-2 bg-[#161616] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#21b3dd]">
                            <button type="submit" class="px-4 py-2 bg-[#21b3dd] hover:bg-[#21b3dd] text-white rounded-lg text-sm font-medium transition-colors">
                                Aplicar
                            </button>
                        </form>
                </div>
                </div>
            </div>
            
            <!-- Filtros e Busca -->
            <div class="box-border flex flex-col gap-2.5 items-start justify-start p-[20px] relative rounded-2xl shrink-0 w-full bg-[#161616]">
                <div class="content-stretch flex gap-4 items-center justify-start relative shrink-0 w-full">
                    <!-- Campo de Busca -->
                    <div class="basis-0 box-border flex flex-col gap-2.5 grow items-start justify-center min-h-px min-w-px px-4 py-3 relative rounded-lg shrink-0 bg-[#1f1f1f]">
                        <div class="content-stretch flex gap-2.5 items-center justify-end relative shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search h-5 w-5 text-[#aaaaaa]">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                </svg>
                            <div class="flex flex-col gap-1.5">
                                <form method="GET" action="<?php echo e(route('transactions.index')); ?>" class="w-full">
                                    <input type="hidden" name="date_from" value="<?php echo e(request('date_from')); ?>">
                                    <input type="hidden" name="date_to" value="<?php echo e(request('date_to')); ?>">
                                    <input type="hidden" name="status" value="<?php echo e(request('status')); ?>">
                                    <input type="hidden" name="payment_method" value="<?php echo e(request('payment_method')); ?>">
                                    <input 
                                        class="flex w-full rounded-md border border-input ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 border-none p-0 h-auto text-[14px] font-normal font-['Manrope:Regular',_sans-serif] tracking-[-0.28px] placeholder:text-[#aaaaaa] focus-visible:ring-0 bg-transparent text-white" 
                                        placeholder="Buscar transações" 
                                        type="text" 
                                        name="search"
                                        value="<?php echo e(request('search')); ?>"
                                        onchange="this.form.submit()"
                                    >
                                </form>
                </div>
            </div>
        </div>

                    <!-- Botão de Filtros -->
                    <div class="flex flex-row items-center self-stretch">
                        <button type="button" class="whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground box-border flex gap-2.5 h-full items-center justify-center px-4 py-2 relative rounded-md shrink-0 bg-[#1f1f1f] button-custom" onclick="toggleFilters()">
                            <div class="content-stretch flex gap-2.5 items-center justify-start relative shrink-0">
                                <div class="content-stretch flex gap-1 items-center justify-start relative shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M12.6667 1.33334H3.33337C2.80294 1.33334 2.29423 1.54406 1.91916 1.91913C1.54409 2.2942 1.33337 2.80291 1.33337 3.33334V4.11334C1.33328 4.38864 1.39002 4.66099 1.50004 4.91334V4.95334C1.59409 5.1674 1.72751 5.36189 1.89337 5.52668L6.00004 9.60668V14C5.99981 14.1133 6.02846 14.2248 6.08329 14.3239C6.13811 14.4231 6.2173 14.5066 6.31337 14.5667C6.41947 14.6324 6.54189 14.6671 6.66671 14.6667C6.77107 14.6661 6.87383 14.6409 6.96671 14.5933L9.63337 13.26C9.74332 13.2046 9.83577 13.1198 9.90049 13.0151C9.96521 12.9104 9.99967 12.7898 10 12.6667V9.60668L14.08 5.52668C14.2459 5.36189 14.3793 5.1674 14.4734 4.95334V4.91334C14.5926 4.66296 14.6584 4.39053 14.6667 4.11334V3.33334C14.6667 2.80291 14.456 2.2942 14.0809 1.91913C13.7058 1.54406 13.1971 1.33334 12.6667 1.33334ZM8.86004 8.86001C8.79825 8.9223 8.74937 8.99618 8.71619 9.07741C8.68302 9.15863 8.6662 9.24561 8.66671 9.33334V12.2533L7.33337 12.92V9.33334C7.33388 9.24561 7.31706 9.15863 7.28389 9.07741C7.25071 8.99618 7.20183 8.9223 7.14004 8.86001L3.60671 5.33334H12.3934L8.86004 8.86001ZM13.3334 4.00001H2.66671V3.33334C2.66671 3.15653 2.73695 2.98696 2.86197 2.86194C2.98699 2.73691 3.15656 2.66668 3.33337 2.66668H12.6667C12.8435 2.66668 13.0131 2.73691 13.1381 2.86194C13.2631 2.98696 13.3334 3.15653 13.3334 3.33334V4.00001Z" fill="#21b3dd"></path>
                    </svg>
                                    <span class="font-['Manrope:SemiBold',_sans-serif] font-semibold leading-[0] relative shrink-0 text-[#21b3dd] text-[12px] text-nowrap tracking-[-0.24px]">Filtros</span>
                </div>
                                <div class="flex h-[10px] items-center justify-center relative shrink-0 w-[20px]">
                                    <div class="flex-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#21b3dd]">
                                            <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </div>
                </div>
            </div>
                        </button>
        </div>
    </div>

                <!-- Filtros Expandidos (oculto por padrão) -->
                <div id="filtersPanel" class="hidden w-full mt-4 pt-4 border-t border-[#2d2d2d]">
                    <form method="GET" action="<?php echo e(route('transactions.index')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="hidden" name="search" value="<?php echo e(request('search')); ?>">
                        <input type="hidden" name="date_from" value="<?php echo e(request('date_from')); ?>">
                        <input type="hidden" name="date_to" value="<?php echo e(request('date_to')); ?>">
            
            <div>
                            <label class="block text-xs font-medium text-[#AAAAAA] mb-2">Status</label>
                            <select name="status" class="w-full px-3 py-2 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#21b3dd]">
                    <option value="">Todos</option>
                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pendente</option>
                    <option value="paid" <?php echo e(request('status') == 'paid' ? 'selected' : ''); ?>>Pago</option>
                    <option value="cancelled" <?php echo e(request('status') == 'cancelled' ? 'selected' : ''); ?>>Cancelado</option>
                    <option value="expired" <?php echo e(request('status') == 'expired' ? 'selected' : ''); ?>>Expirado</option>
                    <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>Falhou</option>
                    <option value="refunded" <?php echo e(request('status') == 'refunded' ? 'selected' : ''); ?>>Estornado</option>
                </select>
            </div>
            
            <div>
                            <label class="block text-xs font-medium text-[#AAAAAA] mb-2">Método de Pagamento</label>
                            <select name="payment_method" class="w-full px-3 py-2 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#21b3dd]">
                    <option value="">Todos</option>
                    <option value="pix" <?php echo e(request('payment_method') == 'pix' ? 'selected' : ''); ?>>PIX</option>
                    <option value="credit_card" <?php echo e(request('payment_method') == 'credit_card' ? 'selected' : ''); ?>>Cartão de Crédito</option>
                    <option value="bank_slip" <?php echo e(request('payment_method') == 'bank_slip' ? 'selected' : ''); ?>>Boleto</option>
                </select>
            </div>
            
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-[#21b3dd] hover:bg-[#21b3dd] text-white rounded-lg text-sm font-medium transition-colors">
                    Aplicar Filtros
                </button>
            </div>
        </form>
                </div>
            </div>
            
            <!-- Tabela Desktop -->
            <div class="hidden md:block w-full overflow-x-auto">
                <div class="min-w-[1000px]">
                    <div class="w-full px-4 py-5">
                        <div class="flex flex-row gap-6 items-center">
                            <div class="w-[300px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Cliente</span>
                            </div>
                            <div class="w-[120px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Empresa</span>
                            </div>
                            <div class="w-[180px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Forma de pagamento e valor</span>
                            </div>
                            <div class="w-[90px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Status</span>
                            </div>
                            <div class="w-[120px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Produto</span>
                            </div>
                            <div class="w-[200px]">
                                <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Datas</span>
                            </div>
                        </div>
                    </div>
                    <div class="w-full h-px mb-5 px-4">
                        <div class="h-px w-full bg-[#1f1f1f]"></div>
                    </div>
                    <div class="w-full flex flex-col gap-3">
                        <div class="flex flex-col gap-3">
                            <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <div class="p-4 rounded-lg flex items-center justify-between cursor-pointer bg-[#1f1f1f] hover:bg-[#2a2a2a]" onclick="window.location.href='<?php echo e(route('transactions.show', $transaction->transaction_id)); ?>'">
                                    <div class="flex flex-row gap-6 items-center">
                                        <!-- Cliente -->
                                        <div class="w-[300px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] leading-[1.3] text-white"><?php echo e($transaction->customer_data['name'] ?? 'N/A'); ?></p>
                                            <p class="text-xs font-semibold tracking-[-0.24px] leading-[1.3] text-[#707070]"><?php echo e($transaction->customer_data['email'] ?? 'N/A'); ?></p>
                                        </div>
                                        
                                        <!-- Empresa -->
                                        <div class="w-[140px] flex flex-row gap-6 items-center">
                                            <?php
                                                $userDocument = $user->document ?? '';
                                                $userName = $user->name ?? '';
                                                // Formatar documento se tiver (remover formatação anterior)
                                                $userDocumentClean = preg_replace('/[^0-9]/', '', $userDocument);
                                                if ($userDocumentClean && strlen($userDocumentClean) >= 11) {
                                                    if (strlen($userDocumentClean) == 11) {
                                                        // CPF: 000.000.000-00
                                                        $userDocument = substr($userDocumentClean, 0, 3) . '.' . substr($userDocumentClean, 3, 3) . '.' . substr($userDocumentClean, 6, 3) . '-' . substr($userDocumentClean, 9, 2);
                                                    } elseif (strlen($userDocumentClean) == 14) {
                                                        // CNPJ: 00.000.000/0000-00
                                                        $userDocument = substr($userDocumentClean, 0, 2) . '.' . substr($userDocumentClean, 2, 3) . '.' . substr($userDocumentClean, 5, 3) . '/' . substr($userDocumentClean, 8, 4) . '-' . substr($userDocumentClean, 12, 2);
                                                    }
                                                }
                                                $userInfo = $userDocument ?: $userName;
                                            ?>
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white truncate" title="<?php echo e($userName); ?> <?php echo e($userDocument); ?>"><?php echo e(\Illuminate\Support\Str::limit($userInfo, 25)); ?></p>
                                        </div>
                                        
                                        <!-- Forma de pagamento e valor -->
                                        <div class="w-[180px] flex flex-col gap-1">
                                            <div class="px-3 py-1 rounded inline-flex items-center gap-1 size-fit bg-[#161616]">
                                                <?php if($transaction->payment_method === 'pix'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code h-4 w-4 text-[#707070]">
                                                        <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                                        <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                                        <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                                        <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                                        <path d="M21 21v.01"></path>
                                                        <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                                        <path d="M3 12h.01"></path>
                                                        <path d="M12 3h.01"></path>
                                                        <path d="M12 16v.01"></path>
                                                        <path d="M16 12h1"></path>
                                                        <path d="M21 12v.01"></path>
                                                        <path d="M12 21v-1"></path>
                                                    </svg>
                                                    <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Pix</span>
                                                <?php elseif($transaction->payment_method === 'credit_card'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-4 w-4 text-[#707070]">
                                                        <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                                        <line x1="2" x2="22" y1="10" y2="10"></line>
                                                    </svg>
                                                    <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Cartão</span>
                                                <?php else: ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text h-4 w-4 text-[#707070]">
                                                        <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                                        <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                                        <path d="M10 9H8"></path>
                                                        <path d="M16 13H8"></path>
                                                        <path d="M16 17H8"></path>
                                                    </svg>
                                                    <span class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#707070]">Boleto</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-xs font-semibold tracking-[-0.28px] text-white">R$ <?php echo e(number_format($transaction->amount, 2, ',', '.')); ?></p>
                                        </div>
                                        
                                        <!-- Status -->
                                        <div class="w-[90px]">
                                            <?php
                                                $statusConfig = [
                                                    'pending' => ['label' => 'Pendente', 'color' => '#ffa782'],
                                                    'paid' => ['label' => 'Pago', 'color' => '#10b981'],
                                                    'cancelled' => ['label' => 'Cancelado', 'color' => '#6b7280'],
                                                    'expired' => ['label' => 'Expirado', 'color' => '#ef4444'],
                                                    'failed' => ['label' => 'Falhou', 'color' => '#ef4444'],
                                                    'refunded' => ['label' => 'Estornado', 'color' => '#8b5cf6'],
                                                    'partially_refunded' => ['label' => 'Estornado Parcial', 'color' => '#8b5cf6'],
                                                    'chargeback' => ['label' => 'Chargeback', 'color' => '#f59e0b'],
                                                ];
                                                $status = $transaction->is_retained ? 'pending' : $transaction->status;
                                                $config = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];
                                            ?>
                                            <div class="px-3 py-1 rounded flex items-center justify-center bg-[#161616]">
                                                <span class="text-xs font-semibold tracking-[-0.24px] text-center" style="color: <?php echo e($config['color']); ?>"><?php echo e($config['label']); ?></span>
                                            </div>
    </div>

                                        <!-- Produto -->
                                        <div class="w-[120px]">
                                            <?php if($transaction->products && is_array($transaction->products) && count($transaction->products) > 0): ?>
                                                <?php
                                                    $firstProduct = $transaction->products[0];
                                                    $productTitle = $firstProduct['title'] ?? $firstProduct['name'] ?? $firstProduct['description'] ?? 'Produto';
                                                    // Limitar tamanho do título
                                                    $productTitle = strlen($productTitle) > 50 ? substr($productTitle, 0, 50) . '...' : $productTitle;
                                                ?>
                                                <p class="text-xs font-semibold tracking-[-0.24px] text-white truncate" title="<?php echo e($firstProduct['title'] ?? $firstProduct['name'] ?? $firstProduct['description'] ?? 'Produto'); ?>"><?php echo e($productTitle); ?></p>
                                                <?php if(count($transaction->products) > 1): ?>
                                                    <p class="text-xs font-semibold tracking-[-0.24px] text-[#707070]">+<?php echo e(count($transaction->products) - 1); ?> mais</p>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php
                                                    $description = $transaction->metadata['description'] ?? ($transaction->payment_data['description'] ?? 'Sem produto');
                                                    $description = strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                                                ?>
                                                <p class="text-xs font-semibold tracking-[-0.24px] text-[#707070] truncate" title="<?php echo e($transaction->metadata['description'] ?? ($transaction->payment_data['description'] ?? 'Sem produto')); ?>"><?php echo e($description); ?></p>
                                            <?php endif; ?>
    </div>

                                        <!-- Datas -->
                                        <div class="w-[200px]">
                                            <p class="text-xs font-semibold tracking-[-0.24px] text-white">Criado: <?php echo e($transaction->created_at->format('d/m/Y, H:i:s')); ?></p>
                                            <?php if($transaction->expires_at): ?>
                                                <p class="text-xs font-semibold tracking-[-0.24px] text-[#707070]">Expira: <?php echo e($transaction->expires_at->format('d/m/Y, H:i:s')); ?></p>
                                            <?php endif; ?>
                                            <?php if($transaction->paid_at): ?>
                                                <p class="text-xs font-semibold tracking-[-0.24px] text-[#10b981]">Pago: <?php echo e($transaction->paid_at->format('d/m/Y, H:i:s')); ?></p>
                                            <?php endif; ?>
                                        </div>
    </div>

                                    <!-- Menu de ações -->
                                    <div class="w-6 h-6 flex items-center justify-center ml-2.5">
                                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 hover:bg-accent hover:text-accent-foreground rounded-[8px] h-6 w-6 p-0 button-custom" type="button" onclick="event.stopPropagation(); window.location.href='<?php echo e(route('transactions.show', $transaction->transaction_id)); ?>'">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ellipsis h-4 w-4 text-[#707070]">
                                                <circle cx="12" cy="12" r="1"></circle>
                                                <circle cx="19" cy="12" r="1"></circle>
                                                <circle cx="5" cy="12" r="1"></circle>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <div class="p-8 text-center">
                                    <p class="text-[#AAAAAA] text-sm">Nenhuma transação encontrada</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        </div>
        
            <!-- Cards Móveis -->
            <div class="md:hidden w-full flex flex-col gap-3 items-start justify-start overflow-clip relative shrink-0">
                <div class="flex flex-col gap-3 items-start justify-start relative shrink-0 w-full">
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="box-border flex flex-col gap-2.5 items-start justify-start p-[16px] relative rounded-lg shrink-0 w-full cursor-pointer bg-[#1f1f1f]" onclick="window.location.href='<?php echo e(route('transactions.show', $transaction->transaction_id)); ?>'">
                            <div class="content-stretch flex font-['Manrope',_sans-serif] font-medium items-center justify-between leading-[0] relative shrink-0 text-[12px] text-nowrap tracking-[-0.24px] w-full">
                                <div class="content-stretch flex flex-col gap-2 items-start justify-start relative shrink-0">
                                    <div class="relative shrink-0 pb-1 text-white"><?php echo e($transaction->customer_data['name'] ?? 'N/A'); ?></div>
                                    <div class="relative shrink-0 text-[#aaaaaa] pt-1"><?php echo e($transaction->customer_data['email'] ?? 'N/A'); ?></div>
                                </div>
                                <div class="relative shrink-0 text-white">R$ <?php echo e(number_format($transaction->amount, 2, ',', '.')); ?></div>
                            </div>
                            <div class="content-stretch flex items-center justify-between relative shrink-0 w-full">
                                <?php
                                    $statusConfig = [
                                        'pending' => ['label' => 'Pendente', 'color' => '#ffa782'],
                                        'paid' => ['label' => 'Pago', 'color' => '#10b981'],
                                        'cancelled' => ['label' => 'Cancelado', 'color' => '#6b7280'],
                                        'expired' => ['label' => 'Expirado', 'color' => '#ef4444'],
                                        'failed' => ['label' => 'Falhou', 'color' => '#ef4444'],
                                        'refunded' => ['label' => 'Estornado', 'color' => '#8b5cf6'],
                                    ];
                                    $status = $transaction->is_retained ? 'pending' : $transaction->status;
                                    $config = $statusConfig[$status] ?? ['label' => ucfirst($status), 'color' => '#6b7280'];
                                ?>
                                <div class="box-border flex flex-col gap-2.5 items-center justify-center px-4 py-2 relative rounded shrink-0 bg-[#161616]">
                                    <div class="font-['Manrope',_sans-serif] font-medium leading-[0] relative shrink-0 text-[12px] text-nowrap tracking-[-0.24px] text-center">
                                        <span style="color: <?php echo e($config['color']); ?>"><?php echo e($config['label']); ?></span>
                                    </div>
                                </div>
                                <div class="box-border flex flex-col gap-2.5 items-center justify-center px-4 py-2 relative rounded shrink-0 bg-[#161616]">
                                    <div class="content-stretch flex gap-1 items-center justify-start relative shrink-0">
                                        <?php if($transaction->payment_method === 'pix'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code h-4 w-4 text-[#707070]">
                                                <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                                <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                                <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                                <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                                <path d="M21 21v.01"></path>
                                                <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                                <path d="M3 12h.01"></path>
                                                <path d="M12 3h.01"></path>
                                                <path d="M12 16v.01"></path>
                                                <path d="M16 12h1"></path>
                                                <path d="M21 12v.01"></path>
                                                <path d="M12 21v-1"></path>
                                            </svg>
                                            <div class="font-['Manrope',_sans-serif] font-medium leading-[0] relative shrink-0 text-[#aaaaaa] text-[12px] text-nowrap tracking-[-0.24px]">Pix</div>
                                        <?php elseif($transaction->payment_method === 'credit_card'): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-4 w-4 text-[#707070]">
                                                <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                                <line x1="2" x2="22" y1="10" y2="10"></line>
                                            </svg>
                                            <div class="font-['Manrope',_sans-serif] font-medium leading-[0] relative shrink-0 text-[#aaaaaa] text-[12px] text-nowrap tracking-[-0.24px]">Cartão</div>
                                        <?php else: ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text h-4 w-4 text-[#707070]">
                                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                                <path d="M10 9H8"></path>
                                                <path d="M16 13H8"></path>
                                                <path d="M16 17H8"></path>
                                                </svg>
                                            <div class="font-['Manrope',_sans-serif] font-medium leading-[0] relative shrink-0 text-[#aaaaaa] text-[12px] text-nowrap tracking-[-0.24px]">Boleto</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 text-center w-full">
                            <p class="text-[#AAAAAA] text-sm">Nenhuma transação encontrada</p>
                                </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Paginação -->
            <?php if($transactions->hasPages()): ?>
                <div class="flex items-center justify-center w-full">
                    <div class="flex items-center gap-2">
                        <?php if($transactions->onFirstPage()): ?>
                            <button class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </button>
                        <?php else: ?>
                            <a href="<?php echo e($transactions->appends(request()->query())->previousPageUrl()); ?>" class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left h-3 w-3">
                                    <path d="m15 18-6-6 6-6"></path>
                                </svg>
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Anterior</span>
                            </a>
                        <?php endif; ?>
                        
                        <div class="flex items-center gap-1">
                            <?php
                                $currentPage = $transactions->currentPage();
                                $lastPage = $transactions->lastPage();
                                $startPage = max(1, $currentPage - 2);
                                $endPage = min($lastPage, $currentPage + 2);
                            ?>
                            
                            <?php if($startPage > 1): ?>
                                <a href="<?php echo e($transactions->appends(request()->query())->url(1)); ?>" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center">
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white">1</span>
                                </a>
                                <?php if($startPage > 2): ?>
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-[#aaaaaa]">...</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php for($page = $startPage; $page <= $endPage; $page++): ?>
                                <?php if($page == $currentPage): ?>
                                    <div class="w-8 h-8 bg-[#21b3dd] rounded flex items-center justify-center">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white"><?php echo e($page); ?></span>
                </div>
                                <?php else: ?>
                                    <a href="<?php echo e($transactions->appends(request()->query())->url($page)); ?>" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center">
                                        <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white"><?php echo e($page); ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if($endPage < $lastPage): ?>
                                <?php if($endPage < $lastPage - 1): ?>
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-[#aaaaaa]">...</span>
                                <?php endif; ?>
                                <a href="<?php echo e($transactions->appends(request()->query())->url($lastPage)); ?>" class="w-8 h-8 bg-[#1f1f1f] hover:bg-[#2a2a2a] rounded flex items-center justify-center">
                                    <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px] text-white"><?php echo e($lastPage); ?></span>
                                </a>
                            <?php endif; ?>
            </div>

                        <?php if($transactions->hasMorePages()): ?>
                            <a href="<?php echo e($transactions->appends(request()->query())->nextPageUrl()); ?>" class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom">
                                <span class="text-[12px] font-semibold font-['Manrope'] tracking-[-0.24px]">Próximo</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right h-3 w-3">
                                    <path d="m9 18 6-6-6-6"></path>
                                </svg>
                            </a>
                        <?php else: ?>
                            <button class="justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 h-8 px-3 py-1 rounded-md flex items-center gap-1.5 bg-[#1f1f1f] hover:bg-[#2a2a2a] text-[#aaaaaa] hover:text-white button-custom" disabled>
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

<?php $__env->startPush('scripts'); ?>
<script>
function toggleFilters() {
    const panel = document.getElementById('filtersPanel');
    if (panel) {
        panel.classList.toggle('hidden');
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/transactions/index.blade.php ENDPATH**/ ?>