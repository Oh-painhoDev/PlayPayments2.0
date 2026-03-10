<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\E2BankWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes - API Subdomain
|--------------------------------------------------------------------------
|
| Este arquivo contém apenas rotas web necessárias para a API:
| - Webhooks (públicos, sem CSRF)
| - Checkout público (para payment links)
|
| TODAS as outras rotas web (dashboard, auth, admin, etc) devem estar em app/
|
*/

// Status da API - retorna online/offline
Route::get('/', function () {
    try {
        // Verificar se o banco de dados está acessível
        \DB::connection()->getPdo();
        $status = 'online';
    } catch (\Exception $e) {
        $status = 'offline';
    }
    
    return response()->json([
        'status' => $status === 'online' ? 'online' : 'offline',
        'service' => 'API Gateway - MazorPay',
        'version' => '1.1',
        'timestamp' => now('America/Sao_Paulo')->toIso8601String()
    ])
    ->header('Access-Control-Allow-Origin', '*')
    ->header('Access-Control-Allow-Headers', 'Content-Type, X-Public-Key, X-Private-Key')
    ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
});

Route::options('/', function () {
    return response()->noContent()
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Headers', 'Content-Type, X-Public-Key, X-Private-Key')
        ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
});

// Public checkout route (no auth required) - necessário para payment links
Route::get('/checkout/{slug}', [\App\Http\Controllers\PaymentLinkController::class, 'checkout'])->name('checkout.show');
Route::post('/checkout/{slug}/process', [\App\Http\Controllers\PaymentLinkController::class, 'processCheckout'])->name('checkout.process');

// Public API Documentation
Route::get('/api-docs', function() {
    return view('api-docs');
})->name('api.docs');

// Rotas da API v2 sem prefixo /api (para compatibilidade)
// As rotas com /api/v2/transactions também funcionam
Route::prefix('v2')->name('api.v2.direct.')->withoutMiddleware(['csrf'])->group(function () {
    Route::prefix('transactions')->name('transactions.')->group(function () {
        // GET - List and search transactions (accepts Public Key or Secret Key)
        Route::middleware([\App\Http\Middleware\ApiAuthMiddleware::class])->group(function () {
            Route::get('/', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'show'])->name('show');
        });
        
        // POST - Create transaction (PIX, Credit Card, Boleto) - REQUIRES Public Key AND Private Key
        Route::middleware([\App\Http\Middleware\ApiPublicPrivateAuthMiddleware::class])->group(function () {
            Route::post('/', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'store'])->name('store');
            // POST - Test PIX (usa gateway do usuário autenticado)
            Route::post('/test-pix', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'testPix'])->name('test-pix');
        });
    });
});

// Webhook Routes (public - no CSRF)
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
