

<?php $__env->startSection('content'); ?>
<div class="flex-1 overflow-y-auto bg-background">
    <div class="flex h-screen bg-[#161616]">
        <main class="flex-1 overflow-y-auto" style="background-color: rgb(0, 0, 0);">
            <div class="container mx-auto p-4 sm:p-6 lg:p-8 max-w-full">
                <div class="space-y-4 sm:space-y-6 h-full pb-16">
                    <div class="flex justify-between items-center">
                        <h1 class="text-[18px] sm:text-[20px] lg:text-[28px] font-medium tracking-[-0.54px] sm:tracking-[-0.6px] lg:tracking-[-0.84px] text-white">Configurações</h1>
                    </div>
                    
                    <div class="rounded-lg text-card-foreground shadow-sm mt-4 sm:mt-6 border-0 w-full h-full bg-[#1f1f1f]">
                        <div class="overflow-x-auto">
                            <div class="px-3 sm:px-4 lg:px-6 min-w-max">
                                <div class="flex gap-1 p-1 rounded-lg w-[223px] bg-[#161616]">
                                    <button onclick="switchTab('profile')" id="tab-profile" class="flex items-center gap-2 px-4 py-2 rounded-md text-[12px] font-semibold tracking-[-0.24px] transition-colors whitespace-nowrap bg-[#1F1F1F] text-[#21b3dd]">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-4 w-4">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span>Meus Dados</span>
                                    </button>
                                    <button onclick="switchTab('fees')" id="tab-fees" class="flex items-center gap-2 px-4 py-2 rounded-md text-[12px] font-semibold tracking-[-0.24px] transition-colors whitespace-nowrap text-[#707070] hover:text-white">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calculator h-4 w-4">
                                            <rect width="16" height="20" x="4" y="2" rx="2"></rect>
                                            <line x1="8" x2="16" y1="6" y2="6"></line>
                                            <line x1="16" x2="16" y1="14" y2="18"></line>
                                            <path d="M16 10h.01"></path>
                                            <path d="M12 10h.01"></path>
                                            <path d="M8 10h.01"></path>
                                            <path d="M12 14h.01"></path>
                                            <path d="M8 14h.01"></path>
                                            <path d="M12 18h.01"></path>
                                            <path d="M8 18h.01"></path>
                                        </svg>
                                        <span>Taxas</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-3 sm:p-4 lg:p-6 w-full h-full overflow-y-auto pb-12">
                            <!-- Tab: Meus Dados -->
                            <div id="content-profile" class="w-full h-full">
                                <div class="space-y-6">
                                    <!-- Informações Pessoais -->
                                    <div class="rounded-2xl p-6 bg-[#161616]">
                                        <div class="mb-6">
                                            <h2 class="text-[20px] font-medium tracking-[-0.6px] mb-2 text-white">Informações Pessoais</h2>
                                            <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]">Atualize suas informações pessoais.</p>
                                        </div>
                                        <form action="<?php echo e(route('settings.profile.update')); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('PUT'); ?>
                                            
                                            <div class="flex items-center gap-3 pb-4">
                                                <div class="relative">
                                                    <div class="w-20 h-20 rounded-full overflow-hidden bg-[#1F1F1F] flex items-center justify-center">
                                                        <?php
                                                            $hasPhoto = $user->photo && \Storage::disk('public')->exists($user->photo);
                                                        ?>
                                                        <?php if($hasPhoto): ?>
                                                            <img src="<?php echo e(asset('storage/' . $user->photo)); ?>" alt="Foto de perfil" class="w-full h-full object-cover">
                                                        <?php else: ?>
                                                            <div class="w-full h-full flex items-center justify-center bg-[#21b3dd] text-white text-lg font-semibold">
                                                                <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <label for="photo-upload" class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center border-2 cursor-pointer hover:scale-105 transition-transform bg-[#161616] border-[#2A2A2A] hover:bg-[#2A2A2A]" title="Alterar foto">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                            <path d="M8.5 1.5L10.5 3.5L3.5 10.5H1.5V8.5L8.5 1.5Z" stroke="#AAAAAA" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </svg>
                                                        <input type="file" id="photo-upload" name="photo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="hidden" onchange="handlePhotoUpload(event)">
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="grid gap-4 grid-cols-1">
                                                <div class="space-y-2">
                                                    <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]" for="name">Nome Completo</label>
                                                    <div class="relative">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#AAAAAA]">
                                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                        <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="name" name="name" value="<?php echo e(old('name', $user->name)); ?>" style="padding-left: 2.2rem;" readonly disabled>
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]" for="fantasy_name">Nome Fantasia</label>
                                                    <div class="relative">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#AAAAAA]">
                                                            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                                            <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                                            <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                                            <path d="M10 6h4"></path>
                                                            <path d="M10 10h4"></path>
                                                            <path d="M10 14h4"></path>
                                                            <path d="M10 18h4"></path>
                                                        </svg>
                                                        <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="fantasy_name" name="fantasy_name" value="<?php echo e(old('fantasy_name', $user->fantasy_name)); ?>" style="padding-left: 2.2rem;" placeholder="Digite o nome fantasia">
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]" for="email">Email</label>
                                                    <div class="relative">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#AAAAAA]">
                                                            <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                                                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                                                        </svg>
                                                        <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="email" name="email" type="email" value="<?php echo e(old('email', $user->email)); ?>" style="padding-left: 2.2rem;" required>
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]" for="phone">Telefone</label>
                                                    <div class="relative">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#AAAAAA]">
                                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                                        </svg>
                                                        <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="phone" value="<?php echo e($user->formatted_whatsapp ?? $user->whatsapp ?? ''); ?>" style="padding-left: 2.2rem;" readonly disabled>
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-2">
                                                    <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#AAAAAA]" for="document">CPF</label>
                                                    <div class="relative">
                                                        <div class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-[#AAAAAA]">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.5 3H10.5C13.3282 3 14.7427 3 15.621 3.879C16.4992 4.758 16.5 6.17175 16.5 9C16.5 11.8282 16.5 13.2427 15.621 14.121C14.742 14.9992 13.3282 15 10.5 15H7.5C4.67175 15 3.25725 15 2.379 14.121C1.50075 13.242 1.5 11.8282 1.5 9C1.5 6.17175 1.5 4.75725 2.379 3.879C3.258 3.00075 4.67175 3 7.5 3ZM9.9375 6.75C9.9375 6.60082 9.99676 6.45774 10.1023 6.35225C10.2077 6.24676 10.3508 6.1875 10.5 6.1875H14.25C14.3992 6.1875 14.5423 6.24676 14.6477 6.35225C14.7532 6.45774 14.8125 6.60082 14.8125 6.75C14.8125 6.89918 14.7532 7.04226 14.6477 7.14775C14.5423 7.25324 14.3992 7.3125 14.25 7.3125H10.5C10.3508 7.3125 10.2077 7.25324 10.1023 7.14775C9.99676 7.04226 9.9375 6.89918 9.9375 6.75ZM10.6875 9C10.6875 8.85082 10.7468 8.70774 10.8523 8.60225C10.9577 8.49676 11.1008 8.4375 11.25 8.4375H14.25C14.3992 8.4375 14.5423 8.49676 14.6477 8.60225C14.7532 8.70774 14.8125 8.85082 14.8125 9C14.8125 9.14918 14.7532 9.29226 14.6477 9.39775C14.5423 9.50324 14.3992 9.5625 14.25 9.5625H11.25C11.1008 9.5625 10.9577 9.50324 10.8523 9.39775C10.7468 9.29226 10.6875 9.14918 10.6875 9ZM11.4375 11.25C11.4375 11.1008 11.4968 10.9577 11.6023 10.8523C11.7077 10.7468 11.8508 10.6875 12 10.6875H14.25C14.3992 10.6875 14.5423 10.7468 14.6477 10.8523C14.7532 10.9577 14.8125 11.1008 14.8125 11.25C14.8125 11.3992 14.7532 11.5423 14.6477 11.6477C14.5423 11.7532 14.3992 11.8125 14.25 11.8125H12C11.8508 11.8125 11.7077 11.7532 11.6023 11.6477C11.4968 11.5423 11.4375 11.3992 11.4375 11.25ZM8.25 6.75C8.25 7.14782 8.09196 7.52936 7.81066 7.81066C7.52936 8.09196 7.14782 8.25 6.75 8.25C6.35218 8.25 5.97064 8.09196 5.68934 7.81066C5.40804 7.52936 5.25 7.14782 5.25 6.75C5.25 6.35218 5.40804 5.97064 5.68934 5.68934C5.97064 5.40804 6.35218 5.25 6.75 5.25C7.14782 5.25 7.52936 5.40804 7.81066 5.68934C8.09196 5.97064 8.25 6.35218 8.25 6.75ZM6.75 12.75C9.75 12.75 9.75 12.0787 9.75 11.25C9.75 10.4212 8.4075 9.75 6.75 9.75C5.0925 9.75 3.75 10.4212 3.75 11.25C3.75 12.0787 3.75 12.75 6.75 12.75Z" fill="#AAAAAA"></path>
                                                            </svg>
                                                        </div>
                                                        <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="document" value="<?php echo e($user->formatted_document); ?>" style="padding-left: 2.2rem;" readonly disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="flex justify-end">
                                                <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 transition-all duration-200 h-10 px-4 py-2.5 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#21b3dd] text-white hover:bg-[#21b3dd]/90 button-custom" type="submit" style="border-radius: 8px;">Salvar alterações</button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <!-- Informações da Empresa -->
                                    <div class="rounded-2xl p-4 sm:p-5 bg-[#161616]">
                                        <div class="flex flex-col gap-6">
                                            <div class="flex flex-col lg:flex-row gap-6">
                                                <div class="flex flex-col justify-between">
                                                    <div class="flex flex-col gap-2">
                                                        <h2 class="text-[20px] font-medium tracking-[-0.6px] text-white">Informações da Empresa</h2>
                                                        <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Atualize as informações da sua empresa.</p>
                                                    </div>
                                                    <div class="flex items-center gap-3 mt-4 lg:mt-0">
                                                        <div class="w-[70px] h-[70px] rounded-full flex items-center justify-center bg-[#1F1F1F]">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 lucide-building-2 h-10 w-10" style="color: rgb(235, 51, 0);">
                                                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                                                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                                                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                                                <path d="M10 6h4"></path>
                                                                <path d="M10 10h4"></path>
                                                                <path d="M10 14h4"></path>
                                                                <path d="M10 18h4"></path>
                                                            </svg>
                                                        </div>
                                                        <div class="flex flex-col gap-2">
                                                            <h3 class="text-[14px] font-semibold tracking-[-0.28px] text-white"><?php echo e($user->name); ?></h3>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <form action="<?php echo e(route('settings.profile.update')); ?>" method="POST" enctype="multipart/form-data" class="flex-1">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('PUT'); ?>
                                                    <div class="grid gap-3 sm:gap-4 grid-cols-1 lg:grid-cols-2">
                                                        <div class="flex flex-col gap-3 sm:gap-4">
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Razão Social</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e($user->name); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">CNPJ</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e($user->formatted_document); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">CEP</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e($user->formatted_cep ?? 'N/A'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Número</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e(explode(',', $user->address ?? '')[1] ?? 'N/A'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Bairro</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e(explode('-', $user->address ?? '')[1] ?? 'N/A'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Faturamento Médio</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white">R$ 0,00</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex flex-col gap-3 sm:gap-4">
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]" for="company_fantasy_name">Nome Fantasia</label>
                                                                <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="company_fantasy_name" name="fantasy_name" value="<?php echo e(old('fantasy_name', $user->fantasy_name)); ?>" placeholder="Digite o nome fantasia">
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]" for="company_website">Website</label>
                                                                <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="company_website" name="website" type="url" value="<?php echo e(old('website', $user->website)); ?>" placeholder="https://exemplo.com.br">
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]" for="company_address">Endereço</label>
                                                                <input class="flex w-full border py-2 ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 h-[42px] px-3 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#1F1F1F] text-white border-[#2A2A2A] placeholder-[#AAAAAA]" id="company_address" name="address" value="<?php echo e(old('address', $user->address)); ?>" placeholder="Digite o endereço">
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Complemento</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Cidade</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e($user->city ?? 'N/A'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="flex flex-col gap-2">
                                                                <label class="peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Ticket Médio</label>
                                                                <div class="px-4 py-3 rounded-lg bg-[#1F1F1F]">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white">R$ 0,00</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end mt-4">
                                                        <button class="inline-flex items-center justify-center gap-2 whitespace-nowrap ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 transition-all duration-200 h-10 w-full sm:w-auto px-4 py-2.5 rounded-lg text-[12px] font-semibold tracking-[-0.24px] bg-[#21b3dd] text-white hover:bg-[#21b3dd]/90 button-custom" type="submit" style="border-radius: 8px;">Salvar alterações</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab: Taxas -->
                            <div id="content-fees" class="w-full h-full hidden">
                                <div class="min-h-full overflow-y-auto pb-16 rounded-[16px] bg-[#161616]">
                                    <div class="p-5">
                                        <div class="mb-4">
                                            <h1 class="text-[20px] font-medium tracking-[-0.6px] mb-2 text-white">Configurações de Taxas</h1>
                                            <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Gerencie suas taxas e faça simulações de transações em tempo real.</p>
                                        </div>
                                        <div class="h-px w-full mb-4 bg-[#1f1f1f]"></div>
                                        
                                        <div class="space-y-6">
                                            <div class="grid gap-4 grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                                                <!-- Tarifa Pix -->
                                                <div>
                                                    <div class="text-card-foreground shadow-sm transition-all duration-300 rounded-[16px] h-full bg-[#1f1f1f] border-[#1f1f1f]">
                                                        <div class="p-5">
                                                            <div class="space-y-4">
                                                                <div class="flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code h-[18px] w-[18px]" style="color: rgb(235, 51, 0);">
                                                                        <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                                                        <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                                                        <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                                                        <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                                                        <path d="M21 21v.01"></path>
                                                                        <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                                                        <path d="M3 12h.01"></path>
                                                                        <path d="M12 3h.01"></path>
                                                                        <path d="M12 16v.01"></path>
                                                                        <path d="M16 12h1"></path>
                                                                        <path d="M21 12v.01"></path>
                                                                        <path d="M12 21v-1"></path>
                                                                    </svg>
                                                                    <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Método de pagamento instantâneo</p>
                                                                </div>
                                                                <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                                                                <h3 class="text-[20px] font-medium tracking-[-0.6px] text-white">Tarifa Pix</h3>
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Valor</span>
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e(number_format($formattedFees['pix']['percentage'], 2, ',', '.')); ?>% + R$ <?php echo e(number_format($formattedFees['pix']['fixed'], 2, ',', '.')); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tarifa Cartão -->
                                                <div>
                                                    <div class="text-card-foreground shadow-sm transition-all duration-300 rounded-[16px] h-full bg-[#1f1f1f] border-[#1f1f1f]">
                                                        <div class="p-5">
                                                            <div class="space-y-4">
                                                                <div class="flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-[18px] w-[18px]" style="color: rgb(235, 51, 0);">
                                                                        <rect width="20" height="14" x="2" y="5" rx="2"></rect>
                                                                        <line x1="2" x2="22" y1="10" y2="10"></line>
                                                                    </svg>
                                                                    <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Método de pagamento instantâneo</p>
                                                                </div>
                                                                <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                                                                <h3 class="text-[20px] font-medium tracking-[-0.6px] text-white">Tarifa Cartão</h3>
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Valor</span>
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e(number_format($formattedFees['credit_card']['percentage'], 2, ',', '.')); ?>% + R$ <?php echo e(number_format($formattedFees['credit_card']['fixed'], 2, ',', '.')); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 p-3 rounded-lg bg-[#2a2a00] border border-[#ffc107]/20">
                                                        <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#ffc107] text-center">Em manutenção</p>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tarifa Boleto -->
                                                <div>
                                                    <div class="text-card-foreground shadow-sm transition-all duration-300 rounded-[16px] h-full bg-[#1f1f1f] border-[#1f1f1f]">
                                                        <div class="p-5">
                                                            <div class="space-y-4">
                                                                <div class="flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none" class="h-[18px] w-[18px]">
                                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M3.00001 3V15H1.5V3H3.00001ZM6 3V15H4.49999V3H6ZM8.99999 3V15H7.50001V3H8.99999ZM16.5 3V15H15V3H16.5ZM11.25 3V15H10.5V3H11.25ZM13.5 3V15H12.75V3H13.5Z" fill="#21b3dd"></path>
                                                                    </svg>
                                                                    <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Método de pagamento instantâneo</p>
                                                                </div>
                                                                <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                                                                <h3 class="text-[20px] font-medium tracking-[-0.6px] text-white">Tarifa Boleto</h3>
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Valor</span>
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e(number_format($formattedFees['bank_slip']['percentage'], 2, ',', '.')); ?>% + R$ <?php echo e(number_format($formattedFees['bank_slip']['fixed'], 2, ',', '.')); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 p-3 rounded-lg bg-[#2a2a00] border border-[#ffc107]/20">
                                                        <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#ffc107] text-center">Em manutenção</p>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tarifa Saque -->
                                                <div>
                                                    <div class="text-card-foreground shadow-sm transition-all duration-300 rounded-[16px] h-full bg-[#1f1f1f] border-[#1f1f1f]">
                                                        <div class="p-5">
                                                            <div class="space-y-4">
                                                                <div class="flex items-center gap-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wallet h-[18px] w-[18px]" style="color: rgb(235, 51, 0);">
                                                                        <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"></path>
                                                                        <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"></path>
                                                                    </svg>
                                                                    <p class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Método de pagamento instantâneo</p>
                                                                </div>
                                                                <div class="h-px w-full opacity-20 bg-[#707070]"></div>
                                                                <h3 class="text-[20px] font-medium tracking-[-0.6px] text-white">Tarifa Saque</h3>
                                                                <div class="flex justify-between items-center">
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-[#707070]">Valor</span>
                                                                    <span class="text-[12px] font-semibold tracking-[-0.24px] text-white"><?php echo e(number_format($formattedFees['withdrawal']['percentage'], 2, ',', '.')); ?>% + R$ <?php echo e(number_format($formattedFees['withdrawal']['fixed'], 2, ',', '.')); ?></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function switchTab(tab) {
    // Hide all content
    document.getElementById('content-profile').classList.add('hidden');
    document.getElementById('content-fees').classList.add('hidden');
    
    // Remove active state from all tabs
    document.getElementById('tab-profile').classList.remove('bg-[#1F1F1F]', 'text-[#21b3dd]');
    document.getElementById('tab-profile').classList.add('text-[#707070]');
    document.getElementById('tab-fees').classList.remove('bg-[#1F1F1F]', 'text-[#21b3dd]');
    document.getElementById('tab-fees').classList.add('text-[#707070]');
    
    // Show selected content and activate tab
    if (tab === 'profile') {
        document.getElementById('content-profile').classList.remove('hidden');
        document.getElementById('tab-profile').classList.add('bg-[#1F1F1F]', 'text-[#21b3dd]');
        document.getElementById('tab-profile').classList.remove('text-[#707070]');
    } else if (tab === 'fees') {
        document.getElementById('content-fees').classList.remove('hidden');
        document.getElementById('tab-fees').classList.add('bg-[#1F1F1F]', 'text-[#21b3dd]');
        document.getElementById('tab-fees').classList.remove('text-[#707070]');
    }
}

// Set default tab on load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'profile';
    switchTab(tab);
});

// Handle photo upload
function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file size (5MB max)
    if (file.size > 5 * 1024 * 1024) {
        alert('A imagem deve ter no máximo 5MB.');
        event.target.value = '';
        return;
    }
    
    // Validate file type
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('Formato de arquivo inválido. Use JPG, PNG, GIF ou WebP.');
        event.target.value = '';
        return;
    }
    
    // Show preview
    const reader = new FileReader();
    reader.onload = function(e) {
        const photoContainer = document.querySelector('.w-20.h-20');
        if (photoContainer) {
            const existingImg = photoContainer.querySelector('img');
            const existingDiv = photoContainer.querySelector('div');
            
            if (existingImg) {
                existingImg.src = e.target.result;
            } else if (existingDiv) {
                // Replace initials div with image
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.alt = 'Foto de perfil';
                newImg.className = 'w-full h-full object-cover';
                photoContainer.replaceChild(newImg, existingDiv);
            } else {
                // Create new image element
                const newImg = document.createElement('img');
                newImg.src = e.target.result;
                newImg.alt = 'Foto de perfil';
                newImg.className = 'w-full h-full object-cover';
                photoContainer.appendChild(newImg);
            }
        }
    };
    reader.readAsDataURL(file);
    
    // Submit form automatically via the profile form
    const profileForm = document.querySelector('form[action="<?php echo e(route("settings.profile.update")); ?>"]');
    if (profileForm) {
        const formData = new FormData(profileForm);
        formData.append('photo', file);
        
        fetch('<?php echo e(route("settings.profile.update")); ?>', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || !data.errors) {
                // Update sidebar photo too
                const sidebarPhoto = document.querySelector('.sidebar-user-photo');
                if (sidebarPhoto && data.photo_url) {
                    sidebarPhoto.src = data.photo_url;
                }
                // Reload after a short delay to show success
                setTimeout(() => {
                    location.reload();
                }, 500);
            } else {
                alert('Erro ao atualizar foto: ' + (data.message || JSON.stringify(data.errors || 'Erro desconhecido')));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erro ao atualizar foto. Tente novamente.');
        });
    }
}
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.dashboard', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/settings/index.blade.php ENDPATH**/ ?>