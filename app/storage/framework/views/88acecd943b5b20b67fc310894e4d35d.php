<?php if(env('ENABLE_PWA_PROMPT', true)): ?>
<div id="pwa-install-prompt" class="fixed bottom-0 left-0 right-0 pwa-hidden-real" style="z-index: 2147483647 !important;">
    <!-- PWA Install Popup Background -->
    <div class="px-4 pb-4 pt-2">
        <div class="bg-[#1a1a1a] border border-[#D4AF37]/30 rounded-2xl shadow-2xl p-4 flex flex-col gap-3 relative overflow-hidden">
            
            <!-- Glow effect -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#D4AF37]/10 blur-[40px] rounded-full pointer-events-none"></div>

            <!-- Close Button -->
            <button id="pwa-close-btn" class="absolute top-3 right-3 text-gray-400 hover:text-white transition-colors bg-white/5 p-1 rounded-full z-10" aria-label="Fechar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <div class="flex items-center gap-4 z-10">
                <!-- App Icon -->
                <div class="w-14 h-14 bg-black rounded-xl border border-white/10 flex items-center justify-center p-1 flex-shrink-0 shadow-inner overflow-hidden">
                    <img src="<?php echo e(asset('images/pwa-icon.png')); ?>" alt="<?php echo e(config('app.name')); ?>" class="w-full h-full object-contain">
                </div>
                
                <!-- Content -->
                <div class="flex-1 pr-6">
                    <h4 class="text-white font-bold text-sm leading-tight mb-1">Instalar o App <?php echo e(config('app.name')); ?></h4>
                    <p class="text-gray-400 text-[11px] leading-snug">
                        Tenha acesso rápido e sem usar memória do seu celular! Aproveite a melhor experiência.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2 z-10 mt-1">
                <button id="pwa-install-btn" class="flex-1 bg-gradient-to-r from-[#D4AF37] to-[#e5c96b] text-black font-bold text-xs py-2.5 px-4 rounded-xl shadow-[0_0_15px_rgba(212,175,55,0.3)] hover:shadow-[0_0_25px_rgba(212,175,55,0.5)] transition-all uppercase tracking-wider">
                    Instalar Agora
                </button>
            </div>

            <!-- iOS Instructions (Hidden by default) -->
            <div id="pwa-ios-instructions" class="hidden flex-col items-center justify-center text-center mt-2 border-t border-white/5 pt-3 animate-fade-in z-10">
                <p class="text-[11px] text-gray-300 font-medium mb-2">Para instalar no seu iPhone ou iPad:</p>
                <div class="flex items-center gap-2 text-[10px] text-gray-400">
                    1. Toque em 
                    <span class="bg-white/10 p-1 rounded-md text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    </span>
                    na barra inferior
                </div>
                <div class="flex items-center gap-2 text-[10px] text-gray-400 mt-1.5">
                    2. Selecione <span class="text-white font-bold bg-white/5 px-2 py-0.5 rounded border border-white/10">Adicionar à Tela de Início</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#pwa-install-prompt {
    display: block !important;
    transform: translateY(110%);
    transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}
#pwa-install-prompt.pwa-visible {
    transform: translateY(0);
}
#pwa-install-prompt.pwa-hidden-real {
    display: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isDismissed = localStorage.getItem('playpayments_pwa_dismissed') === 'true';

    if (isStandalone || isDismissed) return;

    const popup = document.getElementById('pwa-install-prompt');
    const closeBtn = document.getElementById('pwa-close-btn');
    const installBtn = document.getElementById('pwa-install-btn');
    const iosInstructions = document.getElementById('pwa-ios-instructions');

    let deferredPrompt;
    const userAgent = navigator.userAgent.toLowerCase();
    const isIOS = /iphone|ipad|ipod/.test(userAgent);

    const showPopup = () => {
        popup.classList.remove('pwa-hidden-real');
        // Force reflow so transition runs
        void popup.offsetWidth;
        popup.classList.add('pwa-visible');

        if (isIOS) {
            installBtn.innerText = 'Ver como instalar';
            installBtn.addEventListener('click', () => {
                iosInstructions.classList.remove('hidden');
                iosInstructions.classList.add('flex');
                installBtn.classList.add('hidden');
            });
        }
    };

    const hidePopup = () => {
        popup.classList.remove('pwa-visible');
        setTimeout(() => popup.classList.add('pwa-hidden-real'), 550);
        localStorage.setItem('playpayments_pwa_dismissed', 'true');
    };

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        if (!isIOS && !isDismissed && !isStandalone) {
            setTimeout(showPopup, 2000);
        }
    });

    if (isIOS && !isDismissed && !isStandalone) {
        setTimeout(showPopup, 3000);
    }

    installBtn.addEventListener('click', async () => {
        if (!isIOS && deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                hidePopup();
            }
            deferredPrompt = null;
        }
    });

    // Close button — this is the main fix
    closeBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        hidePopup();
    });
});
</script>
<?php endif; ?>
<?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/components/pwa-prompt.blade.php ENDPATH**/ ?>