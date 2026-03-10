<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use App\Models\BaasCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    /**
     * List all withdrawals
     * 
     * GET /api/v1/withdrawals
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            $query = Withdrawal::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($status) {
                $query->where('status', $status);
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            $withdrawals = $query->paginate($perPage);

            $data = $withdrawals->map(function ($withdrawal) {
                return $this->formatWithdrawal($withdrawal);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $withdrawals->total(),
                    'per_page' => $withdrawals->perPage(),
                    'current_page' => $withdrawals->currentPage(),
                    'last_page' => $withdrawals->lastPage(),
                    'from' => $withdrawals->firstItem(),
                    'to' => $withdrawals->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error listing withdrawals via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error listing withdrawals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal details
     * 
     * GET /api/v1/withdrawals/{id}
     * 
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            $withdrawal = Withdrawal::where('withdrawal_id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$withdrawal) {
                return response()->json([
                    'success' => false,
                    'error' => 'Withdrawal not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatWithdrawal($withdrawal)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching withdrawal via API', [
                'withdrawal_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error fetching withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Request a new withdrawal
     * 
     * POST /api/v1/withdrawals
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $user = $request->user();
            
            if (!$user) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Get wallet with lock
            $wallet = $user->wallet()->lockForUpdate()->first();
            
            if (!$wallet) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Wallet not found'
                ], 404);
            }
            
            // Get minimum withdrawal amount from global configuration
            $globalWithdrawalFee = \App\Models\FeeConfiguration::where('payment_method', 'withdrawal')
                ->where('is_global', true)
                ->where('is_active', true)
                ->first();
            
            $minWithdrawalAmount = $globalWithdrawalFee && $globalWithdrawalFee->min_transaction_value 
                ? (float)$globalWithdrawalFee->min_transaction_value 
                : 10.00; // Default minimum
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => [
                    'required',
                    'numeric',
                    'min:' . $minWithdrawalAmount,
                ],
                'pix_type' => 'required|in:email,cpf,cnpj,phone,random',
                'pix_key' => 'required|string|max:255',
            ], [
                'amount.min' => "The minimum withdrawal amount is R$ " . number_format($minWithdrawalAmount, 2, ',', '.'),
            ]);
            
            if ($validator->fails()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $requestedAmount = $request->amount;
            $pixType = $request->pix_type;
            $pixKey = $request->pix_key;
            
            // Get BaaS gateway that will be used (if available)
            $baasGateway = null;
            if ($user->assigned_baas_id) {
                $baasGateway = BaasCredential::find($user->assigned_baas_id);
            }
            if (!$baasGateway) {
                $baasGateway = BaasCredential::where('is_default', true)->where('is_active', true)->first();
            }
            if (!$baasGateway) {
                $baasGateway = BaasCredential::where('is_active', true)->first();
            }
            
            // Calculate fee based on user settings (BaaS fee is absorbed by system, not charged to user)
            $feeCalculation = $user->calculateWithdrawalFee($requestedAmount, $baasGateway);
            $fee = $feeCalculation['fee']; // Taxa do usuário
            $baasFee = $feeCalculation['baas_fee'] ?? 0; // Taxa do BaaS (absorvida pelo sistema)
            $totalFee = $feeCalculation['total_fee']; // Total de taxa cobrada do usuário (sem BaaS)
            $totalToDebit = $feeCalculation['total_to_debit']; // Total a debitar (sem taxa BaaS)
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
                    'error' => 'A pending withdrawal with the same data already exists',
                    'existing_withdrawal_id' => $existingWithdrawal->withdrawal_id
                ], 409);
            }
            
            // Check balance
            if ($wallet->balance < $totalToDebit) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => 'Insufficient balance',
                    'current_balance' => (float) $wallet->balance,
                    'requested_amount' => (float) $requestedAmount,
                    'fee' => (float) $fee,
                    'total_required' => (float) $totalToDebit
                ], 422);
            }
            
            // Create withdrawal record
            $withdrawal = new Withdrawal();
            $withdrawal->user_id = $user->id;
            $withdrawal->withdrawal_id = 'WDR_' . strtoupper(Str::random(8)) . '_' . time();
            $withdrawal->amount = $requestedAmount;
            $withdrawal->fee = $totalFee; // Taxa cobrada do usuário (sem BaaS)
            $withdrawal->net_amount = $netAmount;
            
            // Salvar informações adicionais sobre as taxas
            $withdrawal->response_data = array_merge($withdrawal->response_data ?? [], [
                'user_fee' => $fee, // Taxa do usuário
                'baas_fee' => $baasFee, // Taxa do BaaS (absorvida pelo sistema)
                'total_fee' => $totalFee, // Total cobrado do usuário
                'baas_gateway' => $baasGateway ? $baasGateway->gateway : null,
                'note' => 'A taxa do BaaS é absorvida pelo sistema e não é cobrada do usuário',
            ]);
            $withdrawal->pix_type = $pixType;
            $withdrawal->pix_key = $pixKey;
            $withdrawal->status = 'pending';
            $withdrawal->save();
            
            // Deduct total (amount + fee) from wallet
            $debitResult = $wallet->addDebit(
                $totalToDebit,
                'withdrawal',
                "Withdrawal via PIX - {$withdrawal->withdrawal_id} (R$ {$requestedAmount} + fee R$ {$fee})",
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
                    'error' => 'Failed to debit amount from wallet'
                ], 500);
            }
            
            // Check if automatic withdrawal
            if ($user->withdrawal_type === 'automatic') {
                $result = $this->processAutomaticWithdrawal($withdrawal);
                
                if (!$result) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'Failed to process automatic withdrawal',
                        'withdrawal_id' => $withdrawal->withdrawal_id
                    ], 500);
                }
            }
            
            DB::commit();
            
            // Refresh withdrawal to get updated status
            $withdrawal->refresh();
            
            $message = $user->withdrawal_type === 'automatic' 
                ? 'Withdrawal processed automatically! The amount will be sent shortly.'
                : 'Withdrawal requested successfully. Awaiting administrator approval.';
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_automatic' => $user->withdrawal_type === 'automatic',
                'data' => $this->formatWithdrawal($withdrawal)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating withdrawal via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Error creating withdrawal: ' . $e->getMessage()
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
            ]);
            
            // Process based on BaaS gateway
            if ($baasGateway->gateway === 'pluggou') {
                return $this->processPluggouWithdrawal($withdrawal, $baasGateway);
            } else {
                // For other gateways, mark as processing and let webhook handle
                $withdrawal->update(['status' => 'processing']);
                return true;
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
     * Process withdrawal via PluggouCash
     */
    protected function processPluggouWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            
            // Mapear tipos de chave PIX para o formato PluggouCash
            $keyTypeMap = [
                'cpf' => 'cpf',
                'cnpj' => 'cnpj',
                'email' => 'email',
                'phone' => 'phone',
                'evp' => 'random',
                'random' => 'random',
            ];
            
            $pluggouKeyType = $keyTypeMap[$withdrawal->pix_type] ?? 'random';
            
            // API URL - PluggouCash usa apenas a URL de produção
            $apiUrl = 'https://api.pluggoutech.com/api';
            
            // Valor em centavos
            $amountInCents = (int) ($withdrawal->net_amount * 100);
            
            $payload = [
                'amount' => $amountInCents,
                'key_type' => $pluggouKeyType,
                'key_value' => $pixKey,
            ];
            
            Log::info('Enviando saque para PluggouCash (V1 API)', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount_cents' => $amountInCents,
                'key_type' => $pluggouKeyType,
                'key_value' => $pixKey,
                'api_url' => $apiUrl
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Public-Key' => $baasGateway->public_key,
                    'X-Secret-Key' => $baasGateway->secret_key,
                ])
                ->post($apiUrl . '/withdrawals', $payload);
            
            Log::info('Resposta do PluggouCash para saque (V1 API)', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Extrair ID do saque (pode estar em data['id'] ou data['data']['id'])
                $externalId = $data['id'] ?? $data['data']['id'] ?? null;
                $e2eId = $data['e2e_id'] ?? $data['data']['e2e_id'] ?? null;
                
                // Preparar response_data completo
                $responseData = $data;
                if (isset($data['data']) && is_array($data['data'])) {
                    $responseData = array_merge($data, $data['data']);
                }
                
                // Garantir que external_id e e2e_id estejam no response_data
                if ($externalId) {
                    $responseData['id'] = $externalId;
                }
                if ($e2eId) {
                    $responseData['e2e_id'] = $e2eId;
                }
                
                Log::info('Saque PluggouCash processado com sucesso (V1 API)', [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'external_id' => $externalId,
                    'e2e_id' => $e2eId,
                    'status' => $data['status'] ?? 'pending'
                ]);
                
                $withdrawal->update([
                    'status' => 'processing',
                    'external_id' => $externalId,
                    'response_data' => $responseData
                ]);
                
                return true;
            }
            
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? $errorData['error'] ?? $response->body();
            throw new \Exception('PluggouCash API error: ' . $errorMessage);
        } catch (\Exception $e) {
            Log::error('PluggouCash withdrawal error (V1 API): ' . $e->getMessage(), [
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
     * Format withdrawal for API response
     * 
     * @param Withdrawal $withdrawal
     * @return array
     */
    protected function formatWithdrawal(Withdrawal $withdrawal): array
    {
        return [
            'id' => $withdrawal->withdrawal_id,
            'withdrawal_id' => $withdrawal->withdrawal_id,
            'external_id' => $withdrawal->external_id,
            'amount' => (float) $withdrawal->amount,
            'fee' => (float) $withdrawal->fee,
            'net_amount' => (float) $withdrawal->net_amount,
            'pix_type' => $withdrawal->pix_type,
            'pix_key' => $withdrawal->pix_key,
            'status' => $withdrawal->status,
            'error_message' => $withdrawal->error_message,
            'created_at' => $withdrawal->created_at->toISOString(),
            'updated_at' => $withdrawal->updated_at->toISOString(),
            'completed_at' => $withdrawal->completed_at ? $withdrawal->completed_at->toISOString() : null,
        ];
    }
}

