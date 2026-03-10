<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRetentionConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quantity_cycle',
        'quantity_retained',
        'is_active',
        'last_reset_at',
    ];

    protected $casts = [
        'quantity_cycle' => 'integer',
        'quantity_retained' => 'integer',
        'is_active' => 'boolean',
        'last_reset_at' => 'datetime',
    ];

    /**
     * Get the user that owns the retention config
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current cycle progress for this user
     */
    public function getCycleProgress()
    {
        // Get all possible paid statuses
        $paidStatuses = [
            'paid',
            'paid_out',
            'paidout',
            'completed',
            'success',
            'successful',
            'approved',
            'confirmed',
            'settled',
            'captured'
        ];
        
        $paidCount = Transaction::where('user_id', $this->user_id)
            ->whereIn('status', $paidStatuses)
            ->where('is_retained', false)
            ->where('is_counted_in_cycle', true)
            ->count();
            
        $retainedCount = Transaction::where('user_id', $this->user_id)
            ->where('is_retained', true)
            ->where('is_counted_in_cycle', true)
            ->count();
            
        return [
            'paid_count' => $paidCount,
            'retained_count' => $retainedCount,
            'cycle_total' => $this->quantity_cycle,
            'retained_total' => $this->quantity_retained,
            'cycle_progress' => min(100, ($paidCount / max(1, $this->quantity_cycle)) * 100),
            'retained_progress' => min(100, ($retainedCount / max(1, $this->quantity_retained)) * 100),
            'is_cycle_complete' => $paidCount >= $this->quantity_cycle && $retainedCount >= $this->quantity_retained,
        ];
    }

    /**
     * Reset the current cycle for this user
     */
    public function resetCycle()
    {
        Transaction::where('user_id', $this->user_id)
            ->where('is_counted_in_cycle', true)
            ->update(['is_counted_in_cycle' => false]);
            
        $this->last_reset_at = now();
        $this->save();
        
        return true;
    }

    /**
     * Get or create retention config for user
     */
    public static function getForUser(int $userId): self
    {
        return self::firstOrCreate(
            ['user_id' => $userId],
            [
                'quantity_cycle' => 5,
                'quantity_retained' => 1,
                'is_active' => false,
            ]
        );
    }
}