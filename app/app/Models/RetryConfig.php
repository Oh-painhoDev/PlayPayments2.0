<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RetryConfig extends Model
{
    protected $table = 'retry_config';

    protected $fillable = [
        'is_enabled',
        'retry_gateway_id',
        'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    /**
     * Get the retry gateway
     */
    public function retryGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'retry_gateway_id');
    }

    /**
     * Get the global retry configuration
     */
    public static function getGlobal()
    {
        return self::first();
    }
}
