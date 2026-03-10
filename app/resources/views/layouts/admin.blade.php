@php
    // Contar saques pendentes para aprovação
    $pendingWithdrawalsCount = \App\Models\Withdrawal::where('status', 'pending')->count();
    
    // Contar solicitações de gateway pendentes
    $pendingGatewayRequestsCount = \App\Models\DocumentVerification::where('status', 'pendente')
        ->whereNotNull('submitted_at')
        ->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seoMeta['title'] ?? config('app.name', '$playpayments') }} - @yield('title', 'Admin')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $seoMeta['description'] ?? '$playpayments - Gateway de Pagamento PIX' }}">
    <meta name="keywords" content="{{ $seoMeta['keywords'] ?? 'playpayments, playpayments, gateway pagamento, pix' }}">
    <meta name="author" content="{{ $seoMeta['author'] ?? 'playpayments' }}">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="{{ $seoMeta['og_type'] ?? 'website' }}">
    <meta property="og:url" content="{{ $seoMeta['og_url'] ?? url()->current() }}">
    <meta property="og:title" content="{{ $seoMeta['og_title'] ?? 'playpayments - Gateway de Pagamento PIX' }}">
    <meta property="og:description" content="{{ $seoMeta['og_description'] ?? 'Plataforma completa de gateway de pagamento PIX' }}">
    <meta property="og:image" content="{{ $seoMeta['og_image'] ?? asset('images/playpayments-logo-top.webp') }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ $whiteLabelFavicon ?? asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ $whiteLabelFaviconIco ?? asset('favicon.ico') }}" type="image/x-icon">

    <!-- Theme Configuration -->
    <meta name="theme-background" content="{{ $themeVars['theme_background'] ?? '#0d0d0d' }}">
    <meta name="theme-card-bg" content="{{ $themeVars['theme_card_bg'] ?? '#1a1a1a' }}">
    <meta name="theme-sidebar-bg" content="{{ $themeVars['theme_sidebar_bg'] ?? '#0f0f0f' }}">
    <meta name="theme-header-bg" content="{{ $themeVars['theme_header_bg'] ?? '#0f0f0f' }}">
    <meta name="theme-border" content="{{ env('THEME_BORDER', '#222222') }}">
    <meta name="theme-text" content="{{ env('THEME_TEXT', '#ffffff') }}">
    <meta name="theme-text-secondary" content="{{ $themeVars['theme_text_secondary'] ?? '#a1a1aa' }}">
    <meta name="theme-primary" content="{{ env('THEME_PRIMARY', '#3b82f6') }}">
    <meta name="theme-success" content="{{ env('THEME_SUCCESS', '#10b981') }}">
    <meta name="theme-warning" content="{{ env('THEME_WARNING', '#f59e0b') }}">
    <meta name="theme-danger" content="{{ env('THEME_DANGER', '#10b981') }}">
    <meta name="theme-info" content="{{ env('THEME_INFO', '#6366f1') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    <!-- App JavaScript -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <!-- Garantir fundo visível mesmo sem CSS -->
    <style>
        body {
            background-color: #000000 !important;
            color: #ffffff !important;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        html {
            background-color: #000000 !important;
        }
    </style>
