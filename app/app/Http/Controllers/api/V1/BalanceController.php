<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BalanceController extends Controller
{
    /**
     * Get account balance
     * 
     * GET /api/v1/balance
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

            $wallet = $user->wallet;

            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'error' => 'Wallet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => (float) $wallet->balance,
                    'pending_balance' => (float) $wallet->pending_balance,
                    'reserved_balance' => (float) $wallet->reserved_balance,
                    'blocked_balance' => (float) $wallet->blocked_balance,
                    'available_balance' => (float) $wallet->available_balance,
                    'total_balance' => (float) $wallet->total_balance,
                    'total_received' => (float) $wallet->total_received,
                    'total_withdrawn' => (float) $wallet->total_withdrawn,
                    'currency' => $wallet->currency ?? 'BRL',
                    'last_transaction_at' => $wallet->last_transaction_at ? $wallet->last_transaction_at->toISOString() : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching balance via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error fetching balance: ' . $e->getMessage()
            ], 500);
        }
    }
}





