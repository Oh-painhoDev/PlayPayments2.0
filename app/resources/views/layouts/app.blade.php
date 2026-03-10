<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoMeta['title'] ?? config('app.name', '$playpayments') }} - @yield('title', 'Gateway de Pagamento')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $seoMeta['description'] ?? '$playpayments - Gateway de Pagamento PIX' }}">
    <meta name="keywords" content="{{ $seoMeta['keywords'] ?? 'playpayments, gateway pagamento, pix' }}">
    <meta name="author" content="{{ $seoMeta['author'] ?? '$playpayments' }}">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoMeta['og_type'] ?? 'website' }}">
    <meta property="og:url" content="{{ $seoMeta['og_url'] ?? url()->current() }}">
    <meta property="og:title" content="{{ $seoMeta['og_title'] ?? '$playpayments - Gateway de Pagamento PIX' }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? 'Plataforma completa de gateway de pagamento PIX' }}">
    <meta property="og:image" content="{{ $seoMeta['og_image'] ?? asset('images/playpayments-logo-top.webp') }}">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="{{ $seoMeta['twitter_card'] ?? 'summary_large_image' }}">
    <meta name="twitter:title" content="{{ $seoMeta['og_title'] ?? '$playpayments - Gateway de Pagamento PIX' }}">
    <meta name="twitter:description" content="{{ $seoMeta['og_description'] ?? 'Plataforma completa de gateway de pagamento PIX' }}">
    <meta name="twitter:image" content="{{ $seoMeta['og_image'] ?? asset('images/playpayments-logo-top.webp') }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ $whiteLabelFavicon ?? asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ $whiteLabelFaviconIco ?? asset('favicon.ico') }}" type="image/x-icon">

    <!-- Theme Configuration -->
    <meta name="theme-background" content="{{ $themeVars['theme_background'] ?? '#0d0d0d' }}">
    <meta name="theme-card-bg" content="{{ $themeVars['theme_card_bg'] ?? '#1a1a1a' }}">
    <meta name="theme-sidebar-bg" content="{{ $themeVars['theme_sidebar_bg'] ?? '#0f0f0f' }}">
    <meta name="theme-header-bg" content="{{ $themeVars['theme_header_bg'] ?? '#0f0f0f' }}">
    <meta name="theme-border" content="{{ $themeVars['theme_border'] ?? '#2c2c2e' }}">
    <meta name="theme-text" content="{{ $themeVars['theme_text'] ?? '#f4f4f5' }}">
    <meta name="theme-text-secondary" content="{{ $themeVars['theme_text_secondary'] ?? '#a1a1aa' }}">
    <meta name="theme-primary" content="{{ $themeVars['theme_primary'] ?? '#10b981' }}">
    <meta name="theme-success" content="{{ $themeVars['theme_success'] ?? '#22c55e' }}">
    <meta name="theme-warning" content="{{ $themeVars['theme_warning'] ?? '#eab308' }}">
    <meta name="theme-danger" content="{{ $themeVars['theme_danger'] ?? '#10b981' }}">
    <meta name="theme-info" content="{{ $themeVars['theme_info'] ?? '#10b981' }}">

    <!-- Fonts - Otimizado com display=swap para melhor performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Preload Critical Resources - Otimizado -->
    <link rel="preload" href="{{ asset('images/playpayments-logo-top.webp') }}" as="image" type="image/webp">
    @if(Route::currentRouteName() == 'login' || Route::currentRouteName() == 'register')
        <link rel="preload" href="{{ asset('images/playpayments-logo-top.webp') }}" as="image" type="image/webp">
    @endif
    
    <!-- DNS Prefetch para recursos externos -->
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    
    <!-- Preload de CSS e JS críticos -->
    @if (file_exists(public_path('build/manifest.json')))
        @php
            $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            $jsFile = $manifest['resources/js/app.js']['file'] ?? null;
        @endphp
        @if($cssFile)
            <link rel="preload" href="/build/{{ $cssFile }}" as="style">
        @endif
        @if($jsFile)
            <link rel="preload" href="/build/{{ $jsFile }}" as="script" crossorigin>
        @endif
    @endif

    <!-- Logo Size Configuration -->
    <style>
        :root {
            --logo-auth-height: {{ env('LOGO_AUTH_SIZE', 64) }}px;
            --logo-dashboard-height: {{ env('LOGO_DASHBOARD_SIZE', 40) }}px;
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
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" type="text/css" media="all">
    
    <!-- Stack de estilos adicionais -->
    @stack('styles')
    
    <!-- App JavaScript -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
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
        @yield('content')
    </div>

    @stack('scripts')
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Auth Token Handler for Iframe Compatibility -->
    <script src="{{ asset('js/auth-token-handler.js') }}"></script>
    @include('components.MacosDock')
</body>
</html>