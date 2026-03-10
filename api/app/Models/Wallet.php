<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'reserved_balance',
        'blocked_balance',
        'total_received',
        'total_withdrawn',
        'currency',
        'is_active',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'reserved_balance' => 'decimal:2',
        'blocked_balance' => 'decimal:2',
        'total_received' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'is_active' => 'boolean',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the wallet transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get recent transactions.
     */
    public function recentTransactions(int $limit = 10): HasMany
    {
        return $this->transactions()->latest()->limit($limit);
    }

    /**
     * Get available balance (balance - blocked_balance).
     */
    public function getAvailableBalanceAttribute(): float
    {
        return max(0, $this->balance - $this->blocked_balance);
    }

    /**
     * Get total balance (balance + pending_balance + blocked_balance).
     */
    public function getTotalBalanceAttribute(): float
    {
        return $this->balance + $this->pending_balance;
    }

    /**
     * Get total available balance (balance + pending_balance).
     */
    public function getTotalAvailableBalanceAttribute(): float
    {
        return $this->balance + $this->pending_balance;
    }

    /**
     * Get formatted balance.
     */
    public function getFormattedBalanceAttribute(): string
    {
        return 'R$ ' . number_format($this->balance, 2, ',', '.');
    }

    /**
     * Get formatted pending balance.
     */
    public function getFormattedPendingBalanceAttribute(): string
    {
        return 'R$ ' . number_format($this->pending_balance, 2, ',', '.');
    }

    /**
     * Get formatted total available balance.
     */
    public function getFormattedTotalAvailableBalanceAttribute(): string
    {
        return 'R$ ' . number_format($this->total_available_balance, 2, ',', '.');
    }

    /**
     * Add credit to wallet.
     */
    public function addCredit(
        float $amount,
        string $category,
        string $description = null,
        array $metadata = [],
        string $referenceId = null
    ): WalletTransaction {
        // CRITICAL: Prevent duplicate credits by checking reference_id
        if ($referenceId) {
            $existingTransaction = WalletTransaction::where('wallet_id', $this->id)
                ->where('reference_id', $referenceId)
                ->where('type', 'credit')
                ->where('category', $category)
                ->first();
                
            if ($existingTransaction) {
                Log::warning('Tentativa de crédito duplicado detectada e bloqueada', [
                    'wallet_id' => $this->id,
                    'user_id' => $this->user_id,
                    'reference_id' => $referenceId,
                    'category' => $category,
                    'amount' => $amount,
                    'existing_transaction_id' => $existingTransaction->id,
                    'existing_created_at' => $existingTransaction->created_at
                ]);
                
                return $existingTransaction;
            }
        }
        
        // CRITICAL: Verify this wallet belongs to the correct user
        if (!$this->user_id) {
            throw new \Exception('Wallet user_id is null - cannot add credit safely');
        }
        
        Log::info('Adicionando crédito à wallet', [
            'wallet_id' => $this->id,
            'user_id' => $this->user_id,
            'amount' => $amount,
            'category' => $category,
            'reference_id' => $referenceId,
            'balance_before' => $this->balance
        ]);
        
        $balanceBefore = $this->balance;
        $this->balance += $amount;
        $this->total_received += $amount;
        $this->last_transaction_at = now();
        $this->save();

        $walletTransaction = $this->transactions()->create([
            'transaction_id' => 'TXN_' . strtoupper(uniqid()),
            'type' => 'credit',
            'category' => $category,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'metadata' => $metadata,
            'reference_id' => $referenceId,
            'status' => 'completed',
            'processed_at' => now(),
        ]);
        
        Log::info('Crédito adicionado com sucesso à wallet', [
            'wallet_id' => $this->id,
            'user_id' => $this->user_id,
            'wallet_transaction_id' => $walletTransaction->id,
            'amount' => $amount,
            'balance_after' => $this->balance,
            'reference_id' => $referenceId
        ]);
        
        return $walletTransaction;
    }

    /**
     * Add debit to wallet.
     * 
     * Modified to allow negative balances for refunds and chargebacks
     * 
     * @return WalletTransaction|false
     */
    public function addDebit(
        float $amount,
        string $category,
        string $description = null,
        array $metadata = [],
        string $referenceId = null
    ): WalletTransaction {
        $balanceBefore = $this->balance;

        // Verificar se tem saldo suficiente para saques
        if ($category === 'withdrawal' && $this->balance < $amount) {
            return false;
        }
        
        // For refunds, chargebacks, and withdrawals, allow the balance to go negative
        // For other categories, limit the debit to the available balance
        if ($category === 'refund' || $category === 'chargeback' || $category === 'withdrawal') {
            // Allow full debit amount even if it makes balance negative
            $debitAmount = $amount;
        } else {
            // For other categories, limit to available balance
            $debitAmount = min($amount, $this->balance);
        }
        
        $this->balance -= $debitAmount;
        
        // If this is a withdrawal, update total_withdrawn
        if ($category === 'withdrawal') {
            $this->total_withdrawn += $debitAmount;
        }
        
        $this->last_transaction_at = now();
        $this->save();

        return $this->transactions()->create([
            'transaction_id' => 'TXN_' . strtoupper(uniqid()),
            'type' => 'debit',
            'category' => $category,
            'amount' => $debitAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'metadata' => $metadata,
            'reference_id' => $referenceId,
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Check if wallet has sufficient balance (considering blocked balance).
     * 
     * @param float $amount
     * @return bool
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }

    /**
     * Block amount (for disputes/chargebacks).
     */
    public function blockAmount(float $amount): void
    {
        $this->blocked_balance += $amount;
        $this->save();
    }

    /**
     * Unblock amount (when dispute is resolved).
     */
    public function unblockAmount(float $amount): void
    {
        $this->blocked_balance = max(0, $this->blocked_balance - $amount);
        $this->save();
    }

    /**
     * Get wallet statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_transactions' => $this->transactions()->count(),
            'total_credits' => $this->transactions()->where('type', 'credit')->sum('amount'),
            'total_debits' => $this->transactions()->where('type', 'debit')->sum('amount'),
            'average_transaction' => $this->transactions()->avg('amount'),
            'last_transaction' => $this->transactions()->latest()->first(),
        ];
    }
}