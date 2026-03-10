<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FeeConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'payment_method',
        'percentage_fee',
        'fixed_fee',
        'min_amount',
        'max_amount',
        'min_transaction_value',
        'max_transaction_value',
        'is_global',
        'is_active',
    ];

    protected $casts = [
        'percentage_fee' => 'decimal:2',
        'fixed_fee' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'min_transaction_value' => 'decimal:2',
        'max_transaction_value' => 'decimal:2',
        'is_global' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get global fee configurations
     */
    public static function getGlobalFees(): array
    {
        try {
            $fees = self::where('is_global', true)
                ->where('is_active', true)
                ->get()
                ->keyBy('payment_method');

            return [
                'pix' => $fees->get('pix'),
                'credit_card' => $fees->get('credit_card'),
                'bank_slip' => $fees->get('bank_slip'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting global fees: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'pix' => null,
                'credit_card' => null,
                'bank_slip' => null,
            ];
        }
    }

    /**
     * Get fee for specific payment method
     */
    public static function getFeeForMethod(string $method): ?self
    {
        return self::where('payment_method', $method)
            ->where('is_global', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Calculate fee for amount
     */
    public function calculateFee(float $amount): array
    {
        $percentageFee = ($amount * $this->percentage_fee) / 100;
        $totalFee = $percentageFee + $this->fixed_fee;
        
        // Apply minimum amount
        if ($totalFee < $this->min_amount) {
            $totalFee = $this->min_amount;
        }
        
        // Apply maximum amount if set
        if ($this->max_amount && $totalFee > $this->max_amount) {
            $totalFee = $this->max_amount;
        }
        
        return [
            'percentage_fee' => $percentageFee,
            'fixed_fee' => $this->fixed_fee,
            'total_fee' => $totalFee,
            'net_amount' => $amount - $totalFee,
        ];
    }

    /**
     * Check if amount is within transaction limits
     */
    public function isAmountWithinLimits(float $amount): bool
    {
        // If no limits are set, amount is within limits
        if (!$this->min_transaction_value && !$this->max_transaction_value) {
            return true;
        }
        
        // Check minimum transaction value
        if ($this->min_transaction_value && $amount < $this->min_transaction_value) {
            return false;
        }
        
        // Check maximum transaction value
        if ($this->max_transaction_value && $amount > $this->max_transaction_value) {
            return false;
        }
        
        return true;
    }

    /**
     * Get formatted percentage
     */
    public function getFormattedPercentageAttribute(): string
    {
        return number_format($this->percentage_fee, 2) . '%';
    }

    /**
     * Get formatted fixed fee
     */
    public function getFormattedFixedFeeAttribute(): string
    {
        return 'R$ ' . number_format($this->fixed_fee, 2, ',', '.');
    }

    /**
     * Get formatted min amount
     */
    public function getFormattedMinAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->min_amount, 2, ',', '.');
    }
}