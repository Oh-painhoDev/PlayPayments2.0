<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\UserGatewayCredential;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Models\GatewayFee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class GatewayController extends Controller
{
    /**
     * Show gateways management page
     */
    public function index(Request $request)
    {
        // Get active status filter (default to active)
        $status = $request->input('status', 'active');
        
        // Get gateways based on status
        $query = PaymentGateway::where('is_active', $status === 'active');
        
        // Verificar se as colunas de hierarquia existem (após migration)
        $hasHierarchyColumns = Schema::hasColumn('payment_gateways', 'is_base');
        
        if ($hasHierarchyColumns) {
            $query->with(['parentGateway', 'subGateways'])
                  ->orderBy('is_default', 'desc')
                  ->orderBy('is_base', 'desc')
                  ->orderBy('name');
        } else {
            $query->orderBy('is_default', 'desc')
                  ->orderBy('name');
        }
        
        $gateways = $query->get();
        
        // Separar adquirentes base e sub-adquirentes (se colunas existirem)
        if ($hasHierarchyColumns) {
            $baseAcquirers = $gateways->where('is_base', true);
            $whitelabels = $gateways->where('is_whitelabel', true);
            $regularGateways = $gateways->where('is_base', false)->where('is_whitelabel', false);
        } else {
            // Se migration não foi executada, tratar todos como gateways regulares
            $baseAcquirers = collect();
            $whitelabels = collect();
            $regularGateways = $gateways;
        }
        
        // Get admin user
        $adminUser = User::where('role', 'admin')->first();
        
        // OPTIMIZED: Batch load all credentials (both admin and global) in single query
        $gatewayIds = $gateways->pluck('id');
        $adminCredentials = UserGatewayCredential::where('user_id', $adminUser->id)
            ->whereIn('gateway_id', $gatewayIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('gateway_id');
        
        // Also get global credentials (user_id = null)
        $globalCredentials = UserGatewayCredential::whereNull('user_id')
            ->whereIn('gateway_id', $gatewayIds)
            ->where('is_active', true)
            ->get()
            ->keyBy('gateway_id');
        
        // Map credentials to gateway IDs (prefer admin over global)
        $credentials = [];
        foreach ($gateways as $gateway) {
            $credentials[$gateway->id] = $adminCredentials->get($gateway->id) ?? $globalCredentials->get($gateway->id);
        }
        
        return view('admin.gateways.index', compact('gateways', 'credentials', 'status', 'baseAcquirers', 'whitelabels', 'regularGateways', 'hasHierarchyColumns'));
    }
    
    /**
     * Create a new gateway
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:payment_gateways,slug',
                'api_url' => 'nullable|string|max:255',
                'is_default' => 'nullable|in:0,1,true,false,on,off',
                'gateway_type' => 'required|string|in:hopy,splitwave,sharkgateway,arkama,versell,getpay,cashtime,e2bank,pluggou',
                'is_base' => 'nullable|boolean',
                'is_whitelabel' => 'nullable|boolean',
                'parent_gateway_id' => 'nullable|exists:payment_gateways,id',
                'webhook_name' => 'nullable|string|max:255',
            ]);
            
            // Create new gateway
            $gateway = new PaymentGateway();
            $gateway->name = $request->name;
            $gateway->slug = $request->slug;
            $gateway->api_url = $request->api_url;
            $gateway->is_active = true;
            // Convert to boolean safely - accepts 0, 1, 'true', 'false', 'on', 'off', or missing (defaults to false)
            $gateway->is_default = filter_var($request->input('is_default'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            
            // Hierarquia: Base ou Whitelabel
            $gateway->is_base = filter_var($request->input('is_base'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $gateway->is_whitelabel = filter_var($request->input('is_whitelabel'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            
            // Se for whitelabel, precisa ter parent_gateway_id
            if ($gateway->is_whitelabel) {
                if (!$request->parent_gateway_id) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['parent_gateway_id' => 'Sub-adquirente (whitelabel) precisa ter uma adquirente base selecionada.']);
                }
                $gateway->parent_gateway_id = $request->parent_gateway_id;
                
                // Webhook name é obrigatório para whitelabels
                if ($request->webhook_name) {
                    $gateway->webhook_name = strtolower(trim($request->webhook_name));
                } else {
                    // Se não informado, usar o slug
                    $gateway->webhook_name = strtolower($gateway->slug);
                }
            } else {
                // Se for base, não pode ter parent
                $gateway->parent_gateway_id = null;
                $gateway->webhook_name = null;
            }
            
            // Apenas PIX por padrão
            $gateway->supported_methods = ['pix'];
            
            // Set config based on gateway type
            if ($request->gateway_type === 'hopy') {
                $gateway->config = [
                    'auth_type' => 'basic',
                    'transaction_endpoint' => 'transactions',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'hopy'
                ];
            } else if ($request->gateway_type === 'splitwave') {
                $gateway->config = [
                    'auth_type' => 'header_key',
                    'transaction_endpoint' => 'transactions',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'splitwave'
                ];
            } else if ($request->gateway_type === 'sharkgateway') {
                $gateway->config = [
                    'auth_type' => 'basic',
                    'transaction_endpoint' => 'transactions',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'sharkgateway'
                ];
            } else if ($request->gateway_type === 'arkama') {
                $gateway->config = [
                    'auth_type' => 'bearer',
                    'transaction_endpoint' => '',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'arkama'
                ];
            } else if ($request->gateway_type === 'versell') {
                $gateway->config = [
                    'auth_type' => 'header_keys',
                    'transaction_endpoint' => 'transactions',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'versell'
                ];
            } else if ($request->gateway_type === 'getpay') {
                $gateway->config = [
                    'auth_type' => 'login_token',
                    'transaction_endpoint' => 'create-payment',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'getpay'
                ];
            } else if ($request->gateway_type === 'cashtime') {
                $gateway->config = [
                    'auth_type' => 'header_key',
                    'transaction_endpoint' => 'v1/cob',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'cashtime'
                ];
            } else if ($request->gateway_type === 'e2bank') {
                $gateway->config = [
                    'auth_type' => 'oauth2_mtls',
                    'transaction_endpoint' => 'qrcode/pix',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'e2bank'
                ];
                // E2 Bank não precisa de api_url no formulário (URLs hardcoded no provider)
                $gateway->api_url = 'https://api.e2bank.com.br';
            } else if ($request->gateway_type === 'pluggou') {
                $gateway->config = [
                    'auth_type' => 'header_keys',
                    'transaction_endpoint' => 'transactions',
                    'health_endpoint' => 'health',
                    'gateway_type' => 'pluggou'
                ];
                // Pluggou URL base padrão
                if (empty($gateway->api_url)) {
                    $gateway->api_url = 'https://api.pluggoutech.com/api';
                }
            }
            
            $gateway->save();
            
            // If this is set as default, unset all others
            if ($gateway->is_default) {
                PaymentGateway::where('id', '!=', $gateway->id)
                    ->update(['is_default' => false]);
            }
            
            return redirect()->route('admin.gateways.index')
                ->with('success', 'Gateway criado com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao criar gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao criar gateway: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show edit form for gateway
     */
    public function edit($id)
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            return view('admin.gateways.edit', compact('gateway'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao editar gateway: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update gateway
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'api_url' => 'required|string|max:255',
                'is_default' => 'nullable|in:0,1,true,false,on,off',
                'is_active' => 'nullable|in:0,1,true,false,on,off',
            ]);
            
            // Get gateway
            $gateway = PaymentGateway::findOrFail($id);
            
            // Update gateway
            $gateway->name = $request->name;
            $gateway->api_url = $request->api_url;
            // Convert to boolean safely - accepts 0, 1, 'true', 'false', 'on', 'off', or missing (defaults)
            $gateway->is_default = filter_var($request->input('is_default'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $gateway->is_active = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? ($gateway->is_active ?? true);
            $gateway->save();
            
            // If this is set as default, unset all others
            if ($gateway->is_default) {
                PaymentGateway::where('id', '!=', $gateway->id)
                    ->update(['is_default' => false]);
            } else {
                // If this was the default and is no longer, set another as default
                if ($gateway->getOriginal('is_default')) {
                    $newDefault = PaymentGateway::where('id', '!=', $gateway->id)
                        ->where('is_active', true)
                        ->first();
                    
                    if ($newDefault) {
                        $newDefault->is_default = true;
                        $newDefault->save();
                    }
                }
            }
            
            return redirect()->route('admin.gateways.index')
                ->with('success', 'Gateway atualizado com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar gateway: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Toggle gateway active status
     */
    public function toggleStatus($id)
    {
        try {
            $gateway = PaymentGateway::findOrFail($id);
            
            // If deactivating the default gateway, prevent it
            if ($gateway->is_default && $gateway->is_active) {
                return back()->withErrors(['error' => 'Não é possível desativar o gateway padrão. Defina outro gateway como padrão primeiro.']);
            }
            
            // Toggle status
            $gateway->is_active = !$gateway->is_active;
            $gateway->save();
            
            $statusText = $gateway->is_active ? 'ativado' : 'desativado';
            
            return redirect()->route('admin.gateways.index', ['status' => $gateway->is_active ? 'active' : 'inactive'])
                ->with('success', "Gateway {$statusText} com sucesso!");
                
        } catch (\Exception $e) {
            Log::error('Erro ao alterar status do gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao alterar status do gateway: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete gateway
     */
    public function destroy($id)
    {
        try {
            // Check if gateway has users
            $gateway = PaymentGateway::findOrFail($id);
            $usersCount = User::where('assigned_gateway_id', $gateway->id)->count();
            
            if ($usersCount > 0) {
                return back()->withErrors(['error' => 'Não é possível excluir um gateway que possui usuários atribuídos']);
            }
            
            // Check if gateway has transactions
            $transactionsCount = Transaction::where('gateway_id', $gateway->id)->count();
            
            if ($transactionsCount > 0) {
                return back()->withErrors(['error' => 'Não é possível excluir um gateway que possui transações. Desative-o em vez de excluí-lo.']);
            }
            
            // Check if it's the default gateway
            if ($gateway->is_default) {
                return back()->withErrors(['error' => 'Não é possível excluir o gateway padrão. Defina outro gateway como padrão primeiro.']);
            }
            
            // Delete gateway credentials
            UserGatewayCredential::where('gateway_id', $gateway->id)->delete();
            
            // Delete gateway fees
            GatewayFee::where('gateway_id', $gateway->id)->delete();
            
            // Delete gateway
            $gateway->delete();
            
            return redirect()->route('admin.gateways.index')
                ->with('success', 'Gateway excluído com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao excluir gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao excluir gateway: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get gateway credentials
     */
    public function getCredentials($id)
    {
        try {
            // Get admin user
            $adminUser = User::where('role', 'admin')->first();
            
            if (!$adminUser) {
                return response()->json([
                    'success' => false,
                    'error' => 'Admin user not found'
                ]);
            }
            
            // Get gateway
            $gateway = PaymentGateway::findOrFail($id);
            
            // Get credentials - try admin first, then global
            $credentials = UserGatewayCredential::where('user_id', $adminUser->id)
                ->where('gateway_id', $gateway->id)
                ->where('is_active', true)
                ->first();
            
            // If no admin credentials, try global
            if (!$credentials) {
                $credentials = UserGatewayCredential::whereNull('user_id')
                    ->where('gateway_id', $gateway->id)
                    ->where('is_active', true)
                    ->first();
            }
            
            // Return credentials data (without sensitive info in response, but with decrypted keys for editing)
            $credentialsData = null;
            if ($credentials) {
                $credentialsData = [
                    'id' => $credentials->id,
                    'public_key' => $credentials->public_key,
                    'secret_key' => $credentials->secret_key, // Decrypted
                    'is_sandbox' => $credentials->is_sandbox,
                    'is_active' => $credentials->is_active,
                    'user_id' => $credentials->user_id,
                ];
            }
                
            return response()->json([
                'success' => true,
                'credentials' => $credentialsData,
                'gateway' => [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'config' => $gateway->config,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter credenciais do gateway: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao obter credenciais: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Configure gateway credentials
     */
    public function configure(Request $request)
    {
        try {
            $request->validate([
                'gateway_id' => 'required|exists:payment_gateways,id',
                'public_key' => 'nullable|string|max:255',
                'secret_key' => 'required|string|max:1000',
                'is_sandbox' => 'nullable|in:0,1,true,false,on,off',
                'gateway_type' => 'nullable|string',
            ]);
            
            // Get admin user
            $adminUser = User::where('role', 'admin')->first();
            
            if (!$adminUser) {
                return back()->withErrors(['error' => 'Admin user not found']);
            }
            
            // Get gateway
            $gateway = PaymentGateway::findOrFail($request->gateway_id);
            
            // Validate that secret_key is not empty
            if (empty($request->secret_key)) {
                return back()->withErrors(['error' => 'Secret Key é obrigatória']);
            }
            
            // For Pluggou, public_key is also required
            if ($gateway->getConfig('gateway_type') === 'pluggou' && empty($request->public_key)) {
                return back()->withErrors(['error' => 'Public Key é obrigatória para o gateway Pluggou']);
            }
            
            // Clean credentials: trim, remove newlines, and remove any invisible characters
            $publicKey = trim($request->public_key ?? '');
            $secretKey = trim($request->secret_key ?? '');
            
            // Remove any newlines, carriage returns, or other whitespace characters
            $publicKey = preg_replace('/\s+/', '', $publicKey);
            $secretKey = preg_replace('/\s+/', '', $secretKey);
            
            // Remove any non-printable characters
            $publicKey = preg_replace('/[\x00-\x1F\x7F]/', '', $publicKey);
            $secretKey = preg_replace('/[\x00-\x1F\x7F]/', '', $secretKey);
            
            // Validate credentials are not empty after cleaning
            if (empty($secretKey)) {
                return back()->withErrors(['error' => 'Secret Key é obrigatória e não pode estar vazia']);
            }
            
            // For Pluggou, validate public_key after cleaning
            if ($gateway->getConfig('gateway_type') === 'pluggou' && empty($publicKey)) {
                return back()->withErrors(['error' => 'Public Key é obrigatória para o gateway Pluggou e não pode estar vazia']);
            }
            
            // Convert is_sandbox and is_global to boolean
            $isSandbox = filter_var($request->input('is_sandbox'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            $isGlobal = filter_var($request->input('is_global'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            
            // Determine user_id: null for global, admin user id for user-specific
            $userId = null;
            if (!$isGlobal) {
                $userId = $adminUser->id;
            }
            
            // Find existing credential (check both user-specific and global)
            $credential = UserGatewayCredential::where('gateway_id', $gateway->id)
                ->where(function($query) use ($userId) {
                    if ($userId) {
                        $query->where('user_id', $userId);
                    } else {
                        $query->whereNull('user_id');
                    }
                })
                ->first();
            
            if ($credential) {
                // Update existing credential
                // IMPORTANTE: Atualizar diretamente as propriedades para garantir que o mutator setSecretKeyAttribute seja chamado
                $credential->user_id = $userId; // Update to global if changed
                $credential->public_key = $publicKey;
                $credential->secret_key = $secretKey; // Mutator setSecretKeyAttribute será chamado automaticamente
                $credential->is_active = true;
                $credential->is_sandbox = $isSandbox;
                $credential->save();
            } else {
                // Delete old credentials for this gateway if switching from user to global or vice versa
                if ($userId) {
                    // Delete global credentials if creating user-specific
                    UserGatewayCredential::where('gateway_id', $gateway->id)
                        ->whereNull('user_id')
                        ->delete();
                } else {
                    // Delete user-specific credentials if creating global
                    UserGatewayCredential::where('gateway_id', $gateway->id)
                        ->whereNotNull('user_id')
                        ->delete();
                }
                
                // Create new credential
                $credential = UserGatewayCredential::create([
                    'user_id' => $userId, // null for global
                    'gateway_id' => $gateway->id,
                    'public_key' => $publicKey,
                    'secret_key' => $secretKey, // Mutator will encrypt
                    'is_active' => true,
                    'is_sandbox' => $isSandbox,
                ]);
            }
            
            // Refresh to ensure we have the latest data
            $credential->refresh();
            
            // Verify credentials were saved correctly by checking raw encrypted value
            $rawSecretKey = $credential->getRawSecretKey();
            $decryptedSecretKey = $credential->secret_key; // This will decrypt
            
            // Verify credentials were saved correctly
            Log::info('Credenciais do gateway salvas', [
                'gateway_id' => $gateway->id,
                'gateway_name' => $gateway->name,
                'credential_id' => $credential->id,
                'user_id' => $credential->user_id,
                'is_global' => $credential->user_id === null,
                'has_public_key' => !empty($credential->public_key),
                'public_key_length' => $credential->public_key ? strlen($credential->public_key) : 0,
                'public_key_preview' => $credential->public_key ? substr($credential->public_key, 0, 10) . '...' : null,
                'has_secret_key_raw' => !empty($rawSecretKey),
                'raw_secret_key_length' => $rawSecretKey ? strlen($rawSecretKey) : 0,
                'has_secret_key_decrypted' => !empty($decryptedSecretKey),
                'decrypted_secret_key_length' => $decryptedSecretKey ? strlen($decryptedSecretKey) : 0,
                'is_active' => $credential->is_active,
                'is_sandbox' => $credential->is_sandbox,
            ]);
            
            $message = 'Credenciais do ' . $gateway->name . ' configuradas com sucesso!';
            if ($isGlobal) {
                $message .= ' (Credenciais globais - disponíveis para todos os usuários)';
            }
            
            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Erro ao configurar gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao salvar credenciais: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete gateway credentials
     */
    public function deleteCredentials($id)
    {
        try {
            $credential = UserGatewayCredential::findOrFail($id);
            $gateway = $credential->gateway;
            
            $credential->delete();
            
            return back()->with('success', 'Credenciais do ' . $gateway->name . ' excluídas com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir credenciais: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao excluir credenciais: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Edit gateway credentials (get data)
     */
    public function editCredentials($id)
    {
        try {
            $credential = UserGatewayCredential::findOrFail($id);
            $gateway = $credential->gateway;
            
            return response()->json([
                'success' => true,
                'credential' => [
                    'id' => $credential->id,
                    'gateway_id' => $credential->gateway_id,
                    'public_key' => $credential->public_key,
                    'secret_key' => $credential->secret_key, // Decrypted
                    'is_sandbox' => $credential->is_sandbox,
                    'is_active' => $credential->is_active,
                    'is_global' => $credential->user_id === null,
                    'user_id' => $credential->user_id,
                ],
                'gateway' => [
                    'id' => $gateway->id,
                    'name' => $gateway->name,
                    'gateway_type' => $gateway->getConfig('gateway_type'),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter credenciais para edição: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Erro ao obter credenciais: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Test gateway connection
     */
    public function test(Request $request)
    {
        try {
            $request->validate([
                'gateway_id' => 'required|exists:payment_gateways,id',
            ]);
            
            // Get admin user
            $adminUser = User::where('role', 'admin')->first();
            
            if (!$adminUser) {
                return response()->json([
                    'success' => false,
                    'error' => 'Admin user not found'
                ]);
            }
            
            // Get gateway
            $gateway = PaymentGateway::findOrFail($request->gateway_id);
            
            // Get credentials
            $credentials = UserGatewayCredential::where('user_id', $adminUser->id)
                ->where('gateway_id', $gateway->id)
                ->first();
                
            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'error' => 'Credenciais não configuradas'
                ]);
            }
            
            // Determine auth type and endpoint
            $authType = $gateway->getConfig('auth_type', 'header');
            $healthEndpoint = $gateway->getConfig('health_endpoint', 'health');
            
            // Create HTTP request
            $httpRequest = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-Admin-Test/1.0',
                ]);
            
            // Add authentication based on type
            if ($authType === 'header') {
                $httpRequest = $httpRequest->withHeaders([
                    'x-secret-key' => $credentials->secret_key,
                    'x-public-key' => $credentials->public_key,
                ]);
            } else if ($authType === 'basic') {
                if ($gateway->getConfig('gateway_type') === 'sharkgateway') {
                    // Sharkgateway uses public:secret format
                    $auth = base64_encode($credentials->public_key . ':' . $credentials->secret_key);
                    $httpRequest = $httpRequest->withHeaders([
                        'Authorization' => 'Basic ' . $auth,
                    ]);
                } else {
                    // Hopy uses secret:x format
                    $httpRequest = $httpRequest->withBasicAuth($credentials->secret_key, 'x');
                }
            } else if ($authType === 'header_key') {
                $httpRequest = $httpRequest->withHeaders([
                    'x-authorization-key' => $credentials->secret_key,
                ]);
            } else if ($authType === 'bearer') {
                $httpRequest = $httpRequest->withHeaders([
                    'Authorization' => 'Bearer ' . $credentials->secret_key,
                ]);
            } else if ($authType === 'header_keys') {
                // Versell uses vspi/vsps, Pluggou uses X-Public-Key/X-Secret-Key
                if ($gateway->getConfig('gateway_type') === 'pluggou') {
                    $httpRequest = $httpRequest->withHeaders([
                        'X-Public-Key' => $credentials->public_key,
                        'X-Secret-Key' => $credentials->secret_key,
                    ]);
                } else {
                    $httpRequest = $httpRequest->withHeaders([
                        'vspi' => $credentials->public_key,
                        'vsps' => $credentials->secret_key,
                    ]);
                }
            }
            
            // Make the request
            // For Pluggou, try to check balance endpoint instead of health
            if ($gateway->getConfig('gateway_type') === 'pluggou') {
                $response = $httpRequest->get($gateway->getApiUrl('/withdrawals/balance'));
            } else {
                $response = $httpRequest->get($gateway->getApiUrl('/' . $healthEndpoint));
            }
                
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Conexão bem-sucedida!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Credenciais inválidas ou gateway indisponível'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao testar conexão do gateway: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro de conexão: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show gateway fees page
     */
    public function fees($id)
    {
        try {
            // Get gateway
            $gateway = PaymentGateway::with('fees')->findOrFail($id);
            
            // Get fees for each payment method
            $fees = $gateway->getAllFees();
            
            return view('admin.gateways.fees', compact('gateway', 'fees'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar taxas do gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao carregar taxas: ' . $e->getMessage()]);
        }
    }

    /**
     * Update gateway fees
     */
    public function updateFees(Request $request, $id)
    {
        try {
            $request->validate([
                'pix_percentage' => 'required|numeric|min:0|max:100',
                'pix_fixed' => 'required|numeric|min:0',
                'pix_min' => 'nullable|numeric|min:0',
                'pix_max' => 'nullable|numeric|min:0',
                
                'credit_card_percentage' => 'required|numeric|min:0|max:100',
                'credit_card_fixed' => 'required|numeric|min:0',
                'credit_card_min' => 'nullable|numeric|min:0',
                'credit_card_max' => 'nullable|numeric|min:0',
                
                'bank_slip_percentage' => 'required|numeric|min:0|max:100',
                'bank_slip_fixed' => 'required|numeric|min:0',
                'bank_slip_min' => 'nullable|numeric|min:0',
                'bank_slip_max' => 'nullable|numeric|min:0',
            ]);
            
            // Get gateway
            $gateway = PaymentGateway::findOrFail($id);
            
            // Update or create PIX fee
            GatewayFee::updateOrCreate(
                [
                    'gateway_id' => $gateway->id,
                    'payment_method' => 'pix',
                ],
                [
                    'percentage_fee' => $request->pix_percentage,
                    'fixed_fee' => $request->pix_fixed,
                    'min_amount' => $request->pix_min,
                    'max_amount' => $request->pix_max,
                    'is_active' => true,
                ]
            );
            
            // Update or create Credit Card fee
            GatewayFee::updateOrCreate(
                [
                    'gateway_id' => $gateway->id,
                    'payment_method' => 'credit_card',
                ],
                [
                    'percentage_fee' => $request->credit_card_percentage,
                    'fixed_fee' => $request->credit_card_fixed,
                    'min_amount' => $request->credit_card_min,
                    'max_amount' => $request->credit_card_max,
                    'is_active' => true,
                ]
            );
            
            // Update or create Bank Slip fee
            GatewayFee::updateOrCreate(
                [
                    'gateway_id' => $gateway->id,
                    'payment_method' => 'bank_slip',
                ],
                [
                    'percentage_fee' => $request->bank_slip_percentage,
                    'fixed_fee' => $request->bank_slip_fixed,
                    'min_amount' => $request->bank_slip_min,
                    'max_amount' => $request->bank_slip_max,
                    'is_active' => true,
                ]
            );
            
            return redirect()->route('admin.gateways.fees', $gateway->id)
                ->with('success', 'Taxas do gateway atualizadas com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar taxas do gateway: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar taxas: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test GetPay connection
     */
    private function testGetPayConnection($credentials)
    {
        try {
            // Test login to GetPay
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-Admin-Test/1.0',
                ])
                ->post('https://hub.getpay.store/api/login', [
                    'email' => $credentials->public_key,
                    'password' => $credentials->secret_key,
                ]);
                
            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['success']) && !empty($data['token'])) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Conexão GetPay bem-sucedida!'
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'error' => 'Login GetPay falhou: ' . ($data['message'] ?? 'Credenciais inválidas')
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Erro de conexão GetPay: HTTP ' . $response->status()
                ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Erro ao testar GetPay: ' . $e->getMessage()
            ]);
        }
    }
}
