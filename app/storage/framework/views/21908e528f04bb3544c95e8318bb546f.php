<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>PLAYPAYMENTS // LOGIN</title>
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
            --accent: #ff003c;
            --accent-2: #ffffff;
            --border: rgba(255, 255, 255, 0.1);
            --font-display: 'Syncopate', sans-serif;
            --font-code: 'JetBrains Mono', monospace;
        }

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
                box-shadow: 0 0 30px rgba(212, 175, 55, 0.2);
                background: rgba(20, 20, 20, 0.8);
                z-index: 100;
            }
        }

        .card::before,
        .card::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border: 1px solid transparent;
            transition: 0.3s;
        }

        .card::before {
            top: -1px;
            left: -1px;
            border-top-color: var(--text);
            border-left-color: var(--text);
        }

        .card::after {
            bottom: -1px;
            right: -1px;
            border-bottom-color: var(--text);
            border-right-color: var(--text);
        }

        .card:hover::before,
        .card:hover::after {
            width: 100%;
            height: 100%;
            border-color: var(--accent);
        }

        .card-header {
            border-bottom: 1px solid var(--border);
            padding-bottom: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-id {
            font-family: var(--font-code);
            color: var(--accent);
            font-size: 0.8rem;
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

        .card-footer {
            margin-top: auto;
            font-family: var(--font-code);
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
            display: flex;
            justify-content: space-between;
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

        /* --- SCROLL PROXY --- */
        .scroll-proxy {
            height: 10000vh;
            position: absolute;
            width: 100%;
            z-index: -1;
        }

        /* --- LOGIN SIDEBAR --- */
        .login-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 420px;
            background: #000;
            z-index: 50;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            pointer-events: auto;
            border-right: 1px solid rgba(212, 175, 55, 0.3);
            padding: 3rem;
            box-shadow: 15px 0 40px rgba(0, 0, 0, 0.95), 2px 0 20px rgba(212, 175, 55, 0.15);
        }

        .login-form {
            width: 100%;
            max-width: 340px;
            display: flex;
            flex-direction: column;
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
            font-size: 1rem;
        }

        .cyber-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .cyber-input:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3);
            border-bottom-color: var(--accent-2);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
        }

        .input i {
            position: absolute;
            right: 15px;
            color: var(--text);
            opacity: 0.5;
            pointer-events: none;
        }

        .forgot-password {
            text-align: right;
            margin-top: -0.5rem;
        }

        .forgot-password a {
            color: var(--text);
            font-family: var(--font-code);
            font-size: 0.7rem;
            text-decoration: none;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
        }

        .forgot-password a:hover {
            color: var(--accent-2);
            border-bottom-color: var(--accent-2);
        }

        .form-signin {
            margin-top: 1rem;
        }

        .or-option {
            text-align: center;
            font-family: var(--font-code);
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.4);
            margin: 0.5rem 0;
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

        .form-signup {
            display: flex;
        }

        .cyber-btn {
            background: #af8a2a;
            color: #fff;
            border: none;
            padding: 16px;
            width: 100%;
            box-sizing: border-box;
            font-family: var(--font-display);
            font-size: 0.9rem;
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
            text-align: center;
            text-decoration: none;
        }

        .cyber-btn:hover {
            box-shadow: 0 0 25px #af8a2a;
            background: #fff;
            color: var(--bg);
        }

        .cyber-btn-outline {
            background: transparent;
            border: 1px solid var(--accent-2);
            color: var(--accent-2);
            box-shadow: none;
        }

        .cyber-btn-outline:hover {
            background: var(--accent-2);
            color: var(--bg);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
        }

        /* Alert Styles */
        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-family: var(--font-code);
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideUp {
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.15);
            border-left: 3px solid #4CAF50;
            color: #81c784;
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.15);
            border-left: 3px solid #f44336;
            color: #ef5350;
        }

        .alert i {
            font-size: 1.2rem;
        }
    </style>
</head>

