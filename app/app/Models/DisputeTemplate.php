<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisputeTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'dispute_type',
        'risk_level',
        'message_title',
        'message_body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getDisputeTypeLabel(): string
    {
        $labels = [
            'chargeback' => 'Chargeback',
            'fraud' => 'Fraude',
            'unauthorized' => 'Não Autorizada',
            'not_received' => 'Não Recebido',
            'defective' => 'Defeituoso',
            'other' => 'Outro',
        ];
        
        return $labels[$this->dispute_type] ?? 'N/A';
    }

    public function getRiskLevelLabel(): string
    {
        $labels = [
            'LOW' => 'Baixo',
            'MED' => 'Médio',
            'HIGH' => 'Alto',
        ];
        
        return $labels[$this->risk_level] ?? 'N/A';
    }
}
