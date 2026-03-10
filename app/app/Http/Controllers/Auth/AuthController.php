<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request)
    {
        try {
            // Rate limiting: 5 attempts per minute per IP
            $key = 'login_attempts_' . $request->ip();
            $attempts = \Illuminate\Support\Facades\Cache::get($key, 0);
            
            if ($attempts >= 5) {
                \Illuminate\Support\Facades\Log::warning('Tentativa de login bloqueada por rate limit', [
                    'ip' => $request->ip(),
                    'email' => $request->input('email')
                ]);
                return back()->withErrors([
                    'email' => 'Muitas tentativas de login. Aguarde 1 minuto antes de tentar novamente.',
                ])->onlyInput('email');
            }
            
            $credentials = $request->validated();
            
            // Check if user is blocked before attempting authentication
            $user = User::where('email', $credentials['email'])->first();
            if ($user && $user->isBlocked()) {
                \Illuminate\Support\Facades\Cache::put($key, $attempts + 1, 60); // Increment and cache for 1 minute
                return back()->withErrors([
                    'email' => 'Esta conta está bloqueada. Entre em contato com o suporte para mais informações.',
                ])->onlyInput('email');
            }
            
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                // Clear rate limit on successful login
                \Illuminate\Support\Facades\Cache::forget($key);
                $request->session()->regenerate();
                
                $user = Auth::user();
                
                // Generate auth token for iframe compatibility
                $tokenData = $user->id . ':' . time();
                $authToken = base64_encode($tokenData);
                
                Log::info('Login bem-sucedido', [
                    'user_id' => $user->id,
                    'user_email' => substr($user->email, 0, 3) . '***@***', // Sanitize email in logs
                    'ip' => $request->ip(),
                    'auth_token_generated' => true
                ]);
                
                // Check if user is admin or manager and redirect accordingly
                if ($user->isAdminOrManager()) {
                    return redirect()->intended(route('admin.dashboard') . '?auth_token=' . $authToken)->with('success', 'Login realizado com sucesso!');
                }
                
                return redirect()->intended(route('dashboard') . '?auth_token=' . $authToken)->with('success', 'Login realizado com sucesso!');
            }
            
                // Increment rate limit on failed login
                \Illuminate\Support\Facades\Cache::put($key, $attempts + 1, 60);
                
                Log::warning('Falha na autenticação', [
                    'email' => substr($credentials['email'], 0, 3) . '***@***', // Sanitize email in logs
                    'ip' => $request->ip()
                ]);

                return back()->withErrors([
                    'email' => 'As credenciais fornecidas não conferem com nossos registros.',
                ])->onlyInput('email');

        } catch (\Exception $e) {
            Log::error('Erro no login', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors([
                'email' => 'Erro interno do servidor. Tente novamente. Detalhes: ' . $e->getMessage(),
            ])->onlyInput('email');
        }
    }

    /**
     * Show register form
     */
    public function showRegister(Request $request)
    {
        $referralCode = $request->get('ref');
        return view('auth.register', ['referral_code' => $referralCode]);
    }

    /**
     * Handle register request
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();
            
            // Log dos dados recebidos para debug
            Log::info('Dados de registro recebidos:', $data);
            
            // Remove formatting from document, whatsapp and cep
            $data['document'] = preg_replace('/[^0-9]/', '', $data['document']);
            $data['whatsapp'] = preg_replace('/[^0-9]/', '', $data['whatsapp']);
            $data['cep'] = preg_replace('/[^0-9]/', '', $data['cep']);
            
            // Convert terms_accepted to boolean
            $data['terms_accepted'] = $request->boolean('terms_accepted');
            
            // Handle birth_date for pessoa_fisica
            if ($data['account_type'] === 'pessoa_fisica' && !empty($data['birth_date'])) {
                // Ensure birth_date is in correct format
                $data['birth_date'] = date('Y-m-d', strtotime($data['birth_date']));
            } else {
                $data['birth_date'] = null;
            }
            
            // Set business fields to null for pessoa_fisica
            if ($data['account_type'] === 'pessoa_fisica') {
                $data['business_type'] = null;
                $data['business_sector'] = null;
            }
            
            // Process referral code from query string or request
            if ($request->has('ref')) {
                $data['referral_code'] = $request->get('ref');
            } elseif ($request->has('referral_code')) {
                $data['referral_code'] = $request->get('referral_code');
            }
            
            // Log dos dados limpos
            Log::info('Dados limpos:', [
                'document' => $data['document'],
                'whatsapp' => $data['whatsapp'],
                'cep' => $data['cep'],
                'terms_accepted' => $data['terms_accepted'],
                'birth_date' => $data['birth_date'],
                'referral_code' => $data['referral_code'] ?? null
            ]);
            
            $user = $this->authService->createUser($data);
            
            Auth::login($user);
            $request->session()->regenerate();
            
            Log::info('Registro bem-sucedido', [
                'user_id' => Auth::id(),
                'user_email' => Auth::user()->email,
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('dashboard')->with('success', 'Conta criada com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro no registro: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return back()->withErrors([
                'email' => 'Erro ao criar conta: ' . $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Show forgot password form
     */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        try {
            // Check if user is blocked
            $user = User::where('email', $request->email)->first();
            if ($user && $user->isBlocked()) {
                return back()->withErrors([
                    'email' => 'Esta conta está bloqueada. Entre em contato com o suporte para mais informações.',
                ]);
            }
            
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status === Password::RESET_LINK_SENT
                        ? back()->with(['status' => 'Link de recuperação enviado para seu e-mail!'])
                        : back()->withErrors(['email' => 'Não foi possível enviar o link de recuperação.']);
                        
        } catch (\Exception $e) {
            Log::error('Erro no forgot password: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Erro interno do servidor. Tente novamente.',
            ]);
        }
    }

    /**
     * Show reset password form
     */
    public function showResetPassword(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Handle reset password request
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            // Check if user is blocked
            $user = User::where('email', $request->email)->first();
            if ($user && $user->isBlocked()) {
                return back()->withErrors([
                    'email' => 'Esta conta está bloqueada. Entre em contato com o suporte para mais informações.',
                ]);
            }
            
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function (User $user, string $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    event(new PasswordReset($user));
                }
            );

            return $status === Password::PASSWORD_RESET
                        ? redirect()->route('login')->with('status', 'Senha alterada com sucesso!')
                        : back()->withErrors(['email' => 'Token inválido ou expirado.']);
                        
        } catch (\Exception $e) {
            Log::error('Erro no reset password: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Erro interno do servidor. Tente novamente.',
            ]);
        }
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout realizado com sucesso!');
    }

    /**
     * Handle JWT login (API)
     */
    public function jwtLogin(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $credentials['email'])->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            if ($user->isBlocked()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta conta está bloqueada'
                ], 403);
            }

            if (!Hash::check($credentials['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas'
                ], 401);
            }

            $token = JWTAuth::fromUser($user);
            
            $user->api_token = $token;
            $user->save();

            Log::info('JWT Login bem-sucedido', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login realizado com sucesso',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'account_type' => $user->account_type,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no JWT login: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro no servidor'
            ], 500);
        }
    }

    /**
     * Refresh JWT token
     */
    public function jwtRefresh(Request $request)
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            
            $user = JWTAuth::setToken($token)->toUser();
            $user->api_token = $token;
            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao renovar token: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao renovar token'
            ], 500);
        }
    }

    /**
     * JWT logout
     */
    public function jwtLogout(Request $request)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if ($user) {
                $user->api_token = null;
                $user->save();
            }

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logout realizado com sucesso'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro no JWT logout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro no logout'
            ], 500);
        }
    }

    /**
     * Get authenticated user (JWT)
     */
    public function jwtMe(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'account_type' => $user->account_type,
                        'is_blocked' => $user->is_blocked,
                        'wallet_balance' => $user->wallet_balance,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar usuário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar usuário'
            ], 500);
        }
    }
}