<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PixKey extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'key',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Relacionamento com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se a chave está ativa
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Scope para chaves ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para chaves inativas
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Obtém o tipo formatado
     */
    public function getTypeLabelAttribute(): string
    {
        $types = [
            'EMAIL' => 'E-mail',
            'CPF' => 'CPF',
            'CNPJ' => 'CNPJ',
            'PHONE' => 'Telefone',
            'EVP' => 'Chave Aleatória',
        ];

        return $types[$this->type] ?? $this->type;
    }
}
