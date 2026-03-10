<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxaUsuario extends Model
{
    use HasFactory;

    protected $table = 'taxas_usuarios';
    
    public $timestamps = false; // Usa atualizado_em manualmente
    
    protected $fillable = [
        'user_id',
        'saque_cripto_fixo',
        'saque_cripto_percentual',
        'saque_pix_fixo',
        'saque_pix_percentual',
        'pix_pago_fixo',
        'pix_pago_percentual',
        'atualizado_em',
    ];

    protected $casts = [
        'saque_cripto_fixo' => 'decimal:2',
        'saque_cripto_percentual' => 'decimal:2',
        'saque_pix_fixo' => 'decimal:2',
        'saque_pix_percentual' => 'decimal:2',
        'pix_pago_fixo' => 'decimal:2',
        'pix_pago_percentual' => 'decimal:2',
        'atualizado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

