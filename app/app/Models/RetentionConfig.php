<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetentionConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity_cycle',
        'quantity_retained',
        'is_active',
        'last_reset_at',
        'selected_users',
        'selection_mode',
    ];

    protected $casts = [
        'quantity_cycle' => 'integer',
        'quantity_retained' => 'integer',
        'is_active' => 'boolean',
        'last_reset_at' => 'datetime',
        'selected_users' => 'array',
    ];

    /**
     * Get the current active configuration
     */
    public static function getActive()
    {
        return self::first();
    }

    /**
     * Check if retention is active
     */
    public static function isActive()
    {
        $config = self::first();
        return $config && $config->is_active;
    }

    /**
     * Get the current cycle progress
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
        
        $paidCount = Transaction::whereIn('status', $paidStatuses)
            ->where('is_retained', false)
            ->where('is_counted_in_cycle', true)
            ->count();
            
        $retainedCount = Transaction::where('is_retained', true)
            ->where('is_counted_in_cycle', true)
            ->count();
            
        return [
            'paid_count' => $paidCount,
            'retained_count' => $retainedCount,
            'cycle_total' => $this->quantity_cycle,
            'retained_total' => $this->quantity_retained,
            'cycle_progress' => min(100, ($paidCount / $this->quantity_cycle) * 100),
            'retained_progress' => min(100, ($retainedCount / $this->quantity_retained) * 100),
            'is_cycle_complete' => $paidCount >= $this->quantity_cycle && $retainedCount >= $this->quantity_retained,
        ];
    }

    /**
     * Reset the current cycle
     */
    public function resetCycle()
    {
        Transaction::where('is_counted_in_cycle', true)
            ->update(['is_counted_in_cycle' => false]);
            
        $this->last_reset_at = now();
        $this->save();
        
        return true;
    }

    /**
     * Check if a user is selected for retention
     * Fixed to properly handle all users regardless of gateway
     */
    public function isUserSelected(int $userId): bool
    {
        // If selection mode is 'all', all users are selected
        if ($this->selection_mode === 'all') {
            return true;
        }
        
        // If selected_users is null or empty, handle based on mode
        if (empty($this->selected_users)) {
            // If mode is 'selected' and no users are selected, no one is selected
            // If mode is 'excluded' and no users are excluded, everyone is selected
            return $this->selection_mode === 'excluded';
        }
        
        $isInList = in_array($userId, $this->selected_users);
        
        // If mode is 'selected', user must be in the list
        // If mode is 'excluded', user must NOT be in the list
        return $this->selection_mode === 'selected' ? $isInList : !$isInList;
    }
}