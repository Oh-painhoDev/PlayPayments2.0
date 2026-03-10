

<?php $__env->startSection('title', 'Detalhes da Transação'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <!-- Detalhes da Transação -->
        <div class="relative gap-4 border shadow-lg duration-200 sm:rounded-lg w-full max-w-[95vw] sm:max-w-[85vw] md:max-w-3xl mx-auto max-h-[90vh] min-h-[80vh] p-4 sm:p-6 md:p-[30px] rounded-2xl border-none flex flex-col bg-[#161616]" tabindex="-1" style="pointer-events: auto;">
            <h2 id="transaction-title" class="text-lg font-semibold leading-none tracking-tight sr-only">Detalhes do Pedido #<?php echo e(substr($transaction->transaction_id, 0, 20)); ?>...</h2>
            
            <div class="flex flex-col gap-3 sm:gap-4 w-full flex-1 overflow-y-auto pr-1 sm:pr-2 min-h-0 scrollbar-thin scrollbar-track-transparent scrollbar-thumb-[#2a2a2a] hover:scrollbar-thumb-[#3a3a3a]">
                <!-- Header -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <h2 class="font-['Manrope'] font-medium text-[16px] sm:text-[18px] md:text-[20px] tracking-[-0.6px] text-white">Pedido #<?php echo e(substr($transaction->transaction_id, 0, 12)); ?>...</h2>
                        <button class="text-[#707070] hover:text-[#aaaaaa] transition-colors" onclick="copyTransactionId('<?php echo e($transaction->transaction_id); ?>')" title="Copiar ID da Transação">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy h-4 w-4 sm:h-[18px] sm:w-[18px]">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                            </svg>
                        </button>
                        <?php
                            $statusConfig = [
                                'pending' => ['label' => 'Pendente', 'bg' => '#2a2a00', 'color' => '#ffc107'],
                                'paid' => ['label' => 'Pago', 'bg' => '#1a3a2a', 'color' => '#10b981'],
                                'cancelled' => ['label' => 'Cancelado', 'bg' => '#2a2a2a', 'color' => '#6b7280'],
                                'expired' => ['label' => 'Expirado', 'bg' => '#3a1a1a', 'color' => '#ef4444'],
                                'failed' => ['label' => 'Falhou', 'bg' => '#3a1a1a', 'color' => '#ef4444'],
                                'refunded' => ['label' => 'Estornado', 'bg' => '#2a1a3a', 'color' => '#8b5cf6'],
                                'partially_refunded' => ['label' => 'Estornado Parcial', 'bg' => '#2a1a3a', 'color' => '#8b5cf6'],
                                'chargeback' => ['label' => 'Chargeback', 'bg' => '#3a2a1a', 'color' => '#f59e0b'],
                            ];
                            $status = $transaction->is_retained ? 'pending' : $transaction->status;
                            $statusInfo = $statusConfig[$status] ?? ['label' => ucfirst($status), 'bg' => '#2a2a2a', 'color' => '#6b7280'];
                        ?>
                        <div class="px-2 sm:px-3 py-1 rounded bg-[#2a2a2a]" style="background-color: <?php echo e($statusInfo['bg']); ?>">
                            <span class="text-xs font-semibold tracking-[-0.24px]" style="color: <?php echo e($statusInfo['color']); ?>"><?php echo e($statusInfo['label']); ?></span>
                        </div>
                    </div>
                    <div class="flex gap-2 sm:gap-3">
                        <?php if($transaction->status === 'paid' && !$transaction->is_retained): ?>
                        <button class="flex items-center gap-1 px-3 sm:px-4 py-2 rounded-md bg-[#1f1f1f] hover:bg-[#2a2a2a] transition-colors" onclick="refundTransaction()" title="Estornar Transação">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left-right h-4 w-4 sm:h-5 sm:w-5 text-[#707070]">
                                <path d="M8 3 4 7l4 4"></path>
                                <path d="M4 7h16"></path>
                                <path d="m16 21 4-4-4-4"></path>
                                <path d="M20 17H4"></path>
                            </svg>
                            <span class="text-xs font-semibold tracking-[-0.24px] text-[#707070] hidden sm:inline">Estornar</span>
                        </button>
                        <?php endif; ?>
                        <a href="<?php echo e(route('transactions.receipt', $transaction->transaction_id)); ?>" target="_blank" class="flex items-center gap-1 px-3 sm:px-4 py-2 rounded-md bg-[#1f1f1f] hover:bg-[#2a2a2a] transition-colors" title="Gerar Comprovante">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download h-4 w-4 sm:h-5 sm:w-5 text-[#707070]">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                            <span class="text-xs font-semibold tracking-[-0.24px] text-[#707070] hidden sm:inline">Gerar comprovante</span>
                        </a>
                    </div>
                </div>
                
                <div class="h-px w-full bg-[#1f1f1f]"></div>
        
                <!-- Grid de Detalhes -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <!-- Detalhes do Pagamento -->
            <div class="p-4 sm:p-5 rounded-2xl bg-[#1f1f1f]">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-semibold tracking-[-0.28px] text-white">Detalhes do Pagamento</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#707070]">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                    <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-[#707070]">Método</span>
                            <span class="text-xs font-semibold text-white"><?php echo e(strtoupper($transaction->payment_method === 'pix' ? 'PIX' : ($transaction->payment_method === 'credit_card' ? 'Cartão de Crédito' : 'Boleto'))); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-[#707070]">Parcelas</span>
                            <span class="text-xs font-semibold text-white"><?php echo e($transaction->payment_data['installments'] ?? 1); ?>x</span>
                        </div>
                        <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                        
                        <?php if($transaction->payment_method === 'pix'): ?>
                            <?php
                                $pixPayload = $transaction->payment_data['payment_data']['pix']['payload'] ?? 
                                              $transaction->payment_data['payment_data']['pix']['qrcode'] ?? 
                                              $transaction->payment_data['payment_data']['pix']['emv'] ??
                                              $transaction->payment_data['pix']['payload'] ?? 
                                              $transaction->payment_data['pix']['qrcode'] ?? 
                                              $transaction->payment_data['pix']['emv'] ?? null;
                            ?>
                            <?php if($pixPayload): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-[#707070]">Código PIX</span>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold text-white"><?php echo e(strlen($pixPayload) > 20 ? substr($pixPayload, 0, 20) . '...' : $pixPayload); ?></span>
                                    <button class="text-[#707070] hover:text-[#aaaaaa] transition-colors" onclick="copyPixCode('<?php echo e($pixPayload); ?>')" title="Copiar Código PIX">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy h-[18px] w-[18px]">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <button id="toggleQrCodeBtn" class="w-full flex items-center justify-center gap-1 px-4 py-2 rounded-md bg-[#161616] hover:bg-[#2a2a2a] transition-colors" onclick="toggleQrCode()">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code h-5 w-5 text-[#707070]">
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
                                <span id="toggleQrCodeText" class="text-xs font-semibold text-[#707070]">Visualizar QR Code</span>
                            </button>
                            <!-- QR Code Container (hidden by default) -->
                            <div id="qrCodeContainer" class="hidden flex justify-center mt-4">
                                <img alt="QR Code PIX" loading="lazy" width="200" height="200" decoding="async" class="rounded-lg bg-white p-2" src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo e(urlencode($pixPayload)); ?>">
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-[#707070]">Data de criação</span>
                                <span class="text-xs font-semibold text-white"><?php echo e($transaction->created_at->format('d/m/Y, H:i')); ?></span>
                            </div>
                            <?php if($transaction->expires_at): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-[#707070]">Data de expiração</span>
                                <span class="text-xs font-semibold text-white"><?php echo e($transaction->expires_at->format('d/m/Y, H:i')); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if($transaction->paid_at): ?>
                            <div class="flex justify-between items-center">
                                <span class="text-xs font-semibold text-[#707070]">Data de pagamento</span>
                                <span class="text-xs font-semibold text-white"><?php echo e($transaction->paid_at->format('d/m/Y, H:i')); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumo Financeiro -->
            <div class="p-4 sm:p-5 rounded-2xl bg-[#1f1f1f]">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-semibold tracking-[-0.28px] text-white">Resumo Financeiro</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#707070]">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                    <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-[#707070]">Valor bruto</span>
                            <span class="text-xs font-semibold text-white">R$ <?php echo e(number_format($transaction->amount, 2, ',', '.')); ?></span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-[#707070]">Taxa</span>
                            <span class="text-xs font-semibold text-[#21b3dd]">-R$ <?php echo e(number_format($transaction->fee_amount, 2, ',', '.')); ?></span>
                        </div>
                        <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-semibold text-[#707070]">Valor líquido</span>
                            <span class="text-xs font-semibold text-white">R$ <?php echo e(number_format($transaction->net_amount, 2, ',', '.')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Status de Entrega -->
        <div class="w-full">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-[14px] font-semibold tracking-[-0.28px] text-white">Status de entrega</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#707070]">
                        <path d="m6 9 6 6 6-6"></path>
                    </svg>
                </div>
                <?php
                    $deliveryProgress = 0;
                    if ($transaction->status === 'paid' && !$transaction->is_retained) {
                        $deliveryProgress = 100;
                    } elseif ($transaction->status === 'pending' || $transaction->is_retained) {
                        $deliveryProgress = 0;
                    } elseif ($transaction->status === 'processing') {
                        $deliveryProgress = 33;
                    } elseif ($transaction->status === 'authorized') {
                        $deliveryProgress = 66;
                    }
                ?>
                <div class="w-full h-[7px] rounded-[40px] bg-[#1f1f1f]">
                    <div class="h-full bg-[#21b3dd] rounded-[40px] transition-all duration-500" style="width: <?php echo e($deliveryProgress); ?>%;"></div>
                </div>
                <div class="flex justify-between items-center px-2 sm:px-4">
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center bg-[#1f1f1f]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package h-3 w-3 sm:h-[18px] sm:w-[18px] text-[#707070]">
                                <path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"></path>
                                <path d="M12 22V12"></path>
                                <polyline points="3.29 7 12 12 20.71 7"></polyline>
                                <path d="m7.5 4.27 9 5.15"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold text-center text-[#707070]">Aguardando</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center bg-[#1f1f1f]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock h-3 w-3 sm:h-[18px] sm:w-[18px] text-[#707070]">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold text-center text-[#707070]">Processando</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center bg-[#1f1f1f]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck h-3 w-3 sm:h-[18px] sm:w-[18px] text-[#707070]">
                                <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                <path d="M15 18H9"></path>
                                <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path>
                                <circle cx="17" cy="18" r="2"></circle>
                                <circle cx="7" cy="18" r="2"></circle>
                            </svg>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold text-center text-[#707070]">Em trânsito</span>
                    </div>
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center bg-[#1f1f1f]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check-big h-3 w-3 sm:h-[18px] sm:w-[18px] text-[#707070]">
                                <path d="M21.801 10A10 10 0 1 1 17 3.335"></path>
                                <path d="m9 11 3 3L22 4"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] sm:text-xs font-semibold text-center text-[#707070]">Entregue</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cliente e Endereço -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
            <!-- Cliente -->
            <div class="p-4 sm:p-5 rounded-2xl bg-[#1f1f1f]">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-semibold tracking-[-0.28px] text-white">Cliente</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#707070]">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                    <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-[18px] w-[18px] text-[#707070]">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <div>
                                <p class="text-xs font-semibold text-white"><?php echo e($transaction->customer_data['name'] ?? 'N/A'); ?></p>
                                <p class="text-xs font-semibold text-[#707070]">
                                    <?php
                                        $document = $transaction->customer_data['document'] ?? null;
                                        if (is_array($document)) {
                                            $document = $document['number'] ?? $document['value'] ?? null;
                                        }
                                        if ($document) {
                                            $documentClean = preg_replace('/[^0-9]/', '', $document);
                                            if (strlen($documentClean) == 11) {
                                                $document = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $documentClean);
                                            } elseif (strlen($documentClean) == 14) {
                                                $document = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $documentClean);
                                            }
                                        }
                                    ?>
                                    <?php echo e($document ?? 'N/A'); ?>

                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-[18px] w-[18px] text-[#707070]">
                                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <p class="text-xs font-semibold text-white"><?php echo e($transaction->customer_data['email'] ?? 'N/A'); ?></p>
                        </div>
                        <?php if(isset($transaction->customer_data['phone']) && $transaction->customer_data['phone']): ?>
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone h-[18px] w-[18px] text-[#707070]">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <p class="text-xs font-semibold text-white"><?php echo e($transaction->customer_data['phone']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Endereço -->
            <div class="p-4 sm:p-5 rounded-2xl bg-[#1f1f1f]">
                <div class="flex flex-col gap-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-[14px] font-semibold tracking-[-0.28px] text-white">Endereço</h3>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down h-5 w-2.5 text-[#707070]">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </div>
                    <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                    <div class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin h-[18px] w-[18px] text-[#707070] mt-0.5">
                            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <div class="text-xs font-semibold text-white">
                            <?php if(isset($transaction->customer_data['address']) && is_array($transaction->customer_data['address'])): ?>
                                <p class="leading-[1.3]"><?php echo e($transaction->customer_data['address']['street'] ?? 'N/A'); ?>, <?php echo e($transaction->customer_data['address']['number'] ?? ''); ?></p>
                                <p class="leading-[1.3]"><?php echo e($transaction->customer_data['address']['neighborhood'] ?? ''); ?>, <?php echo e($transaction->customer_data['address']['city'] ?? ''); ?> - <?php echo e($transaction->customer_data['address']['state'] ?? ''); ?></p>
                                <p class="leading-[1.3]"><?php echo e($transaction->customer_data['address']['zipcode'] ?? ''); ?></p>
                            <?php else: ?>
                                <p class="leading-[1.3]">Não informado</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
            
            <!-- Botão Voltar -->
            <div class="mt-4 flex justify-end">
                <a href="<?php echo e(route('transactions.index')); ?>" class="flex items-center gap-2 px-4 py-2 rounded-md bg-[#1f1f1f] hover:bg-[#2a2a2a] text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left h-4 w-4">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    <span class="text-sm font-semibold">Voltar</span>
                </a>
            </div>
        </div>
    </div>
</div>


<?php $__env->startPush('scripts'); ?>
<script>
function copyTransactionId(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('ID da transação copiado!');
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
        alert('Erro ao copiar ID da transação');
    });
}

function copyPixCode(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Código PIX copiado!');
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
        alert('Erro ao copiar código PIX');
    });
}

function refundTransaction() {
    if (confirm('Tem certeza que deseja estornar esta transação?')) {
        // Implementar estorno
        alert('Funcionalidade de estorno em desenvolvimento');
    }
}

function toggleQrCode() {
    const qrCodeContainer = document.getElementById('qrCodeContainer');
    const toggleQrCodeText = document.getElementById('toggleQrCodeText');
    
    if (qrCodeContainer.classList.contains('hidden')) {
        qrCodeContainer.classList.remove('hidden');
        toggleQrCodeText.textContent = 'Ocultar QR Code';
    } else {
        qrCodeContainer.classList.add('hidden');
        toggleQrCodeText.textContent = 'Visualizar QR Code';
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/transactions/show.blade.php ENDPATH**/ ?>