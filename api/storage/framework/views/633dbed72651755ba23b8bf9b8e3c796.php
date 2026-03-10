<?php $__env->startSection('title', 'Recuperar Senha'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="/css/login.css" type="text/css" media="all">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<section class="bg-auth">
    <figure>
        <img src="/images/playpayments-logo-top.webp" alt="playpayments Payments - Logo">
    </figure>
    
    <section class="bg-modal">
        <h2>Recuperar Senha</h2>
        <p>Informe seu e-mail para receber o link de recuperação</p>
        
        <?php if(session('status')): ?>
            <div style="background: rgba(0, 255, 0, 0.1); border: 1px solid #00ff00; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #00ff00;">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div style="background: rgba(106, 0, 0, 0.1); border: 1px solid #21b3dd; border-radius: 8px; padding: 12px; margin-bottom: 20px; color: #21b3dd;">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <p style="margin: 0;"><?php echo e($error); ?></p>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('password.email')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <section class="form">
                <section class="bg-input">
                    <div class="bg-label">
                        <label class="default-label label" for="email">E-mail</label>
                    </div>
                    <div class="input-group">
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="default-input input" 
                            placeholder="Ex: seucontato@suaempresa.com.br" 
                            maxlength="256" 
                            autocomplete="email" 
                            value="<?php echo e(old('email')); ?>"
                            required
                        >
                    </div>
                    <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="error-text"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </section>

                <section class="actions">
                    <section class="primary bg-large-button">
                        <button type="submit">Enviar Link de Recuperação</button>
                    </section>
                </section>

                <div style="text-align: center; margin-top: 20px;">
                    <a href="<?php echo e(route('login')); ?>" style="color: #86898B; text-decoration: none; font-size: 14px;">Voltar para o login</a>
                </div>
            </section>
        </form>
    </section>
</section>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="/js/alerts.js"></script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/auth/forgot-password.blade.php ENDPATH**/ ?>