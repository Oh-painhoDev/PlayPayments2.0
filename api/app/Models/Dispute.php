<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Dispute extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'dispute_id',
        'amount',
        'status',
        'dispute_type',
        'risk_level',
        'defense_details',
        'defense_file',
        'admin_notes',
        'refunded_at',
        'defended_at',
        'responded_at',
        'dispute_deadline',
        'dispute_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_at' => 'datetime',
        'defended_at' => 'datetime',
        'responded_at' => 'datetime',
        'dispute_deadline' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dispute) {
            if (empty($dispute->dispute_id)) {
                $dispute->dispute_id = self::generateDisputeId();
            }
            
            // Calcula prazo de 2 dias (48 horas) para resposta
            if (empty($dispute->dispute_deadline)) {
                $dispute->dispute_deadline = now()->addDays(2)->toDateString();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public static function generateDisputeId(): string
    {
        do {
            $id = 'DSP_' . strtoupper(Str::random(16));
        } while (self::where('dispute_id', $id)->exists());

        return $id;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDefended(): bool
    {
        return $this->status === 'defended';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function isResponded(): bool
    {
        return $this->status === 'responded';
    }

    public function isDefenseRejected(): bool
    {
        return $this->status === 'defense_rejected';
    }

    public function canDefend(): bool
    {
        return in_array($this->status, ['pending']) && !$this->isExpired();
    }

    public function canRefund(): bool
    {
        return in_array($this->status, ['pending', 'responded', 'defense_rejected']);
    }

    public function isExpired(): bool
    {
        if (!$this->dispute_deadline) {
            return false;
        }
        
        return now()->greaterThan($this->dispute_deadline);
    }

    public function getRemainingDays(): int
    {
        if (!$this->dispute_deadline || $this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->dispute_deadline, false);
    }
}
