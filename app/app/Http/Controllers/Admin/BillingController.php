<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display company billing information
     */
    public function index()
    {
        // Get all companies (users with role 'user')
        $companies = User::where('role', 'user')
            ->withCount(['transactions as total_transactions' => function($query) {
                $query->paid();
            }])
            ->withSum(['transactions as total_sales' => function($query) {
                $query->paid();
            }], 'amount')
            ->withCount(['withdrawals as total_withdrawals' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withSum(['withdrawals as total_withdrawn' => function($query) {
                $query->where('status', 'completed');
            }], 'amount')
            ->orderBy('total_sales', 'desc')
            ->paginate(20);
        
        // Calculate totals for all companies
        $totals = [
            'companies' => User::where('role', 'user')->count(),
            'total_sales' => Transaction::paid()->sum('amount'),
            'total_transactions' => Transaction::paid()->count(),
            'total_withdrawals' => Withdrawal::where('status', 'completed')->count(),
            'total_withdrawn' => Withdrawal::where('status', 'completed')->sum('amount'),
        ];
        
        return view('admin.billing.index', compact('companies', 'totals'));
    }
    
    /**
     * Show detailed billing information for a specific company
     */
    public function show(User $user)
    {
        // Get company transactions
        $transactions = Transaction::where('user_id', $user->id)
            ->paid()
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get company withdrawals
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Calculate totals
        $totals = [
            'total_sales' => Transaction::where('user_id', $user->id)
                ->paid()
                ->sum('amount'),
            'total_transactions' => Transaction::where('user_id', $user->id)
                ->paid()
                ->count(),
            'total_withdrawals' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'total_withdrawn' => Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'net_balance' => Transaction::where('user_id', $user->id)
                ->paid()
                ->sum('amount') - 
                Withdrawal::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount')
        ];
        
        return view('admin.billing.show', compact('user', 'transactions', 'withdrawals', 'totals'));
    }
}