<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e($seoMeta['title'] ?? config('app.name', '$playpayments')); ?> - <?php echo $__env->yieldContent('title', 'Gateway de Pagamento'); ?></title>

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
    <meta name="theme-header-bg" content="<?php echo e($themeVars['theme_header_bg'] ?? '#0f0f0f'); ?>">
    <meta name="theme-border" content="<?php echo e($themeVars['theme_border'] ?? '#2c2c2e'); ?>">
    <meta name="theme-text" content="<?php echo e($themeVars['theme_text'] ?? '#f4f4f5'); ?>">
    <meta name="theme-text-secondary" content="<?php echo e($themeVars['theme_text_secondary'] ?? '#a1a1aa'); ?>">
    <meta name="theme-primary" content="<?php echo e($themeVars['theme_primary'] ?? '#10b981'); ?>">
    <meta name="theme-success" content="<?php echo e($themeVars['theme_success'] ?? '#22c55e'); ?>">
    <meta name="theme-warning" content="<?php echo e($themeVars['theme_warning'] ?? '#eab308'); ?>">
    <meta name="theme-danger" content="<?php echo e($themeVars['theme_danger'] ?? '#10b981'); ?>">
    <meta name="theme-info" content="<?php echo e($themeVars['theme_info'] ?? '#10b981'); ?>">

    <!-- Fonts - Otimizado com display=swap para melhor performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Preload Critical Resources - Otimizado -->
    <link rel="preload" href="<?php echo e(asset('images/playpayments-logo-top.webp')); ?>" as="image" type="image/webp">
    <?php if(Route::currentRouteName() == 'login' || Route::currentRouteName() == 'register'): ?>
        <link rel="preload" href="<?php echo e(asset('images/playpayments-logo-top.webp')); ?>" as="image" type="image/webp">
    <?php endif; ?>
    
    <!-- DNS Prefetch para recursos externos -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <!-- Preload de CSS e JS críticos -->
    <?php if(file_exists(public_path('build/manifest.json'))): ?>
        <?php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        ?>
        <?php if($cssFile): ?>
            <link rel="preload" href="/build/<?php echo e($cssFile); ?>" as="style">
        <?php endif; ?>
        <?php if($jsFile): ?>
            <link rel="preload" href="/build/<?php echo e($jsFile); ?>" as="script" crossorigin>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Logo Size Configuration -->
    <style>
        :root {
            --logo-auth-height: <?php echo e(env('LOGO_AUTH_SIZE', 64)); ?>px;
            --logo-dashboard-height: <?php echo e(env('LOGO_DASHBOARD_SIZE', 40)); ?>px;
        }
    </style>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>" type="text/css" media="all">
    
    <!-- Stack de estilos adicionais -->
    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <!-- App JavaScript -->
    <script src="<?php echo e(asset('js/app.js')); ?>" defer></script>
    
    <!-- Garantir fundo visível mesmo sem CSS -->
    <style>
        body {
            background-color: #ffffff !important;
            color: #111827 !important;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-900">
    <div class="min-h-screen flex flex-col">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auth Token Handler for Iframe Compatibility -->
    <script src="<?php echo e(asset('js/auth-token-handler.js')); ?>"></script>
    <?php echo $__env->make('components.MacosDock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH /home/painhodev/PlayPayments2.0/app/resources/views/layouts/app.blade.php ENDPATH**/ ?>