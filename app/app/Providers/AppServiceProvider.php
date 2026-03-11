<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Transaction;
use App\Observers\TransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configurar timezone para São Paulo, Brasil
        $timezone = config('app.timezone', 'America/Sao_Paulo');
        date_default_timezone_set($timezone);
        \Carbon\Carbon::setLocale('pt_BR');
        
        // Garantir que diretórios de storage existem
        if (class_exists(\App\Helpers\StorageHelper::class)) {
            \App\Helpers\StorageHelper::ensureStorageDirectories();
        }
        
        // Set PHP upload and execution limits for large file uploads
        @ini_set('upload_max_filesize', '500M');
        @ini_set('post_max_size', '500M');
        @ini_set('memory_limit', '512M');
        @ini_set('max_execution_time', '300');
        @ini_set('max_input_time', '300');
        @ini_set('max_file_uploads', '50');
        
        // Configure CORS headers for API requests
        if (request()->is('api/*') || request()->is('webhook/*')) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key');
            header('Access-Control-Max-Age: 86400');
            
            // Handle preflight requests
            if (request()->isMethod('OPTIONS')) {
                header('HTTP/1.1 200 OK');
                exit(0);
            }
        }
        
        // Fix asset URLs and session cookies to work on any domain (Replit public URL support)
        if (isset($_SERVER['HTTP_HOST'])) {
            // Detecta se é HTTPS checando headers do proxy/load balancer
            $isHttps = (
                (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            );
            
            $protocol = $isHttps ? 'https' : 'http';
            $currentUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
            
            // Configura URLs
            config(['app.url' => $currentUrl]);
            \Illuminate\Support\Facades\URL::forceRootUrl($currentUrl);
            \Illuminate\Support\Facades\URL::forceScheme($protocol);
            
            // Configura cookies de sessão para HTTPS quando necessário
            if ($isHttps) {
                config(['session.secure' => true]);
                config(['session.same_site' => 'none']); // Required for secure cookies in iframe/cross-origin
            }
        }
        
        // Add a custom directive to check if documents are approved
        Blade::if('documentsApproved', function () {
            $user = Auth::user();
            if (!$user) return false;
            
            // Admin and manager users always have access
            if ($user->isAdminOrManager()) return true;
            
            $verification = $user->documentVerification;
            return $verification && $verification->isApproved();
        });
        
        // Helper function to get white label image URL
        $getWhiteLabelImageUrl = function($key) {
            if (!Schema::hasTable('white_label_settings')) {
                return null;
            }
            
            $setting = DB::table('white_label_settings')
                ->where('key', $key)
                ->first();
            
            if (!$setting || !$setting->value) {
                return null;
            }
            
            // Check if it's a URL
            if (filter_var($setting->value, FILTER_VALIDATE_URL)) {
                // Reject URLs that contain 'undefined' – those are broken uploads
                if (str_contains($setting->value, 'undefined')) {
                    return null;
                }
                return $setting->value;
            }
            
            // It's a local file - use asset() directly without adding storage/
            return asset($setting->value);
        };
        
        $getWhiteLabelSettingValue = function($key, $default = null) {
            if (!Schema::hasTable('white_label_settings')) {
                return $default;
            }
            
            $setting = DB::table('white_label_settings')
                ->where('key', $key)
                ->first();
            
            return $setting ? $setting->value : $default;
        };
        
        // Share theme variables, favicon, banner and SEO with all views
        View::composer('*', function ($view) use ($getWhiteLabelImageUrl, $getWhiteLabelSettingValue) {
            $themeVars = [
                'theme_background' => env('THEME_BACKGROUND', '#0d0d0d'),           // Fundo geral
                'theme_card_bg' => env('THEME_CARD_BG', '#1a1a1a'),                 // Fundo de cartões
                'theme_sidebar_bg' => env('THEME_SIDEBAR_BG', '#0f0f0f'),           // Sidebar
                'theme_header_bg' => env('THEME_HEADER_BG', '#0f0f0f'),             // Header
                'theme_border' => env('THEME_BORDER', '#2c2c2e'),                   // Bordas
                'theme_text' => env('THEME_TEXT', '#f4f4f5'),                       // Texto principal
                'theme_text_secondary' => env('THEME_TEXT_SECONDARY', '#a1a1aa'),   // Texto secundário
                'theme_primary' => env('THEME_PRIMARY', '#dc2626'),                 // 🔴 Vermelho principal (#dc2626 - red-600)
                'theme_success' => env('THEME_SUCCESS', '#22c55e'),                 // Verde sucesso
                'theme_warning' => env('THEME_WARNING', '#eab308'),                 // Amarelo alerta
                'theme_danger' => env('THEME_DANGER', '#b91c1c'),                   // Vermelho escuro (perigo)
                'theme_info' => env('THEME_INFO', '#f87171'),                       // Vermelho claro (informação)
            ];
            
            // Get white label favicon, logo and banner
            $whiteLabelFavicon = $getWhiteLabelImageUrl('favicon');
            $whiteLabelLogo = $getWhiteLabelImageUrl('logo');
            $whiteLabelBanner = $getWhiteLabelImageUrl('dashboard_banner');
            $authBannerImage = $getWhiteLabelImageUrl('auth_banner');
            $authBannerActiveValue = $getWhiteLabelSettingValue('auth_banner_active', null);
            $authBannerActive = $authBannerActiveValue !== null
                ? $authBannerActiveValue === '1'
                : (bool) $authBannerImage;
            $authBannerSide = $getWhiteLabelSettingValue('auth_banner_side', 'left');
            $authBannerSide = in_array($authBannerSide, ['left', 'right']) ? $authBannerSide : 'left';
            if (!$authBannerImage && $whiteLabelBanner) {
                $authBannerImage = $whiteLabelBanner;
            }
            $authBannerEnabled = $authBannerImage && $authBannerActive;
            
            // Fallback to default favicon if not set
            $faviconUrl = $whiteLabelFavicon ?? asset('favicon.svg');
            $faviconIco = $whiteLabelFavicon ?? asset('favicon.ico');
            
            // SEO Meta Tags for playpayments
            $seoMeta = [
                'title' => config('app.name', '$playpayments') . ' - Gateway de Pagamento PIX',
                'description' => '$playpayments - Plataforma completa de gateway de pagamento PIX. Aceite pagamentos de forma rápida, segura e fácil. Integre pagamentos PIX em seu site ou aplicativo.',
                'keywords' => 'playpayments, gateway pagamento, pagamento pix, pix gateway, gateway de pagamento brasil, api pix, integração pix, pagamentos online, gateway pagamentos, pagamento online pix, api de pagamento, gateway pagamento online, pagamento instantâneo, recebimento pix, sistema pagamento pix, integração pagamento pix',
                'author' => '$playpayments',
                'og_title' => '$playpayments - Gateway de Pagamento PIX',
                'og_description' => 'Plataforma completa de gateway de pagamento PIX. Aceite pagamentos de forma rápida, segura e fácil.',
                'og_image' => $whiteLabelBanner ?? asset('images/playpayments-logo-top.webp'),
                'og_url' => url()->current(),
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
            ];
            
            $view->with('themeVars', $themeVars);
            $view->with('whiteLabelFavicon', $faviconUrl);
            $view->with('whiteLabelFaviconIco', $faviconIco);
            $view->with('whiteLabelLogo', $whiteLabelLogo ?? asset('images/playpayments-logo-top.webp'));
            $view->with('whiteLabelBanner', $whiteLabelBanner);
            $view->with('authBannerConfig', [
                'enabled' => (bool) $authBannerEnabled,
                'image' => $authBannerImage,
                'side' => $authBannerSide,
            ]);
            $view->with('seoMeta', $seoMeta);
        });
        
        // Share goals with dashboard layout views
        View::composer('layouts.dashboard', function ($view) {
            $goals = collect([]);
            
            try {
                if (Auth::check() && \Illuminate\Support\Facades\Schema::hasTable('goals')) {
                    $user = Auth::user();
                    // Buscar metas pessoais do usuário E metas globais (user_id null)
                    // O scope forUser já inclui ambas: metas do usuário E metas globais
                    $goals = \App\Models\Goal::forUser($user->id)
                        ->active()
                        ->ordered()
                        ->get()
                        ->map(function($goal) use ($user) {
                            // Para metas globais (user_id null), calcular progresso INDIVIDUAL do usuário
                            // Para metas pessoais (user_id não null), calcular baseado nas transações do usuário da meta
                            
                            // Calcular current_value baseado no usuário logado
                            // Metas globais: progresso individual do usuário logado
                            // Metas pessoais: progresso do usuário da meta (já filtrado)
                            $currentValue = $goal->getCurrentValueForUser($user->id);
                            $percentage = $goal->getPercentageForUser($user->id);
                            
                            // Verificar e premiar se meta foi atingida
                            // Para metas globais E pessoais, premiar baseado no progresso individual do usuário
                            if ($goal->auto_reward) {
                                $goal->checkAndReward($user->id);
                            }
                            
                            // Verificar se já foi premiado
                            $achieved = $goal->hasUserAchieved($user->id);
                            
                            return [
                                'id' => $goal->id,
                                'name' => $goal->name,
                                'type' => $goal->type,
                                'current_value' => $currentValue,
                                'target_value' => $goal->target_value,
                                'percentage' => $percentage,
                                'display_order' => $goal->display_order,
                                'reward_type' => $goal->reward_type,
                                'reward_value' => $goal->reward_value,
                                'reward_description' => $goal->reward_description,
                                'achieved' => $achieved,
                                'is_global' => $goal->user_id === null, // Flag para identificar metas globais
                            ];
                        })
                        // Filtrar apenas a primeira meta que não está completa (percentage < 100)
                        ->filter(function($goal) {
                            return $goal['percentage'] < 100;
                        })
                        ->take(1); // Pegar apenas a primeira meta não completada
                }
            } catch (\Exception $e) {
                // Table doesn't exist or error occurred, use empty collection
                $goals = collect([]);
            }
            
            $view->with('goals', $goals);
        });
        
        // Register Transaction Observer for UTMify integration
        Transaction::observe(TransactionObserver::class);
    }
}