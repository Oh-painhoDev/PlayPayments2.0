<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Acquirer extends Model
{
    protected $table = 'acquirers'; // Usa a tabela acquirers (não adquirentes)
    
    protected $fillable = [
        'name',
        'display_name',
        'api_url',
        'public_key',
        'secret_key',
        'active'
    ];
}
