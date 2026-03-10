<?php

namespace App\Http\Controllers;

use App\Models\AstrofyIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AstrofyController extends Controller
{
    /**
     * Lista todas as integrações Astrofy do usuário
     */
    public function index()
    {
        $user = Auth::user();
        $integrations = AstrofyIntegration::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Gerar base_url automaticamente
        $baseUrl = rtrim(config('app.url'), '/') . '/api';
        
        // Verificar se tem gateway configurado
        $hasGateway = $user->assignedGateway !== null;

        return view('integracoes.astrofy.index', compact('integrations', 'baseUrl', 'hasGateway'));
    }

    /**
     * Salva nova integração
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Verificar se usuário tem gateway configurado
        if (!$user->assignedGateway) {
            return redirect()->back()
                ->withErrors(['error' => 'Você precisa configurar um gateway de pagamento primeiro.'])
                ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Gerar base_url automaticamente
        $baseUrl = rtrim(config('app.url'), '/') . '/api';

        AstrofyIntegration::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'gateway_key' => '', // Será preenchido quando o usuário registrar na Astrofy
            'base_url' => $baseUrl,
            'payment_types' => ['PIX'],
            'is_active' => true,
        ]);

        return redirect()->route('integracoes.astrofy.index')
            ->with('success', 'Integração criada! Agora adicione na Astrofy usando as informações abaixo.');
    }

    /**
     * Atualiza integração existente
     */
    public function update(Request $request, $id)
    {
        $integration = AstrofyIntegration::where('user_id', Auth::id())
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'gateway_key' => 'nullable|string|max:255', // Opcional - pode ser preenchido depois
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $integration->update([
            'name' => $request->name,
            'gateway_key' => $request->gateway_key ?? $integration->gateway_key,
        ]);

        return redirect()->route('integracoes.astrofy.index')
            ->with('success', 'Integração atualizada com sucesso!');
    }

    /**
     * Remove integração
     */
    public function destroy($id)
    {
        $integration = AstrofyIntegration::where('user_id', Auth::id())
            ->findOrFail($id);

        $integration->delete();

        return redirect()->route('integracoes.astrofy.index')
            ->with('success', 'Integração Astrofy removida com sucesso!');
    }
}
