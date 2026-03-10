<?php $__env->startSection('title', 'Configurações - Perfil'); ?>
<?php $__env->startSection('page-title', 'Configurações do Perfil'); ?>
<?php $__env->startSection('page-description', 'Gerencie suas informações pessoais e configurações da conta'); ?>

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
                <h1 class="text-3xl font-bold mb-2">Configurações do Perfil</h1>
                <p class="text-green-100">Gerencie suas informações pessoais e configurações da conta</p>
            </div>
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        Informações Pessoais
                    </h2>
                </div>
                
                <form action="<?php echo e(route('settings.profile.update')); ?>" method="POST" class="p-6 space-y-6">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Nome Completo *
                        </label>
                        <input 
                            id="name" 
                            name="name" 
                            type="text" 
                            required 
                            value="<?php echo e(old('name', $user->name)); ?>"
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                            placeholder="Digite seu nome completo"
                        >
                    </div>

                    <!-- Email (Read-only) -->
                    <div>
                        <label for="email" class="block text-sm font-bold text-gray-900 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            E-mail
                        </label>
                        <div class="relative">
                            <input 
                                id="email" 
                                type="email" 
                                value="<?php echo e($user->email); ?>"
                                class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 focus:outline-none transition-all duration-200"
                                readonly
                                disabled
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Bloqueado
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">O e-mail não pode ser alterado</p>
                    </div>

                    <!-- Document (Read-only) -->
                    <div>
                        <label for="document" class="block text-sm font-bold text-gray-900 mb-2">
                            <?php echo e($user->isPessoaFisica() ? 'CPF' : 'CNPJ'); ?>

                        </label>
                        <div class="relative">
                            <input 
                                id="document" 
                                type="text" 
                                value="<?php echo e($user->formatted_document); ?>"
                                class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 focus:outline-none transition-all duration-200"
                                readonly
                                disabled
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Bloqueado
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">O documento não pode ser alterado</p>
                    </div>

                    <!-- WhatsApp (Read-only) -->
                    <div>
                        <label for="whatsapp" class="block text-sm font-bold text-gray-900 mb-2">
                            WhatsApp
                        </label>
                        <div class="relative">
                            <input 
                                id="whatsapp" 
                                type="text" 
                                value="<?php echo e($user->formatted_whatsapp); ?>"
                                class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 focus:outline-none transition-all duration-200"
                                readonly
                                disabled
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Bloqueado
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">O WhatsApp não pode ser alterado</p>
                    </div>

                    <!-- CEP (Read-only) -->
                    <div>
                        <label for="cep" class="block text-sm font-bold text-gray-900 mb-2">
                            CEP
                        </label>
                        <div class="relative">
                            <input 
                                id="cep" 
                                type="text" 
                                value="<?php echo e($user->formatted_cep); ?>"
                                class="w-full px-4 py-3 bg-gray-100 border-2 border-gray-300 rounded-xl text-gray-600 focus:outline-none transition-all duration-200"
                                readonly
                                disabled
                            >
                            <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                <span class="bg-gray-200 text-gray-600 text-xs font-bold px-3 py-1 rounded-lg flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Bloqueado
                                </span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">O CEP não pode ser alterado</p>
                    </div>

                    <!-- Address Fields (Editable) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="address" class="block text-sm font-bold text-gray-900 mb-2">
                                Endereço
                            </label>
                            <input 
                                id="address" 
                                name="address" 
                                type="text" 
                                value="<?php echo e(old('address', $user->address)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                                placeholder="Rua, número, complemento"
                            >
                        </div>
                        
                        <div>
                            <label for="city" class="block text-sm font-bold text-gray-900 mb-2">
                                Cidade
                            </label>
                            <input 
                                id="city" 
                                name="city" 
                                type="text" 
                                value="<?php echo e(old('city', $user->city)); ?>"
                                class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                                placeholder="Cidade"
                            >
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button 
                            type="submit" 
                            class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-8 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl inline-flex items-center"
                        >
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Account Info -->
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Informações da Conta
                </h3>
                
                <div class="space-y-3">
                    <div class="bg-white rounded-xl border-2 border-emerald-200 p-3">
                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide block mb-1">Tipo de Conta</span>
                        <p class="text-gray-900 font-bold"><?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?></p>
                    </div>
                    
                    <div class="bg-white rounded-xl border-2 border-emerald-200 p-3">
                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide block mb-1"><?php echo e($user->isPessoaFisica() ? 'CPF' : 'CNPJ'); ?></span>
                        <p class="text-gray-900 font-bold"><?php echo e($user->formatted_document); ?></p>
                    </div>
                    
                    <div class="bg-white rounded-xl border-2 border-emerald-200 p-3">
                        <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide block mb-1">Membro desde</span>
                        <p class="text-gray-900 font-bold">
                            <?php if($user->created_at): ?>
                                <?php echo e($user->created_at->format('d/m/Y')); ?>

                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm">
                <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </div>
                        Alterar Senha
                    </h3>
                </div>
                
                <form action="<?php echo e(route('settings.password.update')); ?>" method="POST" class="p-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    
                    <div>
                        <label for="current_password" class="block text-sm font-bold text-gray-900 mb-2">
                            Senha Atual
                        </label>
                        <input 
                            id="current_password" 
                            name="current_password" 
                            type="password" 
                            required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                            placeholder="Digite sua senha atual"
                        >
                        <?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div>
                        <label for="new_password" class="block text-sm font-bold text-gray-900 mb-2">
                            Nova Senha
                        </label>
                        <input 
                            id="new_password" 
                            name="new_password" 
                            type="password" 
                            required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                            placeholder="Digite sua nova senha"
                        >
                        <?php $__errorArgs = ['new_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-bold text-gray-900 mb-2">
                            Confirmar Nova Senha
                        </label>
                        <input 
                            id="new_password_confirmation" 
                            name="new_password_confirmation" 
                            type="password" 
                            required 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
                            placeholder="Confirme sua nova senha"
                        >
                        <?php $__errorArgs = ['new_password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-rose-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-4 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl inline-flex items-center justify-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Alterar Senha
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/settings/profile.blade.php ENDPATH**/ ?>