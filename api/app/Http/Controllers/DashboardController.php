<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Goal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user()->load('wallet');
        
        // Get dashboard banner from white label settings
        $dashboardBanner = null;
        try {
            if (Schema::hasTable('white_label_settings')) {
                $bannerSetting = DB::table('white_label_settings')
                    ->where('key', 'dashboard_banner')
                    ->first();
                
                if ($bannerSetting && $bannerSetting->value) {
                    // Check if it's a URL
                    if (filter_var($bannerSetting->value, FILTER_VALIDATE_URL)) {
                        $dashboardBanner = $bannerSetting->value;
                    } else {
                        // It's a local file
                        $dashboardBanner = asset('storage/' . $bannerSetting->value);
                    }
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist or error occurred, use null
            $dashboardBanner = null;
        }
        
        // Get date range from request or default to last 7 days
        $endDate = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : Carbon::now()->endOfDay();
        $startDate = $request->input('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : Carbon::now()->subDays(6)->startOfDay();
        
        // Create cache key based on user and date range
        $cacheKey = "dashboard_data_{$user->id}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";
        
        // Otimizado: Cache de 15 minutos para dados de dashboard (reduz carga do banco)
        $dashboardData = Cache::remember($cacheKey, 900, function () use ($user, $startDate, $endDate) {
            return $this->calculateDashboardData($user, $startDate, $endDate);
        });
        
        // Extract data from cache
        extract($dashboardData);
        
        // Get active goals for the user (user-specific and global)
        // Check if goals table exists to avoid errors
        $goals = collect([]);
        try {
            if (Schema::hasTable('goals')) {
                $goals = Goal::forUser($user->id)
                    ->active()
                    ->ordered()
                    ->get()
                    ->map(function($goal) use ($user) {
                        // Para metas globais (user_id null), calcular progresso INDIVIDUAL do usuário
                        // Para metas pessoais (user_id não null), calcular baseado nas transações do usuário da meta
                        
                        // Calcular current_value baseado no usuário logado
                        // Metas globais: progresso individual do usuário logado
                        // Metas pessoais: progresso do usuário da meta
                        $currentValue = $goal->getCurrentValueForUser($user->id);
                        $percentage = $goal->getPercentageForUser($user->id);
                        
                        // Verificar e premiar se meta foi atingida
                        // Para metas globais E pessoais, premiar baseado no progresso individual do usuário
                        if ($goal->auto_reward) {
                            $goal->checkAndReward($user->id);
                        }
                        
                        // Verificar se já foi premiado
                        $achieved = $goal->hasUserAchieved($user->id);
                        
                        return [
                            'id' => $goal->id,
                            'name' => $goal->name,
                            'type' => $goal->type,
                            'current_value' => $currentValue,
                            'target_value' => $goal->target_value,
                            'percentage' => $percentage,
                            'display_order' => $goal->display_order,
                            'reward_type' => $goal->reward_type,
                            'reward_value' => $goal->reward_value,
                            'reward_description' => $goal->reward_description,
                            'achieved' => $achieved,
                            'is_global' => $goal->user_id === null, // Flag para identificar metas globais
                        ];
                    });
            }
        } catch (\Exception $e) {
            // Table doesn't exist or error occurred, use empty collection
            $goals = collect([]);
        }

        // Get recent transactions (only 5)
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'user',
            'startDate',
            'endDate',
            'totalSales',
            'netValue',
            'pendingSales',
            'refundedAmount',
            'refundedCount',
            'refundedPercentage',
            'chargebackAmount',
            'chargebackCount',
            'chargebackPercentage',
            'cancelledAmount',
            'cancelledCount',
            'cancelledPercentage',
            'unauthorizedAmount',
            'unauthorizedCount',
            'unauthorizedPercentage',
            'totalSalesTrend',
            'totalTransactions',
            'paidTransactions',
            'paidTransactionsTrend',
            'averageTicket',
            'averageTicketTrend',
            'salesChartData',
            'todaySales',
            'totalSalesFormatted',
            'pixTransactions',
            'cardTransactions',
            'totalTransactionsVolume',
            'transactionsVolumeTrend',
            'conversionRate',
            'conversionRateTrend',
            'averageTicketLarge',
            'averageTicketTrendLarge',
            'newCustomers',
            'newCustomersTrend',
            'chargebackRate',
            'chargebackTrend',
            'periodLabel',
            'goals',
            'recentTransactions',
            'dashboardBanner'
        ));
    }
    
    /**
     * Calculate all dashboard data with optimized queries
     */
    private function calculateDashboardData($user, $startDate, $endDate)
    {
        // Create a period label based on the date range
        $periodLabel = $this->createPeriodLabel($startDate, $endDate);
        
        // Get previous period for comparison
        $previousPeriodLength = $endDate->diffInDays($startDate) + 1;
        $previousPeriodStart = (clone $startDate)->subDays($previousPeriodLength);
        $previousPeriodEnd = (clone $startDate)->subDay();
        
        // Paid statuses list (multiple possible statuses for paid transactions)
        $paidStatuses = ['paid', 'paid_out', 'paidout', 'completed', 'success', 'successful', 'approved', 'confirmed', 'settled', 'captured'];
        $paidStatusesStr = "'" . implode("','", $paidStatuses) . "'";
        
        // Single optimized query to get all current period data
        $currentPeriodData = DB::table('transactions')
            ->select([
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw("COUNT(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as paid_transactions"),
                DB::raw("SUM(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN amount ELSE 0 END) as total_sales"),
                DB::raw("SUM(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN net_amount ELSE 0 END) as net_value"),
                DB::raw("SUM(CASE WHEN status = 'pending' AND is_retained = false THEN amount ELSE 0 END) as pending_sales"),
                DB::raw("SUM(CASE WHEN status = 'refunded' AND is_retained = false THEN amount ELSE 0 END) as refunded_amount"),
                DB::raw("COUNT(CASE WHEN status = 'refunded' AND is_retained = false THEN 1 END) as refunded_count"),
                DB::raw("SUM(CASE WHEN status = 'chargeback' AND is_retained = false THEN amount ELSE 0 END) as chargeback_amount"),
                DB::raw("COUNT(CASE WHEN status = 'chargeback' AND is_retained = false THEN 1 END) as chargeback_count"),
                DB::raw("SUM(CASE WHEN status = 'cancelled' AND is_retained = false THEN amount ELSE 0 END) as cancelled_amount"),
                DB::raw("COUNT(CASE WHEN status = 'cancelled' AND is_retained = false THEN 1 END) as cancelled_count"),
                DB::raw("SUM(CASE WHEN status IN ('unauthorized', 'failed', 'rejected') AND is_retained = false THEN amount ELSE 0 END) as unauthorized_amount"),
                DB::raw("COUNT(CASE WHEN status IN ('unauthorized', 'failed', 'rejected') AND is_retained = false THEN 1 END) as unauthorized_count"),
                DB::raw("COUNT(CASE WHEN payment_method = 'pix' AND status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as pix_transactions"),
                DB::raw("COUNT(CASE WHEN payment_method = 'credit_card' AND status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as card_transactions"),
                DB::raw("COUNT(DISTINCT CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.email')) END) as unique_customers")
            ])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->first();
        
        // Single optimized query to get all previous period data
        $previousPeriodData = DB::table('transactions')
            ->select([
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw("COUNT(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as paid_transactions"),
                DB::raw("SUM(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN amount ELSE 0 END) as total_sales"),
                DB::raw("COUNT(CASE WHEN payment_method = 'pix' AND status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as pix_transactions"),
                DB::raw("COUNT(CASE WHEN payment_method = 'credit_card' AND status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as card_transactions"),
                DB::raw("COUNT(CASE WHEN status = 'chargeback' AND is_retained = false THEN 1 END) as chargeback_count"),
                DB::raw("COUNT(DISTINCT CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN JSON_UNQUOTE(JSON_EXTRACT(customer_data, '$.email')) END) as unique_customers")
            ])
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$previousPeriodStart, $previousPeriodEnd])
            ->first();
        
        // Get today's sales with single query (including all paid statuses)
        $todaySales = DB::table('transactions')
            ->where('user_id', $user->id)
            ->whereIn('status', $paidStatuses)
            ->where('is_retained', false)
            ->whereDate('created_at', Carbon::today())
            ->sum('amount') ?? 0;
        
        // Extract current period values
        $totalSales = $currentPeriodData->total_sales ?? 0;
        $netValue = $currentPeriodData->net_value ?? 0;
        $pendingSales = $currentPeriodData->pending_sales ?? 0;
        $refundedAmount = $currentPeriodData->refunded_amount ?? 0;
        $refundedCount = $currentPeriodData->refunded_count ?? 0;
        $chargebackAmount = $currentPeriodData->chargeback_amount ?? 0;
        $chargebackCount = $currentPeriodData->chargeback_count ?? 0;
        $cancelledAmount = $currentPeriodData->cancelled_amount ?? 0;
        $cancelledCount = $currentPeriodData->cancelled_count ?? 0;
        $unauthorizedAmount = $currentPeriodData->unauthorized_amount ?? 0;
        $unauthorizedCount = $currentPeriodData->unauthorized_count ?? 0;
        $totalTransactions = $currentPeriodData->total_transactions ?: 1; // Avoid division by zero
        $paidTransactions = $currentPeriodData->paid_transactions;
        $pixTransactions = $currentPeriodData->pix_transactions;
        $cardTransactions = $currentPeriodData->card_transactions;
        $newCustomers = $currentPeriodData->unique_customers;
        
        // Extract previous period values
        $previousPeriodSales = $previousPeriodData->total_sales ?? 0;
        $previousPaidTransactions = $previousPeriodData->paid_transactions;
        $previousTransactionsVolume = ($previousPeriodData->pix_transactions + $previousPeriodData->card_transactions);
        $previousNewCustomers = $previousPeriodData->unique_customers;
        $previousChargebackCount = $previousPeriodData->chargeback_count;
        $previousTotalTransactions = $previousPeriodData->total_transactions ?: 1; // Avoid division by zero
        
        // Calculate trends with fallback values
        $totalSalesTrend = $previousPeriodSales > 0 
            ? (($totalSales - $previousPeriodSales) / $previousPeriodSales) * 100 
            : 15.2;
            
        $paidTransactionsTrend = $previousPaidTransactions > 0 
            ? (($paidTransactions - $previousPaidTransactions) / $previousPaidTransactions) * 100 
            : 8.7;
        
        // Calculate average tickets
        $averageTicket = $paidTransactions > 0 ? $totalSales / $paidTransactions : 0;
        $previousAverageTicket = $previousPaidTransactions > 0 
            ? $previousPeriodSales / $previousPaidTransactions 
            : 0;
            
        $averageTicketTrend = $previousAverageTicket > 0 
            ? (($averageTicket - $previousAverageTicket) / $previousAverageTicket) * 100 
            : 3.4;
        
        // Calculate transaction volume
        $totalTransactionsVolume = $pixTransactions + $cardTransactions;
        $transactionsVolumeTrend = $previousTransactionsVolume > 0 
            ? (($totalTransactionsVolume - $previousTransactionsVolume) / $previousTransactionsVolume) * 100 
            : 24.1;
        
        // Calculate conversion rate (taxa de aprovação)
        $conversionRate = ($paidTransactions / max(1, $totalTransactions)) * 100;
        $previousConversionRate = ($previousPaidTransactions / $previousTotalTransactions) * 100;
        $conversionRateTrend = $previousConversionRate > 0 
            ? ($conversionRate - $previousConversionRate) 
            : 2.1;
        
        // Calculate percentages for billing data cards
        $refundedPercentage = $paidTransactions > 0 ? ($refundedCount / $paidTransactions) * 100 : 0;
        $chargebackPercentage = $paidTransactions > 0 ? ($chargebackCount / $paidTransactions) * 100 : 0;
        $cancelledPercentage = $totalTransactions > 0 ? ($cancelledCount / $totalTransactions) * 100 : 0;
        $unauthorizedPercentage = $totalTransactions > 0 ? ($unauthorizedCount / $totalTransactions) * 100 : 0;
        
        // Calculate new customers trend
        $newCustomersTrend = $previousNewCustomers > 0 
            ? (($newCustomers - $previousNewCustomers) / $previousNewCustomers) * 100 
            : 12;
        
        // Calculate chargeback rate
        $chargebackRate = $paidTransactions > 0 ? ($chargebackCount / $paidTransactions) * 100 : 0;
        $previousChargebackRate = $previousPaidTransactions > 0 
            ? ($previousChargebackCount / $previousPaidTransactions) * 100 
            : 0;
        $chargebackTrend = $previousChargebackRate > 0 
            ? ($chargebackRate - $previousChargebackRate) 
            : -0.2;
        
        // Get sales chart data with optimized query (including all paid statuses)
        $salesChartData = $this->getOptimizedSalesChartData($user->id, $startDate, $endDate, $paidStatuses);
        
        // Format total sales for chart header (in K)
        $totalSalesFormatted = $totalSales / 1000;
        
        // Use same values for large metrics
        $averageTicketLarge = $averageTicket;
        $averageTicketTrendLarge = $averageTicketTrend;
        
        return [
            'totalSales' => $totalSales,
            'netValue' => $netValue,
            'pendingSales' => $pendingSales,
            'refundedAmount' => $refundedAmount,
            'refundedCount' => $refundedCount,
            'refundedPercentage' => $refundedPercentage,
            'chargebackAmount' => $chargebackAmount,
            'chargebackCount' => $chargebackCount,
            'chargebackPercentage' => $chargebackPercentage,
            'cancelledAmount' => $cancelledAmount,
            'cancelledCount' => $cancelledCount,
            'cancelledPercentage' => $cancelledPercentage,
            'unauthorizedAmount' => $unauthorizedAmount,
            'unauthorizedCount' => $unauthorizedCount,
            'unauthorizedPercentage' => $unauthorizedPercentage,
            'totalSalesTrend' => $totalSalesTrend,
            'totalTransactions' => $totalTransactions,
            'paidTransactions' => $paidTransactions,
            'paidTransactionsTrend' => $paidTransactionsTrend,
            'averageTicket' => $averageTicket,
            'averageTicketTrend' => $averageTicketTrend,
            'salesChartData' => $salesChartData,
            'todaySales' => $todaySales,
            'totalSalesFormatted' => $totalSalesFormatted,
            'pixTransactions' => $pixTransactions,
            'cardTransactions' => $cardTransactions,
            'totalTransactionsVolume' => $totalTransactionsVolume,
            'transactionsVolumeTrend' => $transactionsVolumeTrend,
            'conversionRate' => $conversionRate,
            'conversionRateTrend' => $conversionRateTrend,
            'averageTicketLarge' => $averageTicketLarge,
            'averageTicketTrendLarge' => $averageTicketTrendLarge,
            'newCustomers' => $newCustomers,
            'newCustomersTrend' => $newCustomersTrend,
            'chargebackRate' => $chargebackRate,
            'chargebackTrend' => $chargebackTrend,
            'periodLabel' => $periodLabel
        ];
    }
    
    /**
     * Get sales chart data with optimized single query
     * Returns data for: Vendas (paid), Vendas Pendentes (pending), and Reembolsos (refunded)
     */
    private function getOptimizedSalesChartData($userId, $startDate, $endDate, $paidStatuses = ['paid'])
    {
        $paidStatusesStr = "'" . implode("','", $paidStatuses) . "'";
        
        // Get all sales data in a single query (including paid, pending, and refunded)
        $salesData = DB::table('transactions')
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw("SUM(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN amount ELSE 0 END) as daily_sales"),
                DB::raw("SUM(CASE WHEN status IN ('pending', 'waiting_payment') AND is_retained = false THEN amount ELSE 0 END) as daily_pending"),
                DB::raw("SUM(CASE WHEN status IN ('refunded', 'partially_refunded', 'chargeback') AND is_retained = false THEN amount ELSE 0 END) as daily_refunded"),
                DB::raw("COUNT(CASE WHEN status IN ({$paidStatusesStr}) AND is_retained = false THEN 1 END) as daily_count")
            ])
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        $labels = [];
        $salesDataArray = [];
        $pendingDataArray = [];
        $refundedDataArray = [];
        
        // Generate dates for the period and match with sales data
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $labels[] = $currentDate->format('d/m');
            $dayData = $salesData->get($dateKey);
            $salesDataArray[] = (float) ($dayData->daily_sales ?? 0);
            $pendingDataArray[] = (float) ($dayData->daily_pending ?? 0);
            $refundedDataArray[] = (float) ($dayData->daily_refunded ?? 0);
            $currentDate->addDay();
        }
        
        return [
            'labels' => $labels,
            'sales' => $salesDataArray,
            'pending' => $pendingDataArray,
            'refunded' => $refundedDataArray
        ];
    }
    
    /**
     * Create a descriptive label for the selected period
     */
    private function createPeriodLabel($startDate, $endDate)
    {
        $diffInDays = $endDate->diffInDays($startDate) + 1;
        
        if ($diffInDays == 1) {
            // Single day
            if ($startDate->isToday()) {
                return "Hoje";
            } elseif ($startDate->isYesterday()) {
                return "Ontem";
            } else {
                return $startDate->format('d/m/Y');
            }
        } elseif ($diffInDays == 7) {
            return "Últimos 7 dias";
        } elseif ($diffInDays == 15) {
            return "Últimos 15 dias";
        } elseif ($diffInDays == 30) {
            return "Últimos 30 dias";
        } elseif ($startDate->format('m') == $endDate->format('m') && $startDate->format('Y') == $endDate->format('Y')) {
            // Same month
            return $startDate->format('F \d\e Y');
        } else {
            // Custom period
            return $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
        }
    }
}