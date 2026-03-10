<?php

namespace App\Services\Gateways;

use App\Models\PaymentGateway;
use App\Models\UserGatewayCredential;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

abstract class BaseGatewayService
{
    protected PaymentGateway $gateway;
    protected ?UserGatewayCredential $credentials;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->credentials = $this->getCredentials();
    }

    /**
     * Get gateway credentials
     * First tries to get admin user credentials, then falls back to global credentials (user_id = null)
     */
    protected function getCredentials(): ?UserGatewayCredential
    {
        $adminUser = User::where('role', 'admin')->first();
        
        // Try to get admin user credentials first
        if ($adminUser) {
            $credentials = UserGatewayCredential::where('user_id', $adminUser->id)
                ->where('gateway_id', $this->gateway->id)
                ->where('is_active', true)
                ->first();
            
            if ($credentials) {
                // Refresh credentials to ensure we have the latest data
                $credentials->refresh();
                
                // Check if secret key can be decrypted
                $secretKeyDecrypted = null;
                try {
                    $secretKeyDecrypted = $credentials->secret_key;
                } catch (\Exception $e) {
                    Log::error('Gateway credentials: Error decrypting secret key', [
                        'gateway_id' => $this->gateway->id,
                        'gateway_name' => $this->gateway->name,
                        'credential_id' => $credentials->id,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue to try global credentials
                    $credentials = null;
                }
                
                if ($secretKeyDecrypted) {
                    // Log credentials status (without sensitive data)
                    Log::info('Gateway credentials found (admin user)', [
                        'gateway_id' => $this->gateway->id,
                        'gateway_name' => $this->gateway->name,
                        'credential_id' => $credentials->id,
                        'user_id' => $adminUser->id,
                        'has_public_key' => !empty($credentials->public_key),
                        'has_secret_key' => !empty($secretKeyDecrypted),
                        'public_key_length' => $credentials->public_key ? strlen($credentials->public_key) : 0,
                        'secret_key_length' => $secretKeyDecrypted ? strlen($secretKeyDecrypted) : 0,
                        'is_active' => $credentials->is_active,
                        'is_sandbox' => $credentials->is_sandbox ?? false,
                    ]);
                    return $credentials;
                }
            }
        }
        
        // Fallback to global credentials (user_id = null) - for global gateways
        $globalCredentials = UserGatewayCredential::whereNull('user_id')
            ->where('gateway_id', $this->gateway->id)
            ->where('is_active', true)
            ->first();
        
        if ($globalCredentials) {
            $globalCredentials->refresh();
            
            // Check if secret key can be decrypted
            $secretKeyDecrypted = null;
            try {
                $secretKeyDecrypted = $globalCredentials->secret_key;
            } catch (\Exception $e) {
                Log::error('Gateway credentials: Error decrypting global secret key', [
                    'gateway_id' => $this->gateway->id,
                    'gateway_name' => $this->gateway->name,
                    'credential_id' => $globalCredentials->id,
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
            
            if ($secretKeyDecrypted) {
                Log::info('Gateway credentials found (global)', [
                    'gateway_id' => $this->gateway->id,
                    'gateway_name' => $this->gateway->name,
                    'credential_id' => $globalCredentials->id,
                    'user_id' => null,
                    'has_public_key' => !empty($globalCredentials->public_key),
                    'has_secret_key' => !empty($secretKeyDecrypted),
                    'public_key_length' => $globalCredentials->public_key ? strlen($globalCredentials->public_key) : 0,
                    'secret_key_length' => $secretKeyDecrypted ? strlen($secretKeyDecrypted) : 0,
                    'is_active' => $globalCredentials->is_active,
                    'is_sandbox' => $globalCredentials->is_sandbox ?? false,
                ]);
                return $globalCredentials;
            }
        }
        
        // No credentials found
        Log::warning('Gateway credentials not found (neither admin nor global)', [
            'gateway_id' => $this->gateway->id,
            'gateway_name' => $this->gateway->name,
            'gateway_slug' => $this->gateway->slug ?? 'N/A',
            'admin_user_id' => $adminUser ? $adminUser->id : null,
        ]);
        
        return null;
    }

    /**
     * Check if gateway is properly configured
     */
    public function isConfigured(): bool
    {
        if ($this->credentials === null) {
            Log::debug('Gateway credentials are null in isConfigured', [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
            ]);
            return false;
        }
        
        // Refresh credentials to ensure we have the latest data
        try {
            $this->credentials->refresh();
        } catch (\Exception $e) {
            Log::error('Error refreshing gateway credentials in isConfigured', [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Check if credentials are active
        if (!$this->credentials->is_active) {
            Log::debug('Gateway credentials are inactive', [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
                'credential_id' => $this->credentials->id,
            ]);
            return false;
        }
        
        // Check if credentials have valid values
        $publicKey = $this->credentials->public_key;
        $secretKey = null;
        
        // Try to decrypt secret key
        try {
            $secretKey = $this->credentials->secret_key;
        } catch (\Exception $e) {
            Log::error('Error decrypting secret key in isConfigured', [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
                'credential_id' => $this->credentials->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        // For Pluggou, we need both public_key and secret_key
        if (empty($publicKey) || empty($secretKey)) {
            Log::warning('Gateway credentials are incomplete', [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
                'credential_id' => $this->credentials->id,
                'has_public_key' => !empty($publicKey),
                'has_secret_key' => !empty($secretKey),
                'public_key_length' => $publicKey ? strlen($publicKey) : 0,
                'secret_key_length' => $secretKey ? strlen($secretKey) : 0,
                'raw_secret_key_exists' => !empty($this->credentials->getRawSecretKey()),
            ]);
            return false;
        }
        
        return true;
    }

    /**
     * Abstract method that each gateway must implement
     */
    abstract public function createTransaction(User $user, array $data): array;

    /**
     * Dispatch webhook for transaction.created
     * This ensures ALL transactions trigger webhooks
     */
    protected function dispatchTransactionCreatedWebhook(Transaction $transaction): void
    {
        try {
            $webhookService = new \App\Services\WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.created');
        } catch (\Exception $e) {
            // Log error but don't fail transaction creation
            $this->logError('webhook_dispatch', 'Erro ao disparar webhook transaction.created: ' . $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
        
        // Enviar para UTMify se integração estiver ativa
        try {
            $utmifyService = new \App\Services\UtmifyService();
            $utmifyService->sendTransaction($transaction, 'created');
        } catch (\Exception $e) {
            // Log error but don't fail transaction creation
            $this->logError('utmify_dispatch', 'Erro ao enviar transação para UTMify (created): ' . $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
            ]);
        }
    }

    /**
     * Generate unique transaction ID
     * Format: VCP-YYYYMMDD-HHMMSS-XXXXX
     * Example: VCP-20251117-143052-A7B9C
     */
    protected function generateTransactionId(): string
    {
        $date = now();
        $datePart = $date->format('Ymd-His'); // YYYYMMDD-HHMMSS
        $randomPart = strtoupper(substr(md5(uniqid(rand(), true)), 0, 5)); // 5 caracteres aleatórios
        
        return "VCP-{$datePart}-{$randomPart}";
    }

    /**
     * Log gateway request
     */
    protected function logRequest(string $action, array $data, ?array $response = null): void
    {
        Log::info("Gateway {$this->gateway->name} - {$action}", [
            'gateway_id' => $this->gateway->id,
            'gateway_name' => $this->gateway->name,
            'action' => $action,
            'request_data' => $data,
            'response_data' => $response,
        ]);
    }

    /**
     * Log gateway error
     */
    protected function logError(string $action, string $error, array $context = []): void
    {
        Log::error("Gateway {$this->gateway->name} - {$action} Error", [
            'gateway_id' => $this->gateway->id,
            'gateway_name' => $this->gateway->name,
            'action' => $action,
            'error' => $error,
            'context' => $context,
        ]);
    }

    /**
     * Format customer document
     */
    protected function formatDocument(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }

    /**
     * Format phone number
     */
    protected function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove country code if present
        if (strlen($phone) > 11 && substr($phone, 0, 2) === '55') {
            $phone = substr($phone, 2);
        }
        
        return $phone;
    }

    /**
     * Calculate fee for transaction
     */
    protected function calculateFee(User $user, float $amount, string $paymentMethod, int $installments = 1): array
    {
        // Get user's custom fee or fallback to global fee
        $fee = $user->getFeeForMethod($paymentMethod);
        
        if (!$fee) {
            throw new \Exception("Taxa não configurada para o método: {$paymentMethod}");
        }

        // For credit card, get installment-specific fee
        if ($paymentMethod === 'credit_card' && $installments > 1) {
            $percentageFee = $fee->getInstallmentFee($installments);
        } else {
            $percentageFee = (float)$fee->percentage_fee;
        }

        $feeAmount = ($amount * $percentageFee / 100) + (float)$fee->fixed_fee;
        
        // Apply minimum fee
        if ($fee->min_amount && $feeAmount < $fee->min_amount) {
            $feeAmount = (float)$fee->min_amount;
        }
        
        // Apply maximum fee
        if ($fee->max_amount && $feeAmount > $fee->max_amount) {
            $feeAmount = (float)$fee->max_amount;
        }

        return [
            'fee_amount' => $feeAmount,
            'net_amount' => $amount - $feeAmount,
            'percentage_fee' => $percentageFee,
            'fixed_fee' => (float)$fee->fixed_fee,
        ];
    }
}