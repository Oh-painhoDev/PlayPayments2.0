<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\AuthController as AuthAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\GatewayController as AdminGatewayController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WhiteLabelController;
use App\Http\Controllers\Admin\BaasController;
use App\Http\Controllers\Admin\RetryController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\BillingPeriodController;
use App\Http\Controllers\Admin\ProfitController;
use App\Http\Controllers\Admin\SystemLogsController;
use App\Http\Controllers\WebhookManagementController;
use App\Http\Controllers\E2BankWebhookController;
use App\Http\Middleware\DocumentVerificationMiddleware;
use App\Http\Middleware\CheckUserBlocked;

// ============================================
// ROTAS PÚBLICAS - DEVEM VIR ANTES DE QUALQUER MIDDLEWARE
// ============================================
// NOTA: Fotos de usuários agora estão em /images/users/photos/ (pasta pública direta)
// Não precisa mais de rotas especiais - o servidor web serve diretamente

// Servir arquivos estáticos (CSS, JS, imagens) - necessário para php artisan serve
Route::get('/css/{file}', function ($file) {
    $path = public_path("css/{$file}");
    if (file_exists($path) && is_file($path)) {
        $mime = mime_content_type($path) ?: 'text/css';
        return response()->file($path, ['Content-Type' => $mime]);
    }
    return response('CSS file not found: ' . $path, 404);
})->where('file', '[a-zA-Z0-9._-]+');

Route::get('/js/{file}', function ($file) {
    $path = public_path("js/{$file}");
    if (file_exists($path) && is_file($path)) {
        $mime = mime_content_type($path) ?: 'application/javascript';
        return response()->file($path, ['Content-Type' => $mime]);
    }
    return response('File not found: ' . $path, 404);
})->where('file', '[a-zA-Z0-9._-]+');

Route::get('/images/{file}', function ($file) {
    $path = public_path("images/{$file}");
    if (file_exists($path) && is_file($path)) {
        $mime = mime_content_type($path) ?: 'image/png';
        return response()->file($path, ['Content-Type' => $mime]);
    }
    return response('File not found: ' . $path, 404);
})->where('file', '[a-zA-Z0-9._/-]+');

Route::get('/favicon.ico', function () {
    $path = public_path('favicon.ico');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/x-icon']);
    }
    abort(404);
});

Route::get('/favicon.svg', function () {
    $path = public_path('favicon.svg');
    if (file_exists($path)) {
        return response()->file($path, ['Content-Type' => 'image/svg+xml']);
    }
    abort(404);
});

// Servir o arquivo index.html como a página inicial
Route::get('/', function () {
    return File::get(public_path('index.html'));
});

// Public checkout route (no auth required)
Route::get('/checkout/{slug}', [\App\Http\Controllers\PaymentLinkController::class, 'checkout'])->name('checkout.show');
Route::post('/checkout/{slug}/process', [\App\Http\Controllers\PaymentLinkController::class, 'processCheckout'])->name('checkout.process');

