<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'type',
        'category',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'metadata',
        'reference_id',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];
    
    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($walletTransaction) {
            // CRITICAL: Verify wallet_id is set and belongs to correct user
            if (empty($walletTransaction->wallet_id)) {
                throw new \Exception('WalletTransaction wallet_id cannot be empty');
            }
            
            $wallet = \App\Models\Wallet::find($walletTransaction->wallet_id);
            if (!$wallet) {
                throw new \Exception('Wallet not found for wallet_id: ' . $walletTransaction->wallet_id);
            }
            
            // CRITICAL: Check for duplicate transactions with same reference_id
            if ($walletTransaction->reference_id && $walletTransaction->type === 'credit') {
                $existingTransaction = self::where('wallet_id', $walletTransaction->wallet_id)
                    ->where('reference_id', $walletTransaction->reference_id)
                    ->where('type', 'credit')
                    ->where('category', $walletTransaction->category)
                    ->first();
                    
                if ($existingTransaction) {
                    throw new \Exception('Duplicate wallet transaction detected: reference_id ' . $walletTransaction->reference_id . ' already exists for wallet ' . $walletTransaction->wallet_id);
                }
            }
            
            Log::info('Creating wallet transaction', [
                'wallet_id' => $walletTransaction->wallet_id,
                'wallet_user_id' => $wallet->user_id,
                'amount' => $walletTransaction->amount,
                'type' => $walletTransaction->type,
                'category' => $walletTransaction->category,
                'reference_id' => $walletTransaction->reference_id,
                'description' => $walletTransaction->description
            ]);
        });
    }

    /**
     * Get the wallet that owns the transaction.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->type === 'credit' ? '+' : '-';
        return $prefix . 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get transaction icon based on category.
     */
    public function getIconAttribute(): string
    {
        return match($this->category) {
            'payment_received' => '??',
            'withdrawal' => '??',
            'refund' => '??',
            'chargeback' => '??',
            'fee' => '??',
            'bonus' => '??',
            'adjustment' => '??',
            'transfer_in' => '??',
            'transfer_out' => '??',
            default => '??'
        };
    }

    /**
     * Get transaction color based on type.
     */
    public function getColorAttribute(): string
    {
        return $this->type === 'credit' ? 'text-green-400' : 'text-red-400';
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'payment_received' => 'Pagamento Recebido',
            'withdrawal' => 'Saque',
            'refund' => 'Estorno',
            'chargeback' => 'Chargeback',
            'fee' => 'Taxa',
            'bonus' => 'B�nus',
            'adjustment' => 'Ajuste',
            'transfer_in' => 'Transfer�ncia Recebida',
            'transfer_out' => 'Transfer�ncia Enviada',
            default => 'Transa��o'
        };
    }

    /**
     * Scope for credits.
     */
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope for debits.
     */
    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    /**
     * Scope for completed transactions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}