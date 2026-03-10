<?php

namespace App\Helpers;

use App\Models\FeeConfiguration;
use Illuminate\Support\Facades\Cache;

class FeeHelper
{
    /**
     * Get all global fees with caching for performance
     * 
     * @return array
     */
    public static function getGlobalFees(): array
    {
        return Cache::remember('global_fees', 300, function () {
            // Single query to fetch all global fees
            $fees = FeeConfiguration::where('is_global', true)
                ->where('is_active', true)
                ->get()
                ->keyBy('payment_method');
            
            $defaultFees = [
                'pix' => ['percentage_fee' => 3.50, 'fixed_fee' => 0.00, 'min_amount' => 0.80],
                'credit_card' => ['percentage_fee' => 3.99, 'fixed_fee' => 0.39, 'min_amount' => 0.50],
                'bank_slip' => ['percentage_fee' => 2.49, 'fixed_fee' => 2.00, 'min_amount' => 2.50],
                'withdrawal' => ['percentage_fee' => 0.00, 'fixed_fee' => 10.00, 'min_amount' => 10.00],
            ];
            
            $result = [];
            foreach (['pix', 'credit_card', 'bank_slip', 'withdrawal'] as $method) {
                $fee = $fees->get($method);
                $default = $defaultFees[$method];
                
                $result[$method] = [
                    'percentage_fee' => $fee ? $fee->percentage_fee : $default['percentage_fee'],
                    'fixed_fee' => $fee ? $fee->fixed_fee : $default['fixed_fee'],
                    'min_amount' => $fee ? $fee->min_amount : $default['min_amount'],
                    'max_amount' => $fee ? $fee->max_amount : null,
                    'min_transaction_value' => $fee ? $fee->min_transaction_value : null,
                    'max_transaction_value' => $fee ? $fee->max_transaction_value : null,
                ];
            }
            
            return $result;
        });
    }
    
    /**
     * Get specific global fee by payment method
     * 
     * @param string $paymentMethod
     * @return array|null
     */
    public static function getGlobalFee(string $paymentMethod): ?array
    {
        $fees = self::getGlobalFees();
        return $fees[$paymentMethod] ?? null;
    }
    
    /**
     * Get withdrawal fee (legacy method - uses global fee)
     * 
     * @return float
     */
    public static function getWithdrawalFee(): float
    {
        $fee = self::getGlobalFee('withdrawal');
        return $fee ? $fee['fixed_fee'] : 10.00;
    }
    
    /**
     * Get withdrawal fee for specific user (considers custom fees)
     * 
     * @param \App\Models\User|null $user
     * @param float $amount
     * @return array
     */
    public static function getUserWithdrawalFee($user = null, float $amount = 0): array
    {
        // If no user, use global fee
        if (!$user) {
            $globalFee = self::getGlobalFee('withdrawal');
            return [
                'type' => 'global',
                'fixed_fee' => $globalFee ? $globalFee['fixed_fee'] : 10.00,
                'percentage_fee' => $globalFee ? $globalFee['percentage_fee'] : 0.00,
                'total_fee' => $globalFee ? $globalFee['fixed_fee'] : 10.00,
            ];
        }
        
        // Check if user has custom withdrawal fee
        if ($user->withdrawal_fee_type && $user->withdrawal_fee_type !== 'global') {
            $fixedFee = 0;
            $percentageFee = 0;
            
            if (in_array($user->withdrawal_fee_type, ['fixed', 'both'])) {
                $fixedFee = $user->withdrawal_fee_fixed ?? 0;
            }
            
            if (in_array($user->withdrawal_fee_type, ['percentage', 'both'])) {
                $percentageFee = ($amount * ($user->withdrawal_fee_percentage ?? 0)) / 100;
            }
            
            return [
                'type' => $user->withdrawal_fee_type,
                'fixed_fee' => $fixedFee,
                'percentage_fee' => $user->withdrawal_fee_percentage ?? 0,
                'total_fee' => $fixedFee + $percentageFee,
            ];
        }
        
        // Use global fee
        $globalFee = self::getGlobalFee('withdrawal');
        return [
            'type' => 'global',
            'fixed_fee' => $globalFee ? $globalFee['fixed_fee'] : 10.00,
            'percentage_fee' => $globalFee ? $globalFee['percentage_fee'] : 0.00,
            'total_fee' => $globalFee ? $globalFee['fixed_fee'] : 10.00,
        ];
    }
    
    /**
     * Clear global fees cache
     * Call this when fees are updated
     */
    public static function clearCache(): void
    {
        Cache::forget('global_fees');
    }
}
