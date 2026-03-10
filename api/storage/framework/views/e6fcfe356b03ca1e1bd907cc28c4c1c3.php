<?php $__env->startSection('title', 'Solicitações Gateway'); ?>
<?php $__env->startSection('page-title', 'Solicitações Gateway'); ?>
<?php $__env->startSection('page-description', 'Veja a lista de todas as solicitações pendentes para usar a plataforma.'); ?>

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

    <!-- Document Verifications Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Empresa</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Faturamento médio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Criado em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php $__empty_1 = true; $__currentLoopData = $pendingVerifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $verification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $user = $verification->user;
                            $avgRevenue = $user->transactions()->where('status', 'paid')->avg('amount') ?? 0;
                        ?>
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-gray-900 font-medium text-sm"><?php echo e(substr($user->name, 0, 2)); ?></span>
                                    </div>
                                    <div>
                                        <div class="text-gray-900 font-medium"><?php echo e($user->name); ?></div>
                                        <div class="text-gray-600 text-sm"><?php echo e($user->formatted_document); ?></div>
                                        <?php if($user->isPessoaJuridica()): ?>
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-500/10 text-blue-600 border border-blue-500/20 mt-1">
                                                Vender produtos
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900 font-medium">R$ <?php echo e(number_format($avgRevenue, 2, ',', '.')); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-900"><?php echo e($verification->created_at ? $verification->created_at->format('d/m/Y') : 'N/A'); ?></div>
                                <div class="text-gray-600 text-sm">às <?php echo e($verification->created_at ? $verification->created_at->format('H:i') : 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button 
                                        onclick="openCompanyModal(<?php echo e($user->id); ?>)"
                                        class="bg-green-600 hover:bg-green-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                                    >
                                        Ver documentos
                                    </button>
                                    <button 
                                        onclick="openApprovalModalDirect(<?php echo e($verification->id); ?>, <?php echo e($user->id); ?>)"
                                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm transition-colors"
                                    >
                                        Aprovar/Rejeitar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-600">
                                Nenhuma solicitação pendente
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Company Details Modal -->
<div id="companyModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-4xl w-full max-h-[90vh]">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Detalhes da empresa</h3>
                    <button onclick="closeCompanyModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div id="companyDetails" class="overflow-y-auto max-h-[70vh]">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Company Actions Modal -->
<div id="companyActionsModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Detalhes da empresa</h3>
                    <button onclick="closeCompanyActionsModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center p-3 bg-gray-800 rounded-lg">
                        <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center mr-3">
                            <span class="text-gray-900 font-medium text-sm" id="companyInitials">PA</span>
                        </div>
                        <div>
                            <div class="text-gray-900 font-medium" id="companyName">PATRICIA PEPE BASTOS</div>
                            <div class="text-gray-600 text-sm" id="companyDocument">55.278.068/0001-29</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <h4 class="text-gray-900 font-medium mb-2">OPÇÕES</h4>
                        
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <button 
                                onclick="openPermissionsModal()"
                                class="flex flex-col items-center p-4 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors"
                            >
                                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span class="text-gray-900 text-sm">Alterar permissões</span>
                            </button>

                            <button class="flex flex-col items-center p-4 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-gray-900 text-sm">Adquirentes customizadas</span>
                            </button>

                            <button class="flex flex-col items-center p-4 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors">
                                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18 12M6 6l12 12" />
                                </svg>
                                <span class="text-gray-900 text-sm">Bloquear</span>
                            </button>

                            <button 
                                onclick="openLoginModal()"
                                class="flex flex-col items-center p-4 bg-gray-800 hover:bg-gray-700 rounded-lg transition-colors"
                            >
                                <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                <span class="text-gray-900 text-sm">Fazer login</span>
                            </button>
                        </div>

                        <button 
                            onclick="openSubaccountsModal()"
                            class="w-full bg-gray-800 hover:bg-gray-700 text-gray-900 py-2 px-4 rounded-lg text-sm transition-colors mb-4"
                        >
                            ⚙️ Configurar subcontas
                        </button>

                        <div class="flex space-x-3">
                            <button 
                                onclick="manageCompany()"
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg text-sm transition-colors"
                            >
                                Gerenciar empresa
                            </button>
                            <button 
                                onclick="approveCompany()"
                                class="flex-1 bg-green-600 hover:bg-green-700 text-gray-900 py-2 px-4 rounded-lg text-sm transition-colors"
                            >
                                Responder solicitação
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fees Modal -->
<div id="feesModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Taxas Personalizadas</h3>
                    <button onclick="closeFeesModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-3 mb-6">
                    <p class="text-blue-700 text-sm">Essa é a taxa final que a empresa irá pagar.</p>
                </div>

                <form id="feesForm" class="space-y-6">
                    <!-- PIX Fees -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Taxas do PIX</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PIX: Taxa fixa *</label>
                                <input type="number" step="0.01" name="pix_fixed" id="pix_fixed" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PIX: Taxa variável (%) *</label>
                                <input type="number" step="0.01" name="pix_percentage" id="pix_percentage" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PIX: Valor mínimo (R$)</label>
                                <input type="number" step="0.01" name="pix_min_transaction" id="pix_min_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Sem limite">
                                <p class="text-xs text-gray-500 mt-1">Valor mínimo para gerar PIX</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">PIX: Valor máximo (R$)</label>
                                <input type="number" step="0.01" name="pix_max_transaction" id="pix_max_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Sem limite">
                                <p class="text-xs text-gray-500 mt-1">Valor máximo para gerar PIX</p>
                            </div>
                        </div>
                    </div>

                    <!-- Boleto Fees -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Taxas do Boleto</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Boleto: Taxa fixa *</label>
                                <input type="number" step="0.01" name="boleto_fixed" id="boleto_fixed" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Boleto: Taxa variável (%) *</label>
                                <input type="number" step="0.01" name="boleto_variable" id="boleto_variable" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Boleto: Valor mínimo (R$)</label>
                                <input type="number" step="0.01" name="boleto_min_transaction" id="boleto_min_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Sem limite">
                                <p class="text-xs text-gray-500 mt-1">Valor mínimo para boleto</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Boleto: Valor máximo (R$)</label>
                                <input type="number" step="0.01" name="boleto_max_transaction" id="boleto_max_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Sem limite">
                                <p class="text-xs text-gray-500 mt-1">Valor máximo para boleto</p>
                            </div>
                        </div>
                    </div>

                    <!-- Credit Card Fees -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Taxas do Cartão</h4>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cartão: Taxa fixa *</label>
                            <input type="number" step="0.01" name="card_fixed" id="card_fixed" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão (à vista) (%) *</label>
                                <input type="number" step="0.01" name="card_1x" id="card_1x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão (2 parcelas) (%) *</label>
                                <input type="number" step="0.01" name="card_2x" id="card_2x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão (3 parc.) (%) *</label>
                                <input type="number" step="0.01" name="card_3x" id="card_3x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão (4 parc.) (%) *</label>
                                <input type="number" step="0.01" name="card_4x" id="card_4x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão (5 parc.) (%) *</label>
                                <input type="number" step="0.01" name="card_5x" id="card_5x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão (6 parc.) (%) *</label>
                                <input type="number" step="0.01" name="card_6x" id="card_6x" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão: Valor mínimo (R$)</label>
                                <input type="number" step="0.01" name="card_min_transaction" id="card_min_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Sem limite">
                                <p class="text-xs text-gray-500 mt-1">Valor mínimo para cartão</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cartão: Valor máximo (R$)</label>
                                <input type="number" step="0.01" name="card_max_transaction" id="card_max_transaction" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-gray-900" placeholder="Sem limite">
                                <p class="text-xs text-gray-500 mt-1">Valor máximo para cartão</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="saveFees()" class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Modal -->
<div id="permissionsModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Permissões da empresa</h3>
                    <button onclick="closePermissionsModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="permissionsForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Meios de pagamento *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="credit_card" name="payment_methods[]" value="credit_card" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded" checked>
                                <label for="credit_card" class="ml-2 text-sm text-gray-700">Cartão de crédito</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="boleto" name="payment_methods[]" value="boleto" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded" checked>
                                <label for="boleto" class="ml-2 text-sm text-gray-700">Boleto</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" id="pix" name="payment_methods[]" value="pix" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded" checked>
                                <label for="pix" class="ml-2 text-sm text-gray-700">PIX</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Segurança *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="redefine_api" name="security[]" value="redefine_api" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded">
                                <label for="redefine_api" class="ml-2 text-sm text-gray-700">Redefinir chaves de API</label>
                            </div>
                        </div>
                        <button type="button" class="text-blue-600 hover:text-blue-700 text-sm mt-2">
                            Mostrar configurações avançadas
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Regras de transferência *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="transfer_enabled" name="transfer_rules[]" value="enabled" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded">
                                <label for="transfer_enabled" class="ml-2 text-sm text-gray-700">Transferência habilitada</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Regras de antecipação *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="anticipation_enabled" name="anticipation_rules[]" value="enabled" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded">
                                <label for="anticipation_enabled" class="ml-2 text-sm text-gray-700">Antecipação habilitada</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de saque *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" id="withdrawal_manual" name="withdrawal_type" value="manual" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded" checked>
                                <label for="withdrawal_manual" class="ml-2 text-sm text-gray-700">Manual (aprovação necessária)</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="withdrawal_automatic" name="withdrawal_type" value="automatic" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded">
                                <label for="withdrawal_automatic" class="ml-2 text-sm text-gray-700">Automático (sem aprovação)</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" onclick="savePermissions()" class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors">
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Document Approval Modal -->
<div id="approvalModal" class="fixed inset-0 bg-gray-100 bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gray-50 rounded-lg max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Responder solicitação</h3>
                    <button onclick="closeApprovalModal()" class="text-gray-600 hover:text-gray-900">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="approvalForm" action="<?php echo e(route('admin.documents.respond')); ?>" method="POST" class="space-y-6">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="verification_id" id="verification_id">
                    <input type="hidden" name="user_id" id="user_id">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Resposta *</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="radio" id="approve" name="status" value="aprovado" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800">
                                <label for="approve" class="ml-2 text-sm text-gray-700">Aprovar</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="reject" name="status" value="recusado" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800">
                                <label for="reject" class="ml-2 text-sm text-gray-700">Recusar</label>
                            </div>
                        </div>
                    </div>

                    <div id="rejection_reason_container" class="hidden">
                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da recusa *
                        </label>
                        <textarea 
                            id="rejection_reason" 
                            name="rejection_reason" 
                            rows="3" 
                            class="w-full px-4 py-3 bg-white border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            placeholder="Informe o motivo da recusa"
                        ></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            type="button" 
                            onclick="closeApprovalModal()"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
                        >
                            Confirmar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
let currentUserId = null;
let currentVerificationId = null;

// Obter taxas globais do sistema
const globalFees = {
    pix: {
        fixed: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.00); ?>,
        variable: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 1.99); ?>,
        min_transaction: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->min_transaction_value ?? 'null'); ?>,
        max_transaction: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'pix')->where('is_global', true)->where('is_active', true)->first()->max_transaction_value ?? 'null'); ?>

    },
    boleto: {
        fixed: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 2.00); ?>,
        variable: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 2.49); ?>,
        min_transaction: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->min_transaction_value ?? 'null'); ?>,
        max_transaction: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'bank_slip')->where('is_global', true)->where('is_active', true)->first()->max_transaction_value ?? 'null'); ?>

    },
    card: {
        fixed: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->fixed_fee ?? 0.39); ?>,
        '1x': <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99); ?>,
        '2x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 0.60); ?>,
        '3x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.20); ?>,
        '4x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 1.80); ?>,
        '5x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 2.40); ?>,
        '6x': <?php echo e((\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->percentage_fee ?? 3.99) + 3.00); ?>,
        min_transaction: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->min_transaction_value ?? 'null'); ?>,
        max_transaction: <?php echo e(\App\Models\FeeConfiguration::where('payment_method', 'credit_card')->where('is_global', true)->where('is_active', true)->first()->max_transaction_value ?? 'null'); ?>

    }
};

