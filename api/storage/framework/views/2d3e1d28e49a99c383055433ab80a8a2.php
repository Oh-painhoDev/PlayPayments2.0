<?php $__env->startSection('title', 'Taxas do Gateway'); ?>
<?php $__env->startSection('page-title', 'Taxas do Gateway ' . $gateway->name); ?>
<?php $__env->startSection('page-description', 'Configure as taxas que você paga para este gateway de pagamento'); ?>

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
            <form action="<?php echo e(route('admin.gateways.fees.update', $gateway->id)); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- PIX Fees -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <div class="p-2 bg-green-500/10 rounded-lg mr-2">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        Taxas do PIX
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pix_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxa Percentual (%)
                            </label>
                            <input 
                                id="pix_percentage" 
                                name="pix_percentage" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                max="100"
                                required 
                                value="<?php echo e(old('pix_percentage', $fees['pix']->percentage_fee)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="0.99"
                            >
                            <p class="text-xs text-gray-500 mt-1">Taxa percentual que você paga ao gateway</p>
                        </div>
                        
                        <div>
                            <label for="pix_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxa Fixa (R$)
                            </label>
                            <input 
                                id="pix_fixed" 
                                name="pix_fixed" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                value="<?php echo e(old('pix_fixed', $fees['pix']->fixed_fee)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="0.00"
                            >
                            <p class="text-xs text-gray-500 mt-1">Taxa fixa por transação</p>
                        </div>
                        
                        <div>
                            <label for="pix_min" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Mínimo (R$)
                            </label>
                            <input 
                                id="pix_min" 
                                name="pix_min" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                value="<?php echo e(old('pix_min', $fees['pix']->min_amount)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="0.01"
                            >
                            <p class="text-xs text-gray-500 mt-1">Valor mínimo da taxa (opcional)</p>
                        </div>
                        
                        <div>
                            <label for="pix_max" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Máximo (R$)
                            </label>
                            <input 
                                id="pix_max" 
                                name="pix_max" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                value="<?php echo e(old('pix_max', $fees['pix']->max_amount)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Sem limite"
                            >
                            <p class="text-xs text-gray-500 mt-1">Valor máximo da taxa (opcional)</p>
                        </div>
                    </div>
                </div>

                <!-- Credit Card Fees -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <div class="p-2 bg-blue-500/10 rounded-lg mr-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        Taxas do Cartão de Crédito
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="credit_card_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxa Percentual (%)
                            </label>
                            <input 
                                id="credit_card_percentage" 
                                name="credit_card_percentage" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                max="100"
                                required 
                                value="<?php echo e(old('credit_card_percentage', $fees['credit_card']->percentage_fee)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="2.99"
                            >
                            <p class="text-xs text-gray-500 mt-1">Taxa percentual que você paga ao gateway</p>
                        </div>
                        
                        <div>
                            <label for="credit_card_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxa Fixa (R$)
                            </label>
                            <input 
                                id="credit_card_fixed" 
                                name="credit_card_fixed" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                value="<?php echo e(old('credit_card_fixed', $fees['credit_card']->fixed_fee)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="0.30"
                            >
                            <p class="text-xs text-gray-500 mt-1">Taxa fixa por transação</p>
                        </div>
                        
                        <div>
                            <label for="credit_card_min" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Mínimo (R$)
                            </label>
                            <input 
                                id="credit_card_min" 
                                name="credit_card_min" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                value="<?php echo e(old('credit_card_min', $fees['credit_card']->min_amount)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="0.50"
                            >
                            <p class="text-xs text-gray-500 mt-1">Valor mínimo da taxa (opcional)</p>
                        </div>
                        
                        <div>
                            <label for="credit_card_max" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Máximo (R$)
                            </label>
                            <input 
                                id="credit_card_max" 
                                name="credit_card_max" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                value="<?php echo e(old('credit_card_max', $fees['credit_card']->max_amount)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Sem limite"
                            >
                            <p class="text-xs text-gray-500 mt-1">Valor máximo da taxa (opcional)</p>
                        </div>
                    </div>
                </div>

                <!-- Bank Slip Fees -->
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <div class="p-2 bg-orange-500/10 rounded-lg mr-2">
                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        Taxas do Boleto Bancário
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="bank_slip_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxa Percentual (%)
                            </label>
                            <input 
                                id="bank_slip_percentage" 
                                name="bank_slip_percentage" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                max="100"
                                required 
                                value="<?php echo e(old('bank_slip_percentage', $fees['bank_slip']->percentage_fee)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="1.99"
                            >
                            <p class="text-xs text-gray-500 mt-1">Taxa percentual que você paga ao gateway</p>
                        </div>
                        
                        <div>
                            <label for="bank_slip_fixed" class="block text-sm font-medium text-gray-700 mb-2">
                                Taxa Fixa (R$)
                            </label>
                            <input 
                                id="bank_slip_fixed" 
                                name="bank_slip_fixed" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                required 
                                value="<?php echo e(old('bank_slip_fixed', $fees['bank_slip']->fixed_fee)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="1.50"
                            >
                            <p class="text-xs text-gray-500 mt-1">Taxa fixa por transação</p>
                        </div>
                        
                        <div>
                            <label for="bank_slip_min" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Mínimo (R$)
                            </label>
                            <input 
                                id="bank_slip_min" 
                                name="bank_slip_min" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                value="<?php echo e(old('bank_slip_min', $fees['bank_slip']->min_amount)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="2.00"
                            >
                            <p class="text-xs text-gray-500 mt-1">Valor mínimo da taxa (opcional)</p>
                        </div>
                        
                        <div>
                            <label for="bank_slip_max" class="block text-sm font-medium text-gray-700 mb-2">
                                Valor Máximo (R$)
                            </label>
                            <input 
                                id="bank_slip_max" 
                                name="bank_slip_max" 
                                type="number" 
                                step="0.01" 
                                min="0" 
                                value="<?php echo e(old('bank_slip_max', $fees['bank_slip']->max_amount)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="Sem limite"
                            >
                            <p class="text-xs text-gray-500 mt-1">Valor máximo da taxa (opcional)</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="<?php echo e(route('admin.gateways.index')); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-200">
                        Voltar
                    </a>
                    <button 
                        type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-6 py-3 rounded-lg font-medium transition-all duration-200"
                    >
                        Salvar Taxas
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Gateway Info -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações do Gateway</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="text-sm text-gray-600">Nome:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($gateway->name); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Slug:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($gateway->slug); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">API URL:</span>
                        <p class="text-gray-900 font-medium"><?php echo e($gateway->api_url); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Tipo:</span>
                        <p class="text-gray-900 font-medium"><?php echo e(ucfirst($gateway->getConfig('gateway_type', 'avivhub'))); ?></p>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Status:</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-green-600 text-sm">Ativo</span>
                        </div>
                    </div>
                    
                    <div>
                        <span class="text-sm text-gray-600">Gateway Padrão:</span>
                        <span class="text-gray-900 font-medium"><?php echo e($gateway->is_default ? 'Sim' : 'Não'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Fee Calculation Example -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Exemplo de Cálculo</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="example_amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Valor da Transação
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600">R$</span>
                            <input 
                                id="example_amount" 
                                type="number" 
                                step="0.01"
                                min="0.01"
                                value="100.00"
                                class="w-full pl-8 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                placeholder="0,00"
                            >
                        </div>
                    </div>
                    
                    <div>
                        <label for="example_method" class="block text-sm font-medium text-gray-700 mb-2">
                            Método de Pagamento
                        </label>
                        <select 
                            id="example_method" 
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                        >
                            <option value="pix">PIX</option>
                            <option value="credit_card">Cartão de Crédito</option>
                            <option value="bank_slip">Boleto Bancário</option>
                        </select>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600 text-sm">Taxa Percentual:</span>
                            <span class="text-gray-900" id="calc_percentage">R$ 0,00</span>
                        </div>
                        
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-600 text-sm">Taxa Fixa:</span>
                            <span class="text-gray-900" id="calc_fixed">R$ 0,00</span>
                        </div>
                        
                        <div class="flex justify-between pt-2 border-t border-gray-200">
                            <span class="text-gray-600 text-sm">Taxa Total:</span>
                            <span class="text-green-600 font-medium" id="calc_total">R$ 0,00</span>
                        </div>
                        
                        <div class="flex justify-between mt-2">
                            <span class="text-gray-600 text-sm">Valor Líquido:</span>
                            <span class="text-green-600 font-medium" id="calc_net">R$ 0,00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações</h3>
                
                <div class="space-y-3">
                    <a href="<?php echo e(route('admin.gateways.index')); ?>" class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Voltar para Gateways
                    </a>
                    
                    <a href="<?php echo e(route('admin.gateways.edit', $gateway->id)); ?>" class="block w-full bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm text-center transition-colors">
                        Editar Gateway
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const exampleAmount = document.getElementById('example_amount');
    const exampleMethod = document.getElementById('example_method');
    
    // Get calculation elements
    const calcPercentage = document.getElementById('calc_percentage');
    const calcFixed = document.getElementById('calc_fixed');
    const calcTotal = document.getElementById('calc_total');
    const calcNet = document.getElementById('calc_net');
    
    // Gateway fees data
    const gatewayFees = {
        pix: {
            percentage: <?php echo e($fees['pix']->percentage_fee); ?>,
            fixed: <?php echo e($fees['pix']->fixed_fee); ?>,
            min: <?php echo e($fees['pix']->min_amount ?? 0); ?>,
            max: <?php echo e($fees['pix']->max_amount ?? 'null'); ?>

        },
        credit_card: {
            percentage: <?php echo e($fees['credit_card']->percentage_fee); ?>,
            fixed: <?php echo e($fees['credit_card']->fixed_fee); ?>,
            min: <?php echo e($fees['credit_card']->min_amount ?? 0); ?>,
            max: <?php echo e($fees['credit_card']->max_amount ?? 'null'); ?>

        },
        bank_slip: {
            percentage: <?php echo e($fees['bank_slip']->percentage_fee); ?>,
            fixed: <?php echo e($fees['bank_slip']->fixed_fee); ?>,
            min: <?php echo e($fees['bank_slip']->min_amount ?? 0); ?>,
            max: <?php echo e($fees['bank_slip']->max_amount ?? 'null'); ?>

        }
    };
    
    // Calculate fees
    function calculateFees() {
        const amount = parseFloat(exampleAmount.value) || 0;
        const method = exampleMethod.value;
        const fees = gatewayFees[method];
        
        // Calculate percentage fee
        const percentageFee = (amount * fees.percentage) / 100;
        
        // Calculate total fee
        let totalFee = percentageFee + fees.fixed;
        
        // Apply minimum fee if set
        if (fees.min && totalFee < fees.min) {
            totalFee = fees.min;
        }
        
        // Apply maximum fee if set
        if (fees.max !== null && totalFee > fees.max) {
            totalFee = fees.max;
        }
        
        // Calculate net amount
        const netAmount = amount - totalFee;
        
        // Update display
        calcPercentage.textContent = 'R$ ' + percentageFee.toFixed(2).replace('.', ',');
        calcFixed.textContent = 'R$ ' + fees.fixed.toFixed(2).replace('.', ',');
        calcTotal.textContent = 'R$ ' + totalFee.toFixed(2).replace('.', ',');
        calcNet.textContent = 'R$ ' + netAmount.toFixed(2).replace('.', ',');
    }
    
    // Add event listeners
    exampleAmount.addEventListener('input', calculateFees);
    exampleMethod.addEventListener('change', calculateFees);
    
    // Calculate initially
    calculateFees();
    
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Validate PIX fields
        const pixPercentage = parseFloat(document.getElementById('pix_percentage').value);
        const pixFixed = parseFloat(document.getElementById('pix_fixed').value);
        const pixMin = document.getElementById('pix_min').value ? parseFloat(document.getElementById('pix_min').value) : null;
        const pixMax = document.getElementById('pix_max').value ? parseFloat(document.getElementById('pix_max').value) : null;
        
        // Validate Credit Card fields
        const creditCardPercentage = parseFloat(document.getElementById('credit_card_percentage').value);
        const creditCardFixed = parseFloat(document.getElementById('credit_card_fixed').value);
        const creditCardMin = document.getElementById('credit_card_min').value ? parseFloat(document.getElementById('credit_card_min').value) : null;
        const creditCardMax = document.getElementById('credit_card_max').value ? parseFloat(document.getElementById('credit_card_max').value) : null;
        
        // Validate Bank Slip fields
        const bankSlipPercentage = parseFloat(document.getElementById('bank_slip_percentage').value);
        const bankSlipFixed = parseFloat(document.getElementById('bank_slip_fixed').value);
        const bankSlipMin = document.getElementById('bank_slip_min').value ? parseFloat(document.getElementById('bank_slip_min').value) : null;
        const bankSlipMax = document.getElementById('bank_slip_max').value ? parseFloat(document.getElementById('bank_slip_max').value) : null;
        
        // Check if min > max for any method
        if (pixMin && pixMax && pixMin > pixMax) {
            e.preventDefault();
            alert('PIX: O valor mínimo não pode ser maior que o valor máximo');
            return;
        }
        
        if (creditCardMin && creditCardMax && creditCardMin > creditCardMax) {
            e.preventDefault();
            alert('Cartão de Crédito: O valor mínimo não pode ser maior que o valor máximo');
            return;
        }
        
        if (bankSlipMin && bankSlipMax && bankSlipMin > bankSlipMax) {
            e.preventDefault();
            alert('Boleto: O valor mínimo não pode ser maior que o valor máximo');
            return;
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/gateways/fees.blade.php ENDPATH**/ ?>