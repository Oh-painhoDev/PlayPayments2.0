<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class CnpjController extends Controller
{
    public function buscar($cnpj)
    {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) !== 14) {
            return response()->json(['erro' => 'CNPJ inválido'], 400);
        }

        $url = "https://brasilapi.com.br/api/cnpj/v1/$cnpj";

        $json = @file_get_contents($url);

        if (!$json) {
            return response()->json(['erro' => 'Falha ao consultar CNPJ'], 500);
        }

        $dados = json_decode($json);

        if (isset($dados->error)) {
            return response()->json(['erro' => 'CNPJ não encontrado'], 404);
        }

        return response()->json($dados);
    }
}
