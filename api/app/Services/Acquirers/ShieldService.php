<?php

namespace App\Services\Acquirers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ShieldService
{
    protected $api_url;
    protected $public_key;
    protected $secret_key;

    public function __construct($config)
    {
        $this->api_url    = rtrim($config->api_url, '/') . '/v1/transactions';
        $this->public_key = $config->public_key;
        $this->secret_key = $config->secret_key;
    }

    protected function auth()
    {
        return base64_encode($this->public_key . ':' . $this->secret_key);
    }


    private function getUserFullData($user)
    {
        $pj = DB::table('user_pj')->where('user_id', $user->id)->first();
        $endereco = DB::table('enderecos')->where('user_id', $user->id)->first();

        return [
            "name"  => $pj->razao_social ?? $user->nome,
            "email" => $pj->responsavel_email ?? $user->email,

            "document" => [
                "type"   => $pj ? "cnpj" : "cpf",
                "number" => $pj->cnpj ?? $pj->responsavel_cpf ?? "00000000000"
            ],

            "phone" => $pj->responsavel_telefone ?? "11999999999",

            "address" => [
                "street"   => $endereco->rua ?? "Nao informado",
                "number"   => $endereco->numero ?? "0",
                "streetNumber" => $endereco->numero ?? "0", // Campo obrigatório para shipping
                "district" => $endereco->bairro ?? "Nao informado",
                "neighborhood" => $endereco->bairro ?? "Nao informado", // Campo obrigatório para shipping
                "zipcode"  => $endereco->cep ?? "00000000",
                "zipCode" => $endereco->cep ?? "00000000", // Campo obrigatório para shipping
                "city"     => $endereco->cidade ?? "Nao informado",
                "state"    => $endereco->estado ?? "SP",
                "country" => "BR" // Campo obrigatório para shipping
            ]
        ];
    }


    public function createPix($transactionId, $amount, $user)
    {
        $u = $this->getUserFullData($user);

        $payload = [
            "amount"        => intval($amount * 100),
            "currency"      => "BRL",
            "paymentMethod" => "pix",

            "pix" => [
                "expiresIn" => 3600
            ],

            "items" => [
                [
                    "title"     => "Créditos na carteira",
                    "quantity"  => 1,
                    "unitPrice" => intval($amount * 100),
                    "tangible"  => false
                ]
            ],

            "customer" => [
                "name"     => $u["name"],
                "email"    => $u["email"],
                "phone"    => $u["phone"],
                "document" => $u["document"]
            ],

            "shipping" => [
                "fee" => 0, // Valor do frete em centavos (obrigatório, 0 para produtos digitais)
                "address" => [
                    "street" => $u["address"]["street"],
                    "streetNumber" => $u["address"]["streetNumber"] ?? $u["address"]["number"],
                    "neighborhood" => $u["address"]["neighborhood"] ?? $u["address"]["district"],
                    "zipCode" => $u["address"]["zipCode"] ?? $u["address"]["zipcode"],
                    "city" => $u["address"]["city"],
                    "state" => $u["address"]["state"],
                    "country" => $u["address"]["country"] ?? "BR" // Obrigatório
                ]
            ],

            "postbackUrl" => url("/shield/callback"),
            "externalRef" => "tx_" . $transactionId,
            "ip"          => request()->ip()
        ];

        $res = Http::withHeaders([
            "Authorization" => "Basic " . $this->auth(),
            "Accept"        => "application/json",
            "Content-Type"  => "application/json"
        ])->post($this->api_url, $payload);

        return $res;
    }


    public function checkPayment($gatewayId)
    {
        return Http::withHeaders([
            "Authorization" => "Basic " . $this->auth()
        ])->get($this->api_url . '/' . $gatewayId);
    }
}
