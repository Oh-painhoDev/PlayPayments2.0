<div class="space-y-6">
    <!-- Company Information -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Dados da empresa</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-gray-600 text-sm">Tipo da empresa:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->isPessoaFisica() ? 'Pessoa Física' : 'Pessoa Jurídica'); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Documento:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->formatted_document); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Razão Social:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->name); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Email:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->email); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Telefone:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->formatted_whatsapp); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Criado em:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->created_at ? $user->created_at->format('d/m/Y \à\s H:i') : 'N/A'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Documents Section -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Documentos</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php if($verification->front_document): ?>
            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <h4 class="text-gray-900 font-medium mb-3">Frente do Documento</h4>
                <div class="bg-gray-100 rounded-lg overflow-hidden" style="min-height: 400px; max-height: 600px;">
                    <?php
                        $frontDoc = $verification->front_document;
                        $isPdf = str_contains(strtolower($frontDoc), '.pdf') || str_contains($frontDoc, 'pdf');
                    ?>
                    <?php if($isPdf): ?>
                        <div class="flex items-center justify-center h-full" style="min-height: 400px;">
                            <a href="<?php echo e($frontDoc); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 flex flex-col items-center">
                                <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="font-medium">Visualizar PDF</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo e($frontDoc); ?>" alt="Frente do Documento" class="object-contain w-full h-full cursor-pointer" style="min-height: 400px;" onclick="window.open('<?php echo e($frontDoc); ?>', '_blank')" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-gray-500\' style=\'min-height: 400px;\'><div class=\'text-center\'><svg class=\'w-16 h-16 mx-auto mb-2 text-gray-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg><p class=\'text-gray-600 mb-2\'>Erro ao carregar imagem</p><a href=\'<?php echo e($frontDoc); ?>\' target=\'_blank\' class=\'text-blue-600 hover:underline text-sm\'>Abrir em nova aba</a></div></div>';">
                    <?php endif; ?>
                </div>
                <div class="mt-3 flex justify-end">
                    <a href="<?php echo e($frontDoc); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Ver em tamanho real
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($verification->back_document): ?>
            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <h4 class="text-gray-900 font-medium mb-3">Verso do Documento</h4>
                <div class="bg-gray-100 rounded-lg overflow-hidden" style="min-height: 400px; max-height: 600px;">
                    <?php
                        $backDoc = $verification->back_document;
                        $isPdf = str_contains(strtolower($backDoc), '.pdf') || str_contains($backDoc, 'pdf');
                    ?>
                    <?php if($isPdf): ?>
                        <div class="flex items-center justify-center h-full" style="min-height: 400px;">
                            <a href="<?php echo e($backDoc); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 flex flex-col items-center">
                                <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="font-medium">Visualizar PDF</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo e($backDoc); ?>" alt="Verso do Documento" class="object-contain w-full h-full cursor-pointer" style="min-height: 400px;" onclick="window.open('<?php echo e($backDoc); ?>', '_blank')" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-gray-500\' style=\'min-height: 400px;\'><div class=\'text-center\'><svg class=\'w-16 h-16 mx-auto mb-2 text-gray-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg><p class=\'text-gray-600 mb-2\'>Erro ao carregar imagem</p><a href=\'<?php echo e($backDoc); ?>\' target=\'_blank\' class=\'text-blue-600 hover:underline text-sm\'>Abrir em nova aba</a></div></div>';">
                    <?php endif; ?>
                </div>
                <div class="mt-3 flex justify-end">
                    <a href="<?php echo e($backDoc); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Ver em tamanho real
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($verification->proof_address): ?>
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                <h4 class="text-gray-900 font-medium mb-2">Documento (Verso)</h4>
                <div class="aspect-w-16 aspect-h-9 bg-gray-700 rounded-lg overflow-hidden">
                    <?php if(pathinfo($verification->proof_address, PATHINFO_EXTENSION) == 'pdf'): ?>
                        <div class="flex items-center justify-center h-full">
                            <a href="<?php echo e($verification->proof_address); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 flex items-center">
                                <svg class="w-10 h-10 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Visualizar PDF</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo e($verification->proof_address); ?>" alt="Documento Verso" class="object-cover w-full h-full">
                    <?php endif; ?>
                </div>
                <div class="mt-2 flex justify-end">
                    <a href="<?php echo e($verification->proof_address); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">
                        Ver em tamanho real
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($verification->selfie_document): ?>
            <div class="bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                <h4 class="text-gray-900 font-medium mb-3">Selfie com Documento</h4>
                <div class="bg-gray-100 rounded-lg overflow-hidden" style="min-height: 400px; max-height: 600px;">
                    <?php
                        $selfieDoc = $verification->selfie_document;
                    ?>
                    <img src="<?php echo e($selfieDoc); ?>" alt="Selfie com Documento" class="object-contain w-full h-full cursor-pointer" style="min-height: 400px;" onclick="window.open('<?php echo e($selfieDoc); ?>', '_blank')" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex items-center justify-center h-full text-gray-500\' style=\'min-height: 400px;\'><div class=\'text-center\'><svg class=\'w-16 h-16 mx-auto mb-2 text-gray-400\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z\' /></svg><p class=\'text-gray-600 mb-2\'>Erro ao carregar imagem</p><a href=\'<?php echo e($selfieDoc); ?>\' target=\'_blank\' class=\'text-blue-600 hover:underline text-sm\'>Abrir em nova aba</a></div></div>';">
                </div>
                <div class="mt-3 flex justify-end">
                    <a href="<?php echo e($selfieDoc); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm font-medium flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Ver em tamanho real
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if($verification->income_proof): ?>
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                <h4 class="text-gray-900 font-medium mb-2">Comprovante de Renda</h4>
                <div class="aspect-w-16 aspect-h-9 bg-gray-700 rounded-lg overflow-hidden">
                    <?php if(pathinfo($verification->income_proof, PATHINFO_EXTENSION) == 'pdf'): ?>
                        <div class="flex items-center justify-center h-full">
                            <a href="<?php echo e($verification->income_proof); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 flex items-center">
                                <svg class="w-10 h-10 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Visualizar PDF</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo e($verification->income_proof); ?>" alt="Comprovante de Renda" class="object-cover w-full h-full">
                    <?php endif; ?>
                </div>
                <div class="mt-2 flex justify-end">
                    <a href="<?php echo e($verification->income_proof); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">
                        Ver em tamanho real
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <?php if($verification->financial_statement): ?>
            <div class="bg-gray-800 rounded-lg p-4 border border-gray-300">
                <h4 class="text-gray-900 font-medium mb-2">Balanço Financeiro</h4>
                <div class="aspect-w-16 aspect-h-9 bg-gray-700 rounded-lg overflow-hidden">
                    <?php if(pathinfo($verification->financial_statement, PATHINFO_EXTENSION) == 'pdf'): ?>
                        <div class="flex items-center justify-center h-full">
                            <a href="<?php echo e($verification->financial_statement); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 flex items-center">
                                <svg class="w-10 h-10 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Visualizar PDF</span>
                            </a>
                        </div>
                    <?php else: ?>
                        <img src="<?php echo e($verification->financial_statement); ?>" alt="Balanço Financeiro" class="object-cover w-full h-full">
                    <?php endif; ?>
                </div>
                <div class="mt-2 flex justify-end">
                    <a href="<?php echo e($verification->financial_statement); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">
                        Ver em tamanho real
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Other Information -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Outras informações</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-gray-600 text-sm">Faturamento médio:</span>
                <p class="text-gray-900 font-medium">R$ <?php echo e(number_format($avgRevenue, 2, ',', '.')); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Ticket médio:</span>
                <p class="text-gray-900 font-medium">R$ <?php echo e(number_format($avgTicket ?? 0, 2, ',', '.')); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Site:</span>
                <p class="text-gray-900 font-medium">
                    <?php if($user->website): ?>
                        <a href="<?php echo e($user->website); ?>" target="_blank" class="text-blue-600 hover:text-blue-700">
                            <?php echo e($user->website); ?>

                        </a>
                    <?php else: ?>
                        Não informado
                    <?php endif; ?>
                </p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Produtos que vende:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->business_sector ?? 'Não informado'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Representative Information -->
    <div class="bg-gray-50 rounded-lg p-6 border border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Representante Legal</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <span class="text-gray-600 text-sm">Nome:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->name); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Documento:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->formatted_document); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Data de nascimento:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->birth_date ? $user->birth_date->format('d/m/Y') : 'Não informado'); ?></p>
            </div>
            
            <div>
                <span class="text-gray-600 text-sm">Telefone:</span>
                <p class="text-gray-900 font-medium"><?php echo e($user->formatted_whatsapp); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="flex justify-end space-x-4">
        <button 
            onclick="closeCompanyModal()"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors"
        >
            Fechar
        </button>
        <button 
            onclick="openApprovalModalDirect(<?php echo e($verification->id); ?>, <?php echo e($user->id); ?>)"
            class="bg-green-600 hover:bg-green-700 text-gray-900 px-6 py-2 rounded-lg text-sm transition-colors"
        >
            Aprovar/Rejeitar
        </button>
    </div>
</div><?php /**PATH C:\xampp\htdocs1 gateway\resources\views/admin/documents/details.blade.php ENDPATH**/ ?>