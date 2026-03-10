<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    // Desabilita timestamps automáticos (created_at e updated_at)
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'adquirente_id',
        'gateway_transaction_id',
        'external_ref',
        'gateway_data',
        'valor_bruto',
        'valor_liquido',
        'taxa_percentual_aplicada',
        'taxa_fixa_aplicada',
        'status',
        'criado_em',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function acquirer()
    {
        // Relacionamento com acquirers (para buscar dados da API)
        return $this->belongsTo(Acquirer::class, 'adquirente_id');
    }
    
    public function adquirente()
    {
        // Relacionamento com adquirentes (tabela da foreign key)
        return $this->belongsTo(Adquirente::class, 'adquirente_id');
    }

    public function transacao()
    {
        return $this->hasOne(Transacao::class, 'venda_id');
    }
}

