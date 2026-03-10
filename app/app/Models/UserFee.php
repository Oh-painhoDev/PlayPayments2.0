<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class UserFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method',
        'percentage_fee',
        'fixed_fee',
        'min_amount',
        'max_amount',
        'min_transaction_value',
        'max_transaction_value',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'percentage_fee' => 'decimal:2',
        'fixed_fee' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'min_transaction_value' => 'decimal:2',
        'max_transaction_value' => 'decimal:2',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the user that owns the fee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    
    /**
     * Get installment fee for a specific number of installments
     */
    public function getInstallmentFee(int $installments): float
    {
        if ($installments <= 1) {
            return (float)$this->percentage_fee;
        }
        
        try {
            $metadata = $this->metadata;
            
            // Log metadata for debugging
            Log::info('Getting installment fee', [
                'user_fee_id' => $this->id,
                'installments' => $installments,
                'metadata_type' => gettype($metadata),
                'metadata' => $metadata
            ]);
            
            // If metadata is a string, decode it
            if (is_string($metadata)) {
                $metadata = json_decode($metadata, true);
                Log::info('Decoded metadata from string', ['decoded' => $metadata]);
            }
            
            // If metadata is null or not an array, return default calculation
            if (!is_array($metadata)) {
                Log::warning('Invalid metadata format in UserFee', [
                    'user_fee_id' => $this->id,
                    'metadata' => $metadata
                ]);
                
                // Default calculation
                $additionalFee = ($installments - 1) * 0.6; // 0.6% per additional installment
                return (float)$this->percentage_fee + $additionalFee;
            }
            
            // Check if installments data exists
            if (isset($metadata['installments']) && is_array($metadata['installments'])) {
                $installmentsData = $metadata['installments'];
                $key = "{$installments}x";
                
                if (isset($installmentsData[$key])) {
                    return (float)$installmentsData[$key];
                }
            }
            
            // Default calculation if not found in metadata
            $additionalFee = ($installments - 1) * 0.6; // 0.6% per additional installment
            return (float)$this->percentage_fee + $additionalFee;
            
        } catch (\Exception $e) {
            Log::error('Error getting installment fee', [
                'user_fee_id' => $this->id,
                'installments' => $installments,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Default calculation on error
            $additionalFee = ($installments - 1) * 0.6; // 0.6% per additional installment
            return (float)$this->percentage_fee + $additionalFee;
        }
    }
}