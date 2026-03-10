<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'amount',
        'currency',
        'payment_method',
        'allow_custom_amount',
        'min_amount',
        'max_amount',
        'is_active',
        'max_uses',
        'current_uses',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'allow_custom_amount' => 'boolean',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($paymentLink) {
            if (empty($paymentLink->slug)) {
                $paymentLink->slug = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payment_link_id');
    }

    public function getCheckoutUrlAttribute(): string
    {
        return route('checkout.show', $this->slug);
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->isAfter($this->expires_at);
    }

    public function canBeUsed(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
