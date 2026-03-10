<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Transaction;
use App\Models\DocumentVerification;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class UserController extends Controller
{
    /**
     * Get user details for modal
     */
    public function getUserDetails($userId)
    {
        try {
            $user = User::with(['assignedGateway', 'documentVerification', 'wallet'])->findOrFail($userId);
            
            $html = View::make('admin.users.user-details-partial', compact('user'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes do usuário: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar detalhes do usuário'
            ], 500);
        }
    }
    
    /**
     * Block a user
     */
    public function block(Request $request, User $user)
    {
        try {
            // Validate request
            $request->validate([
                'blocked_reason' => 'required|string|max:1000',
            ]);
            
            // Cannot block admin or manager users
            if ($user->isAdminOrManager()) {
                return back()->withErrors(['error' => 'Não é possível bloquear um administrador ou gerente']);
            }
            
            // Update user
            $user->update([
                'is_blocked' => true,
                'blocked_at' => now(),
                'blocked_reason' => $request->blocked_reason,
            ]);
            
            // Log the action
            Log::info('Usuário bloqueado pelo admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'reason' => $request->blocked_reason
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'Usuário bloqueado com sucesso');
                
        } catch (\Exception $e) {
            Log::error('Erro ao bloquear usuário: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Erro ao bloquear usuário: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Unblock a user
     */
    public function unblock(User $user)
    {
        try {
            // Update user
            $user->update([
                'is_blocked' => false,
                'blocked_at' => null,
                'blocked_reason' => null,
            ]);
            
            // Log the action
            Log::info('Usuário desbloqueado pelo admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', 'Usuário desbloqueado com sucesso');
                
        } catch (\Exception $e) {
            Log::error('Erro ao desbloquear usuário: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Erro ao desbloquear usuário: ' . $e->getMessage()]);
        }
    }
    /**
     * Show edit withdrawal fees form
     */
    public function editWithdrawalFees(User $user)
    {
        return view('admin.users.edit-withdrawal-fees', compact('user'));
    }
    
    /**
     * Update withdrawal fees
     */
    public function updateWithdrawalFees(Request $request, User $user)
    {
        try {
            // Validate request
            $request->validate([
                'withdrawal_fee_type' => 'required|in:global,fixed,percentage,both',
                'withdrawal_fee_fixed' => 'required_if:withdrawal_fee_type,fixed,both|nullable|numeric|min:0',
                'withdrawal_fee_percentage' => 'required_if:withdrawal_fee_type,percentage,both|nullable|numeric|min:0|max:100',
            ]);
            
            // Update fees based on type
            $updateData = [
                'withdrawal_fee_type' => $request->withdrawal_fee_type
            ];
            
            // Set fee values based on type
            switch ($request->withdrawal_fee_type) {
                case 'global':
                    $updateData['withdrawal_fee_fixed'] = null;
                    $updateData['withdrawal_fee_percentage'] = null;
                    break;
                case 'fixed':
                    $updateData['withdrawal_fee_fixed'] = $request->withdrawal_fee_fixed;
                    $updateData['withdrawal_fee_percentage'] = null;
                    break;
                case 'percentage':
                    $updateData['withdrawal_fee_fixed'] = null;
                    $updateData['withdrawal_fee_percentage'] = $request->withdrawal_fee_percentage;
                    break;
                case 'both':
                    $updateData['withdrawal_fee_fixed'] = $request->withdrawal_fee_fixed;
                    $updateData['withdrawal_fee_percentage'] = $request->withdrawal_fee_percentage;
                    break;
            }
            
            $user->update($updateData);
            
            // Clear user cache
            \Cache::forget('user_' . $user->id);
            
            // Log the action
            Log::info('Taxas de saque atualizadas pelo admin', [
                'admin_id' => auth()->id(),
                'user_id' => $user->id,
                'update_data' => $updateData
            ]);
            
            return redirect()->route('admin.users.edit-withdrawal-fees', $user)
                ->with('success', 'Taxas de saque atualizadas com sucesso!');
                
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar taxas de saque: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Erro ao atualizar taxas: ' . $e->getMessage()])->withInput();
        }
    }
}
