<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    protected $fillable = [
        'user_id',
        'api_key',
        'is_active',
        'utmify_token',
        'utmify_token_is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'utmify_token_is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
