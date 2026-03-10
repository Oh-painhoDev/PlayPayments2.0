<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWebhook;
use App\Models\Transaction;
use App\Models\RetentionConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch webhook for transaction event
     */
    public function dispatchTransactionEvent(Transaction $transaction, string $event): void
    {
        // If transaction is retained, use retained transaction event dispatcher
        if ($transaction->is_retained) {
            $this->dispatchRetainedTransactionEvent($transaction, $event);
            return;
        }
        
        // CRITICAL: Add additional check to prevent duplicate webhook processing
        $cacheKey = "webhook_processed_{$transaction->id}_{$event}";
        if (Cache::has($cacheKey)) {
            Log::warning('Webhook already processed for this transaction and event', [
                'transaction_id' => $transaction->id,
                'event' => $event,
                'cache_key' => $cacheKey
            ]);
            return;
        }
        
        // Mark as processing to prevent duplicates (expires in 5 minutes)
        Cache::put($cacheKey, true, 300);
        
        $user = $transaction->user;
        
        if (!$user) {
            Log::error('User not found for transaction webhook', [
                'transaction_id' => $transaction->id,
                'transaction_user_id' => $transaction->user_id,
                'event' => $event
            ]);
            Cache::forget($cacheKey);
            return;
        }
        
        // Get all active webhooks for this user and event
        $webhooks = UserWebhook::where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($webhook) use ($event) {
                return $webhook->shouldTriggerForEvent($event);
            });
        
        if ($webhooks->isEmpty()) {
            Log::info('No active webhooks found for event', [
                'user_id' => $user->id,
                'event' => $event
            ]);
        } else {
            // Prepare payload
            $payload = $this->prepareTransactionPayload($transaction, $event);
            
            // Send to each webhook
            foreach ($webhooks as $webhook) {
                $this->sendWebhook($webhook, $payload);
            }
        }
        
        // Send to transaction's postbackUrl if it exists
        if (!empty($transaction->metadata['postbackUrl'])) {
            $this->sendToPostbackUrl($transaction, $event, $transaction->metadata['postbackUrl']);
        }
        
        Log::info('Webhook processing completed', [
            'transaction_id' => $transaction->id,
            'event' => $event,
            'webhooks_sent' => $webhooks->count(),
            'has_postback' => !empty($transaction->metadata['postbackUrl'])
        ]);
    }
    
    /**
     * Send webhook to a specific postback URL
     */
    public function sendToPostbackUrl(Transaction $transaction, string $event, string $postbackUrl): void
    {
        // For retained transactions, send with zero amount
        $zeroAmount = $transaction->is_retained;
        
        // Prepare payload
        $payload = $this->prepareTransactionPayload($transaction, $event, $zeroAmount);
        
        try {
            // Send request
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-Webhook/1.0'
                ])
                ->post($postbackUrl, $payload);
            
            if ($response->successful()) {
                Log::info('Postback enviado com sucesso', [
                    'url' => $postbackUrl,
                    'event' => $payload['event'],
                    'transaction_id' => $transaction->transaction_id,
                    'status_code' => $response->status(),
                    'is_retained' => $transaction->is_retained
                ]);
            } else {
                Log::warning('Falha ao enviar postback', [
                    'url' => $postbackUrl,
                    'event' => $payload['event'],
                    'transaction_id' => $transaction->transaction_id,
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar postback', [
                'url' => $postbackUrl,
                'event' => $payload['event'],
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Prepare transaction payload
     */
    protected function prepareTransactionPayload(Transaction $transaction, string $event, bool $zeroAmountForRetention = false): array
    {
        $payload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => $zeroAmountForRetention ? 0 : $transaction->amount,
                'fee_amount' => $zeroAmountForRetention ? 0 : $transaction->fee_amount,
                'net_amount' => $zeroAmountForRetention ? 0 : $transaction->net_amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->toIso8601String(),
                'updated_at' => $transaction->updated_at->toIso8601String(),
            ]
        ];
        
        // Se for retenção tipo 2, adiciona valor original apenas como referência
        if ($zeroAmountForRetention) {
            $payload['data']['original_amount'] = $transaction->amount;
            $payload['data']['retention_type'] = 2;
            $payload['data']['is_retained'] = true;
        }
        
        // Add paid_at if available
        if ($transaction->paid_at) {
            $payload['data']['paid_at'] = $transaction->paid_at->toIso8601String();
        }
        
        // Add refunded_at if available
        if ($transaction->refunded_at) {
            $payload['data']['refunded_at'] = $transaction->refunded_at->toIso8601String();
        }
        
        // Add customer data (excluding sensitive information)
        if ($transaction->customer_data) {
            $payload['data']['customer'] = [
                'name' => $transaction->customer_data['name'] ?? null,
                'email' => $transaction->customer_data['email'] ?? null,
            ];
        }
        
        // Add gateway information if available
        if ($transaction->gateway) {
            $payload['data']['gateway'] = [
                'name' => $transaction->gateway->name,
                'slug' => $transaction->gateway->slug,
            ];
        }
        
        return $payload;
    }
    
    /**
     * Dispatch webhook for retained transaction (Type 2)
     * Sends webhook with amount = 0 so external systems don't credit balance
     */
    public function dispatchRetainedTransactionEvent(Transaction $transaction, string $event): void
    {
        // CRITICAL: Add additional check to prevent duplicate webhook processing
        $cacheKey = "webhook_processed_{$transaction->id}_{$event}";
        if (Cache::has($cacheKey)) {
            Log::warning('Retained webhook already processed for this transaction and event', [
                'transaction_id' => $transaction->id,
                'event' => $event,
                'cache_key' => $cacheKey
            ]);
            return;
        }
        
        // Mark as processing to prevent duplicates (expires in 5 minutes)
        Cache::put($cacheKey, true, 300);
        
        $user = $transaction->user;
        
        if (!$user) {
            Log::error('User not found for retained transaction webhook', [
                'transaction_id' => $transaction->id,
                'event' => $event
            ]);
            Cache::forget($cacheKey);
            return;
        }
        
        // Get all active webhooks for this user and event
        $webhooks = UserWebhook::where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->filter(function ($webhook) use ($event) {
                return $webhook->shouldTriggerForEvent($event);
            });
        
        if ($webhooks->isEmpty()) {
            Log::info('No active webhooks found for retained transaction', [
                'user_id' => $user->id,
                'event' => $event
            ]);
        } else {
            // Prepare payload com valores zerados
            $payload = $this->prepareTransactionPayload($transaction, $event, true);
            
            // Send to each webhook
            foreach ($webhooks as $webhook) {
                $this->sendWebhook($webhook, $payload);
            }
        }
        
        // Send to transaction's postbackUrl if it exists (com valores zerados)
        if (!empty($transaction->metadata['postbackUrl'])) {
            $this->sendRetainedToPostbackUrl($transaction, $event, $transaction->metadata['postbackUrl']);
        }
        
        Log::info('Retained webhook (Type 2) processing completed', [
            'transaction_id' => $transaction->id,
            'event' => $event,
            'amount_sent' => 0,
            'original_amount' => $transaction->amount,
            'webhooks_sent' => $webhooks->count()
        ]);
    }
    
    /**
     * Send retained transaction webhook to postback URL with zero amount
     */
    public function sendRetainedToPostbackUrl(Transaction $transaction, string $event, string $postbackUrl): void
    {
        // Prepare payload com valores zerados
        $payload = $this->prepareTransactionPayload($transaction, $event, true);
        
        try {
            // Send request
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-Webhook/1.0'
                ])
                ->post($postbackUrl, $payload);
            
            if ($response->successful()) {
                Log::info('Retained postback enviado com sucesso (amount=0)', [
                    'url' => $postbackUrl,
                    'event' => $payload['event'],
                    'transaction_id' => $transaction->transaction_id,
                    'amount_sent' => 0,
                    'original_amount' => $transaction->amount,
                    'status_code' => $response->status()
                ]);
            } else {
                Log::warning('Falha ao enviar retained postback', [
                    'url' => $postbackUrl,
                    'event' => $payload['event'],
                    'transaction_id' => $transaction->transaction_id,
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar retained postback', [
                'url' => $postbackUrl,
                'event' => $payload['event'],
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send webhook to URL
     */
    protected function sendWebhook(UserWebhook $webhook, array $payload): void
    {
        try {
            // Generate signature
            $signature = $this->generateSignature($payload, $webhook->secret);
            
            // Send request
            $response = Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-Webhook/1.0',
                    'X-PixBolt-Signature' => $signature
                ])
                ->post($webhook->url, $payload);
            
            if ($response->successful()) {
                $webhook->recordSuccess();
                
                Log::info('Webhook enviado com sucesso', [
                    'webhook_id' => $webhook->id,
                    'url' => $webhook->url,
                    'event' => $payload['event'],
                    'status_code' => $response->status()
                ]);
            } else {
                $errorMessage = 'HTTP ' . $response->status() . ': ' . substr($response->body(), 0, 100);
                $webhook->recordFailure($errorMessage);
                
                Log::warning('Falha ao enviar webhook', [
                    'webhook_id' => $webhook->id,
                    'url' => $webhook->url,
                    'event' => $payload['event'],
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            $webhook->recordFailure($e->getMessage());
            
            Log::error('Erro ao enviar webhook', [
                'webhook_id' => $webhook->id,
                'url' => $webhook->url,
                'event' => $payload['event'],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Generate signature for webhook payload
     */
    protected function generateSignature(array $payload, string $secret): string
    {
        $payloadString = json_encode($payload);
        return hash_hmac('sha256', $payloadString, $secret);
    }
}