<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'fantasy_name',
        'website',
        'email',
        'photo',
        'password',
        'account_type',
        'whatsapp',
        'document',
        'cep',
        'address',
        'city',
        'state',
        'business_type',
        'business_sector',
        'birth_date',
        'terms_accepted',
        'role',
        'assigned_gateway_id',
        'api_secret',
        'api_secret_created_at',
        'api_secret_last_used_at',
        'api_public_key',
        'api_public_key_created_at',
        'api_token',
        'withdrawal_type',
        'assigned_baas_id',
        'retry_gateway_id',
        'retry_enabled',
        'withdrawal_fee_fixed',
        'withdrawal_fee_percentage',
        'withdrawal_fee_type',
        'retention_cycle',
        'retention_quantity',
        'retention_enabled',
        'retention_type',
        'is_blocked',
        'blocked_at',
        'blocked_reason',
        'referrer_id',
        'referral_code',
        'commission_percentage',
        'commission_fixed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_secret',
        'api_public_key',
        'api_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'birth_date' => 'date',
            'terms_accepted' => 'boolean',
            'api_secret_created_at' => 'datetime',
            'api_secret_last_used_at' => 'datetime',
            'api_public_key_created_at' => 'datetime',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
            'retention_enabled' => 'boolean',
        ];
    }
    
    /**
     * Set the user's password (sempre fazer hash, mesmo que já seja hash)
     * Mas verificar se já é hash para evitar hash duplo
     */
    public function setPasswordAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['password'] = $value;
            return;
        }
        
        // Verificar se já é um hash bcrypt (começa com $2y$, $2a$ ou $2b$ e tem 60 caracteres)
        if (preg_match('/^\$2[ayb]\$\d{2}\$[.\/A-Za-z0-9]{53}$/', $value)) {
            // Já é um hash, usar diretamente
            $this->attributes['password'] = $value;
        } else {
            // Não é hash, fazer hash
            $this->attributes['password'] = Hash::make($value);
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate referral code when user is created
        static::creating(function ($user) {
            if (empty($user->referral_code)) {
                $user->referral_code = static::generateUniqueReferralCode();
            }
        });

        // Create wallet when user is created
        static::created(function ($user) {
            $wallet = $user->wallet()->create([
                'balance' => 0.00,
                'currency' => 'BRL',
                'is_active' => true,
            ]);
            
            // CRITICAL: Verify wallet was created with correct user_id
            if ($wallet->user_id !== $user->id) {
                Log::error('CRITICAL: Wallet created with wrong user_id!', [
                    'user_id' => $user->id,
                    'wallet_user_id' => $wallet->user_id,
                    'wallet_id' => $wallet->id
                ]);
                
                // Fix the wallet user_id immediately
                $wallet->update(['user_id' => $user->id]);
            }
        });
    }
    
    /**
     * Generate a unique referral code
     */
    public static function generateUniqueReferralCode(): string
    {
        do {
            // Generate a random code: 6-8 alphanumeric characters
            $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (static::where('referral_code', $code)->exists());
        
        return $code;
    }

    /**
     * Get the user's wallet.
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
    
    /**
     * Get the user who referred this user
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }
    
    /**
     * Get all users referred by this user
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }
    
    /**
     * Get all commissions earned as referrer
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(\App\Models\ReferralCommission::class, 'referrer_id');
    }

    /**
     * Get the document verification for the user.
     */
    public function documentVerification(): HasOne
    {
        return $this->hasOne(DocumentVerification::class);
    }

    /**
     * Get the assigned gateway for the user.
     */
    public function assignedGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'assigned_gateway_id');
    }

    /**
     * Get the assigned BaaS for the user.
     */
    public function assignedBaas(): BelongsTo
    {
        return $this->belongsTo(BaasCredential::class, 'assigned_baas_id');
    }

    /**
     * Get the retry gateway for the user.
     */
    public function retryGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'retry_gateway_id');
    }

    /**
     * Get the user's gateway credentials.
     */
    public function gatewayCredentials(): HasMany
    {
        return $this->hasMany(UserGatewayCredential::class);
    }

    /**
     * Get the user's transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the user's withdrawals.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Get the user's disputes.
     */
    public function disputes(): HasMany
    {
        return $this->hasMany(Dispute::class);
    }

    /**
     * Get the user's custom fees.
     */
    public function userFees(): HasMany
    {
        return $this->hasMany(UserFee::class);
    }

    public function pixKeys(): HasMany
    {
        return $this->hasMany(PixKey::class);
    }

    /**
     * Get the user's payment links.
     */
    public function paymentLinks(): HasMany
    {
        return $this->hasMany(PaymentLink::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager (gerente)
     */
    public function isManager(): bool
    {
        return $this->role === 'gerente';
    }

    /**
     * Check if user is admin or manager (has admin access)
     */
    public function isAdminOrManager(): bool
    {
        return in_array($this->role, ['admin', 'gerente']);
    }

    /**
     * Check if user is regular user
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked(): bool
    {
        return $this->is_blocked;
    }

    /**
     * Get fee for specific payment method (user custom or global)
     */
    public function getFeeForMethod(string $method)
    {
        // First check if user has custom fee
        $userFee = $this->userFees()
            ->where('payment_method', $method)
            ->where('is_active', true)
            ->first();

        if ($userFee) {
            return $userFee;
        }

        // Fallback to global fee
        return FeeConfiguration::getFeeForMethod($method);
    }

    /**
     * Get all fees for user (custom or global)
     */
    public function getAllFees(): array
    {
        $methods = ['pix', 'credit_card', 'bank_slip', 'withdrawal'];
        $fees = [];

        foreach ($methods as $method) {
            $fees[$method] = $this->getFeeForMethod($method);
        }

        return $fees;
    }

    /**
     * Check if user is pessoa física
     */
    public function isPessoaFisica(): bool
    {
        return $this->account_type === 'pessoa_fisica';
    }

    /**
     * Check if user is pessoa jurídica
     */
    public function isPessoaJuridica(): bool
    {
        return $this->account_type === 'pessoa_juridica';
    }

    /**
     * Check if user needs to upload documents
     */
    public function needsDocumentVerification(): bool
    {
        $verification = $this->documentVerification;
        
        if (!$verification) {
            return true; // Precisa criar verificação
        }

        if ($verification->isRejected()) {
            return true; // Precisa reenviar documentos
        }

        if ($verification->isPending() && !$verification->hasAllDocuments()) {
            return true; // Precisa completar documentos
        }

        return false; // Documentos aprovados ou pendentes completos
    }

    /**
     * Get document verification status
     */
    public function getDocumentStatus(): string
    {
        $verification = $this->documentVerification;
        
        if (!$verification) {
            return 'pendente';
        }

        return $verification->status;
    }

    /**
     * Check if user can modify documents
     */
    public function canModifyDocuments(): bool
    {
        $verification = $this->documentVerification;
        
        if (!$verification) {
            return true; // Pode criar/enviar documentos
        }

        // Não pode modificar se já foi aprovado
        if ($verification->isApproved()) {
            return false;
        }
        
        // Não pode modificar se já foi submetido e tem todos os documentos
        // A menos que tenha sido rejeitado
        if ($verification->submitted_at && $verification->hasAllDocuments() && !$verification->isRejected()) {
            return false;
        }

        return true; // Pode modificar em outros casos (incluindo quando foi rejeitado)
    }

    /**
     * Get formatted document (CPF/CNPJ)
     */
    public function getFormattedDocumentAttribute(): string
    {
        if (!$this->document) {
            return '';
        }

        if ($this->isPessoaFisica()) {
            // Format CPF: 000.000.000-00
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $this->document);
        } else {
            // Format CNPJ: 00.000.000/0000-00
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $this->document);
        }
    }

    /**
     * Get formatted CEP
     */
    public function getFormattedCepAttribute(): string
    {
        if (!$this->cep) {
            return '';
        }

        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $this->cep);
    }

    /**
     * Get formatted WhatsApp
     */
    public function getFormattedWhatsappAttribute(): string
    {
        if (!$this->whatsapp) {
            return '';
        }

        // Format: (00) 00000-0000
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $this->whatsapp);
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalanceAttribute(): float
    {
        return $this->wallet ? $this->wallet->balance : 0.00;
    }

    /**
     * Get formatted wallet balance
     */
    public function getFormattedWalletBalanceAttribute(): string
    {
        return 'R$ ' . number_format($this->wallet_balance, 2, ',', '.');
    }
    
    /**
     * Check if withdrawals are automatic
     */
    public function hasAutomaticWithdrawals(): bool
    {
        return $this->withdrawal_type === 'automatic';
    }

    /**
     * Calculate withdrawal fee for user
     * Fee is deducted from wallet balance, withdrawal sends full requested amount
     * 
     * @param float $requestedAmount Valor que o usuário quer receber
     * @param \App\Models\BaasCredential|null $baasGateway Gateway BaaS que será usado (opcional)
     * @return array
     */
    public function calculateWithdrawalFee(float $requestedAmount, ?\App\Models\BaasCredential $baasGateway = null): array
    {
        $userFeeAmount = 0.00;
        $baasFeeAmount = 0.00;
        
        // Calcular taxa do usuário
        if ($this->withdrawal_fee_type === 'global') {
            // Use global fee
            $userFeeAmount = \App\Helpers\FeeHelper::getWithdrawalFee();
        } elseif ($this->withdrawal_fee_type === 'fixed') {
            // Fixed fee only
            $userFeeAmount = $this->withdrawal_fee_fixed;
        } elseif ($this->withdrawal_fee_type === 'percentage') {
            // Percentage fee only
            $userFeeAmount = ($requestedAmount * $this->withdrawal_fee_percentage) / 100;
        } elseif ($this->withdrawal_fee_type === 'both') {
            // Both fixed + percentage
            $percentageFee = ($requestedAmount * $this->withdrawal_fee_percentage) / 100;
            $userFeeAmount = $this->withdrawal_fee_fixed + $percentageFee;
        }
        
        // Adicionar taxa do BaaS se fornecido (apenas para informação, não será cobrada do usuário)
        if ($baasGateway && $baasGateway->withdrawal_fee) {
            $baasFeeAmount = $baasGateway->withdrawal_fee;
        }
        
        // IMPORTANTE: A taxa do BaaS é absorvida pelo sistema, não é cobrada do usuário
        // Total a debitar = valor solicitado + apenas a taxa do usuário
        $totalToDebit = $requestedAmount + $userFeeAmount;
        
        return [
            'fee' => round($userFeeAmount, 2), // Taxa do usuário (o que ele paga)
            'baas_fee' => round($baasFeeAmount, 2), // Taxa do BaaS (absorvida pelo sistema)
            'total_fee' => round($userFeeAmount, 2), // Total de taxa cobrada do usuário (sem BaaS)
            'total_to_debit' => round($totalToDebit, 2), // Total a debitar do usuário (sem taxa BaaS)
            'net_amount' => round($requestedAmount, 2) // Valor que o usuário receberá
        ];
    }
    
    /**
     * Get formatted withdrawal fee display
     */
    public function getFormattedWithdrawalFee(): string
    {
        if ($this->withdrawal_fee_type === 'global') {
            $globalFee = \App\Helpers\FeeHelper::getWithdrawalFee();
            return 'R$ ' . number_format($globalFee, 2, ',', '.') . ' (Taxa Global)';
        } elseif ($this->withdrawal_fee_type === 'fixed') {
            return 'R$ ' . number_format($this->withdrawal_fee_fixed, 2, ',', '.');
        } elseif ($this->withdrawal_fee_type === 'percentage') {
            return number_format($this->withdrawal_fee_percentage, 2, ',', '.') . '%';
        } elseif ($this->withdrawal_fee_type === 'both') {
            return 'R$ ' . number_format($this->withdrawal_fee_fixed, 2, ',', '.') . ' + ' . 
                   number_format($this->withdrawal_fee_percentage, 2, ',', '.') . '%';
        }
        
        return 'Taxa não configurada';
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}