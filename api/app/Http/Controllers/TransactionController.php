<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Venda;
use App\Models\Transacao;
use App\Models\Cliente;

class TransactionController extends Controller
{
    /**
     * Lista todas as transações do usuário (Cobranças)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Venda::where('user_id', $user->id)
            ->with(['transacao' => function($q) {
                $q->with('cliente');
            }]);

        // Filtro de busca
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('gateway_transaction_id', 'like', "%{$search}%")
                  ->orWhere('external_ref', 'like', "%{$search}%")
                  ->orWhereHas('transacao.cliente', function($c) use ($search) {
                      $c->where('nome', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro de status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filtro de data
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->whereDate('criado_em', '>=', $request->data_inicio);
        }
        if ($request->has('data_fim') && $request->data_fim) {
            $query->whereDate('criado_em', '<=', $request->data_fim);
        }

        $vendas = $query->orderBy('criado_em', 'desc')->paginate(20);

        // Calcula métricas
        $metricas = [
            'valor_liquido' => Venda::where('user_id', $user->id)
                ->where('status', 'pago')
                ->whereDate('criado_em', today())
                ->sum('valor_liquido') ?? 0,
            'faturamento' => Venda::where('user_id', $user->id)
                ->where('status', 'pago')
                ->sum('valor_liquido') ?? 0,
            'faturamento_previsto' => Venda::where('user_id', $user->id)
                ->where('status', 'pendente')
                ->sum('valor_bruto') ?? 0,
            'vendas_pendentes' => Venda::where('user_id', $user->id)
                ->where('status', 'pendente')
                ->sum('valor_bruto') ?? 0,
            'ticket_medio' => Venda::where('user_id', $user->id)
                ->where('status', 'pago')
                ->avg('valor_bruto') ?? 0,
            'numero_cobrancas' => Venda::where('user_id', $user->id)
                ->where('status', 'pago')
                ->count(),
            'reembolsos' => Venda::where('user_id', $user->id)
                ->whereHas('transacao', function($q) {
                    $q->where('status', 'reembolsado');
                })
                ->sum('valor_bruto') ?? 0,
            'reembolsos_count' => Venda::where('user_id', $user->id)
                ->whereHas('transacao', function($q) {
                    $q->where('status', 'reembolsado');
                })
                ->count(),
            'chargebacks' => 0, // Implementar quando tiver essa funcionalidade
            'chargebacks_count' => 0,
            'cancelados' => Venda::where('user_id', $user->id)
                ->where('status', 'falhou')
                ->sum('valor_bruto') ?? 0,
            'cancelados_count' => Venda::where('user_id', $user->id)
                ->where('status', 'falhou')
                ->count(),
            'nao_autorizado' => 0, // Implementar quando tiver essa funcionalidade
            'nao_autorizado_count' => 0,
        ];

        return view('public.transactions.index', compact('vendas', 'metricas'));
    }

    /**
     * Mostra detalhes de uma transação específica
     */
    public function show($transactionId)
    {
        $user = auth()->user();
        
        // Busca a venda pelo ID (pode ser venda_id ou gateway_transaction_id)
        $venda = Venda::where('id', $transactionId)
            ->where('user_id', $user->id)
            ->first();
        
        // Se não encontrar pelo ID, tenta pelo gateway_transaction_id
        if (!$venda) {
            // Busca na tabela transacoes pelo gateway_transaction_id
            $transacao = Transacao::where('id', $transactionId)->first();
            if ($transacao) {
                // Tenta encontrar a venda relacionada
                $venda = Venda::where('user_id', $user->id)
                    ->where('valor_bruto', $transacao->valor)
                    ->orderBy('criado_em', 'desc')
                    ->first();
            }
        }
        
        if (!$venda) {
            abort(404, 'Transação não encontrada');
        }
        
        // Busca dados do cliente relacionado
        $cliente = null;
        $transacao = null;
        
        // Busca transação relacionada (primeiro pela venda_id, depois por outros critérios)
        $transacao = Transacao::where('venda_id', $venda->id)->first();
        
        if (!$transacao) {
            // Fallback: busca por outros critérios
            $acquirerName = 'shield';
            if ($venda->adquirente_id) {
                $acquirer = \App\Models\Acquirer::find($venda->adquirente_id);
                if ($acquirer) {
                    $acquirerName = $acquirer->name;
                }
            }
            
            $transacao = Transacao::where('adquirente', $acquirerName)
                ->where('valor', $venda->valor_bruto)
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        if ($transacao && $transacao->cliente_id) {
            $cliente = Cliente::find($transacao->cliente_id);
        }
        
        // Busca dados da company do usuário (para mostrar no detalhe)
        $pj = DB::table('user_pj')->where('user_id', $user->id)->first();
        $endereco = DB::table('enderecos')->where('user_id', $user->id)->first();
        
        // Usa external_ref da venda ou gera um
        $externalRef = $venda->external_ref ?? "tx_{$venda->id}";
        $gatewayTransactionId = $venda->gateway_transaction_id ?? ($transacao->gateway_transaction_id ?? null);
        
        return view('public.transactions.show', compact('venda', 'transacao', 'cliente', 'pj', 'endereco', 'externalRef', 'gatewayTransactionId'));
    }
}

