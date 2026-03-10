<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\Pix\TransactionsController as PixTransactionsController;
use App\Http\Controllers\Api\V1\TransactionController as V1TransactionController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\WithdrawalController;
use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\api\PixApiController;
use App\Http\Controllers\api\WithdrawalApiController;
use App\Http\Controllers\api\TestApiController;
use App\Http\Controllers\api\UtmifyApiController;
use App\Http\Controllers\api\AstrofyApiController;
use App\Http\Controllers\api\CepController;
use App\Http\Controllers\api\CnpjController;

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

// Health check (sem autenticação)
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'playpayments API',
        'timestamp' => now()->toIso8601String(),
    ], 200);
})->name('api.health');

// API v1 - Transactions (Compatível com PodPay)
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Transactions - Usando PixTransactionsController (compatível com PodPay)
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [PixTransactionsController::class, 'index'])->name('index');
        Route::get('/{id}', [PixTransactionsController::class, 'show'])->name('show');
        Route::post('/', [PixTransactionsController::class, 'store'])->name('store');
    });
    
    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/', [CustomerController::class, 'store'])->name('store');
        Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
        Route::put('/{id}', [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy');
    });
    
    // Withdrawals (PIX OUT)
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [WithdrawalController::class, 'index'])->name('index');
        Route::post('/', [WithdrawalController::class, 'store'])->name('store');
        Route::get('/{id}', [WithdrawalController::class, 'show'])->name('show');
        Route::get('/status/{id}', [WithdrawalController::class, 'status'])->name('status');
    });
    
    // Balance
    Route::get('/balance', [BalanceController::class, 'index'])->name('balance');
});

// API v1 - Alternative routes (using /api/v1/transactions format) - Mantido para compatibilidade
// Route::prefix('v1')->name('api.v1.alt.')->group(function () {
//     Route::prefix('transactions')->name('transactions.')->group(function () {
//         Route::get('/', [V1TransactionController::class, 'index'])->name('index');
//         Route::get('/{id}', [V1TransactionController::class, 'show'])->name('show');
//         Route::post('/', [V1TransactionController::class, 'store'])->name('store');
//     });
// });

// PIX API
Route::prefix('pix')->name('api.pix.')->group(function () {
    Route::post('/', [PixApiController::class, 'create'])->name('create');
    Route::get('/', [PixApiController::class, 'index'])->name('index');
    Route::get('/{transactionId}', [PixApiController::class, 'show'])->name('show');
    Route::get('/status/{transactionId}', [PixApiController::class, 'status'])->name('status');
});

// Payments API
Route::prefix('payments')->name('api.payments.')->group(function () {
    Route::post('/', [PixApiController::class, 'create'])->name('create');
    Route::get('/', [PixApiController::class, 'index'])->name('index');
    Route::get('/{transactionId}', [PixApiController::class, 'show'])->name('show');
    Route::get('/status/{transactionId}', [PixApiController::class, 'status'])->name('status');
});

// Withdrawals API
Route::prefix('withdrawals')->name('api.withdrawals.')->group(function () {
    Route::post('/', [WithdrawalApiController::class, 'create'])->name('create');
    Route::get('/', [WithdrawalApiController::class, 'index'])->name('index');
    Route::get('/{withdrawalId}', [WithdrawalApiController::class, 'show'])->name('show');
    Route::get('/status/{withdrawalId}', [WithdrawalApiController::class, 'status'])->name('status');
});

// External PIX API
Route::prefix('external-pix')->name('api.external-pix.')->group(function () {
    Route::post('/create', [PixApiController::class, 'createExternal'])->name('create');
    Route::get('/status/{transactionId}', [PixApiController::class, 'statusExternal'])->name('status');
});

// Utmify API
Route::prefix('utmify')->name('api.utmify.')->group(function () {
    Route::post('/generate-pix', [UtmifyApiController::class, 'generatePix'])->name('generate-pix');
});

// Astrofy API
Route::prefix('astrofy')->name('api.astrofy.')->group(function () {
    Route::post('/order', [AstrofyApiController::class, 'createOrder'])->name('order');
    Route::get('/order/{externalId}', [AstrofyApiController::class, 'orderStatus'])->name('order.status');
});

// Test API
Route::prefix('test')->name('api.test.')->group(function () {
    Route::get('/', [TestApiController::class, 'index'])->name('index');
    Route::post('/pix', [TestApiController::class, 'testPix'])->name('pix');
});

// Test PIX Simple (sem autenticação - apenas desenvolvimento)
Route::post('/test-pix-simple', [TestApiController::class, 'testPixSimple'])->name('api.test-pix-simple');

// CEP API
Route::prefix('cep')->name('api.cep.')->group(function () {
    Route::get('/{cep}', [CepController::class, 'show'])->name('show');
});

// CNPJ API
Route::prefix('cnpj')->name('api.cnpj.')->group(function () {
    Route::get('/{cnpj}', [CnpjController::class, 'show'])->name('show');
});

// Fallback para rotas não encontradas
Route::fallback(function (Request $request) {
    return response()->json([
        'success' => false,
        'error' => 'Rota não encontrada',
        'message' => 'A rota solicitada não existe. Verifique a URL e o método HTTP (GET, POST, etc).',
        'path' => $request->path(),
        'method' => $request->method()
    ], 404);
});
