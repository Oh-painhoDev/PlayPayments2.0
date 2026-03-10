<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthService
{
    /**
     * Create a new user
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Process referral code if provided
            $referrerId = null;
            if (!empty($data['referral_code'])) {
                $referrer = User::where('referral_code', $data['referral_code'])->first();
                if ($referrer) {
                    $referrerId = $referrer->id;
                }
            }
            
            // Gerar chaves API automaticamente
            $apiSecret = $this->generateSecureApiSecret();
            $apiPublicKey = $this->generateSecureApiPublicKey();
            
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'account_type' => $data['account_type'],
                'whatsapp' => $data['whatsapp'],
                'document' => $data['document'],
                'cep' => $data['cep'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'business_type' => $data['business_type'] ?? null,
                'business_sector' => $data['business_sector'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'terms_accepted' => $data['terms_accepted'] ?? false,
                'referrer_id' => $referrerId,
                'commission_percentage' => $data['commission_percentage'] ?? 1.00,
                'commission_fixed' => $data['commission_fixed'] ?? 0.00,
                'api_secret' => $apiSecret,
                'api_secret_created_at' => now(),
                'api_public_key' => $apiPublicKey,
                'api_public_key_created_at' => now(),
            ]);
        });
    }

    /**
     * Generate secure API Secret
     * Format: sk_ + 51 caracteres alfanuméricos (mantendo maiúsculas e minúsculas)
     */
    private function generateSecureApiSecret(): string
    {
        // Gerar 51 caracteres alfanuméricos aleatórios (mantendo maiúsculas e minúsculas)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 51;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'sk_' . $randomString;
    }

    /**
     * Generate secure API Public Key
     * Format: pk_ + 51 caracteres alfanuméricos (mantendo maiúsculas e minúsculas)
     */
    private function generateSecureApiPublicKey(): string
    {
        // Gerar 51 caracteres alfanuméricos aleatórios (mantendo maiúsculas e minúsculas)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 51;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'pk_' . $randomString;
    }

    /**
     * Update user profile
     */
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $user->update($data);
            return $user->fresh();
        });
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, string $newPassword): bool
    {
        return $user->update([
            'password' => Hash::make($newPassword)
        ]);
    }
}