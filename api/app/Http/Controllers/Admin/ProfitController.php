<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\GatewayFee;
use App\Models\UserFee;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ProfitController extends Controller
{
    /**
     * Display profit information by company (OTIMIZADO)
     */
    public function index(Request $request)
    {
        $cacheKey = 'admin_profit_index_' . date('Y-m-d-H');
        
        $data = Cache::remember($cacheKey, 1800, function () {
            // OTIMIZADO: Uma query para todas as empresas com dados agregados
            $companies = User::where('role', 'user')
                ->withCount(['transactions as total_transactions' => function($query) {
                    $query->paid();
                }])
                ->withSum(['transactions as total_sales' => function($query) {
                    $query->paid();
                }], 'amount')
                ->withSum(['transactions as total_fees' => function($query) {
                    $query->paid();
                }], 'fee_amount')
                ->orderBy('total_sales', 'desc')
                ->paginate(20);
            
            // OTIMIZADO: Calcular gateway fees e profit usando queries agregadas (não individual)
            $companyIds = $companies->pluck('id')->toArray();
            
            // Se não há empresas, retornar vazio
            if (empty($companyIds)) {
                return [
                    'companies' => $companies,
                    'totals' => [
                        'companies' => 0,
                        'total_sales' => 0,
                        'total_transactions' => 0,
                        'total_fees' => 0,
                        'total_gateway_fees' => 0,
                        'total_profit' => 0,
                    ]
                ];
            }
            
            // Query agregada para obter dados de gateway fees por empresa (MySQL compatível)
            $placeholders = implode(',', array_fill(0, count($companyIds), '?'));
            $gatewayData = DB::select("
                SELECT 
                    t.user_id,
                    t.gateway_id,
                    t.payment_method,
                    gf.percentage_fee,
                    gf.fixed_fee,
                    COUNT(*) as transaction_count,
                    SUM(t.amount) as total_amount
                FROM transactions t
                LEFT JOIN gateway_fees gf ON t.gateway_id = gf.gateway_id 
                    AND t.payment_method = gf.payment_method 
                    AND gf.is_active = 1
                WHERE t.user_id IN ({$placeholders})
                    AND t.status = 'paid'
                GROUP BY t.user_id, t.gateway_id, t.payment_method, gf.percentage_fee, gf.fixed_fee
            ", $companyIds);
            
            $gatewayDataByUser = collect($gatewayData)->groupBy('user_id');
            
            foreach ($companies as $company) {
                $gatewayFees = 0;
                
                if (isset($gatewayDataByUser[$company->id])) {
                    foreach ($gatewayDataByUser[$company->id] as $data) {
                        // Se não há taxa configurada, usar valores padrão
                        $percentageFee = $data->percentage_fee ?? 0.99; // Padrão 0.99%
                        $fixedFee = $data->fixed_fee ?? 0.00;
                        
                        // Calcular taxa: porcentagem do total + taxa fixa por transação
                        $percentageAmount = ($data->total_amount * $percentageFee) / 100;
                        $fixedAmount = $data->transaction_count * $fixedFee;
                        
                        $gatewayFees += ($percentageAmount + $fixedAmount);
                    }
                }
                
                $company->gateway_fees = $gatewayFees;
                $company->profit = ($company->total_fees ?? 0) - $gatewayFees;
            }
            
            // OTIMIZADO: Totais com uma única query agregada
            $totals = DB::selectOne("
                SELECT 
                    COUNT(DISTINCT t.user_id) as companies,
                    SUM(CASE WHEN t.status = 'paid' THEN t.amount ELSE 0 END) as total_sales,
                    COUNT(CASE WHEN t.status = 'paid' THEN 1 END) as total_transactions,
                    SUM(CASE WHEN t.status = 'paid' THEN t.fee_amount ELSE 0 END) as total_fees
                FROM transactions t
                INNER JOIN users u ON t.user_id = u.id
                WHERE u.role = 'user'
            ");
            
            // Calcular gateway fees totais de forma agregada
            $totalGatewayData = DB::select("
                SELECT 
                    t.gateway_id,
                    t.payment_method,
                    gf.percentage_fee,
                    gf.fixed_fee,
                    COUNT(*) as transaction_count,
                    SUM(t.amount) as total_amount
                FROM transactions t
                LEFT JOIN gateway_fees gf ON t.gateway_id = gf.gateway_id 
                    AND t.payment_method = gf.payment_method 
                    AND gf.is_active = 1
                INNER JOIN users u ON t.user_id = u.id
                WHERE t.status = 'paid'
                    AND u.role = 'user'
                GROUP BY t.gateway_id, t.payment_method, gf.percentage_fee, gf.fixed_fee
            ");
            
            $totalGatewayFees = 0;
            foreach ($totalGatewayData as $data) {
                // Se não há taxa configurada, usar valores padrão
                $percentageFee = $data->percentage_fee ?? 0.99; // Padrão 0.99%
                $fixedFee = $data->fixed_fee ?? 0.00;
                
                // Calcular taxa: porcentagem do total + taxa fixa por transação
                $percentageAmount = ($data->total_amount * $percentageFee) / 100;
                $fixedAmount = $data->transaction_count * $fixedFee;
                
                $totalGatewayFees += ($percentageAmount + $fixedAmount);
            }
            
            $totalsArray = [
                'companies' => $totals->companies ?? 0,
                'total_sales' => $totals->total_sales ?? 0,
                'total_transactions' => $totals->total_transactions ?? 0,
                'total_fees' => $totals->total_fees ?? 0,
                'total_gateway_fees' => $totalGatewayFees,
                'total_profit' => ($totals->total_fees ?? 0) - $totalGatewayFees,
            ];
            
            return [
                'companies' => $companies,
                'totals' => $totalsArray
            ];
        });
        
        return view('admin.profit.index', $data);
    }
    
    /**
     * Show detailed profit information for a specific company (OTIMIZADO)
     */
    public function show(User $user)
    {
        // Get company transactions paginados
        $transactions = Transaction::where('user_id', $user->id)
            ->paid()
            ->with('gateway')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Calcular gateway fees apenas para a página atual (não todos)
        foreach ($transactions as $transaction) {
            $transaction->gateway_fee = $this->calculateGatewayFeeOptimized($transaction);
            $transaction->transaction_profit = $transaction->fee_amount - $transaction->gateway_fee;
        }
        
        // OTIMIZADO: Totais com uma única query agregada
        $totals = DB::selectOne("
            SELECT 
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as total_sales,
                COUNT(CASE WHEN status = 'paid' THEN 1 END) as total_transactions,
                SUM(CASE WHEN status = 'paid' THEN fee_amount ELSE 0 END) as total_fees
            FROM transactions
            WHERE user_id = ?
        ", [$user->id]);
        
        // Calcular gateway fees totais de forma agregada
        $gatewayData = DB::select("
            SELECT 
                t.gateway_id,
                t.payment_method,
                gf.percentage_fee,
                gf.fixed_fee,
                COUNT(*) as transaction_count,
                SUM(t.amount) as total_amount
            FROM transactions t
            LEFT JOIN gateway_fees gf ON t.gateway_id = gf.gateway_id 
                AND t.payment_method = gf.payment_method 
                AND gf.is_active = 1
            WHERE t.user_id = ?
                AND t.status = 'paid'
            GROUP BY t.gateway_id, t.payment_method, gf.percentage_fee, gf.fixed_fee
        ", [$user->id]);
        
        $totalGatewayFees = 0;
        foreach ($gatewayData as $data) {
            // Se não há taxa configurada, usar valores padrão
            $percentageFee = $data->percentage_fee ?? 0.99; // Padrão 0.99%
            $fixedFee = $data->fixed_fee ?? 0.00;
            
            // Calcular taxa: porcentagem do total + taxa fixa por transação
            $percentageAmount = ($data->total_amount * $percentageFee) / 100;
            $fixedAmount = $data->transaction_count * $fixedFee;
            
            $totalGatewayFees += ($percentageAmount + $fixedAmount);
        }
        
        $totalsArray = [
            'total_sales' => $totals->total_sales ?? 0,
            'total_transactions' => $totals->total_transactions ?? 0,
            'total_fees' => $totals->total_fees ?? 0,
            'total_gateway_fees' => $totalGatewayFees,
            'total_profit' => ($totals->total_fees ?? 0) - $totalGatewayFees,
        ];
        
        // OTIMIZADO: Breakdown por método de pagamento com queries agregadas
        $methodBreakdown = DB::select("
            SELECT 
                t.payment_method,
                t.gateway_id,
                gf.percentage_fee,
                gf.fixed_fee,
                COUNT(*) as count,
                SUM(t.amount) as total_amount,
                SUM(t.fee_amount) as total_fees
            FROM transactions t
            LEFT JOIN gateway_fees gf ON t.gateway_id = gf.gateway_id 
                AND t.payment_method = gf.payment_method 
                AND gf.is_active = 1
            WHERE t.user_id = ?
                AND t.status = 'paid'
            GROUP BY t.payment_method, t.gateway_id, gf.percentage_fee, gf.fixed_fee
        ", [$user->id]);
        
        $methodBreakdownFormatted = collect($methodBreakdown)->groupBy('payment_method')->map(function ($methods, $paymentMethod) {
            $first = $methods->first();
            $gatewayFees = 0;
            
            foreach ($methods as $method) {
                // Se não há taxa configurada, usar valores padrão
                $percentageFee = $method->percentage_fee ?? 0.99; // Padrão 0.99%
                $fixedFee = $method->fixed_fee ?? 0.00;
                
                // Calcular taxa: porcentagem do total + taxa fixa por transação
                $percentageAmount = ($method->total_amount * $percentageFee) / 100;
                $fixedAmount = $method->count * $fixedFee;
                
                $gatewayFees += ($percentageAmount + $fixedAmount);
            }
            
            return (object)[
                'payment_method' => $paymentMethod,
                'count' => $first->count,
                'total_amount' => $first->total_amount,
                'total_fees' => $first->total_fees,
                'gateway_fees' => $gatewayFees,
                'profit' => $first->total_fees - $gatewayFees,
            ];
        })->values();
        
        return view('admin.profit.show', [
            'user' => $user,
            'transactions' => $transactions,
            'totals' => $totalsArray,
            'methodBreakdown' => $methodBreakdownFormatted
        ]);
    }
    
    /**
     * Calculate gateway fee for a transaction (versão otimizada com cache)
     */
    private function calculateGatewayFeeOptimized($transaction)
    {
        if (!$transaction->gateway) {
            return 0;
        }
        
        // Cache da taxa do gateway
        $cacheKey = "gateway_fee_{$transaction->gateway_id}_{$transaction->payment_method}";
        
        $gatewayFee = Cache::remember($cacheKey, 3600, function () use ($transaction) {
            return GatewayFee::where('gateway_id', $transaction->gateway_id)
                ->where('payment_method', $transaction->payment_method)
                ->where('is_active', true)
                ->first();
        });
            
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
        $percentageFee = ($transaction->amount * $gatewayFee->percentage_fee) / 100;
        $totalFee = $percentageFee + $gatewayFee->fixed_fee;
        
        // Apply minimum amount
        if ($gatewayFee->min_amount && $totalFee < $gatewayFee->min_amount) {
            $totalFee = $gatewayFee->min_amount;
        }
        
        // Apply maximum amount if set
        if ($gatewayFee->max_amount && $totalFee > $gatewayFee->max_amount) {
            $totalFee = $gatewayFee->max_amount;
        }
        
        return $totalFee;
    }
    
    /**
     * Calculate gateway fee for a transaction
     */
    private function calculateGatewayFee($transaction)
    {
        return $this->calculateGatewayFeeOptimized($transaction);
    }
}
