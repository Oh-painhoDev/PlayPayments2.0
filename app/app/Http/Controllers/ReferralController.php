<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Garantir que o usuário tem um código de referência
        if (empty($user->referral_code)) {
            $user->referral_code = User::generateUniqueReferralCode();
            $user->save();
        }
        
        // Buscar todos os usuários referidos
        $referredUsers = User::where('referrer_id', $user->id)
            ->withCount(['transactions' => function($query) {
                $query->where('status', 'paid');
            }])
            ->get();
        
        // Calcular totais
        $totalCommissions = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission_amount');
        
        $totalPaidCommissions = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'paid')
            ->sum('commission_amount');
        
        // Buscar comissões pendentes
        $pendingCommissions = DB::table('referral_commissions')
            ->join('users as referred', 'referral_commissions.referred_id', '=', 'referred.id')
            ->leftJoin('transactions', 'referral_commissions.transaction_id', '=', 'transactions.id')
            ->where('referral_commissions.referrer_id', $user->id)
            ->where('referral_commissions.status', 'pending')
            ->select(
                'referral_commissions.*',
                'referred.name as referred_name',
                'referred.document as referred_document',
                'transactions.transaction_id as transaction_code'
            )
            ->orderBy('referral_commissions.created_at', 'desc')
            ->get();
        
        // Calcular saldo disponível (saldo total - comissões pendentes)
        $wallet = $user->wallet;
        $availableBalance = $wallet ? ($wallet->balance - $totalCommissions) : 0;
        
        return view('referrals.index', [
            'user' => $user,
            'referredUsers' => $referredUsers,
            'totalCommissions' => $totalCommissions,
            'totalPaidCommissions' => $totalPaidCommissions,
            'pendingCommissions' => $pendingCommissions,
            'availableBalance' => $availableBalance,
        ]);
    }
    
    public function updateCommission(Request $request, $userId)
    {
        $user = auth()->user();
        $referredUser = User::where('referrer_id', $user->id)
            ->where('id', $userId)
            ->firstOrFail();
        
        $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'commission_fixed' => 'nullable|numeric|min:0',
        ]);
        
        $referredUser->update([
            'commission_percentage' => $request->commission_percentage,
            'commission_fixed' => $request->commission_fixed ?? 0,
        ]);
        
        return redirect()->back()->with('success', 'Comissão atualizada com sucesso!');
    }
    
    public function requestWithdrawal()
    {
        $user = auth()->user();
        
        $totalCommissions = DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'pending')
            ->sum('commission_amount');
        
        if ($totalCommissions <= 0) {
            return redirect()->back()->with('error', 'Não há comissões pendentes para saque.');
        }
        
        // Marcar todas as comissões pendentes como pagas
        DB::table('referral_commissions')
            ->where('referrer_id', $user->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        
        // Adicionar ao saldo da carteira
        $wallet = $user->wallet;
        if ($wallet) {
            $wallet->increment('balance', $totalCommissions);
        } else {
            // Criar carteira se não existir
            $user->wallet()->create([
                'balance' => $totalCommissions,
            ]);
        }
        
        return redirect()->back()->with('success', 'Saque solicitado com sucesso! R$ ' . number_format($totalCommissions, 2, ',', '.') . ' adicionado ao seu saldo.');
    }
}
