<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingGate
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $rotaOnboarding = $request->is('onboarding') || $request->is('onboarding/*');

        // Buscar dados do usuário
        $userData = DB::table('users')->where('id', $user->id)->first();

        // PRIORIDADE 1: Verificar se informações básicas foram completadas
        if (empty($userData->name) || empty($userData->email) || empty($userData->password)) {
            // Se está tentando acessar dashboard, redireciona para onboarding com mensagem
            if (!$rotaOnboarding) {
                return redirect()->route('onboarding')->with('warning', 'Complete seu cadastro para acessar o dashboard.');
            }
            return $next($request);
        }

        // PRIORIDADE 2: Verificar se tipo de conta foi definido
        if (empty($userData->account_type)) {
            if (!$rotaOnboarding) {
                return redirect()->route('onboarding')->with('warning', 'Você precisa selecionar o tipo de conta para continuar.');
            }
            return $next($request);
        }

        // PRIORIDADE 3: Verificar se documento foi preenchido (document pode ser CPF ou CNPJ)
        if (empty($userData->document)) {
            if (!$rotaOnboarding) {
                return redirect()->route('onboarding')->with('warning', 'Você precisa informar seu CPF ou CNPJ para continuar.');
            }
            return $next($request);
        }

        // PRIORIDADE 4: Verificar se endereço foi preenchido
        if (empty($userData->cep) || empty($userData->address) || empty($userData->city) || empty($userData->state)) {
            if (!$rotaOnboarding) {
                return redirect()->route('onboarding')->with('warning', 'Você precisa completar seu endereço para continuar.');
            }
            return $next($request);
        }

        // Se chegou até aqui, significa que os dados básicos estão completos
        // Verificar documentos (opcional - não bloqueia acesso ao dashboard)
        // Por enquanto, só verificamos os dados básicos: name, email, password, account_type, document, endereço
        
        // Se todos os dados básicos estão completos, permite acesso
        // Os documentos podem ser enviados depois
        return $next($request);
    }
}
