<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeConfiguration;
use App\Helpers\FeeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhiteLabelController extends Controller
{
    /**
     * Show global fees page
     */
    public function globalFees()
    {
        return view('admin.white-label.global-fees');
    }

    /**
     * Get global fees - OPTIMIZED with caching
     */
    public function getGlobalFees()
    {
        try {
            // Use cached helper for performance
            $fees = FeeHelper::getGlobalFees();
            
            return response()->json([
                'success' => true,
                'fees' => $fees
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar taxas globais: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar taxas globais: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update global fees
     */
    public function updateGlobalFees(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'pix.percentage_fee' => 'required|numeric|min:0',
                'pix.fixed_fee' => 'required|numeric|min:0',
                'pix.min_amount' => 'required|numeric|min:0',
                'pix.max_amount' => 'nullable|numeric|min:0',
                'pix.min_transaction_value' => 'nullable|numeric|min:0',
                'pix.max_transaction_value' => 'nullable|numeric|min:0',
                'credit_card.percentage_fee' => 'required|numeric|min:0',
                'credit_card.fixed_fee' => 'required|numeric|min:0',
                'credit_card.min_amount' => 'required|numeric|min:0',
                'credit_card.max_amount' => 'nullable|numeric|min:0',
                'credit_card.min_transaction_value' => 'nullable|numeric|min:0',
                'credit_card.max_transaction_value' => 'nullable|numeric|min:0',
                'bank_slip.percentage_fee' => 'required|numeric|min:0',
                'bank_slip.fixed_fee' => 'required|numeric|min:0',
                'bank_slip.min_amount' => 'required|numeric|min:0',
                'bank_slip.max_amount' => 'nullable|numeric|min:0',
                'bank_slip.min_transaction_value' => 'nullable|numeric|min:0',
                'bank_slip.max_transaction_value' => 'nullable|numeric|min:0',
                'withdrawal.percentage_fee' => 'required|numeric|min:0',
                'withdrawal.fixed_fee' => 'required|numeric|min:0',
                'withdrawal.min_amount' => 'required|numeric|min:0',
                'withdrawal.max_amount' => 'nullable|numeric|min:0',
                'withdrawal.min_transaction_value' => 'nullable|numeric|min:0',
                'withdrawal.max_transaction_value' => 'nullable|numeric|min:0',
                'installments' => 'required|array',
            ]);

            // Begin transaction
            DB::beginTransaction();

            // Update PIX fee
            FeeConfiguration::updateOrCreate(
                [
                    'payment_method' => 'pix',
                    'is_global' => true,
                ],
                [
                    'name' => 'Taxa Global PIX',
                    'percentage_fee' => $request->input('pix.percentage_fee'),
                    'fixed_fee' => $request->input('pix.fixed_fee'),
                    'min_amount' => $request->input('pix.min_amount'),
                    'max_amount' => $request->input('pix.max_amount'),
                    'min_transaction_value' => $request->input('pix.min_transaction_value'),
                    'max_transaction_value' => $request->input('pix.max_transaction_value'),
                    'is_active' => true,
                ]
            );

            // Update Credit Card fee
            FeeConfiguration::updateOrCreate(
                [
                    'payment_method' => 'credit_card',
                    'is_global' => true,
                ],
                [
                    'name' => 'Taxa Global Cartão de Crédito',
                    'percentage_fee' => $request->input('credit_card.percentage_fee'),
                    'fixed_fee' => $request->input('credit_card.fixed_fee'),
                    'min_amount' => $request->input('credit_card.min_amount'),
                    'max_amount' => $request->input('credit_card.max_amount'),
                    'min_transaction_value' => $request->input('credit_card.min_transaction_value'),
                    'max_transaction_value' => $request->input('credit_card.max_transaction_value'),
                    'is_active' => true,
                ]
            );

            // Update Bank Slip fee
            FeeConfiguration::updateOrCreate(
                [
                    'payment_method' => 'bank_slip',
                    'is_global' => true,
                ],
                [
                    'name' => 'Taxa Global Boleto Bancário',
                    'percentage_fee' => $request->input('bank_slip.percentage_fee'),
                    'fixed_fee' => $request->input('bank_slip.fixed_fee'),
                    'min_amount' => $request->input('bank_slip.min_amount'),
                    'max_amount' => $request->input('bank_slip.max_amount'),
                    'min_transaction_value' => $request->input('bank_slip.min_transaction_value'),
                    'max_transaction_value' => $request->input('bank_slip.max_transaction_value'),
                    'is_active' => true,
                ]
            );
            
            // Update Withdrawal fee
            FeeConfiguration::updateOrCreate(
                [
                    'payment_method' => 'withdrawal',
                    'is_global' => true,
                ],
                [
                    'name' => 'Taxa Global de Saque',
                    'percentage_fee' => $request->input('withdrawal.percentage_fee'),
                    'fixed_fee' => $request->input('withdrawal.fixed_fee'),
                    'min_amount' => $request->input('withdrawal.min_amount'),
                    'max_amount' => $request->input('withdrawal.max_amount'),
                    'min_transaction_value' => $request->input('withdrawal.min_transaction_value'),
                    'max_transaction_value' => $request->input('withdrawal.max_transaction_value'),
                    'is_active' => true,
                ]
            );

            // Commit transaction
            DB::commit();

            // Clear cache after update - IMPORTANT for performance
            FeeHelper::clearCache();

            // Get updated fees from cache (will rebuild)
            $fees = FeeHelper::getGlobalFees();
            $fees['installments'] = $request->input('installments');

            return response()->json([
                'success' => true,
                'message' => 'Taxas globais atualizadas com sucesso!',
                'fees' => $fees
            ]);

        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            
            Log::error('Erro ao atualizar taxas globais: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao atualizar taxas globais: ' . $e->getMessage()
            ], 500);
        }
    }
}
