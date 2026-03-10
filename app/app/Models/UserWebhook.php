<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'description',
        'events',
        'is_active',
        'secret',
        'last_triggered_at',
        'failure_count',
        'last_failure_message',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    /**
     * Get the user that owns the webhook.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if webhook should be triggered for a specific event
     */
    public function shouldTriggerForEvent(string $event): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return in_array($event, $this->events);
    }

    /**
     * Record a successful trigger
     */
    public function recordSuccess(): void
    {
        $this->last_triggered_at = now();
        $this->failure_count = 0;
        $this->last_failure_message = null;
        $this->save();
    }

    /**
     * Record a failure
     */
    public function recordFailure(string $message): void
    {
        $this->last_triggered_at = now();
        $this->failure_count += 1;
        $this->last_failure_message = $message;
        
        // Disable webhook if it fails too many times
        if ($this->failure_count >= 10) {
            $this->is_active = false;
        }
        
        $this->save();
    }

    /**
     * Get masked secret
     */
    public function getMaskedSecretAttribute(): string
    {
        if (!$this->secret) {
            return '';
        }
        
        $prefix = substr($this->secret, 0, 7); // whsec_
        $suffix = substr($this->secret, -4);
        
        return $prefix . '...' . $suffix;
    }

    /**
     * Get formatted events list
     */
    public function getFormattedEventsAttribute(): string
    {
        if (!$this->events || empty($this->events)) {
            return 'Nenhum';
        }
        
        $eventLabels = [
            'transaction.created' => 'Venda aguardando pagamento',
            'transaction.paid' => 'Vendas aprovadas',
            'transaction.failed' => 'Venda recusada',
            'transaction.expired' => 'Transação Expirada',
            'transaction.refunded' => 'Venda estornadas',
            'transaction.chargeback' => 'Venda chargeback',
            'transaction.cancelled' => 'Venda canceladas',
        ];
        
        $formattedEvents = [];
        foreach ($this->events as $event) {
            $formattedEvents[] = $eventLabels[$event] ?? $event;
        }
        
        return implode(', ', $formattedEvents);
    }
}