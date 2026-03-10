<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatewayFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway_id',
        'payment_method',
        'percentage_fee',
        'fixed_fee',
        'min_amount',
        'max_amount',
        'min_transaction_value',
        'max_transaction_value',
        'is_active',
    ];

    protected $casts = [
        'percentage_fee' => 'decimal:4',
        'fixed_fee' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'min_transaction_value' => 'decimal:2',
        'max_transaction_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the gateway that owns the fee.
     */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    /**
     * Calculate fee for amount
     */
    public function calculateFee(float $amount): array
    {
        $percentageFee = ($amount * $this->percentage_fee) / 100;
        $totalFee = $percentageFee + $this->fixed_fee;
        
        // Apply minimum amount
        if ($this->min_amount && $totalFee < $this->min_amount) {
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
        return $this->min_amount ? 'R$ ' . number_format($this->min_amount, 2, ',', '.') : 'N/A';
    }

    /**
     * Get formatted max amount
     */
    public function getFormattedMaxAmountAttribute(): string
    {
        return $this->max_amount ? 'R$ ' . number_format($this->max_amount, 2, ',', '.') : 'N/A';
    }

    /**
     * Get default fees for a payment method
     */
    public static function getDefaultFees(string $paymentMethod): array
    {
        $defaults = [
            'pix' => [
                'percentage_fee' => 0.99,
                'fixed_fee' => 0.00,
                'min_amount' => 0.01,
                'max_amount' => null,
                'min_transaction_value' => 1.00,
                'max_transaction_value' => null,
            ],
            'credit_card' => [
                'percentage_fee' => 2.99,
                'fixed_fee' => 0.30,
                'min_amount' => 0.50,
                'max_amount' => null,
                'min_transaction_value' => 5.00,
                'max_transaction_value' => null,
            ],
            'bank_slip' => [
                'percentage_fee' => 1.99,
                'fixed_fee' => 1.50,
                'min_amount' => 2.00,
                'max_amount' => null,
                'min_transaction_value' => 10.00,
                'max_transaction_value' => null,
            ],
        ];

        return $defaults[$paymentMethod] ?? [
            'percentage_fee' => 0.00,
            'fixed_fee' => 0.00,
            'min_amount' => null,
            'max_amount' => null,
            'min_transaction_value' => null,
            'max_transaction_value' => null,
        ];
    }
}