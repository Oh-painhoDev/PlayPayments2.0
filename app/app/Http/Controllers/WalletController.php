<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Saque;
use App\Models\TaxaUsuario;
use App\Models\User;

class WalletController extends Controller
{
    /**
     * Exibe a página da carteira
     */
    public function index()
    {
        $user = auth()->user();
        
        // Busca taxas do usuário ou cria com valores padrão
        $taxaUsuario = TaxaUsuario::firstOrCreate(
            ['user_id' => $user->id],
            [
                'saque_pix_fixo' => 5.00,
                'saque_pix_percentual' => 1.00,
            ]
        );

        // Busca histórico de saques
        $saques = Saque::where('user_id', $user->id)
            ->orderBy('criado_em', 'desc')
            ->limit(10)
            ->get();

        return view('public.wallet', compact('user', 'taxaUsuario', 'saques'));
    }

    /**
     * Processa solicitação de saque
     */
    public function sacar(Request $request)
    {
        $request->validate([
            'valor' => 'required|numeric|min:10',
        ]);

        $user = auth()->user();
        $valor = $request->valor;

        // Busca taxas do usuário
        $taxaUsuario = TaxaUsuario::firstOrCreate(
            ['user_id' => $user->id],
            [
                'saque_pix_fixo' => 5.00,
                'saque_pix_percentual' => 1.00,
            ]
        );

        // Calcula taxa: R$ 5,00 + 1%
        $taxaFixa = $taxaUsuario->saque_pix_fixo ?? 5.00;
        $taxaPercentual = $taxaUsuario->saque_pix_percentual ?? 1.00;
        $taxa = $taxaFixa + ($valor * ($taxaPercentual / 100));
        $valorTotal = $valor + $taxa;

        // Verifica se tem saldo suficiente
        if ($user->saldo < $valorTotal) {
            return back()->withErrors(['valor' => 'Saldo insuficiente. Você precisa de R$ ' . number_format($valorTotal, 2, ',', '.') . ' (valor + taxa).']);
        }

        // Cria solicitação de saque
        $saque = Saque::create([
            'user_id' => $user->id,
            'valor' => $valor,
            'status' => 'solicitado',
            'criado_em' => now(),
        ]);

        // Debita o saldo do usuário
        $user->saldo -= $valorTotal;
        $user->total_sacado += $valor;
        $user->save();

        return redirect()->route('wallet.index')->with('success', 'Saque solicitado com sucesso! Valor: R$ ' . number_format($valor, 2, ',', '.') . ' | Taxa: R$ ' . number_format($taxa, 2, ',', '.'));
    }
}

