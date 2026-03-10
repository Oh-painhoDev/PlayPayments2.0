<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\User;
use App\Models\Transaction;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispute::with(['user', 'transaction']);

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('dispute_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('document', 'like', "%{$search}%");
                  });
            });
        }

        $query->orderBy('created_at', 'desc');

        $disputes = $query->paginate(20);

        $stats = [
            'total' => Dispute::count(),
            'pending' => Dispute::where('status', 'pending')->count(),
            'responded' => Dispute::where('status', 'responded')->count(),
            'defended' => Dispute::where('status', 'defended')->count(),
            'rejected' => Dispute::where('status', 'defense_rejected')->count(),
            'refunded' => Dispute::where('status', 'refunded')->count(),
        ];

        return view('admin.disputes.index', compact('disputes', 'stats'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load(['user', 'transaction']);
        return view('admin.disputes.show', compact('dispute'));
    }

    public function acceptDefense(Request $request, Dispute $dispute)
    {
        try {
            DB::beginTransaction();

            if ($dispute->status !== 'responded') {
                DB::rollBack();
                return back()->withErrors(['error' => 'Esta infração não pode ter a defesa aceita']);
            }

            $wallet = $dispute->user->wallet()->lockForUpdate()->first();

            if ($dispute->risk_level === 'MED') {
                $wallet->unblockAmount($dispute->amount);
            }

            $dispute->update([
                'status' => 'defended',
                'defended_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            DB::commit();
            
            // Dispatch webhook for defense accepted (valor desbloqueado)
            $webhookService = new \App\Services\WebhookService();
            $webhookService->dispatchTransactionEvent($dispute->transaction, 'transaction.defense_accepted', [
                'dispute_id' => $dispute->id,
                'unblocked_amount' => $dispute->amount,
                'accepted_at' => now()->toIso8601String(),
            ]);

            Log::info('Dispute defense accepted', [
                'dispute_id' => $dispute->dispute_id,
                'user_id' => $dispute->user_id,
                'admin_id' => auth()->id()
            ]);

            return back()->with('success', 'Defesa aceita com sucesso! O bloqueio cautelar foi removido.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting dispute defense', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => 'Erro ao aceitar defesa: ' . $e->getMessage()]);
        }
    }

    public function rejectDefense(Request $request, Dispute $dispute)
    {
        try {
            DB::beginTransaction();

            if ($dispute->status !== 'responded') {
                DB::rollBack();
                return back()->withErrors(['error' => 'Esta infração não pode ter a defesa rejeitada']);
            }
            
            $wallet = $dispute->user->wallet()->lockForUpdate()->first();
            $transaction = $dispute->transaction;

            // Update dispute status
            $dispute->update([
                'status' => 'defense_rejected',
                'admin_notes' => $request->admin_notes,
            ]);
            
            // Process automatic refund
            // Deduct blocked amount from wallet
            if ($wallet->blocked_balance >= $dispute->amount) {
                $wallet->blocked_balance -= $dispute->amount;
                $wallet->balance -= $dispute->amount;
                $wallet->save();
            }
            
            // Update transaction status to refunded
            $transaction->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);

            DB::commit();
            
            // Dispatch webhook for refund (after defense rejection)
            $webhookService = new \App\Services\WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded', [
                'refund_reason' => 'Defense rejected - Dispute ID: ' . $dispute->id,
                'refunded_amount' => $dispute->amount,
                'refunded_by' => 'admin_after_defense_rejection',
                'dispute_id' => $dispute->id,
            ]);

            Log::info('Dispute defense rejected - automatic refund processed', [
                'dispute_id' => $dispute->dispute_id,
                'user_id' => $dispute->user_id,
                'admin_id' => auth()->id(),
                'refunded_amount' => $dispute->amount
            ]);

            return back()->with('success', 'Defesa rejeitada. Reembolso processado automaticamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting dispute defense', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => 'Erro ao rejeitar defesa: ' . $e->getMessage()]);
        }
    }

    public function create(Request $request)
    {
        $users = User::where('role', 'user')->get();
        return view('admin.disputes.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric|min:0.01',
            'dispute_type' => 'required|in:chargeback,fraud,unauthorized,not_received,defective,other',
            'risk_level' => 'required|in:LOW,MED,HIGH',
        ]);

        try {
            DB::beginTransaction();

            $user = User::find($request->user_id);
            $wallet = $user->wallet()->lockForUpdate()->first();

            $dispute = Dispute::create([
                'user_id' => $request->user_id,
                'transaction_id' => $request->transaction_id,
                'amount' => $request->amount,
                'dispute_type' => $request->dispute_type,
                'risk_level' => $request->risk_level,
                'status' => 'pending',
            ]);

            if ($request->risk_level === 'MED') {
                $wallet->blockAmount($request->amount);
            }

            DB::commit();

            Log::info('Dispute created by admin', [
                'dispute_id' => $dispute->dispute_id,
                'user_id' => $user->id,
                'admin_id' => auth()->id()
            ]);

            return redirect()->route('admin.disputes.index')->with('success', 'Infração criada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating dispute', [
                'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => 'Erro ao criar infração: ' . $e->getMessage()])->withInput();
        }
    }

    public function bulkCreate()
    {
        $users = User::where('role', 'user')->orderBy('name')->get();
        $gateways = PaymentGateway::orderBy('name')->get();
        $templates = \App\Models\DisputeTemplate::active()->orderBy('name')->get();
        
        return view('admin.disputes.bulk-create', compact('users', 'gateways', 'templates'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'gateway_id' => 'nullable|exists:payment_gateways,id',
            'user_id' => 'nullable|exists:users,id',
            'dispute_type' => 'required|in:chargeback,fraud,unauthorized,not_received,defective,other',
            'risk_level' => 'required|in:LOW,MED,HIGH',
            'max_disputes' => 'required|integer|min:1|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $query = Transaction::where('status', 'PAID')
                ->whereBetween('created_at', [
                    $request->date_from . ' 00:00:00',
                    $request->date_to . ' 23:59:59'
                ]);

            if ($request->gateway_id) {
                $query->where('gateway_id', $request->gateway_id);
            }

            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            $query->whereDoesntHave('disputes');

            $transactions = $query->limit($request->max_disputes)->get();

            if ($transactions->isEmpty()) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Nenhuma transação paga encontrada com os filtros selecionados.'])->withInput();
            }

            $createdCount = 0;
            $blockedCount = 0;

            foreach ($transactions as $transaction) {
                $dispute = Dispute::create([
                    'user_id' => $transaction->user_id,
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'dispute_type' => $request->dispute_type,
                    'risk_level' => $request->risk_level,
                    'status' => 'pending',
                ]);

                if ($request->risk_level === 'MED') {
                    $wallet = $transaction->user->wallet()->lockForUpdate()->first();
                    if ($wallet) {
                        $wallet->blockAmount($transaction->amount);
                        $blockedCount++;
                    }
                }

                $createdCount++;
            }

            DB::commit();

            Log::info('Bulk disputes created by admin', [
                'created_count' => $createdCount,
                'blocked_count' => $blockedCount,
                'admin_id' => auth()->id(),
                'filters' => [
                    'date_from' => $request->date_from,
                    'date_to' => $request->date_to,
                    'gateway_id' => $request->gateway_id,
                    'user_id' => $request->user_id,
                    'risk_level' => $request->risk_level,
                ]
            ]);

            return redirect()
                ->route('admin.setup.disputes')
                ->with('success', "✅ Infrações criadas com sucesso!")
                ->with('summary', [
                    'created' => $createdCount,
                    'blocked' => $blockedCount,
                    'transactions' => $transactions->count(),
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bulk disputes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Erro ao criar infrações: ' . $e->getMessage()])->withInput();
        }
    }
}
