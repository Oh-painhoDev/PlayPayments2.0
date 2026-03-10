<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Dispute;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    /**
     * Display revenues page with all transactions (entries and exits)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ensure user has a wallet
        if (!$user->wallet) {
            $user->wallet()->create([
                'balance' => 0,
                'pending_balance' => 0,
                'reserved_balance' => 0,
                'blocked_balance' => 0,
            ]);
            $user->refresh();
        }
        
        // Get date filters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Get all transactions (entries)
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('gateway')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all withdrawals (exits)
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all disputes/refunds (exits)
        $disputes = Dispute::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Combine all movements
        $movements = collect();
        
        // Add transactions as entries
        foreach ($transactions as $transaction) {
            $movements->push([
                'type' => 'entry',
                'type_label' => 'Entrada',
                'date' => $transaction->created_at,
                'description' => 'Transação #' . $transaction->transaction_id,
                'payment_method' => $transaction->payment_method,
                'amount' => $transaction->amount,
                'fee' => $transaction->fee_amount,
                'net_amount' => $transaction->net_amount,
                'status' => $transaction->status,
                'reference' => $transaction->transaction_id,
                'data' => $transaction,
            ]);
        }
        
        // Add withdrawals as exits
        foreach ($withdrawals as $withdrawal) {
            $movements->push([
                'type' => 'exit',
                'type_label' => 'Saída',
                'date' => $withdrawal->created_at,
                'description' => 'Saque #' . $withdrawal->withdrawal_id,
                'payment_method' => 'PIX',
                'amount' => $withdrawal->amount,
                'fee' => $withdrawal->fee,
                'net_amount' => -$withdrawal->net_amount, // Negative for exit
                'status' => $withdrawal->status,
                'reference' => $withdrawal->withdrawal_id,
                'data' => $withdrawal,
            ]);
        }
        
        // Add disputes/refunds as exits
        foreach ($disputes as $dispute) {
            $movements->push([
                'type' => 'exit',
                'type_label' => 'Reembolso',
                'date' => $dispute->created_at,
                'description' => 'Reembolso #' . $dispute->id,
                'payment_method' => $dispute->transaction->payment_method ?? 'N/A',
                'amount' => $dispute->amount ?? 0,
                'fee' => 0,
                'net_amount' => -($dispute->amount ?? 0), // Negative for exit
                'status' => $dispute->status,
                'reference' => 'DISPUTE-' . $dispute->id,
                'data' => $dispute,
            ]);
        }
        
        // Sort by date (most recent first)
        $movements = $movements->sortByDesc('date')->values();
        
        // Calculate totals for the period
        $totalEntries = $movements->where('type', 'entry')->sum('net_amount');
        $totalExits = abs($movements->where('type', 'exit')->sum('net_amount'));
        
        // Get available balance (what user can withdraw) - from wallet
        $availableBalance = $user->wallet_balance ?? 0.00;
        
        // Get pending balance (transactions with status 'pending' or 'waiting_payment')
        $pendingBalance = Transaction::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'waiting_payment'])
            ->sum('net_amount');
        
        // Paginate manually
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $total = $movements->count();
        $items = $movements->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
        
        return view('revenues.index', compact('movements', 'paginator', 'totalEntries', 'totalExits', 'availableBalance', 'pendingBalance', 'startDate', 'endDate', 'user'));
    }
    
    /**
     * Export revenues to CSV
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        
        // Get date filters
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        
        // Get all transactions (entries)
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('gateway')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all withdrawals (exits)
        $withdrawals = Withdrawal::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get all disputes/refunds (exits)
        $disputes = Dispute::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Combine all movements
        $movements = collect();
        
        // Add transactions as entries
        foreach ($transactions as $transaction) {
            $movements->push([
                'type' => 'Entrada',
                'data' => $transaction->created_at->format('d/m/Y H:i:s'),
                'descricao' => 'Transação #' . $transaction->transaction_id,
                'metodo_pagamento' => strtoupper($transaction->payment_method),
                'valor_bruto' => number_format($transaction->amount, 2, ',', '.'),
                'taxa' => number_format($transaction->fee_amount, 2, ',', '.'),
                'valor_liquido' => number_format($transaction->net_amount, 2, ',', '.'),
                'status' => $transaction->status,
                'referencia' => $transaction->transaction_id,
            ]);
        }
        
        // Add withdrawals as exits
        foreach ($withdrawals as $withdrawal) {
            $movements->push([
                'type' => 'Saída',
                'data' => $withdrawal->created_at->format('d/m/Y H:i:s'),
                'descricao' => 'Saque #' . $withdrawal->withdrawal_id,
                'metodo_pagamento' => 'PIX',
                'valor_bruto' => number_format($withdrawal->amount, 2, ',', '.'),
                'taxa' => number_format($withdrawal->fee, 2, ',', '.'),
                'valor_liquido' => '-' . number_format($withdrawal->net_amount, 2, ',', '.'),
                'status' => $withdrawal->status,
                'referencia' => $withdrawal->withdrawal_id,
            ]);
        }
        
        // Add disputes/refunds as exits
        foreach ($disputes as $dispute) {
            $movements->push([
                'type' => 'Reembolso',
                'data' => $dispute->created_at->format('d/m/Y H:i:s'),
                'descricao' => 'Reembolso #' . $dispute->id,
                'metodo_pagamento' => $dispute->transaction->payment_method ?? 'N/A',
                'valor_bruto' => number_format($dispute->amount ?? 0, 2, ',', '.'),
                'taxa' => '0,00',
                'valor_liquido' => '-' . number_format($dispute->amount ?? 0, 2, ',', '.'),
                'status' => $dispute->status,
                'referencia' => 'DISPUTE-' . $dispute->id,
            ]);
        }
        
        // Sort by date (most recent first)
        $movements = $movements->sortByDesc(function($item) {
            return \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $item['data'])->timestamp;
        })->values();
        
        // Generate CSV
        $filename = 'extrato_' . $user->id . '_' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($movements) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add headers
            fputcsv($file, ['Tipo', 'Data', 'Descrição', 'Método de Pagamento', 'Valor Bruto (R$)', 'Taxa (R$)', 'Valor Líquido (R$)', 'Status', 'Referência'], ';');
            
            // Add data
            foreach ($movements as $movement) {
                fputcsv($file, [
                    $movement['type'],
                    $movement['data'],
                    $movement['descricao'],
                    $movement['metodo_pagamento'],
                    $movement['valor_bruto'],
                    $movement['taxa'],
                    $movement['valor_liquido'],
                    $movement['status'],
                    $movement['referencia'],
                ], ';');
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
