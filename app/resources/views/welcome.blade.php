<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>$playpayments | Next-Gen Financial Hub</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Plus+Jakarta+Sans:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <style>
        :root {
            --bg-deep: #050505;
            --emerald: #10b981;
            --emerald-glow: rgba(16, 185, 129, 0.4);
            --cyan: #21b3dd;
            --cyan-glow: rgba(33, 179, 221, 0.4);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass: rgba(15, 15, 15, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--bg-deep);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
        }

        /* --- BACKGROUND EFFECTS --- */
        #canvas-bg {
            position: fixed;
            top: 0;
            left: 0;
            z-index: -1;
            pointer-events: none;
        }

        .gradient-blur {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--emerald-glow) 0%, transparent 70%);
            filter: blur(80px);
            z-index: -1;
            opacity: 0.4;
            pointer-events: none;
            top: -100px;
            right: -100px;
        }

        .gradient-blur-2 {
            position: fixed;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, var(--cyan-glow) 0%, transparent 70%);
            filter: blur(100px);
            z-index: -1;
            opacity: 0.3;
            pointer-events: none;
            bottom: -200px;
            left: -200px;
        }

        /* --- NAVBAR --- */
        nav {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            max-width: 1200px;
            height: 70px;
            background: var(--glass);
            backdrop-filter: blur(15px);
            border: 1px solid var(--glass-border);
            border-radius: 100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        nav.scrolled {
            top: 10px;
            width: 95%;
            height: 60px;
            background: rgba(0,0,0,0.85);
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(to right, #fff, var(--cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #fff;
        }

        .btn-cta-nav {
            background: var(--emerald);
            color: #000;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.85rem;
            transition: all 0.3s;
        }

        .btn-cta-nav:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px var(--emerald-glow);
        }

        /* --- HERO SECTION --- */
        .hero {
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 20px;
            position: relative;
        }

        .hero-badge {
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--glass-border);
            padding: 8px 16px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--cyan);
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 2px;
            opacity: 0;
            transform: translateY(20px);
        }

        .hero h1 {
            font-size: clamp(3rem, 10vw, 6.5rem);
            line-height: 0.95;
            margin-bottom: 24px;
            letter-spacing: -4px;
            opacity: 0;
            transform: translateY(30px);
        }

        .hero h1 span {
            color: var(--emerald);
            display: block;
        }

        .hero p {
            max-width: 600px;
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 40px;
            opacity: 0;
            transform: translateY(40px);
        }

        .hero-btns {
            display: flex;
            gap: 20px;
            opacity: 0;
            transform: translateY(50px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--emerald), var(--cyan));
            color: #000;
            padding: 18px 40px;
            border-radius: 12px;
            font-weight: 800;
            text-decoration: none;
            font-size: 1rem;
            box-shadow: 0 10px 40px -10px var(--emerald-glow);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px -5px var(--cyan-glow);
        }

        .btn-secondary {
            border: 1px solid var(--glass-border);
            background: var(--glass);
            color: #fff;
            padding: 18px 40px;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.05);
            border-color: #fff;
        }

        /* --- FEATURES --- */
        .features {
            padding: 120px 5%;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            margin-bottom: 80px;
        }

        .section-header h2 {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.03) 0%, transparent 100%);
            border: 1px solid var(--glass-border);
            padding: 40px;
            border-radius: 30px;
            transition: all 0.4s;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            border-color: var(--cyan);
            transform: translateY(-10px);
            background: linear-gradient(180deg, rgba(33, 179, 221, 0.05) 0%, transparent 100%);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--cyan);
            border-radius: 15px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 16px;
        }

        .feature-card p {
            color: var(--text-muted);
            font-size: 1rem;
        }

        /* --- 3D MOUSE EFFECT --- */
        .glow-cursor {
            position: fixed;
            width: 400px;
            height: 400px;
            background: var(--emerald-glow);
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: -1;
            transform: translate(-50%, -50%);
            transition: width 0.3s, height 0.3s;
        }

        /* --- FOOTER --- */
        footer {
            padding: 80px 5%;
            border-top: 1px solid var(--glass-border);
            text-align: center;
        }

        .footer-logo {
            font-size: 2rem;
            margin-bottom: 20px;
        }

        .copyright {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        /* --- MICROS ANIMATIONS --- */
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .floating-element {
            animation: floating 4s ease-in-out infinite;
        }

        /* --- MOBILE --- */
        @media (max-width: 768px) {
            .nav-links { display: none; }
            .hero h1 { font-size: 3.5rem; letter-spacing: -2px; }
            .section-header h2 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <div id="canvas-bg"></div>
    <div class="gradient-blur"></div>
    <div class="gradient-blur-2"></div>
    <div class="glow-cursor" id="cursor"></div>

    <nav id="navbar">
        <div class="logo">$playpayments</div>
        <div class="nav-links">
            <a href="#home">Início</a>
            <a href="#features">Recursos</a>
            <a href="#solutions">Soluções</a>
            <a href="#pricing">Preços</a>
        </div>
        <div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-cta-nav">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn-cta-nav">Entrar</a>
                @endauth
            @endif
        </div>
    </nav>

    <section class="hero" id="home">
        <div class="hero-badge">Web3.0 Payments Infrastructure</div>
        <h1 id="hero-title">A Nova Era dos <span>Pagamentos.</span></h1>
        <p id="hero-desc">Processamento em tempo real, segurança militar e uma interface que respira o futuro. Conecte sua empresa ao ecossistema $playpayments.</p>
        <div class="hero-btns" id="hero-btns">
            <a href="#" class="btn-primary">Criar Conta Agora</a>
            <a href="#" class="btn-secondary">Ver Documentação</a>
        </div>
    </section>

    <section class="features" id="features">
        <div class="section-header">
            <h2>Por que nós?</h2>
            <p>A tecnologia que move os grandes players do mercado.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Segurança Máxima</h3>
                <p>Criptografia de ponta a ponta e protocolos de proteção multicamadas para seus ativos.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
                <h3>Escalabilidade</h3>
                <p>Infraestrutura pronta para processar milhões de requisições por segundo sem latência.</p>
            </div>
            <div class="feature-card reveal">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <h3>Globalização</h3>
                <p>Transações sem fronteiras em mais de 150 moedas e redes blockchain.</p>
            </div>
        </div>
    </section>

    <footer>
        <div class="logo footer-logo">$playpayments</div>
        <div class="nav-links" style="display: block; margin-bottom: 30px;">
            <a href="#">Privacidade</a> | <a href="#">Termos</a> | <a href="#">API</a>
        </div>
        <p class="copyright">© 2026 $playpayments Digital Assets S.A. Todos os direitos reservados.</p>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/0.162.0/three.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

    <script>
        // --- CURSOR GLOW ---
        const cursor = document.getElementById('cursor');
        window.addEventListener('mousemove', (e) => {
            gsap.to(cursor, {
                x: e.clientX,
                y: e.clientY,
                duration: 0.8,
                ease: "power2.out"
            });
        });

        // --- NAVBAR SCROLL EFFECT ---
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) navbar.classList.add('scrolled');
            else navbar.classList.remove('scrolled');
        });

        // --- GSAP REVEALS ---
        gsap.to('.hero-badge', { opacity: 1, y: 0, duration: 1, delay: 0.2 });
        gsap.to('#hero-title', { opacity: 1, y: 0, duration: 1, delay: 0.4 });
        gsap.to('#hero-desc', { opacity: 1, y: 0, duration: 1, delay: 0.6 });
        gsap.to('#hero-btns', { opacity: 1, y: 0, duration: 1, delay: 0.8 });

        gsap.utils.toArray('.reveal').forEach(elem => {
            gsap.fromTo(elem, 
                { opacity: 0, y: 50 }, 
                { 
                    opacity: 1, y: 0, 
                    duration: 1, 
                    scrollTrigger: {
                        trigger: elem,
                        start: "top 85%",
                    }
                }
            );
        });

        // --- THREE.JS BACKGROUND ---
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        document.getElementById('canvas-bg').appendChild(renderer.domElement);

        const geometry = new THREE.IcosahedronGeometry(20, 1);
        const material = new THREE.MeshBasicMaterial({ 
            color: 0x10b981, 
            wireframe: true,
            transparent: true,
            opacity: 0.05
        });
        const mesh = new THREE.Mesh(geometry, material);
        scene.add(mesh);

        // Particles
        const partGeo = new THREE.BufferGeometry();
        const partCount = 500;
        const posArray = new Float32Array(partCount * 3);
        for(let i=0; i<partCount * 3; i++) {
            posArray[i] = (Math.random() - 0.5) * 1000;
        }
        partGeo.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
        const partMat = new THREE.PointsMaterial({ size: 2, color: 0x21b3dd, transparent: true, opacity: 0.3 });
        const partMesh = new THREE.Points(partGeo, partMat);
        scene.add(partMesh);

        camera.position.z = 100;

        function animate() {
            requestAnimationFrame(animate);
            mesh.rotation.x += 0.001;
            mesh.rotation.y += 0.002;
            partMesh.rotation.y += 0.0005;
            renderer.render(scene, camera);
        }
        animate();

        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</body>
</html>
