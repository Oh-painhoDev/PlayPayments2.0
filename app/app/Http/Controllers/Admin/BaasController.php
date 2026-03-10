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
                'withdrawal_fee' => 'nullable|numeric|min:0',
            ]);
            
            $gateway = $request->gateway;
            
            // Update in BaasCredential ONLY
            $baasCredential = BaasCredential::where('gateway', $gateway)->first();
            
            // Get is_default value from request (hidden input ensures it's always sent)
            $isDefault = $request->boolean('is_default');
            
            // If this is set as default, unset all others as default (but keep them active)
            if ($isDefault) {
                BaasCredential::where('gateway', '!=', $gateway)
                    ->update(['is_default' => false]);
            }
            
            $data = [
                'public_key' => $request->public_key ?: 'dummy_public_key',
                'secret_key' => $request->secret_key,
                'is_active' => true,
                'is_sandbox' => $request->boolean('is_sandbox'),
                'is_default' => $isDefault,
                'withdrawal_fee' => (float)($request->withdrawal_fee ?? 0.00),
            ];
            
            // Encrypt secret_key before saving
            $encryptedSecretKey = \Illuminate\Support\Facades\Crypt::encryptString($data['secret_key']);
            
            // Get withdrawal_fee value
            $withdrawalFee = (float)($data['withdrawal_fee'] ?? 0.00);
            
            // Remove withdrawal_fee from data array to avoid issues
            unset($data['withdrawal_fee']);
            $data['secret_key'] = $encryptedSecretKey;
            
            if ($baasCredential) {
                // First, ensure column exists
                try {
                    \Illuminate\Support\Facades\DB::statement(
                        "ALTER TABLE baas_credentials ADD COLUMN IF NOT EXISTS withdrawal_fee DECIMAL(10,2) DEFAULT 0.00 AFTER is_default"
                    );
                } catch (\Exception $e) {
                    // Column might already exist, try without IF NOT EXISTS
                    try {
                        \Illuminate\Support\Facades\DB::statement(
                            "ALTER TABLE baas_credentials ADD COLUMN withdrawal_fee DECIMAL(10,2) DEFAULT 0.00 AFTER is_default"
                        );
                    } catch (\Exception $e2) {
                        // Column exists, continue
                    }
                }
                
                // Update all fields except withdrawal_fee first
                \Illuminate\Support\Facades\DB::table('baas_credentials')
                    ->where('id', $baasCredential->id)
                    ->update($data);
                
                // Update withdrawal_fee separately using raw SQL
                try {
                    \Illuminate\Support\Facades\DB::statement(
                        "UPDATE baas_credentials SET withdrawal_fee = ? WHERE id = ?",
                        [$withdrawalFee, $baasCredential->id]
                    );
                } catch (\Exception $e) {
                    Log::warning('Could not update withdrawal_fee: ' . $e->getMessage());
                }
            } else {
                $data['gateway'] = $gateway;
                $data['created_at'] = now();
                $data['updated_at'] = now();
                
                // Ensure column exists before insert
                try {
                    \Illuminate\Support\Facades\DB::statement(
                        "ALTER TABLE baas_credentials ADD COLUMN IF NOT EXISTS withdrawal_fee DECIMAL(10,2) DEFAULT 0.00 AFTER is_default"
                    );
                } catch (\Exception $e) {
                    try {
                        \Illuminate\Support\Facades\DB::statement(
                            "ALTER TABLE baas_credentials ADD COLUMN withdrawal_fee DECIMAL(10,2) DEFAULT 0.00 AFTER is_default"
                        );
                    } catch (\Exception $e2) {
                        // Column exists
                    }
                }
                
                // Add withdrawal_fee back for insert
                $data['withdrawal_fee'] = $withdrawalFee;
                \Illuminate\Support\Facades\DB::table('baas_credentials')->insert($data);
            }
            
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
            } else if ($request->gateway === 'pluggou') {
                // Test PluggouCash connection - consultar saldo
                // PluggouCash usa apenas a URL de produção: https://api.pluggoutech.com/api
                $apiUrl = 'https://api.pluggoutech.com/api';
                    
                try {
                    $response = Http::timeout(10)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'X-Public-Key' => $baasCredentials->public_key,
                            'X-Secret-Key' => $baasCredentials->secret_key,
                        ])
                        ->get($apiUrl . '/withdrawals/balance');
                        
                    if ($response->successful()) {
                        $data = $response->json();
                        return response()->json([
                            'success' => true,
                            'message' => 'Conexão PluggouCash bem-sucedida! Saldo disponível: R$ ' . number_format(($data['balance'] ?? 0) / 100, 2, ',', '.')
                        ]);
                    } else {
                        $errorData = $response->json();
                        $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Credenciais inválidas ou gateway indisponível';
                        return response()->json([
                            'success' => false,
                            'error' => $errorMessage . ' (Status: ' . $response->status() . ')'
                        ]);
                    }
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Erro de conexão: ' . $e->getMessage()
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Erro ao testar conexão: ' . $e->getMessage()
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