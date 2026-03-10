<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    // A tabela só tem created_at, não updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'cpf',
        'endereco',
    ];

    public function transacoes()
    {
        return $this->hasMany(Transacao::class, 'cliente_id');
    }
}

