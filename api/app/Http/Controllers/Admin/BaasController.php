<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserGatewayCredential;
use App\Models\PaymentGateway;
use App\Models\BaasCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BaasController extends Controller
{
    /**
     * Show BaaS configuration page
     */
    public function index()
    {
        // Get admin user
        $adminUser = User::where('role', 'admin')->first();
        
        // Get all gateways
        $gateways = PaymentGateway::where('is_active', true)->get();
        
        // Get credentials for each gateway
        $credentials = [];
        foreach ($gateways as $gateway) {
            $credential = UserGatewayCredential::where('user_id', $adminUser->id)
                ->where('gateway_id', $gateway->id)
                ->first();
                
            $credentials[$gateway->id] = $credential;
        }
        
        // Get BaaS credentials
        $baasCredentials = BaasCredential::all()->keyBy('gateway');
        
        return view('admin.baas.index', compact('gateways', 'credentials', 'baasCredentials'));
    }
    
    /**
     * Update BaaS credentials
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'gateway' => 'required|string',
                'public_key' => 'nullable|string|max:255',
                'secret_key' => 'required|string|max:255',
                'is_sandbox' => 'required|boolean',
                'is_default' => 'boolean',
            ]);
            
            $gateway = $request->gateway;
            
            // If this is set as default, unset all others as default (but keep them active)
            if ($request->has('is_default') && $request->boolean('is_default')) {
                BaasCredential::where('gateway', '!=', $gateway)
                    ->update(['is_default' => false]);
            }
            
            // Update in BaasCredential ONLY
            BaasCredential::updateOrCreate(
                [
                    'gateway' => $gateway,
                ],
                [
                    'public_key' => $request->public_key ?: 'dummy_public_key',
                    'secret_key' => $request->secret_key,
                    'is_active' => true,
                    'is_sandbox' => $request->boolean('is_sandbox'),
                    'is_default' => $request->boolean('is_default', false),
                ]
            );
            
            return back()->with('success', 'Credenciais do ' . ucfirst($gateway) . ' BaaS atualizadas com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar credenciais BaaS: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao salvar credenciais: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test BaaS connection
     */
    public function test(Request $request)
    {
        try {
            $request->validate([
                'gateway' => 'required|string',
            ]);
            
            // Get BaaS credentials
            $baasCredentials = BaasCredential::where('gateway', $request->gateway)
                ->where('is_active', true)
                ->first();
                
            if (!$baasCredentials) {
                return response()->json([
                    'success' => false,
                    'error' => 'Credenciais BaaS não configuradas'
                ]);
            }
            
            // Test connection based on gateway type
            if ($request->gateway === 'strikecash') {
                // Test connection with a simple request for StrikeCash
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'PixBolt-Admin-Test/1.0',
                        'x-secret-key' => $baasCredentials->secret_key,
                        'x-public-key' => $baasCredentials->public_key,
                    ])
                    ->get('https://srv.strikecash.com.br/v1/health');
                    
                if ($response->successful()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Conexão BaaS bem-sucedida!'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Credenciais inválidas ou gateway indisponível'
                    ]);
                }
            } else if ($request->gateway === 'cashtime') {
                // Test connection with a simple request for Cashtime
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'User-Agent' => 'PixBolt-Admin-Test/1.0',
                        'x-authorization-key' => $baasCredentials->secret_key
                    ])
                    ->get('https://api.cashtime.com.br/health');
                    
                if ($response->successful()) {
                    $data = $response->json();
                    
                    // Verificar se o status é "ok"
                    if (isset($data['status']) && $data['status'] === 'ok') {
                        return response()->json([
                            'success' => true,
                            'message' => 'Conexão BaaS bem-sucedida! Credenciais válidas.'
                        ]);
                    } else {
                        return response()->json([
                            'success' => false,
                            'error' => 'Resposta inesperada do gateway'
                        ]);
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Credenciais inválidas ou gateway indisponível'
                    ]);
                }
            } else if ($request->gateway === 'e2bank') {
                // Test E2 Bank connection - simply return success if credentials are configured
                // Actual OAuth2 test happens when creating transactions
                if ($baasCredentials && $baasCredentials->public_key && $baasCredentials->secret_key) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Credenciais E2 Bank configuradas! A conexão OAuth2 será testada ao criar transação.'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Credenciais E2 Bank não estão completas'
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Tipo de gateway não suportado'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão BaaS: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Toggle BaaS active status
     */
    public function toggleActive(Request $request)
    {
        try {
            $request->validate([
                'gateway' => 'required|string',
            ]);
            
            // Get BaaS credentials
            $baasCredentials = BaasCredential::where('gateway', $request->gateway)->first();
            
            if (!$baasCredentials) {
                return back()->withErrors(['error' => 'Credenciais BaaS não encontradas']);
            }
            
            // Toggle active status - ALLOW MULTIPLE ACTIVE BaaS
            $baasCredentials->is_active = !$baasCredentials->is_active;
            $baasCredentials->save();
            
            $status = $baasCredentials->is_active ? 'ativado' : 'desativado';
            
            return back()->with('success', 'BaaS ' . ucfirst($request->gateway) . ' ' . $status . ' com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status do BaaS: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao alternar status: ' . $e->getMessage()]);
        }
    }
}