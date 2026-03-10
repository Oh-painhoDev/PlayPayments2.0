<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingPeriodController extends Controller
{
    /**
     * Display company billing information by period
     */
    public function index(Request $request)
    {
        // Set default date range if not provided (last month)
        $endDate = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now();
        $startDate = $request->input('date_from') ? Carbon::parse($request->input('date_from')) : Carbon::now()->subMonth();
        
        // Ensure end date is not in the future
        if ($endDate->isAfter(Carbon::now())) {
            $endDate = Carbon::now();
        }
        
        // Get all companies (users with role 'user')
        $companies = User::where('role', 'user')
            ->withCount(['transactions as total_transactions' => function($query) use ($startDate, $endDate) {
                $query->paid()
                      ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }])
            ->withSum(['transactions as total_sales' => function($query) use ($startDate, $endDate) {
                $query->paid()
                      ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }], 'amount')
            ->withCount(['withdrawals as total_withdrawals' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                      ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }])
            ->withSum(['withdrawals as total_withdrawn' => function($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                      ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
            }], 'amount')
            ->orderBy('total_sales', 'desc')
            ->paginate(20);
        
        // Calculate totals for all companies within the date range
        $totals = [
            'companies' => User::where('role', 'user')->count(),
            'total_sales' => Transaction::paid()
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->sum('amount'),
            'total_transactions' => Transaction::paid()
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count(),
            'total_withdrawals' => Withdrawal::where('status', 'completed')
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count(),
            'total_withdrawn' => Withdrawal::where('status', 'completed')
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->sum('amount'),
        ];
        
        return view('admin.billing-period.index', compact('companies', 'totals', 'startDate', 'endDate'));
    }
    
    /**
     * Show detailed billing information for a specific company within a date range
     */
    public function show(Request $request, User $user)
    {
        // Set default date range if not provided (last month)
        $endDate = $request->input('date_to') ? Carbon::parse($request->input('date_to')) : Carbon::now();
        $startDate = $request->input('date_from') ? Carbon::parse($request->input('date_from')) : Carbon::now()->subMonth();
        
        // Ensure end date is not in the future
        if ($endDate->isAfter(Carbon::now())) {
            $endDate = Carbon::now();
        }
        
        // Get company transactions within date range
        $transactions = Transaction::where('user_id', $user->id)
            ->paid()
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get company withdrawals within date range
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Calculate totals within date range
        $totals = [
            'total_sales' => Transaction::where('user_id', $user->id)
                ->paid()
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->sum('amount'),
            'total_transactions' => Transaction::where('user_id', $user->id)
                ->paid()
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count(),
            'total_withdrawals' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->count(),
            'total_withdrawn' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->sum('amount'),
            'net_balance' => Transaction::where('user_id', $user->id)
                ->paid()
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->sum('amount') - 
                Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
                ->sum('amount')
        ];
        
        return view('admin.billing-period.show', compact('user', 'transactions', 'withdrawals', 'totals', 'startDate', 'endDate'));
    }
}