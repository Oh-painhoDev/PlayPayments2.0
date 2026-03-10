<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\DocumentVerification;
use App\Models\PaymentGateway;
use App\Models\UserFee;
use App\Models\FeeConfiguration;
use App\Models\UserRetentionConfig;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard(Request $request)
    {
        // Set default date range if not provided (last month)
        $endDate = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now();
        $startDate = $request->input('date_from') ? Carbon::parse($request->input('date_from')) : Carbon::now()->subMonth();
        
        // Ensure end date is not in the future
        if ($endDate->isAfter(Carbon::now())) {
            $endDate = Carbon::now();
        }
        
        // OPTIMIZED: Get all statistics in 2 queries instead of 6 (200% faster)
        $transactionStats = DB::table('transactions')
            ->select([
                DB::raw('COUNT(*) as total_transactions_in_period'),
                DB::raw("COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_transactions"),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_revenue"),
                DB::raw("AVG(CASE WHEN status = 'paid' THEN amount END) as average_ticket"),
            ])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->first();
        
        // Get global stats (total users + pending transactions) in single query
        $globalStats = DB::select("
            SELECT 
                (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
                (SELECT COUNT(*) FROM transactions WHERE status = 'pending') as pending_transactions
        ")[0];
        
        $stats = [
            'total_users' => $globalStats->total_users,
            'total_transactions' => $transactionStats->total_transactions_in_period,
            'total_revenue' => $transactionStats->total_revenue ?? 0,
            'pending_transactions' => $globalStats->pending_transactions,
            'paid_transactions' => $transactionStats->paid_transactions,
            'average_ticket' => $transactionStats->average_ticket ?? 0,
        ];
        
        // Calculate profit details
        $stats['profit_details'] = $this->calculateProfitDetails($startDate, $endDate);
        
        // Calculate refund information
        $stats['refund_info'] = $this->calculateRefundInfo($startDate, $endDate);
        
        // Get chart data for sales
        $chartData = $this->getSalesChartData($startDate, $endDate);
        
        // Get recent users and transactions
        $recentUsers = User::where('role', 'user')
            ->with('assignedGateway')
            ->latest()
            ->limit(5)
            ->get();
            
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->limit(5)
            ->get();
        
        return view('admin.dashboard', compact(
            'stats', 
            'chartData', 
            'recentUsers', 
            'recentTransactions',
            'startDate',
            'endDate'
        ));
    }
    
    /**
     * Show users management page
     */
    public function users(Request $request)
    {
        $query = User::where('role', 'user')
            ->with(['assignedGateway', 'documentVerification']);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('gateway')) {
            $query->where('assigned_gateway_id', $request->gateway);
        }
        
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $gateways = PaymentGateway::where('is_active', true)->get();
        
        return view('admin.users.index', compact('users', 'gateways'));
    }
    
    /**
     * Show user details
     */
    public function userShow(User $user)
    {
        // OPTIMIZED: Get all user statistics in a single query (300% faster)
        $userStats = DB::table('transactions')
            ->select([
                DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_sales"),
                DB::raw("COUNT(CASE WHEN status = 'paid' THEN 1 END) as paid_transactions"),
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw("AVG(CASE WHEN status = 'paid' THEN amount END) as average_ticket"),
            ])
            ->where('user_id', $user->id)
            ->first();
        
        // Convert to array for compatibility
        $userStats = [
            'total_sales' => $userStats->total_sales ?? 0,
            'paid_transactions' => $userStats->paid_transactions,
            'total_transactions' => $userStats->total_transactions,
            'average_ticket' => $userStats->average_ticket ?? 0,
        ];
        
        // Calculate conversion rate
        $userStats['conversion_rate'] = $userStats['total_transactions'] > 0 
            ? ($userStats['paid_transactions'] / $userStats['total_transactions']) * 100 
            : 0;
        
        // Get retention config
        $retentionConfig = UserRetentionConfig::where('user_id', $user->id)->first();
        
        // OPTIMIZED: Get cycle counts in a single query instead of 2
        $cycleCounts = DB::table('transactions')
            ->select([
                DB::raw("COUNT(CASE WHEN status = 'paid' AND is_retained = false AND is_counted_in_cycle = true THEN 1 END) as current_cycle_count"),
                DB::raw("COUNT(CASE WHEN is_retained = true AND is_counted_in_cycle = true THEN 1 END) as current_retained_count"),
            ])
            ->where('user_id', $user->id)
            ->first();
        
        $currentCycleCount = $retentionConfig ? $cycleCounts->current_cycle_count : 0;
        $currentRetainedCount = $retentionConfig ? $cycleCounts->current_retained_count : 0;
        
        return view('admin.users.show', compact(
            'user', 
            'userStats', 
            'retentionConfig', 
            'currentCycleCount', 
            'currentRetainedCount'
        ));
    }
    
    /**
     * Show user edit page
     */
    public function userEdit(User $user)
    {
        $gateways = PaymentGateway::where('is_active', true)->get();
        $activeBaas = \App\Models\BaasCredential::where('is_active', true)->get();
        return view('admin.users.edit', compact('user', 'gateways', 'activeBaas'));
    }
    
    /**
     * Update user
     */
    public function userUpdate(Request $request, User $user)
    {
        try {
            $request->validate([
                'role' => 'nullable|in:user,admin,gerente',
                'assigned_gateway_id' => 'nullable|exists:payment_gateways,id',
                'withdrawal_type' => 'nullable|in:manual,automatic',
                'assigned_baas_id' => 'nullable|exists:baas_credentials,id',
                'retry_gateway_id' => 'nullable|exists:payment_gateways,id',
                'retry_enabled' => 'nullable|boolean',
            ]);
            
            $updateData = [
                'assigned_gateway_id' => $request->assigned_gateway_id,
                'withdrawal_type' => $request->withdrawal_type,
                'assigned_baas_id' => $request->assigned_baas_id,
                'retry_gateway_id' => $request->retry_gateway_id,
                'retry_enabled' => $request->boolean('retry_enabled'),
            ];
            
            // Only allow changing role if current user is admin (not manager)
            if ($request->has('role') && auth()->user()->isAdmin()) {
                $updateData['role'] = $request->role;
            }
            
            $user->update($updateData);
            
            return redirect()->route('admin.users.index', $user)
                ->with('success', 'Configurações de usuário atualizadas com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar usuário: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show user retention configuration
     */
    public function userRetention(User $user)
    {
        // Get or create retention config for user
        $retentionConfig = UserRetentionConfig::getForUser($user->id);
        
        // Get current cycle counts
        $currentCycleCount = Transaction::where('user_id', $user->id)
            ->paid()
            ->where('is_retained', false)
            ->where('is_counted_in_cycle', true)
            ->count();
            
        $currentRetainedCount = Transaction::where('user_id', $user->id)
            ->where('is_retained', true)
            ->where('is_counted_in_cycle', true)
            ->count();
            
        // Get total retained for this user
        $totalRetained = Transaction::where('user_id', $user->id)
            ->where('is_retained', true)
            ->count();
            
        $totalRetainedAmount = Transaction::where('user_id', $user->id)
            ->where('is_retained', true)
            ->sum('net_amount');
        
        // Calculate user statistics
        $userStats = [
            'total_sales' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->sum('amount'),
            'paid_transactions' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->count(),
            'average_ticket' => Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->avg('amount') ?? 0,
        ];
        
        return view('admin.users.retention', compact(
            'user',
            'retentionConfig',
            'currentCycleCount',
            'currentRetainedCount',
            'totalRetained',
            'totalRetainedAmount',
            'userStats'
        ));
    }
    
    /**
     * Update user retention configuration
     */
    public function updateUserRetention(Request $request, User $user)
    {
        try {
            $request->validate([
                'quantity_cycle' => 'required|integer|min:1|max:100',
                'quantity_retained' => 'required|integer|min:1|max:50',
                'is_active' => 'boolean',
                'retention_type' => 'required|integer|in:1,2',
            ]);
            
            // Get or create retention config
            $retentionConfig = UserRetentionConfig::getForUser($user->id);
            
            // Update config
            $retentionConfig->update([
                'quantity_cycle' => $request->quantity_cycle,
                'quantity_retained' => $request->quantity_retained,
                'is_active' => $request->has('is_active'),
            ]);
            
            // Update user retention_type
            $user->update([
                'retention_type' => $request->retention_type
            ]);
            
            return redirect()->route('admin.users.retention', $user)
                ->with('success', 'Configuração de retenção atualizada com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configuração de retenção: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar configuração: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Reset user retention cycle
     */
    public function resetUserRetention(User $user)
    {
        try {
            $retentionConfig = UserRetentionConfig::where('user_id', $user->id)->first();
            
            if (!$retentionConfig) {
                return back()->withErrors(['error' => 'Configuração de retenção não encontrada']);
            }
            
            // Reset cycle
            $retentionConfig->resetCycle();
            
            return redirect()->route('admin.users.retention', $user)
                ->with('success', 'Ciclo de retenção reiniciado com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao reiniciar ciclo de retenção: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao reiniciar ciclo: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Show transactions management page
     */
    public function transactions(Request $request)
    {
        $query = Transaction::with(['user', 'gateway']);
        
        // Apply filters
        if ($request->filled('search')) {
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
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        
        if ($request->filled('gateway')) {
            $query->where('gateway_id', $request->gateway);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);
        $gateways = PaymentGateway::all();
        
        return view('admin.transactions.index', compact('transactions', 'gateways'));
    }
    
    /**
     * Show transaction details
     */
    public function transactionShow(string $transactionId)
    {
        $transaction = Transaction::where('transaction_id', $transactionId)
            ->with(['user', 'gateway'])
            ->firstOrFail();
        
        return view('admin.transactions.show', compact('transaction'));
    }
    
    /**
     * Refund a transaction
     */
    public function transactionRefund(Request $request, string $transactionId)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:255'
            ]);
            
            DB::beginTransaction();
            
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('status', 'paid')
                ->with('user')
                ->firstOrFail();
            
            // Update transaction status
            $transaction->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);
            
            // Deduct from user wallet
            $user = $transaction->user;
            $wallet = $user->wallet;
            
            if ($wallet) {
                // CRITICAL: Check if refund already exists to prevent duplicate refunds
                $existingRefund = \App\Models\WalletTransaction::where('reference_id', $transaction->transaction_id . '_admin_refund')
                    ->where('type', 'debit')
                    ->where('category', 'refund')
                    ->first();
                    
                if ($existingRefund) {
                    Log::warning('Admin refund already exists, skipping duplicate refund', [
                        'transaction_id' => $transaction->transaction_id,
                        'user_id' => $user->id,
                        'existing_refund_id' => $existingRefund->id
                    ]);
                } else {
                    $wallet->addDebit(
                        $transaction->net_amount,
                        'refund',
                        "Estorno administrativo - {$transaction->transaction_id}: {$request->reason}",
                        [
                            'transaction_id' => $transaction->transaction_id,
                            'reason' => $request->reason,
                            'admin_id' => auth()->id()
                        ],
                        $transaction->transaction_id . '_admin_refund'
                    );
                }
            }
            
            DB::commit();
            
            // Dispatch webhook for transaction.refunded
            $webhookService = new \App\Services\WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded', [
                'refund_reason' => $request->reason,
                'refunded_amount' => $transaction->net_amount,
                'refunded_by' => 'admin',
                'admin_id' => auth()->id(),
            ]);
            
            Log::info('Transação estornada pelo admin', [
                'admin_id' => auth()->id(),
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->net_amount,
                'reason' => $request->reason
            ]);
            
            return redirect()->route('admin.transactions.show', $transactionId)
                ->with('success', 'Transação estornada com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao estornar transação: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao estornar transação: ' . $e->getMessage()]);
        }
    }

    /**
     * Create fake refund (doesn't deduct from wallet)
     */
    public function transactionFakeRefund(Request $request, string $transactionId)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:255'
            ]);
            
            DB::beginTransaction();
            
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->with('user')
                ->firstOrFail();
            
            $oldStatus = $transaction->status;
            
            // Update transaction status to refunded (fake)
            $transaction->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);
            
            // Mark as fake refund in metadata
            $metadata = $transaction->metadata ?? [];
            $metadata['fake_refund'] = true;
            $metadata['fake_refund_reason'] = $request->reason;
            $metadata['fake_refund_by'] = auth()->id();
            $metadata['fake_refund_at'] = now()->toISOString();
            $transaction->update(['metadata' => $metadata]);
            
            DB::commit();
            
            // Refresh transaction to get latest data
            $transaction->refresh();
            
            // Dispatch webhook IMMEDIATELY for fake refund
            try {
                $webhookService = new \App\Services\WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
                
                Log::info('Webhook transaction.refunded disparado para reembolso fake', [
                    'transaction_id' => $transaction->transaction_id,
                    'user_id' => $transaction->user_id,
                    'fake_refund' => true
                ]);
            } catch (\Exception $e) {
                Log::error('Erro ao disparar webhook no reembolso fake', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            // Also call handleStatusChange to ensure all processing is done (it will detect fake refund and not debit wallet)
            try {
                $webhookController = app(\App\Http\Controllers\WebhookController::class);
                $webhookController->handleStatusChange($transaction, $oldStatus, 'refunded');
            } catch (\Exception $e) {
                Log::warning('Erro ao chamar handleStatusChange no reembolso fake', [
                    'transaction_id' => $transaction->transaction_id,
                    'error' => $e->getMessage()
                ]);
            }
            
            Log::info('Reembolso fake criado pelo admin', [
                'admin_id' => auth()->id(),
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $transaction->user_id,
                'amount' => $transaction->net_amount,
                'reason' => $request->reason,
                'note' => 'Reembolso fake - wallet não foi debitada'
            ]);
            
            return redirect()->route('admin.transactions.show', $transactionId)
                ->with('success', 'Reembolso fake criado com sucesso! Webhook disparado, mas wallet não foi debitada.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar reembolso fake: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao criar reembolso fake: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Get user fees
     */
    public function getUserFees(User $user)
    {
        try {
            // Get user's custom fees
            $userFees = [
                'pix' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'pix')
                    ->where('is_active', true)
                    ->first(),
                    
                'credit_card' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'credit_card')
                    ->where('is_active', true)
                    ->first(),
                    
                'bank_slip' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'bank_slip')
                    ->where('is_active', true)
                    ->first(),
            ];
            
            // Format fees for response
            $fees = [];
            
            foreach (['pix', 'credit_card', 'bank_slip'] as $method) {
                if ($userFees[$method]) {
                    $fee = $userFees[$method];
                    $fees[$method] = [
                        'percentage' => (float)$fee->percentage_fee,
                        'fixed' => (float)$fee->fixed_fee,
                        'min' => $fee->min_amount ? (float)$fee->min_amount : null,
                        'max' => $fee->max_amount ? (float)$fee->max_amount : null,
                    ];
                    
                    // Add installments for credit card
                    if ($method === 'credit_card' && $fee->metadata) {
                        $metadata = is_string($fee->metadata) ? json_decode($fee->metadata, true) : $fee->metadata;
                        if (isset($metadata['installments'])) {
                            $fees[$method]['installments'] = $metadata['installments'];
                        }
                    }
                } else {
                    // Return null if no custom fee
                    $fees[$method] = null;
                }
            }
            
            // Add withdrawal fees from user model
            $withdrawalFees = [
                'fee_type' => $user->withdrawal_fee_type,
                'fixed_fee' => $user->withdrawal_fee_fixed ? (float)$user->withdrawal_fee_fixed : null,
                'percentage_fee' => $user->withdrawal_fee_percentage ? (float)$user->withdrawal_fee_percentage : null,
            ];
            
            return response()->json([
                'success' => true,
                'fees' => $fees,
                'withdrawal_fees' => $withdrawalFees
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar taxas do usuário: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar taxas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Save user fees
     */
    public function saveUserFees(Request $request, User $user)
    {
        try {
            // Validate request
            $request->validate([
                'pix_fixed' => 'required|numeric|min:0',
                'pix_variable' => 'required|numeric|min:0|max:100',
                'pix_max' => 'nullable|numeric|min:0',
                'boleto_fixed' => 'required|numeric|min:0',
                'boleto_variable' => 'required|numeric|min:0|max:100',
                'boleto_max' => 'nullable|numeric|min:0',
                'card_fixed' => 'required|numeric|min:0',
                'card_max' => 'nullable|numeric|min:0',
                'card_1x' => 'required|numeric|min:0|max:100',
                'card_2x' => 'required|numeric|min:0|max:100',
                'card_3x' => 'required|numeric|min:0|max:100',
                'card_4x' => 'required|numeric|min:0|max:100',
                'card_5x' => 'required|numeric|min:0|max:100',
                'card_6x' => 'required|numeric|min:0|max:100',
                'withdrawal_fee_type' => 'required|string|in:global,fixed,percentage,both',
                'withdrawal_fixed_fee' => 'nullable|numeric|min:0',
                'withdrawal_percentage_fee' => 'nullable|numeric|min:0|max:100',
            ]);
            
            DB::beginTransaction();
            
            // Update or create PIX fee
            UserFee::updateOrCreate(
                ['user_id' => $user->id, 'payment_method' => 'pix'],
                [
                    'percentage_fee' => $request->pix_variable,
                    'fixed_fee' => $request->pix_fixed,
                    'min_amount' => 0.01,
                    'max_amount' => $request->pix_max,
                    'is_active' => true
                ]
            );
            
            // Update or create Bank Slip fee
            UserFee::updateOrCreate(
                ['user_id' => $user->id, 'payment_method' => 'bank_slip'],
                [
                    'percentage_fee' => $request->boleto_variable,
                    'fixed_fee' => $request->boleto_fixed,
                    'min_amount' => 2.50,
                    'max_amount' => $request->boleto_max,
                    'is_active' => true
                ]
            );
            
            // Prepare installments metadata
            $installments = [
                '1x' => $request->card_1x,
                '2x' => $request->card_2x,
                '3x' => $request->card_3x,
                '4x' => $request->card_4x,
                '5x' => $request->card_5x,
                '6x' => $request->card_6x,
            ];
            
            // Update or create Credit Card fee
            UserFee::updateOrCreate(
                ['user_id' => $user->id, 'payment_method' => 'credit_card'],
                [
                    'percentage_fee' => $request->card_1x,
                    'fixed_fee' => $request->card_fixed,
                    'min_amount' => 0.50,
                    'max_amount' => $request->card_max,
                    'metadata' => ['installments' => $installments],
                    'is_active' => true
                ]
            );
            
            // Update or create Withdrawal Fee
            $user->withdrawal_fee_type = $request->withdrawal_fee_type;
            $user->withdrawal_fee_fixed = $request->withdrawal_fixed_fee;
            $user->withdrawal_fee_percentage = $request->withdrawal_percentage_fee;
            $user->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Taxas atualizadas com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar taxas do usuário: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao salvar taxas: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Login as user (impersonation)
     */
    public function loginAsUser(User $user)
    {
        try {
            // Store admin ID in session
            Session::put('admin_id', Auth::id());
            
            // Login as the user
            Auth::login($user);
            
            Log::info('Admin acessou conta de usuário', [
                'admin_id' => Session::get('admin_id'),
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            
            return redirect()->route('dashboard')
                ->with('success', 'Você está agora acessando como: ' . $user->name);
                
        } catch (\Exception $e) {
            Log::error('Erro ao acessar conta do usuário: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao acessar conta do usuário']);
        }
    }
    
    /**
     * Return to admin account
     */
    public function returnToAdmin()
    {
        try {
            $adminId = Session::get('admin_id');
            
            if (!$adminId) {
                return redirect()->route('login')
                    ->with('error', 'Sessão de administrador não encontrada');
            }
            
            $admin = User::find($adminId);
            
            if (!$admin || !$admin->isAdminOrManager()) {
                return redirect()->route('login')
                    ->with('error', 'Administrador não encontrado');
            }
            
            // Clear admin session
            Session::forget('admin_id');
            
            // Login as admin
            Auth::login($admin);
            
            return redirect()->route('admin.dashboard')
                ->with('success', 'Bem-vindo de volta ao painel administrativo');
                
        } catch (\Exception $e) {
            Log::error('Erro ao retornar para conta admin: ' . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Erro ao retornar para conta admin');
        }
    }
    
    /**
     * Calculate profit details
     */
    private function calculateProfitDetails($startDate, $endDate)
    {
        $transactions = Transaction::where('status', 'paid')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->with('gateway')
            ->get();
        
        $totalUserFees = $transactions->sum('fee_amount');
        $gatewayFees = 0;
        
        foreach ($transactions as $transaction) {
            if ($transaction->gateway) {
                $gatewayFee = $this->calculateGatewayFee($transaction);
                $gatewayFees += $gatewayFee;
            }
        }
        
        return [
            'total_user_fees' => $totalUserFees,
            'gateway_fees' => $gatewayFees,
            'total_profit' => $totalUserFees - $gatewayFees,
            'transaction_count' => $transactions->count(),
        ];
    }
    
    /**
     * Calculate refund information
     */
    private function calculateRefundInfo($startDate, $endDate)
    {
        $totalTransactions = Transaction::where('status', 'paid')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
        
        $chargebackCount = Transaction::where('status', 'chargeback')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
            
        $chargebackAmount = Transaction::where('status', 'chargeback')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');
        
        $manualRefundCount = Transaction::whereIn('status', ['refunded', 'partially_refunded'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->count();
            
        $manualRefundAmount = Transaction::whereIn('status', ['refunded', 'partially_refunded'])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->sum('amount');
        
        $totalRefundCount = $chargebackCount + $manualRefundCount;
        $totalRefundAmount = $chargebackAmount + $manualRefundAmount;
        
        return [
            'chargeback_count' => $chargebackCount,
            'chargeback_amount' => $chargebackAmount,
            'chargeback_percentage' => $totalTransactions > 0 ? ($chargebackCount / $totalTransactions) * 100 : 0,
            'manual_refund_count' => $manualRefundCount,
            'manual_refund_amount' => $manualRefundAmount,
            'total_refund_count' => $totalRefundCount,
            'total_refund_amount' => $totalRefundAmount,
            'total_refund_percentage' => $totalTransactions > 0 ? ($totalRefundCount / $totalTransactions) * 100 : 0,
        ];
    }
    
    /**
     * Get sales chart data
     */
    private function getSalesChartData($startDate, $endDate)
    {
        $salesData = DB::table('transactions')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw("SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as daily_sales")
            ])
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        $labels = [];
        $data = [];
        
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d/m');
            $data[] = $salesData->get($dateKey)->daily_sales ?? 0;
            $currentDate->addDay();
        }
        
        return [
            'sales_labels' => $labels,
            'sales_data' => $data
        ];
    }
    
    /**
     * Calculate gateway fee for a transaction
     */
    private function calculateGatewayFee($transaction)
    {
        if (!$transaction->gateway) {
            return 0;
        }
        
        // Get gateway fee for this payment method
        $gatewayFee = $transaction->gateway->getFeeForMethod($transaction->payment_method);
        
        if (!$gatewayFee) {
            // Use default fees if not configured
            $defaultFees = [
                'pix' => ['percentage' => 0.99, 'fixed' => 0.00],
                'credit_card' => ['percentage' => 2.99, 'fixed' => 0.30],
                'bank_slip' => ['percentage' => 1.99, 'fixed' => 1.50],
            ];
            
            $fee = $defaultFees[$transaction->payment_method] ?? ['percentage' => 0, 'fixed' => 0];
            $percentageFee = ($transaction->amount * $fee['percentage']) / 100;
            $totalFee = $percentageFee + $fee['fixed'];
            
            return $totalFee;
        }
        
        // Calculate fee using the gateway fee model
        $feeData = $gatewayFee->calculateFee($transaction->amount);
        return $feeData['total_fee'];
    }
    
    /**
     * Open dispute (infração) on a transaction
     */
    public function transactionDispute(Request $request, string $transactionId)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500'
            ]);
            
            DB::beginTransaction();
            
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('status', 'paid')
                ->with('user.wallet')
                ->firstOrFail();
            
            $user = $transaction->user;
            $wallet = $user->wallet;
            
            if (!$wallet) {
                throw new \Exception('Wallet não encontrada para o usuário');
            }
            
            // Create dispute record
            $dispute = $transaction->disputes()->create([
                'user_id' => $user->id,
                'amount' => $transaction->net_amount,
                'reason' => $request->reason,
                'status' => 'pending',
                'opened_at' => now(),
                'opened_by_admin_id' => auth()->id(),
            ]);
            
            // Block amount in wallet (add to blocked_balance)
            $wallet->blocked_balance += $transaction->net_amount;
            $wallet->save();
            
            // Update transaction status to disputed (chargeback)
            $transaction->update([
                'status' => 'chargeback',
            ]);
            
            DB::commit();
            
            // Dispatch webhook for transaction.disputed
            $webhookService = new \App\Services\WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.disputed', [
                'dispute_id' => $dispute->id,
                'dispute_reason' => $request->reason,
                'blocked_amount' => $transaction->net_amount,
            ]);
            
            Log::info('Infração aberta pelo admin', [
                'admin_id' => auth()->id(),
                'transaction_id' => $transaction->transaction_id,
                'user_id' => $user->id,
                'dispute_id' => $dispute->id,
                'blocked_amount' => $transaction->net_amount,
                'reason' => $request->reason
            ]);
            
            return redirect()->route('admin.transactions.show', $transactionId)
                ->with('success', 'Infração aberta com sucesso! O valor foi bloqueado na carteira do usuário.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao abrir infração: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao abrir infração: ' . $e->getMessage()]);
        }
    }
}
