<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\WithdrawalApiController;
use App\Http\Middleware\ApiAuthMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// JWT Authentication Routes (public)
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login', [AuthController::class, 'jwtLogin'])->name('login');
    Route::post('/refresh', [AuthController::class, 'jwtRefresh'])->name('refresh');
    
    // Protected routes with JWT
    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/logout', [AuthController::class, 'jwtLogout'])->name('logout');
        Route::get('/me', [AuthController::class, 'jwtMe'])->name('me');
    });
});

// API Routes (sem CSRF)
Route::middleware(['api', \App\Http\Middleware\ForceJsonResponse::class])->group(function () {
    // Payment API Routes (protegidas por API Secret)
    Route::middleware([ApiAuthMiddleware::class])->prefix('payments')->name('api.payments.')->group(function () {
        Route::post('/', [PaymentController::class, 'create'])->name('create');
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{transactionId}', [PaymentController::class, 'show'])->name('show');
        Route::get('/status/{transactionId}', [PaymentController::class, 'checkStatus'])->name('status');
    });
    
    // Withdrawal API Routes (PIX OUT - protegidas por API Secret)
    Route::middleware([ApiAuthMiddleware::class])->prefix('withdrawals')->name('api.withdrawals.')->group(function () {
        Route::post('/', [WithdrawalApiController::class, 'store'])->name('create');
        Route::get('/', [WithdrawalApiController::class, 'index'])->name('index');
        Route::get('/{withdrawalId}', [WithdrawalApiController::class, 'show'])->name('show');
        Route::get('/status/{withdrawalId}', [WithdrawalApiController::class, 'checkStatus'])->name('status');
    });
    
    // PIX API Routes (PIX IN - protegidas por API Secret)
    Route::middleware([ApiAuthMiddleware::class])->prefix('pix')->name('api.pix.')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\PixApiController::class, 'create'])->name('create');
        Route::get('/', [\App\Http\Controllers\Api\PixApiController::class, 'index'])->name('index');
        Route::get('/status/{transactionId}', [\App\Http\Controllers\Api\PixApiController::class, 'checkStatus'])->name('status');
        Route::get('/{transactionId}', [\App\Http\Controllers\Api\PixApiController::class, 'show'])->name('show');
    });
    
    // Test API Routes (protegidas por API Secret)
    Route::middleware([ApiAuthMiddleware::class])->prefix('test')->name('api.test.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\TestApiController::class, 'test'])->name('test');
        Route::post('/pix', [\App\Http\Controllers\Api\TestApiController::class, 'testPix'])->name('pix');
        Route::post('/transaction', [\App\Http\Controllers\Api\TestApiController::class, 'testTransaction'])->name('transaction');
    });
    
    // Test PIX Route (sem autenticação - apenas para desenvolvimento)
    Route::post('/test-pix-simple', [\App\Http\Controllers\TestPixController::class, 'createTestPix'])->name('api.test-pix-simple');
    
    // External PIX API Routes (API PIX de outros websites - com tokens do cliente)
    // REQUER autenticação para identificar a conta
    Route::middleware([ApiAuthMiddleware::class])->prefix('external-pix')->name('api.external-pix.')->group(function () {
        Route::post('/create', [\App\Http\Controllers\ExternalPixController::class, 'create'])->name('create');
        Route::get('/status/{transactionId}', [\App\Http\Controllers\ExternalPixController::class, 'checkStatus'])->name('status');
    });
    
    // Utmify API - Generate PIX and send to Utmify
    Route::middleware([ApiAuthMiddleware::class])->prefix('utmify')->name('api.utmify.')->group(function () {
        Route::post('/generate-pix', [\App\Http\Controllers\Api\UtmifyApiController::class, 'generatePix'])->name('generate-pix');
    });

    // API v1 - Main API Routes (English)
    Route::prefix('v1')->name('api.v1.')->group(function () {
        // Transactions - Using the proven PIX TransactionsController
        Route::prefix('transactions')->name('transactions.')->group(function () {
            // GET - List and search transactions (accepts Public Key or Secret Key)
            Route::middleware([ApiAuthMiddleware::class])->group(function () {
                Route::get('/', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'index'])->name('index');
                Route::get('/{id}', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'show'])->name('show');
            });
            
            // POST - Create transaction (PIX) - REQUIRES Public Key AND Private Key
            Route::middleware([\App\Http\Middleware\ApiPublicPrivateAuthMiddleware::class])->group(function () {
                Route::post('/', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'store'])->name('store');
            });
        });

        // Customers
        Route::middleware([ApiAuthMiddleware::class])->prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\CustomerController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\CustomerController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Api\V1\CustomerController::class, 'show'])->name('show');
            Route::put('/{id}', [\App\Http\Controllers\Api\V1\CustomerController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Api\V1\CustomerController::class, 'destroy'])->name('destroy');
        });

        // Withdrawals
        Route::middleware([ApiAuthMiddleware::class])->prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\WithdrawalController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Api\V1\WithdrawalController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Api\V1\WithdrawalController::class, 'show'])->name('show');
        });

        // Balance
        Route::middleware([ApiAuthMiddleware::class])->prefix('balance')->name('balance.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\V1\BalanceController::class, 'index'])->name('index');
        });
    });
    
    // API v1 - Enhanced API Routes (v2 movido para v1)
    Route::prefix('v1')->name('api.v1.')->group(function () {
        // Transactions - Enhanced version with more features
        Route::prefix('transactions')->name('transactions.')->group(function () {
            // GET - List and search transactions (accepts Public Key or Secret Key)
            Route::middleware([ApiAuthMiddleware::class])->group(function () {
                Route::get('/', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'index'])->name('index');
                // GET - Buscar venda por transaction_id ou external_id (deve vir antes de /{id})
                Route::get('/search/{identifier}', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'search'])->name('search');
                Route::get('/{id}', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'show'])->name('show');
            });
            
            // POST - Create transaction (PIX, Credit Card, Boleto) - REQUIRES Public Key AND Private Key
            Route::middleware([\App\Http\Middleware\ApiPublicPrivateAuthMiddleware::class])->group(function () {
                Route::post('/', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'store'])->name('store');
            });
            
            // POST - Test PIX (usa gateway do usuário autenticado) - REQUIRES Public Key AND Private Key
            Route::middleware([\App\Http\Middleware\ApiPublicPrivateAuthMiddleware::class])->group(function () {
                Route::post('/test-pix', [\App\Http\Controllers\api\Pix\TransactionsController::class, 'testPix'])->name('test-pix');
            });
        });
    });
    
    // Astrofy API Routes (chamadas pela Astrofy)
    // Endpoints do provedor de pagamento (requerem X-Gateway-Key e X-Api-Key)
    Route::middleware([\App\Http\Middleware\AstrofyAuthMiddleware::class])
        ->prefix('astrofy')
        ->name('api.astrofy.')
        ->group(function () {
            Route::post('/order', [\App\Http\Controllers\Api\AstrofyGatewayController::class, 'createOrder'])->name('order.create');
            Route::get('/order/{externalId}', [\App\Http\Controllers\Api\AstrofyGatewayController::class, 'getOrderStatus'])->name('order.status');
        });
    
    // Endpoints do ecossistema Astrofy (chamados pelo provedor para registrar/atualizar gateway)
    // Requerem apenas X-Gateway-Key
    Route::prefix('v1/gateway')->name('api.astrofy.ecosystem.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\AstrofyEcosystemController::class, 'getGateway'])->name('get');
        Route::post('/', [\App\Http\Controllers\Api\AstrofyEcosystemController::class, 'createOrUpdateGateway'])->name('create-or-update');
        Route::delete('/', [\App\Http\Controllers\Api\AstrofyEcosystemController::class, 'deleteGateway'])->name('delete');
        Route::post('/logo', [\App\Http\Controllers\Api\AstrofyEcosystemController::class, 'uploadLogo'])->name('logo');
    });
    
    // Fallback for routes not found - always return JSON
    Route::fallback(function (Request $request) {
        return response()->json([
            'success' => false,
            'error' => 'Route not found',
            'message' => 'The requested route does not exist. Please check the URL and HTTP method (GET, POST, etc).',
            'path' => $request->path(),
            'method' => $request->method()
        ], 404);
    });
});