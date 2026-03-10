<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\Cliente;
use App\Models\Transacao;
use App\Helpers\AcquirerHelper;
use App\Services\Acquirers\ShieldService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BalanceController extends Controller
{
    public function addBalance(Request $request)
    {
        // ✅ Validação
        $request->validate([
            'valor' => 'required|numeric|min:5'
        ]);

        $valor = $request->input('valor');
        $user  = Auth::user();

        // ✅ Pega adquirente ativa (da tabela acquirers)
        $acquirer = AcquirerHelper::active();

        if (!$acquirer) {
            return redirect()->back()->with([
                'status' => 'erro',
                'message' => 'Nenhuma adquirente ativa configurada.'
            ]);
        }

        // ✅ Garante que existe registro em adquirentes (para foreign key)
        // A foreign key de vendas aponta para adquirentes, não acquirers
        $adquirente = DB::table('adquirentes')->where('id', $acquirer->id)->first();
        if (!$adquirente) {
            // Cria registro em adquirentes com mesmo ID
            DB::table('adquirentes')->insert([
                'id' => $acquirer->id,
                'nome' => $acquirer->display_name ?? $acquirer->name,
                'taxa_percentual' => null,
                'taxa_fixa' => null,
                'ativo' => $acquirer->active ? 1 : 0
            ]);
        }

        // ✅ Cria venda pendente na tabela vendas
        $venda = Venda::create([
            'user_id' => $user->id,
            'adquirente_id' => $acquirer->id, // Usa o ID que existe em ambas as tabelas
            'valor_bruto' => $valor,
            'valor_liquido' => null,
            'taxa_percentual_aplicada' => null,
            'taxa_fixa_aplicada' => null,
            'status' => 'pendente',
            'criado_em' => now()
        ]);

        // ✅ Cria cliente (usando dados do usuário)
        $cliente = Cliente::firstOrCreate(
            ['email' => $user->email],
            [
                'nome' => $user->nome,
                'telefone' => DB::table('user_pf')->where('user_id', $user->id)->value('telefone') ?? 
                             DB::table('user_pj')->where('user_id', $user->id)->value('responsavel_telefone') ?? null,
                'cpf' => DB::table('user_pf')->where('user_id', $user->id)->value('cpf') ?? null,
                'endereco' => $this->getEnderecoCompleto($user->id)
            ]
        );

        // ✅ Instancia serviço da adquirente
        $gateway = new ShieldService($acquirer);

        // ✅ Gera o PIX (passa o ID da venda para o externalRef)
        $res = $gateway->createPix($venda->id, $valor, $user);

        if ($res->failed()) {
            $venda->update(['status' => 'falhou']);
            return redirect()->back()->with([
                'status' => 'erro',
                'message' => 'Falha ao gerar PIX: ' . ($res->json()['message'] ?? 'Erro desconhecido')
            ]);
        }

        $json = $res->json();
        
        // Atualiza venda com gateway_transaction_id e external_ref (se as colunas existirem)
        try {
            $updateData = [];
            if (DB::getSchemaBuilder()->hasColumn('vendas', 'gateway_transaction_id')) {
                $updateData['gateway_transaction_id'] = $json['id'] ?? null;
            }
            if (DB::getSchemaBuilder()->hasColumn('vendas', 'external_ref')) {
                $updateData['external_ref'] = "tx_{$venda->id}";
            }
            if (!empty($updateData)) {
                $venda->update($updateData);
            }
        } catch (\Exception $e) {
            // Se der erro, continua sem atualizar esses campos
        }

        // ✅ Cria transação na tabela transacoes
        $transacaoData = [
            'cliente_id' => $cliente->id,
            'adquirente' => $acquirer->name,
            'chave_pix' => $json['pix']['copyAndPaste'] ?? $json['pix']['copyPasteKey'] ?? null,
            'qr_code' => $json['pix']['qrcode'] ?? $json['pix']['qrcodeBase64'] ?? null,
            'status' => $json['status'] ?? 'pendente',
            'valor' => $valor
        ];
        
        // Adiciona campos opcionais se existirem
        if (DB::getSchemaBuilder()->hasColumn('transacoes', 'gateway_transaction_id')) {
            $transacaoData['gateway_transaction_id'] = $json['id'] ?? null;
        }
        if (DB::getSchemaBuilder()->hasColumn('transacoes', 'external_ref')) {
            $transacaoData['external_ref'] = "tx_{$venda->id}";
        }
        if (DB::getSchemaBuilder()->hasColumn('transacoes', 'venda_id')) {
            $transacaoData['venda_id'] = $venda->id;
        }
        
        $transacao = Transacao::create($transacaoData);

        // ✅ Retorna com sucesso e dados do PIX
        return redirect()->back()->with([
            'status'  => 'sucesso',
            'message' => 'PIX gerado com sucesso! Escaneie o QR Code ou copie o código PIX para pagar.',
            'venda_id' => $venda->id,
            'transacao_id' => $transacao->id
        ]);
    }

    private function getEnderecoCompleto($userId)
    {
        $endereco = DB::table('enderecos')->where('user_id', $userId)->first();
        
        if (!$endereco) {
            return null;
        }

        return sprintf(
            "%s, %s - %s, %s - %s, %s",
            $endereco->rua ?? '',
            $endereco->numero ?? '',
            $endereco->bairro ?? '',
            $endereco->cidade ?? '',
            $endereco->estado ?? '',
            $endereco->cep ?? ''
        );
    }
}
