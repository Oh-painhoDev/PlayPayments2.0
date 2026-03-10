<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MultiGatewayConfig;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MultiGatewayController extends Controller
{
    /**
     * Show multi-gateway configuration page
     */
    public function index()
    {
        $config = MultiGatewayConfig::getGlobal();
        $gateways = PaymentGateway::where('is_active', true)->get();
        $users = User::whereNotIn('role', ['admin', 'gerente'])->orderBy('name')->get();
        
        return view('admin.multi-gateway.index', compact('config', 'gateways', 'users'));
    }
    
    /**
     * Update multi-gateway configuration
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'is_enabled' => 'required|boolean',
                'mode' => 'required|in:global,specific_users,all_except',
                'selected_gateways' => 'nullable|array',
                'selected_gateways.*' => 'exists:payment_gateways,id',
                'selected_users' => 'nullable|array',
                'selected_users.*' => 'exists:users,id',
            ]);
            
            $config = MultiGatewayConfig::getGlobal();
            
            $config->update([
                'is_enabled' => $request->boolean('is_enabled'),
                'mode' => $request->mode,
                'selected_gateways' => $request->selected_gateways ?? [],
                'selected_users' => $request->selected_users ?? [],
            ]);
            
            Log::info('Multi-gateway configuration updated', [
                'config' => $config->toArray()
            ]);
            
            return redirect()->route('admin.multi-gateway.index')
                ->with('success', 'Configuração Multi-Liquidante atualizada com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Error updating multi-gateway config: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar configuração: ' . $e->getMessage()]);
        }
    }
}
