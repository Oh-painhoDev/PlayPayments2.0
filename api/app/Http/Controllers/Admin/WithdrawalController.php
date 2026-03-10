<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\UserGatewayCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of withdrawals
     */
    public function index(Request $request)
    {
        $query = Withdrawal::with('user');

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('type')) {
            if ($request->type === 'manual') {
                $query->whereHas('user', function($q) {
                    $q->where('withdrawal_type', 'manual');
                });
            } elseif ($request->type === 'automatic') {
                $query->whereHas('user', function($q) {
                    $q->where('withdrawal_type', 'automatic');
                });
            }
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('withdrawal_id', 'like', "%{$search}%")
                  ->orWhere('pix_key', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%");
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

        $withdrawals = $query->paginate(20);

        // Get counts for dashboard
        $pendingCount = Withdrawal::where('status', 'pending')->count();
        $processingCount = Withdrawal::where('status', 'processing')->count();
        $completedCount = Withdrawal::where('status', 'completed')->count();
        $failedCount = Withdrawal::where('status', 'failed')->count();
        $totalAmount = Withdrawal::where('status', 'completed')->sum('amount');

        return view('admin.withdrawals.index', compact(
            'withdrawals', 
            'pendingCount', 
            'processingCount', 
            'completedCount', 
            'failedCount',
            'totalAmount'
        ));
    }

    /**
     * Show withdrawal details
     */
    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load('user');
        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Approve a withdrawal
     */
    public function approve(Request $request, Withdrawal $withdrawal)
    {
        try {
            // Iniciar transação de banco de dados
            DB::beginTransaction();
            
            // Check if withdrawal is pending
            if ($withdrawal->status !== 'pending') {
                DB::rollBack();
                return back()->withErrors(['error' => 'Apenas saques pendentes podem ser aprovados']);
            }

            // Update withdrawal status to completed
            $withdrawal->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            // Commit a transação
            DB::commit();
            
            // Log the action
            Log::info('Saque manual aprovado pelo admin', [
                'admin_id' => auth()->id(),
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount
            ]);

            return redirect()->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Saque aprovado com sucesso!');

        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();
            
            Log::error('Erro ao aprovar saque: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Erro ao aprovar saque: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a withdrawal
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        try {
            // Iniciar transação de banco de dados
            DB::beginTransaction();
            
            // Validate request
            $request->validate([
                'rejection_reason' => 'required|string|max:255'
            ]);

            // Check if withdrawal is pending
            if ($withdrawal->status !== 'pending') {
                DB::rollBack();
                return back()->withErrors(['error' => 'Apenas saques pendentes podem ser rejeitados']);
            }

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'cancelled',
                'error_message' => $request->rejection_reason
            ]);

            // Refund the amount to user's wallet
            $user = $withdrawal->user;
            $wallet = $user->wallet;

            if ($wallet) {
                $wallet->addCredit(
                    $withdrawal->amount,
                    'refund',
                    "Estorno de saque rejeitado - {$withdrawal->withdrawal_id}",
                    [
                        'withdrawal_id' => $withdrawal->withdrawal_id,
                        'reason' => $request->rejection_reason
                    ],
                    $withdrawal->withdrawal_id . '_refund'
                );
            }

            // Commit a transação
            DB::commit();
            
            // Log the action
            Log::info('Saque rejeitado pelo admin', [
                'admin_id' => auth()->id(),
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'reason' => $request->rejection_reason
            ]);

            return redirect()->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Saque rejeitado e valor estornado para o usuário.');

        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollBack();
            
            Log::error('Erro ao rejeitar saque: ' . $e->getMessage(), [
                'withdrawal_id' => $withdrawal->id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Erro ao rejeitar saque: ' . $e->getMessage()]);
        }
    }
}