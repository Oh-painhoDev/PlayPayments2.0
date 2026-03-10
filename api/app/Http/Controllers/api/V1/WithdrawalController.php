<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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
                    'error' => 'Invalid data',
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
            // Note: Automatic processing will be handled by the system's job queue or webhook
            // For now, the withdrawal is created as 'pending' and will be processed automatically
            if ($user->withdrawal_type === 'automatic') {
                Log::info('Automatic withdrawal created, will be processed by system', [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'user_id' => $user->id
                ]);
                // The withdrawal will be processed automatically by the system
                // You may want to dispatch a job here if you have a queue system
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Withdrawal requested successfully',
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

