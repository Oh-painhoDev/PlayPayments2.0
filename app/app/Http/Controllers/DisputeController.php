<?php

namespace App\Http\Controllers;

use App\Models\Dispute;
use App\Models\Transaction;
use App\Services\CloudinaryService;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class DisputeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $disputes = Dispute::where('user_id', $user->id)
            ->with('transaction')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $wallet = $user->wallet;
        
        $stats = [
            'total_disputes' => Dispute::where('user_id', $user->id)->count(),
            'pending' => Dispute::where('user_id', $user->id)->where('status', 'pending')->count(),
            'defended' => Dispute::where('user_id', $user->id)->where('status', 'defended')->count(),
            'refunded' => Dispute::where('user_id', $user->id)->where('status', 'refunded')->count(),
        ];
        
        return view('refunds.index', compact('disputes', 'wallet', 'stats'));
    }
    
    public function show(Dispute $dispute)
    {
        $user = Auth::user();
        
        if ($dispute->user_id !== $user->id) {
            abort(403, 'Você não tem permissão para visualizar esta infração');
        }
        
        $dispute->load('transaction');
        
        return view('disputes.show', compact('dispute'));
    }
    
    public function refund(Request $request, Dispute $dispute)
    {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            
            if ($dispute->user_id !== $user->id) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Você não tem permissão para reembolsar esta infração']);
            }
            
            if (!$dispute->canRefund()) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Esta infração não pode ser reembolsada']);
            }
            
            $wallet = $user->wallet()->lockForUpdate()->first();
            
            $wallet->addDebit(
                $dispute->amount,
                'refund',
                'Reembolso de infração: ' . $dispute->dispute_id,
                ['dispute_id' => $dispute->dispute_id],
                $dispute->dispute_id
            );
            
            if ($dispute->risk_level === 'MED') {
                $wallet->unblockAmount($dispute->amount);
            }
            
            $dispute->update([
                'status' => 'refunded',
                'refunded_at' => now(),
            ]);
            
            // Atualizar status da transação para indicar reembolso por infração
            if ($dispute->transaction) {
                $dispute->transaction->update([
                    'status' => 'MED[REEMBOLSO]',
                    'refunded_at' => now(),
                ]);
                
                // Disparar webhook para notificar sobre reembolso por infração
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($dispute->transaction, 'transaction.refunded');
            }
            
            DB::commit();
            
            Log::info('Dispute refunded', [
                'dispute_id' => $dispute->dispute_id,
                'user_id' => $user->id,
                'amount' => $dispute->amount
            ]);
            
            return back()->with('success', 'Infração reembolsada com sucesso');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error refunding dispute', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => 'Erro ao reembolsar infração: ' . $e->getMessage()]);
        }
    }
    
    public function defend(Request $request, Dispute $dispute)
    {
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            
            if ($dispute->user_id !== $user->id) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Você não tem permissão para defender esta infração']);
            }
            
            if (!$dispute->canDefend()) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Esta infração não pode mais ser defendida']);
            }
            
            $validator = Validator::make($request->all(), [
                'defense_details' => 'required|string|min:50',
                'defense_file' => 'required|file|mimes:pdf|max:10240',
            ], [
                'defense_details.required' => 'Os detalhes da defesa são obrigatórios',
                'defense_details.min' => 'A defesa deve ter no mínimo 50 caracteres',
                'defense_file.required' => 'O arquivo PDF é obrigatório',
                'defense_file.mimes' => 'O arquivo deve ser um PDF',
                'defense_file.max' => 'O arquivo deve ter no máximo 10MB',
            ]);
            
            if ($validator->fails()) {
                DB::rollBack();
                return back()->withErrors($validator)->withInput();
            }
            
            $filePath = null;
            if ($request->hasFile('defense_file')) {
                $cloudinary = new CloudinaryService();
                $result = $cloudinary->uploadDisputeDefense($request->file('defense_file'), $dispute->dispute_id);
                
                if (!$result['success']) {
                    DB::rollBack();
                    return back()->withErrors(['error' => $result['error']])->withInput();
                }
                
                $filePath = $result['url'];
            }
            
            $dispute->update([
                'defense_details' => $request->defense_details,
                'defense_file' => $filePath,
                'status' => 'responded',
                'responded_at' => now(),
            ]);
            
            DB::commit();
            
            Log::info('Dispute defense submitted', [
                'dispute_id' => $dispute->dispute_id,
                'user_id' => $user->id
            ]);
            
            return back()->with('success', 'Defesa enviada com sucesso! Aguarde a análise do administrador.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting dispute defense', [
                'dispute_id' => $dispute->id,
                'error' => $e->getMessage()
            ]);
            return back()->withErrors(['error' => 'Erro ao enviar defesa: ' . $e->getMessage()]);
        }
    }
}
