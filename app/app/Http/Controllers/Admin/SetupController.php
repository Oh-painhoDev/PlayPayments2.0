<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RetentionConfig;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserRetentionConfig;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetupController extends Controller
{
    /**
     * Show retention overview page
     */
    public function retentionOverview(Request $request)
    {
        // Get all users with individual retention configs
        $configs = UserRetentionConfig::with('user')
            ->where('is_active', true)
            ->get();
            
        // Get all user IDs for batch query
        $userIds = $configs->pluck('user_id')->unique();
        
        // Batch fetch retention stats for all users - OPTIMIZED: Single query instead of N queries
        $retentionStats = Transaction::select('user_id')
            ->selectRaw('COUNT(*) as total_retained')
            ->selectRaw('SUM(net_amount) as total_retained_amount')
            ->where('is_retained', true)
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');
        
        $usersWithRetention = $configs->map(function ($config) use ($retentionStats) {
            $progress = $config->getCycleProgress();
            $stats = $retentionStats->get($config->user_id);
            
            return [
                'user' => $config->user,
                'config' => $config,
                'progress' => $progress,
                'total_retained' => $stats ? $stats->total_retained : 0,
                'total_retained_amount' => $stats ? $stats->total_retained_amount : 0,
            ];
        });
        
        // Get retained transactions with filters
        $query = Transaction::with(['user', 'gateway'])
            ->where('is_retained', true);
            
        // Apply filters
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $retainedTransactions = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Calculate stats
        $stats = [
            'users_with_retention' => UserRetentionConfig::where('is_active', true)->count(),
            'individual_configs' => UserRetentionConfig::count(),
            'total_retained' => Transaction::where('is_retained', true)->count(),
            'total_amount' => Transaction::where('is_retained', true)->sum('net_amount'),
            'active_configs' => UserRetentionConfig::where('is_active', true)->count(),
        ];
        
        // Get global config for modal
        $globalConfig = RetentionConfig::first();
        
        return view('admin.setup.retention-overview', compact(
            'usersWithRetention',
            'retainedTransactions',
            'stats',
            'globalConfig'
        ));
    }
    
    /**
     * Show retained sales
     */
    public function retainedSales(Request $request)
    {
        $query = Transaction::with(['user', 'gateway'])
            ->where('is_retained', true)
            ->withCount(['walletTransactions as refund_count' => function($q) {
                $q->where('type', 'debit')
                  ->where('category', 'refund');
            }]);
            
        // Apply filters
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Default sorting
        $query->orderBy('created_at', 'desc');
        
        $retainedSales = $query->paginate(20);
        
        // Calculate totals
        $totalRetained = Transaction::where('is_retained', true)->count();
        $totalRetainedAmount = Transaction::where('is_retained', true)
            ->where(function($q) {
                $q->whereNotIn('status', ['refunded', 'partially_refunded', 'chargeback'])
                  ->orWhereNull('status');
            })
            ->sum('amount');
            
        // Calculate refunded totals
        $totalRefunded = Transaction::where('is_retained', true)
            ->whereIn('status', ['refunded', 'partially_refunded', 'chargeback'])
            ->count();
            
        $totalRefundedAmount = Transaction::where('is_retained', true)
            ->whereIn('status', ['refunded', 'partially_refunded', 'chargeback'])
            ->sum('amount');
            
        // Calculate gateway fees - OPTIMIZED: Load all transactions with gateway in one query
        $totalGatewayFees = 0;
        $transactions = Transaction::where('is_retained', true)->with('gateway')->get();
        foreach ($transactions as $transaction) {
            if ($transaction->gateway) {
                $gatewayFee = $this->calculateGatewayFee($transaction);
                $totalGatewayFees += $gatewayFee;
            }
        }
        
        return view('admin.setup.retained-sales', compact(
            'retainedSales', 
            'totalRetained', 
            'totalRetainedAmount',
            'totalRefunded',
            'totalRefundedAmount',
            'totalGatewayFees'
        ));
    }
    
    /**
     * Calculate gateway fee for a transaction
     */
    private function calculateGatewayFee($transaction)
    {
        $gateway = $transaction->gateway;
        $method = $transaction->payment_method;
        $amount = $transaction->amount;
        
        if (!$gateway) {
            return 0;
        }
        
        // Get gateway fee for this method
        $gatewayFee = $gateway->getFeeForMethod($method);
        
        if (!$gatewayFee) {
            // Use default fees if not configured
            $defaultFees = [
                'pix' => ['percentage' => 0.99, 'fixed' => 0.00],
                'credit_card' => ['percentage' => 2.99, 'fixed' => 0.30],
                'bank_slip' => ['percentage' => 1.99, 'fixed' => 1.50],
            ];
            
            $fee = $defaultFees[$method] ?? ['percentage' => 0, 'fixed' => 0];
            $percentageFee = ($amount * $fee['percentage']) / 100;
            $totalFee = $percentageFee + $fee['fixed'];
            
            return $totalFee;
        }
        
        // Calculate fee using the gateway fee model
        $feeData = $gatewayFee->calculateFee($amount);
        return $feeData['total_fee'];
    }
    
    /**
     * Show retained sale details
     */
    public function retainedSaleDetails($transactionId)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)
            ->where('is_retained', true)
            ->with(['user', 'gateway'])
            ->firstOrFail();
            
        return view('admin.setup.retained-sale-details', compact('transaction'));
    }
    
    /**
     * Return a retained sale to the user
     */
    public function returnRetainedSale($transactionId)
    {
        try {
            DB::beginTransaction();
            
            // Find the transaction
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('is_retained', true)
                ->with(['user', 'gateway'])
                ->firstOrFail();
            
            // Update transaction to not retained
            $transaction->is_retained = false;
            $transaction->save();
            
            // Process payment for the user
            $user = $transaction->user;
            $wallet = $user->wallet;
            
            if ($wallet) {
                // CRITICAL: Check if payment was already processed to prevent duplicate credits
                $existingCredit = \App\Models\WalletTransaction::where('reference_id', $transaction->transaction_id)
                    ->where('type', 'credit')
                    ->where('category', 'payment_received')
                    ->first();
                    
                if ($existingCredit) {
                    Log::warning('Payment already processed for returned transaction, skipping duplicate credit', [
                        'transaction_id' => $transaction->transaction_id,
                        'user_id' => $user->id,
                        'existing_credit_id' => $existingCredit->id
                    ]);
                } else {
                // Add net amount to wallet
                $wallet->addCredit(
                    $transaction->net_amount,
                    'payment_received',
                    "Pagamento recebido - {$transaction->transaction_id}",
                    [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $transaction->external_id,
                        'payment_method' => $transaction->payment_method,
                        'gateway' => $transaction->gateway ? $transaction->gateway->slug : 'unknown',
                        'returned_by_admin' => true
                    ],
                    $transaction->transaction_id
                );
                }
                
                Log::info('Pagamento retido devolvido e adicionado à wallet', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => $transaction->net_amount,
                    'wallet_credit_added' => !$existingCredit
                ]);
            }
            
            // Dispatch webhook event for transaction.paid
            $webhookService = new WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.paid');
            
            // Enviar para UTMify se integração estiver ativa
            try {
                $utmifyService = new \App\Services\UtmifyService();
                $utmifyService->sendTransaction($transaction, 'paid');
            } catch (\Exception $e) {
                Log::error('Erro ao enviar transação para UTMify (paid) - SetupController', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.setup.retained-sales')
                ->with('success', 'Venda devolvida com sucesso para o usuário!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao devolver venda retida: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Erro ao devolver venda: ' . $e->getMessage()]);
        }
    }
}
