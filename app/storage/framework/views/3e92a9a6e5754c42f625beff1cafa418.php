<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($seoMeta['title'] ?? config('app.name', '$playpayments')); ?> - <?php echo $__env->yieldContent('title', 'Dashboard'); ?></title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo e($seoMeta['description'] ?? '$playpayments - Gateway de Pagamento PIX'); ?>">
    <meta name="keywords" content="<?php echo e($seoMeta['keywords'] ?? 'playpayments, gateway pagamento, pix'); ?>">
    <meta name="author" content="<?php echo e($seoMeta['author'] ?? '$playpayments'); ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo e($seoMeta['og_type'] ?? 'website'); ?>">
    <meta property="og:url" content="<?php echo e($seoMeta['og_url'] ?? url()->current()); ?>">
    <meta property="og:title" content="<?php echo e($seoMeta['og_title'] ?? '$playpayments - Gateway de Pagamento PIX'); ?>">
    <meta property="og:description" content="<?php echo e($seoMeta['og_description'] ?? 'Plataforma completa de gateway de pagamento PIX'); ?>">
    <meta property="og:image" content="<?php echo e($seoMeta['og_image'] ?? asset('images/playpayments-logo-top.webp')); ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="<?php echo e($seoMeta['twitter_card'] ?? 'summary_large_image'); ?>">
    <meta name="twitter:title" content="<?php echo e($seoMeta['og_title'] ?? '$playpayments - Gateway de Pagamento PIX'); ?>">
    <meta name="twitter:description" content="<?php echo e($seoMeta['og_description'] ?? 'Plataforma completa de gateway de pagamento PIX'); ?>">
    <meta name="twitter:image" content="<?php echo e($seoMeta['og_image'] ?? asset('images/playpayments-logo-top.webp')); ?>">

    <!-- Favicon -->
    <link rel="icon" href="<?php echo e($whiteLabelFavicon ?? asset('favicon.svg')); ?>" type="image/svg+xml">
    <link rel="alternate icon" href="<?php echo e($whiteLabelFaviconIco ?? asset('favicon.ico')); ?>" type="image/x-icon">

    <!-- Theme Configuration -->
    <meta name="theme-background" content="<?php echo e($themeVars['theme_background'] ?? '#0d0d0d'); ?>">
    <meta name="theme-card-bg" content="<?php echo e($themeVars['theme_card_bg'] ?? '#1a1a1a'); ?>">
    <meta name="theme-sidebar-bg" content="<?php echo e($themeVars['theme_sidebar_bg'] ?? '#0f0f0f'); ?>">
    <meta name="theme-header-bg" content="<?php echo e($themeVars['theme_sidebar_bg'] ?? '#0f0f0f'); ?>">
    <meta name="theme-border" content="<?php echo e($themeVars['theme_border'] ?? '#2c2c2e'); ?>">
    <meta name="theme-text" content="<?php echo e(env('THEME_TEXT', '#ffffff')); ?>">
    <meta name="theme-text-secondary" content="<?php echo e($themeVars['theme_text_secondary'] ?? '#a1a1aa'); ?>">
    <meta name="theme-primary" content="<?php echo e(env('THEME_PRIMARY', '#3b82f6')); ?>">
    <meta name="theme-success" content="<?php echo e(env('THEME_SUCCESS', '#10b981')); ?>">
    <meta name="theme-warning" content="<?php echo e(env('THEME_WARNING', '#f59e0b')); ?>">
    <meta name="theme-danger" content="<?php echo e(env('THEME_DANGER', '#10b981')); ?>">
    <meta name="theme-info" content="<?php echo e(env('THEME_INFO', '#6366f1')); ?>">

    <!-- Preload Critical Resources -->
    <link rel="preload" href="/images/playpayments-logo-top.webp" as="image" fetchpriority="high">
    <link rel="preload" href="<?php echo e(asset('css/dashboard.css')); ?>" as="style">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/light.css">

    <!-- Logo Size Configuration -->
    <style>
        :root {
            --logo-auth-height: <?php echo e(env('LOGO_AUTH_SIZE', 64)); ?>px;
            --logo-dashboard-height: <?php echo e(env('LOGO_DASHBOARD_SIZE', 40)); ?>px;
        }
        
        /* Scrollbar Customizada Verde */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 200, 83, 0.1);
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 200, 83, 0.4);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 200, 83, 0.7);
        }
        
        /* Estilos da Sidebar */
        .sidebar-container {
            background: #161616 !important;
            border-right: 1px solid #1f1f1f;
            overflow: visible !important;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        /* Garantir que a barra laranja apareça - posicionada na borda esquerda do aside */
        .sidebar-container nav {
            position: relative;
            overflow-x: visible !important;
        }
        
        /* Barra laranja de indicação de item ativo */
        .sidebar-container nav a .absolute.bg-\\[\\#D4AF37\\] {
            left: -20px !important;
            z-index: 10;
        }
        
        /* Scrollbar da Sidebar */
        .custom-scrollbar {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        /* Scrollbar Transparente Global */
        * {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }
        
        *::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        *::-webkit-scrollbar-track {
            background: transparent;
        }
        
        *::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 10px;
        }
        
        *::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Scrollbar para área de conteúdo */
        .scrollable-content {
            scrollbar-width: thin;
            scrollbar-color: transparent transparent;
        }
        
        .scrollable-content::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollable-content::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .scrollable-content::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 10px;
        }
        
        .scrollable-content::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Efeito Glow Verde Neon */
        .hover\:shadow-glow-green:hover {
            box-shadow: 0 0 15px rgba(0, 200, 83, 0.5), 0 0 30px rgba(0, 200, 83, 0.3);
        }
        
        /* Sombra 3D Elevada */
        .shadow-3xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        
        /* Animação do Mini Gráfico */
        @keyframes draw-line {
            0% {
                stroke-dasharray: 1000;
                stroke-dashoffset: 1000;
            }
            100% {
                stroke-dasharray: 1000;
                stroke-dashoffset: 0;
            }
        }
        
        .animate-draw-line {
            animation: draw-line 2s ease-in-out infinite;
        }
    </style>

    <!-- Prefetch DNS para recursos externos -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Suprimir aviso de produção do Tailwind CDN
        if (window.tailwind) {
            window.tailwind.config = window.tailwind.config || {};
        }
        // Interceptar console.warn para suprimir aviso específico do Tailwind
        const originalWarn = console.warn;
        console.warn = function(...args) {
            if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com should not be used in production')) {
                return; // Suprimir este aviso específico
            }
            originalWarn.apply(console, args);
        };
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-new.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/instant-transitions.css')); ?>">
    
    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <!-- App JavaScript -->
    <script src="<?php echo e(asset('js/app.js')); ?>" defer></script>
    
    <!-- Instant Navigation -->
    <script src="<?php echo e(asset('js/instant-navigation.js')); ?>" defer></script>
    
    <!-- Garantir fundo visível mesmo sem CSS -->
    <style>
        body {
            background-color: #000000 !important;
            color: #FFFFFF !important;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
            padding-bottom: 100px;
        }
        body.no-css {
            background-color: #000000 !important;
        }
        
        /* Ajuste para mobile: sidebar como overlay */
        @media (max-width: 1023px) {
            html, body {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
                background-color: #000000 !important;
                height: 100vh;
                height: 100dvh;
            }
            
            .dashboard-wrapper {
                width: 100vw !important;
                max-width: 100vw !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
                position: relative !important;
                background-color: #000000 !important;
                height: 100vh;
                height: 100dvh;
            }
            
            /* Sidebar como overlay no mobile */
            .sidebar-container {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                height: 100vh !important;
                height: 100dvh !important;
                width: 266px !important;
                z-index: 9999 !important;
                transform: translateX(-100%) !important;
                transition: transform 0.3s ease-in-out, background-color 0.3s ease !important;
                margin: 0 !important;
                border-radius: 0 !important;
                background: #161616 !important;
            }
            
            .sidebar-container.open {
                transform: translateX(0) !important;
            }
            
            /* Overlay no mobile */
            .sidebar-overlay {
                display: block !important;
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                height: 100dvh !important;
                background-color: rgba(0, 0, 0, 0.7) !important;
                z-index: 9998 !important;
            }
            
            .sidebar-container > div {
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
            }
            
            /* Garantir que a sidebar mobile tenha estrutura correta */
            .sidebar-container nav {
                flex: 1 !important;
                overflow-y: auto !important;
                overflow-x: visible !important;
                -webkit-overflow-scrolling: touch !important;
                position: relative !important;
            }
            
            /* Main content sempre visível no mobile */
            .main-content {
                width: 100vw !important;
                max-width: 100vw !important;
                margin: 0 !important;
                padding: 0 !important;
                flex: 1 1 100% !important;
                min-width: 0 !important;
                overflow-x: hidden !important;
                background-color: #000000 !important;
                position: relative !important;
                z-index: 1 !important;
                height: 100vh;
                height: 100dvh;
            }
            
            .main-content header {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                position: relative !important;
                z-index: 10 !important;
            }
            
            .main-content header > div {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 1rem !important;
            }
            
            .main-content .scrollable-content {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow-x: hidden !important;
                background-color: #000000 !important;
                position: relative !important;
                z-index: 1 !important;
                flex: 1 !important;
                overflow-y: auto !important;
            }
            
            .main-content .dashboard-container {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 16px !important;
                box-sizing: border-box !important;
                background-color: #000000 !important;
                padding-bottom: 100px !important;
            }
            
            /* Garantir que o botão de fechar funcione */
            .sidebar-container .lg\\:hidden {
                display: flex !important;
            }
        }
        
        @media (min-width: 1024px) {
            html, body {
                height: 100vh;
                overflow: hidden;
            }
            
            .dashboard-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                background-color: #000000 !important;
                display: flex !important;
                gap: 0 !important;
                height: 100vh !important;
                overflow: hidden !important;
                box-sizing: border-box !important;
                align-items: stretch !important;
            }
            
            /* No desktop, overlay não deve aparecer */
            .sidebar-overlay {
                display: none !important;
            }
            
            .sidebar-container {
                position: relative !important;
                transform: translateX(0) !important;
                width: 266px;
                flex-shrink: 0;
                height: calc(100vh - 40px) !important;
                min-height: calc(100vh - 40px) !important;
                max-height: calc(100vh - 40px) !important;
                overflow: visible !important;
                margin: 20px 0 20px 20px !important;
                border-radius: 20px !important;
                transition: width 0.3s ease-in-out, background-color 0.3s ease;
                background: #161616 !important;
                z-index: 1 !important;
                display: flex !important;
                flex-direction: column !important;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.5) !important;
            }
            
            .sidebar-container > div {
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: visible !important;
            }
            
            .sidebar-container nav {
                flex: 1 !important;
                overflow-y: auto !important;
                overflow-x: visible !important;
                position: relative !important;
            }
            
            .main-content {
                flex: 1 !important;
                min-width: 0 !important;
                margin: 20px 20px 20px 0 !important;
                padding: 0 !important;
                background-color: #000000 !important;
                height: calc(100vh - 40px) !important;
                min-height: calc(100vh - 40px) !important;
                max-height: calc(100vh - 40px) !important;
                overflow: hidden !important;
                display: flex !important;
                flex-direction: column !important;
                border-radius: 20px !important;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.5) !important;
            }
            
            .main-content header {
                border-radius: 20px 20px 0 0 !important;
                overflow: hidden !important;
            }
            
            .main-content .scrollable-content {
                flex: 1 !important;
                overflow-y: auto !important;
                overflow-x: hidden !important;
                border-radius: 0 0 20px 20px !important;
                padding-bottom: 120px !important;
            }
            
            .sidebar-container.collapsed {
                width: 72px !important;
            }
            
            .sidebar-container.collapsed .sidebar-text {
                display: none;
            }
            
            .sidebar-container.collapsed .sidebar-section-title {
                display: none;
            }
            
            .sidebar-container.collapsed .sidebar-logo-full {
                display: none;
            }
            
            .sidebar-container.collapsed .sidebar-logo-icon {
                display: block !important;
            }
            
            .sidebar-container:not(.collapsed) .sidebar-logo-icon {
                display: none !important;
            }
            
            .sidebar-container.collapsed .sidebar-link {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            .sidebar-container.collapsed .sidebar-link .absolute {
                left: 0 !important;
            }
            
            .sidebar-container.collapsed .sidebar-link.text-white {
                color: #D4AF37 !important;
            }
            
            .sidebar-container.collapsed .sidebar-link.text-white svg {
                color: #D4AF37 !important;
                fill: none;
                stroke: #D4AF37 !important;
            }
            
            .sidebar-container.collapsed .sidebar-link.text-white span {
                color: #D4AF37 !important;
            }
            
            .sidebar-container.collapsed .sidebar-link.text-white path {
                fill: #D4AF37 !important;
                stroke: #D4AF37 !important;
            }
            
            .sidebar-container.collapsed .sidebar-profile-text {
                display: none;
            }
            
            .sidebar-container.collapsed .sidebar-profile-full {
                justify-content: center;
            }
            
            /* Botão toggle - quando colapsada, centralizado */
            .sidebar-container.collapsed .sidebar-toggle-btn {
                right: auto !important;
                left: 50% !important;
                transform: translateX(-50%) !important;
            }
            
            /* Botão toggle - quando aberta, à direita */
            .sidebar-container:not(.collapsed) .sidebar-toggle-btn {
                right: -12px !important;
                left: auto !important;
                transform: translateX(0) !important;
            }
            
            /* Botão toggle melhorado */
            .sidebar-toggle-btn {
                z-index: 100 !important;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4) !important;
                border: 1px solid rgba(106, 0, 0, 0.3) !important;
                background: #1E1E1E !important;
            }
            
            .sidebar-toggle-btn:hover {
                box-shadow: 0 4px 16px rgba(106, 0, 0, 0.5) !important;
                border-color: rgba(106, 0, 0, 0.6) !important;
                background: #2a2a2a !important;
            }
            
            .sidebar-container.collapsed nav {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            .sidebar-container.collapsed .space-y-2 > div {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            .sidebar-container.collapsed .mb-10 {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
        }
        
        html {
            background-color: #000000 !important;
        }
        
        .main-content .scrollable-content {
            background-color: #000000 !important;
        }
        
        body {
            background-color: #000000 !important;
            color: #FFFFFF !important;
        }
        
        .dashboard-wrapper {
            background-color: #000000 !important;
        }
        
        .sidebar-container {
            background: #161616 !important;
        }
        
        .main-content {
            background-color: #000000 !important;
        }
        
        .main-content header {
            background-color: #000000 !important;
        }
        
        /* Aplicar variáveis de tema aos botões e elementos com cores hardcoded */
        .bg-\[#161616\] {
            background-color: #161616 !important;
        }
        
        .bg-\[#1E1E1E\] {
            background-color: #1f1f1f !important;
        }
        
        .text-\[#6B7280\] {
            color: #707070 !important;
        }
        
        .text-white {
            color: #FFFFFF !important;
        }
        
        .text-\[#AAAAAA\] {
            color: #AAAAAA !important;
        }
        
        .bg-\[#D4AF37\] {
            background-color: #D4AF37 !important;
        }
        
        .text-\[#D4AF37\] {
            color: #D4AF37 !important;
        }
        
        /* Hover states */
        .hover\:bg-\[#252525\]:hover {
            background-color: #2D2D2D !important;
        }
        
        .hover\:bg-\[#2a2a2a\]:hover {
            background-color: #2D2D2D !important;
        }
        
        /* Cards de perfil do usuário */
        .bg-\[#1f1f1f\] {
            background-color: #1f1f1f !important;
        }
    </style>

    <!-- PWA Padrão da Plataforma -->
    <?php echo $__env->make('components.pwa-head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</head>
<body class="font-sans antialiased" style="background-color: #000000; color: #FFFFFF;" x-data="{ sidebarOpen: false, sidebarCollapsed: false }">
    <div class="dashboard-wrapper flex h-screen w-full overflow-hidden">
        <?php
            $user = auth()->user();
            $verification = $user->documentVerification;
            // Mostrar documentos apenas se NÃO foram enviados
            // Se foi enviado (submitted_at não é null), ocultar
            $showDocuments = true;
            if ($verification && $verification->submitted_at) {
                $showDocuments = false;
            }
        ?>
        
        <!-- Sidebar Overlay (Mobile) -->
    
        
        <!-- Sidebar -->
        <aside x-bind:class="{ 'open': sidebarOpen }" class="sidebar-container flex flex-col transition-all duration-300 ease-in-out top-0 left-0 z-50 relative mt-5 ml-5 mb-5 rounded-t-[20px] w-[266px]" style="background-color: #161616;">
            <div class="flex flex-col h-full pt-10 pb-5">
                <div class="px-5 mb-6 relative flex-shrink-0">
                    <div class="flex items-center justify-center mb-4 relative">
                        <img alt="<?php echo e(config('app.name')); ?>" loading="lazy" width="280" height="68" decoding="async" data-nimg="1" class="h-[68px] w-[280px]" src="<?php echo e($whiteLabelLogo ?? '/images/playpayments-logo-top.webp'); ?>" style="color: transparent;">
                        <button @click="sidebarOpen = false" class="lg:hidden absolute flex items-center justify-center rounded-full transition-all duration-300 ease-in-out h-8 w-8 right-0 bg-[#1E1E1E] hover:bg-[#2a2a2a]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x h-5 w-5 text-white">
                                <path d="M18 6L6 18M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <nav id="sidebar-nav" class="flex-1 overflow-y-auto overflow-x-hidden space-y-6 px-0 scrollbar-none [&::-webkit-scrollbar]:hidden">
                
                    <!-- Principal -->
                    <div id="section-principal" class="space-y-2">
                        <h3 class="px-5 text-sm font-semibold tracking-[-0.28px] text-[#6B7280]">Principal</h3>
                        <div class="space-y-1">
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('dashboard') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('dashboard')); ?>">
                                        <?php if(request()->routeIs('dashboard')): ?>
                                        <div class="absolute top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]" style="left: -20px;"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('dashboard') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.557 2.75H4.682A1.93 1.93 0 0 0 2.75 4.682v3.875a1.94 1.94 0 0 0 1.932 1.942h3.875a1.94 1.94 0 0 0 1.942-1.942V4.682A1.94 1.94 0 0 0 8.557 2.75m10.761 0h-3.875a1.94 1.94 0 0 0-1.942 1.932v3.875a1.943 1.943 0 0 0 1.942 1.942h3.875a1.94 1.94 0 0 0 1.932-1.942V4.682a1.93 1.93 0 0 0-1.932-1.932m0 10.75h-3.875a1.94 1.94 0 0 0-1.942 1.933v3.875a1.94 1.94 0 0 0 1.942 1.942h3.875a1.94 1.94 0 0 0 1.932-1.942v-3.875a1.93 1.93 0 0 0-1.932-1.932M8.557 13.5H4.682a1.943 1.943 0 0 0-1.932 1.943v3.875a1.93 1.93 0 0 0 1.932 1.932h3.875a1.94 1.94 0 0 0 1.942-1.932v-3.875a1.94 1.94 0 0 0-1.942-1.942"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('dashboard') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Dashboard</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financeiro -->
                    <?php if (\Illuminate\Support\Facades\Blade::check('documentsApproved')): ?>
                    <div id="section-financeiro" class="space-y-2">
                        <h3 class="px-5 text-sm font-semibold tracking-[-0.28px] text-[#6B7280]">Financeiro</h3>
                        <div class="space-y-1">
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('wallet.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('wallet.index')); ?>">
                                        <?php if(request()->routeIs('wallet.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 48 48" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('wallet.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" fill-rule="evenodd" d="M24 4.5C13.23 4.5 4.5 13.23 4.5 24S13.23 43.5 24 43.5S43.5 34.77 43.5 24S34.77 4.5 24 4.5M.5 24C.5 11.021 11.021.5 24 .5S47.5 11.021 47.5 24S36.98 47.5 24 47.5S.5 36.98.5 24M24 8.974c8.299 0 15.026 6.728 15.026 15.026S32.3 39.026 24 39.026S8.975 32.299 8.975 24S15.702 8.974 24 8.974M26 15a2 2 0 1 0-4 0v.834c-.8.214-1.56.553-2.219 1.001c-1.179.802-2.281 2.148-2.281 3.95c0 .92.232 1.77.717 2.507c.474.72 1.11 1.206 1.733 1.545c1.112.605 2.493.886 3.517 1.094l.133.028c1.215.248 2.011.429 2.538.715c.22.12.288.207.306.234v.001c.01.013.056.083.056.304c0 .036-.023.297-.531.642c-.494.337-1.223.573-1.969.573c-2.25 0-3.003-.708-3.068-.775a2 2 0 1 0-2.864 2.793c.717.735 1.969 1.507 3.932 1.828V33a2 2 0 1 0 4 0v-.838c.8-.213 1.56-.552 2.219-1c1.179-.802 2.281-2.149 2.281-3.95c0-.92-.232-1.771-.717-2.507c-.474-.72-1.11-1.207-1.733-1.545c-1.112-.606-2.493-.886-3.517-1.095l-.133-.027c-1.215-.248-2.011-.429-2.538-.715c-.22-.12-.288-.207-.306-.234v-.001c-.01-.013-.056-.083-.056-.304c0-.036.023-.297.531-.643c.483-.328 1.188-.56 1.915-.571h.112a6.2 6.2 0 0 1 3.074.867q.108.067.148.096l.02.013a2 2 0 0 0 2.415-3.187l-1.18 1.541l1.18-1.542l-.002-.001l-.002-.002l-.006-.004l-.013-.01l-.035-.026l-.102-.073a7 7 0 0 0-.334-.216a10 10 0 0 0-1.157-.6A10.3 10.3 0 0 0 26 15.773z" clip-rule="evenodd"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('wallet.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Carteira</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('transactions.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('transactions.index')); ?>">
                                        <?php if(request()->routeIs('transactions.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 20 20" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('transactions.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" fill-rule="evenodd" d="M2.24 6.8a.75.75 0 0 0 1.06-.04l1.95-2.1v8.59a.75.75 0 0 0 1.5 0V4.66l1.95 2.1a.75.75 0 1 0 1.1-1.02l-3.25-3.5a.75.75 0 0 0-1.1 0L2.2 5.74a.75.75 0 0 0 .04 1.06m8 6.4a.75.75 0 0 0-.04 1.06l3.25 3.5a.75.75 0 0 0 1.1 0l3.25-3.5a.75.75 0 1 0-1.1-1.02l-1.95 2.1V6.75a.75.75 0 0 0-1.5 0v8.59l-1.95-2.1a.75.75 0 0 0-1.06-.04" clip-rule="evenodd"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('transactions.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Transações</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('refunds.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('refunds.index')); ?>">
                                        <?php if(request()->routeIs('refunds.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('refunds.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill-rule="evenodd"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('refunds.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Infrações</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Gestão -->
                    <?php if (\Illuminate\Support\Facades\Blade::check('documentsApproved')): ?>
                    <div id="section-gestao" class="space-y-2">
                        <h3 class="px-5 text-sm font-semibold tracking-[-0.28px] text-[#6B7280]">Gestão</h3>
                        <div class="space-y-1">
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('revenues.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('revenues.index')); ?>">
                                        <?php if(request()->routeIs('revenues.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('revenues.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M12 4c4.411 0 8 3.589 8 8s-3.589 8-8 8s-8-3.589-8-8s3.589-8 8-8m0-2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2m4 8a4 4 0 0 0-8 0h2c0-1.103.897-2 2-2s2 .897 2 2s-.897 2-2 2a1 1 0 0 0-1 1v2h2v-1.141A3.99 3.99 0 0 0 16 10m-3 6h-2v2h2z"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('revenues.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Extrato</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('customers.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('customers.index')); ?>">
                                        <?php if(request()->routeIs('customers.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('customers.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <g fill="none" fill-rule="evenodd">
                                                    <path d="m12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z"></path>
                                                    <path fill="currentColor" d="M13 13a4 4 0 0 1 4 4v2a1 1 0 1 1-2 0v-2a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v2a1 1 0 1 1-2 0v-2a4 4 0 0 1 4-4zm6 0a3 3 0 0 1 3 3v2a1 1 0 1 1-2 0v-2a1 1 0 0 0-1-1h-1.416a5 5 0 0 0-1.583-2zM9.5 3a4.5 4.5 0 1 1 0 9a4.5 4.5 0 0 1 0-9M18 6a3 3 0 1 1 0 6a3 3 0 0 1 0-6M9.5 5a2.5 2.5 0 1 0 0 5a2.5 2.5 0 0 0 0-5M18 8a1 1 0 1 0 0 2a1 1 0 0 0 0-2"></path>
                                                </g>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('customers.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Clientes</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('payment-links.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('payment-links.index')); ?>">
                                        <?php if(request()->routeIs('payment-links.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('payment-links.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M13.828 10.172a4 4 0 0 0-5.656 0l-4 4a4 4 0 1 0 5.656 5.656l1.102-1.101m-.758-4.899a4 4 0 0 0 5.656 0l4-4a4 4 0 0 0-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('payment-links.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Links de Pagamento</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('referrals.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('referrals.index')); ?>">
                                        <?php if(request()->routeIs('referrals.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('referrals.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2m-2 15l-5-5l1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('referrals.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Comissões</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('premiacoes') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('premiacoes')); ?>">
                                        <?php if(request()->routeIs('premiacoes')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('premiacoes') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('premiacoes') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Premiações</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Integrações -->
                    <?php if (\Illuminate\Support\Facades\Blade::check('documentsApproved')): ?>
                    <div id="section-integracoes" class="space-y-2">
                        <h3 class="px-5 text-sm font-semibold tracking-[-0.28px] text-[#6B7280]">Integrações</h3>
                        <div class="space-y-1">
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('integracoes') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('integracoes')); ?>">
                                        <?php if(request()->routeIs('integracoes')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('integracoes') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M19 2a3 3 0 1 1 0 6a3 3 0 0 1-1.523-.419l-2.896 2.896a2.98 2.98 0 0 1 0 3.044l2.896 2.897A3 3 0 0 1 19 16a3 3 0 1 1-2.583 1.479l-2.897-2.897a2.98 2.98 0 0 1-3.043 0L7.58 17.476c.264.447.419.966.419 1.523a3 3 0 1 1-3-3c.556 0 1.074.154 1.52.417l2.897-2.898a2.98 2.98 0 0 1 0-3.04L6.521 7.582A3 3 0 0 1 5 8a3 3 0 1 1 3-3a3 3 0 0 1-.419 1.521l2.896 2.897a2.98 2.98 0 0 1 3.043-.001l2.897-2.896A3 3 0 0 1 16 5a3 3 0 0 1 3-3M5 17.5a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3m14 0a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3m-7-7a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3m-7-7a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3m14 0a1.5 1.5 0 1 0 0 3a1.5 1.5 0 0 0 0-3"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('integracoes') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Integrações</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('api-key') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('api-key')); ?>">
                                        <?php if(request()->routeIs('api-key')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('api-key') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M7 14q-.825 0-1.412-.587T5 12t.588-1.412T7 10t1.413.588T9 12t-.587 1.413T7 14m0 4q-2.5 0-4.25-1.75T1 12t1.75-4.25T7 6q1.675 0 3.038.825T12.2 9H21l3 3l-4.5 4.5l-2-1.5l-2 1.5l-2.125-1.5H12.2q-.8 1.35-2.162 2.175T7 18m0-2q1.4 0 2.463-.85T10.875 13H14l1.45 1.025L17.5 12.5l1.775 1.375L21.15 12l-1-1h-9.275q-.35-1.3-1.412-2.15T7 8Q5.35 8 4.175 9.175T3 12t1.175 2.825T7 16"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('api-key') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Chave de API</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('webhooks.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('webhooks.index')); ?>">
                                        <?php if(request()->routeIs('webhooks.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('webhooks.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M7 21q-2.075 0-3.537-1.463T2 16q0-1.4.675-2.537t1.8-1.788q.525-.3 1.025.013t.5.862q0 .275-.112.5t-.313.325q-.7.375-1.137 1.075T4 16q0 1.25.875 2.125T7 19t2.125-.875T10 16q0-.425.238-.712T10.9 15h4.975q.2-.225.488-.363T17 14.5q.625 0 1.063.438T18.5 16t-.437 1.063T17 17.5q-.35 0-.638-.137T15.876 17H11.9q-.35 1.725-1.713 2.863T7 21m0-3.5q-.625 0-1.062-.437T5.5 16q0-.55.35-.95t.85-.525l2.35-3.9q-.725-.675-1.138-1.612T7.5 7q0-2.075 1.463-3.537T12.5 2q1.75 0 3.088 1.063T17.35 5.75q.125.475-.175.863t-.8.387q-.325 0-.612-.238t-.388-.587q-.275-.95-1.05-1.562T12.5 4q-1.25 0-2.125.875T9.5 7q0 .825.413 1.513T10.974 9.6q.35.2.438.5t-.088.6l-2.9 4.85q.05.125.063.225T8.5 16q0 .625-.437 1.063T7 17.5M17 21q-.65 0-1.263-.162T14.6 20.4q-.675-.375-.537-1.137t1.012-.763q.125 0 .275.05t.275.125q.325.175.663.25T17 19q1.25 0 2.125-.875T20 16t-.875-2.125T17 13q-.25 0-.475.038t-.45.112q-.4.125-.75.013t-.525-.388l-2.575-4.3q-.525-.1-.875-.5T11 7q0-.625.438-1.062T12.5 5.5t1.063.438T14 7v.213q0 .087-.05.212l2.175 3.65q.2-.05.425-.062T17 11q2.075 0 3.538 1.463T22 16t-1.463 3.538T17 21"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('webhooks.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Webhooks</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Admin Center (Only for Admins/Managers) -->
                    <?php if(auth()->user()->isAdminOrManager()): ?>
                    <div id="section-admin" class="space-y-2">
                        <h3 class="px-5 text-sm font-semibold tracking-[-0.28px] text-[#D4AF37]">Administração</h3>
                        <div class="space-y-1">
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->is('admin*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
                                        <?php if(request()->is('admin*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->is('admin*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="currentColor" d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12c5.16-1.26 9-6.45 9-12V5l-9-4m0 6a3 3 0 1 1-3 3a3 3 0 0 1 3-3m0 12c-2.7 0-5.8-1.28-6-3c.03-2.12 3.89-3 6-3c2.1 0 5.97.88 6 3c-.2 1.72-3.3 3-6 3Z"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->is('admin*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Painel Admin</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                            
                    <!-- Sistema -->
                    <div id="section-sistema" class="space-y-2">
                        <h3 class="px-5 text-sm font-semibold tracking-[-0.28px] text-[#6B7280]">Sistema</h3>
                        <div class="space-y-1">
                            <div>
                                <div class="px-5">
                                    <?php
                                        $isSettingsActive = request()->routeIs('settings.index') || request()->routeIs('settings.*');
                                    ?>
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e($isSettingsActive ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('settings.index')); ?>">
                                        <?php if($isSettingsActive): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e($isSettingsActive ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 5h-3m-4.25-2v4M13 5H3m4 7H3m7.75-2v4M21 12H11m10 7h-3m-4.25-2v4M13 19H3"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e($isSettingsActive ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Configurações</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <?php if($showDocuments): ?>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 <?php echo e(request()->routeIs('documents.*') ? 'text-white' : 'text-[#6B7280]'); ?>" href="<?php echo e(route('documents.index')); ?>">
                                        <?php if(request()->routeIs('documents.*')): ?>
                                        <div class="absolute -left-5 top-1/2 transform -translate-y-1/2 bg-[#D4AF37] w-1.5 h-[26px] rounded-br-[4px] rounded-tr-[4px]"></div>
                                        <?php endif; ?>
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 <?php echo e(request()->routeIs('documents.*') ? 'text-[#D4AF37]' : 'text-[#6B7280]'); ?>">
                                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] <?php echo e(request()->routeIs('documents.*') ? 'text-white' : 'text-[#6B7280]'); ?>" style="font-family: Manrope, sans-serif;">Documentos</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <div>
                                <div class="px-5">
                                    <a class="relative flex items-center w-full transition-all duration-200 py-2 text-[#6B7280] cursor-not-allowed" href="#" onclick="return false;">
                                        <div class="flex items-center gap-2.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="h-5 w-5 flex-shrink-0 text-[#6B7280]">
                                                <path fill="currentColor" d="M12 4c4.411 0 8 3.589 8 8s-3.589 8-8 8s-8-3.589-8-8s3.589-8 8-8m0-2C6.477 2 2 6.477 2 12s4.477 10 10 10s10-4.477 10-10S17.523 2 12 2m4 8a4 4 0 0 0-8 0h2c0-1.103.897-2 2-2s2 .897 2 2s-.897 2-2 2a1 1 0 0 0-1 1v2h2v-1.141A3.99 3.99 0 0 0 16 10m-3 6h-2v2h2z"></path>
                                            </svg>
                                            <div class="flex items-center gap-2 whitespace-nowrap min-w-0">
                                                <span class="text-sm font-semibold tracking-[-0.28px] text-[#6B7280]" style="font-family: Manrope, sans-serif;">Ajuda</span>
                                                <span class="px-1.5 py-0.5 text-[10px] font-medium bg-[#D4AF37] text-white rounded-full">Em Breve</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <div class="w-full px-2 mt-auto pb-2">
                    <div class="flex flex-col gap-5 p-4 rounded-[6px] bg-[#1f1f1f]">
                        <div class="flex items-center gap-3">
                            <div class="relative w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                                <?php
                                    // Verificar se a foto existe na nova pasta pública
                                    $hasPhoto = $user->photo && file_exists(public_path('images/users/photos/' . $user->photo));
                                    $photoUrl = null;
                                    if ($hasPhoto) {
                                        $photoUrl = url('/images/users/photos/' . $user->photo);
                                    }
                                    $userInitials = strtoupper(substr($user->name, 0, 2));
                                ?>
                                <?php if($hasPhoto && $photoUrl): ?>
                                    <img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($user->name); ?>" class="w-full h-full object-cover sidebar-user-photo" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-full h-full hidden items-center justify-center bg-[#D4AF37] text-white text-xs font-semibold">
                                        <?php echo e($userInitials); ?>

                                    </div>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center bg-[#D4AF37] text-white text-xs font-semibold">
                                        <?php echo e($userInitials); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-semibold tracking-[-0.28px] truncate text-white" style="font-family: Manrope, sans-serif;"><?php echo e($user->fantasy_name ?? $user->name); ?></h2>
                                <p class="text-sm tracking-[-0.28px] truncate text-[#6B7280]" style="font-family: Manrope, sans-serif;"><?php echo e($user->email); ?></p>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="flex items-center gap-2.5 cursor-pointer hover:opacity-80 transition-opacity text-[#6B7280] w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out h-5 w-5">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16 17 21 12 16 7"></polyline>
                                    <line x1="21" x2="9" y1="12" y2="12"></line>
                                </svg>
                                <span class="text-sm font-semibold tracking-[-0.28px]" style="font-family: Manrope, sans-serif;">Sair</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
            <main class="main-content flex-1 flex flex-col overflow-hidden" style="background-color: #000000;">
            <!-- Header -->
            <header class="w-full sticky top-0 z-40 transition-all duration-300 flex-shrink-0 backdrop-blur-md border-b border-white/5" 
                    style="background-color: rgba(0, 0, 0, 0.8); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);">
                <!-- Desktop Header -->
                <div class="hidden lg:flex container mx-auto px-4 md:px-6 items-center justify-between h-[80px]">
                    <!-- Right Content (Goals & Settings) -->
                    <div class="flex items-center justify-end gap-6 ml-auto">
                        <?php if(isset($goals) && $goals->count() > 0): ?>
                            <?php
                                $currentGoal = $goals->first();
                            ?>
                            <?php if($currentGoal): ?>
                                <!-- Goal Card -->
                                <div class="group flex items-center gap-4 px-5 py-2 rounded-2xl transition-all duration-300 hover:bg-white/5" style="align-self: center;">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-[#D4AF37] blur-md opacity-20 group-hover:opacity-40 transition-opacity"></div>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trophy h-6 w-6 text-[#D4AF37] relative z-10 animate-pulse">
                                            <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path>
                                            <path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path>
                                            <path d="M4 22h16"></path>
                                            <path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"></path>
                                            <path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"></path>
                                            <path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex flex-col gap-2 justify-center min-w-[240px]">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-[10px] uppercase font-bold tracking-widest text-gray-500 font-['JetBrains Mono']">Meta Atual</span>
                                            <span class="text-[13px] font-bold text-[#D4AF37] shadow-[#D4AF37]/20 drop-shadow-sm"><?php echo e(number_format($currentGoal['percentage'], 1)); ?>%</span>
                                        </div>
                                        <div class="relative h-2 w-full bg-white/5 rounded-full overflow-hidden border border-white/5">
                                            <div class="absolute top-0 left-0 h-full bg-gradient-to-r from-[#8a6d1d] via-[#D4AF37] to-[#f5de8a] rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(212,175,55,0.4)]" 
                                                 style="width: <?php echo e(min(100, $currentGoal['percentage'])); ?>%;">
                                                <div class="absolute inset-0 w-full h-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between items-center text-[11px] font-medium font-['Inter']">
                                            <span class="text-white/70">R$ <?php echo e(number_format($currentGoal['current_value'], 2, ',', '.')); ?></span>
                                            <span class="text-white/40">R$ <?php echo e(number_format($currentGoal['target_value'], 2, ',', '.')); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="hidden lg:block h-8 w-[1px] bg-gradient-to-b from-transparent via-white/10 to-transparent" style="align-self: center;"></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <!-- Settings Button -->
                        <a class="group relative flex items-center justify-center rounded-xl h-12 w-12 bg-white/5 hover:bg-[#D4AF37] transition-all duration-500 overflow-hidden" 
                           href="<?php echo e(route('settings.index')); ?>" style="align-self: center;">
                            <div class="absolute inset-0 bg-[#D4AF37] opacity-0 group-hover:opacity-20 blur-xl transition-all"></div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                 class="lucide lucide-settings h-5 w-5 text-gray-400 group-hover:text-white group-hover:rotate-90 transition-all duration-500">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Mobile Header -->
                <div class="lg:hidden flex flex-col w-full px-4 py-4 backdrop-blur-xl border-b border-white/5" style="background: rgba(0,0,0,0.5);">
                    <div class="flex items-center justify-between gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="active:scale-95 flex items-center justify-center w-11 h-11 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-white transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu h-6 w-6">
                                <line x1="4" x2="20" y1="12" y2="12"></line>
                                <line x1="4" x2="20" y1="6" y2="6"></line>
                                <line x1="4" x2="20" y1="18" y2="18"></line>
                            </svg>
                        </button>
                        
                        <!-- Meta Mobile -->
                        <?php if(isset($goals) && $goals->count() > 0): ?>
                            <?php $currentGoal = $goals->first(); ?>
                            <?php if($currentGoal): ?>
                                <div class="flex flex-col flex-1 gap-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-[#D4AF37]"><?php echo e(number_format($currentGoal['percentage'], 0)); ?>%</span>
                                        <span class="text-[9px] text-gray-500 font-mono">META ATIVA</span>
                                    </div>
                                    <div class="relative h-1.5 w-full bg-white/5 rounded-full overflow-hidden">
                                        <div class="absolute top-0 left-0 h-full bg-[#D4AF37] shadow-[0_0_10px_rgba(212,175,55,0.4)]" style="width: <?php echo e(min(100, $currentGoal['percentage'])); ?>%;"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <a class="active:scale-95 flex items-center justify-center rounded-xl h-11 w-11 bg-white/5 border border-white/10" href="<?php echo e(route('settings.index')); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings text-gray-400">
                                <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </a>
                    </div>
                </div>
            </header>
            
            <style>
                @keyframes shimmer {
                    0% { transform: translateX(-100%); }
                    100% { transform: translateX(100%); }
                }
            </style>
            

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden scrollable-content w-full" style="background-color: #000000;">
                <?php echo $__env->yieldContent('content'); ?>
                <div class="dock-spacer"></div>
            </div>
        </main>
    </div>


    <?php echo $__env->yieldPushContent('scripts'); ?>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.1/qrcode.min.js"></script>
    
    <!-- Auth Token Handler for Iframe Compatibility -->
    <script>
    (function() {
        // Get auth token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const authToken = urlParams.get('auth_token');
        
        // Store token in localStorage if present
        if (authToken) {
            localStorage.setItem('auth_token', authToken);
            
            // Remove token from URL for cleaner appearance
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        // Get stored token
        const storedToken = localStorage.getItem('auth_token');
        
        // Add token to all internal links
        if (storedToken) {
            document.addEventListener('DOMContentLoaded', function() {
                // Intercept all internal links
                document.addEventListener('click', function(e) {
                    const link = e.target.closest('a');
                    if (link && link.hostname === window.location.hostname) {
                        const href = link.getAttribute('href');
                        if (href && !href.includes('auth_token=') && !href.startsWith('#')) {
                            e.preventDefault();
                            const separator = href.includes('?') ? '&' : '?';
                            window.location.href = href + separator + 'auth_token=' + storedToken;
                        }
                    }
                });
                
                // Intercept all forms
                document.addEventListener('submit', function(e) {
                    const form = e.target;
                    if (form.method.toLowerCase() === 'get') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'auth_token';
                        input.value = storedToken;
                        form.appendChild(input);
                    }
                });
            });
            
            // Helper function para fazer requisições fetch com token
            window.makeRequest = function(url, options = {}) {
                const headers = options.headers || {};
                headers['X-Auth-Token'] = storedToken;
                headers['X-Requested-With'] = 'XMLHttpRequest';
                headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                headers['Accept'] = headers['Accept'] || 'application/json';
                
                // Adicionar CSRF token se disponível
                const csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
                }
                
                return fetch(url, {
                    ...options,
                    headers: headers
                });
            };
        }
    })();
    
    // Função para scroll suave até as seções da sidebar
    function scrollToSection(sectionId) {
        const nav = document.getElementById('sidebar-nav');
        const section = document.getElementById(sectionId);
        
        if (nav && section) {
            // Calcular a posição relativa da seção dentro do nav
            const navRect = nav.getBoundingClientRect();
            const sectionRect = section.getBoundingClientRect();
            const scrollTop = nav.scrollTop;
            const targetPosition = scrollTop + (sectionRect.top - navRect.top) - 20; // 20px de offset
            
            // Scroll suave
            nav.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    }
    </script>

    <?php echo $__env->make('components.MacosDock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <!-- Modal Popup Instalação App -->
    <?php echo $__env->make('components.pwa-prompt', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/layouts/dashboard.blade.php ENDPATH**/ ?>