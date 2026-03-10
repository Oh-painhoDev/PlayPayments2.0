<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiGatewayConfig extends Model
{
    protected $table = 'multi_gateway_config';
    
    protected $fillable = [
        'is_enabled',
        'mode',
        'selected_gateways',
        'selected_users',
    ];
    
    protected $casts = [
        'is_enabled' => 'boolean',
        'selected_gateways' => 'array',
        'selected_users' => 'array',
    ];
    
    /**
     * Get global multi-gateway configuration
     */
    public static function getGlobal()
    {
        $config = self::first();
        
        if (!$config) {
            $config = self::create([
                'is_enabled' => false,
                'mode' => 'global',
                'selected_gateways' => [],
                'selected_users' => [],
            ]);
        }
        
        return $config;
    }
    
    /**
     * Check if multi-gateway is enabled for a specific user
     */
    public static function isEnabledForUser($userId)
    {
        $config = self::getGlobal();
        
        if (!$config->is_enabled) {
            return false;
        }
        
        switch ($config->mode) {
            case 'global':
                return true;
                
            case 'specific_users':
                return in_array($userId, $config->selected_users ?? []);
                
            case 'all_except':
                return !in_array($userId, $config->selected_users ?? []);
                
            default:
                return false;
        }
    }
    
    /**
     * Get active gateways for multi-gateway
     */
    public static function getActiveGateways()
    {
        $config = self::getGlobal();
        
        if (!$config->is_enabled || empty($config->selected_gateways)) {
            return collect();
        }
        
        return PaymentGateway::whereIn('id', $config->selected_gateways)
            ->where('is_active', true)
            ->get();
    }
}
