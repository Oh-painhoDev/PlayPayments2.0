<?php

namespace App\Services;

use App\Models\RetentionConfig;
use App\Models\UserRetentionConfig;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RetentionService
{
    /**
     * Process transaction for retention
     */
    public function processTransaction(Transaction $transaction)
    {
        // Only process paid transactions (including all variations of paid status)
        if (!$this->isPaidStatus($transaction->status)) {
            return false; 
        }
        
        // Skip if already retained
        if ($transaction->is_retained) {
            Log::info('Transaction already retained, skipping', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id
            ]);
            return false;
        }
        
        // CRITICAL: Check if transaction was already processed for wallet credit
        $existingWalletCredit = \App\Models\WalletTransaction::where('reference_id', $transaction->transaction_id)
            ->where('type', 'credit')
            ->where('category', 'payment_received')
            ->first();
            
        if ($existingWalletCredit) {
            Log::warning('Transaction already has wallet credit, checking retention status', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'existing_wallet_transaction_id' => $existingWalletCredit->id
            ]);
        }
        
        // Check if user has individual retention config
        $userConfig = UserRetentionConfig::where('user_id', $transaction->user_id)
            ->where('is_active', true)
            ->first();
            
        if (!$userConfig) {
            Log::info('No individual retention config found, transaction will be processed normally', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id
            ]);
            // No individual config, skip retention
            return false;
        }
        
        try {
            DB::beginTransaction();
            
            // Individual config - count only this user's transactions
            $paidCount = Transaction::where('user_id', $transaction->user_id)
                ->where('status', 'paid')
                ->where('is_retained', false)
                ->where('is_counted_in_cycle', true)
                ->count();
                
            $retainedCount = Transaction::where('user_id', $transaction->user_id)
                ->where('is_retained', true)
                ->where('is_counted_in_cycle', true)
                ->count();
            
            Log::info('Processing transaction for individual retention', [
                'transaction_id' => $transaction->id,
                'user_id' => $transaction->user_id,
                'current_paid_count' => $paidCount,
                'current_retained_count' => $retainedCount,
                'cycle_limit' => $userConfig->quantity_cycle,
                'retention_limit' => $userConfig->quantity_retained
            ]);
            
            // Check if we need to reset the cycle
            if ($paidCount >= $userConfig->quantity_cycle && $retainedCount >= $userConfig->quantity_retained) {
                // Reset only this user's transactions
                Transaction::where('user_id', $transaction->user_id)
                    ->where('is_counted_in_cycle', true)
                    ->update(['is_counted_in_cycle' => false]);
                
                $userConfig->last_reset_at = now();
                $userConfig->save();
                
                // Reset counters
                $paidCount = 0;
                $retainedCount = 0;
                
                Log::info('Individual retention cycle reset', [
                    'user_id' => $transaction->user_id,
                    'config_id' => $userConfig->id,
                    'transaction_id' => $transaction->id
                ]);
            }
            
            // Determine if this transaction should be retained
            $shouldRetain = false;
            
            if ($paidCount >= $userConfig->quantity_cycle && $retainedCount < $userConfig->quantity_retained) {
                $shouldRetain = true;
            }
            
            // Mark transaction as part of the cycle
            $transaction->is_counted_in_cycle = true;
            
            if ($shouldRetain) {
                // Mark as retained
                $transaction->is_retained = true;
                $transaction->retention_date = now();
                
                Log::info('Transaction retained (individual config)', [
                    'transaction_id' => $transaction->id,
                    'transaction_external_id' => $transaction->external_id,
                    'user_id' => $transaction->user_id,
                    'amount' => $transaction->amount,
                    'config_id' => $userConfig->id,
                    'cycle_count' => $paidCount,
                    'retained_count' => $retainedCount
                ]);
            } else {
                Log::info('Transaction not retained (individual config)', [
                    'transaction_id' => $transaction->id,
                    'transaction_external_id' => $transaction->external_id,
                    'user_id' => $transaction->user_id,
                    'paid_count' => $paidCount,
                    'retained_count' => $retainedCount,
                    'should_retain' => $shouldRetain,
                    'config_id' => $userConfig->id
                ]);
            }
            
            $transaction->save();
            
            DB::commit();
            return $shouldRetain;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing transaction for individual retention: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Check if status represents a paid transaction
     */
    private function isPaidStatus(string $status): bool
    {
        $paidStatuses = [
            'paid',
            'paid_out',
            'paidout',
            'completed',
            'success',
            'successful',
            'approved',
            'confirmed',
            'settled',
            'captured'
        ];
        
        return in_array(strtolower($status), $paidStatuses);
    }
    
    /**
     * Get retention statistics
     */
    public function getStatistics()
    {
        $totalRetained = Transaction::where('is_retained', true)->count();
        $totalRetainedAmount = Transaction::where('is_retained', true)->sum('amount');
        
        // Get stats from individual configs
        $individualStats = UserRetentionConfig::where('is_active', true)
            ->get()
            ->map(function ($config) {
                return $config->getCycleProgress();
            });
            
        if ($individualStats->isEmpty()) {
            return [
                'total_retained' => $totalRetained,
                'total_retained_amount' => $totalRetainedAmount,
                'cycle_progress' => 0,
                'retained_progress' => 0,
                'is_active' => false
            ];
        }
        
        // Calculate average progress from individual configs
        $avgCycleProgress = $individualStats->avg('cycle_progress') ?? 0;
        $avgRetainedProgress = $individualStats->avg('retained_progress') ?? 0;
        $totalPaidCount = $individualStats->sum('paid_count') ?? 0;
        $totalRetainedCount = $individualStats->sum('retained_count') ?? 0;
        $totalCycleLimit = $individualStats->sum('cycle_total') ?? 1;
        $totalRetainedLimit = $individualStats->sum('retained_total') ?? 1;
            
        return [
            'total_retained' => $totalRetained,
            'total_retained_amount' => $totalRetainedAmount,
            'cycle_progress' => $avgCycleProgress,
            'retained_progress' => $avgRetainedProgress,
            'paid_count' => $totalPaidCount,
            'retained_count' => $totalRetainedCount,
            'cycle_total' => $totalCycleLimit,
            'retained_total' => $totalRetainedLimit,
            'is_active' => $individualStats->isNotEmpty(),
            'last_reset' => $individualStats->max('last_reset') ?? null
        ];
    }
}