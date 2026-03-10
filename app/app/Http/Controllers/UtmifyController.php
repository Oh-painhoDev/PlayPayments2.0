<?php

namespace App\Http\Controllers;

use App\Models\UtmifyIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UtmifyController extends Controller
{
    /**
     * Lista todas as integrações UTMify do usuário
     */
    public function index()
    {
        $integrations = UtmifyIntegration::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('integracoes.utmfy.index', compact('integrations'));
    }

    /**
     * Mostra formulário para criar nova integração
     */
    public function create()
    {
        return view('integracoes.utmfy.create');
    }

    /**
     * Salva nova integração
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'api_token' => 'required|string|max:255',
            'pixel_id' => 'nullable|string|max:255',
            'platform_name' => 'nullable|string|max:100',
            'trigger_on_payment' => 'boolean',
            'trigger_on_creation' => 'boolean',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        UtmifyIntegration::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'api_token' => $request->api_token,
            'pixel_id' => $request->pixel_id ?? null,
            'platform_name' => $request->platform_name ?? null,
            'trigger_on_payment' => $request->input('trigger_on_payment', false) ? true : false,
            'trigger_on_creation' => $request->input('trigger_on_creation', false) ? true : false,
            'is_active' => $request->input('is_active', false) ? true : false,
        ]);

        return redirect()->route('integracoes.utmfy.index')
            ->with('success', 'Integração UTMify criada com sucesso!');
    }

    /**
     * Atualiza integração existente
     */
    public function update(Request $request, $id)
    {
        $integration = UtmifyIntegration::where('user_id', Auth::id())
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'api_token' => 'required|string|max:255',
            'pixel_id' => 'nullable|string|max:255',
            'platform_name' => 'nullable|string|max:100',
            'trigger_on_payment' => 'nullable|boolean',
            'trigger_on_creation' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $integration->update([
            'name' => $request->name,
            'api_token' => $request->api_token,
            'pixel_id' => $request->pixel_id ?? null,
            'platform_name' => $request->platform_name ?? null,
            'trigger_on_payment' => $request->input('trigger_on_payment', false) ? true : false,
            'trigger_on_creation' => $request->input('trigger_on_creation', false) ? true : false,
            'is_active' => $request->input('is_active', false) ? true : false,
        ]);

        return redirect()->route('integracoes.utmfy.index')
            ->with('success', 'Integração UTMify atualizada com sucesso!');
    }

    /**
     * Remove integração
     */
    public function destroy($id)
    {
        $integration = UtmifyIntegration::where('user_id', Auth::id())
            ->findOrFail($id);

        $integration->delete();

        return redirect()->route('integracoes.utmfy.index')
            ->with('success', 'Integração UTMify removida com sucesso!');
    }
}
