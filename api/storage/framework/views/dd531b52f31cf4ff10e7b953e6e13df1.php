<?php $__env->startSection('title', 'Detalhes do Usuário'); ?>
<?php $__env->startSection('page-title', 'Usuário: ' . $user->name); ?>
<?php $__env->startSection('page-description', 'Informações completas e configurações do usuário'); ?>

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

    <!-- User Header -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                <p class="text-gray-600"><?php echo e($user->email); ?></p>
                <div class="flex items-center space-x-4 mt-2">
                    <span class="px-2 py-1 text-xs rounded <?php echo e($user->isPessoaFisica() ? 'bg-blue-500/10 text-blue-600' : 'bg-purple-500/10 text-purple-400'); ?>">
                        <?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?>

                    </span>
                    <span class="text-gray-500 text-sm"><?php echo e($user->formatted_document); ?></span>
                    <span class="text-gray-500 text-sm">Membro desde <?php echo e($user->created_at ? $user->created_at->format('d/m/Y') : 'N/A'); ?></span>
                    <?php if($user->isBlocked()): ?>
                        <span class="px-2 py-1 text-xs rounded bg-green-500/10 text-green-600 border border-green-500/20">
                            Bloqueado
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex space-x-3">
                <a 
                    href="<?php echo e(route('admin.users.login.as.user', $user)); ?>" 
                    class="bg-orange-600 hover:bg-orange-700 text-gray-900 px-4 py-2 rounded-lg text-sm"
                    onclick="return confirm('Tem certeza que deseja acessar a conta deste usuário?');"
                >
                    Acessar Conta
                </a>
                <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                    Editar
                </a>
                <a href="<?php echo e(route('admin.users.retention', $user)); ?>" class="bg-purple-600 hover:bg-purple-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                    Configurar Retenção
                </a>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Information -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Pessoais</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Nome Completo</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->name); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">E-mail</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->email); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600"><?php echo e($user->isPessoaFisica() ? 'CPF' : 'CNPJ'); ?></label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->formatted_document); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">WhatsApp</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->formatted_whatsapp); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">CEP</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->formatted_cep); ?></p>
                    </div>
                    
                    <?php if($user->address): ?>
                    <div>
                        <label class="text-sm text-gray-600">Endereço</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->address); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($user->city): ?>
                    <div>
                        <label class="text-sm text-gray-600">Cidade</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->city); ?> - <?php echo e($user->state); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="text-sm text-gray-600">Tipo de Conta</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Membro desde</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->created_at ? $user->created_at->format('d/m/Y') : 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Wallet Info -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações Financeiras</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Saldo Disponível</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->wallet ? $user->wallet->formatted_balance : 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Total Recebido</label>
                        <p class="text-green-600 font-medium"><?php echo e($user->wallet ? 'R$ ' . number_format($user->wallet->total_received, 2, ',', '.') : 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Total Sacado</label>
                        <p class="text-green-600 font-medium"><?php echo e($user->wallet ? 'R$ ' . number_format($user->wallet->total_withdrawn, 2, ',', '.') : 'N/A'); ?></p>
                    </div>
                    
                    <div>
                        <label class="text-sm text-gray-600">Última Transação</label>
                        <p class="text-gray-900 font-medium"><?php echo e($user->wallet && $user->wallet->last_transaction_at ? $user->wallet->last_transaction_at->format('d/m/Y H:i') : 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Gateway Info -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Gateway</h3>
                
                <?php if($user->assignedGateway): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Gateway</label>
                            <p class="text-gray-900 font-medium"><?php echo e($user->assignedGateway->name); ?></p>
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-600">Status</label>
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-green-600 text-sm">Ativo</span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-600">URL da API</label>
                            <p class="text-gray-900 font-medium"><?php echo e(parse_url($user->assignedGateway->api_url, PHP_URL_HOST)); ?></p>
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-600">Tipo de Saque</label>
                            <p class="text-gray-900 font-medium"><?php echo e($user->withdrawal_type === 'automatic' ? 'Automático' : 'Manual'); ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-2">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <p class="text-gray-600 text-sm">Nenhum gateway atribuído</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Document Verification -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Verificação de Documentos</h3>
                
                <?php if($user->documentVerification): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-600">Status</label>
                            <span class="px-2 py-1 text-xs rounded <?php echo e($user->documentVerification->isApproved() ? 'bg-green-500/10 text-green-600' : ($user->documentVerification->isRejected() ? 'bg-green-500/10 text-green-600' : 'bg-yellow-500/10 text-yellow-400')); ?>">
                                <?php echo e(ucfirst($user->documentVerification->status)); ?>

                            </span>
                        </div>
                        
                        <?php if($user->documentVerification->submitted_at): ?>
                        <div>
                            <label class="text-sm text-gray-600">Enviado em</label>
                            <p class="text-gray-900 font-medium"><?php echo e($user->documentVerification->formatted_submitted_at); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($user->documentVerification->reviewed_at): ?>
                        <div>
                            <label class="text-sm text-gray-600">Revisado em</label>
                            <p class="text-gray-900 font-medium"><?php echo e($user->documentVerification->formatted_reviewed_at); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($user->documentVerification->rejection_reason): ?>
                    <div class="mt-3 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                        <p class="text-green-600 text-sm"><?php echo e($user->documentVerification->rejection_reason); ?></p>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-gray-600 text-center py-4">Documentos não enviados</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Statistics -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas</h3>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Total de Vendas:</span>
                        <span class="text-gray-900 text-sm">R$ <?php echo e(number_format($userStats['total_sales'], 2, ',', '.')); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Transações Pagas:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($userStats['paid_transactions']); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Ticket Médio:</span>
                        <span class="text-gray-900 text-sm">R$ <?php echo e(number_format($userStats['average_ticket'], 2, ',', '.')); ?></span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 text-sm">Taxa de Conversão:</span>
                        <span class="text-gray-900 text-sm"><?php echo e(number_format($userStats['conversion_rate'], 1)); ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Retention Status -->
            <?php if($retentionConfig): ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status da Retenção</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Status:</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 <?php echo e($retentionConfig->is_active ? 'bg-green-500' : 'bg-green-500'); ?> rounded-full mr-2"></div>
                            <span class="<?php echo e($retentionConfig->is_active ? 'text-green-600' : 'text-green-600'); ?> text-sm"><?php echo e($retentionConfig->is_active ? 'Ativo' : 'Inativo'); ?></span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Configuração:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($retentionConfig->quantity_cycle); ?> de <?php echo e($retentionConfig->quantity_cycle); ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Reter:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($retentionConfig->quantity_retained); ?> transações</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Progresso:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($currentCycleCount); ?>/<?php echo e($retentionConfig->quantity_cycle); ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600 text-sm">Retidas:</span>
                        <span class="text-gray-900 text-sm"><?php echo e($currentRetainedCount); ?>/<?php echo e($retentionConfig->quantity_retained); ?></span>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Retenção Individual</h3>
                <p class="text-gray-600 text-center py-4">Retenção não configurada</p>
                <div class="text-center">
                    <a href="<?php echo e(route('admin.users.retention', $user)); ?>" class="bg-purple-600 hover:bg-purple-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                        Configurar Retenção
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
                
                <div class="grid grid-cols-2 gap-3">
                    <?php if(!$user->isBlocked()): ?>
                        <button 
                            onclick="openBlockUserModal()"
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Bloquear Usuário
                        </button>
                    <?php else: ?>
                        <form action="<?php echo e(route('admin.users.unblock', $user)); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <button 
                                type="submit"
                                class="w-full bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                                onclick="return confirm('Tem certeza que deseja desbloquear este usuário?');"
                            >
                                Desbloquear Usuário
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <button 
                        onclick="openFeesModal(<?php echo e($user->id); ?>)"
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        Configurar Taxas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block User Modal -->
<?php if(!$user->isBlocked()): ?>
<div id="blockUserModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Bloquear Usuário</h3>
                    <button onclick="closeBlockUserModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form action="<?php echo e(route('admin.users.block', $user)); ?>" method="POST" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    
                    <div>
                        <label for="blocked_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo do Bloqueio *
                        </label>
                        <textarea 
                            id="blocked_reason" 
                            name="blocked_reason" 
                            rows="3" 
                            required
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Informe o motivo do bloqueio"
                        ></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeBlockUserModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar Bloqueio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Fees Modal -->
<div id="feesModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Configurar Taxas - <?php echo e($user->name); ?></h3>
                    <button onclick="closeFeesModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- PIX Fees -->
                    <div class="bg-gray-800 rounded-lg p-4">
                        <h4 class="text-gray-900 font-medium mb-4 flex items-center">
                            <div class="p-2 bg-green-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                            </div>
                            Taxas do PIX
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Fixa (R$)</label>
                                <input type="number" id="pix_fixed" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Variável (%)</label>
                                <input type="number" id="pix_variable" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Máxima (R$)</label>
                                <input type="number" id="pix_max" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Boleto Fees -->
                    <div class="bg-gray-800 rounded-lg p-4">
                        <h4 class="text-gray-900 font-medium mb-4 flex items-center">
                            <div class="p-2 bg-orange-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            Taxas do Boleto
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Fixa (R$)</label>
                                <input type="number" id="boleto_fixed" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Variável (%)</label>
                                <input type="number" id="boleto_variable" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Máxima (R$)</label>
                                <input type="number" id="boleto_max" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm" placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Card Fees -->
                    <div class="bg-gray-800 rounded-lg p-4">
                        <h4 class="text-gray-900 font-medium mb-4 flex items-center">
                            <div class="p-2 bg-blue-500/10 rounded-lg mr-2">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                            </div>
                            Taxas do Cartão
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Fixa (R$)</label>
                                <input type="number" id="card_fixed" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxa Máxima (R$)</label>
                                <input type="number" id="card_max" step="0.01" min="0" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm" placeholder="Opcional">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">1x (%)</label>
                                <input type="number" id="card_1x" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">2x (%)</label>
                                <input type="number" id="card_2x" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">3x (%)</label>
                                <input type="number" id="card_3x" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">4x (%)</label>
                                <input type="number" id="card_4x" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">5x (%)</label>
                                <input type="number" id="card_5x" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">6x (%)</label>
                                <input type="number" id="card_6x" step="0.01" min="0" max="100" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-gray-900 text-sm">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button 
                        type="button" 
                        onclick="closeFeesModal()"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        onclick="saveFees()"
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                    >
                        Salvar Taxas
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentUserId = <?php echo e($user->id); ?>;

// Obter taxas globais do sistema
const globalFees = {
    pix: {
        fixed: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.00); ?>,
        variable: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 1.99); ?>,
        max: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null'); ?>

    },
    boleto: {
        fixed: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 2.00); ?>,
        variable: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 2.49); ?>,
        max: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null'); ?>

    },
    card: {
        fixed: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.39); ?>,
        max: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->max_amount ?? 'null'); ?>,
        '1x': <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99); ?>,
        '2x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 0.60); ?>,
        '3x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.20); ?>,
        '4x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.80); ?>,
        '5x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 2.40); ?>,
        '6x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 3.00); ?>

    }
};

