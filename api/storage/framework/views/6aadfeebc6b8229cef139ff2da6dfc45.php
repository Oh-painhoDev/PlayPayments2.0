<?php $__env->startSection('title', 'Configurações - Documentos'); ?>
<?php $__env->startSection('page-title', 'Verificação de Documentos'); ?>
<?php $__env->startSection('page-description', 'Envie seus documentos para verificação da conta'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 via-emerald-500 to-teal-500 rounded-2xl p-8 text-white shadow-xl">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Verificação de Documentos</h1>
                <p class="text-green-100">Envie seus documentos para verificação e validação da conta</p>
            </div>
            <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    <?php if($verification && $verification->isApproved()): ?>
        <div class="bg-gradient-to-r from-emerald-500/10 to-green-500/10 border-l-4 border-emerald-500 text-emerald-700 px-6 py-4 rounded-r-xl shadow-lg backdrop-blur-sm">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg">Documentos Aprovados!</h3>
                    <p class="text-emerald-600">Seus documentos foram verificados e aprovados<?php echo e($verification->reviewed_at ? ' em ' . $verification->formatted_reviewed_at : ''); ?>.</p>
                </div>
            </div>
        </div>
    <?php elseif($verification && $verification->isRejected()): ?>
        <div class="bg-gradient-to-r from-red-500/10 to-orange-500/10 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-r-xl shadow-lg backdrop-blur-sm">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg">Documentos Rejeitados</h3>
                    <p class="text-red-600"><?php echo e($verification->rejection_reason ?? 'Documentos rejeitados. Envie novamente.'); ?></p>
                    <?php if($verification->reviewed_at): ?>
                        <p class="text-sm mt-1">Rejeitado em <?php echo e($verification->formatted_reviewed_at); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php elseif($verification && $verification->submitted_at && $verification->hasAllDocuments()): ?>
        <div class="bg-gradient-to-r from-emerald-500/10 to-green-500/10 border-l-4 border-emerald-500 text-emerald-700 px-6 py-4 rounded-r-xl shadow-lg backdrop-blur-sm">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg">Documentos em Análise</h3>
                    <p class="text-emerald-600">Seus documentos foram enviados e estão sendo analisados. Aguarde a conclusão da verificação.</p>
                    <?php if($verification->submitted_at): ?>
                        <p class="text-sm mt-1">Enviado em <?php echo e($verification->formatted_submitted_at); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-gradient-to-r from-amber-500/10 to-orange-500/10 border-l-4 border-amber-500 text-amber-700 px-6 py-4 rounded-r-xl shadow-lg backdrop-blur-sm">
            <div class="flex items-center">
                <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <div>
                    <h3 class="font-bold text-lg">Documentos Pendentes</h3>
                    <p class="text-amber-600">Você precisa enviar os seguintes documentos:</p>
                    <?php if($verification): ?>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            <?php $__currentLoopData = $verification->getMissingDocuments(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($doc); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <ul class="list-disc list-inside mt-2 text-sm">
                            <li>Frente do documento</li>
                            <li>Verso do documento</li>
                            <li>Selfie com documento</li>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?php echo e(session('success')); ?>

            </div>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?php echo e(session('error')); ?>

            </div>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border-l-4 border-rose-500 text-rose-800 px-6 py-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="font-bold mb-1">Erros encontrados:</h4>
                    <ul class="list-disc list-inside text-sm">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <!-- Document Upload Form -->
            <?php if($user->canModifyDocuments()): ?>
                <form action="<?php echo e(route('documents.upload')); ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <?php echo csrf_field(); ?>

                    <!-- Verification Info -->
                    <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                            <h3 class="text-xl font-bold text-white flex items-center">
                                <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                Informações da Verificação
                            </h3>
                        </div>
                        
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide block mb-2">Tipo de Conta</span>
                                    <p class="text-gray-900 font-bold"><?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?></p>
                                </div>
                                
                                <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wide block mb-2">Status</span>
                                    <span class="inline-flex px-3 py-1 rounded-lg text-xs font-bold
                                        <?php if($verification && $verification->isApproved()): ?> bg-emerald-500/20 text-emerald-700 border-2 border-emerald-500/30
                                        <?php elseif($verification && $verification->isRejected()): ?> bg-red-500/20 text-red-700 border-2 border-red-500/30
                                        <?php else: ?> bg-amber-500/20 text-amber-700 border-2 border-amber-500/30 <?php endif; ?>">
                                        <?php echo e($verification ? ucfirst($verification->status) : 'Pendente'); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Required Documents -->
                    <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-sm">
                        <div class="bg-gradient-to-r from-green-500 to-emerald-500 p-6">
                            <h3 class="text-xl font-bold text-white flex items-center">
                                <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                Documentos Obrigatórios
                            </h3>
                        </div>
                        
                        <div class="p-6 space-y-5">
                            <!-- Front Document -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <label class="block text-sm font-bold text-gray-900 mb-3">
                                    Frente do Documento (<?php echo e($user->isPessoaFisica() ? 'CPF/RG' : 'CNPJ'); ?>) *
                                </label>
                                <div class="flex items-center space-x-4">
                                    <?php if($verification && $verification->front_document): ?>
                                        <div class="flex items-center bg-white border-2 border-emerald-300 text-emerald-700 px-4 py-2 rounded-xl font-bold">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    <?php endif; ?>
                                    <input 
                                        type="file" 
                                        name="front_document" 
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-sm text-gray-900 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-green-600 file:to-emerald-600 file:text-white hover:file:from-green-700 hover:file:to-emerald-700 file:cursor-pointer file:transition-all file:shadow-md"
                                    >
                                </div>
                                <p class="text-xs text-emerald-700 mt-2 font-medium">JPG, PNG ou PDF - Máximo 2MB</p>
                            </div>

                            <!-- Back Document -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <label class="block text-sm font-bold text-gray-900 mb-3">
                                    Verso do Documento *
                                </label>
                                <div class="flex items-center space-x-4">
                                    <?php if($verification && $verification->back_document): ?>
                                        <div class="flex items-center bg-white border-2 border-emerald-300 text-emerald-700 px-4 py-2 rounded-xl font-bold">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    <?php endif; ?>
                                    <input 
                                        type="file" 
                                        name="back_document" 
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="block w-full text-sm text-gray-900 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-green-600 file:to-emerald-600 file:text-white hover:file:from-green-700 hover:file:to-emerald-700 file:cursor-pointer file:transition-all file:shadow-md"
                                    >
                                </div>
                                <p class="text-xs text-emerald-700 mt-2 font-medium">JPG, PNG ou PDF - Máximo 2MB</p>
                            </div>

                            <!-- Selfie with Document -->
                            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-xl p-4">
                                <label class="block text-sm font-bold text-gray-900 mb-3">
                                    Selfie com o Documento *
                                </label>
                                <div class="flex items-center space-x-4">
                                    <?php if($verification && $verification->selfie_document): ?>
                                        <div class="flex items-center bg-white border-2 border-emerald-300 text-emerald-700 px-4 py-2 rounded-xl font-bold">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Enviado
                                        </div>
                                    <?php endif; ?>
                                    <input 
                                        type="file" 
                                        name="selfie_document" 
                                        accept=".jpg,.jpeg,.png"
                                        class="block w-full text-sm text-gray-900 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-gradient-to-r file:from-green-600 file:to-emerald-600 file:text-white hover:file:from-green-700 hover:file:to-emerald-700 file:cursor-pointer file:transition-all file:shadow-md"
                                    >
                                </div>
                                <p class="text-xs text-emerald-700 mt-2 font-medium">📎 Apenas JPG ou PNG - Máximo 2MB</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6 text-center">
                                <button 
                                    type="submit" 
                                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-10 py-3.5 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl inline-flex items-center"
                                >
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    Enviar Documentos
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php elseif($verification && $verification->isApproved()): ?>
                <div class="bg-white rounded-2xl border-2 border-gray-200 p-12 text-center shadow-sm">
                    <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Documentos Bloqueados</h3>
                    <p class="text-gray-600">
                        Seus documentos foram aprovados e não podem mais ser alterados.
                    </p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl border-2 border-gray-200 p-12 text-center shadow-sm">
                    <div class="w-24 h-24 bg-gradient-to-br from-emerald-100 to-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-12 h-12 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Documentos em Análise</h3>
                    <p class="text-gray-600">
                        Seus documentos foram enviados e estão sendo analisados. Aguarde a conclusão da verificação.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar: Instructions -->
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-emerald-50 to-green-50 border-2 border-emerald-200 rounded-2xl p-6 shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Instruções Importantes
                </h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-start bg-white rounded-xl p-3 border-2 border-emerald-200">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-900 font-medium">Documentos legíveis e válidos</span>
                    </li>
                    <li class="flex items-start bg-white rounded-xl p-3 border-2 border-emerald-200">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-900 font-medium">JPG, PNG, PDF (máx 2MB)</span>
                    </li>
                    <li class="flex items-start bg-white rounded-xl p-3 border-2 border-emerald-200">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-900 font-medium">Análise em até 2 dias úteis</span>
                    </li>
                    <li class="flex items-start bg-white rounded-xl p-3 border-2 border-emerald-200">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-900 font-medium">Notificação por email</span>
                    </li>
                    <li class="flex items-start bg-white rounded-xl p-3 border-2 border-emerald-200">
                        <svg class="w-5 h-5 mr-3 mt-0.5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-gray-900 font-medium">Bloqueado após aprovação</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
$(document).ready(function() {
    $('input[type="file"]').change(function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            if (file.size > maxSize) {
                alert(`ERRO: Arquivo muito grande (${fileSizeMB}MB)!\n\n` +
                      `Máximo permitido: 2MB\n\n` +
                      `DICA: Comprima seu arquivo antes de enviar:\n` +
                      `• Use TinyPNG.com ou Compressor.io para imagens\n` +
                      `• Reduza a qualidade/resolução da foto\n` +
                      `• Para PDFs, use PDF Compressor online`);
                this.value = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('ERRO: Tipo de arquivo não permitido!\n\n' +
                      'Use apenas JPG, PNG ou PDF.');
                this.value = '';
                return;
            }
            
            // Mostrar tamanho do arquivo se tudo estiver ok
            console.log(`Arquivo válido: ${file.name} (${fileSizeMB}MB)`);
        }
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\resources\views/documents/index.blade.php ENDPATH**/ ?>