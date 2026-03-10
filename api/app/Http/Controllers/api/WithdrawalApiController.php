<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use App\Models\FeeConfiguration;
use App\Models\BaasCredential;
use App\Helpers\FeeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WithdrawalApiController extends Controller
{
    /**
     * List all withdrawals for the authenticated user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            $withdrawals = Withdrawal::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));
            
            return response()->json([
                'success' => true,
                'data' => $withdrawals->items(),
                'pagination' => [
                    'total' => $withdrawals->total(),
                    'per_page' => $withdrawals->perPage(),
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Withdrawal Index Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar saques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new withdrawal (PIX OUT)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $user = $request->user();
            
            // Get wallet with lock
            $wallet = $user->wallet()->lockForUpdate()->first();
            
            if (!$wallet) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Carteira não encontrada'
                ], 404);
            }
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01',
                'pix_type' => 'required|in:email,cpf,cnpj,phone,random',
                'pix_key' => 'required|string|max:255',
            ]);
            
            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $requestedAmount = $request->amount;
            $pixType = $request->pix_type;
            $pixKey = $request->pix_key;
            
            // Calculate fee based on user settings
            $feeCalculation = $user->calculateWithdrawalFee($requestedAmount);
            $fee = $feeCalculation['fee'];
            $totalToDebit = $feeCalculation['total_to_debit'];
            $netAmount = $feeCalculation['net_amount'];
            
            // Check for duplicate pending withdrawal
            $existingWithdrawal = Withdrawal::where('user_id', $user->id)
                ->where('amount', $requestedAmount)
                ->where('pix_type', $pixType)
                ->where('pix_key', $pixKey)
                ->whereIn('status', ['pending', 'processing'])
                ->first();
                
            if ($existingWithdrawal) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe um saque pendente com os mesmos dados',
                    'existing_withdrawal_id' => $existingWithdrawal->withdrawal_id
                ], 409);
            }
            
            // Check balance (need balance for requested amount + fee)
            if ($wallet->balance < $totalToDebit) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente para saque + taxa',
                    'current_balance' => $wallet->balance,
                    'requested_amount' => $requestedAmount,
                    'fee' => $fee,
                    'total_required' => $totalToDebit
                ], 422);
            }
            
            // Create withdrawal record
            $withdrawal = new Withdrawal();
            $withdrawal->user_id = $user->id;
            $withdrawal->withdrawal_id = 'WDR_' . strtoupper(Str::random(8)) . '_' . time();
            $withdrawal->amount = $requestedAmount;
            $withdrawal->fee = $fee;
            $withdrawal->net_amount = $netAmount;
            $withdrawal->pix_type = $pixType;
            $withdrawal->pix_key = $pixKey;
            $withdrawal->status = 'pending';
            $withdrawal->save();
            
            // Deduct total (amount + fee) from wallet
            $debitResult = $wallet->addDebit(
                $totalToDebit,
                'withdrawal',
                "Saque via PIX - {$withdrawal->withdrawal_id} (R$ {$requestedAmount} + taxa R$ {$fee})",
                [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'requested_amount' => $requestedAmount,
                    'fee' => $fee,
                    'pix_type' => $pixType,
                    'pix_key' => $pixKey,
                ],
                $withdrawal->withdrawal_id
            );
            
            if (!$debitResult) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível debitar o valor da carteira'
                ], 500);
            }
            
            // Check if automatic withdrawal
            if ($user->withdrawal_type === 'automatic') {
                $result = $this->processAutomaticWithdrawal($withdrawal);
                
                if (!$result) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Falha ao processar saque automático',
                        'withdrawal_id' => $withdrawal->withdrawal_id
                    ], 500);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Saque criado com sucesso',
                'data' => [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'amount' => $withdrawal->amount,
                    'fee' => $withdrawal->fee,
                    'net_amount' => $withdrawal->net_amount,
                    'pix_type' => $withdrawal->pix_type,
                    'pix_key' => $withdrawal->pix_key,
                    'status' => $withdrawal->status,
                    'created_at' => $withdrawal->created_at->toIso8601String(),
                ]
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Withdrawal Store Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar saque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show a specific withdrawal
     * 
     * @param string $withdrawalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $withdrawalId)
    {
        try {
            $user = $request->user();
            
            $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$withdrawal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saque não encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'amount' => $withdrawal->amount,
                    'fee' => $withdrawal->fee,
                    'net_amount' => $withdrawal->net_amount,
                    'pix_type' => $withdrawal->pix_type,
                    'pix_key' => $withdrawal->pix_key,
                    'status' => $withdrawal->status,
                    'error_message' => $withdrawal->error_message,
                    'created_at' => $withdrawal->created_at->toIso8601String(),
                    'updated_at' => $withdrawal->updated_at->toIso8601String(),
                    'completed_at' => $withdrawal->completed_at ? $withdrawal->completed_at->toIso8601String() : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Withdrawal Show Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar saque',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check withdrawal status
     * 
     * @param string $withdrawalId
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Request $request, $withdrawalId)
    {
        try {
            $user = $request->user();
            
            $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$withdrawal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saque não encontrado'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'status' => $withdrawal->status,
                    'amount' => $withdrawal->amount,
                    'net_amount' => $withdrawal->net_amount,
                    'error_message' => $withdrawal->error_message,
                    'created_at' => $withdrawal->created_at->toIso8601String(),
                    'completed_at' => $withdrawal->completed_at ? $withdrawal->completed_at->toIso8601String() : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('API Withdrawal Status Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process automatic withdrawal via BaaS
     */
    protected function processAutomaticWithdrawal(Withdrawal $withdrawal)
    {
        try {
            // Get BaaS gateway for this user - first check user's assigned BaaS, then fall back to default
            $baasGateway = null;
            
            if ($withdrawal->user->assigned_baas_id) {
                // User has a specific BaaS assigned
                $baasGateway = BaasCredential::find($withdrawal->user->assigned_baas_id);
                
                if (!$baasGateway || !$baasGateway->is_active) {
                    Log::warning('User assigned BaaS is inactive, falling back to default', [
                        'user_id' => $withdrawal->user->id,
                        'assigned_baas_id' => $withdrawal->user->assigned_baas_id
                    ]);
                    $baasGateway = null;
                }
            }
            
            // Check if multi-BaaS round-robin is enabled
            if (!$baasGateway && BaasCredential::isMultiBaasEnabled()) {
                // Get last used BaaS from user's last withdrawal
                $lastWithdrawal = Withdrawal::where('user_id', $withdrawal->user_id)
                    ->where('id', '<', $withdrawal->id)
                    ->whereNotNull('baas_provider_id')
                    ->orderBy('id', 'desc')
                    ->first();
                    
                $lastUsedBaasId = $lastWithdrawal ? $lastWithdrawal->baas_provider_id : null;
                
                // Get next BaaS in round-robin
                $baasGateway = BaasCredential::getNextRoundRobin($lastUsedBaasId);
                
                Log::info('Using round-robin BaaS selection', [
                    'user_id' => $withdrawal->user_id,
                    'selected_baas' => $baasGateway->gateway,
                    'last_used_baas_id' => $lastUsedBaasId
                ]);
            }
            
            // Fall back to default BaaS if user doesn't have one assigned or it's inactive
            if (!$baasGateway) {
                $baasGateway = BaasCredential::where('is_default', true)
                    ->where('is_active', true)
                    ->first();
            }
            
            // If still no BaaS, get any active one
            if (!$baasGateway) {
                $baasGateway = BaasCredential::where('is_active', true)->first();
            }
                
            if (!$baasGateway) {
                throw new \Exception('No active BaaS gateway found');
            }
            
            // Save which BaaS provider is being used for this withdrawal
            $withdrawal->update(['baas_provider_id' => $baasGateway->id]);
            
            Log::info('Processing automatic withdrawal', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'baas_gateway' => $baasGateway->gateway,
                'baas_provider_id' => $baasGateway->id,
                'user_has_assigned_baas' => $withdrawal->user->assigned_baas_id ? 'yes' : 'no'
            ]);
            
            if ($baasGateway->gateway === 'strikecash') {
                return $this->processStrikeCashWithdrawal($withdrawal, $baasGateway);
            } else if ($baasGateway->gateway === 'cashtime') {
                return $this->processCashtimeWithdrawal($withdrawal, $baasGateway);
            } else if ($baasGateway->gateway === 'e2bank') {
                return $this->processE2BankWithdrawal($withdrawal, $baasGateway);
            } else {
                throw new \Exception('Unsupported BaaS gateway: ' . $baasGateway->gateway);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar saque automático: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Process withdrawal via StrikeCash
     */
    protected function processStrikeCashWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            $pixTypeValue = $this->mapPixTypeToAvivHubValue($withdrawal->pix_type);
            
            $appUrl = config('app.url');
            $webhookUrl = rtrim($appUrl, '/') . '/webhook/withdrawal';
            
            $payload = [
                'amount' => (int)round($withdrawal->net_amount * 100),
                'externalRef' => $withdrawal->withdrawal_id,
                'postbackUrl' => $webhookUrl,
                'type' => $pixTypeValue,
                'pix' => $pixKey,
                'name' => $withdrawal->user->name,
                'document' => preg_replace('/[^0-9]/', '', $withdrawal->user->document)
            ];
            
            Log::info('Enviando saque automático via API para StrikeCash', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'payload' => $payload,
                'webhook_url' => $webhookUrl
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'x-secret-key' => $baasGateway->secret_key,
                    'x-public-key' => $baasGateway->public_key,
                ])
                ->post('https://srv.strikecash.com.br/v1/withdraw', $payload);
            
            Log::info('Resposta do StrikeCash para saque via API', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $withdrawal->update([
                    'status' => 'processing',
                    'external_id' => $data['id'] ?? null,
                    'response_data' => $data
                ]);
                return true;
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? $response->body();
            throw new \Exception('StrikeCash API error: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('StrikeCash withdrawal error: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process withdrawal via Cashtime
     */
    protected function processCashtimeWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            
            $appUrl = config('app.url');
            $webhookUrl = rtrim($appUrl, '/') . '/webhook/cashtime';
            
            $payload = [
                'amount' => (int)round($withdrawal->net_amount * 100),
                'pixKey' => $this->formatPixKeyForCashtime($pixKey, $withdrawal->pix_type),
                'pixKeyType' => $this->mapPixTypeToCashtimeValue($withdrawal->pix_type),
                'baasPostbackUrl' => $webhookUrl,
                'externalCode' => $withdrawal->withdrawal_id
            ];
            
            Log::info('Enviando saque automático via API para Cashtime', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'payload' => $payload,
                'webhook_url' => $webhookUrl
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'x-authorization-key' => $baasGateway->secret_key
                ])
                ->post('https://api.cashtime.com.br/v1/request/withdraw', $payload);
            
            Log::info('Resposta do Cashtime para saque via API', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $withdrawal->update([
                    'status' => 'processing',
                    'external_id' => $data['id'] ?? null,
                    'response_data' => $data
                ]);
                return true;
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? $response->body();
            throw new \Exception('Cashtime API error: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('Cashtime withdrawal error: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process withdrawal via E2 Bank
     */
    protected function processE2BankWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            $e2bank = new \App\Services\BaaS\E2BankProvider();
            
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            
            $result = $e2bank->createPixTransfer([
                'amount' => $withdrawal->net_amount,
                'pix_key' => $pixKey,
                'pix_key_type' => $withdrawal->pix_type,
                'recipient_name' => $withdrawal->user->name,
                'recipient_document' => preg_replace('/[^0-9]/', '', $withdrawal->user->document),
                'description' => 'Saque de saldo',
                'external_id' => $withdrawal->withdrawal_id,
            ]);
            
            Log::info('E2 Bank withdrawal response', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'result' => $result
            ]);
            
            if ($result['success']) {
                $withdrawal->update([
                    'status' => 'processing',
                    'external_id' => $result['data']['transfer_id'] ?? null,
                    'response_data' => $result['data'] ?? []
                ]);
                return true;
            }
            
            throw new \Exception('E2 Bank error: ' . ($result['error'] ?? 'Unknown error'));
        } catch (\Exception $e) {
            Log::error('E2 Bank withdrawal error: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Format PIX key for Cashtime
     */
    protected function formatPixKeyForCashtime(string $pixKey, string $pixType): string
    {
        $pixKey = preg_replace('/[^a-zA-Z0-9@._-]/', '', $pixKey);
        
        switch ($pixType) {
            case 'cpf':
            case 'cnpj':
                return preg_replace('/[^0-9]/', '', $pixKey);
            case 'phone':
                $phone = preg_replace('/[^0-9]/', '', $pixKey);
                return '+55' . $phone;
            case 'email':
            case 'random':
            default:
                return $pixKey;
        }
    }
    
    /**
     * Map PIX type to Cashtime value
     */
    protected function mapPixTypeToCashtimeValue(string $pixType): string
    {
        return match($pixType) {
            'cpf' => 'CPF',
            'cnpj' => 'CNPJ',
            'email' => 'EMAIL',
            'phone' => 'PHONE',
            'random' => 'EVP',
            default => 'CPF'
        };
    }

    /**
     * Clean PIX key based on type
     */
    protected function cleanPixKey($pixKey, $pixType)
    {
        if ($pixType === 'cpf' || $pixType === 'cnpj' || $pixType === 'phone') {
            return preg_replace('/[^0-9]/', '', $pixKey);
        }
        return $pixKey;
    }

    /**
     * Map PIX type to AvivHub value
     */
    protected function mapPixTypeToAvivHubValue($pixType)
    {
        return match($pixType) {
            'cpf' => 1,
            'cnpj' => 2,
            'email' => 3,
            'phone' => 4,
            'random' => 5,
            default => 1
        };
    }
}
