<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>PLAYPAYMENTS // CADASTRO</title>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.33/dist/lenis.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;800&family=Syncopate:wght@400;700&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --bg: #030303;
            --card-bg: rgba(10, 10, 10, 0.4);
            --text: #e0e0e0;
            --accent: #af8a2a;
            --accent-glow: #af8a2a;
            --accent-2: #ffffff;
            --border: rgba(255, 255, 255, 0.1);
            --font-display: 'Syncopate', sans-serif;
            --font-code: 'JetBrains Mono', monospace;
        }

        /* Override for compatibility with login's red accent if needed, but here we use gold/white */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background: var(--bg);
            color: var(--text);
            font-family: var(--font-display);
            overflow: hidden;
            width: 100vw;
            height: 100vh;
            cursor: crosshair;
        }

        /* --- POST PROCESSING & OVERLAYS --- */
        .scanlines {
            position: fixed;
            inset: 0;
            background: linear-gradient(to bottom,
                    rgba(255, 255, 255, 0),
                    rgba(255, 255, 255, 0) 50%,
                    rgba(0, 0, 0, 0.2) 50%,
                    rgba(0, 0, 0, 0.2));
            background-size: 100% 4px;
            pointer-events: none;
            z-index: 10;
        }

        .vignette {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle, transparent 40%, #000 120%);
            z-index: 11;
            pointer-events: none;
        }

        .noise {
            position: fixed;
            inset: 0;
            z-index: 12;
            opacity: 0.07;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        }

        /* --- HUD --- */
        .hud {
            position: fixed;
            inset: 2rem;
            z-index: 20;
            pointer-events: none;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: var(--font-code);
            font-size: 10px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
        }

        .hud-top,
        .hud-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hud strong {
            color: var(--accent-2);
        }

        .hud-line {
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            margin: 0 1rem;
            position: relative;
        }

        .hud-line::after {
            content: '';
            position: absolute;
            right: 0;
            top: -2px;
            width: 5px;
            height: 5px;
            background: var(--accent);
        }

        .center-nav {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- 3D SCENE --- */
        .viewport {
            position: fixed;
            inset: 0;
            perspective: 1000px;
            overflow: hidden;
            z-index: 1;
        }

        .world {
            position: absolute;
            top: 50%;
            left: 50%;
            transform-style: preserve-3d;
            will-change: transform;
        }

        .item {
            position: absolute;
            left: 0;
            top: 0;
            backface-visibility: hidden;
            transform-origin: center center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- CARDS & CONTENT --- */
        .card {
            width: 320px;
            height: 460px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            position: relative;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.5), 0 20px 50px rgba(0, 0, 0, 0.5);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            transform: translate(-50%, -50%);
        }

        @media (hover: hover) {
            .card:hover {
                border-color: var(--accent);
                box-shadow: 0 0 30px rgba(175, 138, 42, 0.2);
                background: rgba(20, 20, 20, 0.8);
                z-index: 100;
            }
        }

        .card h2 {
            font-size: 2.5rem;
            line-height: 0.9;
            margin: 0;
            text-transform: uppercase;
            font-weight: 700;
            color: #fff;
            mix-blend-mode: hard-light;
        }

        /* --- BIG TEXT --- */
        .big-text {
            font-size: 15vw;
            font-weight: 800;
            color: transparent;
            -webkit-text-stroke: 2px rgba(255, 255, 255, 0.15);
            text-transform: uppercase;
            white-space: nowrap;
            transform: translate(-50%, -50%);
            pointer-events: none;
            letter-spacing: -0.5rem;
            mix-blend-mode: overlay;
        }

        /* --- PARTICLES --- */
        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: white;
            transform: translate(-50%, -50%);
        }

        /* --- LOGIN SIDEBAR (ADAPTED FOR REGISTER) --- */
        .login-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 450px;
            background: #000;
            z-index: 50;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            pointer-events: auto;
            border-right: 1px solid rgba(175, 138, 42, 0.3);
            padding: 3rem;
            box-shadow: 15px 0 40px rgba(0, 0, 0, 0.95), 2px 0 20px rgba(175, 138, 42, 0.15);
            overflow-y: auto;
            scrollbar-width: none;
        }
        .login-sidebar::-webkit-scrollbar { display: none; }

        .login-form {
            width: 100%;
            max-width: 360px;
            display: flex;
            flex-direction: column;
            padding-bottom: 2rem;
        }

        .logo-img {
            max-height: 80px;
            filter: drop-shadow(0 0 10px #af8a2a);
        }

        .page-heading {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-heading h2 {
            font-size: 1.5rem;
            letter-spacing: 2px;
            margin: 0;
        }

        .login-form-items {
            display: flex;
            flex-direction: column;
            gap: 1.2rem;
        }

        .items {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .items label {
            font-family: var(--font-code);
            font-size: 0.8rem;
            color: var(--accent-2);
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .cyber-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            color: var(--text);
            font-family: inherit;
            padding: 12px 40px 12px 15px;
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
            font-size: 0.9rem;
        }

        .cyber-input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-bottom-color: var(--accent);
            box-shadow: 0 4px 15px rgba(175, 138, 42, 0.1);
        }

        .input i {
            position: absolute;
            right: 15px;
            color: var(--text);
            opacity: 0.5;
            pointer-events: none;
        }

        .cyber-btn {
            background: #af8a2a;
            color: #fff;
            border: none;
            padding: 16px;
            width: 100%;
            box-sizing: border-box;
            font-family: var(--font-display);
            font-size: 0.8rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(175, 138, 42, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            margin-top: 1rem;
        }

        .cyber-btn:hover {
            box-shadow: 0 0 25px #af8a2a;
            background: #fff;
            color: var(--bg);
        }

        .or-option {
            text-align: center;
            font-family: var(--font-code);
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
            margin: 1rem 0;
            position: relative;
        }

        .or-option div {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-50%);
            z-index: 0;
        }

        .or-option span {
            background: #000;
            padding: 0 15px;
            position: relative;
            z-index: 1;
        }

        .checkbox-group {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-family: var(--font-code);
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }

        .checkbox-group input[type="checkbox"] {
            accent-color: #af8a2a;
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .checkbox-group a {
            color: #af8a2a;
            text-decoration: none;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-family: var(--font-code);
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.15);
            border-left: 3px solid #f44336;
            color: #ef5350;
        }
    </style>
</head>

<body>
    <div class="scanlines"></div>
    <div class="vignette"></div>
    <div class="noise"></div>

    <div class="hud">
        <div class="hud-top">
            <span>SISTEMA.PRONTO</span>
            <div class="hud-line"></div>
            <span>FPS: <strong id="fps">60</strong></span>
        </div>
        <div class="center-nav"
            style="align-self: flex-start; margin-top: auto; margin-bottom: auto; writing-mode: vertical-rl; transform: rotate(180deg);">
            CADASTRO // <strong id="vel-readout">0.00</strong>
        </div>
        <div class="hud-bottom">
            <span>REGISTRO: <strong id="coord">000</strong></span>
            <div class="hud-line"></div>
            <span>API.REGISTER V3.1.0</span>
        </div>
    </div>

    <div class="viewport" id="viewport">
        <div class="world" id="world"></div>
    </div>

    <div class="login-sidebar">
        <div class="login-form">
            <div class="card-header" style="justify-content: center; border-bottom: none; margin-bottom: 2rem;">
                <a href="<?php echo e(url('/')); ?>" class="logo-link">
                    <img src="<?php echo e(asset('images/playpayments-logo-top.webp')); ?>" alt="PlayPayments" class="logo-img">
                </a>
            </div>

            <div class="page-heading">
                <h2>CRIAR SUA CONTA</h2>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <i class="ri-alert-line"></i>
                    Verifique os campos abaixo.
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

                <div class="login-form-items">
                    <div class="items">
                        <label for="reg-name">Nome Completo</label>
                        <div class="input">
                            <input type="text" id="reg-name" name="name" placeholder="Seu nome" value="<?php echo e(old('name')); ?>" required class="cyber-input">
                            <i class="ri-user-line"></i>
                        </div>
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small style="color: #ef5350; font-family: var(--font-code); font-size: 0.7rem; margin-top: 4px;"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="items">
                        <label for="reg-email">E-mail</label>
                        <div class="input">
                            <input type="email" id="reg-email" name="email" placeholder="seu@email.com" value="<?php echo e(old('email')); ?>" required class="cyber-input">
                            <i class="ri-mail-line"></i>
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small style="color: #ef5350; font-family: var(--font-code); font-size: 0.7rem; margin-top: 4px;"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="items">
                        <label for="reg-whatsapp">WhatsApp</label>
                        <div class="input">
                            <input type="text" id="reg-whatsapp" name="whatsapp" placeholder="(00) 00000-0000" value="<?php echo e(old('whatsapp')); ?>" required class="cyber-input">
                            <i class="ri-whatsapp-line"></i>
                        </div>
                        <?php $__errorArgs = ['whatsapp'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small style="color: #ef5350; font-family: var(--font-code); font-size: 0.7rem; margin-top: 4px;"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="items">
                        <label for="reg-password">Senha</label>
                        <div class="input">
                            <input type="password" id="reg-password" name="password" placeholder="Mínimo 6 caracteres" required class="cyber-input">
                            <i class="ri-lock-line"></i>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small style="color: #ef5350; font-family: var(--font-code); font-size: 0.7rem; margin-top: 4px;"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="items">
                        <label for="reg-password-confirm">Confirmar Senha</label>
                        <div class="input">
                            <input type="password" id="reg-password-confirm" name="password_confirmation" placeholder="Repita a senha" required class="cyber-input">
                            <i class="ri-lock-password-line"></i>
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <input id="terms_accepted" name="terms_accepted" type="checkbox" value="1" required>
                        <label for="terms_accepted">
                            Li e aceito os <a href="#">Termos de Uso</a> e <a href="#">Política de Privacidade</a>
                        </label>
                    </div>

                    <button type="submit" class="cyber-btn">Registrar Agora</button>

                    <div class="or-option">
                        <div></div>
                        <span>OU</span>
                    </div>

                    <a href="<?php echo e(route('login')); ?>" class="cyber-btn" style="background: transparent; border: 1px solid #fff; box-shadow: none;">Já tenho conta</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        const CONFIG = {
            itemCount: 20, zGap: 800, camSpeed: 2.5,
            colors: ['#af8a2a', '#ffffff', '#af8a2a', '#ffffff']
        };
        CONFIG.loopSize = CONFIG.itemCount * CONFIG.zGap;
        const TEXTS = ["CADASTRO", "CHECKOUT", "GATEWAY", "LIQUIDEZ", "INSTANTÂNEO", "CRIPTO", "SPLIT", "SEGURO", "RECORRÊNCIA", "PAYMENTS"];
        
        const state = { scroll: 0, velocity: 0, targetSpeed: 0, mouseX: 0, mouseY: 0 };
        const world = document.getElementById('world');
        const viewport = document.getElementById('viewport');
        const items = [];

        function init() {
            for (let i = 0; i < CONFIG.itemCount; i++) {
                const el = document.createElement('div');
                el.className = 'item';
                if (i % 4 === 0) {
                    const txt = document.createElement('div');
                    txt.className = 'big-text';
                    txt.innerText = TEXTS[i % TEXTS.length];
                    el.appendChild(txt);
                    items.push({ el, type: 'text', x: 0, y: 0, rot: 0, baseZ: -i * CONFIG.zGap });
                } else {
                    const card = document.createElement('div');
                    card.className = 'card';
                    card.innerHTML = `<div class="card-header"><span class="card-id">NODE-${i}</span></div><h2>${TEXTS[i % TEXTS.length]}</h2><div class="card-footer"><span>V3.0</span><span>0${i}</span></div>`;
                    el.appendChild(card);
                    const angle = (i / CONFIG.itemCount) * Math.PI * 6;
                    items.push({ el, type: 'card', x: Math.cos(angle) * 500, y: Math.sin(angle) * 300, rot: (Math.random()-0.5)*30, baseZ: -i * CONFIG.zGap });
                }
                world.appendChild(el);
            }
            window.addEventListener('mousemove', (e) => {
                state.mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
                state.mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
            });
        }
        init();

        const lenis = new Lenis({ lerp: 0.08 });
        lenis.on('scroll', ({ scroll, velocity }) => { state.scroll = scroll; state.targetSpeed = velocity; });

        let lastTime = 0;
        function raf(time) {
            lenis.raf(time);
            const delta = time - lastTime; lastTime = time;
            document.getElementById('fps').innerText = Math.round(1000/delta);
            state.velocity += (state.targetSpeed - state.velocity) * 0.1;
            document.getElementById('vel-readout').innerText = Math.abs(state.velocity).toFixed(2);

            world.style.transform = `rotateX(${state.mouseY*5}deg) rotateY(${state.mouseX*5}deg)`;
            const cameraZ = state.scroll * CONFIG.camSpeed;

            items.forEach(item => {
                let relZ = item.baseZ + cameraZ;
                let vizZ = ((relZ % CONFIG.loopSize) + CONFIG.loopSize) % CONFIG.loopSize;
                if (vizZ > 500) vizZ -= CONFIG.loopSize;
                item.el.style.opacity = vizZ > 100 ? 1 - ((vizZ-100)/400) : (vizZ < -2000 ? 0 : 1);
                item.el.style.transform = `translate3d(${item.x}px, ${item.y}px, ${vizZ}px) rotateZ(${item.rot}deg)`;
            });
            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);

        // WhatsApp Mask
        const whatsapp = document.getElementById('reg-whatsapp');
        if (whatsapp) {
            whatsapp.addEventListener('input', (e) => {
                let v = e.target.value.replace(/\D/g, '');
                if (v.length > 11) v = v.slice(0, 11);
                if (v.length > 2) v = `(${v.slice(0, 2)}) ${v.slice(2)}`;
                if (v.length > 9) v = `${v.slice(0, 10)}-${v.slice(10)}`;
                e.target.value = v;
            });
        }
    </script>
</body>
</html>
<?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/auth/register.blade.php ENDPATH**/ ?>