// Setup admin (apenas primeira vez)
Route::get('/setup-admin-brpix', function () {
    set_time_limit(120);
    ini_set('max_execution_time', 120);
    
    try {
        $user = \App\Models\User::updateOrCreate(
            ['email' => 'brpixoficial@gmail.com'],
            [
                'name' => 'Admin Brpix',
                'password' => \Illuminate\Support\Facades\Hash::make('@Davib0110'),
                'document_verified' => true,
            ]
        );
        
        // Gerar token de autenticação
        $timestamp = time();
        $token = base64_encode($user->id . ':' . $timestamp);
        $dashboardUrl = route('dashboard') . '?auth_token=' . $token;
        
        return response()->json([
            'success' => true,
            'message' => 'Admin criado com sucesso!',
            'user_id' => $user->id,
            'email' => 'brpixoficial@gmail.com',
            'senha' => '@Davib0110',
            'token' => $token,
            'dashboard_url' => $dashboardUrl
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->withoutMiddleware(['csrf']);

// Tornar qualquer usuário admin
Route::get('/make-admin/{email}', function ($email) {
    set_time_limit(120);
    ini_set('max_execution_time', 120);
    
    try {
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Usuário não encontrado com email: ' . $email
            ], 404);
        }
        
        // Verificar se user tem coluna is_admin
        $hasIsAdmin = \Illuminate\Support\Facades\Schema::hasColumn('users', 'is_admin');
        
        if ($hasIsAdmin) {
            $user->is_admin = true;
        }
        
        $user->document_verified = true;
        $user->save();
        
        // Gerar token de autenticação
        $timestamp = time();
        $token = base64_encode($user->id . ':' . $timestamp);
        $dashboardUrl = route('dashboard') . '?auth_token=' . $token;
        
        return response()->json([
            'success' => true,
            'message' => 'Usuário promovido a admin!',
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'is_admin' => $hasIsAdmin ? true : 'N/A',
            'document_verified' => true,
            'token' => $token,
            'dashboard_url' => $dashboardUrl
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->withoutMiddleware(['csrf']);

// Public API Documentation
Route::get('/api-docs', function() {
    return view('api-docs');
})->name('api.docs');

// Webhook Routes (public)
Route::prefix('webhook')->name('webhook.')->group(function () {
    // Aceitar tanto POST quanto GET para os webhooks
    Route::match(['get', 'post'], '/hopy', [WebhookController::class, 'hopy'])
        ->name('hopy')
        ->withoutMiddleware(['web', 'csrf']);
    
    Route::match(['get', 'post'], '/splitwave', [WebhookController::class, 'splitwave'])
        ->name('splitwave')
        ->withoutMiddleware(['web', 'csrf']);
    
    // Sharkgateway webhook
    Route::match(['get', 'post'], '/sharkgateway', [WebhookController::class, 'sharkgateway'])
        ->name('sharkgateway')
        ->withoutMiddleware(['web', 'csrf']);
    
    // Withdrawal webhook - completely public, no CSRF
    Route::any('/withdrawal', [WithdrawalController::class, 'webhook'])
        ->name('withdrawal')
        ->withoutMiddleware(['web', 'csrf']);
        
    // Cashtime webhook - completely public, no CSRF
    Route::any('/cashtime', [WebhookController::class, 'cashtime'])
        ->name('cashtime')
        ->withoutMiddleware(['web', 'csrf', 'throttle']);
    
    // Arkama webhook
    Route::match(['get', 'post'], '/arkama', [WebhookController::class, 'arkama'])
        ->name('arkama')
        ->withoutMiddleware(['web', 'csrf']);
    
    // Versell webhook
    Route::match(['get', 'post'], '/versell', [WebhookController::class, 'versell'])
        ->name('versell')
        ->withoutMiddleware(['web', 'csrf']);
    
    // GetPay webhook
    Route::match(['get', 'post'], '/getpay', [WebhookController::class, 'getpay'])
        ->name('getpay')
        ->withoutMiddleware(['web', 'csrf']);
    
    // E2 Bank webhooks
    Route::any('/e2bank/pix-in', [E2BankWebhookController::class, 'pixIn'])
        ->name('e2bank.pix_in')
        ->withoutMiddleware(['web', 'csrf']);
    
    Route::any('/e2bank/pix-out', [E2BankWebhookController::class, 'pixOut'])
        ->name('e2bank.pix_out')
        ->withoutMiddleware(['web', 'csrf']);
    
    // Pluggou webhook
    Route::match(['get', 'post'], '/pluggou', [WebhookController::class, 'pluggou'])
        ->name('pluggou')
        ->withoutMiddleware(['web', 'csrf']);
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
    
    // Redirect /acessar e /login para /auth/login (compatibilidade)
    Route::get('/acessar', function () {
        return redirect('/auth/login');
    });
    Route::get('/login', function () {
        return redirect('/auth/login');
    });
    
    // Register
    Route::get('/auth/register', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
    Route::post('/auth/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register.post');
    
    // Redirect /register e /cadastro para /auth/register (compatibilidade)
    Route::get('/register', function () {
        return redirect('/auth/register');
    });
    Route::get('/cadastro', function () {
        return redirect('/auth/register');
    });
    Route::post('/cadastro', [\App\Http\Controllers\AuthController::class, 'register'])->name('cadastro.post');
    
    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    
    // Reset Password
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// Protected Routes
Route::middleware(['auth', CheckUserBlocked::class])->group(function () {
    // Onboarding (sempre acessível para usuários autenticados)
    Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'save'])->name('onboarding.save');
    
    // Logout (sempre acessível)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Rotas que exigem onboarding completo
Route::middleware(['auth', CheckUserBlocked::class, 'onboarding.gate'])->group(function () {
    // Dashboard - só acessível se onboarding estiver completo
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/globe-sales', [DashboardController::class, 'getGlobeSales'])->name('api.globe.sales');
    
    // Deposit Routes
    Route::prefix('deposit')->name('deposit.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DepositController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DepositController::class, 'create'])->name('create');
        Route::get('/status/{transaction}', [\App\Http\Controllers\DepositController::class, 'checkStatus'])->name('status');
    });
    
    // Document viewing route (protected) - aceita caminho completo ou apenas filename
    Route::get('/documents/{path}', [DocumentController::class, 'viewDocument'])->where('path', '.*')->name('documents.view');
    
    // Document management routes - both /documents and /settings/documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents');
        Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::post('/documents/delete', [DocumentController::class, 'deleteDocument'])->name('documents.delete');
        
        // Password update route (always accessible)
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password.update');
        
        // Photo update route (always accessible - não precisa de verificação de documentos)
        Route::post('/photo', [SettingsController::class, 'updatePhoto'])->name('photo.update');
    });
    
    // Routes that require document verification
    Route::middleware([DocumentVerificationMiddleware::class])->group(function () {
        // Transações Routes (antigo sales)
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [SalesController::class, 'index'])->name('index');
            Route::get('/create', [SalesController::class, 'create'])->name('create');
            Route::post('/', [SalesController::class, 'store'])->name('store');
            Route::get('/{transactionId}/receipt', [SalesController::class, 'receipt'])->name('receipt');
            Route::get('/{transactionId}', [SalesController::class, 'show'])->name('show');
        });
        
        // Carteira Routes (antigo withdrawals)
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [WithdrawalController::class, 'index'])->name('index');
            Route::get('/create', [WithdrawalController::class, 'create'])->name('create');
            Route::post('/', [WithdrawalController::class, 'store'])->name('store');
            Route::get('/{withdrawalId}', [WithdrawalController::class, 'show'])->name('show');
        });
        
        // Chaves PIX Routes
        Route::prefix('pix-keys')->name('pix-keys.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PixKeyController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\PixKeyController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\PixKeyController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\PixKeyController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\PixKeyController::class, 'destroy'])->name('destroy');
        });
        
        // Estornos Routes (antigo disputes)
        Route::prefix('refunds')->name('refunds.')->group(function () {
            Route::get('/', [\App\Http\Controllers\DisputeController::class, 'index'])->name('index');
            Route::get('/{dispute}', [\App\Http\Controllers\DisputeController::class, 'show'])->name('show');
            Route::post('/{dispute}/refund', [\App\Http\Controllers\DisputeController::class, 'refund'])->name('refund');
            Route::post('/{dispute}/defend', [\App\Http\Controllers\DisputeController::class, 'defend'])->name('defend');
        });
        
        // Extrato Routes
        Route::prefix('revenues')->name('revenues.')->group(function () {
            Route::get('/', [\App\Http\Controllers\RevenueController::class, 'index'])->name('index');
            Route::get('/export', [\App\Http\Controllers\RevenueController::class, 'export'])->name('export');
        });
        
        // Clientes Routes
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\CustomerController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\CustomerController::class, 'store'])->name('store');
        });
        
        // Payment Links Routes
        Route::prefix('payment-links')->name('payment-links.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PaymentLinkController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\PaymentLinkController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\PaymentLinkController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\PaymentLinkController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\PaymentLinkController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\PaymentLinkController::class, 'destroy'])->name('destroy');
        });
        
        Route::prefix('referrals')->name('referrals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReferralController::class, 'index'])->name('index');
            Route::put('/{userId}/commission', [\App\Http\Controllers\ReferralController::class, 'updateCommission'])->name('update-commission');
            Route::post('/request-withdrawal', [\App\Http\Controllers\ReferralController::class, 'requestWithdrawal'])->name('request-withdrawal');
        });
        
        // Integrações Routes
        Route::get('/integracoes', function() {
            return view('integracoes');
        })->name('integracoes');
        
        // Premiações Route
        Route::get('/premiacoes', function() {
            return view('premiacoes');
        })->name('premiacoes');
        
        // UTMify Integration Routes
        Route::prefix('integracoes/utmfy')->name('integracoes.utmfy.')->group(function () {
            Route::get('/', [\App\Http\Controllers\UtmifyController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\UtmifyController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\UtmifyController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\UtmifyController::class, 'destroy'])->name('destroy');
        });
        
        // Astrofy Integration Routes
        Route::prefix('integracoes/astrofy')->name('integracoes.astrofy.')->group(function () {
            Route::get('/', [\App\Http\Controllers\AstrofyController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\AstrofyController::class, 'store'])->name('store');
            Route::put('/{id}', [\App\Http\Controllers\AstrofyController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\AstrofyController::class, 'destroy'])->name('destroy');
        });
        
        // API Key Route (antigo settings/api-keys)
        Route::get('/api-key', [SettingsController::class, 'apiKeys'])->name('api-key');
        
        // Webhooks Routes (antigo settings/webhooks)
        Route::prefix('webhooks')->name('webhooks.')->group(function () {
            Route::get('/documentation', [\App\Http\Controllers\WebhookManagementController::class, 'documentation'])->name('documentation');
            Route::get('/', [WebhookManagementController::class, 'index'])->name('index');
            Route::post('/', [WebhookManagementController::class, 'store'])->name('store');
            Route::put('/{id}', [WebhookManagementController::class, 'update'])->name('update');
            Route::delete('/{id}', [WebhookManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/regenerate-secret', [WebhookManagementController::class, 'regenerateSecret'])->name('regenerate-secret');
            Route::post('/{id}/test', [WebhookManagementController::class, 'test'])->name('test');
            Route::post('/dispatch', [WebhookManagementController::class, 'dispatchWebhooks'])->name('dispatch');
        });
        
        // Settings Routes (except documents, password, api-keys, webhooks, photo)
        Route::prefix('settings')->name('settings.')->group(function () {
            // Settings Index (with tabs)
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
            Route::put('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
            // Photo route moved outside - accessible without document verification
            
            // API Credentials
            Route::get('/api', [SettingsController::class, 'api'])->name('api');
            Route::post('/api/generate', [SettingsController::class, 'generateApiSecret'])->name('api.generate');
            Route::post('/api/regenerate', [SettingsController::class, 'regenerateApiSecret'])->name('api.regenerate');
            
            // API Documentation
            Route::get('/api/docs', [SettingsController::class, 'apiDocs'])->name('api.docs');
            
            // Gateway
            Route::get('/gateway', [GatewayController::class, 'index'])->name('gateway');
            Route::put('/gateway', [GatewayController::class, 'update'])->name('gateway.update');
            Route::post('/gateway/test', [GatewayController::class, 'test'])->name('gateway.test');
            
            // Fees
            Route::get('/fees', [SettingsController::class, 'fees'])->name('fees');
            Route::post('/withdrawal-fees', [SettingsController::class, 'updateWithdrawalFees'])->name('withdrawal-fees.update');
        });
    });
    
    // Documentos Route (não requer verificação de documentos, mas está no settings)
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::post('/upload', [DocumentController::class, 'upload'])->name('upload');
        Route::post('/delete', [DocumentController::class, 'deleteDocument'])->name('delete');
    });
    
    // Redirecionamentos para manter compatibilidade com rotas antigas
    Route::middleware([DocumentVerificationMiddleware::class])->group(function () {
        // Redirecionamentos sales -> transactions
        Route::get('/sales', function() { return redirect()->route('transactions.index'); });
        Route::get('/sales/create', function() { return redirect()->route('transactions.create'); });
        Route::get('/sales/{transactionId}', function($id) { return redirect()->route('transactions.show', $id); });
        
        // Redirecionamentos withdrawals -> wallet
        Route::get('/withdrawals', function() { return redirect()->route('wallet.index'); });
        Route::get('/withdrawals/create', function() { return redirect()->route('wallet.create'); });
        Route::get('/withdrawals/{withdrawalId}', function($id) { return redirect()->route('wallet.show', $id); });
        
        // Redirecionamentos disputes -> refunds
        Route::get('/disputes', function() { return redirect()->route('refunds.index'); });
        Route::get('/disputes/{dispute}', function($id) { return redirect()->route('refunds.show', $id); });
        
        // Redirecionamentos settings/api-keys -> api-key
        Route::get('/settings/api-keys', function() { return redirect()->route('api-key'); });
        
        // Redirecionamentos settings/webhooks -> webhooks
        Route::get('/settings/webhooks', function() { return redirect()->route('webhooks.index'); });
    });
    
    // Redirecionamento settings.documents -> documents (mantém rotas antigas funcionando)
    Route::get('/settings/documents', function() { return redirect()->route('documents.index'); });
});