<body>
    <!-- OVERLAYS -->
    <div class="scanlines"></div>
    <div class="vignette"></div>
    <div class="noise"></div>

    <!-- HUD -->
    <div class="hud">
        <div class="hud-top">
            <span>SISTEMA.PRONTO</span>
            <div class="hud-line"></div>
            <span>FPS: <strong id="fps">60</strong></span>
        </div>
        <div class="center-nav"
            style="align-self: flex-start; margin-top: auto; margin-bottom: auto; writing-mode: vertical-rl; transform: rotate(180deg);">
            TAXA DE TRANSAÇÃO (TPS) // <strong id="vel-readout">0.00</strong>
        </div>
        <div class="hud-bottom">
            <span>PING: <strong id="coord">000.000</strong>ms</span>
            <div class="hud-line"></div>
            <span>API.PIX V3.1.0</span>
        </div>
    </div>

    <!-- 3D WORLD -->
    <div class="viewport" id="viewport">
        <div class="world" id="world"></div>
    </div>

    <div class="scroll-proxy"></div>

    <!-- LOGIN FORM SIDEBAR -->
    <div class="login-sidebar">
        <div class="login-form">
            <!-- Logo -->
            <div class="card-header" style="justify-content: center; border-bottom: none; margin-bottom: 0;">
                <a href="<?php echo e(url('/')); ?>" class="logo-link">
                    <img src="<?php echo e(asset('images/playpayments-logo-top.webp')); ?>" alt="PlayPayments"
                        class="logo-img">
                </a>
            </div>

            <!-- Page Heading -->
            <div class="page-heading">
                <h2>LOGIN NA SUA CONTA</h2>
            </div>

            <!-- Alerts Container -->
            <div id="alerts-container"></div>

            <!-- Login Form -->
            <form action="<?php echo e(route('login.post')); ?>" method="POST" class="login-form-container"
                style="display: flex; flex-direction: column; gap: 1.5rem;">
                <?php echo csrf_field(); ?>
                <div class="login-form-items">
                    <!-- Email Input -->
                    <div class="items">
                        <label for="email">Endereço de E-mail</label>
                        <div class="input">
                            <input type="email" id="email" name="email" placeholder="seu@email.com" value="<?php echo e(old('email')); ?>" required=""
                                autocomplete="email" class="cyber-input">
                            <i class="ri-mail-line"></i>
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small style="color: #ef5350; font-family: var(--font-code); font-size: 0.75rem; margin-top: 4px;"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Password Input -->
                    <div class="items">
                        <label for="password">Senha</label>
                        <div class="input">
                            <input type="password" id="password" name="password" placeholder="Digite sua senha"
                                required="" autocomplete="current-password" class="cyber-input">
                            <i class="ri-lock-line"></i>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small style="color: #ef5350; font-family: var(--font-code); font-size: 0.75rem; margin-top: 4px;"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="forgot-password">
                        <a href="<?php echo e(route('password.request')); ?>">Esqueceu a senha?</a>
                    </div>

                    <!-- Login Button -->
                    <div class="form-signin">
                        <button type="submit" class="cyber-btn">Entrar Agora</button>
                    </div>

                    <!-- Divider -->
                    <div class="or-option">
                        <div></div>
                        <span>OU</span>
                    </div>

                    <!-- Signup Button -->
                    <div class="form-signup">
                        <a href="<?php echo e(route('register')); ?>"
                            class="cyber-btn cyber-btn-outline">Criar Conta</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // --- CONFIGURATION ---
        const CONFIG = {
            itemCount: 20,
            starCount: 150,
            zGap: 800,
            loopSize: 0,
            camSpeed: 2.5,
            colors: ['#ff003c', '#ffffff', '#ccff00', '#ffffff']
        };
        CONFIG.loopSize = CONFIG.itemCount * CONFIG.zGap;

        const TEXTS = ["PLAY PAY", "CHECKOUT", "GATEWAY", "LIQUIDEZ", "INSTANTÂNEO", "CRIPTO", "SPLIT", "SEGURO", "RECORRÊNCIA", "PAYMENTS"];

        // --- STATE ---
        const state = {
            scroll: 0,
            velocity: 0,
            targetSpeed: 0,
            mouseX: 0,
            mouseY: 0
        };

        const world = document.getElementById('world');
        const viewport = document.getElementById('viewport');
        const items = [];

        // --- INIT ---
        function init() {
            // Create Items
            for (let i = 0; i < CONFIG.itemCount; i++) {
                const el = document.createElement('div');
                el.className = 'item';

                const isHeading = i % 4 === 0;

                if (isHeading) {
                    const txt = document.createElement('div');
                    txt.className = 'big-text';
                    txt.innerText = TEXTS[i % TEXTS.length];
                    el.appendChild(txt);
                    items.push({
                        el, type: 'text',
                        x: 0, y: 0, rot: 0,
                        baseZ: -i * CONFIG.zGap
                    });
                } else {
                    const card = document.createElement('div');
                    card.className = 'card';
                    const randId = Math.floor(Math.random() * 9999);
                    card.innerHTML = `
                        <div class="card-header">
                            <span class="card-id">ID-${randId}</span>
                            <div style="width: 10px; height: 10px; background: var(--accent);"></div>
                        </div>
                        <h2>${TEXTS[i % TEXTS.length]}</h2>
                        <div class="card-footer">
                            <span>NODE: ${Math.floor(Math.random() * 100)}</span>
                            <span>LATÊNCIA: ${(Math.random() * 50).toFixed(1)}ms</span>
                        </div>
                        <div style="position:absolute; bottom:2rem; right:2rem; font-size:4rem; opacity:0.1; font-weight:900;">0${i}</div>
                    `;
                    el.appendChild(card);

                    // Spiral / Chaos positioning
                    const angle = (i / CONFIG.itemCount) * Math.PI * 6;
                    const radius = 400 + Math.random() * 200;
                    const x = Math.cos(angle) * (window.innerWidth * 0.3);
                    const y = Math.sin(angle) * (window.innerHeight * 0.3);
                    const rot = (Math.random() - 0.5) * 30;

                    items.push({
                        el, type: 'card',
                        x, y, rot,
                        baseZ: -i * CONFIG.zGap
                    });
                }
                world.appendChild(el);
            }

            // Create Stars
            for (let i = 0; i < CONFIG.starCount; i++) {
                const el = document.createElement('div');
                el.className = 'star';
                world.appendChild(el);
                items.push({
                    el, type: 'star',
                    x: (Math.random() - 0.5) * 3000,
                    y: (Math.random() - 0.5) * 3000,
                    baseZ: -Math.random() * CONFIG.loopSize
                });
            }

            // Events
            window.addEventListener('mousemove', (e) => {
                state.mouseX = (e.clientX / window.innerWidth - 0.5) * 2;
                state.mouseY = (e.clientY / window.innerHeight - 0.5) * 2;
            });
        }
        init();

        // --- LENIS ---
        const lenis = new Lenis({
            smooth: true,
            lerp: 0.08,
            direction: 'vertical',
            gestureDirection: 'vertical',
            smoothTouch: true
        });

        lenis.on('scroll', ({ scroll, velocity }) => {
            state.scroll = scroll;
            state.targetSpeed = velocity;
        });

        // --- RAF LOOP ---
        const feedbackVel = document.getElementById('vel-readout');
        const feedbackFPS = document.getElementById('fps');
        let lastTime = 0;

        function raf(time) {
            lenis.raf(time);

            // FPS
            const delta = time - lastTime;
            lastTime = time;
            if (time % 10 < 1) feedbackFPS.innerText = Math.round(1000 / delta);

            // Smooth Velocity
            state.velocity += (state.targetSpeed - state.velocity) * 0.1;

            // HUD Updates
            feedbackVel.innerText = Math.abs(state.velocity).toFixed(2);
            document.getElementById('coord').innerText = `${state.scroll.toFixed(0)}`;

            // --- RENDER LOGIC ---

            // 1. Camera Tilt & Shake
            const shake = state.velocity * 0.2;
            const tiltX = state.mouseY * 5 - state.velocity * 0.5;
            const tiltY = state.mouseX * 5;

            world.style.transform = `
                rotateX(${tiltX}deg) 
                rotateY(${tiltY}deg)
            `;

            // 2. Dynamic Perspective (Warp)
            const baseFov = 1000;
            const fov = baseFov - Math.min(Math.abs(state.velocity) * 10, 600);
            viewport.style.perspective = `${fov}px`;

            // 3. Item Loop
            const cameraZ = state.scroll * CONFIG.camSpeed;

            items.forEach(item => {
                let relZ = item.baseZ + cameraZ;
                const modC = CONFIG.loopSize;
                let vizZ = ((relZ % modC) + modC) % modC;
                if (vizZ > 500) vizZ -= modC;

                // Determine Opacity
                let alpha = 1;
                if (vizZ < -3000) alpha = 0;
                else if (vizZ < -2000) alpha = (vizZ + 3000) / 1000;

                if (vizZ > 100 && item.type !== 'star') alpha = 1 - ((vizZ - 100) / 400);

                if (alpha < 0) alpha = 0;
                item.el.style.opacity = alpha;

                if (alpha > 0) {
                    let trans = `translate3d(${item.x}px, ${item.y}px, ${vizZ}px)`;

                    if (item.type === 'star') {
                        // Warp Stars
                        const stretch = Math.max(1, Math.min(1 + Math.abs(state.velocity) * 0.1, 10));
                        trans += ` scale3d(1, 1, ${stretch})`;
                    } else if (item.type === 'text') {
                        trans += ` rotateZ(${item.rot}deg)`;
                        // RGB Split effect on text
                        if (Math.abs(state.velocity) > 1) {
                            const offset = state.velocity * 2;
                            item.el.style.textShadow = `${offset}px 0 #ff003c, ${-offset}px 0 rgba(255, 255, 255, 0.5)`;
                        } else {
                            item.el.style.textShadow = 'none';
                        }
                    } else {
                        // Card floats
                        const t = time * 0.001;
                        const float = Math.sin(t + item.x) * 10;
                        trans += ` rotateZ(${item.rot}deg) rotateY(${float}deg)`;
                    }

                    item.el.style.transform = trans;
                }
            });

            requestAnimationFrame(raf);
        }
        requestAnimationFrame(raf);

        // --- ALERTS HANDLING ---
        document.addEventListener('DOMContentLoaded', function() {
            const alertsContainer = document.getElementById('alerts-container');
            
            <?php if(session('success')): ?>
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success';
                successAlert.innerHTML = '<i class="ri-check-line"></i> <?php echo e(session("success")); ?>';
                alertsContainer.appendChild(successAlert);
                setTimeout(() => {
                    successAlert.style.animation = 'slideUp 0.3s ease-out forwards';
                    setTimeout(() => successAlert.remove(), 300);
                }, 5000);
            <?php endif; ?>

            <?php if(session('status')): ?>
                const statusAlert = document.createElement('div');
                statusAlert.className = 'alert alert-success';
                statusAlert.innerHTML = '<i class="ri-check-line"></i> <?php echo e(session("status")); ?>';
                alertsContainer.appendChild(statusAlert);
                setTimeout(() => {
                    statusAlert.style.animation = 'slideUp 0.3s ease-out forwards';
                    setTimeout(() => statusAlert.remove(), 300);
                }, 5000);
            <?php endif; ?>

            <?php if($errors->any()): ?>
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger';
                errorAlert.innerHTML = '<i class="ri-alert-line"></i> Falha na autenticação. Verifique seus dados.';
                alertsContainer.appendChild(errorAlert);
                setTimeout(() => {
                    errorAlert.style.animation = 'slideUp 0.3s ease-out forwards';
                    setTimeout(() => errorAlert.remove(), 300);
                }, 7000);
            <?php endif; ?>
        });

        console.log('%c🎮 PLAYPAYMENTS LOGIN SYSTEM ACTIVATED', 'color: #ff003c; font-size: 16px; font-weight: bold;');
        console.log('%cScroll • Mouse para controlar perspectiva • v3.1.0', 'color: #ffffff; font-size: 12px;');
    </script>
    <?php echo $__env->make('components.MacosDock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </body>

</html>
<?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/auth/login.blade.php ENDPATH**/ ?>