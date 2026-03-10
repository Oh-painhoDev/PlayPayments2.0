<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /* ============================
       LOGIN VIEW
    ============================ */
    public function loginView()
    {
        return view('auth.login');
    }

    /* ============================
       SHOW LOGIN (alias)
    ============================ */
    public function showLogin()
    {
        return view('auth.login');
    }

    /* ============================
       LOGIN
    ============================ */
    public function login(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        try {
            // Verificar se o usuário existe primeiro
            try {
                $user = \App\Models\User::where('email', $req->email)->first();
            } catch (\Illuminate\Database\QueryException $e) {
                // Erro de conexão com o banco
                if (strpos($e->getMessage(), 'Access denied') !== false || strpos($e->getMessage(), '1045') !== false) {
                    Log::error('Erro de conexão com banco de dados no login', [
                        'message' => $e->getMessage(),
                        'email' => $req->email
                    ]);
                    return back()->withErrors(['email' => 'Erro de conexão com o banco de dados. Verifique as configurações no arquivo .env.'])->withInput($req->only('email'));
                }
                throw $e;
            }
            
            if (!$user) {
                // Usuário não existe
                Log::warning('Tentativa de login com email não encontrado', ['email' => $req->email]);
                return back()->withErrors(['email' => 'As credenciais fornecidas não conferem com nossos registros.'])->withInput($req->only('email'));
            }
            
            // Verificar se usuário está bloqueado ANTES de tentar autenticar
            if ($user->is_blocked) {
                return back()->withErrors(['email' => 'Esta conta está bloqueada. Entre em contato com o suporte.'])->withInput($req->only('email'));
            }
            
            // Usar Auth::attempt() que faz toda a verificação de senha automaticamente
            $credentials = $req->only('email', 'password');
            $remember = $req->has('remember');

            // Tentar fazer login
            if (!Auth::attempt($credentials, $remember)) {
                // Senha incorreta
                Log::warning('Tentativa de login com senha incorreta', [
                    'user_id' => $user->id,
                    'email' => $req->email
                ]);
                return back()->withErrors(['email' => 'Senha incorreta. Verifique suas credenciais e tente novamente.'])->withInput($req->only('email'));
            }

            // Regenerar sessão após login
            $req->session()->regenerate();
            
            // Buscar usuário autenticado (já temos do check anterior, mas vamos pegar novamente para garantir)
            $user = Auth::user();
            
            Log::info('Login bem-sucedido', ['user_id' => $user->id, 'email' => $user->email]);

            // PRIORIDADE 1: Verificar se informações básicas foram completadas
            if (empty($user->name) || empty($user->email)) {
                return redirect()->route('onboarding');
            }

            // PRIORIDADE 2: Verificar se tipo de conta foi definido
            if (empty($user->account_type)) {
                return redirect()->route('onboarding');
            }

            // PRIORIDADE 3: Verificar se documento foi preenchido (document pode ser CPF ou CNPJ)
            if (empty($user->document)) {
                return redirect()->route('onboarding');
            }

            // PRIORIDADE 4: Verificar se endereço foi preenchido
            if (empty($user->cep) || empty($user->address) || empty($user->city) || empty($user->state)) {
                return redirect()->route('onboarding');
            }

            // PRIORIDADE 5: Verificar documentos (apenas se dados básicos estiverem completos)
            $docs = DB::table('document_verifications')
                ->where('user_id', $user->id)
                ->first();

            // Se não tem documentos ou falta algum documento, permite acesso ao dashboard
            // (onboarding de documentos será implementado depois)
            if (!$docs || empty($docs->front_document) || empty($docs->back_document) || empty($docs->selfie_document)) {
                return redirect()->route('dashboard')
                    ->with('info', 'Complete seu cadastro enviando seus documentos.');
            }

            /* -----------------------------------------
               3 — Rejeitado/Recusado → Fica preso no onboarding
            ------------------------------------------ */
            if ($docs->status === 'rejeitado' || $docs->status === 'recusado') {
                return redirect()->route('onboarding')
                    ->withErrors(['docs' => 'Seus documentos foram rejeitados. Envie novamente.']);
            }

            /* -----------------------------------------
               4 — Pendente → Dashboard com aviso
            ------------------------------------------ */
            if ($docs->status === 'pendente') {
                return redirect()->route('dashboard')
                    ->with('info', 'Seus documentos estão em análise.');
            }

            /* -----------------------------------------
               5 — Aprovado → Dashboard
            ------------------------------------------ */
            return redirect()->route('dashboard');
            
        } catch (\Illuminate\Database\QueryException $e) {
            // Erro de conexão com o banco
            Log::error('Erro de conexão com banco de dados no login', [
                'message' => $e->getMessage(),
                'email' => $req->email ?? 'N/A'
            ]);
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Access denied') !== false || strpos($errorMessage, '1045') !== false) {
                return back()->withErrors(['email' => 'Erro de conexão com o banco de dados. Verifique as configurações no arquivo .env.'])->withInput($req->only('email'));
            }
            return back()->withErrors(['email' => 'Erro ao processar login. Tente novamente.'])->withInput($req->only('email'));
        } catch (\Exception $e) {
            Log::error('Erro no login', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $req->email ?? 'N/A'
            ]);
            return back()->withErrors(['email' => 'Erro ao processar login. Tente novamente.'])->withInput($req->only('email'));
        }
    }

    /* ============================
       LOGOUT
    ============================ */
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    /* ============================
       REGISTER VIEW
    ============================ */
    public function registerView()
    {
        return view('auth.register');
    }

    /* ============================
       SHOW REGISTER (alias)
    ============================ */
    public function showRegister()
    {
        return view('auth.register'); // View simplificada com apenas 4 campos básicos
    }

    /* ============================
       API KEYS
    ============================ */
    private function bloco6()
    {
        return strtoupper(bin2hex(random_bytes(3)));
    }

    private function gerarChave($prefixo)
    {
        return $prefixo . "-" .
            $this->bloco6() . "-" .
            $this->bloco6() . "-" .
            $this->bloco6() . "-" .
            $this->bloco6();
    }

    /* ============================
       REGISTER
    ============================ */
    public function register(Request $req)
    {
        try {
            // Verificar se email já existe ANTES de validar
            try {
                $existingUser = \App\Models\User::where('email', $req->email)->first();
            } catch (\Illuminate\Database\QueryException $e) {
                // Erro de conexão com o banco - será tratado no catch principal
                // Mas vamos logar aqui também
                if (strpos($e->getMessage(), 'Access denied') !== false || strpos($e->getMessage(), '1045') !== false) {
                    Log::error('Erro de conexão com banco de dados no registro (verificação de email)', [
                        'message' => $e->getMessage(),
                        'email' => $req->email
                    ]);
                }
                // Relançar para ser tratado no catch de QueryException
                throw $e;
            }
            
            if ($existingUser) {
                return back()->withErrors(['email' => 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.'])->withInput($req->except('password'));
            }
            
            // Validar dados
            $req->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'whatsapp' => 'required|string',
                'password' => 'required|min:6',
                'terms_accepted' => 'required',
            ], [
                'name.required' => 'O nome completo é obrigatório.',
                'email.required' => 'O e-mail é obrigatório.',
                'email.email' => 'Por favor, insira um e-mail válido.',
                'email.unique' => 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.',
                'whatsapp.required' => 'O WhatsApp é obrigatório.',
                'password.required' => 'A senha é obrigatória.',
                'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
                'terms_accepted.required' => 'Você deve aceitar os termos de uso.',
            ]);

            // Usar transação para garantir consistência
            DB::beginTransaction();
            
            try {
                // Gerar chaves API automaticamente
                $apiSecret = $this->generateSecureApiSecret();
                $apiPublicKey = $this->generateSecureApiPublicKey();
                
                // Preparar dados do usuário usando Model User
                // O mutator setPasswordAttribute faz o hash automaticamente
                $userData = [
                    'name' => trim($req->name),
                    'email' => trim($req->email),
                    'password' => $req->password, // O mutator setPasswordAttribute faz o hash
                    'whatsapp' => preg_replace('/[^0-9]/', '', $req->whatsapp),
                    'terms_accepted' => 1,
                    'api_secret' => $apiSecret,
                    'api_secret_created_at' => now(),
                    'api_public_key' => $apiPublicKey,
                    'api_public_key_created_at' => now(),
                ];
                
                // Criar usuário usando Eloquent Model (o hash é aplicado automaticamente pelo mutator)
                $user = \App\Models\User::create($userData);
                
                Log::info('Usuário criado com sucesso', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name
                ]);

                DB::commit();

                // Login automático usando o Model
                Auth::login($user);

                // Redirecionar para onboarding
                return redirect()->route('onboarding')->with('success', 'Conta criada com sucesso! Complete seu cadastro.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao criar usuário', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $req->except('password')
                ]);
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator->errors())->withInput($req->except('password'));
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            
            // Erro de banco de dados - mostrar detalhes
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            // Log detalhado
            Log::error('Erro de banco ao registrar usuário', [
                'code' => $errorCode,
                'message' => $errorMessage,
                'sql' => method_exists($e, 'getSql') ? $e->getSql() : 'N/A',
                'bindings' => method_exists($e, 'getBindings') ? $e->getBindings() : [],
            ]);
            
            // Mensagem amigável baseada no erro
            if (strpos($errorMessage, 'Access denied') !== false || strpos($errorMessage, '1045') !== false || (strpos($errorMessage, 'HY000') !== false && strpos($errorMessage, 'Access denied') !== false)) {
                // Erro de conexão com o banco de dados
                $friendlyMessage = 'Erro de conexão com o banco de dados. Verifique as configurações no arquivo .env. '
                    . 'Certifique-se de que as credenciais do banco (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD) estão corretas.';
            } elseif (strpos($errorMessage, 'Unknown column') !== false) {
                // Extrair nome da coluna do erro
                preg_match('/Unknown column \'([^\']+)\'/', $errorMessage, $matches);
                $coluna = $matches[1] ?? 'desconhecida';
                $friendlyMessage = "Erro: a coluna '{$coluna}' não existe na tabela users. Verifique a estrutura do banco.";
            } elseif (strpos($errorMessage, 'Duplicate entry') !== false || strpos($errorMessage, '1062') !== false || strpos($errorMessage, 'Integrity constraint violation') !== false) {
                $friendlyMessage = 'Este e-mail já está cadastrado. Faça login ou use outro e-mail.';
            } elseif (strpos($errorMessage, 'cannot be null') !== false || strpos($errorMessage, 'doesn\'t have a default value') !== false) {
                // Extrair nome da coluna do erro
                preg_match('/Column \'([^\']+)\'/', $errorMessage, $matches);
                $coluna = $matches[1] ?? 'desconhecida';
                $friendlyMessage = "Erro: o campo '{$coluna}' é obrigatório mas não foi preenchido. Por favor, verifique a estrutura do banco de dados.";
            } else {
                // Mostrar erro completo em desenvolvimento, mas mensagem genérica em produção
                if (config('app.debug')) {
                    $friendlyMessage = 'Erro ao criar conta: ' . $errorMessage;
                } else {
                    // Verificar se é um erro de conexão genérico
                    if (strpos($errorMessage, 'SQLSTATE') !== false) {
                        $friendlyMessage = 'Erro de conexão com o banco de dados. Verifique as configurações no arquivo .env ou entre em contato com o suporte.';
                    } else {
                        $friendlyMessage = 'Erro ao criar conta. Por favor, tente novamente ou entre em contato com o suporte.';
                    }
                }
            }
            
            return back()->withErrors(['email' => $friendlyMessage])->withInput($req->except('password'));
            
        } catch (\Exception $e) {
            // Outros erros
            $errorMessage = 'Erro ao criar conta: ' . $e->getMessage();
            
            // Log do erro
            if (function_exists('logger')) {
                logger()->error('Erro ao registrar usuário', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            return back()->withErrors(['email' => $errorMessage])->withInput($req->except('password'));
        }
    }

    /* ============================
       SHOW FORGOT PASSWORD
    ============================ */
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    /* ============================
       FORGOT PASSWORD
    ============================ */
    public function forgotPassword(Request $req)
    {
        $req->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
        ]);

        try {
            // Verificar se o usuário existe usando Eloquent (necessário para Password::sendResetLink)
            $user = \App\Models\User::where('email', $req->email)->first();
            
            if (!$user) {
                // Por segurança, não revelar se o email existe ou não
                return back()->with('status', 'Se o e-mail estiver cadastrado, você receberá um link de recuperação.');
            }

            // Verificar se o usuário está bloqueado
            if ($user->is_blocked) {
                return back()->withErrors(['email' => 'Esta conta está bloqueada. Entre em contato com o suporte.']);
            }

            // Usar o sistema de reset de senha do Laravel
            $status = Password::sendResetLink(
                $req->only('email')
            );

            // Verificar status do envio
            if ($status === Password::RESET_LINK_SENT || $status === 'passwords.sent') {
                Log::info('Link de recuperação de senha enviado', ['email' => $req->email]);
                return back()->with('status', 'Link de recuperação enviado para seu e-mail! Verifique sua caixa de entrada e a pasta de spam.');
            } elseif ($status === Password::INVALID_USER || $status === 'passwords.user') {
                // Por segurança, não revelar se o email existe
                return back()->with('status', 'Se o e-mail estiver cadastrado, você receberá um link de recuperação.');
            } else {
                Log::warning('Falha ao enviar link de recuperação', [
                    'status' => $status,
                    'email' => $req->email
                ]);
                return back()->withErrors(['email' => 'Não foi possível enviar o link de recuperação. Verifique se o e-mail está correto ou tente novamente mais tarde.'])->withInput($req->only('email'));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar link de recuperação de senha', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $req->email
            ]);
            return back()->withErrors(['email' => 'Erro ao processar solicitação. Verifique a configuração de e-mail do sistema.']);
        }
    }

    /* ============================
       SHOW RESET PASSWORD
    ============================ */
    public function showResetPassword(Request $req, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $req->email
        ]);
    }

    /* ============================
       RESET PASSWORD
    ============================ */
    public function resetPassword(Request $req)
    {
        $req->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', 'min:6'],
        ], [
            'token.required' => 'Token de recuperação é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Por favor, insira um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ]);

        try {
            // Usar o sistema de reset de senha do Laravel
            $status = Password::reset(
                $req->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    // Verificar se o usuário está bloqueado
                    if ($user->is_blocked) {
                        throw new \Exception('Conta bloqueada');
                    }
                    
                    // Atualizar a senha do usuário
                    $user->password = Hash::make($password);
                    $user->save();
                }
            );

            // Verificar status do reset
            if ($status === Password::PASSWORD_RESET || $status === 'passwords.reset') {
                return redirect()->route('login')->with('status', 'Senha alterada com sucesso! Faça login com sua nova senha.');
            } elseif ($status === Password::INVALID_TOKEN || $status === 'passwords.token') {
                return back()->withErrors(['email' => 'Token inválido ou expirado. Solicite um novo link de recuperação.'])->withInput($req->only('email'));
            } elseif ($status === Password::INVALID_USER || $status === 'passwords.user') {
                return back()->withErrors(['email' => 'E-mail não encontrado. Verifique o e-mail informado.'])->withInput($req->only('email'));
            } else {
                Log::warning('Status de reset de senha desconhecido', ['status' => $status, 'email' => $req->email]);
                return back()->withErrors(['email' => 'Token expirado ou inválido. Solicite um novo link de recuperação.'])->withInput($req->only('email'));
            }
        } catch (\Exception $e) {
            Log::error('Erro ao redefinir senha: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'email' => $req->email
            ]);
            
            if (strpos($e->getMessage(), 'bloqueada') !== false) {
                return back()->withErrors(['email' => 'Esta conta está bloqueada. Entre em contato com o suporte.']);
            }
            
            return back()->withErrors(['email' => 'Erro ao processar solicitação. Verifique se o link está correto e não expirou.']);
        }
    }

    /**
     * Generate secure API Secret
     * Format: sk_ + 51 caracteres alfanuméricos (mantendo maiúsculas e minúsculas)
     */
    private function generateSecureApiSecret(): string
    {
        // Gerar 51 caracteres alfanuméricos aleatórios (mantendo maiúsculas e minúsculas)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 51;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'sk_' . $randomString;
    }

    /**
     * Generate secure API Public Key
     * Format: pk_ + 51 caracteres alfanuméricos (mantendo maiúsculas e minúsculas)
     */
    private function generateSecureApiPublicKey(): string
    {
        // Gerar 51 caracteres alfanuméricos aleatórios (mantendo maiúsculas e minúsculas)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 51;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'pk_' . $randomString;
    }
}
