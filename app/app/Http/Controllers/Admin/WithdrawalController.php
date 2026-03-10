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

            $user = $withdrawal->user;
            $wallet = $user->wallet()->lockForUpdate()->first();
            
            if (!$wallet) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Carteira do usuário não encontrada']);
            }

            // O valor já foi debitado na criação do saque
            // Apenas atualizar o status para completed
            // Recalcular a taxa para garantir que está correta (pode ter mudado)
            $requestedAmount = $withdrawal->amount;
            $feeCalculation = $user->calculateWithdrawalFee($requestedAmount);
            $fee = $feeCalculation['fee'];
            $totalToDebit = $feeCalculation['total_to_debit'];
            $netAmount = $feeCalculation['net_amount'];
            
            // Verificar se há diferença no valor debitado
            $debitTransaction = \App\Models\WalletTransaction::where('reference_id', $withdrawal->withdrawal_id)
                ->where('type', 'debit')
                ->where('category', 'withdrawal')
                ->first();
            
            // Se o valor calculado for diferente do que foi debitado, ajustar
            if ($debitTransaction && abs($debitTransaction->amount - $totalToDebit) > 0.01) {
                // Estornar o valor antigo
                $wallet->addCredit(
                    $debitTransaction->amount,
                    'refund',
                    "Ajuste de saque - estorno valor antigo - {$withdrawal->withdrawal_id}",
                    [
                        'withdrawal_id' => $withdrawal->withdrawal_id,
                        'reason' => 'Ajuste de valor na aprovação',
                        'original_debit_id' => $debitTransaction->id,
                    ],
                    $withdrawal->withdrawal_id . '_adjustment_refund'
                );
                
                // Debitar o valor correto
                $debitResult = $wallet->addDebit(
                    $totalToDebit,
                    'withdrawal',
                    "Saque via PIX aprovado (ajustado) - {$withdrawal->withdrawal_id} (Recebe: R$ {$requestedAmount} + Taxa: R$ {$fee})",
                    [
                        'withdrawal_id' => $withdrawal->withdrawal_id,
                        'requested_amount' => $requestedAmount,
                        'fee' => $fee,
                        'total_debited' => $totalToDebit,
                        'pix_type' => $withdrawal->pix_type,
                        'pix_key' => $withdrawal->pix_key,
                        'approved_by' => auth()->id(),
                        'adjusted' => true,
                    ],
                    $withdrawal->withdrawal_id . '_approved'
                );
                
                if (!$debitResult) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'Não foi possível ajustar o débito da carteira.']);
                }
            }
            
            // Atualizar o saque com os valores finais
            $withdrawal->update([
                'amount' => $requestedAmount, // Valor que vai cair na conta
                'fee' => $fee,
                'net_amount' => $netAmount,
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            // IMPORTANTE: Creditar a taxa na wallet do admin
            // O admin ganha a taxa quando aprova o saque
            $admin = auth()->user();
            $adminWallet = $admin->wallet;
            
            // Se não tiver wallet, criar
            if (!$adminWallet) {
                $adminWallet = $admin->wallet()->create([
                    'balance' => 0.00,
                    'currency' => 'BRL',
                    'is_active' => true,
                ]);
            }
            
            // Creditar a taxa na wallet do admin
            $adminWallet->addCredit(
                $fee,
                'withdrawal_fee',
                "Taxa de saque aprovado - {$withdrawal->withdrawal_id}",
                [
                    'withdrawal_id' => $withdrawal->withdrawal_id,
                    'user_id' => $withdrawal->user_id,
                    'fee' => $fee,
                    'approved_by' => auth()->id(),
                ],
                $withdrawal->withdrawal_id . '_admin_fee'
            );

            // Commit a transação
            DB::commit();
            
            // Log the action
            Log::info('Saque manual aprovado pelo admin', [
                'admin_id' => auth()->id(),
                'withdrawal_id' => $withdrawal->withdrawal_id,
                'user_id' => $withdrawal->user_id,
                'amount' => $requestedAmount,
                'fee' => $fee,
                'total_debited' => $totalToDebit,
                'net_amount' => $netAmount
            ]);

            return redirect()->route('admin.withdrawals.show', $withdrawal)
                ->with('success', 'Saque aprovado com sucesso! O usuário receberá R$ ' . number_format($requestedAmount, 2, ',', '.') . ' (Taxa: R$ ' . number_format($fee, 2, ',', '.') . ')');

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

            // IMPORTANTE: Sempre estornar o valor quando o saque é rejeitado
            // O valor foi debitado na criação do saque para prevenir burlas
            $user = $withdrawal->user;
            $wallet = $user->wallet;
            
            if (!$wallet) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Carteira do usuário não encontrada']);
            }
            
            // Buscar a transação de débito do saque
            $debitTransaction = \App\Models\WalletTransaction::where('reference_id', $withdrawal->withdrawal_id)
                ->where('type', 'debit')
                ->where('category', 'withdrawal')
                ->first();
            
            // Se encontrou a transação de débito, estornar o valor
            if ($debitTransaction) {
                $wallet->addCredit(
                    $debitTransaction->amount,
                    'refund',
                    "Estorno de saque rejeitado - {$withdrawal->withdrawal_id}",
                    [
                        'withdrawal_id' => $withdrawal->withdrawal_id,
                        'reason' => $request->rejection_reason,
                        'original_debit_id' => $debitTransaction->id,
                    ],
                    $withdrawal->withdrawal_id . '_refund'
                );
            } else {
                // Se não encontrou a transação, calcular o valor a estornar baseado no saque
                // Isso pode acontecer se o saque foi criado antes da correção
                $feeCalculation = $user->calculateWithdrawalFee($withdrawal->amount);
                $totalToRefund = $feeCalculation['total_to_debit'];
                
                $wallet->addCredit(
                    $totalToRefund,
                    'refund',
                    "Estorno de saque rejeitado - {$withdrawal->withdrawal_id}",
                    [
                        'withdrawal_id' => $withdrawal->withdrawal_id,
                        'reason' => $request->rejection_reason,
                        'calculated_refund' => true,
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
                'reason' => $request->rejection_reason,
                'was_debited' => $debitTransaction !== null
            ]);

            $message = 'Saque rejeitado e valor estornado para o usuário.';
            
            return redirect()->route('admin.withdrawals.show', $withdrawal)
                ->with('success', $message);

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