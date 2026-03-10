<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\Transacao;
use Illuminate\Support\Facades\DB;

class CustomersController extends Controller
{
    /**
     * Lista todos os clientes do usuário
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Busca clientes através das transações do usuário
        $query = Cliente::whereHas('transacoes.venda', function($v) use ($user) {
            $v->where('user_id', $user->id);
        });

        // Filtro de busca
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%");
            });
        }

        $clientes = $query->with(['transacoes' => function($q) use ($user) {
            $q->whereHas('venda', function($v) use ($user) {
                $v->where('user_id', $user->id);
            })->with('venda');
        }])->orderBy('created_at', 'desc')->paginate(20);

        return view('public.customers', compact('clientes'));
    }

    /**
     * Exibe detalhes de um cliente específico
     */
    public function show($id)
    {
        $user = auth()->user();
        
        $cliente = Cliente::whereHas('transacoes.venda', function($v) use ($user) {
            $v->where('user_id', $user->id);
        })->with(['transacoes' => function($q) use ($user) {
            $q->whereHas('venda', function($v) use ($user) {
                $v->where('user_id', $user->id);
            })->with('venda')->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('public.customers.show', compact('cliente'));
    }
}

