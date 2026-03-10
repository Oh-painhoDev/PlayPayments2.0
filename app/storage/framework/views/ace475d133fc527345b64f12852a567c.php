<?php $__env->startSection('title', 'Integrações'); ?>

<?php $__env->startSection('content'); ?>
<section class="bg-view">
    <div class="flex-1 bg-[#000000] p-5 font-manrope">
        <div class="max-w-[1600px] mx-auto">
            <div class="bg-[#000000] rounded-2xl p-5 space-y-8">
                <!-- Header -->
                <div class="flex flex-col gap-2.5">
                    <h1 class="font-['Manrope'] font-medium text-[28px] tracking-[-0.56px] text-white">Integrações</h1>
                    <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] text-[#AAAAAA]">Conecte-se com nossas ferramentas parceiras para potencializar seu negócio</p>
                </div>

                <!-- Cards de Integrações -->
                <div class="flex flex-col gap-4 items-start justify-start w-full">
                    <div class="flex flex-col md:flex-row gap-4 items-start justify-start w-full">
                        <!-- Card UTMify -->
                        <div>
                            <a href="<?php echo e(route('integracoes.utmfy.index')); ?>">
                                <div class="text-card-foreground shadow-sm group relative overflow-hidden p-[20px] rounded-[16px] border-0 bg-[#161616] hover:bg-[#1a1a1a] transition-colors cursor-pointer">
                                    <div class="flex flex-col gap-3 items-start justify-center w-full md:w-[227px]">
                                        <div class="h-[133px] overflow-clip relative rounded-[8px] w-full md:w-[227px] bg-white flex items-center justify-center">
                                            <img alt="UTMify" loading="lazy" width="131" height="34" decoding="async" class="object-contain max-w-[80%] max-h-[60%]" src="https://utmify.com.br/logo/logo-white.png" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <div style="display: none; color: #D4AF37; font-weight: bold; font-size: 24px;">UTMify</div>
                                        </div>
                                        <h2 class="font-['Manrope'] font-medium text-[20px] tracking-[-0.6px] text-white">UTMify</h2>
                                        <div class="h-px w-full bg-[#1f1f1f]"></div>
                                        <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] w-full md:w-[157px] text-[#707070]">Gerencie e acompanhe suas UTMs de forma simplificada</p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Card Astrofy -->
                        <div>
                            <a href="<?php echo e(route('integracoes.astrofy.index')); ?>">
                                <div class="text-card-foreground shadow-sm group relative overflow-hidden p-[20px] rounded-[16px] border-0 bg-[#161616] hover:bg-[#1a1a1a] transition-colors cursor-pointer">
                                    <div class="flex flex-col gap-3 items-start justify-center w-full md:w-[227px]">
                                        <div class="h-[133px] overflow-clip relative rounded-[8px] w-full md:w-[227px] bg-gradient-to-br from-purple-900/20 to-blue-900/20 flex items-center justify-center">
                                            <div style="color: #8b5cf6; font-weight: bold; font-size: 32px; text-align: center;">Astrofy</div>
                                        </div>
                                        <h2 class="font-['Manrope'] font-medium text-[20px] tracking-[-0.6px] text-white">Astrofy</h2>
                                        <div class="h-px w-full bg-[#1f1f1f]"></div>
                                        <p class="font-['Manrope'] font-semibold text-[12px] tracking-[-0.24px] w-full md:w-[157px] text-[#707070]">Gateway Hub para integração de pagamentos com múltiplos gateways</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/integracoes.blade.php ENDPATH**/ ?>