<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGoalAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'achieved_value',
        'reward_value',
        'reward_type',
        'reward_description',
        'reward_given',
        'reward_given_at',
        'notes',
    ];

    protected $casts = [
        'achieved_value' => 'decimal:2',
        'reward_value' => 'decimal:2',
        'reward_given' => 'boolean',
        'reward_given_at' => 'datetime',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com meta
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
