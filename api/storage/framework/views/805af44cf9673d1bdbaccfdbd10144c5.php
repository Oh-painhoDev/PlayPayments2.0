<?php $__env->startSection('title', 'Configurações - API'); ?>
<?php $__env->startSection('page-title', 'Chave Secreta API'); ?>
<?php $__env->startSection('page-description', 'Gerencie sua chave secreta para integração com sistemas externos'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">
    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-r-lg flex items-start">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold mb-2">Erros encontrados:</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Chaves de API</h1>
                <p class="text-green-100">Gerencie suas chaves de acesso (Secret e Public) para integração com sistemas externos</p>
            </div>
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- API Keys Section -->
        <div class="lg:col-span-2 space-y-6">
            <?php if($user->api_secret): ?>
                <!-- SECRET KEY Section -->
                <div class="bg-white rounded-2xl border-2 border-red-200 overflow-hidden shadow-sm">
                    <div class="bg-gradient-to-r from-red-500 to-rose-500 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white">Secret Key (SK-playpayments-...)</h2>
                                    <p class="text-red-100 text-sm mt-1">Para criar/modificar pagamentos (POST, PUT, DELETE)</p>
                                </div>
                            </div>
                            <span class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white text-sm font-semibold rounded-xl border border-white/30">
                                Ativa
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <p class="text-red-700 text-sm"><strong>Mantenha esta chave em segredo!</strong> Ela permite criar e modificar pagamentos. Não compartilhe publicamente.</p>
                            </div>
                        </div>
                        
                        <div class="relative">
                            <input 
                                type="password" 
                                id="api-secret"
                                value="<?php echo e($user->api_secret); ?>" 
                                readonly
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 font-mono text-sm pr-24 focus:outline-none focus:ring-2 focus:ring-red-500"
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                <button 
                                    onclick="toggleVisibility('api-secret')"
                                    class="text-gray-600 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50"
                                    title="Mostrar/Ocultar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button 
                                    onclick="copyToClipboard('api-secret')"
                                    class="text-gray-600 hover:text-red-600 transition-colors p-2 rounded-lg hover:bg-red-50"
                                    title="Copiar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- API Secret Info Cards -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-red-700 uppercase tracking-wide block mb-2">Gerada em</span>
                                <p class="text-sm text-gray-900 font-semibold">
                                    <?php if($user->api_secret_created_at && $user->api_secret_created_at instanceof \Carbon\Carbon): ?>
                                        <?php echo e($user->api_secret_created_at->format('d/m/Y H:i')); ?>

                                    <?php elseif($user->api_secret_created_at): ?>
                                        <?php echo e(\Carbon\Carbon::parse($user->api_secret_created_at)->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </p>
                            </div>
                            
                            <div class="bg-gradient-to-br from-red-50 to-rose-50 border-2 border-red-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-red-700 uppercase tracking-wide block mb-2">Último uso</span>
                                <p class="text-sm text-gray-900 font-semibold">
                                    <?php if($user->api_secret_last_used_at && $user->api_secret_last_used_at instanceof \Carbon\Carbon): ?>
                                        <?php echo e($user->api_secret_last_used_at->format('d/m/Y H:i')); ?>

                                    <?php elseif($user->api_secret_last_used_at): ?>
                                        <?php echo e(\Carbon\Carbon::parse($user->api_secret_last_used_at)->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        Nunca utilizada
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PUBLIC KEY Section -->
                <?php if($user->api_public_key): ?>
                <div class="bg-white rounded-2xl border-2 border-blue-200 overflow-hidden shadow-sm">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-500 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white">Public Key (PB-playpayments-...)</h2>
                                    <p class="text-blue-100 text-sm mt-1">Para consultar status de pagamentos (GET apenas)</p>
                                </div>
                            </div>
                            <span class="px-4 py-2 bg-white/20 backdrop-blur-sm text-white text-sm font-semibold rounded-xl border border-white/30">
                                Ativa
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-blue-700 text-sm">Esta chave pode ser usada publicamente no frontend. Ela permite apenas consultar informações (GET), não pode criar ou modificar pagamentos.</p>
                            </div>
                        </div>
                        
                        <div class="relative">
                            <input 
                                type="text" 
                                id="api-public-key"
                                value="<?php echo e($user->api_public_key); ?>" 
                                readonly
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 font-mono text-sm pr-24 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                <button 
                                    onclick="copyToClipboard('api-public-key')"
                                    class="text-gray-600 hover:text-blue-600 transition-colors p-2 rounded-lg hover:bg-blue-50"
                                    title="Copiar"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- API Public Key Info Cards -->
                        <div class="mt-6">
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-4">
                                <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide block mb-2">Gerada em</span>
                                <p class="text-sm text-gray-900 font-semibold">
                                    <?php if($user->api_public_key_created_at && $user->api_public_key_created_at instanceof \Carbon\Carbon): ?>
                                        <?php echo e($user->api_public_key_created_at->format('d/m/Y H:i')); ?>

                                    <?php elseif($user->api_public_key_created_at): ?>
                                        <?php echo e(\Carbon\Carbon::parse($user->api_public_key_created_at)->format('d/m/Y H:i')); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Regenerate API Secret -->
                <div class="bg-white rounded-2xl border-2 border-gray-200 p-6 shadow-sm">
                    <div class="flex items-start">
                        <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <h2 class="text-lg font-bold text-gray-900 mb-2">Regenerar Chave Secreta</h2>
                            <p class="text-gray-600 text-sm mb-4 flex items-start gap-2">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Ao regenerar a chave, a antiga será invalidada e você precisará atualizar suas integrações.</span>
                            </p>
                            
                            <form action="<?php echo e(route('settings.api.regenerate')); ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja regenerar a chave secreta? A chave atual será invalidada!')">
                                <?php echo csrf_field(); ?>
                                <button 
                                    type="submit" 
                                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-md hover:shadow-lg inline-flex items-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Regenerar Chave Secreta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Generate First API Secret -->
                <div class="bg-white rounded-2xl border-2 border-dashed border-gray-300 p-16 text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Gerar Chaves de API</h2>
                    <p class="text-gray-500 mb-6">
                        Você ainda não possui chaves de API. Clique no botão abaixo para gerar suas chaves (Secret e Public).
                    </p>
                    
                    <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-6 mb-6 max-w-md mx-auto">
                        <div class="flex items-start text-left">
                            <svg class="w-6 h-6 text-emerald-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="text-emerald-700 font-bold text-sm mb-1">Importante</h3>
                                <p class="text-emerald-700 text-sm mb-2">
                                    Serão geradas duas chaves:
                                </p>
                                <ul class="text-emerald-700 text-sm list-disc list-inside space-y-1">
                                    <li><strong>Secret Key (SK-playpayments-...):</strong> Para criar/modificar pagamentos</li>
                                    <li><strong>Public Key (PB-playpayments-...):</strong> Para consultar status (apenas leitura)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <form action="<?php echo e(route('settings.api.generate')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl inline-flex items-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Gerar Chaves de API
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- API Documentation -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </span>
                    Documentação
                </h3>
                
                <div class="space-y-3">
                    <a href="<?php echo e(route('settings.api.docs')); ?>" class="flex items-center p-3 bg-white rounded-xl border-2 border-emerald-200 hover:border-emerald-300 transition-all group">
                        <svg class="w-5 h-5 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-gray-700 group-hover:text-emerald-700 font-medium">Guia de Início Rápido</span>
                    </a>
                    
                    <a href="<?php echo e(route('settings.api.docs')); ?>#endpoints" class="flex items-center p-3 bg-white rounded-xl border-2 border-emerald-200 hover:border-emerald-300 transition-all group">
                        <svg class="w-5 h-5 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        <span class="text-gray-700 group-hover:text-emerald-700 font-medium">Referência da API</span>
                    </a>
                    
                    <a href="<?php echo e(route('settings.api.docs')); ?>#exemplos" class="flex items-center p-3 bg-white rounded-xl border-2 border-emerald-200 hover:border-emerald-300 transition-all group">
                        <svg class="w-5 h-5 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-700 group-hover:text-emerald-700 font-medium">Exemplos de Código</span>
                    </a>
                    
                    <a href="mailto:suporte@pixbolt.com" class="flex items-center p-3 bg-white rounded-xl border-2 border-emerald-200 hover:border-emerald-300 transition-all group">
                        <svg class="w-5 h-5 text-emerald-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-gray-700 group-hover:text-emerald-700 font-medium">Suporte Técnico</span>
                    </a>
                </div>
            </div>

            <!-- API Status -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Status da API
                </h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-700 text-sm font-medium">Endpoint Principal</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></div>
                            <span class="text-emerald-600 text-sm font-semibold">Online</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-700 text-sm font-medium">Webhooks</span>
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></div>
                            <span class="text-emerald-600 text-sm font-semibold">Funcionando</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-700 text-sm font-medium">Rate Limit</span>
                        <span class="text-gray-900 text-sm font-bold">1000/min</span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-700 text-sm font-medium">Secret Key</span>
                        <span class="text-gray-900 text-sm font-bold">
                            <?php echo e($user->api_secret ? 'Ativa' : 'Não gerada'); ?>

                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-white rounded-xl">
                        <span class="text-gray-700 text-sm font-medium">Public Key</span>
                        <span class="text-gray-900 text-sm font-bold">
                            <?php echo e($user->api_public_key ? 'Ativa' : 'Não gerada'); ?>

                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast de notificação (copiar) -->
<div id="copyToast" class="fixed bottom-4 right-4 bg-gradient-to-r from-emerald-500 to-teal-500 text-white px-6 py-3 rounded-xl shadow-2xl transform translate-y-20 opacity-0 transition-all duration-300 z-50">
    <div class="flex items-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span class="font-semibold">Chave copiada!</span>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');
    
    // Show toast
    showToast();
}

function showToast() {
    const toast = document.getElementById('copyToast');
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    
    setTimeout(() => {
        toast.classList.remove('translate-y-0', 'opacity-100');
        toast.classList.add('translate-y-20', 'opacity-0');
    }, 3000);
}

function toggleVisibility(elementId) {
    const element = document.getElementById(elementId);
    const type = element.type === 'password' ? 'text' : 'password';
    element.type = type;
    
    // Update icon
    const button = event.target.closest('button');
    const icon = button.querySelector('svg');
    
    if (type === 'text') {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />';
    } else {
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}

// Auto-hide alerts
setTimeout(function() {
    const alerts = document.querySelectorAll('.bg-emerald-50, .bg-rose-50');
    alerts.forEach(alert => {
        if (alert.classList.contains('border-l-4')) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }
    });
}, 5000);
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/settings/api.blade.php ENDPATH**/ ?>