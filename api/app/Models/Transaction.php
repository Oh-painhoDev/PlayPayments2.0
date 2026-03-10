<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gateway_id',
        'payment_link_id',
        'transaction_id',
        'external_id',
        'amount',
        'fee_amount',
        'net_amount',
        'currency',
        'payment_method',
        'status',
        'customer_data',
        'payment_data',
        'metadata',
        'products',
        'shipping_address',
        'expires_at',
        'paid_at',
        'refunded_at',
        'is_retained',
        'is_counted_in_cycle',
        'retention_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'customer_data' => 'array',
        'payment_data' => 'array',
        'metadata' => 'array',
        'products' => 'array',
        'shipping_address' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'is_retained' => 'boolean',
        'is_counted_in_cycle' => 'boolean',
        'retention_date' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = self::generateTransactionId();
            }
            
            // CRITICAL: Ensure user_id is always set
            if (empty($transaction->user_id)) {
                throw new \Exception('Transaction user_id cannot be empty');
            }
        });
    }

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gateway used
     */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

    /**
     * Get the payment link (if transaction was created via payment link)
     */
    public function paymentLink(): BelongsTo
    {
        return $this->belongsTo(PaymentLink::class);
    }

    /**
     * Generate unique transaction ID
     */
    public static function generateTransactionId(): string
    {
        do {
            $id = 'TXN_' . strtoupper(Str::random(16));
        } while (self::where('transaction_id', $id)->exists());

        return $id;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if transaction is authorized
     */
    public function isAuthorized(): bool
    {
        return $this->status === 'authorized';
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
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
        
        return in_array(strtolower($this->status), $paidStatuses);
    }

    /**
     * Check if transaction is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if transaction is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if transaction is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if transaction is refunded
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
    
    /**
     * Check if transaction is refunded or partially refunded or chargeback
     */
    public function isRefundedOrChargeback(): bool
    {
        return $this->isRefunded() || $this->isPartiallyRefunded() || $this->isChargeback();
    }

    /**
     * Check if transaction is partially refunded
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->status === 'partially_refunded';
    }

    /**
     * Check if transaction is chargeback
     */
    public function isChargeback(): bool
    {
        return $this->status === 'chargeback';
    }

    /**
     * Check if transaction is retained
     */
    public function isRetained(): bool
    {
        return $this->is_retained;
    }
    
    /**
     * Get the wallet transactions for this transaction
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class, 'reference_id', 'transaction_id');
    }
    
    /**
     * Get the disputes for this transaction
     */
    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(): bool
    {
        $this->status = 'paid';
        $this->paid_at = now();
        return $this->save();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): bool
    {
        $this->status = 'expired';
        return $this->save();
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get formatted fee amount
     */
    public function getFormattedFeeAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->fee_amount, 2, ',', '.');
    }

    /**
     * Get formatted net amount
     */
    public function getFormattedNetAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->net_amount, 2, ',', '.');
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendente',
            'processing' => 'Processando',
            'authorized' => 'Autorizado',
            'paid' => 'Pago',
            'cancelled' => 'Cancelado',
            'expired' => 'Expirado',
            'failed' => 'Falhou',
            'refunded' => 'Estornado',
            'partially_refunded' => 'Estornado Parcialmente',
            'chargeback' => 'Chargeback',
            default => 'Desconhecido'
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30',
            'processing' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'authorized' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
            'paid' => 'bg-green-500/20 text-green-400 border-green-500/30',
            'cancelled' => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
            'expired' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'failed' => 'bg-red-500/20 text-red-400 border-red-500/30',
            'refunded' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'partially_refunded' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'chargeback' => 'bg-red-500/20 text-red-400 border-red-500/30',
            default => 'bg-gray-500/20 text-gray-400 border-gray-500/30'
        };
    }

    /**
     * Scope for user transactions
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for payment method
     */
    public function scopeByPaymentMethod($query, string $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope for status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Scope for paid transactions (all variations)
     */
    public function scopePaid($query)
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
        
        return $query->whereIn('status', $paidStatuses);
    }
}