function openCompanyModal(userId) {
    currentUserId = userId;
    
    // Fetch company details
    fetch(`/admin/documents/details/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('companyDetails').innerHTML = data.html;
                document.getElementById('companyModal').classList.remove('hidden');
                document.body.classList.add('modal-open');
            } else {
                alert('Erro ao carregar detalhes da empresa');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao carregar detalhes da empresa');
        });
}

function closeCompanyModal() {
    document.getElementById('companyModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openCompanyActionsModal(userId, verificationId, name, document) {
    closeCompanyModal();
    currentUserId = userId;
    currentVerificationId = verificationId;
    
    // Set company details
    document.getElementById('companyInitials').textContent = name.substring(0, 2);
    document.getElementById('companyName').textContent = name;
    document.getElementById('companyDocument').textContent = document;
    
    document.getElementById('companyActionsModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeCompanyActionsModal() {
    document.getElementById('companyActionsModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function openFeesModalDirect(userId, verificationId, name, document) {
    currentUserId = userId;
    currentVerificationId = verificationId;
    
    // Set company details for later use
    document.getElementById('companyInitials').textContent = name.substring(0, 2);
    document.getElementById('companyName').textContent = name;
    document.getElementById('companyDocument').textContent = document;
    
    // Preencher o formulário com as taxas globais
    document.getElementById('pix_fixed').value = globalFees.pix.fixed.toFixed(2);
    document.getElementById('pix_percentage').value = globalFees.pix.variable.toFixed(2);
    document.getElementById('pix_min_transaction').value = globalFees.pix.min_transaction !== null ? globalFees.pix.min_transaction : '';
    document.getElementById('pix_max_transaction').value = globalFees.pix.max_transaction !== null ? globalFees.pix.max_transaction : '';
    
    document.getElementById('boleto_fixed').value = globalFees.boleto.fixed.toFixed(2);
    document.getElementById('boleto_variable').value = globalFees.boleto.variable.toFixed(2);
    document.getElementById('boleto_min_transaction').value = globalFees.boleto.min_transaction !== null ? globalFees.boleto.min_transaction : '';
    document.getElementById('boleto_max_transaction').value = globalFees.boleto.max_transaction !== null ? globalFees.boleto.max_transaction : '';
    
    document.getElementById('card_fixed').value = globalFees.card.fixed.toFixed(2);
    document.getElementById('card_1x').value = globalFees.card['1x'].toFixed(2);
    document.getElementById('card_2x').value = globalFees.card['2x'].toFixed(2);
    document.getElementById('card_3x').value = globalFees.card['3x'].toFixed(2);
    document.getElementById('card_4x').value = globalFees.card['4x'].toFixed(2);
    document.getElementById('card_5x').value = globalFees.card['5x'].toFixed(2);
    document.getElementById('card_6x').value = globalFees.card['6x'].toFixed(2);
    document.getElementById('card_min_transaction').value = globalFees.card.min_transaction !== null ? globalFees.card.min_transaction : '';
    document.getElementById('card_max_transaction').value = globalFees.card.max_transaction !== null ? globalFees.card.max_transaction : '';
    
    // Verificar se o usuário já tem taxas personalizadas
    fetch(`/admin/users/${userId}/fees`, {
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
            document.getElementById('pix_percentage').value = data.fees.pix.percentage.toFixed(2);
            document.getElementById('pix_min_transaction').value = data.fees.pix.min_transaction_value || '';
            document.getElementById('pix_max_transaction').value = data.fees.pix.max_transaction_value || '';
            
            document.getElementById('boleto_fixed').value = data.fees.bank_slip.fixed.toFixed(2);
            document.getElementById('boleto_variable').value = data.fees.bank_slip.percentage.toFixed(2);
            document.getElementById('boleto_min_transaction').value = data.fees.bank_slip.min_transaction_value || '';
            document.getElementById('boleto_max_transaction').value = data.fees.bank_slip.max_transaction_value || '';
            
            document.getElementById('card_fixed').value = data.fees.credit_card.fixed.toFixed(2);
            document.getElementById('card_1x').value = data.fees.credit_card.percentage.toFixed(2);
            document.getElementById('card_min_transaction').value = data.fees.credit_card.min_transaction_value || '';
            document.getElementById('card_max_transaction').value = data.fees.credit_card.max_transaction_value || '';
            
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

function openFeesModal() {
    closeCompanyActionsModal();
    
    // Preencher o formulário com as taxas globais
    document.getElementById('pix_fixed').value = globalFees.pix.fixed.toFixed(2);
    document.getElementById('pix_percentage').value = globalFees.pix.variable.toFixed(2);
    document.getElementById('pix_min_transaction').value = globalFees.pix.min_transaction !== null ? globalFees.pix.min_transaction : '';
    document.getElementById('pix_max_transaction').value = globalFees.pix.max_transaction !== null ? globalFees.pix.max_transaction : '';
    
    document.getElementById('boleto_fixed').value = globalFees.boleto.fixed.toFixed(2);
    document.getElementById('boleto_variable').value = globalFees.boleto.variable.toFixed(2);
    document.getElementById('boleto_min_transaction').value = globalFees.boleto.min_transaction !== null ? globalFees.boleto.min_transaction : '';
    document.getElementById('boleto_max_transaction').value = globalFees.boleto.max_transaction !== null ? globalFees.boleto.max_transaction : '';
    
    document.getElementById('card_fixed').value = globalFees.card.fixed.toFixed(2);
    document.getElementById('card_1x').value = globalFees.card['1x'].toFixed(2);
    document.getElementById('card_2x').value = globalFees.card['2x'].toFixed(2);
    document.getElementById('card_3x').value = globalFees.card['3x'].toFixed(2);
    document.getElementById('card_4x').value = globalFees.card['4x'].toFixed(2);
    document.getElementById('card_5x').value = globalFees.card['5x'].toFixed(2);
    document.getElementById('card_6x').value = globalFees.card['6x'].toFixed(2);
    document.getElementById('card_min_transaction').value = globalFees.card.min_transaction !== null ? globalFees.card.min_transaction : '';
    document.getElementById('card_max_transaction').value = globalFees.card.max_transaction !== null ? globalFees.card.max_transaction : '';
    
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
            document.getElementById('pix_percentage').value = data.fees.pix.percentage.toFixed(2);
            document.getElementById('pix_min_transaction').value = data.fees.pix.min_transaction_value || '';
            document.getElementById('pix_max_transaction').value = data.fees.pix.max_transaction_value || '';
            
            document.getElementById('boleto_fixed').value = data.fees.bank_slip.fixed.toFixed(2);
            document.getElementById('boleto_variable').value = data.fees.bank_slip.percentage.toFixed(2);
            document.getElementById('boleto_min_transaction').value = data.fees.bank_slip.min_transaction_value || '';
            document.getElementById('boleto_max_transaction').value = data.fees.bank_slip.max_transaction_value || '';
            
            document.getElementById('card_fixed').value = data.fees.credit_card.fixed.toFixed(2);
            document.getElementById('card_1x').value = data.fees.credit_card.percentage.toFixed(2);
            document.getElementById('card_min_transaction').value = data.fees.credit_card.min_transaction_value || '';
            document.getElementById('card_max_transaction').value = data.fees.credit_card.max_transaction_value || '';
            
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
        pix_percentage: parseFloat(document.getElementById('pix_percentage').value),
        pix_min_transaction: document.getElementById('pix_min_transaction').value ? parseFloat(document.getElementById('pix_min_transaction').value) : null,
        pix_max_transaction: document.getElementById('pix_max_transaction').value ? parseFloat(document.getElementById('pix_max_transaction').value) : null,
        boleto_fixed: parseFloat(document.getElementById('boleto_fixed').value),
        boleto_variable: parseFloat(document.getElementById('boleto_variable').value),
        boleto_min_transaction: document.getElementById('boleto_min_transaction').value ? parseFloat(document.getElementById('boleto_min_transaction').value) : null,
        boleto_max_transaction: document.getElementById('boleto_max_transaction').value ? parseFloat(document.getElementById('boleto_max_transaction').value) : null,
        card_fixed: parseFloat(document.getElementById('card_fixed').value),
        card_1x: parseFloat(document.getElementById('card_1x').value),
        card_2x: parseFloat(document.getElementById('card_2x').value),
        card_3x: parseFloat(document.getElementById('card_3x').value),
        card_4x: parseFloat(document.getElementById('card_4x').value),
        card_5x: parseFloat(document.getElementById('card_5x').value),
        card_6x: parseFloat(document.getElementById('card_6x').value),
        card_min_transaction: document.getElementById('card_min_transaction').value ? parseFloat(document.getElementById('card_min_transaction').value) : null,
        card_max_transaction: document.getElementById('card_max_transaction').value ? parseFloat(document.getElementById('card_max_transaction').value) : null
    };
    
    // Validate transaction limits
    if (formData.pix_min_transaction !== null && formData.pix_max_transaction !== null) {
        if (formData.pix_min_transaction > formData.pix_max_transaction) {
            alert('PIX: O valor mínimo da transação não pode ser maior que o valor máximo');
            return;
        }
    }
    
    if (formData.boleto_min_transaction !== null && formData.boleto_max_transaction !== null) {
        if (formData.boleto_min_transaction > formData.boleto_max_transaction) {
            alert('Boleto: O valor mínimo da transação não pode ser maior que o valor máximo');
            return;
        }
    }
    
    if (formData.card_min_transaction !== null && formData.card_max_transaction !== null) {
        if (formData.card_min_transaction > formData.card_max_transaction) {
            alert('Cartão: O valor mínimo da transação não pode ser maior que o valor máximo');
            return;
        }
    }
    
    // Send AJAX request
    fetch(`/admin/documents/fees/${currentUserId}`, {
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

function openPermissionsModal() {
    closeCompanyActionsModal();
    document.getElementById('permissionsModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closePermissionsModal() {
    document.getElementById('permissionsModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

function savePermissions() {
    // Get form data
    const formData = new FormData(document.getElementById('permissionsForm'));
    const data = {};
    
    // Handle checkboxes (multiple values)
    const paymentMethods = [];
    const security = [];
    const transferRules = [];
    const anticipationRules = [];
    
    formData.getAll('payment_methods[]').forEach(value => {
        paymentMethods.push(value);
    });
    
    formData.getAll('security[]').forEach(value => {
        security.push(value);
    });
    
    formData.getAll('transfer_rules[]').forEach(value => {
        transferRules.push(value);
    });
    
    formData.getAll('anticipation_rules[]').forEach(value => {
        anticipationRules.push(value);
    });
    
    data.payment_methods = paymentMethods;
    data.security = security;
    data.transfer_rules = transferRules;
    data.anticipation_rules = anticipationRules;
    data.withdrawal_type = formData.get('withdrawal_type');
    
    // Send AJAX request
    fetch(`/admin/documents/permissions/${currentUserId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Permissões salvas com sucesso!');
            closePermissionsModal();
        } else {
            alert('Erro ao salvar permissões: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao salvar permissões');
    });
}

