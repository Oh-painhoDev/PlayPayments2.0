<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TaxaUsuario;

class FeesController extends Controller
{
    /**
     * Exibe as taxas do usuário
     */
    public function index()
    {
        $user = auth()->user();
        
        // Busca taxas do usuário ou cria com valores padrão
        $taxaUsuario = TaxaUsuario::firstOrCreate(
            ['user_id' => $user->id],
            [
                'saque_cripto_fixo' => 25.00,
                'saque_cripto_percentual' => 10.00,
                'saque_pix_fixo' => 5.00,
                'saque_pix_percentual' => 1.00,
                'pix_pago_fixo' => 1.95,
                'pix_pago_percentual' => 3.50,
            ]
        );

        return view('public.fees', compact('taxaUsuario'));
    }
}

