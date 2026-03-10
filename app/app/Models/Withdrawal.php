<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'withdrawal_id',
        'external_id',
        'amount',
        'fee',
        'net_amount',
        'pix_type',
        'pix_key',
        'status',
        'baas_provider_id',
        'error_message',
        'response_data',
        'webhook_data',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'response_data' => 'array',
        'webhook_data' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that owns the withdrawal.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if withdrawal is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if withdrawal is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if withdrawal is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if withdrawal is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if withdrawal is failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Get formatted fee
     */
    public function getFormattedFeeAttribute(): string
    {
        return 'R$ ' . number_format($this->fee, 2, ',', '.');
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
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            'failed' => 'Falhou',
            default => 'Desconhecido'
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'text-yellow-400',
            'processing' => 'text-blue-400',
            'completed' => 'text-green-400',
            'cancelled' => 'text-gray-400',
            'failed' => 'text-red-400',
            default => 'text-gray-400'
        };
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Get formatted completed date
     */
    public function getFormattedCompletedAtAttribute(): ?string
    {
        return $this->completed_at ? $this->completed_at->format('d/m/Y H:i') : null;
    }

    /**
     * Get PIX type label
     */
    public function getPixTypeLabelAttribute(): string
    {
        return match($this->pix_type) {
            'email' => 'E-mail',
            'cpf' => 'CPF',
            'phone' => 'Telefone',
            'random' => 'Chave Aleatória',
            default => 'Desconhecido'
        };
    }
}