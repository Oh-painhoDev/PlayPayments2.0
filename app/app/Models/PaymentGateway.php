<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'api_url',
        'is_active',
        'is_default',
        'supported_methods',
        'config',
        'parent_gateway_id',
        'webhook_name',
        'is_base',
        'is_whitelabel',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_base' => 'boolean',
        'is_whitelabel' => 'boolean',
        'supported_methods' => 'array',
        'config' => 'array',
    ];

    /**
     * Get the parent gateway (adquirente base)
     */
    public function parentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'parent_gateway_id');
    }

    /**
     * Get sub-adquirentes (whitelabels) of this gateway
     */
    public function subGateways(): HasMany
    {
        return $this->hasMany(PaymentGateway::class, 'parent_gateway_id');
    }

    /**
     * Get the user credentials for this gateway
     */
    public function userCredentials(): HasMany
    {
        return $this->hasMany(UserGatewayCredential::class, 'gateway_id');
    }

    /**
     * Get transactions for this gateway
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'gateway_id');
    }

    /**
     * Get fees for this gateway
     */
    public function fees(): HasMany
    {
        return $this->hasMany(GatewayFee::class, 'gateway_id');
    }

    /**
     * Get the default gateway
     */
    public static function getDefault(): ?self
    {
        return self::where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get active gateways
     */
    public static function getActive()
    {
        return self::where('is_active', true)->get();
    }

    /**
     * Check if gateway supports payment method
     */
    public function supportsMethod(string $method): bool
    {
        return in_array($method, $this->supported_methods ?? []);
    }

    /**
     * Get configuration value
     */
    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }

    /**
     * Get full API URL for endpoint
     */
    public function getApiUrl(string $endpoint = ''): string
    {
        return rtrim($this->api_url, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * Get fee for specific payment method
     */
    public function getFeeForMethod(string $method): ?GatewayFee
    {
        return $this->fees()
            ->where('payment_method', $method)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all fees for this gateway
     */
    public function getAllFees(): array
    {
        $fees = $this->fees()
            ->where('is_active', true)
            ->get()
            ->keyBy('payment_method');

        $methods = ['pix', 'credit_card', 'bank_slip'];
        $result = [];

        foreach ($methods as $method) {
            if ($fees->has($method)) {
                $result[$method] = $fees->get($method);
            } else {
                // Create a default fee object
                $result[$method] = new GatewayFee(GatewayFee::getDefaultFees($method));
            }
        }

        return $result;
    }
    
    /**
     * Get transaction count for this gateway
     */
    public function getTransactionCount(): int
    {
        return $this->transactions()->count();
    }
    
    /**
     * Get user count for this gateway
     */
    public function getUserCount(): int
    {
        return User::where('assigned_gateway_id', $this->id)->count();
    }
    
    /**
     * Check if gateway can be deleted
     */
    public function canBeDeleted(): bool
    {
        // Can't delete if it's the default gateway
        if ($this->is_default) {
            return false;
        }
        
        // Can't delete if it has users assigned
        if ($this->getUserCount() > 0) {
            return false;
        }
        
        // Can't delete if it has transactions
        if ($this->getTransactionCount() > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if this gateway is a base acquirer
     */
    public function isBaseAcquirer(): bool
    {
        return $this->is_base === true;
    }

    /**
     * Check if this gateway is a whitelabel/sub-acquirer
     */
    public function isWhitelabel(): bool
    {
        return $this->is_whitelabel === true;
    }

    /**
     * Get webhook name for this gateway
     * If it's a whitelabel, use its webhook_name, otherwise use slug
     */
    public function getWebhookName(): string
    {
        if ($this->is_whitelabel && $this->webhook_name) {
            return $this->webhook_name;
        }
        
        return $this->slug;
    }

    /**
     * Get base acquirers only
     */
    public static function getBaseAcquirers()
    {
        return self::where('is_base', true)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get whitelabels for a specific base acquirer
     */
    public function getWhitelabels()
    {
        return $this->subGateways()
            ->where('is_whitelabel', true)
            ->where('is_active', true)
            ->get();
    }
}