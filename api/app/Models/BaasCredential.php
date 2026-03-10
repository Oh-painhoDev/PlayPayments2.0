<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BaasCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'public_key',
        'secret_key',
        'is_active',
        'is_sandbox',
        'is_default',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
        'is_default' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Encrypt secret key when saving
     */
    public function setSecretKeyAttribute($value)
    {
        $this->attributes['secret_key'] = Crypt::encryptString($value);
    }

    /**
     * Decrypt secret key when retrieving
     */
    public function getSecretKeyAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get active credential for gateway
     */
    public static function getActiveForGateway(?string $gateway = null): ?self
    {
        if ($gateway) {
            return self::where('gateway', $gateway)
                ->where('is_active', true)
                ->first();
        }
        
        // If no gateway specified, get the active one
        return self::where('gateway', $gateway)
            ->where('is_active', true)
            ->first() ?? self::where('is_active', true)->first();
    }

    /**
     * Get default active BaaS
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)->first();
    }

    /**
     * Create or update credential
     */
    public static function createOrUpdate(string $gateway, array $data): self
    {
        return self::updateOrCreate(
            ['gateway' => $gateway],
            $data
        );
    }
    /**
     * Get next BaaS in round-robin sequence
     */
    public static function getNextRoundRobin(?int $lastUsedId = null): ?self
    {
        $activeBaas = self::where('is_active', true)->where('multi_baas_enabled', true)->get();
        
        if ($activeBaas->isEmpty()) {
            return self::where('is_active', true)->first();
        }
        
        if ($activeBaas->count() === 1) {
            return $activeBaas->first();
        }
        
        if (!$lastUsedId) {
            return $activeBaas->first();
        }
        
        $currentIndex = $activeBaas->search(function ($baas) use ($lastUsedId) {
            return $baas->id === $lastUsedId;
        });
        
        if ($currentIndex === false) {
            return $activeBaas->first();
        }
        
        $nextIndex = ($currentIndex + 1) % $activeBaas->count();
        return $activeBaas->get($nextIndex);
    }
    
    /**
     * Check if multi-BaaS is enabled
     */
    public static function isMultiBaasEnabled(): bool
    {
        return self::where('is_active', true)->where('multi_baas_enabled', true)->count() > 1;
    }
}