function openLoginModal() {
    // Simulate login as user
    alert('Login como usuário simulado');
}

function openSubaccountsModal() {
    // Open subaccounts configuration
    alert('Configuração de subcontas');
}

function manageCompany() {
    // Redirect to company management
    window.location.href = `/admin/users/${currentUserId}`;
}

function approveCompany() {
    closeCompanyActionsModal();
    openApprovalModal();
}

function openApprovalModalDirect(verificationId, userId) {
    currentVerificationId = verificationId;
    currentUserId = userId;
    openApprovalModal();
}

function openApprovalModal() {
    // Set form values
    document.getElementById('verification_id').value = currentVerificationId;
    document.getElementById('user_id').value = currentUserId;
    
    // Show approval modal
    document.getElementById('approvalModal').classList.remove('hidden');
    document.body.classList.add('modal-open');
}

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.add('hidden');
    document.body.classList.remove('modal-open');
}

// Show/hide rejection reason based on status selection
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="status"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const rejectionContainer = document.getElementById('rejection_reason_container');
            if (this.value === 'recusado') {
                rejectionContainer.classList.remove('hidden');
                document.getElementById('rejection_reason').setAttribute('required', 'required');
            } else {
                rejectionContainer.classList.add('hidden');
                document.getElementById('rejection_reason').removeAttribute('required');
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/admin/documents/index.blade.php ENDPATH**/ ?>