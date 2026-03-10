<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserWebhook;
use App\Models\Transaction;
use App\Models\RetentionConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch webhook for transaction event
     */
    public function dispatchTransactionEvent(Transaction $transaction, string $event): void
    {
        // Skip webhooks for retained transactions
        if ($transaction->is_retained) {
            Log::info('Skipping webhook for retained transaction', [
                'transaction_id' => $transaction->id,
                'event' => $event
            ]);
            return;
        }
        
        $user = $transaction->user;
        
        if (!$user) {
            Log::error('User not found for transaction webhook', [
                'transaction_id' => $transaction->id,
                'event' => $event
            ]);
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
    }
    
    /**
     * Send webhook to a specific postback URL
     */
    public function sendToPostbackUrl(Transaction $transaction, string $event, string $postbackUrl): void
    {
        // Skip for retained transactions
        if ($transaction->is_retained) {
            Log::info('Skipping postback for retained transaction', [
                'transaction_id' => $transaction->id,
                'event' => $event
            ]);
            return;
        }
        
        // Prepare payload
        $payload = $this->prepareTransactionPayload($transaction, $event);
        
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
                    'status_code' => $response->status()
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
    protected function prepareTransactionPayload(Transaction $transaction, string $event): array
    {
        $payload = [
            'event' => $event,
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => $transaction->amount,
                'fee_amount' => $transaction->fee_amount,
                'net_amount' => $transaction->net_amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
                'status' => $transaction->status,
                'created_at' => $transaction->created_at->toIso8601String(),
                'updated_at' => $transaction->updated_at->toIso8601String(),
            ]
        ];
        
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
        
        return $payload;
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