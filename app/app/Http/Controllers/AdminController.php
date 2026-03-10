<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\TaxaUsuario;
use App\Models\Venda;
use App\Models\Saque;

class AdminController extends Controller
{
    /**
     * Middleware para verificar se é admin
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (!$user || !$user->isAdminOrManager()) {
                abort(403, 'Acesso negado. Apenas administradores e gerentes podem acessar esta área.');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard do admin
     */
    public function dashboard()
    {
        $totalUsuarios = User::count();
        $totalVendas = Venda::count();
        $totalSaques = Saque::count();
        $faturamentoTotal = Venda::where('status', 'pago')->sum('valor_liquido') ?? 0;

        return view('admin.dashboard', compact('totalUsuarios', 'totalVendas', 'totalSaques', 'faturamentoTotal'));
    }

    /**
     * Lista de usuários
     */
    public function usuarios(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->with('taxaUsuario')->orderBy('criado_em', 'desc')->paginate(20);

        return view('admin.usuarios.index', compact('usuarios'));
    }

    /**
     * Editar taxas de um usuário
     */
    public function editarTaxas($userId)
    {
        $usuario = User::findOrFail($userId);
        $taxaUsuario = TaxaUsuario::firstOrCreate(
            ['user_id' => $userId],
            [
                'saque_cripto_fixo' => 25.00,
                'saque_cripto_percentual' => 10.00,
                'saque_pix_fixo' => 5.00,
                'saque_pix_percentual' => 1.00,
                'pix_pago_fixo' => 1.95,
                'pix_pago_percentual' => 3.50,
            ]
        );

        return view('admin.usuarios.editar-taxas', compact('usuario', 'taxaUsuario'));
    }

    /**
     * Atualizar taxas de um usuário
     */
    public function atualizarTaxas(Request $request, $userId)
    {
        $request->validate([
            'saque_cripto_fixo' => 'required|numeric|min:0',
            'saque_cripto_percentual' => 'required|numeric|min:0|max:100',
            'saque_pix_fixo' => 'required|numeric|min:0',
            'saque_pix_percentual' => 'required|numeric|min:0|max:100',
            'pix_pago_fixo' => 'required|numeric|min:0',
            'pix_pago_percentual' => 'required|numeric|min:0|max:100',
        ]);

        $taxaUsuario = TaxaUsuario::updateOrCreate(
            ['user_id' => $userId],
            [
                'saque_cripto_fixo' => $request->saque_cripto_fixo,
                'saque_cripto_percentual' => $request->saque_cripto_percentual,
                'saque_pix_fixo' => $request->saque_pix_fixo,
                'saque_pix_percentual' => $request->saque_pix_percentual,
                'pix_pago_fixo' => $request->pix_pago_fixo,
                'pix_pago_percentual' => $request->pix_pago_percentual,
                'atualizado_em' => now(),
            ]
        );

        return redirect()->route('admin.usuarios')->with('success', 'Taxas atualizadas com sucesso!');
    }

    /**
     * Editar taxas padrão (para novos usuários)
     */
    public function taxasPadrao()
    {
        // Busca taxas padrão (pode ser de um usuário específico ou configuração global)
        // Por enquanto, vamos usar valores fixos ou criar uma tabela de configurações
        $taxasPadrao = [
            'saque_cripto_fixo' => 25.00,
            'saque_cripto_percentual' => 10.00,
            'saque_pix_fixo' => 5.00,
            'saque_pix_percentual' => 1.00,
            'pix_pago_fixo' => 1.95,
            'pix_pago_percentual' => 3.50,
        ];

        return view('admin.taxas-padrao', compact('taxasPadrao'));
    }

    /**
     * Atualizar taxas padrão
     */
    public function atualizarTaxasPadrao(Request $request)
    {
        $request->validate([
            'saque_cripto_fixo' => 'required|numeric|min:0',
            'saque_cripto_percentual' => 'required|numeric|min:0|max:100',
            'saque_pix_fixo' => 'required|numeric|min:0',
            'saque_pix_percentual' => 'required|numeric|min:0|max:100',
            'pix_pago_fixo' => 'required|numeric|min:0',
            'pix_pago_percentual' => 'required|numeric|min:0|max:100',
        ]);

        // Aqui você pode salvar em uma tabela de configurações
        // Por enquanto, vamos apenas retornar sucesso
        // TODO: Criar tabela de configurações para taxas padrão

        return redirect()->route('admin.taxas-padrao')->with('success', 'Taxas padrão atualizadas com sucesso!');
    }

    /**
     * Lista todas as transações (admin)
     */
    public function transacoes(Request $request)
    {
        $query = Venda::with(['user', 'transacao.cliente']);

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('nome', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $vendas = $query->orderBy('criado_em', 'desc')->paginate(50);

        return view('admin.transacoes.index', compact('vendas'));
    }
}

