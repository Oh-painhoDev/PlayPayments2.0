<?php

// Load deployment configuration FIRST
if (file_exists(__DIR__ . '/deployment.php')) {
    require_once __DIR__ . '/deployment.php';
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/health.php'));
            
            // Carregar rotas do subdomínio API se o arquivo existir
            // Estas rotas são carregadas ANTES das rotas principais para ter prioridade
            // Elas só funcionam no subdomínio API (api.playpayments.com)
            // IMPORTANTE: Esta pasta (api/) é apenas para API, então só carrega rotas de API
            if (file_exists(base_path('routes/api-subdomain.php'))) {
                require base_path('routes/api-subdomain.php');
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'api.auth' => \App\Http\Middleware\ApiAuthMiddleware::class,
            'api.public.private' => \App\Http\Middleware\ApiPublicPrivateAuthMiddleware::class,
            'jwt.auth' => \App\Http\Middleware\JwtAuthenticate::class,
            'document.verified' => \App\Http\Middleware\DocumentVerificationMiddleware::class,
            'user.not.blocked' => \App\Http\Middleware\CheckUserBlocked::class,
            'onboarding.gate' => \App\Http\Middleware\OnboardingGate::class,
        ]);
        
        // Apply CORS globally (necessário para o checkout em localhost:3000)
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);
        
        // Disable CSRF protection completely (for Replit iframe compatibility)
        $middleware->validateCsrfTokens(except: ['*']);
        
        // Add the CheckUserBlocked middleware to the web group
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TokenAuthMiddleware::class,
            \App\Http\Middleware\AllowIframeAuth::class,
            \App\Http\Middleware\CompressResponse::class,
            \App\Http\Middleware\CheckUserBlocked::class,
            \App\Http\Middleware\NoCacheMiddleware::class,
            \App\Http\Middleware\SetCacheHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // SEMPRE retornar JSON para rotas de API, independente do modo debug
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            // Para rotas de API, sempre retornar JSON mesmo em 404
            if ($request->is('api/*') || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Rota não encontrada',
                    'message' => 'A rota solicitada não existe. Verifique a URL e o método HTTP (GET, POST, etc).',
                    'path' => $request->path(),
                    'method' => $request->method()
                ], 404);
            }
        });
        
        // Handler para outras exceções HTTP
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            // Para rotas de API, sempre retornar JSON
            if ($request->is('api/*') || $request->wantsJson() || $request->ajax()) {
                $statusCode = $e->getStatusCode();
                $message = $e->getMessage() ?: 'Erro na requisição';
                
                return response()->json([
                    'success' => false,
                    'error' => $message,
                    'message' => $message,
                    'status_code' => $statusCode
                ], $statusCode);
            }
        });
        
        // Em produção, ocultar detalhes técnicos dos erros
        if (!config('app.debug')) {
            $exceptions->render(function (\Throwable $e, Request $request) {
                // Log do erro completo
                \Illuminate\Support\Facades\Log::error('Erro não tratado: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method()
                ]);
                
                // Para requisições AJAX/API, retornar JSON
                if ($request->ajax() || $request->wantsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Erro interno do servidor. Tente novamente.',
                        'message' => 'Ocorreu um erro ao processar sua requisição.'
                    ], 500);
                }
                
                // Para requisições web, retornar página de erro genérica
                // Verifica se a view existe antes de tentar renderizar
                if (view()->exists('errors.500')) {
                    return response()->view('errors.500', [
                        'message' => 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.'
                    ], 500);
                }
                
                // Fallback: retornar resposta simples se view não existir
                return response('Erro interno do servidor. Tente novamente mais tarde.', 500);
            });
        } else {
            // Em modo debug, ainda retornar JSON para rotas de API
            $exceptions->render(function (\Throwable $e, Request $request) {
                // Para requisições AJAX/API, retornar JSON mesmo em debug
                if ($request->is('api/*') || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : null
                    ], 500);
                }
            });
        }
    })->create();