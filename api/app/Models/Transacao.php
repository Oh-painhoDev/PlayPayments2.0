<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacao extends Model
{
    use HasFactory;

    protected $table = 'transacoes';

    protected $fillable = [
        'cliente_id',
        'adquirente',
        'gateway_transaction_id',
        'external_ref',
        'venda_id',
        'chave_pix',
        'qr_code',
        'status',
        'valor',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    
    public function venda()
    {
        return $this->belongsTo(Venda::class, 'venda_id');
    }
}