</head>
<body class="font-sans antialiased text-white overflow-hidden" style="background-color: #000000;">
    <div class="flex h-screen">
        <!-- Admin Sidebar -->
        <aside class="w-64 border-r border-gray-800 flex flex-col bg-[#161616] shadow-sm overflow-hidden">
            <!-- Logo -->
            <div class="flex flex-col items-center justify-center px-6 py-6 border-b border-gray-700 flex-shrink-0 bg-gradient-to-b from-[#1a1a1a] to-[#161616]">
                <div class="w-full flex items-center justify-center">
                    <img src="{{ $whiteLabelLogo ?? asset('images/playpayments-logo-top.webp') }}" alt="{{ config('app.name') }}" class="max-w-full max-h-28 object-contain" style="image-rendering: crisp-edges;" onerror="this.src='{{ asset('images/playpayments-logo-top.webp') }}'">
                </div>
                <span class="text-[#D4AF37] text-xs font-semibold tracking-widest mt-2 drop-shadow-lg">ADMIN PANEL</span>
            </div>

            <!-- Admin Info -->
            <div class="px-6 py-4 border-b border-gray-700 flex-shrink-0">
                <div class="mb-1">
                    <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[#D4AF37] truncate font-medium">
                        @if(auth()->user()->role === 'admin')
                            👑 Administrador
                        @elseif(auth()->user()->role === 'gerente')
                            👔 Gerente
                        @else
                            👤 Usuário
                        @endif
                    </p>
                </div>
            </div>

            <!-- Admin Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollable-content hover:scrollbar-thumb-gray-700">
                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" class="nav-item group relative {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'text-[#D4AF37] bg-[#1E1E1E] shadow-lg' : 'text-[#9CA3AF] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-150">
                    @if(request()->routeIs('admin.dashboard'))
                    <div class="absolute -left-3 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-[#D4AF37] to-[#06b6d4] w-1 h-[32px] rounded-r-full"></div>
                    @endif
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-[#D4AF37]' : 'text-[#6B7280] group-hover:text-[#D4AF37]' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Dashboard
                </a>

                <!-- System Logs -->
                <a href="{{ route('admin.system-logs.index') }}" class="nav-item group relative {{ request()->routeIs('admin.system-logs.*') ? 'active' : '' }} flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.system-logs.*') ? 'text-[#D4AF37] bg-[#1E1E1E] shadow-lg' : 'text-[#9CA3AF] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-150">
                    @if(request()->routeIs('admin.system-logs.*'))
                    <div class="absolute -left-3 top-1/2 transform -translate-y-1/2 bg-gradient-to-r from-[#D4AF37] to-[#06b6d4] w-1 h-[32px] rounded-r-full"></div>
                    @endif
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.system-logs.*') ? 'text-[#D4AF37]' : 'text-[#6B7280] group-hover:text-[#D4AF37]' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Logs
                </a>
                
                <!-- Meta Dropdown -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.goals.*') ? 'true' : 'false' }} }">
                    <button 
                        @click="open = !open"
                        class="nav-item group flex items-center justify-between w-full px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.goals.*') ? 'text-[#D4AF37] bg-[#1E1E1E]' : 'text-[#9CA3AF] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-150"
                    >
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-3 {{ request()->routeIs('admin.goals.*') ? 'text-[#D4AF37]' : 'text-[#6B7280] group-hover:text-[#D4AF37]' }} transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            Meta
                        </div>
                        <svg 
                            class="w-3 h-3 transition-transform duration-200" 
                            :class="{ 'rotate-180': open }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Meta Submenu -->
                    <div 
                        x-show="open" 
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 transform -translate-y-1"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-1"
                        class="ml-4 space-y-0.5"
                    >
                        <!-- Goals Management -->
                        <a href="{{ route('admin.goals.index') }}" class="nav-item group {{ request()->routeIs('admin.goals.*') ? 'active' : '' }} flex items-center px-3 py-2 text-xs font-medium rounded-lg {{ request()->routeIs('admin.goals.*') ? 'text-[#D4AF37] bg-[#1E1E1E]' : 'text-[#9CA3AF] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-150">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-[#6B7280] group-hover:bg-[#D4AF37] mr-2.5 transition-colors"></span>
                            Gerenciar Metas
                        </a>
                    </div>
                </div>
                
                <!-- Admin Dropdown -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.documents.*') || request()->routeIs('admin.transactions.*') || request()->routeIs('admin.withdrawals.*') || request()->routeIs('admin.billing.*') || request()->routeIs('admin.billing-period.*') || request()->routeIs('admin.profit.*') ? 'true' : 'false' }} }">
                    <button 
                        @click="open = !open"
                        class="nav-item flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.documents.*') || request()->routeIs('admin.transactions.*') || request()->routeIs('admin.withdrawals.*') || request()->routeIs('admin.billing.*') || request()->routeIs('admin.billing-period.*') || request()->routeIs('admin.profit.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200"
                    >
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            Admin
                        </div>
                        <svg 
                            class="w-3 h-3 transition-transform duration-200" 
                            :class="{ 'rotate-180': open }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Admin Submenu -->
                    <div 
                        x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="ml-4 space-y-1"
                    >
                        <!-- Users Management -->
                        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            Todos os usuários
                        </a>

                        <!-- Document Verifications -->
                        <a href="{{ route('admin.documents.index') }}" class="nav-item {{ request()->routeIs('admin.documents.*') ? 'active' : '' }} flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.documents.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Solicitações Gateway
                            </div>
                            @if($pendingGatewayRequestsCount > 0)
                                <span class="ml-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ $pendingGatewayRequestsCount > 99 ? '99+' : $pendingGatewayRequestsCount }}
                                </span>
                            @endif
                        </a>

                        <!-- Transactions -->
                        <a href="{{ route('admin.transactions.index') }}" class="nav-item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.transactions.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Todas as transações
                        </a>

                        <!-- Withdrawals -->
                        <a href="{{ route('admin.withdrawals.index') }}" class="nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }} flex items-center justify-between px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.withdrawals.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                </svg>
                                Todos os saques
                            </div>
                            @if($pendingWithdrawalsCount > 0)
                                <span class="ml-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                                    {{ $pendingWithdrawalsCount > 99 ? '99+' : $pendingWithdrawalsCount }}
                                </span>
                            @endif
                        </a>
                        
                        <!-- Company Billing -->
                        <a href="{{ route('admin.billing.index') }}" class="nav-item {{ request()->routeIs('admin.billing.*') && !request()->routeIs('admin.billing-period.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.billing.*') && !request()->routeIs('admin.billing-period.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Faturamento por Emp
                        </a>
                        
                        <!-- Period Billing -->
                        <a href="{{ route('admin.billing-period.index') }}" class="nav-item {{ request()->routeIs('admin.billing-period.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.billing-period.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Faturamento por Per
                        </a>
                        
                        <!-- Profit by Company -->
                        <a href="{{ route('admin.profit.index') }}" class="nav-item {{ request()->routeIs('admin.profit.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.profit.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Lucro por Empresas
                        </a>
                    </div>
                </div>

                <!-- White Label Dropdown -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.white-label.*') || request()->routeIs('admin.gateways.*') || request()->routeIs('admin.baas.*') || request()->routeIs('admin.retry.*') || request()->routeIs('admin.multi-gateway.*') ? 'true' : 'false' }} }">
                    <button 
                        @click="open = !open"
                        class="nav-item flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.white-label.*') || request()->routeIs('admin.gateways.*') || request()->routeIs('admin.baas.*') || request()->routeIs('admin.retry.*') || request()->routeIs('admin.multi-gateway.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200"
                    >
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                            </svg>
                            White Label
                        </div>
                        <svg 
                            class="w-3 h-3 transition-transform duration-200" 
                            :class="{ 'rotate-180': open }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- White Label Submenu -->
                    <div 
                        x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="ml-4 space-y-1"
                    >
                        <!-- Gateways Management -->
                        <a href="{{ route('admin.gateways.index') }}" class="nav-item {{ request()->routeIs('admin.gateways.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.gateways.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            adquirentes
                        </a>

                        <!-- BaaS Management -->
                        <a href="{{ route('admin.baas.index') }}" class="nav-item {{ request()->routeIs('admin.baas.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.baas.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                            BaaS
                        </a>

                        <!-- Global Fees -->
                        <a href="{{ route('admin.white-label.global-fees') }}" class="nav-item {{ request()->routeIs('admin.white-label.global-fees') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.white-label.global-fees') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Taxas Globais
                        </a>

                        <!-- UTMify -->
                        <a href="{{ route('admin.white-label.utmify.index') }}" class="nav-item {{ request()->routeIs('admin.white-label.utmify.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.white-label.utmify.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            UTMify
                        </a>

                        <!-- Retry/Failover -->
                        <a href="{{ route('admin.retry.index') }}" class="nav-item {{ request()->routeIs('admin.retry.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.retry.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Retry / Failover
                        </a>

                        <!-- Multi-Gateway -->
                        <a href="{{ route('admin.multi-gateway.index') }}" class="nav-item {{ request()->routeIs('admin.multi-gateway.*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.multi-gateway.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Multi-Gateway
                        </a>

                        <!-- Branding Settings -->
                        <a href="{{ route('admin.white-label.branding') }}" class="nav-item {{ request()->routeIs('admin.white-label.branding') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.white-label.branding') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                            </svg>
                            Personalização
                        </a>

                        <!-- Announcements -->
                        <a href="{{ route('admin.white-label.announcements') }}" class="nav-item {{ request()->routeIs('admin.white-label.announcements') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.white-label.announcements') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            Avisos
                        </a>
                    </div>
                </div>

                <!-- Setup Dropdown -->
                <div class="space-y-1" x-data="{ open: {{ request()->routeIs('admin.setup.*') ? 'true' : 'false' }} }">
                    <button 
                        @click="open = !open"
                        class="nav-item flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.setup.*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200"
                    >
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Setup
                        </div>
                        <svg 
                            class="w-3 h-3 transition-transform duration-200" 
                            :class="{ 'rotate-180': open }"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Setup Submenu -->
                    <div 
                        x-show="open" 
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 transform -translate-y-2"
                        x-transition:enter-end="opacity-100 transform translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 transform translate-y-0"
                        x-transition:leave-end="opacity-0 transform -translate-y-2"
                        class="ml-4 space-y-1"
                    >
                        <!-- Retention Overview -->
                        <a href="{{ route('admin.setup.retention-overview') }}" class="nav-item {{ request()->routeIs('admin.setup.retention-overview') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.setup.retention-overview') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Visão Geral Retenções
                        </a>

                        <!-- Retained Sales -->
                        <a href="{{ route('admin.setup.retained-sales') }}" class="nav-item {{ request()->routeIs('admin.setup.retained-sales') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.setup.retained-sales') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Vendas Retidas
                        </a>

                        <!-- Infrações -->
                        <a href="{{ route('admin.setup.disputes') }}" class="nav-item {{ request()->routeIs('admin.setup.disputes*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.setup.disputes*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Infrações
                        </a>

                        <!-- Templates de Infrações -->
                        <a href="{{ route('admin.setup.dispute-templates.index') }}" class="nav-item {{ request()->routeIs('admin.setup.dispute-templates*') ? 'active' : '' }} flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.setup.dispute-templates*') ? 'text-white bg-[#1E1E1E]' : 'text-[#6B7280] hover:text-white hover:bg-[#1E1E1E]' }} transition-all duration-200">
                            <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Templates de Infrações
                        </a>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-gray-800 my-4"></div>

                <!-- Back to User Panel -->
                <a href="{{ route('dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm font-medium text-[#6B7280] hover:text-[#D4AF37] hover:bg-[#1E1E1E] rounded-lg transition-all duration-200">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Painel do Usuário
                </a>
            </nav>

            <!-- Logout -->
            <div class="px-4 py-3 border-t border-gray-800 flex-shrink-0">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-3 py-2 text-sm font-medium text-[#6B7280] hover:text-[#D4AF37] hover:bg-[#1E1E1E] rounded-lg transition-all duration-200">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Sair da Conta
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden" style="background-color: #000000;">
            <!-- Header -->
            <header class="border-b border-gray-800 flex-shrink-0 bg-[#0f0f0f] shadow-sm">
                <div class="px-6 py-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-white">@yield('page-title', 'Admin')</h1>
                            <p class="text-xs text-[#6B7280] mt-1">@yield('page-description', 'Painel administrativo')</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 bg-[#D4AF37]/20 text-[#D4AF37] text-xs font-medium rounded-full border border-[#D4AF37]/30">
                                @if(auth()->user()->role === 'admin')
                                    ADMIN
                                @elseif(auth()->user()->role === 'gerente')
                                    GERENTE
                                @else
                                    USUÁRIO
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden scrollable-content" style="background-color: #000000;">
                @yield('content')
                <div class="dock-spacer"></div>
            </div>
        </main>
    </div>

    @stack('scripts')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @include('components.MacosDock')
</body>
</html>