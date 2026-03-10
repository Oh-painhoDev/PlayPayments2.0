<?php $__env->startSection('title', 'Cadastro'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/login.css')); ?>" type="text/css" media="all">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php
    $authBannerSettings = $authBannerConfig ?? [];
    $authBannerEnabled = !empty($authBannerSettings['enabled']);
    $authBannerImage = $authBannerSettings['image'] ?? null;
    $authBannerSide = $authBannerSettings['side'] ?? 'left';
?>

<section class="bg-auth <?php echo e($authBannerEnabled ? 'bg-auth--with-banner bg-auth--banner-' . $authBannerSide : ''); ?>" data-banner-side="<?php echo e($authBannerSide ?? 'left'); ?>">
    <?php if($authBannerEnabled && $authBannerImage): ?>
        <div class="auth-banner" style="background-image: url('<?php echo e($authBannerImage); ?>'); background-color: #ffffff;"></div>
    <?php else: ?>
        <figure>
            <img src="<?php echo e($whiteLabelLogo ?? '/images/playpayments-logo-top.webp'); ?>" alt="$playpayments - Logo">
        </figure>
    <?php endif; ?>
    
    <section class="bg-modal <?php echo e($authBannerEnabled ? 'bg-modal--full-height' : ''); ?>">
        <div class="auth-form-wrapper">
            <?php if($authBannerEnabled): ?>
                <figure class="auth-form-logo">
                    <img src="<?php echo e($whiteLabelLogo ?? '/images/playpayments-logo-top.webp'); ?>" alt="$playpayments - Logo">
                </figure>
            <?php endif; ?>
        <h2>Criar Conta</h2>
        <p>Preencha os dados abaixo para se cadastrar</p>
        
        <?php if($errors->any()): ?>
            <div style="background: rgba(106, 0, 0, 0.1); border: 1px solid #D4AF37; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #D4AF37;">
                <strong>Erros:</strong>
                <ul style="margin: 8px 0 0 20px; padding: 0;">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(request()->is('cadastro*') ? route('cadastro.post') : route('register.post')); ?>" method="POST" id="registerForm">
            <?php echo csrf_field(); ?>
            <?php if(isset($referral_code) && $referral_code): ?>
                <input type="hidden" name="referral_code" value="<?php echo e($referral_code); ?>">
            <?php endif; ?>
            <?php if(request()->has('ref')): ?>
                <input type="hidden" name="ref" value="<?php echo e(request()->get('ref')); ?>">
            <?php endif; ?>

            <section class="form">
                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="reg-name">Nome completo *</label>
                    </div>
                    <div class="input-group">
                        <input id="reg-name" name="name" type="text" class="default-input input" placeholder="Seu nome completo" value="<?php echo e(old('name')); ?>" required>
                    </div>
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #D4AF37; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="reg-email">E-mail *</label>
                    </div>
                    <div class="input-group">
                        <input id="reg-email" name="email" type="email" class="default-input input" placeholder="seu@email.com" value="<?php echo e(old('email')); ?>" required>
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #D4AF37; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="reg-whatsapp">WhatsApp *</label>
                    </div>
                    <div class="input-group">
                        <input id="reg-whatsapp" name="whatsapp" type="text" class="default-input input" placeholder="(00) 00000-0000" value="<?php echo e(old('whatsapp')); ?>" required>
                    </div>
                    <?php $__errorArgs = ['whatsapp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #D4AF37; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="reg-password">Senha *</label>
                    </div>
                    <div class="input-group">
                        <input id="reg-password" name="password" type="password" class="default-input input" placeholder="Mínimo 6 caracteres" maxlength="256" autocomplete="new-password" required>
                        <div class="show-text" onclick="togglePassword('reg-password')">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path id="eye-path-reg-password" d="M2 12C2 13.64 2.425 14.191 3.275 15.296C4.972 17.5 7.818 20 12 20C16.182 20 19.028 17.5 20.725 15.296C21.575 14.192 22 13.639 22 12C22 10.36 21.575 9.809 20.725 8.704C19.028 6.5 16.182 4 12 4C7.818 4 4.972 6.5 3.275 8.704C2.425 9.81 2 10.361 2 12Z" fill="#86898B" opacity="0.5"></path>
                                <path id="eye-inner-reg-password" fill-rule="evenodd" clip-rule="evenodd" d="M8.25 12C8.25 11.0054 8.64509 10.0516 9.34835 9.34835C10.0516 8.64509 11.0054 8.25 12 8.25C12.9946 8.25 13.9484 8.64509 14.6517 9.34835C15.3549 10.0516 15.75 11.0054 15.75 12C15.75 12.9946 15.3549 13.9484 14.6517 14.6517C13.9484 15.3549 12.9946 15.75 12 15.75C11.0054 15.75 10.0516 15.3549 9.34835 14.6517C8.64509 13.9484 8.25 12.9946 8.25 12ZM9.75 12C9.75 11.4033 9.98705 10.831 10.409 10.409C10.831 9.98705 11.4033 9.75 12 9.75C12.5967 9.75 13.169 9.98705 13.591 10.409C14.0129 10.831 14.25 11.4033 14.25 12C14.25 12.5967 14.0129 13.169 13.591 13.591C13.169 14.0129 12.5967 14.25 12 14.25C11.4033 14.25 10.831 14.0129 10.409 13.591C9.98705 13.169 9.75 12.5967 9.75 12Z" fill="#86898B" style="display: block;"></path>
                            </svg>
                        </div>
                    </div>
                    <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p style="color: #D4AF37; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>

                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="reg-password-confirm">Confirmar Senha *</label>
                    </div>
                    <div class="input-group">
                        <input id="reg-password-confirm" name="password_confirmation" type="password" class="default-input input" placeholder="Digite a senha novamente" maxlength="256" autocomplete="new-password" required>
                        <div class="show-text" onclick="togglePassword('reg-password-confirm')">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path id="eye-path-reg-password-confirm" d="M2 12C2 13.64 2.425 14.191 3.275 15.296C4.972 17.5 7.818 20 12 20C16.182 20 19.028 17.5 20.725 15.296C21.575 14.192 22 13.639 22 12C22 10.36 21.575 9.809 20.725 8.704C19.028 6.5 16.182 4 12 4C7.818 4 4.972 6.5 3.275 8.704C2.425 9.81 2 10.361 2 12Z" fill="#86898B" opacity="0.5"></path>
                                <path id="eye-inner-reg-password-confirm" fill-rule="evenodd" clip-rule="evenodd" d="M8.25 12C8.25 11.0054 8.64509 10.0516 9.34835 9.34835C10.0516 8.64509 11.0054 8.25 12 8.25C12.9946 8.25 13.9484 8.64509 14.6517 9.34835C15.3549 10.0516 15.75 11.0054 15.75 12C15.75 12.9946 15.3549 13.9484 14.6517 14.6517C13.9484 15.3549 12.9946 15.75 12 15.75C11.0054 15.75 10.0516 15.3549 9.34835 14.6517C8.64509 13.9484 8.25 12.9946 8.25 12ZM9.75 12C9.75 11.4033 9.98705 10.831 10.409 10.409C10.831 9.98705 11.4033 9.75 12 9.75C12.5967 9.75 13.169 9.98705 13.591 10.409C14.0129 10.831 14.25 11.4033 14.25 12C14.25 12.5967 14.0129 13.169 13.591 13.591C13.169 14.0129 12.5967 14.25 12 14.25C11.4033 14.25 10.831 14.0129 10.409 13.591C9.98705 13.169 9.75 12.5967 9.75 12Z" fill="#86898B" style="display: block;"></path>
                            </svg>
                        </div>
                    </div>
                </section>

                <div class="checkbox-group" style="margin-top: 10px; align-items: center;">
                    <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required="">
                    <label for="terms_accepted" class="default-label" style="display: inline-flex; align-items: center; margin: 0; color: #ffffff;">
                        <span style="line-height: 1.5; display: inline-block; vertical-align: middle;">Li e aceito os <a href="#" style="color: #D4AF37; text-decoration: none; display: inline; vertical-align: baseline; line-height: 1.5;">Termos de Uso</a> e <a href="#" style="color: #D4AF37; text-decoration: none; display: inline; vertical-align: baseline; line-height: 1.5;">Política de Privacidade</a> *</span>
                    </label>
                </div>
                <?php $__errorArgs = ['terms_accepted'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <p style="color: #D4AF37; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                <section class="actions">
                    <section class="primary bg-large-button">
                        <button type="submit">Criar Conta</button>
                    </section>
                </section>
            </section>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <p style="color: #86898B; font-size: 14px;">
                Já possui conta? <a href="<?php echo e(route('login')); ?>" style="color: #D4AF37; text-decoration: none;">Entrar</a>
            </p>
        </div>
        </div>
    </section>
</section>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="<?php echo e(asset('js/auth.js')); ?>"></script>
<script>
if (document.getElementById('reg-whatsapp')) {
    document.getElementById('reg-whatsapp').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length <= 11) {
            if (value.length <= 2) {
                value = value;
            } else if (value.length <= 7) {
                value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
            } else {
                value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
            }
            e.target.value = value;
        }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/auth/register.blade.php ENDPATH**/ ?>