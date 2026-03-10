<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adquirente extends Model
{
    protected $table = 'adquirentes'; // Tabela para foreign key de vendas
    
    public $timestamps = false;
    
    protected $fillable = [
        'nome',
        'taxa_percentual',
        'taxa_fixa',
        'ativo'
    ];
}

