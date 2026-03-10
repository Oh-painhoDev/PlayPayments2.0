<?php $__env->startSection('title', 'Detalhes da Transação'); ?>
<?php $__env->startSection('page-title', 'Detalhes da Transação'); ?>
<?php $__env->startSection('page-description', 'Informações completas sobre a transação'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-3 rounded-lg">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Transaction Header -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Transação #<?php echo e(substr($transaction->transaction_id, 0, 12)); ?></h2>
                    <div>
                        <?php
                            $statusConfig = [
                                'pending' => ['label' => 'Pendente', 'class' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30'],
                                'processing' => ['label' => 'Processando', 'class' => 'bg-blue-500/20 text-blue-600 border-blue-500/30'],
                                'authorized' => ['label' => 'Autorizado', 'class' => 'bg-blue-500/20 text-blue-600 border-blue-500/30'],
                                'paid' => ['label' => 'Pago', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                                'cancelled' => ['label' => 'Cancelado', 'class' => 'bg-gray-500/20 text-gray-600 border-gray-500/30'],
                                'expired' => ['label' => 'Expirado', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                                'failed' => ['label' => 'Falhou', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                                'refunded' => ['label' => 'Estornado', 'class' => 'bg-purple-500/20 text-purple-400 border-purple-500/30'],
                                'partially_refunded' => ['label' => 'Estornado Parcial', 'class' => 'bg-purple-500/20 text-purple-400 border-purple-500/30'],
                                'chargeback' => ['label' => 'Chargeback', 'class' => 'bg-green-500/20 text-green-600 border-green-500/30'],
                            ];
                            $config = $statusConfig[$transaction->status] ?? ['label' => ucfirst($transaction->status), 'class' => 'bg-gray-500/20 text-gray-600 border-gray-500/30'];
                        ?>
                        <span class="px-3 py-1 text-sm font-medium rounded-full border <?php echo e($config['class']); ?>">
                            <?php echo e($config['label']); ?>

                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">ID da Transação:</span>
                        <span class="text-gray-900 ml-2 font-mono"><?php echo e($transaction->transaction_id); ?></span>
                    </div>
                    
                    <?php if($transaction->external_id): ?>
                    <div>
                        <span class="text-gray-600">ID Externo:</span>
                        <span class="text-gray-900 ml-2 font-mono"><?php echo e($transaction->external_id); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <span class="text-gray-600">Criado em:</span>
                        <span class="text-gray-900 ml-2"><?php echo e($transaction->created_at->format('d/m/Y \à\s H:i')); ?></span>
                    </div>
                    
                    <?php if($transaction->paid_at): ?>
                    <div>
                        <span class="text-gray-600">Pago em:</span>
                        <span class="text-gray-900 ml-2"><?php echo e($transaction->paid_at->format('d/m/Y \à\s H:i')); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transaction->refunded_at): ?>
                    <div>
                        <span class="text-gray-600">Estornado em:</span>
                        <span class="text-gray-900 ml-2"><?php echo e($transaction->refunded_at->format('d/m/Y \à\s H:i')); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Financial Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes Financeiros</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Valor Bruto:</span>
                        <span class="text-gray-900 font-medium"><?php echo e($transaction->formatted_amount); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Taxa:</span>
                        <span class="text-green-600"><?php echo e($transaction->formatted_fee_amount); ?></span>
                    </div>
                    
                    <div class="flex justify-between border-t border-gray-200 pt-3">
                        <span class="text-gray-600">Valor Líquido:</span>
                        <span class="text-green-600 font-medium"><?php echo e($transaction->formatted_net_amount); ?></span>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Pagamento</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Método de Pagamento:</span>
                        <span class="text-gray-900"><?php echo e(strtoupper(str_replace('_', ' ', $transaction->payment_method))); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Gateway:</span>
                        <span class="text-gray-900"><?php echo e($transaction->gateway->name); ?></span>
                    </div>
                    
                    <?php if($transaction->payment_method === 'credit_card' && isset($transaction->payment_data['installments'])): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Parcelas:</span>
                        <span class="text-gray-900"><?php echo e($transaction->payment_data['installments']); ?>x</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($transaction->expires_at): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Expira em:</span>
                        <span class="text-gray-900"><?php echo e($transaction->expires_at->format('d/m/Y \à\s H:i')); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- PIX Code (if applicable) -->
                <?php if($transaction->payment_method === 'pix' && isset($transaction->payment_data['pix']['payload'])): ?>
                <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-300">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900">Código PIX</span>
                        <button 
                            onclick="copyToClipboard('<?php echo e($transaction->payment_data['pix']['payload']); ?>')"
                            class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-3 py-1 rounded text-xs transition-colors"
                        >
                            Copiar
                        </button>
                    </div>
                    <div class="text-xs text-gray-600 font-mono break-all">
                        <?php echo e($transaction->payment_data['pix']['payload']); ?>

                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Customer Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Cliente</h3>
                
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Nome:</span>
                        <span class="text-gray-900"><?php echo e($transaction->customer_data['name'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">E-mail:</span>
                        <span class="text-gray-900"><?php echo e($transaction->customer_data['email'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Documento:</span>
                        <span class="text-gray-900"><?php echo e($transaction->customer_data['document'] ?? 'N/A'); ?></span>
                    </div>
                    
                    <?php if(isset($transaction->customer_data['phone']) && $transaction->customer_data['phone']): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Telefone:</span>
                        <span class="text-gray-900"><?php echo e($transaction->customer_data['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Metadata -->
            <?php if($transaction->metadata && count((array)$transaction->metadata) > 0): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Metadados</h3>
                
                <div class="space-y-4">
                    <?php $__currentLoopData = (array)$transaction->metadata; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between">
                        <span class="text-gray-600"><?php echo e(ucfirst(str_replace('_', ' ', $key))); ?>:</span>
                        <span class="text-gray-900"><?php echo e(is_array($value) || is_object($value) ? json_encode($value) : $value); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Payment Data -->
            <?php if($transaction->payment_data && count((array)$transaction->payment_data) > 0): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados do Pagamento</h3>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-300">
                    <pre class="text-xs text-gray-700 font-mono overflow-x-auto"><?php echo e(json_encode($transaction->payment_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></pre>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Usuário</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Nome:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($transaction->user->name); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Email:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($transaction->user->email); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Documento:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($transaction->user->formatted_document); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Saldo Atual:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($transaction->user->formatted_wallet_balance); ?></p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="<?php echo e(route('admin.users.show', $transaction->user)); ?>" class="text-blue-600 hover:text-blue-700 text-sm">
                        Ver perfil completo
                    </a>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="<?php echo e(route('admin.transactions.index')); ?>" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar para Lista
                    </a>
                    
                    <?php if($transaction->isPaid()): ?>
                        <button 
                            onclick="openDisputeModal()"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            🚨 Abrir Infração
                        </button>
                        
                        <button 
                            onclick="openRefundModal()"
                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            💸 Reembolsar Direto
                        </button>
                    <?php endif; ?>
                    
                    <?php if($transaction->status !== 'refunded' && $transaction->status !== 'partially_refunded'): ?>
                        <button 
                            onclick="openFakeRefundModal()"
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            🎭 Reembolso Fake
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Transaction Status -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status da Transação</h3>
                
                <div class="flex items-center justify-center mb-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center
                        <?php if($transaction->isPaid()): ?>
                            bg-green-500/10 text-green-500
                        <?php elseif($transaction->isProcessing() || $transaction->isAuthorized()): ?>
                            bg-blue-500/10 text-blue-500
                        <?php elseif($transaction->isPending()): ?>
                            bg-yellow-500/10 text-yellow-500
                        <?php elseif($transaction->isRefunded() || $transaction->isPartiallyRefunded() || $transaction->isChargeback()): ?>
                            bg-purple-500/10 text-purple-500
                        <?php elseif($transaction->isFailed() || $transaction->isExpired() || $transaction->isCancelled()): ?>
                            bg-green-500/10 text-green-500
                        <?php else: ?>
                            bg-gray-500/10 text-gray-500
                        <?php endif; ?>
                    ">
                        <?php if($transaction->isPaid()): ?>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        <?php elseif($transaction->isProcessing() || $transaction->isAuthorized()): ?>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php elseif($transaction->isPending()): ?>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php elseif($transaction->isRefunded() || $transaction->isPartiallyRefunded() || $transaction->isChargeback()): ?>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                        <?php elseif($transaction->isFailed() || $transaction->isExpired() || $transaction->isCancelled()): ?>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        <?php else: ?>
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="text-lg font-semibold 
                        <?php if($transaction->isPaid()): ?>
                            text-green-600
                        <?php elseif($transaction->isProcessing() || $transaction->isAuthorized()): ?>
                            text-blue-600
                        <?php elseif($transaction->isPending()): ?>
                            text-yellow-400
                        <?php elseif($transaction->isRefunded() || $transaction->isPartiallyRefunded() || $transaction->isChargeback()): ?>
                            text-purple-400
                        <?php elseif($transaction->isFailed() || $transaction->isExpired() || $transaction->isCancelled()): ?>
                            text-green-600
                        <?php else: ?>
                            text-gray-600
                        <?php endif; ?>
                    ">
                        <?php echo e($transaction->status_label); ?>

                    </p>
                    
                    <p class="text-gray-600 text-sm mt-2">
                        <?php if($transaction->isPaid()): ?>
                            Transação paga e confirmada.
                        <?php elseif($transaction->isProcessing()): ?>
                            Transação em processamento.
                        <?php elseif($transaction->isAuthorized()): ?>
                            Transação autorizada, aguardando captura.
                        <?php elseif($transaction->isPending()): ?>
                            Transação pendente de pagamento.
                        <?php elseif($transaction->isRefunded()): ?>
                            Transação estornada completamente.
                        <?php elseif($transaction->isPartiallyRefunded()): ?>
                            Transação estornada parcialmente.
                        <?php elseif($transaction->isChargeback()): ?>
                            Transação com chargeback.
                        <?php elseif($transaction->isFailed()): ?>
                            Transação falhou.
                        <?php elseif($transaction->isExpired()): ?>
                            Transação expirada.
                        <?php elseif($transaction->isCancelled()): ?>
                            Transação cancelada.
                        <?php else: ?>
                            Status desconhecido.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dispute Modal (Infração) -->
<div id="disputeModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">🚨 Abrir Infração</h3>
                    <button onclick="closeDisputeModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-orange-500/10 border border-orange-500/20 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-orange-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-orange-600 font-medium text-sm">ATENÇÃO - PROCESSO DE INFRAÇÃO</h3>
                            <p class="text-orange-700 text-sm mt-1">
                                Ao abrir uma infração, o valor de <strong><?php echo e($transaction->formatted_net_amount); ?></strong> será <strong>BLOQUEADO</strong> na carteira do usuário. O usuário poderá apresentar defesa antes de qualquer ação definitiva.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="<?php echo e(route('admin.transactions.dispute', $transaction->transaction_id)); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    
                    <div>
                        <label for="dispute_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da Infração *
                        </label>
                        <textarea 
                            id="dispute_reason" 
                            name="reason" 
                            rows="3" 
                            required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200"
                            placeholder="Descreva o motivo da infração (ex: chargeback, fraude, etc.)"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeDisputeModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Abrir Infração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Refund Modal (Reembolso Direto) -->
<div id="refundModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">💸 Reembolso Direto</h3>
                    <button onclick="closeRefundModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 mb-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-red-600 font-medium text-sm">ATENÇÃO - REEMBOLSO IMEDIATO!</h3>
                            <p class="text-red-700 text-sm mt-1">
                                Esta ação irá reembolsar <strong><?php echo e($transaction->formatted_net_amount); ?></strong> IMEDIATAMENTE, descontando do saldo do usuário. Não passará por processo de infração. Esta ação não pode ser desfeita.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="<?php echo e(route('admin.transactions.refund', $transaction->transaction_id)); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo do Reembolso *
                        </label>
                        <textarea 
                            id="reason" 
                            name="reason" 
                            rows="3" 
                            required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200"
                            placeholder="Informe o motivo do reembolso direto"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeRefundModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar Reembolso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Fake Refund Modal (Reembolso Fake) -->
<div id="fakeRefundModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🎭 Reembolso Fake</h3>
                <button onclick="closeFakeRefundModal()" class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="bg-purple-500/10 border border-purple-500/20 rounded-lg p-4 mb-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-purple-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="text-purple-600 font-medium text-sm">REEMBOLSO FAKE - SEM DEBITAR WALLET!</h3>
                        <p class="text-purple-700 text-sm mt-1">
                            Esta ação irá marcar a transação como <strong>reembolsada</strong> e disparar webhook normalmente, mas <strong>NÃO descontará</strong> o valor da wallet do usuário. Útil para testes ou situações especiais.
                        </p>
                    </div>
                </div>
            </div>

            <form action="<?php echo e(route('admin.transactions.fake-refund', $transaction->transaction_id)); ?>" method="POST" class="space-y-4">
                <?php echo csrf_field(); ?>
                
                <div>
                    <label for="fake_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo do Reembolso Fake *
                    </label>
                    <textarea 
                        id="fake_reason" 
                        name="reason" 
                        rows="3" 
                        required
                        class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                        placeholder="Informe o motivo do reembolso fake"
                    ></textarea>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        onclick="closeFakeRefundModal()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg text-sm transition-colors"
                    >
                        Criar Reembolso Fake
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.textContent;
        btn.textContent = 'Copiado!';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-500');
        
        setTimeout(() => {
            btn.textContent = originalText;
            btn.classList.remove('bg-green-500');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
    }).catch(function(err) {
        console.error('Erro ao copiar: ', err);
        alert('Erro ao copiar código');
    });
}

function openDisputeModal() {
    document.getElementById('disputeModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeDisputeModal() {
    document.getElementById('disputeModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openRefundModal() {
    document.getElementById('refundModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeRefundModal() {
    document.getElementById('refundModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openFakeRefundModal() {
    document.getElementById('fakeRefundModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeFakeRefundModal() {
    document.getElementById('fakeRefundModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/transactions/show.blade.php ENDPATH**/ ?>