<?php $__env->startSection('title', 'Painel Administrativo'); ?>
<?php $__env->startSection('page-title', 'Dashboard Admin'); ?>
<?php $__env->startSection('page-description', 'Visão geral do sistema e estatísticas'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">
    <!-- Date Filter -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <form method="GET" action="<?php echo e(route('admin.dashboard')); ?>" class="flex flex-wrap gap-4">
            <div class="flex items-center">
                <label for="date_from" class="text-sm text-gray-600 mr-2">De:</label>
                <input 
                    type="date" 
                    id="date_from" 
                    name="date_from" 
                    value="<?php echo e($startDate->format('Y-m-d')); ?>"
                    class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm px-3 py-2"
                >
            </div>
            
            <div class="flex items-center">
                <label for="date_to" class="text-sm text-gray-600 mr-2">Até:</label>
                <input 
                    type="date" 
                    id="date_to" 
                    name="date_to" 
                    value="<?php echo e($endDate->format('Y-m-d')); ?>"
                    class="bg-gray-50 border border-gray-300 rounded-lg text-gray-900 text-sm px-3 py-2"
                >
            </div>
            
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-gray-900 px-4 py-2 rounded-lg text-sm">
                Filtrar
            </button>
            
            <div class="ml-auto text-right">
                <p class="text-sm text-gray-600">Período: <?php echo e($startDate->format('d/m/Y')); ?> até <?php echo e($endDate->format('d/m/Y')); ?></p>
                <p class="text-xs text-gray-500"><?php echo e($endDate->diffInDays($startDate) + 1); ?> dias</p>
            </div>
        </form>
    </div>

    <!-- Main Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-gray-300 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="p-3 bg-purple-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-xs text-purple-500 font-medium bg-purple-500/10 px-2 py-1 rounded-full">Total</span>
                </div>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">R$ <?php echo e(number_format($stats['total_revenue'], 2, ',', '.')); ?></p>
                <p class="text-sm text-gray-600 mt-1">Receita Total</p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e($stats['total_transactions']); ?> transações</p>
            </div>
        </div>

        <!-- Gateway Fees -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-gray-300 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="p-3 bg-green-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-xs text-green-500 font-medium bg-green-500/10 px-2 py-1 rounded-full">Custos</span>
                </div>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">R$ <?php echo e(number_format($stats['profit_details']['gateway_fees'], 2, ',', '.')); ?></p>
                <p class="text-sm text-gray-600 mt-1">Taxas de Gateway</p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e(number_format(($stats['profit_details']['gateway_fees'] / max($stats['total_revenue'], 1)) * 100, 2)); ?>% da receita</p>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-white rounded-lg p-6 border border-gray-200 hover:border-gray-300 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <div class="p-3 bg-green-500/10 rounded-lg">
                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="text-right">
                    <span class="text-xs text-green-500 font-medium bg-green-500/10 px-2 py-1 rounded-full">Lucro</span>
                </div>
            </div>
            <div>
                <p class="text-2xl font-bold text-green-600">R$ <?php echo e(number_format($stats['profit_details']['total_profit'], 2, ',', '.')); ?></p>
                <p class="text-sm text-gray-600 mt-1">Lucro Líquido</p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e(number_format(($stats['profit_details']['total_profit'] / max($stats['total_revenue'], 1)) * 100, 2)); ?>% de margem</p>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <!-- Total Users -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['total_users'])); ?></p>
            <p class="text-xs text-gray-600">Usuários</p>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-indigo-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['total_transactions'])); ?></p>
            <p class="text-xs text-gray-600">Transações</p>
        </div>

        <!-- Average Ticket -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-yellow-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900">R$ <?php echo e(number_format($stats['average_ticket'], 2, ',', '.')); ?></p>
            <p class="text-xs text-gray-600">Ticket Médio</p>
        </div>

        <!-- Conversion Rate -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-emerald-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['paid_transactions'] / max($stats['total_transactions'], 1) * 100, 1)); ?>%</p>
            <p class="text-xs text-gray-600">Taxa de Conversão</p>
        </div>

        <!-- Refund Rate -->
        <div class="bg-white rounded-lg p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <div class="p-2 bg-orange-500/10 rounded-lg">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-lg font-bold text-gray-900"><?php echo e(number_format($stats['refund_info']['total_refund_percentage'], 1)); ?>%</p>
            <p class="text-xs text-gray-600">Taxa de Reembolso</p>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="bg-white rounded-lg p-6 border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Vendas por Período</h3>
                <p class="text-sm text-gray-600 mt-1">Evolução das vendas no período selecionado</p>
            </div>
        </div>
        
        <!-- Chart Container -->
        <div class="h-80">
            <canvas id="salesChart" class="w-full h-full"></canvas>
        </div>
    </div>

    <!-- Profit Details -->
    <div class="bg-white rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes do Lucro</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Taxas Cobradas</h4>
                <p class="text-xl font-bold text-gray-900">R$ <?php echo e(number_format($stats['profit_details']['total_user_fees'], 2, ',', '.')); ?></p>
                <p class="text-xs text-gray-500 mt-1">Total cobrado dos usuários</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Taxas Pagas</h4>
                <p class="text-xl font-bold text-green-600">R$ <?php echo e(number_format($stats['profit_details']['gateway_fees'], 2, ',', '.')); ?></p>
                <p class="text-xs text-gray-500 mt-1">Total pago aos gateways</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Reembolsos</h4>
                <p class="text-xl font-bold text-orange-400">R$ <?php echo e(number_format($stats['refund_info']['total_refund_amount'], 2, ',', '.')); ?></p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e($stats['refund_info']['total_refund_count']); ?> transações</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Lucro por Transação</h4>
                <p class="text-xl font-bold text-gray-900">R$ <?php echo e(number_format($stats['profit_details']['transaction_count'] > 0 ? $stats['profit_details']['total_profit'] / $stats['profit_details']['transaction_count'] : 0, 2, ',', '.')); ?></p>
                <p class="text-xs text-gray-500 mt-1">Média por transação</p>
            </div>
        </div>
    </div>

    <!-- Refund Details -->
    <div class="bg-white rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detalhes de Reembolsos</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Chargebacks</h4>
                <p class="text-xl font-bold text-green-600">R$ <?php echo e(number_format($stats['refund_info']['chargeback_amount'], 2, ',', '.')); ?></p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e($stats['refund_info']['chargeback_count']); ?> transações</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Reembolsos Manuais</h4>
                <p class="text-xl font-bold text-orange-400">R$ <?php echo e(number_format($stats['refund_info']['manual_refund_amount'], 2, ',', '.')); ?></p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e($stats['refund_info']['manual_refund_count']); ?> transações</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Taxa de Chargeback</h4>
                <p class="text-xl font-bold text-gray-900"><?php echo e(number_format($stats['refund_info']['chargeback_percentage'], 2)); ?>%</p>
                <p class="text-xs text-gray-500 mt-1">Percentual sobre vendas</p>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <h4 class="text-sm font-medium text-gray-600 mb-2">Taxa Total de Reembolso</h4>
                <p class="text-xl font-bold text-gray-900"><?php echo e(number_format($stats['refund_info']['total_refund_percentage'], 2)); ?>%</p>
                <p class="text-xs text-gray-500 mt-1">Percentual sobre vendas</p>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartData['sales_labels']); ?>,
            datasets: [{
                label: 'Vendas',
                data: <?php echo json_encode($chartData['sales_data']); ?>,
                borderColor: '#8b5cf6',
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('pt-BR', { 
                                    style: 'currency', 
                                    currency: 'BRL' 
                                }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(55, 65, 81, 0.3)'
                    },
                    ticks: {
                        color: '#9ca3af'
                    }
                },
                y: {
                    grid: {
                        color: 'rgba(55, 65, 81, 0.3)'
                    },
                    ticks: {
                        color: '#9ca3af',
                        callback: function(value) {
                            return new Intl.NumberFormat('pt-BR', { 
                                style: 'currency', 
                                currency: 'BRL',
                                minimumFractionDigits: 0,
                                maximumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });

    // Date range validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const dateFrom = new Date(document.getElementById('date_from').value);
        const dateTo = new Date(document.getElementById('date_to').value);
        
        if (dateFrom > dateTo) {
            e.preventDefault();
            alert('A data inicial não pode ser maior que a data final');
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>