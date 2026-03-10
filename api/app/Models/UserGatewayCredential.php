<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class UserGatewayCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gateway_id',
        'public_key',
        'secret_key',
        'is_active',
        'is_sandbox',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_sandbox' => 'boolean',
    ];

    /**
     * Get the user that owns the credentials
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gateway
     */
    public function gateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'gateway_id');
    }

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
            if (!$value || empty(trim($value))) {
                return null;
            }
            
            // Try to decrypt
            $decrypted = Crypt::decryptString($value);
            
            // If decrypted value is empty, return null
            if (empty(trim($decrypted))) {
                \Illuminate\Support\Facades\Log::warning('UserGatewayCredential: Secret key descriptografada está vazia', [
                    'credential_id' => $this->id,
                    'gateway_id' => $this->gateway_id,
                    'user_id' => $this->user_id,
                    'encrypted_length' => strlen($value),
                ]);
                return null;
            }
            
            return trim($decrypted);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // Erro de descriptografia - pode ser que a APP_KEY mudou ou a credencial está corrompida
            \Illuminate\Support\Facades\Log::error('UserGatewayCredential: Erro ao descriptografar secret key (APP_KEY pode ter mudado ou credencial corrompida)', [
                'credential_id' => $this->id,
                'gateway_id' => $this->gateway_id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'encrypted_length' => strlen($value),
                'solution' => 'Re-salve as credenciais no painel administrativo para re-criptografar com a APP_KEY atual',
            ]);
            return null;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('UserGatewayCredential: Erro ao descriptografar secret key', [
                'credential_id' => $this->id,
                'gateway_id' => $this->gateway_id,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'encrypted_length' => strlen($value),
            ]);
            return null;
        }
    }
    
    /**
     * Get raw secret key (encrypted) for debugging
     * Access the raw attribute value directly, bypassing the accessor
     */
    public function getRawSecretKey(): ?string
    {
        return $this->getAttributes()['secret_key'] ?? null;
    }

    /**
     * Get credentials for user and gateway
     */
    public static function getForUserAndGateway(int $userId, int $gatewayId): ?self
    {
        return self::where('user_id', $userId)
            ->where('gateway_id', $gatewayId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create or update credentials
     */
    public static function createOrUpdate(int $userId, int $gatewayId, array $data): self
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'gateway_id' => $gatewayId,
            ],
            $data
        );
    }
}