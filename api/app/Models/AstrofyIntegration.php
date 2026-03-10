<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AstrofyIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'gateway_key',
        'base_url',
        'payment_types',
        'description',
        'picture_url',
        'is_active',
    ];

    protected $casts = [
        'payment_types' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Verifica se é uma integração global
     */
    public function isGlobal(): bool
    {
        return $this->user_id === null;
    }
}
