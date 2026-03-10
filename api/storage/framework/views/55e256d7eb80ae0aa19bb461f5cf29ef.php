<?php $__env->startSection('title', 'Chaves API'); ?>
<?php $__env->startSection('page-title', 'Chaves API'); ?>
<?php $__env->startSection('page-description', 'Gerencie suas chaves de API com segurança'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    /* Força background preto na página de API Keys */
    body:has(.api-keys-wrapper) .scrollable-content {
        background-color: rgb(0, 0, 0) !important;
    }
    .api-keys-wrapper {
        background-color: rgb(0, 0, 0) !important;
        min-height: calc(100vh - 80px);
        margin: 0;
        padding: 32px 0;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="api-keys-wrapper">
    <?php if(session('success')): ?>
        <div class="fixed top-20 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    
    <div class="container mx-auto px-8">
        <div class="max-w-[1200px] mx-auto space-y-6">
            <div class="space-y-4">
                <div class="space-y-2.5">
                    <h1 class="text-[28px] font-medium tracking-[-0.56px] text-white">Chaves API</h1>
                    <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]">Gerencie suas chaves de API com segurança</p>
                </div>
                <button 
                    onclick="generateNewKey()" 
                    class="inline-flex items-center justify-center whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0 border hover:text-accent-foreground h-10 px-4 py-2 rounded-[8px] gap-2 bg-[#161616] border-[#2d2d2d] text-[#707070] hover:bg-[#1f1f1f]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw h-4 w-4 text-[#21b3dd]">
                        <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                        <path d="M3 3v5h5"></path>
                        <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
                        <path d="M16 16h5v5"></path>
                    </svg>
                    <span class="text-[12px] font-semibold">Gerar nova chave</span>
                </button>
            </div>

            <!-- Tabs -->
            <div class="space-y-4" x-data="{ activeTab: 'keys' }">
                <div role="tablist" class="inline-flex h-10 items-center justify-center rounded-md text-muted-foreground p-1 bg-[#161616]">
                    <button 
                        type="button" 
                        role="tab" 
                        @click="activeTab = 'keys'"
                        :class="activeTab === 'keys' ? 'bg-[#1f1f1f] text-[#21b3dd]' : 'text-white'"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 shadow-sm gap-1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key h-5 w-5">
                            <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"></path>
                            <path d="m21 2-9.6 9.6"></path>
                            <circle cx="7.5" cy="15.5" r="5.5"></circle>
                        </svg>
                        <span class="text-[12px] font-semibold">Suas chaves</span>
                    </button>
                    <button 
                        type="button" 
                        role="tab" 
                        @click="activeTab = 'example'"
                        :class="activeTab === 'example' ? 'bg-[#1f1f1f] text-[#21b3dd]' : 'text-white'"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 shadow-sm gap-1"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield h-5 w-5">
                            <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path>
                        </svg>
                        <span class="text-[12px] font-semibold">Exemplos de uso</span>
                    </button>
                </div>

                <!-- Tab Content: Keys -->
                <div x-show="activeTab === 'keys'" class="space-y-4">
                    <div class="flex flex-col md:flex-row gap-4 items-start justify-start w-full">
                        <!-- Secret Key Card -->
                        <div class="rounded-lg text-card-foreground shadow-sm border-2 transition-all duration-200 flex-1 w-full bg-[#161616] border-[#2d2d2d] hover:border-[#21b3dd]/20">
                            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-[#1f1f1f]">
                                        <div class="text-[#21b3dd]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key h-5 w-5">
                                                <path d="m15.5 7.5 2.3 2.3a1 1 0 0 0 1.4 0l2.1-2.1a1 1 0 0 0 0-1.4L19 4"></path>
                                                <path d="m21 2-9.6 9.6"></path>
                                                <circle cx="7.5" cy="15.5" r="5.5"></circle>
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="tracking-tight text-sm font-semibold text-white">Chave secreta</h3>
                                </div>
                                <button 
                                    onclick="toggleSecretKey()" 
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:text-accent-foreground rounded-[8px] h-8 w-8 p-0 hover:bg-transparent"
                                >
                                    <svg id="secretKeyEyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4 text-[#21b3dd]">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <div class="p-6 pt-0">
                                <div class="flex items-center gap-4 w-full">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col gap-1.5">
                                            <input 
                                                id="secretKeyInput" 
                                                class="flex rounded-md border px-3 py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-9 font-mono text-sm w-full bg-[#1f1f1f] text-white border-[#2d2d2d]" 
                                                readonly 
                                                type="password" 
                                                value="<?php echo e($user->api_secret ?? 'Nenhuma chave gerada'); ?>"
                                            >
                                        </div>
                                    </div>
                                    <button 
                                        onclick="copySecretKey(event)" 
                                        class="inline-flex items-center justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 rounded-[8px] px-3 gap-2 whitespace-nowrap shrink-0 bg-[#1f1f1f] text-white hover:bg-[#2d2d2d]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy h-3.5 w-3.5 text-[#21b3dd]">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                        </svg>
                                        Copiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Public Key Card -->
                        <div class="rounded-lg text-card-foreground shadow-sm border-2 transition-all duration-200 flex-1 w-full bg-[#161616] border-[#2d2d2d] hover:border-[#21b3dd]/20">
                            <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center justify-center w-10 h-10 rounded-lg bg-[#1f1f1f]">
                                        <div class="text-[#21b3dd]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                                <path d="M3.33331 6.66668V5.00001C3.33331 4.55798 3.50891 4.13406 3.82147 3.8215C4.13403 3.50894 4.55795 3.33334 4.99998 3.33334H6.66665M3.33331 13.3333V15C3.33331 15.442 3.50891 15.866 3.82147 16.1785C4.13403 16.4911 4.55795 16.6667 4.99998 16.6667H6.66665M13.3333 3.33334H15C15.442 3.33334 15.8659 3.50894 16.1785 3.8215C16.4911 4.13406 16.6666 4.55798 16.6666 5.00001V6.66668M13.3333 16.6667H15C15.442 16.6667 15.8659 16.4911 16.1785 16.1785C16.4911 15.866 16.6666 15.442 16.6666 15V13.3333M5.83331 10H14.1666" stroke="#21b3dd" stroke-width="1.66667" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <h3 class="tracking-tight text-sm font-semibold text-white">Public Key</h3>
                                </div>
                                <button 
                                    onclick="togglePublicKey()" 
                                    class="inline-flex items-center justify-center gap-2 whitespace-nowrap text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 hover:text-accent-foreground rounded-[8px] h-8 w-8 p-0 hover:bg-transparent"
                                >
                                    <svg id="publicKeyEyeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye h-4 w-4 text-[#21b3dd]">
                                        <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            <div class="p-6 pt-0">
                                <div class="flex items-center gap-4 w-full">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-col gap-1.5">
                                            <input 
                                                id="publicKeyInput" 
                                                class="flex rounded-md border px-3 py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-9 font-mono text-sm w-full bg-[#1f1f1f] text-white border-[#2d2d2d]" 
                                                readonly 
                                                type="password" 
                                                value="<?php echo e($user->api_public_key ?? 'Nenhuma chave gerada'); ?>"
                                            >
                                        </div>
                                    </div>
                                    <button 
                                        onclick="copyPublicKey(event)" 
                                        class="inline-flex items-center justify-center text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 rounded-[8px] px-3 gap-2 whitespace-nowrap shrink-0 bg-[#1f1f1f] text-white hover:bg-[#2d2d2d]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy h-3.5 w-3.5 text-[#21b3dd]">
                                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
                                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
                                        </svg>
                                        Copiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Content: Examples -->
                <div x-show="activeTab === 'example'" x-cloak class="space-y-4">
                    <div class="bg-[#161616] rounded-lg p-6 border border-[#2d2d2d]">
                        <h3 class="text-white font-semibold mb-4">Exemplos de uso da API</h3>
                        <p class="text-[#AAAAAA] text-sm mb-4">Aqui estão alguns exemplos de como usar suas chaves API:</p>
                        
                        <div class="space-y-4">
                            <div class="bg-[#1f1f1f] rounded-lg p-4 border border-[#2d2d2d]">
                                <h4 class="text-white font-medium mb-2 text-sm">Criar Pagamento PIX</h4>
                                <pre class="text-xs text-[#AAAAAA] font-mono overflow-x-auto"><code>curl -X POST https://seu-dominio.com/api/pix \
  -H "Authorization: Bearer <?php echo e($user->api_secret ?? 'SUA_CHAVE_SECRETA'); ?>" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100.00,
    "customer": {
      "name": "João Silva",
      "email": "joao@email.com",
      "document": "12345678900"
    }
  }'</code></pre>
                            </div>
                            
                            <div class="bg-[#1f1f1f] rounded-lg p-4 border border-[#2d2d2d]">
                                <h4 class="text-white font-medium mb-2 text-sm">Consultar Pagamento</h4>
                                <pre class="text-xs text-[#AAAAAA] font-mono overflow-x-auto"><code>curl -X GET https://seu-dominio.com/api/pix/{transaction_id} \
  -H "Authorization: Bearer <?php echo e($user->api_secret ?? 'SUA_CHAVE_SECRETA'); ?>"</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let secretKeyVisible = false;
let publicKeyVisible = false;

function toggleSecretKey() {
    const input = document.getElementById('secretKeyInput');
    const icon = document.getElementById('secretKeyEyeIcon');
    
    secretKeyVisible = !secretKeyVisible;
    
    if (secretKeyVisible) {
        input.type = 'text';
        icon.innerHTML = `
            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
    }
}

function togglePublicKey() {
    const input = document.getElementById('publicKeyInput');
    const icon = document.getElementById('publicKeyEyeIcon');
    
    publicKeyVisible = !publicKeyVisible;
    
    if (publicKeyVisible) {
        input.type = 'text';
        icon.innerHTML = `
            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
    }
}

function copySecretKey(e) {
    const input = document.getElementById('secretKeyInput');
    const wasPassword = input.type === 'password';
    
    if (wasPassword) {
        input.type = 'text';
    }
    
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        navigator.clipboard.writeText(input.value);
    } catch (err) {
        document.execCommand('copy');
    }
    
    if (wasPassword && !secretKeyVisible) {
        input.type = 'password';
    }
    
    // Feedback visual
    const button = e.target.closest('button');
    if (button) {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<span class="text-[12px] text-green-500">Copiado!</span>';
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    }
}

function copyPublicKey(e) {
    const input = document.getElementById('publicKeyInput');
    const wasPassword = input.type === 'password';
    
    if (wasPassword) {
        input.type = 'text';
    }
    
    input.select();
    input.setSelectionRange(0, 99999);
    
    try {
        navigator.clipboard.writeText(input.value);
    } catch (err) {
        document.execCommand('copy');
    }
    
    if (wasPassword && !publicKeyVisible) {
        input.type = 'password';
    }
    
    // Feedback visual
    const button = e.target.closest('button');
    if (button) {
        const originalHTML = button.innerHTML;
        button.innerHTML = '<span class="text-[12px] text-green-500">Copiado!</span>';
        setTimeout(() => {
            button.innerHTML = originalHTML;
        }, 2000);
    }
}

function generateNewKey() {
    if (confirm('Tem certeza que deseja gerar uma nova chave? A chave atual será invalidada.')) {
        // Criar formulário e submeter
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo e(route("settings.api.generate")); ?>';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/api-keys.blade.php ENDPATH**/ ?>