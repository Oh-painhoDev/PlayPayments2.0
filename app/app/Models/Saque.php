<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Saque extends Model
{
    use HasFactory;

    protected $table = 'saques';
    
    public $timestamps = false; // Usa criado_em e atualizado_em manualmente
    
    protected $fillable = [
        'user_id',
        'valor',
        'status',
        'criado_em',
        'atualizado_em',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'criado_em' => 'datetime',
        'atualizado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

