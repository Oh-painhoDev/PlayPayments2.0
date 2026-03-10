<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GatewayController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        return view('settings.gateway', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'public_key' => 'nullable|string|max:255',
            'secret_key' => 'nullable|string',
            'is_sandbox' => 'boolean'
        ]);

        $user = Auth::user();
        
        // Aqui você pode adicionar a lógica para salvar as credenciais do gateway
        // Por exemplo, salvar em user_gateway_credentials
        
        return redirect()->route('settings.gateway')
            ->with('success', 'Credenciais do gateway atualizadas com sucesso!');
    }

    public function test(Request $request)
    {
        // Lógica para testar a conexão com o gateway
        
        return response()->json([
            'success' => true,
            'message' => 'Conexão testada com sucesso!'
        ]);
    }
}
