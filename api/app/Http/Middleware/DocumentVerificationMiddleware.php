<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DocumentVerificationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if ($user->isAdminOrManager()) {
            return $next($request);
        }
        
        // Verificar diretamente do banco (sem cache) para garantir dados atualizados
        // O cache pode estar desatualizado após aprovação
        $verification = $user->documentVerification;
        $isApproved = $verification && $verification->isApproved();
        
        // Atualizar cache com o valor correto
        Cache::put("user_doc_approved_{$user->id}", $isApproved, 300);
        
        $allowedRoutes = [
            'dashboard',
            'documents.index',
            'documents.view',
            'logout'
        ];
        
        // Se está aprovado, sempre permitir acesso
        if ($isApproved) {
            return $next($request);
        }
        
        // Se não está aprovado e não está em uma rota permitida, bloquear
        if (!in_array($request->route()->getName(), $allowedRoutes)) {
            return redirect()->route('documents.index')
                ->with('error', 'Você precisa ter seus documentos aprovados para acessar esta página.');
        }
        
        return $next($request);
    }
}
