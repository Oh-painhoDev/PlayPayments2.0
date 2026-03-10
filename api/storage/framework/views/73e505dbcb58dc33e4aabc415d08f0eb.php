<?php $__env->startSection('title', 'Editar Usuário'); ?>
<?php $__env->startSection('page-title', 'Editar Usuário'); ?>
<?php $__env->startSection('page-description', 'Configure gateway para ' . $user->name); ?>

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
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form action="<?php echo e(route('admin.users.update', $user)); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- User Role -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Cargo do Usuário</h3>
                    
                    <div>
                        <label for="role" class="block text-sm font-medium text-[#6B7280] mb-2">
                            Cargo
                        </label>
                        <select 
                            id="role" 
                            name="role" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            <?php if(!auth()->user()->isAdmin()): ?> disabled <?php endif; ?>
                        >
                            <option value="user" <?php echo e($user->role === 'user' ? 'selected' : ''); ?>>Usuário</option>
                            <option value="gerente" <?php echo e($user->role === 'gerente' ? 'selected' : ''); ?>>Gerente</option>
                            <option value="admin" <?php echo e($user->role === 'admin' ? 'selected' : ''); ?>>Administrador</option>
                        </select>
                        <?php if(!auth()->user()->isAdmin()): ?>
                            <input type="hidden" name="role" value="<?php echo e($user->role); ?>">
                            <p class="text-xs text-yellow-400 mt-1">
                                ⚠️ Apenas administradores podem alterar o cargo de um usuário.
                            </p>
                        <?php else: ?>
                            <p class="text-xs text-gray-500 mt-1">
                                <strong>Usuário:</strong> Acesso normal ao sistema<br>
                                <strong>Gerente:</strong> Acesso ao painel administrativo<br>
                                <strong>Administrador:</strong> Acesso completo ao sistema
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Gateway Assignment -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Atribuição de Gateway</h3>
                    
                    <div>
                        <label for="assigned_gateway_id" class="block text-sm font-medium text-[#6B7280] mb-2">
                            Gateway Atribuído
                        </label>
                        <select 
                            id="assigned_gateway_id" 
                            name="assigned_gateway_id" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Nenhum gateway atribuído</option>
                            <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($gateway->id); ?>" <?php echo e($user->assigned_gateway_id == $gateway->id ? 'selected' : ''); ?>>
                                    <?php echo e($gateway->name); ?> <?php echo e($gateway->is_default ? '(Padrão)' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Escolha qual adquirente este usuário irá utilizar</p>
                    </div>

                    <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                        <p class="text-blue-700 text-xs">
                            💡 <strong>Importante:</strong> O usuário utilizará as credenciais configuradas pelo administrador no gateway selecionado.
                        </p>
                    </div>
                </div>

                <!-- Withdrawal Type -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Configuração de Saque</h3>
                    
                    <div>
                        <label class="block text-sm font-medium text-[#6B7280] mb-3">Tipo de Saque</label>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="withdrawal_manual" 
                                    name="withdrawal_type" 
                                    value="manual" 
                                    <?php echo e($user->withdrawal_type !== 'automatic' ? 'checked' : ''); ?>

                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                                >
                                <label for="withdrawal_manual" class="ml-2 text-sm text-[#6B7280]">
                                    Manual (requer aprovação do administrador)
                                </label>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="radio" 
                                    id="withdrawal_automatic" 
                                    name="withdrawal_type" 
                                    value="automatic" 
                                    <?php echo e($user->withdrawal_type === 'automatic' ? 'checked' : ''); ?>

                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-600 bg-gray-800 rounded"
                                >
                                <label for="withdrawal_automatic" class="ml-2 text-sm text-[#6B7280]">
                                    Automático (sem aprovação)
                                </label>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            Define se os saques do usuário serão processados automaticamente ou se precisarão de aprovação manual.
                        </p>
                    </div>

                    <div class="mt-4 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                        <p class="text-yellow-300 text-xs">
                            ⚠️ <strong>Atenção:</strong> Saques automáticos são processados imediatamente sem revisão. Use com cuidado.
                        </p>
                    </div>

                    <!-- BaaS Selection for Automatic Withdrawals -->
                    <div id="baas-selection" class="mt-6" style="<?php echo e($user->withdrawal_type === 'automatic' ? '' : 'display: none;'); ?>">
                        <label for="assigned_baas_id" class="block text-sm font-medium text-[#6B7280] mb-2">
                            Provedor BaaS para Saques Automáticos
                        </label>
                        <select 
                            id="assigned_baas_id" 
                            name="assigned_baas_id" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Usar BaaS padrão</option>
                            <?php $__currentLoopData = $activeBaas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $baas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($baas->id); ?>" <?php echo e($user->assigned_baas_id == $baas->id ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($baas->gateway)); ?> <?php echo e($baas->is_default ? '(Padrão)' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Selecione qual provedor BaaS este usuário utilizará para saques automáticos
                        </p>
                    </div>

                    <!-- Retry Gateway Selection -->
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-[#6B7280]">
                                Gateway de Retentativa Individual
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    name="retry_enabled" 
                                    value="1"
                                    <?php echo e($user->retry_enabled ? 'checked' : ''); ?>

                                    class="sr-only peer"
                                    id="retry_enabled"
                                >
                                <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        
                        <select 
                            id="retry_gateway_id" 
                            name="retry_gateway_id" 
                            class="w-full px-4 py-3 bg-[#1a1a1a] border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">Usar configuração global</option>
                            <?php $__currentLoopData = $gateways; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gateway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($gateway->id); ?>" <?php echo e($user->retry_gateway_id == $gateway->id ? 'selected' : ''); ?>>
                                    <?php echo e($gateway->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            Se o gateway principal falhar, tentará automaticamente neste gateway alternativo
                        </p>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const withdrawalTypeInputs = document.querySelectorAll('input[name="withdrawal_type"]');
                        const baasSelection = document.getElementById('baas-selection');
                        
                        withdrawalTypeInputs.forEach(input => {
                            input.addEventListener('change', function() {
                                if (this.value === 'automatic') {
                                    baasSelection.style.display = 'block';
                                } else {
                                    baasSelection.style.display = 'none';
                                }
                            });
                        });
                    });
                </script>

                <!-- Access User Account Button -->
                <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">Acessar Conta do Usuário</h3>
                    
                    <a 
                        href="<?php echo e(route('admin.users.login.as.user', $user)); ?>" 
                        class="w-full bg-orange-600/80 hover:bg-orange-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-200 block text-center"
                        onclick="return confirm('Tem certeza que deseja acessar a conta deste usuário?');"
                    >
                        Acessar Conta do Usuário
                    </a>
                    
                    <p class="text-xs text-gray-500 mt-2">
                        Acesse a conta deste usuário para visualizar o painel como ele vê e resolver problemas.
                    </p>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        class="bg-[#21b3dd] hover:bg-[#7A0000] text-white px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info -->
            <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Informações do Usuário</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Nome:</span>
                        <p class="text-white font-medium"><?php echo e($user->name); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Email:</span>
                        <p class="text-white font-medium"><?php echo e($user->email); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Cargo:</span>
                        <p class="text-white font-medium">
                            <?php if($user->role === 'admin'): ?>
                                Administrador
                            <?php elseif($user->role === 'gerente'): ?>
                                Gerente
                            <?php else: ?>
                                Usuário
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Tipo:</span>
                        <p class="text-white font-medium"><?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Documento:</span>
                        <p class="text-white font-medium"><?php echo e($user->formatted_document); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Cadastro:</span>
                        <p class="text-white font-medium"><?php echo e($user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Current Gateway -->
            <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Gateway Atual</h3>
                
                <?php if($user->assignedGateway): ?>
                    <div class="space-y-2">
                        <p class="text-white font-medium"><?php echo e($user->assignedGateway->name); ?></p>
                        <p class="text-gray-600 text-sm"><?php echo e($user->assignedGateway->api_url); ?></p>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-green-600 text-sm">Ativo</span>
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

            <!-- Actions -->
            <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="block w-full bg-gray-200 hover:bg-gray-300 text-[#6B7280] px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Ver Detalhes
                    </a>
                    
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="block w-full bg-gray-200 hover:bg-gray-300 text-[#6B7280] px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar à Lista
                    </a>
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
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>