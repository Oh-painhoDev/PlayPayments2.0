<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class CepController extends Controller
{
    public function buscar($cep)
    {
        $cep = preg_replace('/\D/', '', $cep);

        if (strlen($cep) !== 8) {
            return response()->json(['erro' => 'CEP inválido'], 400);
        }

        $url = "https://viacep.com.br/ws/$cep/json/";

        $json = @file_get_contents($url);

        if (!$json) {
            return response()->json(['erro' => 'Falha ao consultar CEP'], 500);
        }

        $dados = json_decode($json);

        if (isset($dados->erro)) {
            return response()->json(['erro' => 'CEP não encontrado'], 404);
        }

        return response()->json($dados);
    }
}
