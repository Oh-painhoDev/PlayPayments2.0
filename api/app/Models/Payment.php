<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'acquirer_id',
        'transaction_id',
        'amount',
        'status',
        'external_ref',
        'qrcode',
        'customer_id',
    ];

    // Relacionamento com o cliente (usuário)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Relacionamento com a adquirente
    public function acquirer()
    {
        return $this->belongsTo(Acquirer::class);
    }
}
