<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\FeeConfiguration;
use App\Models\Withdrawal;
use App\Models\UserGatewayCredential;
use App\Models\PaymentGateway;
use App\Models\BaasCredential;
use App\Helpers\FeeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Show withdrawals page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Garantir que o wallet exista
        if (!$user->wallet) {
            \App\Models\Wallet::create([
                'user_id' => $user->id,
                'balance' => 0.00,
                'currency' => 'BRL',
                'is_active' => true,
            ]);
            $user->refresh();
        }
        
        // Base query for transactions
        $query = \App\Models\Transaction::where('user_id', $user->id);

        // Advanced Filtering
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('customer_data', 'like', "%{$search}%")
                  ->orWhere('external_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('method') && $request->method !== 'all') {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('date_start')) {
            $query->whereDate('created_at', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $query->whereDate('created_at', '<=', $request->date_end);
        }
        
        $availableBalance = $user->wallet_balance;
        
        // Calcular saldos por método de pagamento (apenas para exibição)
        $pixBalance = $availableBalance; 
        $cardBalance = 0.00;
        
        // Saldo a receber (transações pendentes que ainda não foram pagas)
        $pendingAmount = \App\Models\Transaction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('net_amount');
        
        // Reserva financeira (saldo bloqueado/reservado)
        $reservedBalance = $user->wallet->blocked_balance ?? 0.00;
        
        // Execute filtered query
        $transactions = $query->with(['gateway', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->all());
        
        // Extended Stats for Wallet Dashboard
        $statsBase = \App\Models\Transaction::where('user_id', $user->id);
        
        $todayStats = (clone $statsBase)->whereDate('created_at', now())->where('status', 'paid');
        $revenueToday = $todayStats->sum('net_amount');
        $paidTodayCount = $todayStats->count();

        $allTimePaid = (clone $statsBase)->where('status', 'paid');
        $avgTicket = $allTimePaid->avg('amount') ?? 0;
        
        $totalCount = (clone $statsBase)->count();
        $paidCount = (clone $statsBase)->where('status', 'paid')->count();
        $conversionRate = $totalCount > 0 ? ($paidCount / $totalCount) * 100 : 0;

        // Growth comparison
        $yesterdayRevenue = (clone $statsBase)->whereDate('created_at', now()->subDay())->where('status', 'paid')->sum('net_amount');
        $growth = $yesterdayRevenue > 0 ? (($revenueToday - $yesterdayRevenue) / $yesterdayRevenue) * 100 : 0;

        // Chart Data (Last 7 Days)
        $chartDataReceived = [];
        $chartDataWithdrawals = [];
        $chartLabels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            // Label em português: D, S, T, Q, Q, S, S
            $diasSemana = ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'];
            $chartLabels[] = $diasSemana[$date->dayOfWeek];
            
            // Recebimentos no dia
            $received = \App\Models\Transaction::where('user_id', $user->id)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'paid')
                ->sum('net_amount');
            $chartDataReceived[] = $received;
                
            // Saques no dia
            $withdrawn = \App\Models\Withdrawal::where('user_id', $user->id)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->whereIn('status', ['completed', 'approved', 'processing', 'pending'])
                ->sum('amount');
            $chartDataWithdrawals[] = $withdrawn;
        }
        
        // Find max value to normalize chart heights
        $maxChartVal = max(
            count($chartDataReceived) > 0 ? max($chartDataReceived) : 1,
            count($chartDataWithdrawals) > 0 ? max($chartDataWithdrawals) : 1
        );
        if ($maxChartVal <= 0) $maxChartVal = 1;

        // Buscar saques
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Buscar disputes (reembolsos/MEDs)
        $disputes = \App\Models\Dispute::where('user_id', $user->id)
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Buscar chaves PIX
        $pixKeys = \App\Models\PixKey::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalKeys = $pixKeys->count();
        $activeKeys = $pixKeys->where('status', 'active')->count();
        $availableSlots = 2 - $totalKeys; // Limite máximo de 2 chaves
        
        // Calcular saldo Crypto (por enquanto, usar o mesmo saldo disponível)
        $cryptoBalance = $availableBalance;
        
        // Get user's withdrawal fee (considers custom fees)
        $feeData = FeeHelper::getUserWithdrawalFee($user, 0);
        
        // Get minimum withdrawal amount from global configuration
        $globalWithdrawalFee = \App\Models\FeeConfiguration::where('payment_method', 'withdrawal')
            ->where('is_global', true)
            ->where('is_active', true)
            ->first();
        
        $minWithdrawalAmount = $globalWithdrawalFee && $globalWithdrawalFee->min_transaction_value 
            ? (float)$globalWithdrawalFee->min_transaction_value 
            : 10.00; // Default minimum
        
        return view('wallet.index', [
            'withdrawals' => $withdrawals,
            'transactions' => $transactions,
            'disputes' => $disputes,
            'pixKeys' => $pixKeys,
            'pixKeysStats' => [
                'total' => $totalKeys,
                'active' => $activeKeys,
                'available' => max(0, $availableSlots),
            ],
            'user' => $user,
            'fee' => $feeData['fixed_fee'], // For backward compatibility
            'feeData' => $feeData,
            'minWithdrawalAmount' => $minWithdrawalAmount,
            'pixBalance' => $pixBalance,
            'cryptoBalance' => $cryptoBalance,
            'pendingAmount' => $pendingAmount,
            'reservedBalance' => $reservedBalance,
            'availableBalance' => $availableBalance,
            'filters' => $request->all(),
            'chart' => [
                'dataReceived' => $chartDataReceived,
                'dataWithdrawals' => $chartDataWithdrawals,
                'labels' => $chartLabels,
                'max' => $maxChartVal
            ],
            'stats' => [
                'revenue_today' => $revenueToday,
                'paid_today_count' => $paidTodayCount,
                'avg_ticket' => $avgTicket,
                'conversion_rate' => $conversionRate,
                'growth' => $growth
            ]
        ]);
    }
    
    /**
     * Show withdrawal form
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get user's withdrawal fee (considers custom fees)
        $feeData = FeeHelper::getUserWithdrawalFee($user, 0);
        
        return view('withdrawals.create', [
            'user' => $user,
            'fee' => $feeData['fixed_fee'], // For backward compatibility
            'feeData' => $feeData,
        ]);
    }
    
    /**
     * Process withdrawal request
     */
    public function store(Request $request)
    {
        try {
            // Iniciar transação de banco de dados para garantir consistência
            DB::beginTransaction();
            
            $user = Auth::user();
            
            // Obter wallet com lock para evitar condições de corrida
            $wallet = $user->wallet()->lockForUpdate()->first();
            
            if (!$wallet) {
                DB::rollBack();
                // Se for requisição AJAX, retornar JSON
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Carteira não encontrada'
                    ], 404);
                }
                return back()->withErrors(['error' => 'Carteira não encontrada'])->withInput();
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
                'pix_type' => 'required|in:email,cpf,phone,random',
                'pix_key' => 'required|string|max:255',
            ], [
                'amount.min' => "O valor mínimo para saque é R$ " . number_format($minWithdrawalAmount, 2, ',', '.'),
            ]);
            
            if ($validator->fails()) {
                // Se for requisição AJAX, retornar JSON
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erro de validação',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
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
            
            // Verificar se já existe um saque pendente com os mesmos dados
            $existingWithdrawal = Withdrawal::where('user_id', $user->id)
                ->where('amount', $requestedAmount)
                ->where('pix_type', $pixType)
                ->where('pix_key', $pixKey)
                ->whereIn('status', ['pending', 'processing'])
                ->first();
                
            if ($existingWithdrawal) {
                DB::rollBack();
                // Se for requisição AJAX, retornar JSON
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Já existe um saque pendente com os mesmos dados. Por favor, aguarde a conclusão ou verifique na lista de saques.'
                    ], 422);
                }
                return back()->withErrors(['error' => 'Já existe um saque pendente com os mesmos dados. Por favor, aguarde a conclusão ou verifique na lista de saques.'])->withInput();
            }
            
            // Verificar se o usuário tem saldo disponível suficiente (valor + taxa)
            // IMPORTANTE: Sempre verificar se tem saldo para valor + taxa para evitar burlas
            $availableBalance = $wallet->available_balance;
            
            // Se o valor + taxa exceder o saldo, ajustar o valor para o máximo disponível
            // IMPORTANTE: Considerar apenas a taxa do usuário (taxa do BaaS é absorvida pelo sistema)
            if ($totalToDebit > $availableBalance) {
                // Calcular o valor máximo que pode ser sacado considerando apenas a taxa do usuário
                if ($user->withdrawal_fee_type === 'fixed' || $user->withdrawal_fee_type === 'global') {
                    $actualNetAmount = max(0, $availableBalance - $fee);
                } else if ($user->withdrawal_fee_type === 'percentage') {
                    $actualNetAmount = $availableBalance / (1 + ($user->withdrawal_fee_percentage / 100));
                } else if ($user->withdrawal_fee_type === 'both') {
                    $actualNetAmount = ($availableBalance - $user->withdrawal_fee_fixed) / (1 + ($user->withdrawal_fee_percentage / 100));
                }
                
                // Ajustar o valor solicitado para o máximo disponível
                $requestedAmount = $actualNetAmount;
                $feeCalculation = $user->calculateWithdrawalFee($requestedAmount, $baasGateway);
                $fee = $feeCalculation['fee']; // Taxa do usuário
                $baasFee = $feeCalculation['baas_fee'] ?? 0; // Taxa do BaaS (absorvida pelo sistema)
                $totalFee = $feeCalculation['total_fee']; // Total de taxa cobrada do usuário (sem BaaS)
                $totalToDebit = $feeCalculation['total_to_debit']; // Total a debitar (sem taxa BaaS)
                $netAmount = $feeCalculation['net_amount'];
            }
            
            // Verificar se após o ajuste ainda tem saldo suficiente
            if ($totalToDebit > $availableBalance || $requestedAmount <= 0) {
                DB::rollBack();
                $blockedMessage = $wallet->blocked_balance > 0 
                    ? ' (Saldo bloqueado: R$ ' . number_format($wallet->blocked_balance, 2, ',', '.') . ')'
                    : '';
                $errorMessage = 'Saldo disponível insuficiente para saque + taxa. Seu saldo disponível é R$ ' . number_format($availableBalance, 2, ',', '.') . '. Total necessário: R$ ' . number_format($totalToDebit, 2, ',', '.') . ' (Saque: R$ ' . number_format($requestedAmount, 2, ',', '.') . ' + Taxa: R$ ' . number_format($fee, 2, ',', '.') . ')' . $blockedMessage;
                
                // Se for requisição AJAX, retornar JSON
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => ['amount' => $errorMessage]
                    ], 422);
                }
                return back()->withErrors(['amount' => $errorMessage])->withInput();
            }
            
            // Create withdrawal record
            $withdrawal = new Withdrawal();
            $withdrawal->user_id = $user->id;
            $withdrawal->withdrawal_id = 'WDR_' . strtoupper(Str::random(8)) . '_' . time();
            $withdrawal->amount = $requestedAmount; // Valor que o usuário quer receber
            $withdrawal->fee = $totalFee; // Taxa cobrada do usuário (sem BaaS)
            $withdrawal->net_amount = $netAmount; // Valor líquido
            
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
            
            // IMPORTANTE: SEMPRE debitar o valor + taxa na criação do saque
            // Isso previne que o usuário crie múltiplos saques e "burle" o sistema
            // Se o saque for rejeitado, o valor será estornado
            $debitResult = $wallet->addDebit(
                $totalToDebit,
                'withdrawal',
                "Saque via PIX - {$withdrawal->withdrawal_id} (R$ {$requestedAmount} + taxa R$ {$fee})",
                [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'requested_amount' => $requestedAmount,
                    'fee' => $fee,
                    'total_debited' => $totalToDebit,
                    'pix_type' => $pixType,
                    'pix_key' => $pixKey,
                    'withdrawal_type' => $user->withdrawal_type,
                ],
                $withdrawal->withdrawal_id
            );
            
            // Verificar se o débito foi bem-sucedido
            if (!$debitResult) {
                DB::rollBack();
                // Se for requisição AJAX, retornar JSON
                if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Não foi possível debitar o valor da carteira. Verifique seu saldo.'
                    ], 422);
                }
                return back()->withErrors(['error' => 'Não foi possível debitar o valor da carteira. Verifique seu saldo.'])->withInput();
            }
            
            // Commit a transação - o débito já foi feito
            DB::commit();
            
            // Check if automatic withdrawal
            if ($user->withdrawal_type === 'automatic') {
                try {
                    // Process automatic withdrawal
                    $result = $this->processAutomaticWithdrawal($withdrawal);
                    
                    // If processing failed, mark as failed and refund
                    if (!$result) {
                        // Iniciar nova transação para o estorno
                        DB::beginTransaction();
                        
                        // Update withdrawal status
                        $withdrawal->update([
                            'status' => 'failed',
                            'error_message' => 'Falha ao processar saque automático. Saldo insuficiente ou erro no gateway.'
                        ]);
                        
                        // Refund the amount to wallet
                        $wallet->addCredit(
                            $totalToDebit,
                            'refund',
                            "Estorno de saque falho - {$withdrawal->withdrawal_id}",
                            [
                                'withdrawal_id' => $withdrawal->withdrawal_id,
                                'reason' => 'Falha no processamento automático'
                            ],
                            $withdrawal->withdrawal_id . '_refund'
                        );
                        
                        DB::commit();
                        
                        // Se for requisição AJAX, retornar JSON
                        if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Falha ao processar saque automático. O valor foi estornado para sua carteira.',
                                'withdrawal_id' => $withdrawal->withdrawal_id
                            ], 500);
                        }
                        
                        return redirect()->route('withdrawals.show', $withdrawal->withdrawal_id)
                            ->with('error', 'Falha ao processar saque automático. O valor foi estornado para sua carteira.');
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao processar saque automático no store: ' . $e->getMessage(), [
                        'withdrawal_id' => $withdrawal->withdrawal_id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    // Se for requisição AJAX, retornar JSON
                    if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Erro ao processar saque automático: ' . $e->getMessage(),
                            'withdrawal_id' => $withdrawal->withdrawal_id
                        ], 500);
                    }
                    
                    return redirect()->route('wallet.index')
                        ->with('error', 'Erro ao processar saque automático: ' . $e->getMessage());
                }
            }
            
            // Refresh withdrawal to get updated status
            $withdrawal->refresh();
            
            $message = $user->withdrawal_type === 'automatic' 
                ? 'Saque processado automaticamente! O valor será enviado em breve.'
                : 'Saque solicitado com sucesso! Aguarde a aprovação do administrador.';
            
            // Se for requisição AJAX, retornar JSON
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_automatic' => $user->withdrawal_type === 'automatic',
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'status' => $withdrawal->status
                ]);
            }
            
            return redirect()->route('wallet.index')
                ->with('success', $message);
            
        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();
            
            Log::error('Erro ao processar saque: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Se for requisição AJAX, retornar JSON
            if ($request->wantsJson() || $request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro interno ao processar saque. Tente novamente.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Erro interno ao processar saque. Tente novamente.'])->withInput();
        }
    }
    
    /**
     * Process automatic withdrawal via gateway
     * 
     * @return bool
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
            
            if ($baasGateway->gateway === 'strikecash') {
                return $this->processStrikeCashWithdrawal($withdrawal, $baasGateway);
            } else if ($baasGateway->gateway === 'cashtime') {
                return $this->processCashtimeWithdrawal($withdrawal, $baasGateway);
            } else if ($baasGateway->gateway === 'e2bank') {
                return $this->processE2BankWithdrawal($withdrawal, $baasGateway);
            } else if ($baasGateway->gateway === 'pluggou') {
                return $this->processPluggouWithdrawal($withdrawal, $baasGateway);
            } else {
                throw new \Exception('Unsupported BaaS gateway: ' . $baasGateway->gateway);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao processar saque automático: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update withdrawal with error
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Process withdrawal via StrikeCash
     * 
     * @return bool
     */
    protected function processStrikeCashWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            // Clean PIX key based on type
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            
            // Map PIX type to AvivHub type
            $pixTypeValue = $this->mapPixTypeToAvivHubValue($withdrawal->pix_type);
            
            // Get the correct webhook URL from .env
            $appUrl = config('app.url');
            $webhookUrl = rtrim($appUrl, '/') . '/webhook/withdrawal';
            
            // Prepare payload for AvivHub
            $payload = [
                'amount' => (int)round($withdrawal->net_amount * 100), // Convert to cents with proper rounding
                'externalRef' => $withdrawal->withdrawal_id,
                'postbackUrl' => $webhookUrl,
                'type' => $pixTypeValue,
                'pix' => $pixKey,
                'name' => $withdrawal->user->name,
                'document' => preg_replace('/[^0-9]/', '', $withdrawal->user->document)
            ];
            
            Log::info('Enviando saque automático para StrikeCash', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'payload' => $payload,
                'webhook_url' => $webhookUrl,
                'is_sandbox' => $baasGateway->is_sandbox
            ]);
            
            // Send request to StrikeCash
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'x-secret-key' => $baasGateway->secret_key,
                    'x-public-key' => $baasGateway->public_key,
                ])
                ->post('https://srv.strikecash.com.br/v1/withdraw', $payload);
                
            // Log the response
            Log::info('Resposta do StrikeCash para saque', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);
            
            // Check if successful
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Verificar se a resposta contém um status e se é "pending"
                // Se não tiver status "pending", significa que houve falha
                if (!isset($responseData['status']) || $responseData['status'] !== 'pending') {
                    // Se status não é pending ou não existe, algo deu errado
                    $errorMessage = isset($responseData['message']) ? 
                        (is_array($responseData['message']) ? implode(', ', $responseData['message']) : $responseData['message']) : 
                        'Erro no processamento: Status não é pending';
                    
                    // Verificar se há mensagem de erro específica sobre saldo
                    if (isset($responseData['message']) && is_array($responseData['message'])) {
                        foreach ($responseData['message'] as $msg) {
                            if (strpos($msg, 'Available balance') !== false) {
                                $errorMessage = 'Saldo insuficiente no gateway: ' . $msg;
                                break;
                            }
                        }
                    }
                    
                    // Update withdrawal with error
                    $withdrawal->update([
                        'status' => 'failed',
                        'error_message' => $errorMessage,
                        'response_data' => $responseData
                    ]);
                    
                    Log::error('Erro no status do saque automático', [
                        'withdrawal_id' => $withdrawal->id,
                        'response' => $responseData
                    ]);
                    
                    return false;
                }
                
                // Update withdrawal with response data
                $withdrawal->update([
                    'status' => 'processing',
                    'external_id' => $responseData['id'] ?? null,
                    'response_data' => $responseData
                ]);
                
                return true;
            } else {
                // Handle error
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? 'Unknown error';
                
                if (is_array($errorMessage)) {
                    $errorMessage = implode(', ', $errorMessage);
                }
                
                // Update withdrawal with error
                $withdrawal->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'response_data' => $response->json()
                ]);
                
                throw new \Exception('AvivHub API error: ' . $errorMessage);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar saque StrikeCash: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update withdrawal with error
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Process withdrawal via Cashtime
     * 
     * @return bool
     */
    protected function processCashtimeWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            // Clean PIX key based on type
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            
            // Get the correct webhook URL from .env
            $appUrl = config('app.url');
            $webhookUrl = rtrim($appUrl, '/') . '/webhook/cashtime';
                        
            // Prepare payload for Cashtime
            $payload = [
                'amount' => (int)round($withdrawal->net_amount * 100), // Convert to cents with proper rounding
                'pixKey' => $this->formatPixKeyForCashtime($pixKey, $withdrawal->pix_type),
                'pixKeyType' => $this->mapPixTypeToCashtimeValue($withdrawal->pix_type),
                'baasPostbackUrl' => $webhookUrl,
                'externalCode' => $withdrawal->withdrawal_id
            ];
            
            Log::info('Enviando saque automático para Cashtime', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'payload' => $payload,
                'webhook_url' => $webhookUrl,
                'is_sandbox' => $baasGateway->is_sandbox
            ]);
            
            // Send request to Cashtime
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-API/1.0',
                    'x-authorization-key' => $baasGateway->secret_key
                ])
                ->post('https://api.cashtime.com.br/v1/request/withdraw', $payload);
                
            // Log the response
            Log::info('Resposta do Cashtime para saque', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'status_code' => $response->status(),
                'response' => $response->json()
            ]);
            
            // Check if successful
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update withdrawal with response data
                $withdrawal->update([
                    'status' => 'processing',
                    'external_id' => $responseData['id'] ?? null,
                    'response_data' => $responseData
                ]);
                
                return true;
            } else {
                // Handle error
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? 'Unknown error';
                
                // Update withdrawal with error
                $withdrawal->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'response_data' => $response->json()
                ]);
                
                throw new \Exception('Cashtime API error: ' . $errorMessage);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar saque Cashtime: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update withdrawal with error
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
        // Remove any formatting
        $pixKey = preg_replace('/[^a-zA-Z0-9@._-]/', '', $pixKey);
        
        // Format based on type
        switch ($pixType) {
            case 'cpf':
                return preg_replace('/[^0-9]/', '', $pixKey);
            case 'cnpj':
                return preg_replace('/[^0-9]/', '', $pixKey);
            case 'phone':
                $phone = preg_replace('/[^0-9]/', '', $pixKey);
                // If it starts with +55, remove it
                if (substr($phone, 0, 2) === '55') {
                    $phone = substr($phone, 2);
                }
                return $phone;
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
            'cpf' => 'cpf',
            'cnpj' => 'cnpj',
            'email' => 'email',
            'phone' => 'phone',
            'random' => 'random',
            default => 'cpf'
        };
    }
    
    /**
     * Clean PIX key based on type
     */
    protected function cleanPixKey(string $pixKey, string $pixType): string
    {
        switch ($pixType) {
            case 'cpf':
                return preg_replace('/[^0-9]/', '', $pixKey);
            case 'phone':
                $phone = preg_replace('/[^0-9]/', '', $pixKey);
                // If it starts with +55, remove it
                if (substr($phone, 0, 2) === '55') {
                    $phone = substr($phone, 2);
                }
                return $phone;
            case 'email':
            case 'random':
            default:
                return $pixKey;
        }
    }
    
    /**
     * Map PIX type to AvivHub value
     */
    protected function mapPixTypeToAvivHubValue(string $pixType): string
    {
        return match($pixType) {
            'cpf' => '5', // CPF
            'email' => '2', // Email
            'phone' => '3', // Phone
            'random' => '4', // Random key
            default => '2' // Default to email
        };
    }
    
    /**
     * Show withdrawal details
     */
    public function show($withdrawalId)
    {
        $user = Auth::user();
        $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        return view('withdrawals.show', compact('withdrawal', 'user'));
    }
    
    /**
     * Webhook for withdrawal status updates
     * 
     * This endpoint is completely public and bypasses CSRF protection
     */
    public function webhook(Request $request)
    {
        try {
            // Log all received data for debugging
            Log::info('Webhook de saque recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            $data = $request->all();
            
            // Validate minimum required data
            if (empty($data['externalRef'])) {
                Log::warning('Webhook de saque inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }
            
            if (!isset($data['status'])) {
                Log::warning('Webhook de saque inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }
            
            // Find withdrawal by our internal ID
            $withdrawalId = $data['externalRef'];
            $withdrawal = Withdrawal::where('withdrawal_id', $withdrawalId)->first();
            
            if (!$withdrawal) {
                Log::warning('Saque não encontrado para webhook', [
                    'withdrawal_id' => $withdrawalId,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Saque não encontrado'], 404);
            }
            
            // CRITICAL: Verify withdrawal belongs to correct user before processing
            Log::info('Processing withdrawal webhook', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'withdrawal_user_id' => $withdrawal->user_id,
                'withdrawal_amount' => $withdrawal->amount
            ]);
            
            // Map status
            $newStatus = $this->mapAvivHubStatus($data['status']);
            $oldStatus = $withdrawal->status;
            
            Log::info('Status de saque mapeado', [
                'original_status' => $data['status'],
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'withdrawal_id' => $withdrawal->withdrawal_id
            ]);
            
            // Update withdrawal
            $updateData = [
                'status' => $newStatus,
                'webhook_data' => $data,
            ];
            
            // Update external_id if provided and different
            if (isset($data['id']) && $data['id'] !== $withdrawal->external_id) {
                $updateData['external_id'] = $data['id'];
            }
            
            // Set completed_at if status changed to completed
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $updateData['completed_at'] = now();
            }
            
            $withdrawal->update($updateData);
            
            // Handle status changes
            if ($newStatus !== $oldStatus) {
                $this->handleStatusChange($withdrawal, $oldStatus, $newStatus);
            }
            
            Log::info('Webhook de saque processado com sucesso', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'external_id' => $withdrawal->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook de saque: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle withdrawal status changes
     */
    protected function handleStatusChange(Withdrawal $withdrawal, string $oldStatus, string $newStatus): void
    {
        $user = $withdrawal->user;
        
        // CRITICAL: Verify we have the correct user
        if (!$user) {
            Log::error('User not found for withdrawal', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'withdrawal_user_id' => $withdrawal->user_id
            ]);
            return;
        }
        
        $wallet = $user->wallet;
        
        if (!$wallet) {
            Log::error('Wallet não encontrada para usuário em atualização de saque', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'withdrawal_user_id' => $withdrawal->user_id
            ]);
            return;
        }
        
        // CRITICAL: Double-check wallet belongs to the correct user
        if ($wallet->user_id !== $user->id) {
            Log::error('CRITICAL: Wallet user_id mismatch detected!', [
                'wallet_user_id' => $wallet->user_id,
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'withdrawal_user_id' => $withdrawal->user_id
            ]);
            return;
        }
        
        // If status changed to failed or cancelled, refund the amount
        if (($newStatus === 'failed' || $newStatus === 'cancelled') && 
            ($oldStatus === 'pending' || $oldStatus === 'processing')) {
            
            // CRITICAL: Check if refund already exists to prevent duplicate refunds
            $existingRefund = \App\Models\WalletTransaction::where('reference_id', $withdrawal->withdrawal_id . '_refund')
                ->where('type', 'credit')
                ->where('category', 'refund')
                ->first();
                
            if ($existingRefund) {
                Log::warning('Refund already exists for withdrawal, skipping duplicate refund', [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'existing_refund_id' => $existingRefund->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
                return;
            }
            
            // Add the amount back to the wallet
            $wallet->addCredit(
                $withdrawal->amount,
                'refund',
                "Estorno de saque {$newStatus} - {$withdrawal->withdrawal_id}",
                [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'withdrawal_user_id' => $withdrawal->user_id,
                    'wallet_user_id' => $wallet->user_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
                $withdrawal->withdrawal_id . '_refund'
            );
            
            Log::info('Saque estornado para a wallet', [
                'user_id' => $user->id,
                'wallet_user_id' => $wallet->user_id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'withdrawal_user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }
        
        // If status changed to completed, update total_withdrawn
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            // Update total_withdrawn in wallet
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();
            
            Log::info('Saque concluído e total_withdrawn atualizado', [
                'user_id' => $user->id,
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount' => $withdrawal->amount,
                'total_withdrawn' => $wallet->total_withdrawn,
            ]);
        }
    }
    
    /**
     * Process withdrawal via E2 Bank
     * 
     * @return bool
     */
    protected function processE2BankWithdrawal(Withdrawal $withdrawal, BaasCredential $baasGateway)
    {
        try {
            $e2bank = new \App\Services\BaaS\E2BankProvider();
            
            // Clean PIX key based on type
            $pixKey = $this->cleanPixKey($withdrawal->pix_key, $withdrawal->pix_type);
            
            Log::info('Enviando saque automático para E2 Bank', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'amount' => $withdrawal->net_amount,
                'pix_key' => $pixKey,
                'pix_type' => $withdrawal->pix_type
            ]);
            
            // Create PIX transfer via E2 Bank
            $result = $e2bank->createPixTransfer([
                'amount' => $withdrawal->net_amount,
                'pix_key' => $pixKey,
                'description' => "Saque PIX - {$withdrawal->withdrawal_id}",
                'withdrawal_id' => $withdrawal->withdrawal_id,
            ]);
            
            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Erro ao processar saque via E2 Bank');
            }
            
            // Update withdrawal with E2 Bank data
            $withdrawal->update([
                'status' => 'processing',
                'external_id' => $result['transfer_id'],
                'response_data' => $result['raw_response'] ?? []
            ]);
            
            Log::info('Saque E2 Bank processado com sucesso', [
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'external_id' => $result['transfer_id'],
                'status' => $result['status']
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar saque E2 Bank: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Update withdrawal with error
            $withdrawal->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Map AvivHub status to our status
     */
    protected function mapAvivHubStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending', 'waiting_payment', 'pendingprocessing', 'awaitingaprove' => 'pending',
            'process. baas', 'transf. baas', 'processing' => 'processing',
            'paid', 'approved', 'completed', 'success', 'successfull' => 'completed',
            'cancelled', 'canceled' => 'cancelled',
            'refused', 'error', 'failed', 'refound', 'failure' => 'failed',
            default => 'processing',
        };
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
            
            Log::info('Enviando saque para PluggouCash', [
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
            
            Log::info('Resposta do PluggouCash para saque', [
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
                
                Log::info('Saque PluggouCash processado com sucesso', [
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
            Log::error('PluggouCash withdrawal error: ' . $e->getMessage(), [
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
}