// Rotas administrativas (não precisam de onboarding completo)
Route::middleware(['auth', CheckUserBlocked::class])->group(function () {
    // Return to admin account
    Route::get('/return-to-admin', [AdminController::class, 'returnToAdmin'])
        ->name('return.to.admin');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // System Logs
        Route::prefix('system-logs')->name('system-logs.')->group(function () {
            Route::get('/', [SystemLogsController::class, 'index'])->name('index');
            Route::get('/live', [SystemLogsController::class, 'getLiveLogs'])->name('live');
            Route::delete('/clear', [SystemLogsController::class, 'clearLogs'])->name('clear');
        });
        
        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'users'])->name('index');
            Route::get('/{user}', [AdminController::class, 'userShow'])->name('show');
            Route::get('/{user}/edit', [AdminController::class, 'userEdit'])->name('edit');
            Route::put('/{user}', [AdminController::class, 'userUpdate'])->name('update');
            Route::get('/{user}/fees', [AdminController::class, 'getUserFees'])->name('fees');
            Route::post('/{user}/fees', [AdminController::class, 'saveUserFees'])->name('fees.save');
            Route::post('/{user}/block', [AdminUserController::class, 'block'])->name('block');
            Route::post('/{user}/unblock', [AdminUserController::class, 'unblock'])->name('unblock');
            Route::get('/{user}/login', [AdminController::class, 'loginAsUser'])->name('login.as.user');
            Route::get('/{user}/details', [AdminUserController::class, 'getUserDetails'])->name('details');
            Route::get('/{user}/retention', [AdminController::class, 'userRetention'])->name('retention');
            Route::post('/{user}/retention', [AdminController::class, 'updateUserRetention'])->name('retention.update');
            Route::post('/{user}/retention/reset', [AdminController::class, 'resetUserRetention'])->name('retention.reset');
            Route::get('/{user}/withdrawal-fees', [AdminUserController::class, 'editWithdrawalFees'])->name('withdrawal-fees.edit');
            Route::post('/{user}/withdrawal-fees', [AdminUserController::class, 'updateWithdrawalFees'])->name('withdrawal-fees.update');
        });
        
        // Gateways Management
        Route::prefix('gateways')->name('gateways.')->group(function () {
            Route::delete('/credentials/{id}', [AdminGatewayController::class, 'deleteCredentials'])->name('credentials.delete');
            Route::get('/credentials/{id}/edit', [AdminGatewayController::class, 'editCredentials'])->name('credentials.edit');
            Route::get('/', [AdminGatewayController::class, 'index'])->name('index');
            Route::post('/', [AdminGatewayController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AdminGatewayController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AdminGatewayController::class, 'update'])->name('update');
            Route::delete('/{id}', [AdminGatewayController::class, 'destroy'])->name('destroy');
            Route::post('/configure', [AdminGatewayController::class, 'configure'])->name('configure');
            Route::post('/test', [AdminGatewayController::class, 'test'])->name('test');
            Route::get('/{id}/credentials', [AdminGatewayController::class, 'getCredentials'])->name('credentials');
            Route::get('/{id}/fees', [AdminGatewayController::class, 'fees'])->name('fees');
            Route::put('/{id}/fees', [AdminGatewayController::class, 'updateFees'])->name('fees.update');
            Route::put('/{id}/toggle-status', [AdminGatewayController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        // BaaS Management
        Route::prefix('baas')->name('baas.')->group(function () {
            Route::get('/', [BaasController::class, 'index'])->name('index');
            Route::post('/update', [BaasController::class, 'update'])->name('update');
            Route::post('/test', [BaasController::class, 'test'])->name('test');
            Route::post('/toggle-active', [BaasController::class, 'toggleActive'])->name('toggle-active');
        });
        
        // Retry Configuration
        Route::prefix('retry')->name('retry.')->group(function () {
            Route::get('/', [RetryController::class, 'index'])->name('index');
            Route::put('/update', [RetryController::class, 'update'])->name('update');
        });
        
        // Goals Management
        Route::prefix('goals')->name('goals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\GoalController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\GoalController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\GoalController::class, 'store'])->name('store');
            Route::get('/{goal}', [\App\Http\Controllers\Admin\GoalController::class, 'show'])->name('show');
            Route::get('/{goal}/edit', [\App\Http\Controllers\Admin\GoalController::class, 'edit'])->name('edit');
            Route::put('/{goal}', [\App\Http\Controllers\Admin\GoalController::class, 'update'])->name('update');
            Route::delete('/{goal}', [\App\Http\Controllers\Admin\GoalController::class, 'destroy'])->name('destroy');
            Route::post('/{goal}/toggle-status', [\App\Http\Controllers\Admin\GoalController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{goal}/reward-user', [\App\Http\Controllers\Admin\GoalController::class, 'rewardUser'])->name('reward-user');
        });
        
        // Multi-Gateway Configuration
        Route::prefix('multi-gateway')->name('multi-gateway.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MultiGatewayController::class, 'index'])->name('index');
            Route::put('/update', [\App\Http\Controllers\Admin\MultiGatewayController::class, 'update'])->name('update');
        });
        
        // Transactions
        Route::prefix('transactions')->name('transactions.')->group(function () {
            Route::get('/', [AdminController::class, 'transactions'])->name('index');
            Route::get('/{transactionId}', [AdminController::class, 'transactionShow'])->name('show');
            Route::post('/{transactionId}/dispute', [AdminController::class, 'transactionDispute'])->name('dispute');
            Route::post('/{transactionId}/refund', [AdminController::class, 'transactionRefund'])->name('refund');
            Route::post('/{transactionId}/fake-refund', [AdminController::class, 'transactionFakeRefund'])->name('fake-refund');
        });
        
        // Test PIX for UTMify
        Route::post('/test-pix', [\App\Http\Controllers\TestPixController::class, 'createTestPix'])->name('test-pix');
        
        // Withdrawals
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [AdminWithdrawalController::class, 'index'])->name('index');
            Route::get('/{withdrawal}', [AdminWithdrawalController::class, 'show'])->name('show');
            Route::post('/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('approve');
            Route::post('/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('reject');
        });
        
        // Admin Wallet Balance Adjustment
        Route::post('/adjust-wallet-balance', [AdminController::class, 'adjustWalletBalance'])->name('adjust-wallet-balance');
        
        // Document Verifications
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [AdminDocumentController::class, 'index'])->name('index');
            Route::get('/details/{userId}', [AdminDocumentController::class, 'details'])->name('details');
            Route::get('/serve/{userId}/{field}/{filename}', [AdminDocumentController::class, 'serveDocument'])->where('filename', '[^/]+')->name('serve');
            Route::post('/respond', [AdminDocumentController::class, 'respond'])->name('respond');
            Route::post('/fees/{user}', [AdminDocumentController::class, 'updateFees'])->name('fees.save');
            Route::post('/permissions/{user}', [AdminController::class, 'saveUserPermissions'])->name('permissions.save');
        });
        
        // White Label
        Route::prefix('white-label')->name('white-label.')->group(function () {
            // Branding
            Route::get('/branding', [\App\Http\Controllers\Admin\WhiteLabelBrandingController::class, 'index'])->name('branding');
            Route::post('/branding', [\App\Http\Controllers\Admin\WhiteLabelBrandingController::class, 'update'])->name('branding.update');
            
            // Announcements
            Route::get('/announcements', [\App\Http\Controllers\Admin\WhiteLabelAnnouncementController::class, 'index'])->name('announcements');
            Route::post('/announcements', [\App\Http\Controllers\Admin\WhiteLabelAnnouncementController::class, 'store'])->name('announcements.store');
            Route::put('/announcements/{id}', [\App\Http\Controllers\Admin\WhiteLabelAnnouncementController::class, 'update'])->name('announcements.update');
            Route::delete('/announcements/{id}', [\App\Http\Controllers\Admin\WhiteLabelAnnouncementController::class, 'destroy'])->name('announcements.destroy');
            
            // Global Fees
            Route::get('/global-fees', [WhiteLabelController::class, 'globalFees'])->name('global-fees');
            Route::get('/global-fees/get', [WhiteLabelController::class, 'getGlobalFees'])->name('global-fees.get');
            
            // UTMify Management
            Route::prefix('utmify')->name('utmify.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\UtmifyController::class, 'index'])->name('index');
                Route::post('/', [\App\Http\Controllers\Admin\UtmifyController::class, 'store'])->name('store');
                Route::put('/{id}', [\App\Http\Controllers\Admin\UtmifyController::class, 'update'])->name('update');
                Route::delete('/{id}', [\App\Http\Controllers\Admin\UtmifyController::class, 'destroy'])->name('destroy');
            });
            Route::post('/global-fees/update', [WhiteLabelController::class, 'updateGlobalFees'])->name('global-fees.update');
        });
        
        // Setup
        Route::prefix('setup')->name('setup.')->group(function () {
            // Retained Sales
            Route::get('/retained-sales', [SetupController::class, 'retainedSales'])->name('retained-sales');
            Route::get('/retained-sales/{transactionId}', [SetupController::class, 'retainedSaleDetails'])->name('retained-sales.details');
            Route::post('/retained-sales/{transactionId}/return', [SetupController::class, 'returnRetainedSale'])->name('retained-sales.return');
            
            // Retention Overview
            Route::get('/retention-overview', [SetupController::class, 'retentionOverview'])->name('retention-overview');
            
            // Disputes
            Route::get('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'index'])->name('disputes');
            Route::get('/disputes/create', [\App\Http\Controllers\Admin\DisputeController::class, 'create'])->name('disputes.create');
            Route::get('/disputes/bulk-create', [\App\Http\Controllers\Admin\DisputeController::class, 'bulkCreate'])->name('disputes.bulk-create');
            Route::post('/disputes/bulk-store', [\App\Http\Controllers\Admin\DisputeController::class, 'bulkStore'])->name('disputes.bulk-store');
            Route::post('/disputes', [\App\Http\Controllers\Admin\DisputeController::class, 'store'])->name('disputes.store');
            Route::get('/disputes/{dispute}', [\App\Http\Controllers\Admin\DisputeController::class, 'show'])->name('disputes.show');
            Route::post('/disputes/{dispute}/accept', [\App\Http\Controllers\Admin\DisputeController::class, 'acceptDefense'])->name('disputes.accept-defense');
            Route::post('/disputes/{dispute}/reject', [\App\Http\Controllers\Admin\DisputeController::class, 'rejectDefense'])->name('disputes.reject-defense');
            
            // Dispute Templates
            Route::get('/dispute-templates', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'index'])->name('dispute-templates.index');
            Route::get('/dispute-templates/create', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'create'])->name('dispute-templates.create');
            Route::post('/dispute-templates', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'store'])->name('dispute-templates.store');
            Route::get('/dispute-templates/{disputeTemplate}/edit', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'edit'])->name('dispute-templates.edit');
            Route::put('/dispute-templates/{disputeTemplate}', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'update'])->name('dispute-templates.update');
            Route::delete('/dispute-templates/{disputeTemplate}', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'destroy'])->name('dispute-templates.destroy');
            Route::post('/dispute-templates/{disputeTemplate}/toggle', [\App\Http\Controllers\Admin\DisputeTemplateController::class, 'toggle'])->name('dispute-templates.toggle');
        });
        
        // Billing
        Route::prefix('billing')->name('billing.')->group(function () {
            Route::get('/', [BillingController::class, 'index'])->name('index');
            Route::get('/{user}', [BillingController::class, 'show'])->name('show');
        });
        
        // Billing Period
        Route::prefix('billing-period')->name('billing-period.')->group(function () {
            Route::get('/', [BillingPeriodController::class, 'index'])->name('index');
            Route::get('/{user}', [BillingPeriodController::class, 'show'])->name('show');
        });
        
        // Profit
        Route::prefix('profit')->name('profit.')->group(function () {
            Route::get('/', [ProfitController::class, 'index'])->name('index');
            Route::get('/{user}', [ProfitController::class, 'show'])->name('show');
        });
    });
});
Route::view('/offline', 'offline');
