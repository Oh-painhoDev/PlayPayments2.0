protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,

    'onboarding.gate' => \App\Http\Middleware\OnboardingGate::class,
];