function openBlockUserModal() {
    document.getElementById('blockUserModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeBlockUserModal() {
    document.getElementById('blockUserModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openFeesModal(userId) {
    if (userId) {
        currentUserId = userId;
    }
    
    // Preencher o formulário com as taxas globais
    document.getElementById('pix_fixed').value = globalFees.pix.fixed.toFixed(2);
    document.getElementById('pix_variable').value = globalFees.pix.variable.toFixed(2);
    if (globalFees.pix.max !== null) {
        document.getElementById('pix_max').value = globalFees.pix.max.toFixed(2);
    }
    
    document.getElementById('boleto_fixed').value = globalFees.boleto.fixed.toFixed(2);
    document.getElementById('boleto_variable').value = globalFees.boleto.variable.toFixed(2);
    if (globalFees.boleto.max !== null) {
        document.getElementById('boleto_max').value = globalFees.boleto.max.toFixed(2);
    }
    
    document.getElementById('card_fixed').value = globalFees.card.fixed.toFixed(2);
    if (globalFees.card.max !== null) {
        document.getElementById('card_max').value = globalFees.card.max.toFixed(2);
    }
    
    document.getElementById('card_1x').value = globalFees.card['1x'].toFixed(2);
    document.getElementById('card_2x').value = globalFees.card['2x'].toFixed(2);
    document.getElementById('card_3x').value = globalFees.card['3x'].toFixed(2);
    document.getElementById('card_4x').value = globalFees.card['4x'].toFixed(2);
    document.getElementById('card_5x').value = globalFees.card['5x'].toFixed(2);
    document.getElementById('card_6x').value = globalFees.card['6x'].toFixed(2);
    
    // Verificar se o usuário já tem taxas personalizadas
    fetch(`/admin/users/${currentUserId}/fees`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.fees) {
            // Preencher com as taxas personalizadas do usuário
            document.getElementById('pix_fixed').value = data.fees.pix.fixed.toFixed(2);
            document.getElementById('pix_variable').value = data.fees.pix.percentage.toFixed(2);
            if (data.fees.pix.max !== null) {
                document.getElementById('pix_max').value = data.fees.pix.max.toFixed(2);
            } else {
                document.getElementById('pix_max').value = '';
            }
            
            document.getElementById('boleto_fixed').value = data.fees.bank_slip.fixed.toFixed(2);
            document.getElementById('boleto_variable').value = data.fees.bank_slip.percentage.toFixed(2);
            if (data.fees.bank_slip.max !== null) {
                document.getElementById('boleto_max').value = data.fees.bank_slip.max.toFixed(2);
            } else {
                document.getElementById('boleto_max').value = '';
            }
            
            document.getElementById('card_fixed').value = data.fees.credit_card.fixed.toFixed(2);
            if (data.fees.credit_card.max !== null) {
                document.getElementById('card_max').value = data.fees.credit_card.max.toFixed(2);
            } else {
                document.getElementById('card_max').value = '';
            }
            
            document.getElementById('card_1x').value = data.fees.credit_card.percentage.toFixed(2);
            
            // Preencher parcelas se disponíveis
            if (data.fees.credit_card.installments) {
                const installments = data.fees.credit_card.installments;
                if (installments['2x']) document.getElementById('card_2x').value = installments['2x'].toFixed(2);
                if (installments['3x']) document.getElementById('card_3x').value = installments['3x'].toFixed(2);
                if (installments['4x']) document.getElementById('card_4x').value = installments['4x'].toFixed(2);
                if (installments['5x']) document.getElementById('card_5x').value = installments['5x'].toFixed(2);
                if (installments['6x']) document.getElementById('card_6x').value = installments['6x'].toFixed(2);
            }
        }
    })
    .catch(error => {
        console.error('Error fetching user fees:', error);
    });
    
    document.getElementById('feesModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeFeesModal() {
    document.getElementById('feesModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function saveFees() {
    // Get form data
    const formData = {
        pix_fixed: parseFloat(document.getElementById('pix_fixed').value),
        pix_variable: parseFloat(document.getElementById('pix_variable').value),
        pix_max: document.getElementById('pix_max').value ? parseFloat(document.getElementById('pix_max').value) : null,
        boleto_fixed: parseFloat(document.getElementById('boleto_fixed').value),
        boleto_variable: parseFloat(document.getElementById('boleto_variable').value),
        boleto_max: document.getElementById('boleto_max').value ? parseFloat(document.getElementById('boleto_max').value) : null,
        card_fixed: parseFloat(document.getElementById('card_fixed').value),
        card_max: document.getElementById('card_max').value ? parseFloat(document.getElementById('card_max').value) : null,
        card_1x: parseFloat(document.getElementById('card_1x').value),
        card_2x: parseFloat(document.getElementById('card_2x').value),
        card_3x: parseFloat(document.getElementById('card_3x').value),
        card_4x: parseFloat(document.getElementById('card_4x').value),
        card_5x: parseFloat(document.getElementById('card_5x').value),
        card_6x: parseFloat(document.getElementById('card_6x').value),
    };
    
    // Send AJAX request
    fetch(`/admin/users/${currentUserId}/fees`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Taxas salvas com sucesso!');
            closeFeesModal();
        } else {
            alert('Erro ao salvar taxas: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao salvar taxas');
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/users/show.blade.php ENDPATH**/ ?>