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
            ]);
        });
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