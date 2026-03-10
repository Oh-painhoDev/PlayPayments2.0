<?php $__env->startSection('title', 'Complete seu cadastro'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="/css/login.css" type="text/css" media="all">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-auth">
    <div class="bg-modal">
        <h2>Complete seu cadastro</h2>
        <p>Preencha seus dados para continuar</p>

<?php if($errors->any()): ?>
            <div style="background: rgba(106, 0, 0, 0.1); border: 1px solid #21b3dd; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #21b3dd;">
                <strong>Erros:</strong>
                <ul style="margin: 8px 0 0 20px; padding: 0;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
    </div>
<?php endif; ?>

        <?php if(session('success')): ?>
            <div style="background: rgba(0, 255, 0, 0.1); border: 1px solid #00ff00; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #00ff00;">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('warning')): ?>
            <div style="background: rgba(255, 193, 7, 0.1); border: 1px solid #ffc107; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #ffc107;">
                <strong>Atenção:</strong> <?php echo e(session('warning')); ?>

            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('onboarding.save')); ?>" method="POST" id="onboardingForm">
            <?php echo csrf_field(); ?>
            <section class="form">
                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label">Tipo de conta *</label>
                    </div>
                    <div class="account-type-selector">
                        <label class="account-type-option">
                            <input type="radio" name="account_type" value="pessoa_juridica" <?php echo e(old('account_type', $user->account_type ?? '') === 'pessoa_juridica' ? 'checked' : ''); ?>>
                            <div class="account-type-card">
                                <div class="account-type-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M19 21H5C4.44772 21 4 20.5523 4 20V4C4 3.44772 4.44772 3 5 3H19C19.5523 3 20 3.44772 20 4V20C20 20.5523 19.5523 21 19 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9 7H15M9 11H15M9 15H13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="account-type-text">
                                    <span class="account-type-title">Pessoa Jurídica</span>
                                    <span class="account-type-desc">Empresa/CNPJ</span>
                                </div>
                                <div class="account-type-check">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                        <label class="account-type-option">
                            <input type="radio" name="account_type" value="pessoa_fisica" <?php echo e(old('account_type', $user->account_type ?? '') === 'pessoa_fisica' ? 'checked' : (old('account_type') !== 'pessoa_juridica' && !$user->account_type ? 'checked' : '')); ?>>
                            <div class="account-type-card">
                                <div class="account-type-icon">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="account-type-text">
                                    <span class="account-type-title">Pessoa Física</span>
                                    <span class="account-type-desc">CPF</span>
                                </div>
                                <div class="account-type-check">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            </div>
                        </label>
                    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-document">
                            <span id="document-label">CPF</span> *
    </label>
                    </div>
                    <div class="input-group">
                        <input id="onb-document" name="document" type="text" class="default-input input" placeholder="000.000.000-00" value="<?php echo e(old('document', $document ?? '')); ?>" required>
                    </div>
                </section>

                <!-- Campos condicionais para Pessoa Jurídica -->
                <div id="pessoa-juridica-fields" style="display: none;">
                    <section class="bg-input">
                        <div class="bg-label">
                            <label class="default-label label" for="onb-business-type">Tipo de Empresa *</label>
                        </div>
                        <div class="input-group">
                            <select id="onb-business-type" name="business_type" class="default-input input">
                                <option value="">Selecione o tipo de empresa</option>
                                <option value="mei" <?php echo e(old('business_type', $user->business_type ?? '') === 'mei' ? 'selected' : ''); ?>>MEI (Microempreendedor Individual)</option>
                                <option value="eireli" <?php echo e(old('business_type', $user->business_type ?? '') === 'eireli' ? 'selected' : ''); ?>>EIRELI (Empresa Individual)</option>
                                <option value="ltda" <?php echo e(old('business_type', $user->business_type ?? '') === 'ltda' ? 'selected' : ''); ?>>LTDA (Limitada)</option>
                                <option value="sa" <?php echo e(old('business_type', $user->business_type ?? '') === 'sa' ? 'selected' : ''); ?>>SA (Sociedade Anônima)</option>
                                <option value="ss" <?php echo e(old('business_type', $user->business_type ?? '') === 'ss' ? 'selected' : ''); ?>>SS (Sociedade Simples)</option>
                                <option value="outro" <?php echo e(old('business_type', $user->business_type ?? '') === 'outro' ? 'selected' : ''); ?>>Outro</option>
                            </select>
                        </div>
                    </section>

                    <section class="bg-input">
                        <div class="bg-label">
                            <label class="default-label label" for="onb-business-sector">Setor de Atividade *</label>
                        </div>
                        <div class="input-group">
                            <select id="onb-business-sector" name="business_sector" class="default-input input">
                                <option value="">Selecione o setor de atividade</option>
                                <option value="servicos" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'servicos' ? 'selected' : ''); ?>>Serviços</option>
                                <option value="comercio" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'comercio' ? 'selected' : ''); ?>>Comércio</option>
                                <option value="industria" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'industria' ? 'selected' : ''); ?>>Indústria</option>
                                <option value="tecnologia" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'tecnologia' ? 'selected' : ''); ?>>Tecnologia</option>
                                <option value="saude" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'saude' ? 'selected' : ''); ?>>Saúde</option>
                                <option value="educacao" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'educacao' ? 'selected' : ''); ?>>Educação</option>
                                <option value="alimentacao" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'alimentacao' ? 'selected' : ''); ?>>Alimentação</option>
                                <option value="construcao" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'construcao' ? 'selected' : ''); ?>>Construção</option>
                                <option value="outro" <?php echo e(old('business_sector', $user->business_sector ?? '') === 'outro' ? 'selected' : ''); ?>>Outro</option>
                            </select>
                        </div>
                    </section>
                </div>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-cep">CEP *</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-cep" name="cep" type="text" class="default-input input" placeholder="00000-000" value="<?php echo e(old('cep', $endereco->cep ?? '')); ?>" required>
                    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-rua">Rua *</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-rua" name="rua" type="text" class="default-input input" placeholder="Nome da rua" value="<?php echo e(old('rua', $endereco->rua ?? '')); ?>" required>
                    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-numero">Número *</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-numero" name="numero" type="text" class="default-input input" placeholder="123" value="<?php echo e(old('numero', $endereco->numero ?? '')); ?>" required>
                    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-bairro">Bairro *</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-bairro" name="bairro" type="text" class="default-input input" placeholder="Nome do bairro" value="<?php echo e(old('bairro', $endereco->bairro ?? '')); ?>" required>
                    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-cidade">Cidade *</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-cidade" name="cidade" type="text" class="default-input input" placeholder="Nome da cidade" value="<?php echo e(old('cidade', $endereco->cidade ?? '')); ?>" required>
    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-estado">Estado *</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-estado" name="estado" type="text" class="default-input input" placeholder="SP" maxlength="2" value="<?php echo e(old('estado', $endereco->estado ?? '')); ?>" required>
                    </div>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="onb-complemento">Complemento</label>
                    </div>
                    <div class="input-group">
                        <input id="onb-complemento" name="complemento" type="text" class="default-input input" placeholder="Apto, bloco, etc" value="<?php echo e(old('complemento', $endereco->complemento ?? '')); ?>">
                    </div>
                </section>

                <section class="actions">
                    <section class="primary bg-large-button">
                        <button type="submit">Finalizar cadastro</button>
                    </section>
                </section>
            </section>
        </form>
    </div>
</section>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="/js/onboarding.js"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/auth/onboarding.blade.php ENDPATH**/ ?>