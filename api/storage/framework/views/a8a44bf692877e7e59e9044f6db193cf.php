<?php $__env->startSection('title', 'Reembolsos'); ?>

<?php $__env->startSection('content'); ?>
<div class="flex-1 overflow-y-auto bg-[#000000] p-5">
    <div class="max-w-[1600px] mx-auto">
        <div class="bg-[#000000] rounded-2xl space-y-6">
            <!-- Header -->
            <div class="content-stretch flex flex-col md:flex-row items-start md:items-center justify-start md:justify-between relative size-full p-5 gap-6 md:gap-0">
                <div class="content-stretch flex flex-col gap-2.5 items-start justify-start leading-[0] relative shrink-0 text-nowrap">
                    <div class="font-['Manrope:Regular',_sans-serif] font-normal relative shrink-0 text-[28px] tracking-[-0.56px]">
                        <h1 class="leading-[1.2] text-nowrap whitespace-pre text-white">Reembolsos</h1>
                    </div>
                    <div class="font-['Manrope:SemiBold',_sans-serif] font-regular relative shrink-0 text-[12px] tracking-[-0.24px]">
                        <p class="leading-[1.3] text-nowrap whitespace-pre text-[#AAAAAA]">Gerencie e acompanhe todas as suas disputas e reembolsos</p>
                    </div>
                </div>
            </div>

            <?php if(session('success')): ?>
                <div class="mx-5 p-4 bg-[#1f1f1f] border border-[#2d2d2d] rounded-lg flex items-center">
                    <svg class="w-5 h-5 text-[#22C672] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-white font-semibold text-sm"><?php echo e(session('success')); ?></p>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="mx-5 p-4 bg-[#1f1f1f] border border-[#ff6b6b] rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-[#ff6b6b] mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p class="text-[#ff6b6b] font-semibold text-sm"><?php echo e($error); ?></p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cards de Métricas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 px-5">
                <!-- Total de Infrações -->
                <div class="bg-[#1f1f1f] rounded-2xl p-5 border border-[#2d2d2d]">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-[#707070] uppercase tracking-wide">Total Infrações</span>
                        <div class="w-10 h-10 bg-[#1f1f1f] rounded-xl flex items-center justify-center border border-[#2d2d2d]">
                            <svg class="w-5 h-5 text-[#707070]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-white"><?php echo e($stats['total_disputes'] ?? 0); ?></p>
                </div>

                <!-- Pendentes -->
                <div class="bg-[#1f1f1f] rounded-2xl p-5 border border-[#2d2d2d]">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-[#707070] uppercase tracking-wide">Pendentes</span>
                        <div class="w-10 h-10 bg-[#1f1f1f] rounded-xl flex items-center justify-center border border-[#2d2d2d]">
                            <svg class="w-5 h-5 text-[#707070]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-white"><?php echo e($stats['pending'] ?? 0); ?></p>
                </div>

                <!-- Defendidas -->
                <div class="bg-[#1f1f1f] rounded-2xl p-5 border border-[#2d2d2d]">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-[#707070] uppercase tracking-wide">Defendidas</span>
                        <div class="w-10 h-10 bg-[#1f1f1f] rounded-xl flex items-center justify-center border border-[#2d2d2d]">
                            <svg class="w-5 h-5 text-[#707070]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-white"><?php echo e($stats['defended'] ?? 0); ?></p>
                </div>

                <!-- Reembolsadas -->
                <div class="bg-[#1f1f1f] rounded-2xl p-5 border border-[#2d2d2d]">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-semibold text-[#707070] uppercase tracking-wide">Reembolsadas</span>
                        <div class="w-10 h-10 bg-[#1f1f1f] rounded-xl flex items-center justify-center border border-[#2d2d2d]">
                            <svg class="w-5 h-5 text-[#707070]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-white"><?php echo e($stats['refunded'] ?? 0); ?></p>
                </div>
            </div>

            <!-- Lista de Infrações -->
            <div class="bg-[#161616] rounded-2xl border border-[#1f1f1f] overflow-hidden mx-5 mb-5">
                <div class="p-5 border-b border-[#1f1f1f]">
                    <h3 class="text-lg font-medium text-white flex items-center">
                        <span class="w-8 h-8 bg-[#1f1f1f] rounded-lg flex items-center justify-center mr-3 border border-[#2d2d2d]">
                            <svg class="w-4 h-4 text-[#707070]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </span>
                        Histórico de Infrações
                    </h3>
                </div>

                <?php if($disputes->count() > 0): ?>
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-[#1f1f1f] border-b border-[#2d2d2d]">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">ID Infração</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Transação</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Valor</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Tipo</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Risco</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Data Criação</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-[#707070] uppercase tracking-wider">Prazo Limite</th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-[#707070] uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-[#161616] divide-y divide-[#1f1f1f]">
                                <?php $__currentLoopData = $disputes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dispute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-[#1f1f1f] transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm font-semibold text-white"><?php echo e($dispute->dispute_id); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-mono text-sm text-[#AAAAAA]"><?php echo e($dispute->transaction->transaction_id ?? 'N/A'); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="font-bold text-white">R$ <?php echo e(number_format($dispute->amount, 2, ',', '.')); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#AAAAAA] border border-[#2d2d2d]">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $dispute->dispute_type))); ?>

                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($dispute->risk_level === 'LOW'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#22C672] border border-[#2d2d2d]">Baixo</span>
                                            <?php elseif($dispute->risk_level === 'MED'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#FF9D3A] border border-[#2d2d2d]">Médio</span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#ff6b6b] border border-[#2d2d2d]">Alto</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($dispute->status === 'pending'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#FF9D3A] border border-[#2d2d2d]">Pendente</span>
                                            <?php elseif($dispute->status === 'responded'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#AAAAAA] border border-[#2d2d2d]">Respondido</span>
                                            <?php elseif($dispute->status === 'defended'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#22C672] border border-[#2d2d2d]">Defendido</span>
                                            <?php elseif($dispute->status === 'defense_rejected'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#ff6b6b] border border-[#2d2d2d]">Defesa Rejeitada</span>
                                            <?php elseif($dispute->status === 'refunded'): ?>
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#707070] border border-[#2d2d2d]">Reembolsado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-[#AAAAAA]">
                                            <?php echo e($dispute->created_at->format('d/m/Y H:i')); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($dispute->dispute_deadline): ?>
                                                <?php
                                                    $isExpired = $dispute->isExpired();
                                                    $remainingDays = $dispute->getRemainingDays();
                                                ?>
                                                <?php if($isExpired): ?>
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-[#1f1f1f] text-[#ff6b6b] border border-[#2d2d2d]">
                                                        Vencido em <?php echo e(\Carbon\Carbon::parse($dispute->dispute_deadline)->format('d/m/Y')); ?>

                                                    </span>
                                                <?php elseif($remainingDays <= 1): ?>
                                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-[#1f1f1f] text-[#FF9D3A] border border-[#2d2d2d]">
                                                        <?php echo e(\Carbon\Carbon::parse($dispute->dispute_deadline)->format('d/m/Y H:i')); ?> (<?php echo e($remainingDays); ?>d)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#AAAAAA] border border-[#2d2d2d]">
                                                        <?php echo e(\Carbon\Carbon::parse($dispute->dispute_deadline)->format('d/m/Y H:i')); ?>

                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-xs text-[#707070]">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <?php if($dispute->canDefend()): ?>
                                                    <button onclick="openDefenseModal(<?php echo e($dispute->id); ?>, '<?php echo e($dispute->dispute_id); ?>')" 
                                                            class="bg-[#21b3dd] hover:bg-[#21b3dd] text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                                                        Defender
                                                    </button>
                                                <?php endif; ?>
                                                <?php if($dispute->canRefund()): ?>
                                                    <form action="<?php echo e(route('refunds.refund', $dispute)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja reembolsar esta infração? O valor será debitado do seu saldo.')">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="bg-[#ff6b6b] hover:bg-[#ff5555] text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all">
                                                            Reembolsar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if($dispute->status !== 'pending'): ?>
                                                    <button onclick="openDisputeDetailsModal(<?php echo e($dispute->id); ?>)" class="text-[#21b3dd] hover:text-[#21b3dd] text-xs font-semibold">
                                                        Ver Detalhes
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden divide-y divide-[#1f1f1f]">
                        <?php $__currentLoopData = $disputes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dispute): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-4">
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center justify-between">
                                        <span class="font-mono text-sm font-semibold text-white"><?php echo e($dispute->dispute_id); ?></span>
                                        <?php if($dispute->status === 'pending'): ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#FF9D3A] border border-[#2d2d2d]">Pendente</span>
                                        <?php elseif($dispute->status === 'defended'): ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#22C672] border border-[#2d2d2d]">Defendido</span>
                                        <?php elseif($dispute->status === 'refunded'): ?>
                                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-[#1f1f1f] text-[#707070] border border-[#2d2d2d]">Reembolsado</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-sm text-[#AAAAAA]">
                                        <p>Transação: <span class="text-white"><?php echo e($dispute->transaction->transaction_id ?? 'N/A'); ?></span></p>
                                        <p>Valor: <span class="text-white font-bold">R$ <?php echo e(number_format($dispute->amount, 2, ',', '.')); ?></span></p>
                                        <p>Data: <span class="text-white"><?php echo e($dispute->created_at->format('d/m/Y H:i')); ?></span></p>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <?php if($dispute->canDefend()): ?>
                                            <button onclick="openDefenseModal(<?php echo e($dispute->id); ?>, '<?php echo e($dispute->dispute_id); ?>')" 
                                                    class="bg-[#21b3dd] hover:bg-[#21b3dd] text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                                                Defender
                                            </button>
                                        <?php endif; ?>
                                        <?php if($dispute->canRefund()): ?>
                                            <form action="<?php echo e(route('refunds.refund', $dispute)); ?>" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja reembolsar esta infração? O valor será debitado do seu saldo.')">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="bg-[#ff6b6b] hover:bg-[#ff5555] text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                                                    Reembolsar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 bg-[#1f1f1f] border-t border-[#2d2d2d]">
                        <?php echo e($disputes->appends(request()->query())->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-[#707070] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-xl font-semibold text-white mb-2">Nenhuma infração encontrada</p>
                        <p class="text-[#AAAAAA]">Você não possui infrações no momento.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Defesa -->
<div id="defenseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-[#161616] rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-[#1f1f1f]">
        <div class="bg-[#1f1f1f] p-6 rounded-t-2xl border-b border-[#2d2d2d]">
            <h3 class="text-2xl font-medium text-white flex items-center">
                Defender Infração
            </h3>
            <p class="text-[#AAAAAA] mt-2 text-sm">Apresente sua defesa com detalhes e anexe um documento PDF</p>
        </div>
        <form id="defenseForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <input type="hidden" id="disputeIdField" name="dispute_id">
            
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4">
                <p class="text-sm text-[#FF9D3A] font-semibold">
                    <strong>Atenção:</strong> Sua defesa deve conter no mínimo 50 caracteres e você deve anexar um arquivo PDF com as evidências.
                </p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Detalhes da Defesa *</label>
                <textarea name="defense_details" rows="6" required minlength="50"
                          class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-[#21b3dd] focus:border-transparent transition-all placeholder-[#707070]"
                          placeholder="Descreva detalhadamente os motivos da sua defesa, incluindo provas, evidências e justificativas..."></textarea>
                <p class="text-xs text-[#707070] mt-1">Mínimo de 50 caracteres</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-[#AAAAAA] mb-2">Anexar Documento PDF *</label>
                <input type="file" name="defense_file" accept=".pdf" required
                       class="w-full px-4 py-3 bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-[#21b3dd] focus:border-transparent transition-all">
                <p class="text-xs text-[#707070] mt-1">Tamanho máximo: 10MB | Formato: PDF</p>
            </div>

            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-[#21b3dd] hover:bg-[#21b3dd] text-white px-6 py-3 rounded-xl font-semibold transition-all">
                    Enviar Defesa
                </button>
                <button type="button" onclick="closeDefenseModal()" class="px-6 py-3 bg-[#1f1f1f] hover:bg-[#2d2d2d] text-white rounded-xl font-semibold transition-all border border-[#2d2d2d]">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Detalhes da Infração -->
<div id="disputeDetailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-[#161616] rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-[#1f1f1f]">
        <div class="bg-[#1f1f1f] p-6 rounded-t-2xl border-b border-[#2d2d2d]">
            <h3 class="text-2xl font-medium text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Detalhes da Infração
            </h3>
            <p class="text-[#AAAAAA] mt-2 text-sm">Informações completas sobre a infração</p>
        </div>
        
        <div id="disputeDetailsContent" class="p-6 space-y-6">
            <!-- Conteúdo será preenchido via JavaScript -->
        </div>
        
        <div class="p-6 bg-[#1f1f1f] rounded-b-2xl border-t border-[#2d2d2d]">
            <button type="button" onclick="closeDisputeDetailsModal()" class="w-full bg-[#1f1f1f] hover:bg-[#2d2d2d] text-white px-6 py-3 rounded-xl font-semibold transition-all border border-[#2d2d2d]">
                Fechar
            </button>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const disputes = <?php echo json_encode($disputes, 15, 512) ?>;

function openDefenseModal(disputeId, disputeCode) {
    const modal = document.getElementById('defenseModal');
    const form = document.getElementById('defenseForm');
    form.action = `/refunds/${disputeId}/defend`;
    modal.classList.remove('hidden');
}

function closeDefenseModal() {
    const modal = document.getElementById('defenseModal');
    modal.classList.add('hidden');
    document.getElementById('defenseForm').reset();
}

function openDisputeDetailsModal(disputeId) {
    const dispute = disputes.data.find(d => d.id === disputeId);
    if (!dispute) return;
    
    const statusLabels = {
        'pending': 'Pendente',
        'responded': 'Respondido',
        'defended': 'Defendido',
        'defense_rejected': 'Defesa Rejeitada',
        'refunded': 'Reembolsado'
    };
    
    const riskLabels = {
        'LOW': 'Baixo',
        'MED': 'Médio',
        'HIGH': 'Alto'
    };
    
    const statusColors = {
        'pending': 'bg-[#1f1f1f] text-[#FF9D3A] border-[#2d2d2d]',
        'responded': 'bg-[#1f1f1f] text-[#AAAAAA] border-[#2d2d2d]',
        'defended': 'bg-[#1f1f1f] text-[#22C672] border-[#2d2d2d]',
        'defense_rejected': 'bg-[#1f1f1f] text-[#ff6b6b] border-[#2d2d2d]',
        'refunded': 'bg-[#1f1f1f] text-[#707070] border-[#2d2d2d]'
    };
    
    const content = `
        <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-xs font-semibold text-[#707070] uppercase mb-1">ID da Infração</p>
                    <p class="text-2xl font-bold text-white">${dispute.dispute_id}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold text-[#707070] uppercase mb-1">Status</p>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full border ${statusColors[dispute.status]}">
                        ${statusLabels[dispute.status]}
                    </span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4">
                <p class="text-xs font-semibold text-[#707070] uppercase mb-1">Transação</p>
                <p class="text-lg font-semibold text-white">${dispute.transaction?.transaction_id || 'N/A'}</p>
            </div>
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4">
                <p class="text-xs font-semibold text-[#707070] uppercase mb-1">Valor</p>
                <p class="text-lg font-bold text-[#21b3dd]">R$ ${parseFloat(dispute.amount).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</p>
            </div>
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4">
                <p class="text-xs font-semibold text-[#707070] uppercase mb-1">Tipo</p>
                <p class="text-lg font-semibold text-white">${dispute.dispute_type.replace('_', ' ')}</p>
            </div>
            <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4">
                <p class="text-xs font-semibold text-[#707070] uppercase mb-1">Nível de Risco</p>
                <p class="text-lg font-semibold text-white">${riskLabels[dispute.risk_level]}</p>
            </div>
        </div>
        
        ${dispute.reason ? `
        <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4 mb-6">
            <p class="text-xs font-semibold text-[#FF9D3A] uppercase mb-2">Motivo da Infração</p>
            <p class="text-sm text-[#AAAAAA]">${dispute.reason}</p>
        </div>
        ` : ''}
        
        ${dispute.defense_details ? `
        <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4 mb-6">
            <p class="text-xs font-semibold text-[#AAAAAA] uppercase mb-2">Sua Defesa</p>
            <p class="text-sm text-[#AAAAAA] whitespace-pre-line">${dispute.defense_details}</p>
        </div>
        ` : ''}
        
        ${dispute.admin_response ? `
        <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4 mb-6">
            <p class="text-xs font-semibold text-[#AAAAAA] uppercase mb-2">Resposta do Admin</p>
            <p class="text-sm text-[#AAAAAA]">${dispute.admin_response}</p>
        </div>
        ` : ''}
        
        <div class="bg-[#1f1f1f] border border-[#2d2d2d] rounded-xl p-4">
            <p class="text-xs font-semibold text-[#707070] uppercase mb-2">Data de Criação</p>
            <p class="text-sm text-[#AAAAAA]">${new Date(dispute.created_at).toLocaleString('pt-BR')}</p>
        </div>
    `;
    
    document.getElementById('disputeDetailsContent').innerHTML = content;
    document.getElementById('disputeDetailsModal').classList.remove('hidden');
}

function closeDisputeDetailsModal() {
    document.getElementById('disputeDetailsModal').classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDefenseModal();
        closeDisputeDetailsModal();
    }
});

// Close modals on outside click
document.getElementById('defenseModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDefenseModal();
    }
});

document.getElementById('disputeDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDisputeDetailsModal();
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/refunds/index.blade.php ENDPATH**/ ?>