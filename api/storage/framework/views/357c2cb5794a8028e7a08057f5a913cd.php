<?php $__env->startSection('title', 'Cadastro'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="/css/login.css" type="text/css" media="all">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-auth">
    <section class="bg-modal">
        <h2>Bem-vindo(a) a Brpix</h2>
        <p>Crie sua conta para começar</p>
        
        <section class="auth-tabs" style="display: none;">
            <!-- Tabs ocultas já que esta é a página de registro -->
        </section>

        <!-- Register Content -->
        <div id="register-content">
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

            <form action="<?php echo e(route('register.post')); ?>" method="POST" id="registerForm">
                <?php echo csrf_field(); ?>
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
                            <p style="color: #21b3dd; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
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
                            <input id="reg-email" name="email" type="email" class="default-input input" placeholder="Ex: seucontato@suaempresa.com.br" value="<?php echo e(old('email')); ?>" required>
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p style="color: #21b3dd; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
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
                            <input id="reg-whatsapp" name="whatsapp" type="text" class="default-input input" placeholder="(11) 99999-9999" value="<?php echo e(old('whatsapp')); ?>" required>
                        </div>
                        <?php $__errorArgs = ['whatsapp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p style="color: #21b3dd; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
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
                            <input id="reg-password" name="password" type="password" class="default-input input" placeholder="Digite sua senha" required>
                            <div class="show-text" onclick="togglePassword('reg-password')">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path id="eye-path-reg-password" opacity="0.5" d="M2 12C2 13.64 2.425 14.191 3.275 15.296C4.972 17.5 7.818 20 12 20C16.182 20 19.028 17.5 20.725 15.296C21.575 14.192 22 13.639 22 12C22 10.36 21.575 9.809 20.725 8.704C19.028 6.5 16.182 4 12 4C7.818 4 4.972 6.5 3.275 8.704C2.425 9.81 2 10.361 2 12Z" fill="#86898B"></path>
                                    <path id="eye-inner-reg-password" fill-rule="evenodd" clip-rule="evenodd" d="M8.25 12C8.25 11.0054 8.64509 10.0516 9.34835 9.34835C10.0516 8.64509 11.0054 8.25 12 8.25C12.9946 8.25 13.9484 8.64509 14.6517 9.34835C15.3549 10.0516 15.75 11.0054 15.75 12C15.75 12.9946 15.3549 13.9484 14.6517 14.6517C13.9484 15.3549 12.9946 15.75 12 15.75C11.0054 15.75 10.0516 15.3549 9.34835 14.6517C8.64509 13.9484 8.25 12.9946 8.25 12ZM9.75 12C9.75 11.4033 9.98705 10.831 10.409 10.409C10.831 9.98705 11.4033 9.75 12 9.75C12.5967 9.75 13.169 9.98705 13.591 10.409C14.0129 10.831 14.25 11.4033 14.25 12C14.25 12.5967 14.0129 13.169 13.591 13.591C13.169 14.0129 12.5967 14.25 12 14.25C11.4033 14.25 10.831 14.0129 10.409 13.591C9.98705 13.169 9.75 12.5967 9.75 12Z" fill="#86898B"></path>
                                </svg>
                            </div>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p style="color: #21b3dd; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </section>

                    <section class="bg-input">
                        <div class="checkbox-group">
                            <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required>
                            <label for="terms_accepted" class="default-label">
                                Li e aceito os <a href="#">Termos de Uso</a> e <a href="#">Política de Privacidade</a> *
                            </label>
                        </div>
                        <?php $__errorArgs = ['terms_accepted'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p style="color: #21b3dd; font-size: 12px; margin-top: 5px;"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </section>

                    <section class="actions">
                        <section class="primary bg-large-button">
                            <button type="submit">Criar Conta</button>
                        </section>
                    </section>

                    <div style="text-align: center; margin-top: 20px;">
                        <p style="color: #86898B; font-size: 14px;">
                            Já possui conta? <a href="<?php echo e(route('login')); ?>" style="color: #21b3dd; text-decoration: none;">Entrar</a>
                        </p>
                    </div>
                </section>
            </form>
        </div>
    </section>
</section>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="/js/auth.js"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/auth/register.blade.php ENDPATH**/ ?>