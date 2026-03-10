<div class="space-y-6">
    <!-- User Information -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
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
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
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
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
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
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
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
    
    <!-- Block Status -->
    <?php if($user->isBlocked()): ?>
    <div class="bg-gray-50 rounded-lg p-6 border border-red-800">
        <h3 class="text-lg font-semibold text-green-600 mb-4">Informações de Bloqueio</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-600">Status</label>
                <span class="px-2 py-1 text-xs rounded bg-green-500/10 text-green-600 border border-green-500/20">
                    Bloqueado
                </span>
            </div>
            
            <div>
                <label class="text-sm text-gray-600">Bloqueado em</label>
                <p class="text-gray-900 font-medium"><?php echo e($user->blocked_at ? $user->blocked_at->format('d/m/Y H:i') : 'N/A'); ?></p>
            </div>
        </div>
        
        <?php if($user->blocked_reason): ?>
        <div class="mt-3 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
            <p class="text-green-600 text-sm"><?php echo e($user->blocked_reason); ?></p>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div><?php /**PATH C:\xampp\htdocs\resources\views/admin/users/user-details-partial.blade.php ENDPATH**/ ?>