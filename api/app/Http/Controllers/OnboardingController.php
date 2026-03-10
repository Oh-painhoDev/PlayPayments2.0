<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index()
    {
        $user_id = Auth::id();
        if (!$user_id) return redirect()->route('login');

        $user = DB::table('users')->where('id', $user_id)->first();
        
        // Extrair documento e dados de endereço da tabela users
        $document = $user->document ?? null;
        
        // Extrair dados do endereço (formato: "Rua, Número - Bairro - Complemento")
        $endereco = null;
        if ($user->address) {
            // Tentar separar rua, número, bairro e complemento do campo address
            $addressParts = explode(', ', $user->address, 2);
            $rua = $addressParts[0] ?? '';
            $resto = $addressParts[1] ?? '';
            
            // Separar número, bairro e complemento (formato: "Número - Bairro - Complemento")
            $parts = explode(' - ', $resto);
            $numero = $parts[0] ?? '';
            $bairro = $parts[1] ?? '';
            $complemento = $parts[2] ?? null;
            
            $endereco = (object) [
                'cep' => $user->cep ?? '',
                'rua' => $rua,
                'numero' => $numero,
                'bairro' => $bairro,
                'cidade' => $user->city ?? '',
                'estado' => $user->state ?? '',
                'complemento' => $complemento,
            ];
        } else {
            $endereco = (object) [
                'cep' => $user->cep ?? '',
                'rua' => '',
                'numero' => '',
                'bairro' => '',
                'cidade' => $user->city ?? '',
                'estado' => $user->state ?? '',
                'complemento' => '',
            ];
        }

        return view('auth.onboarding', [
            'user' => $user,
            'document' => $document,
            'endereco' => $endereco
        ]);
    }

    public function save(Request $req)
    {
        $user_id = Auth::id();
        if (!$user_id) return redirect()->route('login');

        // Validar dados básicos
        $rules = [
            'account_type' => 'required|in:pessoa_fisica,pessoa_juridica',
            'document' => 'required|string',
            'cep' => 'required|string',
            'rua' => 'required|string',
            'numero' => 'required|string',
            'bairro' => 'required|string',
            'cidade' => 'required|string',
            'estado' => 'required|string|max:2',
        ];

        // Se for pessoa jurídica, adicionar validação para campos específicos
        if ($req->account_type === 'pessoa_juridica') {
            $rules['business_type'] = 'required|string|max:100';
            $rules['business_sector'] = 'required|string|max:100';
        }

        $req->validate($rules, [
            'account_type.required' => 'O tipo de conta é obrigatório.',
            'document.required' => 'O documento é obrigatório.',
            'cep.required' => 'O CEP é obrigatório.',
            'rua.required' => 'A rua é obrigatória.',
            'numero.required' => 'O número é obrigatório.',
            'bairro.required' => 'O bairro é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'estado.required' => 'O estado é obrigatório.',
            'business_type.required' => 'O tipo de empresa é obrigatório para pessoa jurídica.',
            'business_sector.required' => 'O setor de atividade é obrigatório para pessoa jurídica.',
        ]);

        // Limpar documento (remover formatação)
        $document = preg_replace('/[^0-9]/', '', $req->document);
        $cep = preg_replace('/[^0-9]/', '', $req->cep);

        // Montar endereço completo
        $address = $req->rua . ', ' . $req->numero;
        if (!empty($req->bairro)) {
            $address .= ' - ' . $req->bairro;
        }
        if (!empty($req->complemento)) {
            $address .= ' - ' . $req->complemento;
        }

        // Preparar dados para atualização
        $updateData = [
            'account_type' => $req->account_type,
            'document' => $document,
            'cep' => $cep,
            'address' => $address,
            'city' => $req->cidade,
            'state' => strtoupper($req->estado),
        ];

        // Se for pessoa jurídica, adicionar campos específicos
        if ($req->account_type === 'pessoa_juridica') {
            $updateData['business_type'] = $req->business_type;
            $updateData['business_sector'] = $req->business_sector;
        } else {
            // Se for pessoa física, limpar campos de pessoa jurídica
            $updateData['business_type'] = null;
            $updateData['business_sector'] = null;
        }

        // Atualizar usuário na tabela users com todos os dados
        DB::table('users')->where('id', $user_id)->update($updateData);

        // Redirecionar para dashboard
        return redirect()->route('dashboard')->with('success', 'Cadastro completado com sucesso!');
    }

    private function getTenantFolder($user_id)
    {
        // Criar ou obter registro de verificação de documentos
        $record = DB::table('document_verifications')->where('user_id', $user_id)->first();

        if (!$record) {
            // Criar registro inicial
            DB::table('document_verifications')->insert([
                'user_id'      => $user_id,
                'status'       => 'pendente',
                'created_at'   => now(),
                'updated_at'   => now()
            ]);
        }

        // Usar user_id como parte do caminho da pasta (mais simples e direto)
        return 'users/' . $user_id . '/verification';
    }

    private function saveDoc($file, $user_id, $campo, $tenant)
    {
        if (!$file) return;

        // Mapear campos antigos para novos
        $campoMap = [
            'rg_frente' => 'front_document',
            'rg_verso' => 'back_document',
            'selfie_rg' => 'selfie_document',
            'contrato_social' => 'proof_address',
        ];

        $campoCorreto = $campoMap[$campo] ?? $campo;

        // Verificar se o registro existe, se não, criar
        $doc = DB::table('document_verifications')->where('user_id', $user_id)->first();
        if (!$doc) {
            DB::table('document_verifications')->insert([
                'user_id' => $user_id,
                'status' => 'pendente',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Gerar nome único
        $filename = time() . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Pasta final (público!)
        $path = "documents/$tenant/$campoCorreto/images/$filename";

        // Salvar no public/storage/documents/
        $file->storeAs("documents/$tenant/$campoCorreto/images", $filename, 'public');

        // URL completa do arquivo
        $fileUrl = asset("storage/documents/$tenant/$campoCorreto/images/$filename");

        // Atualizar BD
        DB::table('document_verifications')
            ->where('user_id', $user_id)
            ->update([
                $campoCorreto => $fileUrl,
                'status' => 'pendente',
                'rejection_reason' => null,
                'submitted_at' => now(),
                'updated_at' => now()
            ]);
    }
}
