<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UtmifyIntegration extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'api_token',
        'pixel_id',
        'platform_name',
        'trigger_on_payment',
        'trigger_on_creation',
        'is_active',
    ];

    protected $casts = [
        'trigger_on_payment' => 'boolean',
        'trigger_on_creation' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Não esconder api_token por padrão, pois precisamos para edição
    // Podemos usar makeVisible() quando necessário

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
