<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'PixBolt')); ?> - <?php echo $__env->yieldContent('title', 'Gateway de Pagamento'); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="alternate icon" href="/favicon.ico" type="image/x-icon">

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
    <link rel="preload" href="/images/brpix.png" as="image" type="image/png">
    <?php if(Route::currentRouteName() == 'login' || Route::currentRouteName() == 'register'): ?>
        <link rel="preload" href="/images/bg-brpix.png" as="image" type="image/png">
        <link rel="preload" href="/images/brpix-logo-top.webp" as="image" type="image/webp">
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/css/app.css" type="text/css" media="all">
    
    <!-- Stack de estilos adicionais -->
    <?php echo $__env->yieldPushContent('styles'); ?>
    
    <!-- App JavaScript -->
    <script src="/js/app.js" defer></script>
    
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
    
    <!-- Auth Token Handler for Iframe Compatibility -->
    <script src="/js/auth-token-handler.js"></script>
</body>
</html><?php /**PATH /home/u999974013/domains/playpayments.com/public_html/resources/views/layouts/app.blade.php ENDPATH**/ ?>