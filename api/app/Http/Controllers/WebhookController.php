<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\PaymentGateway;
use App\Services\WebhookService;
use App\Services\RetentionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WebhookController extends Controller
{
    protected $retentionService;

    public function __construct(RetentionService $retentionService)
    {
        $this->retentionService = $retentionService;
    }

    /**
     * Handle AvivHub webhook
     */
    public function avivhub(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug
            Log::info('AvivHub Webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validar dados mínimos necessários
            if (empty($data['externalRef']) && empty($data['externalCode'])) {
                Log::warning('Webhook AvivHub inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }

            if (!isset($data['status'])) {
                Log::warning('Webhook AvivHub inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }

            // Encontrar transação pelo nosso ID interno (externalRef ou externalCode)
            $externalRef = $data['externalRef'] ?? $data['externalCode'] ?? null;
            $transaction = Transaction::where('transaction_id', $externalRef)->first();

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook', [
                    'external_ref' => $externalRef,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Mapear status
            $newStatus = $this->mapAvivHubStatus($data['status']);
            $oldStatus = $transaction->status;

            Log::info('Status mapeado', [
                'original_status' => $data['status'],
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
                'refunded_at' => ($newStatus === 'refunded' || $newStatus === 'partially_refunded' || $newStatus === 'chargeback') ? now() : null,
            ];

            // Update external_id if provided and different
            if (isset($data['transactionId']) && $data['transactionId'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['transactionId'];
            }

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = now();
            }

            $transaction->update($updateData);

            // Process payment if paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                // Process for retention if applicable
                $this->retentionService->processTransaction($transaction);
                
                $user = $transaction->user;
                $retentionType = $user->retention_type ?? 1; // Default to Type 1
                
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processPayment($transaction);
                    Log::info('Pagamento processado com sucesso', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                } else {
                    Log::info('Transação retida, não processando pagamento para wallet', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount,
                        'retention_type' => $retentionType
                    ]);
                }
                // Sempre disparar webhook e UTMify (mesmo para retidas - será enviado com amount=0)
                $this->dispatchPaidWebhookAndUtmify($transaction);
            }

            // Process refund if refunded
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && $oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processRefund($transaction, $data);
                } else {
                    Log::info('Transação retida, não processando reembolso para wallet', [
                        'transaction_id' => $transaction->transaction_id,
                        'status' => $newStatus
                    ]);
                }
                // Sempre disparar webhook para reembolsos
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
            }

            // Process chargeback if chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processChargeback($transaction);
                } else {
                    Log::info('Transação retida, não processando chargeback para wallet', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para chargebacks
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
            }
            
            // Dispatch webhook event for transaction.failed (sempre dispara)
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            }
            
            // Dispatch webhook event for transaction.expired (sempre dispara)
            if ($newStatus === 'expired' && $oldStatus !== 'expired') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }

            Log::info('Webhook processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook AvivHub: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Hopy webhook
     */
    public function hopy(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug
            Log::info('Hopy Webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validar dados mínimos necessários
            if (empty($data['data']['metadata']) && empty($data['data']['externalRef'])) {
                Log::warning('Webhook Hopy inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }

            if (!isset($data['data']['status'])) {
                Log::warning('Webhook Hopy inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }

            // Encontrar transação pelo nosso ID interno (metadata ou externalRef)
            $externalRef = $data['data']['metadata'] ?? $data['data']['externalRef'] ?? null;
            $transaction = Transaction::where('transaction_id', $externalRef)->first();

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Hopy', [
                    'external_ref' => $externalRef,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Mapear status
            $newStatus = $this->mapHopyStatus($data['data']['status']);
            $oldStatus = $transaction->status;

            Log::info('Status mapeado Hopy', [
                'original_status' => $data['data']['status'],
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
            ];

            // Update external_id if provided and different
            if (isset($data['data']['id']) && $data['data']['id'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['data']['id'];
            }

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = now();
            }

            $transaction->update($updateData);

            // Process payment if paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                // Process for retention if applicable
                $this->retentionService->processTransaction($transaction);
                
                $user = $transaction->user;
                $retentionType = $user->retention_type ?? 1; // Default to Type 1
                
                if (!$transaction->is_retained) {
                    // NOT RETAINED: Process payment AND send webhook
                    $this->processPayment($transaction);
                    Log::info('Pagamento processado com sucesso (Hopy)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                    
                } else {
                    Log::info('Transação retida, não processando pagamento para wallet (Hopy)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount,
                        'retention_type' => $retentionType
                    ]);
                }
                // Sempre disparar webhook e UTMify (mesmo para retidas - será enviado com amount=0)
                $this->dispatchPaidWebhookAndUtmify($transaction);
            }

            // Process refund if refunded
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && $oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processRefund($transaction, $data['data']);
                } else {
                    Log::info('Transação retida, não processando reembolso para wallet (Hopy)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para reembolsos
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
            }

            // Process chargeback if chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processChargeback($transaction);
                } else {
                    Log::info('Transação retida, não processando chargeback para wallet (Hopy)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para chargebacks
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
            }
            
            // Dispatch webhook event for transaction.failed (sempre dispara)
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            }
            
            // Dispatch webhook event for transaction.expired (sempre dispara)
            if ($newStatus === 'expired' && $oldStatus !== 'expired') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }

            Log::info('Webhook Hopy processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Hopy: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Splitwave webhook
     */
    public function splitwave(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug
            Log::info('Splitwave Webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validar dados mínimos necessários
            if (empty($data['orderId']) && empty($data['code']) && empty($data['externalCode'])) {
                Log::warning('Webhook Splitwave inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }

            if (!isset($data['status'])) {
                Log::warning('Webhook Splitwave inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }

            // Encontrar transação pelo nosso ID interno (orderId)
            $externalRef = $data['orderId'] ?? $data['code'] ?? $data['externalCode'] ?? null;
            $transaction = Transaction::where('transaction_id', $externalRef)
                ->orWhere('external_id', $externalRef)
                ->first();

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Splitwave', [
                    'external_ref' => $externalRef,
                    'data' => $data,
                    'request_ip' => request()->ip(),
                    'request_headers' => request()->headers->all()
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Mapear status
            $newStatus = $this->mapSplitwaveStatus($data['status']);
            $oldStatus = $transaction->status;
            
            // Log detalhado para debug
            Log::info('Webhook Splitwave - Detalhes completos', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'webhook_data' => $data,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'is_retained' => $transaction->is_retained
            ]);

            Log::info('Status mapeado Splitwave', [
                'original_status' => $data['status'],
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
            ];

            // Update external_id if provided and different
            if (isset($data['code']) && $data['code'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['code'];
            }

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = now();
            }

            // Set refunded_at if status changed to refunded
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && 
                $oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded') {
                $updateData['refunded_at'] = now();
            }

            $transaction->update($updateData);

            // Process payment if paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                // Process for retention if applicable
                $this->retentionService->processTransaction($transaction);
                
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processPayment($transaction);
                    Log::info('Pagamento processado com sucesso (Splitwave)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                } else {
                    Log::info('Transação retida, não processando pagamento para wallet (Splitwave)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                }
                // Sempre disparar webhook e UTMify (mesmo para retidas)
                $this->dispatchPaidWebhookAndUtmify($transaction);
            }

            // Process refund if refunded
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && $oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processRefund($transaction, $data);
                } else {
                    Log::info('Transação retida, não processando reembolso para wallet (Splitwave)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para reembolsos
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
            }

            // Process chargeback if chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processChargeback($transaction);
                } else {
                    Log::info('Transação retida, não processando chargeback para wallet (Splitwave)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para chargebacks
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
            }
            
            // Dispatch webhook event for transaction.failed (sempre dispara)
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            }
            
            // Dispatch webhook event for transaction.expired (sempre dispara)
            if ($newStatus === 'expired' && $oldStatus !== 'expired') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }

            Log::info('Webhook Splitwave processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Splitwave: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Ocean webhook
     */
    public function ocean(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug
            Log::info('Ocean Webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validar dados mínimos necessários
            if (empty($data['metadata']['order_id'])) {
                Log::warning('Webhook Ocean inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }

            if (!isset($data['status'])) {
                Log::warning('Webhook Ocean inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }

            // Encontrar transação pelo nosso ID interno (order_id)
            $externalRef = $data['metadata']['order_id'];
            $transaction = Transaction::where('transaction_id', $externalRef)->first();

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Ocean', [
                    'external_ref' => $externalRef,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Mapear status
            $newStatus = $this->mapOceanStatus($data['status']);
            $oldStatus = $transaction->status;

            Log::info('Status mapeado Ocean', [
                'original_status' => $data['status'],
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
            ];

            // Update external_id if provided and different
            if (isset($data['transaction_id']) && $data['transaction_id'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['transaction_id'];
            }

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = now();
            }

            $transaction->update($updateData);

            // Process payment if paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                // Process for retention if applicable
                $this->retentionService->processTransaction($transaction);
                
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processPayment($transaction);
                    Log::info('Pagamento processado com sucesso (Ocean)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                } else {
                    Log::info('Transação retida, não processando pagamento para wallet (Ocean)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                }
                // Sempre disparar webhook e UTMify (mesmo para retidas)
                $this->dispatchPaidWebhookAndUtmify($transaction);
            }

            // Process refund if refunded
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && $oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processRefund($transaction, $data);
                } else {
                    Log::info('Transação retida, não processando reembolso para wallet (Ocean)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para reembolsos
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
            }

            // Process chargeback if chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processChargeback($transaction);
                } else {
                    Log::info('Transação retida, não processando chargeback para wallet (Ocean)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para chargebacks
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
            }
            
            // Dispatch webhook event for transaction.failed (sempre dispara)
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            }
            
            // Dispatch webhook event for transaction.expired (sempre dispara)
            if ($newStatus === 'expired' && $oldStatus !== 'expired') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }

            Log::info('Webhook Ocean processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Ocean: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle Cashtime webhook (PIX IN and PIX OUT)
     */
    public function cashtime(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug
            Log::info('Cashtime Webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Determinar se é PIX IN ou PIX OUT
            // PIX OUT tem withdrawStatusId, PIX IN tem code/orderId/externalCode
            $isPixOut = isset($data['withdrawStatusId']) || isset($data['pixKeyType']);
            
            if ($isPixOut) {
                return $this->handleCashtimePixOut($request, $data);
            } else {
                return $this->handleCashtimePixIn($request, $data);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Cashtime: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => true, 'message' => 'Webhook processado com erro: ' . $e->getMessage()], 200);
        }
    }

    /**
     * Handle Cashtime PIX IN webhook
     */
    private function handleCashtimePixIn(Request $request, array $data)
    {
        // Validar dados mínimos necessários para PIX IN
        if (empty($data['code']) && empty($data['externalCode']) && empty($data['orderId'])) {
            Log::warning('Webhook Cashtime PIX IN inválido - referência externa ausente', $data);
            return response()->json(['success' => true, 'message' => 'Webhook processado'], 200);
        }

        if (!isset($data['status'])) {
            Log::warning('Webhook Cashtime PIX IN inválido - status ausente', $data);
            return response()->json(['success' => true, 'message' => 'Webhook processado'], 200);
        }

        // Encontrar transação pelo nosso ID interno (code, externalCode ou orderId)
        $externalRef = $data['code'] ?? $data['externalCode'] ?? $data['orderId'] ?? null;
        $transaction = Transaction::where('transaction_id', $externalRef)
            ->orWhere('external_id', $externalRef)
            ->first();

        if (!$transaction) {
            Log::warning('Transação não encontrada para webhook Cashtime PIX IN', [
                'external_ref' => $externalRef,
                'data' => $data,
                'request_ip' => request()->ip(),
                'request_headers' => request()->headers->all()
            ]);
            return response()->json(['success' => true, 'message' => 'Webhook processado'], 200);
        }

        // Mapear status
        $newStatus = $this->mapCashtimePixInStatus($data['status']);
        $oldStatus = $transaction->status;

        Log::info('Status mapeado Cashtime PIX IN', [
            'original_status' => $data['status'],
            'mapped_status' => $newStatus,
            'old_status' => $oldStatus,
            'transaction_id' => $transaction->transaction_id,
            'data' => $data
        ]);

        // Update transaction
        $updateData = [
            'status' => $newStatus,
        ];

        // Update external_id if provided and different
        if (isset($data['code']) && $data['code'] !== $transaction->external_id) {
            $updateData['external_id'] = $data['code'];
        }

        // Set paid_at if status changed to paid
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            $updateData['paid_at'] = now();
        }

        // Set refunded_at if status is refunded or chargeback
        if (($newStatus === 'refunded' || $newStatus === 'partially_refunded' || $newStatus === 'chargeback') && 
            !in_array($oldStatus, ['refunded', 'partially_refunded', 'chargeback'])) {
            $updateData['refunded_at'] = now();
        }

        $transaction->update($updateData);

        // Handle payment if status is paid
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            if (!$transaction->is_retained) {
                // Credit user wallet
                $user = $transaction->user;
                $user->increment('balance', $transaction->net_amount);

                Log::info('Carteira creditada - Cashtime PIX IN', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->net_amount,
                    'new_balance' => $user->fresh()->balance
                ]);
            }

            // Dispatch webhook
            $webhookService = new \App\Services\WebhookService($transaction->user);
            $webhookService->dispatchWebhook('transaction.paid', $transaction);
        }

        // Handle refund/chargeback
        if (in_array($newStatus, ['refunded', 'partially_refunded', 'chargeback']) && 
            !in_array($oldStatus, ['refunded', 'partially_refunded', 'chargeback'])) {
            
            if ($oldStatus === 'paid') {
                $user = $transaction->user;
                $user->decrement('balance', $transaction->net_amount);

                Log::info('Carteira debitada por estorno - Cashtime PIX IN', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->net_amount,
                    'new_balance' => $user->fresh()->balance
                ]);
            }

            $webhookService = new \App\Services\WebhookService($transaction->user);
            $webhookService->dispatchWebhook('transaction.refunded', $transaction);
        }

        Log::info('Webhook Cashtime PIX IN processado com sucesso', [
            'transaction_id' => $transaction->transaction_id,
            'external_id' => $transaction->external_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Handle Cashtime PIX OUT webhook
     */
    private function handleCashtimePixOut(Request $request, array $data)
    {
        // Validar dados mínimos necessários
        if (empty($data['externalCode']) && empty($data['external_code']) && empty($data['externalRef']) && empty($data['id'])) {
            Log::warning('Webhook Cashtime PIX OUT inválido - referência externa ausente', $data);
            return response()->json(['success' => true, 'message' => 'Webhook processado'], 200);
        }

        if (!isset($data['withdrawStatusId']) && !isset($data['status'])) {
            Log::warning('Webhook Cashtime PIX OUT inválido - status ausente', $data);
            return response()->json(['success' => true, 'message' => 'Webhook processado'], 200);
        }

        // Encontrar saque pelo nosso ID interno (externalCode)
        $withdrawalId = $data['externalCode'] ?? $data['external_code'] ?? $data['externalRef'] ?? $data['id'] ?? null;
        $withdrawal = \App\Models\Withdrawal::where('withdrawal_id', $withdrawalId)->first();

        if (!$withdrawal) {
            Log::warning('Saque não encontrado para webhook Cashtime PIX OUT', [
                'withdrawal_id' => $withdrawalId,
                'data' => $data,
                'request_ip' => request()->ip(),
                'request_headers' => request()->headers->all()
            ]);
            return response()->json(['success' => true, 'message' => 'Webhook processado'], 200);
        }

        // Mapear status
        $status = $data['withdrawStatusId'] ?? $data['status'] ?? 'processing';
        $newStatus = $this->mapCashtimeStatus($status);
        $oldStatus = $withdrawal->status ?? 'pending';

        Log::info('Status mapeado Cashtime PIX OUT', [
            'original_status' => $status,
            'mapped_status' => $newStatus,
            'old_status' => $oldStatus,
            'withdrawal_id' => $withdrawal->withdrawal_id,
            'data' => $data
        ]);

        // Update withdrawal
        $updateData = [
            'status' => $newStatus,
            'webhook_data' => $data,
        ];

        // Update external_id if provided and different
        if (isset($data['id']) && $data['id'] !== $withdrawal->external_id) {
            $updateData['external_id'] = $data['id'];
        }

        // Set completed_at if status changed to completed
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $updateData['completed_at'] = now();
        }

        $withdrawal->update($updateData);

        // Handle status changes
        if ($newStatus !== $oldStatus) {
            $this->handleStatusChange($withdrawal, $oldStatus, $newStatus);
        }

        Log::info('Webhook Cashtime PIX OUT processado com sucesso', [
            'withdrawal_id' => $withdrawal->withdrawal_id,
            'external_id' => $withdrawal->external_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'data' => $data
        ]);

        return response()->json(['success' => true, 'message' => 'Webhook processado']);
    }
    
    /**
     * Handle Witetec/Witepay webhook
     */
    public function witetec(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug
            Log::info('Witetec/Witepay Webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Validar dados mínimos necessários
            if (empty($data['id']) && empty($data['items'])) {
                Log::warning('Webhook Witetec/Witepay inválido - ID da transação ausente', $data);
                return response()->json(['error' => 'Dados inválidos: ID da transação ausente'], 400);
            }

            if (!isset($data['status']) && !isset($data['eventType'])) {
                Log::warning('Webhook Witetec/Witepay inválido - status e eventType ausentes', $data);
                return response()->json(['error' => 'Dados inválidos: status e eventType ausentes'], 400);
            }

            // Determinar o ID da transação
            $transactionId = null;
            
            // Tentar obter o ID da transação de várias maneiras possíveis
            if (!empty($data['id'])) {
                $externalId = $data['id'];
                $transaction = Transaction::where('external_id', $externalId)->first();
                
                if (!$transaction && !empty($data['items'])) {
                    // Procurar pelo externalRef nos itens
                    foreach ($data['items'] as $item) {
                        if (!empty($item['externalRef'])) {
                            $transaction = Transaction::where('transaction_id', $item['externalRef'])->first();
                            if ($transaction) {
                                break;
                            }
                        }
                    }
                }
            }
            
            // Se ainda não encontrou, tente outros campos
            if (!$transaction && !empty($data['metadata']) && !empty($data['metadata']['order_id'])) {
                $transaction = Transaction::where('transaction_id', $data['metadata']['order_id'])->first();
            }
            
            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Witetec/Witepay', [
                    'data' => $data,
                    'request_ip' => request()->ip(),
                    'request_headers' => request()->headers->all()
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Determinar o status com base no eventType ou status
            $originalStatus = null;
            if (!empty($data['eventType'])) {
                // Extrair status do eventType (ex: TRANSACTION_PAID -> PAID)
                $parts = explode('_', $data['eventType']);
                $originalStatus = end($parts);
            } else if (!empty($data['status'])) {
                $originalStatus = $data['status'];
            }
            
            if (!$originalStatus) {
                Log::warning('Status não encontrado no webhook Witetec/Witepay', [
                    'data' => $data
                ]);
                return response()->json(['error' => 'Status não encontrado'], 400);
            }

            // Mapear status
            $newStatus = $this->mapWitetecStatus($originalStatus);
            $oldStatus = $transaction->status;

            Log::info('Status mapeado Witetec/Witepay', [
                'original_status' => $originalStatus,
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
                'refunded_at' => ($newStatus === 'refunded' || $newStatus === 'partially_refunded' || $newStatus === 'chargeback') ? now() : null,
            ];

            // Update external_id if provided and different
            if (isset($data['id']) && $data['id'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['id'];
            }

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = isset($data['paidAt']) ? 
                    \Carbon\Carbon::parse($data['paidAt']) : now();
            }

            $transaction->update($updateData);

            // Process payment if paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                // Process for retention if applicable
                $this->retentionService->processTransaction($transaction);
                
                // Only process payment and send webhook if not retained
                if (!$transaction->is_retained) {
                    $this->processPayment($transaction);
                    Log::info('Pagamento processado com sucesso (Witetec/Witepay)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                    
                    // Dispatch webhook event and UTMify for transaction.paid (sempre dispara, mesmo para retidas)
                    $this->dispatchPaidWebhookAndUtmify($transaction);
                } else {
                    Log::info('Transação retida, não processando pagamento para wallet (Witetec/Witepay)', [
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->amount
                    ]);
                    // Disparar webhook e UTMify mesmo para transações retidas
                    $this->dispatchPaidWebhookAndUtmify($transaction);
                }
            }

            // Process refund if refunded
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded') && 
                $oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processRefund($transaction, $data);
                } else {
                    Log::info('Transação retida, não processando reembolso para wallet (Witetec/Witepay)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para reembolsos
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
            }

            // Process chargeback if chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                // Process wallet only if not retained
                if (!$transaction->is_retained) {
                    $this->processChargeback($transaction);
                } else {
                    Log::info('Transação retida, não processando chargeback para wallet (Witetec/Witepay)', [
                        'transaction_id' => $transaction->transaction_id
                    ]);
                }
                // Sempre disparar webhook para chargebacks
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
            }
            
            // Dispatch webhook event for transaction.failed (sempre dispara)
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            }
            
            // Dispatch webhook event for transaction.expired (sempre dispara)
            if ($newStatus === 'expired' && $oldStatus !== 'expired') {
                $webhookService = new WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }

            Log::info('Webhook Witetec/Witepay processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Witetec/Witepay: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Sharkgateway webhook
     */
    public function sharkgateway(Request $request)
    {
        try {
            // Log all received data for debugging
            Log::info('Webhook Sharkgateway recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            $data = $request->all();
            
            // Validate webhook data structure
            if (!isset($data['data']) || !isset($data['data']['id'])) {
                Log::warning('Webhook Sharkgateway inválido - estrutura incorreta', $data);
                return response()->json(['error' => 'Dados inválidos'], 400);
            }
            
            $transactionData = $data['data'];
            $gatewayTransactionId = $transactionData['id'];
            $status = $transactionData['status'] ?? 'waiting_payment';
            
            // Try to find transaction by multiple methods
            $transaction = null;
            
            // 1. Try by external_id (gateway transaction ID)
            $transaction = Transaction::where('external_id', $gatewayTransactionId)->first();
            
            // 2. If not found, try by external_id as string
            if (!$transaction) {
                $transaction = Transaction::where('external_id', (string)$gatewayTransactionId)->first();
            }
            
            // 3. If not found, try by our internal transaction_id if it matches
            if (!$transaction && isset($transactionData['externalRef']) && !empty($transactionData['externalRef'])) {
                $transaction = Transaction::where('transaction_id', $transactionData['externalRef'])->first();
            }
            
            // 4. If not found, try by metadata or other identifiers
            if (!$transaction && isset($transactionData['metadata']) && !empty($transactionData['metadata'])) {
                $transaction = Transaction::where('transaction_id', $transactionData['metadata'])->first();
            }
            
            // 5. If still not found, try to find by customer email and amount (last resort)
            if (!$transaction && isset($transactionData['customer']['email']) && isset($transactionData['amount'])) {
                $amount = $transactionData['amount'] / 100; // Convert from cents
                $email = $transactionData['customer']['email'];
                
                $transaction = Transaction::whereJsonContains('customer_data->email', $email)
                    ->where('amount', $amount)
                    ->where('payment_method', 'pix')
                    ->whereIn('status', ['pending', 'processing'])
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            // 6. If still not found, try by amount and recent transactions
            if (!$transaction && isset($transactionData['amount'])) {
                $amount = $transactionData['amount'] / 100; // Convert from cents
                
                $transaction = Transaction::where('amount', $amount)
                    ->where('payment_method', 'pix')
                    ->whereIn('status', ['pending', 'processing'])
                    ->where('created_at', '>=', now()->subHours(24)) // Last 24 hours
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Sharkgateway', [
                    'gateway_transaction_id' => $gatewayTransactionId,
                    'data' => $data,
                    'search_attempts' => [
                        'external_ref' => $transactionData['externalRef'] ?? 'not_provided',
                        'metadata' => $transactionData['metadata'] ?? 'not_provided',
                        'customer_email' => $transactionData['customer']['email'] ?? 'not_provided',
                        'amount' => isset($transactionData['amount']) ? ($transactionData['amount'] / 100) : 'not_provided'
                    ]
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }
            
            // Check for refunds first (refundedAmount or refunds array)
            $refundedAmount = $transactionData['refundedAmount'] ?? 0;
            $refundsArray = $transactionData['refunds'] ?? [];
            
            // Calculate total refunded amount from refunds array if refundedAmount is not available
            if ($refundedAmount == 0 && !empty($refundsArray) && is_array($refundsArray)) {
                foreach ($refundsArray as $refund) {
                    if (isset($refund['amount'])) {
                        $refundedAmount += (int)$refund['amount'];
                    } elseif (isset($refund['value'])) {
                        $refundedAmount += (int)$refund['value'];
                    }
                }
            }
            
            $hasRefunds = ($refundedAmount > 0) || (!empty($refundsArray) && is_array($refundsArray));
            
            // Map status
            $newStatus = $this->mapSharkgatewayStatus($status);
            $oldStatus = $transaction->status;
            
            // Override status if there are refunds
            if ($hasRefunds) {
                // Check if it's a partial or full refund
                $transactionAmount = $transactionData['amount'] ?? ($transaction->amount * 100); // Amount in cents
                if ($refundedAmount >= $transactionAmount) {
                    $newStatus = 'refunded';
                } else {
                    $newStatus = 'partially_refunded';
                }
            }
            
            Log::info('Status Sharkgateway mapeado', [
                'original_status' => $status,
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id,
                'refundedAmount' => $refundedAmount,
                'refunds_count' => count($refundsArray),
                'hasRefunds' => $hasRefunds,
            ]);
            
            // Update transaction
            $transaction->status = $newStatus;
            
            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $transaction->paid_at = isset($transactionData['paidAt']) ? 
                    Carbon::parse($transactionData['paidAt']) : now();
            }
            
            // Set refunded_at if status is refunded, partially_refunded, or chargeback
            if (($newStatus === 'refunded' || $newStatus === 'partially_refunded' || $newStatus === 'chargeback') && 
                ($oldStatus !== 'refunded' && $oldStatus !== 'partially_refunded' && $oldStatus !== 'chargeback')) {
                
                // Try to get refundedAt from refunds array (most recent refund)
                $refundedAt = null;
                if (!empty($refundsArray) && is_array($refundsArray)) {
                    // Get the most recent refund
                    $latestRefund = end($refundsArray);
                    if (isset($latestRefund['createdAt']) || isset($latestRefund['created_at'])) {
                        try {
                            $refundedAt = Carbon::parse($latestRefund['createdAt'] ?? $latestRefund['created_at']);
                        } catch (\Exception $e) {
                            Log::warning('Erro ao parsear createdAt do refund', [
                                'refund' => $latestRefund,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
                
                // If not found in refunds array, check refundedAt, updatedAt, or use now
                if (!$refundedAt) {
                    if (isset($transactionData['refundedAt']) && !empty($transactionData['refundedAt'])) {
                        try {
                            $refundedAt = Carbon::parse($transactionData['refundedAt']);
                        } catch (\Exception $e) {
                            Log::warning('Erro ao parsear refundedAt', [
                                'refundedAt' => $transactionData['refundedAt'],
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
                
                if (!$refundedAt) {
                    if (isset($transactionData['updatedAt']) && !empty($transactionData['updatedAt'])) {
                        try {
                            $refundedAt = Carbon::parse($transactionData['updatedAt']);
                        } catch (\Exception $e) {
                            Log::warning('Erro ao parsear updatedAt', [
                                'updatedAt' => $transactionData['updatedAt'],
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
                
                $transaction->refunded_at = $refundedAt ?? now();
            }
            
            $transaction->save();
            
            // Handle status changes
            if ($newStatus !== $oldStatus) {
                $this->handleStatusChange($transaction, $oldStatus, $newStatus);
            }
            
            Log::info('Webhook Sharkgateway processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Sharkgateway: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handle Arkama webhook
     */
    public function arkama(Request $request)
    {
        try {
            // Log all received data for debugging
            Log::info('Webhook da Arkama recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            $data = $request->all();
            
            // Validate minimum required data for Arkama
            if (!isset($data['data']['order']['externalRef'])) {
                Log::warning('Webhook da Arkama inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }
            
            if (!isset($data['data']['order']['status'])) {
                Log::warning('Webhook da Arkama inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }
            
            // Extract data from Arkama structure
            $externalRef = $data['data']['order']['externalRef'];
            $arkamaStatus = $data['data']['order']['status'];
            $arkamaOrderId = $data['data']['order']['id'] ?? null;
            $paidAt = $data['data']['order']['paid_at'] ?? null;
            
            // Find transaction by our internal ID
            $transaction = Transaction::where('transaction_id', $externalRef)->first();
            
            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook da Arkama', [
                    'external_ref' => $externalRef,
                    'arkama_order_id' => $arkamaOrderId,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }
            
            // Map Arkama status to our status
            $newStatus = $this->mapArkamaStatus($arkamaStatus);
            $oldStatus = $transaction->status;
            
            Log::info('Status da Arkama mapeado', [
                'arkama_status' => $arkamaStatus,
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id,
                'arkama_order_id' => $arkamaOrderId
            ]);
            
            // Update transaction
            $updateData = [
                'status' => $newStatus,
            ];
            
            // Update external_id if provided and different
            if ($arkamaOrderId && $arkamaOrderId !== $transaction->external_id) {
                $updateData['external_id'] = $arkamaOrderId;
            }
            
            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid' && $paidAt) {
                $updateData['paid_at'] = \Carbon\Carbon::parse($paidAt);
            }
            
            $transaction->update($updateData);
            
            // Handle status changes
            if ($newStatus !== $oldStatus) {
                $this->handleStatusChange($transaction, $oldStatus, $newStatus, [
                    'arkama_order_id' => $arkamaOrderId,
                ]);
            }
            
            Log::info('Webhook da Arkama processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'arkama_order_id' => $arkamaOrderId,
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook da Arkama: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Map Arkama status to our status
     */
    protected function mapArkamaStatus(string $status): string
    {
        return match(strtoupper($status)) {
            'PENDING' => 'pending',
            'PROCESSING' => 'processing',
            'IN_ANALYSIS' => 'processing',
            'IN_DISPUTE' => 'processing',
            'PAID', 'PAID_OUT', 'COMPLETED', 'SUCCESS' => 'paid',
            'CANCELED' => 'cancelled',
            'REFUSED' => 'failed',
            'CHARGEDBACK' => 'chargeback',
            'PRE_CHARGEDBACK' => 'chargeback',
            'REFUNDED' => 'refunded',
            default => 'pending',
        };
    }
    
    /**
     * Map Sharkgateway status to our status
     */
    /**
     * Dispara webhook e envia para UTMify quando necessário
     */
    protected function dispatchPaidWebhookAndUtmify(Transaction $transaction): void
    {
        // Disparar webhook
        $webhookService = new WebhookService();
        $webhookService->dispatchTransactionEvent($transaction, 'transaction.paid');
        
        // Enviar para UTMify se integração estiver ativa
        try {
            $utmifyService = new \App\Services\UtmifyService();
            $utmifyService->sendTransaction($transaction, 'paid');
        } catch (\Exception $e) {
            Log::error('Erro ao enviar transação para UTMify (paid)', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage(),
            ]);
        }
    }
    
    protected function mapSharkgatewayStatus(string $status): string
    {
        return match(strtolower($status)) {
            'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }
    
    /**
     * Map Witetec/Witepay status to our status
     */
    protected function mapWitetecStatus(string $status): string
    {
        return match(strtoupper($status)) {
            'PENDING', 'WAITING_PAYMENT', 'WAITING' => 'pending',
            'PROCESSING' => 'processing',
            'AUTHORIZED' => 'authorized',
            'PAID', 'APPROVED', 'SUCCESS', 'COMPLETED' => 'paid',
            'CANCELLED', 'CANCELED', 'CANCEL' => 'cancelled',
            'EXPIRED' => 'expired',
            'FAILED', 'REFUSED', 'ERROR' => 'failed',
            'REFUNDED', 'REFOUND' => 'refunded',
            'PARTIALLY_REFUNDED' => 'partially_refunded',
            'CHARGEBACK', 'CHARGEDBACK', 'DISPUTE' => 'chargeback',
            default => 'pending',
        };
    }
    
    /**
     * Map Cashtime status to our status
     */
    protected function mapCashtimeStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pendingprocessing', 'awaitingaprove', 'pending' => 'pending',
            'processing', 'process' => 'processing',
            'completed', 'success', 'successfull' => 'completed',
            'cancelled', 'canceled', 'cancel' => 'cancelled',
            'failed', 'error', 'failure' => 'failed',
            default => 'processing',
        };
    }

    /**
     * Map Cashtime PIX IN status to our status
     */
    protected function mapCashtimePixInStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending', 'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success' => 'paid',
            'refused', 'failed', 'error' => 'failed',
            'refunded' => 'refunded',
            'infraction' => 'chargeback',
            default => 'pending',
        };
    }
    
    /**
     * Handle Versell webhook
     */
    public function versell(Request $request)
    {
        try {
            // Log all received data for debugging
            Log::info('Webhook Versell recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            $data = $request->all();
            
            // Validate minimum required data
            if (empty($data['requestNumber'])) {
                Log::warning('Webhook Versell inválido - requestNumber ausente', $data);
                return response()->json(['error' => 'Dados inválidos: requestNumber ausente'], 400);
            }
            
            if (!isset($data['statusTransaction'])) {
                Log::warning('Webhook Versell inválido - statusTransaction ausente', $data);
                return response()->json(['error' => 'Dados inválidos: statusTransaction ausente'], 400);
            }
            
            // Find transaction by our internal ID (requestNumber)
            $transactionId = $data['requestNumber'];
            $transaction = Transaction::where('transaction_id', $transactionId)->first();
            
            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Versell', [
                    'transaction_id' => $transactionId,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }
            
            // Map status
            $newStatus = $this->mapVersellStatus($data['statusTransaction']);
            $oldStatus = $transaction->status;
            
            Log::info('Status de transação Versell mapeado', [
                'original_status' => $data['statusTransaction'],
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);
            
            // Update transaction
            $updateData = [
                'status' => $newStatus,
            ];
            
            // Update external_id if provided and different
            if (isset($data['idTransaction']) && $data['idTransaction'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['idTransaction'];
            }
            
            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = now();
            }
            
            // Set refunded_at if status changed to chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                $updateData['refunded_at'] = now();
            }
            
            $transaction->update($updateData);
            
            // Process retention if transaction is paid and not already retained
            if ($transaction->status === 'paid' && !$transaction->is_retained) {
                $retentionService = new RetentionService();
                $processed = $retentionService->processTransaction($transaction);
                
                Log::info('Retention processing result for Versell transaction', [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'processed' => $processed,
                    'is_retained' => $transaction->fresh()->is_retained,
                    'is_counted_in_cycle' => $transaction->fresh()->is_counted_in_cycle
                ]);
            }
            
            // Process wallet only if not retained
            if (!$transaction->fresh()->is_retained) {
                $this->processPayment($transaction);
            } else {
                Log::info('Transação retida, não processando pagamento para wallet (Versell)', [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount
                ]);
            }
            
            // Handle status changes (sempre dispara webhooks, mesmo para retidas)
            if ($newStatus !== $oldStatus) {
                $this->handleStatusChange($transaction, $oldStatus, $newStatus);
            }
            
            Log::info('Webhook Versell processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Versell: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Map Versell status to our status
     */
    protected function mapVersellStatus(string $gatewayStatus): string
    {
        return match(strtoupper($gatewayStatus)) {
            'PAID_OUT' => 'paid',
            'CHARGEBACK' => 'chargeback',
            'PENDING' => 'pending',
            'CANCELLED', 'CANCELED' => 'cancelled',
            'EXPIRED' => 'expired',
            'FAILED', 'REFUSED', 'ERROR' => 'failed',
            'REFUNDED', 'REFUND' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Map AvivHub status to our status
     */
    protected function mapAvivHubStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending', 'waiting_payment' => 'pending',
            'processing', 'analyzing' => 'processing',
            'authorized' => 'authorized',
            'paid', 'approved' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused' => 'failed',
            'refunded' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }

    /**
     * Map Hopy status to our status
     */
    protected function mapHopyStatus(string $status): string
    {
        return match(strtolower($status)) {
            'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }

    /**
     * Map Splitwave status to our status
     */
    protected function mapSplitwaveStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending', 'waiting_payment', 'waiting', 'created', 'new' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed', 'payment_confirmed', 'confirmed', 'approved_by_risk', 'captured' => 'paid',
            'cancelled', 'canceled', 'cancel', 'cancellation' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error', 'payment_failed', 'declined', 'rejected', 'denied' => 'failed',
            'refunded', 'refound', 'refund', 'reimbursed', 'reimbursement' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback', 'charge_back', 'chargedback', 'charged_back' => 'chargeback',
            default => 'pending',
        };
    }

    /**
     * Map Ocean status to our status
     */
    protected function mapOceanStatus(string $status): string
    {
        return match(strtolower($status)) {
            'pending', 'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }

    /**
     * Handle MediusPag webhook
     */
    public function mediuspag(Request $request)
    {
        try {
            // Log all received data for debugging
            Log::info('Webhook MediusPag recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);

            $data = $request->all();

            // Extract externalRef from items array
            $externalRef = null;
            if (isset($data['data']['items']) && is_array($data['data']['items'])) {
                foreach ($data['data']['items'] as $item) {
                    if (isset($item['externalRef']) && !empty($item['externalRef'])) {
                        $externalRef = $item['externalRef'];
                        break;
                    }
                }
            }
            
            // Validate minimum required data
            if (empty($externalRef)) {
                Log::warning('Webhook MediusPag inválido - referência externa ausente', $data);
                return response()->json(['error' => 'Dados inválidos: referência externa ausente'], 400);
            }

            // Extract status from data.data.status
            $status = $data['data']['status'] ?? null;
            
            if (!$status) {
                Log::warning('Webhook MediusPag inválido - status ausente', $data);
                return response()->json(['error' => 'Dados inválidos: status ausente'], 400);
            }

            // Find transaction by our internal ID
            $transaction = Transaction::where('transaction_id', $externalRef)->first();

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook MediusPag', [
                    'external_ref' => $externalRef,
                    'data' => $data
                ]);
                return response()->json(['error' => 'Transação não encontrada'], 404);
            }

            // Map status
            $newStatus = $this->mapMediusPagStatus($status);
            $oldStatus = $transaction->status;

            Log::info('Status de transação MediusPag mapeado', [
                'original_status' => $status,
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'transaction_id' => $transaction->transaction_id
            ]);

            // Update transaction
            $updateData = [
                'status' => $newStatus,
            ];

            // Update external_id if provided and different
            if (isset($data['id']) && $data['id'] !== $transaction->external_id) {
                $updateData['external_id'] = $data['id'];
            }

            // Set paid_at if status changed to paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                $updateData['paid_at'] = isset($data['paidAt']) ? 
                    Carbon::parse($data['paidAt']) : now();
            }

            // Set refunded_at if status is refunded
            if (in_array($newStatus, ['refunded', 'partially_refunded', 'chargeback']) && 
                !in_array($oldStatus, ['refunded', 'partially_refunded', 'chargeback'])) {
                $updateData['refunded_at'] = now();
            }

            $transaction->update($updateData);

            // Handle status changes
            if ($newStatus !== $oldStatus) {
                $this->handleStatusChange($transaction, $oldStatus, $newStatus);
            }

            Log::info('Webhook MediusPag processado com sucesso', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook MediusPag: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Map MediusPag status to our status
     */
    protected function mapMediusPagStatus(string $status): string
    {
        return match(strtolower($status)) {
            'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }
    
    /**
     * Handle GetPay webhook
     */
    public function getpay(Request $request)
    {
        try {
            // Log all received data for debugging
            Log::info('GetPay webhook recebido', [
                'method' => $request->method(),
                'data' => $request->all(),
                'headers' => $request->header(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            
            $data = $request->all();
            
            // Validate minimum required data
            if (empty($data['externalId'])) {
                Log::warning('GetPay webhook inválido - externalId ausente', $data);
                return response()->json(['error' => 'Dados inválidos: externalId ausente'], 400);
            }
            
            // Get signature from header
            $receivedSignature = $request->header('x-signature');
            $webhookSecret = 'qgAC5lmcwzufD1CzJGTeLXPlxzUg685Bivwqv5QiaHXP7pnyL8WNAzuvBPJK48ai';
            
            // Verify signature if present (but don't block processing if it fails)
            $signatureValid = false;
            if ($receivedSignature) {
                $rawPayload = $request->getContent();
                $expectedSignature = hash_hmac('sha256', $rawPayload, $webhookSecret);
                
                // Remove sha256= prefix if present
                $cleanReceivedSignature = str_replace('sha256=', '', $receivedSignature);
                
                $signatureValid = hash_equals($expectedSignature, $cleanReceivedSignature);
                
                if (!$signatureValid) {
                    Log::warning('GetPay webhook signature verification failed', [
                        'received_signature' => $cleanReceivedSignature,
                        'expected_signature' => $expectedSignature,
                        'raw_payload_length' => strlen($rawPayload),
                        'webhook_secret_length' => strlen($webhookSecret)
                    ]);
                }
            }
            
            // Find transaction by external_id or transaction_id (multiple search strategies)
            $externalId = $data['externalId'];
            
            // Strategy 1: Search by external_id
            $transaction = Transaction::where('external_id', $externalId)->first();
            
            // Strategy 2: Search by transaction_id if not found
            if (!$transaction) {
                $transaction = Transaction::where('transaction_id', $externalId)->first();
            }
            
            // Strategy 3: Search by partial match if still not found
            if (!$transaction) {
                // Try to find by partial external_id match
                $transaction = Transaction::where('external_id', 'like', "%{$externalId}%")
                    ->orWhere('transaction_id', 'like', "%{$externalId}%")
                    ->first();
            }
            
            // Strategy 4: Search by UUID if present
            if (!$transaction && isset($data['uuid'])) {
                $uuid = $data['uuid'];
                $transaction = Transaction::where('external_id', 'like', "%{$uuid}%")
                    ->orWhere('transaction_id', 'like', "%{$uuid}%")
                    ->orWhereJsonContains('metadata->uuid', $uuid)
                    ->first();
            }
            
            if (!$transaction) {
                // Log all transactions for debugging
                $recentTransactions = Transaction::orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(['id', 'transaction_id', 'external_id', 'created_at', 'status'])
                    ->toArray();
                
                Log::error('Transação não encontrada para GetPay webhook - PROCESSANDO MESMO ASSIM', [
                    'external_id' => $externalId,
                    'uuid' => $data['uuid'] ?? null,
                    'data' => $data,
                    'recent_transactions' => $recentTransactions
                ]);
                
                // Don't return error - continue processing for debugging
                // return response()->json(['error' => 'Transação não encontrada'], 404);
            }
            
            // Only process if transaction was found
            if ($transaction) {
                // Map status from the 'status' field (prioritize over 'type')
                $newStatus = $this->mapGetPayStatus($data['status'] ?? $data['type']);
                $oldStatus = $transaction->status;
                
                Log::info('GetPay status mapeado', [
                    'external_id' => $externalId,
                    'original_status' => $data['status'] ?? $data['type'],
                    'mapped_status' => $newStatus,
                    'old_status' => $oldStatus,
                    'transaction_id' => $transaction->transaction_id
                ]);
                
                // Update transaction
                $updateData = [
                    'status' => $newStatus,
                    'webhook_data' => $data,
                ];
                
                // Set paid_at if status changed to paid
                if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                    $updateData['paid_at'] = now();
                }
                
                // Set refunded_at if status changed to refunded
                if ($newStatus === 'refunded' && $oldStatus !== 'refunded') {
                    $updateData['refunded_at'] = now();
                }
                
                $transaction->update($updateData);
                
                // Handle status changes
                if ($newStatus !== $oldStatus) {
                    $this->handleGetPayStatusChange($transaction, $oldStatus, $newStatus);
                }
                
                Log::info('GetPay webhook processado com sucesso', [
                    'external_id' => $externalId,
                    'transaction_id' => $transaction->transaction_id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'signature_valid' => $signatureValid
                ]);
            } else {
                Log::error('TRANSAÇÃO NÃO ENCONTRADA - Verifique se o external_id está sendo salvo corretamente', [
                    'searched_external_id' => $externalId,
                    'webhook_data' => $data
                ]);
            }
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar GetPay webhook: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Map GetPay status to our transaction status
     */
    protected function mapGetPayStatus(string $status, string $type = ''): string
    {
        // First check the status field (priority)
        if ($status === 'paid') {
            return 'paid';
        }
        
        if ($status === 'refunded') {
            return 'refunded';
        }
        
        if ($status === 'failed') {
            return 'failed';
        }
        
        if ($status === 'cancelled') {
            return 'cancelled';
        }
        
        if ($status === 'pending') {
            return 'pending';
        }
        
        // Fallback to type field
        if ($type === 'PAYIN_CONFIRMED' || $type === 'PAYOUT_CONFIRMED') {
            return 'paid';
        }
        
        // Additional status mapping
        return match(strtolower($status)) {
            'paid', 'completed', 'success', 'approved' => 'paid',
            'pending', 'processing', 'waiting' => 'pending',
            'failed', 'error', 'declined' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            'refunded', 'refund' => 'refunded',
            default => 'pending',
        };
    }
    
    /**
     * Handle GetPay status changes
     */
    protected function handleGetPayStatusChange(Transaction $transaction, string $oldStatus, string $newStatus): void
    {
        $user = $transaction->user;
        
        // CRITICAL: Verify we have the correct user
        if (!$user) {
            Log::error('User not found for GetPay transaction', [
                'transaction_id' => $transaction->transaction_id,
                'transaction_user_id' => $transaction->user_id
            ]);
            return;
        }
        
        // Process payment if status changed to paid
        if ($newStatus === 'paid' && $oldStatus !== 'paid') {
            // Check if transaction is retained
            $retentionService = new \App\Services\RetentionService();
            $isRetained = $retentionService->processTransaction($transaction);
            
            // Only credit wallet if not retained
            if (!$isRetained) {
                $wallet = $user->wallet;
                
                if ($wallet) {
                    // CRITICAL: Check if payment was already processed to prevent duplicate credits
                    $existingCredit = \App\Models\WalletTransaction::where('reference_id', $transaction->transaction_id)
                        ->where('type', 'credit')
                        ->where('category', 'payment_received')
                        ->first();
                        
                    if ($existingCredit) {
                        Log::warning('GetPay payment already processed, skipping duplicate credit', [
                            'transaction_id' => $transaction->transaction_id,
                            'user_id' => $user->id,
                            'existing_credit_id' => $existingCredit->id
                        ]);
                    } else {
                        // Add net amount to wallet
                        $wallet->addCredit(
                            $transaction->net_amount,
                            'payment_received',
                            "Pagamento GetPay recebido - {$transaction->transaction_id}",
                            [
                                'transaction_id' => $transaction->transaction_id,
                                'external_id' => $transaction->external_id,
                                'payment_method' => $transaction->payment_method,
                                'gateway' => 'getpay',
                                'webhook_type' => $webhookData['type'] ?? 'unknown',
                                'end_to_end_id' => $webhookData['endToEndId'] ?? null,
                                'fee_applied' => $webhookData['fee_applied'] ?? null
                            ],
                            $transaction->transaction_id
                        );
                        
                        Log::info('GetPay payment credited to wallet', [
                            'user_id' => $user->id,
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                            'amount' => $transaction->net_amount,
                            'webhook_type' => $webhookData['type'] ?? 'unknown'
                        ]);
                    }
                }
                
                // Dispatch webhook event and UTMify for transaction.paid
                $this->dispatchPaidWebhookAndUtmify($transaction);
            } else {
                Log::info('GetPay transaction retained, wallet not credited', [
                    'transaction_id' => $transaction->transaction_id,
                    'user_id' => $user->id,
                    'amount' => $transaction->net_amount
                ]);
            }
        }
        
        // Handle refunds
        if ($newStatus === 'refunded' && $oldStatus !== 'refunded') {
            $wallet = $user->wallet;
            
            if ($wallet) {
                // CRITICAL: Check if refund already exists to prevent duplicate refunds
                $existingRefund = \App\Models\WalletTransaction::where('reference_id', $transaction->transaction_id . '_getpay_refund')
                    ->where('type', 'debit')
                    ->where('category', 'refund')
                    ->first();
                    
                if ($existingRefund) {
                    Log::warning('GetPay refund already processed, skipping duplicate refund', [
                        'transaction_id' => $transaction->transaction_id,
                        'user_id' => $user->id,
                        'existing_refund_id' => $existingRefund->id
                    ]);
                } else {
                    // Deduct net amount from wallet
                    $wallet->addDebit(
                        $transaction->net_amount,
                        'refund',
                        "Estorno GetPay - {$transaction->transaction_id}",
                        [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                            'webhook_type' => $webhookData['type'] ?? 'unknown',
                            'end_to_end_id' => $webhookData['endToEndId'] ?? null
                        ],
                        $transaction->transaction_id . '_getpay_refund'
                    );
                }
            }
            
            // Dispatch webhook event for transaction.refunded
            $webhookService = new \App\Services\WebhookService();
            $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
        }
        
        // Handle failures and expirations
        if (in_array($newStatus, ['failed', 'expired', 'cancelled']) && !in_array($oldStatus, ['failed', 'expired', 'cancelled'])) {
            // Dispatch appropriate webhook event
            $webhookService = new \App\Services\WebhookService();
            
            if ($newStatus === 'failed') {
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            } elseif ($newStatus === 'expired') {
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }
        }
    }

    /**
     * Process payment (add to wallet, etc.)
     */
    protected function processPayment(Transaction $transaction): void
    {
        try {
            // Skip if transaction is retained
            if ($transaction->is_retained) {
                Log::info('Transação retida, não processando pagamento para wallet', [
                    'transaction_id' => $transaction->transaction_id
                ]);
                return;
            }
            
            $user = $transaction->user;
            $wallet = $user->wallet;

            if (!$wallet) {
                Log::error('Wallet não encontrada para usuário', ['user_id' => $user->id]);
                return;
            }

            // Ensure we're using the exact amount from our database, not from the webhook
            // This ensures consistency between what we charged and what we credit
            // Add net amount to wallet
            $wallet->addCredit(
                $transaction->net_amount,
                'payment_received',
                "Pagamento recebido - {$transaction->transaction_id}",
                [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'payment_method' => $transaction->payment_method,
                    'gateway' => $transaction->gateway->slug,
                ],
                $transaction->transaction_id
            );

            Log::info('Pagamento processado e adicionado à wallet', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'amount' => $transaction->net_amount,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar pagamento na wallet: ' . $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process refund (remove from wallet, etc.)
     * 
     * Modified to allow negative balances
     */
    protected function processRefund(Transaction $transaction, array $webhookData): void
    {
        try {
            // Skip for retained transactions
            if ($transaction->is_retained) {
                Log::info('Transação retida, não processando reembolso para wallet', [
                    'transaction_id' => $transaction->transaction_id
                ]);
                return;
            }
            
            $user = $transaction->user;
            $wallet = $user->wallet;

            if (!$wallet) {
                Log::error('Wallet não encontrada para usuário em reembolso', ['user_id' => $user->id]);
                return;
            }

            // Determine refund amount
            $refundAmount = $transaction->net_amount;
            
            // Check if partial refund
            if (isset($webhookData['refundAmount'])) {
                // Convert from cents to decimal if needed
                $refundAmount = $webhookData['refundAmount'] / 100;
                
                // Calculate fee proportion for partial refund
                $refundProportion = $refundAmount / $transaction->amount;
                $refundFee = $transaction->fee_amount * $refundProportion;
                
                // Calculate net refund amount
                $refundAmount = $refundAmount - $refundFee;
            } else if (isset($webhookData['refundedAmount']) && $webhookData['refundedAmount'] > 0) {
                // Convert from cents to decimal if needed
                $refundAmount = $webhookData['refundedAmount'] / 100;
                
                // Calculate fee proportion for partial refund
                $refundProportion = $refundAmount / $transaction->amount;
                $refundFee = $transaction->fee_amount * $refundProportion;
                
                // Calculate net refund amount
                $refundAmount = $refundAmount - $refundFee;
            }

            // Update refunded_at timestamp if not already set
            if (!$transaction->refunded_at) {
                $transaction->refunded_at = now();
                $transaction->save();
            }

            // Deduct refund amount from wallet (will allow negative balance)
            $wallet->addDebit(
                $refundAmount,
                'refund',
                "Reembolso - {$transaction->transaction_id}",
                [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'payment_method' => $transaction->payment_method,
                    'gateway' => $transaction->gateway->slug,
                ],
                $transaction->transaction_id . '_refund'
            );

            Log::info('Reembolso processado e deduzido da wallet', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'refund_amount' => $refundAmount,
                'new_balance' => $wallet->balance,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar reembolso na wallet: ' . $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Process chargeback (remove from wallet, etc.)
     * 
     * Modified to allow negative balances
     */
    protected function processChargeback(Transaction $transaction): void
    {
        try {
            // Skip if transaction is retained
            if ($transaction->is_retained) {
                Log::info('Transação retida, não processando chargeback para wallet', [
                    'transaction_id' => $transaction->transaction_id
                ]);
                return;
            }
            
            $user = $transaction->user;
            $wallet = $user->wallet;

            if (!$wallet) {
                Log::error('Wallet não encontrada para usuário em chargeback', ['user_id' => $user->id]);
                return;
            }

            // Determine chargeback amount (full amount)
            $chargebackAmount = $transaction->net_amount;

            // Deduct chargeback amount from wallet (will allow negative balance)
            $wallet->addDebit(
                $chargebackAmount,
                'chargeback',
                "Chargeback - {$transaction->transaction_id}",
                [
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'payment_method' => $transaction->payment_method,
                    'gateway' => $transaction->gateway->slug,
                ],
                $transaction->transaction_id . '_chargeback'
            );

            Log::info('Chargeback processado e deduzido da wallet', [
                'user_id' => $user->id,
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $transaction->external_id,
                'chargeback_amount' => $chargebackAmount,
                'new_balance' => $wallet->balance,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao processar chargeback na wallet: ' . $e->getMessage(), [
                'transaction_id' => $transaction->transaction_id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * Handle transaction status changes
     * Public method to allow calling from gateway services when status changes during verification
     */
    public function handleStatusChange(Transaction $transaction, string $oldStatus, string $newStatus): void
    {
        try {
            $user = $transaction->user;
            $wallet = $user->wallet;
            
            if (!$wallet) {
                Log::error('Wallet não encontrada para usuário em webhook', ['user_id' => $user->id]);
                return;
            }
            
            // If status changed to paid and transaction is not retained, credit the wallet
            if ($newStatus === 'paid' && $oldStatus !== 'paid' && !$transaction->is_retained) {
                // Add net amount to wallet
                $wallet->addCredit(
                    $transaction->net_amount,
                    'payment_received',
                    "Pagamento recebido - {$transaction->transaction_id}",
                    [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $transaction->external_id,
                        'payment_method' => $transaction->payment_method,
                        'gateway' => $transaction->gateway ? $transaction->gateway->slug : 'unknown'
                    ],
                    $transaction->transaction_id
                );
                
                Log::info('Pagamento creditado na wallet via webhook', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => $transaction->net_amount,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
                
                // Dispatch webhook event and UTMify for transaction.paid
                $this->dispatchPaidWebhookAndUtmify($transaction);
            }
            
            // Handle other status changes
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                $webhookService = new \App\Services\WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
            }
            
            if ($newStatus === 'expired' && $oldStatus !== 'expired') {
                $webhookService = new \App\Services\WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.expired');
            }
            
            // Handle chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                // Handle chargeback - deduct from wallet if it was previously paid
                if ($oldStatus === 'paid' && !$transaction->is_retained) {
                    $wallet->addDebit(
                        $transaction->net_amount,
                        'chargeback',
                        "Chargeback - {$transaction->transaction_id}",
                        [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus
                        ],
                        $transaction->transaction_id . '_chargeback'
                    );
                    
                    Log::info('Chargeback processado via webhook', [
                        'user_id' => $user->id,
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->net_amount,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus
                    ]);
                }
                
                $webhookService = new \App\Services\WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
            }
            
            // Handle refunds
            if (in_array($newStatus, ['refunded', 'partially_refunded']) && !in_array($oldStatus, ['refunded', 'partially_refunded'])) {
                // Check if this is a fake refund (doesn't deduct from wallet)
                $isFakeRefund = isset($transaction->metadata['fake_refund']) && $transaction->metadata['fake_refund'] === true;
                
                // Handle refunds - deduct from wallet if it was previously paid AND it's not a fake refund
                if ($oldStatus === 'paid' && !$transaction->is_retained && !$isFakeRefund) {
                    $wallet->addDebit(
                        $transaction->net_amount,
                        'refund',
                        "Estorno - {$transaction->transaction_id}",
                        [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                            'old_status' => $oldStatus,
                            'new_status' => $newStatus
                        ],
                        $transaction->transaction_id . '_refund'
                    );
                    
                    Log::info('Estorno processado via webhook', [
                        'user_id' => $user->id,
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->net_amount,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus
                    ]);
                } elseif ($isFakeRefund) {
                    Log::info('Reembolso fake detectado - wallet não será debitada', [
                        'user_id' => $user->id,
                        'transaction_id' => $transaction->transaction_id,
                        'amount' => $transaction->net_amount,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'fake_refund_reason' => $transaction->metadata['fake_refund_reason'] ?? 'N/A'
                    ]);
                }
                
                $webhookService = new \App\Services\WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
            }
            
            // Handle cancelled
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                $webhookService = new \App\Services\WebhookService();
                $webhookService->dispatchTransactionEvent($transaction, 'transaction.cancelled');
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar mudança de status via webhook: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle Pluggou webhook
     * 
     * Documentação: https://api.pluggoutech.com/api/docs
     * 
     * Estrutura do webhook:
     * {
     *   "id": "uuid",
     *   "event_type": "transaction" | "withdrawal",
     *   "data": {
     *     "id": "uuid",
     *     "payment_method": "pix",
     *     "e2e_id": "E60701190202507071554DY5IQBAOH2C",
     *     "amount": 10000,
     *     "platform_tax": 649,
     *     "liquid_amount": 9351,
     *     "status": "pending" | "paid" | "expired" | "failed" | "refunded" | "chargeback",
     *     "paid_at": "2025-10-19 17:31:25",
     *     "created_at": "2025-10-19 17:31:25"
     *   }
     * }
     */
    public function pluggou(Request $request)
    {
        try {
            // Log todos os dados recebidos para debug (sem expor dados sensíveis)
            $requestData = $request->all();
            $logData = [
                'method' => $request->method(),
                'event_type' => $requestData['event_type'] ?? 'unknown',
                'headers' => [
                    'content-type' => $request->header('Content-Type'),
                    'user-agent' => $request->header('User-Agent'),
                ],
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'has_data' => isset($requestData['data']),
                'data_id' => $requestData['data']['id'] ?? null,
                'data_status' => $requestData['data']['status'] ?? null,
            ];
            
            Log::info('Pluggou Webhook recebido', $logData);

            $data = $request->all();

            // Validar estrutura básica do webhook
            if (!isset($data['event_type'])) {
                Log::warning('Webhook Pluggou inválido - event_type ausente', [
                    'received_data' => array_keys($data),
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Dados inválidos: event_type ausente'
                ], 400);
            }

            if (!isset($data['data']) || !is_array($data['data'])) {
                Log::warning('Webhook Pluggou inválido - dados ausente ou inválido', [
                    'event_type' => $data['event_type'] ?? null,
                    'has_data' => isset($data['data']),
                    'data_type' => gettype($data['data'] ?? null),
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Dados inválidos: campo data ausente ou inválido'
                ], 400);
            }

            if (!isset($data['data']['id'])) {
                Log::warning('Webhook Pluggou inválido - ID da transação ausente', [
                    'event_type' => $data['event_type'],
                    'data_keys' => array_keys($data['data']),
                    'ip' => $request->ip()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Dados inválidos: ID da transação ausente'
                ], 400);
            }

            // Processar apenas webhooks de transação (não de saque)
            if ($data['event_type'] !== 'transaction') {
                Log::info('Webhook Pluggou processado - tipo de evento não é transação', [
                    'event_type' => $data['event_type'],
                    'webhook_id' => $data['id'] ?? null
                ]);
                // Retornar sucesso para evitar reenvios desnecessários
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook processado (tipo não tratado: ' . $data['event_type'] . ')'
                ], 200);
            }

            $transactionData = $data['data'];
            $webhookId = $data['id'] ?? null;
            $externalId = $transactionData['id'];
            $status = $transactionData['status'] ?? 'pending';

            // Encontrar transação pelo external_id (ID da Pluggou)
            // Tentar múltiplas formas de busca
            $transaction = Transaction::where('external_id', $externalId)->first();
            
            // Se não encontrou, tentar buscar por outros campos
            if (!$transaction) {
                // Tentar buscar por e2e_id se disponível
                if (isset($transactionData['e2e_id']) && !empty($transactionData['e2e_id'])) {
                    $transaction = Transaction::whereHas('gateway', function($q) {
                        $q->where(function($query) {
                            $query->where('slug', 'pluggou')
                                  ->orWhere('name', 'like', '%pluggou%')
                                  ->orWhereRaw("JSON_EXTRACT(config, '$.gateway_type') = 'pluggou'");
                        });
                    })
                    ->where('payment_method', 'pix')
                    ->whereRaw("JSON_EXTRACT(payment_data, '$.e2e_id') = ?", [$transactionData['e2e_id']])
                    ->first();
                }
                
                // Se ainda não encontrou, tentar buscar por amount e customer (último recurso)
                if (!$transaction && isset($transactionData['amount'])) {
                    $amount = $transactionData['amount'] / 100; // Converter de centavos para reais
                    $transaction = Transaction::whereHas('gateway', function($q) {
                        $q->where(function($query) {
                            $query->where('slug', 'pluggou')
                                  ->orWhere('name', 'like', '%pluggou%')
                                  ->orWhereRaw("JSON_EXTRACT(config, '$.gateway_type') = 'pluggou'");
                        });
                    })
                    ->where('payment_method', 'pix')
                    ->where('status', 'pending')
                    ->whereBetween('amount', [$amount - 0.01, $amount + 0.01])
                    ->where('created_at', '>=', now()->subDays(7)) // Últimos 7 dias
                    ->orderBy('created_at', 'desc')
                    ->first();
                }
            }

            if (!$transaction) {
                Log::warning('Transação não encontrada para webhook Pluggou', [
                    'external_id' => $externalId,
                    'webhook_id' => $webhookId,
                    'status' => $status,
                    'event_type' => $data['event_type'],
                    'ip' => $request->ip(),
                    'data_keys' => array_keys($transactionData),
                    'e2e_id' => $transactionData['e2e_id'] ?? null,
                    'amount' => $transactionData['amount'] ?? null,
                    'search_attempts' => 'external_id, e2e_id, amount',
                    'note' => 'Verifique se o external_id está sendo salvo corretamente ao criar a transação'
                ]);
                
                // Listar transações recentes da Pluggou para debug
                $recentPluggouTransactions = Transaction::whereHas('gateway', function($q) {
                    $q->where(function($query) {
                        $query->where('slug', 'pluggou')
                              ->orWhere('name', 'like', '%pluggou%')
                              ->orWhereRaw("JSON_EXTRACT(config, '$.gateway_type') = 'pluggou'");
                    });
                })
                ->where('payment_method', 'pix')
                ->where('status', 'pending')
                ->where('created_at', '>=', now()->subDays(1))
                ->select('transaction_id', 'external_id', 'amount', 'created_at')
                ->get();
                
                Log::info('Transações Pluggou recentes (para debug)', [
                    'count' => $recentPluggouTransactions->count(),
                    'transactions' => $recentPluggouTransactions->map(function($t) {
                        return [
                            'transaction_id' => $t->transaction_id,
                            'external_id' => $t->external_id,
                            'amount' => $t->amount,
                        ];
                    })->toArray()
                ]);
                
                // Retornar 200 para evitar reenvios infinitos
                // A Pluggou vai reenviar até 5 vezes se retornar erro
                return response()->json([
                    'success' => false,
                    'message' => 'Transação não encontrada',
                    'external_id' => $externalId
                ], 200);
            }

            // Mapear status
            $newStatus = $this->mapPluggouStatus($status);
            $oldStatus = $transaction->status;

            Log::info('Webhook Pluggou processando transação', [
                'transaction_id' => $transaction->transaction_id,
                'external_id' => $externalId,
                'webhook_id' => $webhookId,
                'original_status' => $status,
                'mapped_status' => $newStatus,
                'old_status' => $oldStatus,
                'user_id' => $transaction->user_id,
                'amount' => $transactionData['amount'] ?? null,
                'liquid_amount' => $transactionData['liquid_amount'] ?? null,
                'e2e_id' => $transactionData['e2e_id'] ?? null,
                'paid_at' => $transactionData['paid_at'] ?? null,
            ]);

            // Preparar dados de atualização
            $updateData = [
                'status' => $newStatus,
            ];

            // Atualizar refunded_at se reembolsado ou chargeback
            if (in_array($newStatus, ['refunded', 'partially_refunded', 'chargeback'])) {
                $updateData['refunded_at'] = now();
            } elseif ($newStatus !== 'refunded' && $newStatus !== 'partially_refunded' && $newStatus !== 'chargeback') {
                // Limpar refunded_at se status mudou para não reembolsado
                $updateData['refunded_at'] = null;
            }

            // Atualizar external_id se fornecido e diferente
            if (isset($transactionData['id']) && $transactionData['id'] !== $transaction->external_id) {
                $updateData['external_id'] = $transactionData['id'];
                Log::info('External ID atualizado no webhook Pluggou', [
                    'transaction_id' => $transaction->transaction_id,
                    'old_external_id' => $transaction->external_id,
                    'new_external_id' => $transactionData['id']
                ]);
            }

            // Atualizar paid_at se status mudou para paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                if (isset($transactionData['paid_at']) && !empty($transactionData['paid_at'])) {
                    try {
                        $updateData['paid_at'] = \Carbon\Carbon::parse($transactionData['paid_at']);
                    } catch (\Exception $e) {
                        Log::warning('Erro ao parsear paid_at do webhook Pluggou', [
                            'transaction_id' => $transaction->transaction_id,
                            'paid_at' => $transactionData['paid_at'],
                            'error' => $e->getMessage()
                        ]);
                        $updateData['paid_at'] = now();
                    }
                } else {
                    $updateData['paid_at'] = now();
                }
            }

            // Atualizar metadata com informações adicionais
            $metadata = $transaction->metadata ?? [];
            
            // E2E ID (End-to-End ID) - identificador único no sistema bancário
            if (isset($transactionData['e2e_id']) && !empty($transactionData['e2e_id'])) {
                $metadata['e2e_id'] = $transactionData['e2e_id'];
                $metadata['pluggou_e2e_id'] = $transactionData['e2e_id'];
            }
            
            // Liquid amount (valor líquido após taxas)
            if (isset($transactionData['liquid_amount']) && !empty($transactionData['liquid_amount'])) {
                $metadata['liquid_amount'] = $transactionData['liquid_amount'];
                $metadata['platform_tax'] = $transactionData['platform_tax'] ?? null;
            }
            
            // Webhook ID para rastreamento
            if ($webhookId) {
                $metadata['pluggou_webhook_id'] = $webhookId;
                $metadata['last_webhook_received_at'] = now()->toDateTimeString();
            }
            
            if (!empty($metadata)) {
                $updateData['metadata'] = $metadata;
            }

            // Atualizar transação
            $transaction->update($updateData);
            
            Log::info('Transação atualizada via webhook Pluggou', [
                'transaction_id' => $transaction->transaction_id,
                'status' => $newStatus,
                'paid_at' => $updateData['paid_at'] ?? null,
                'has_e2e_id' => isset($metadata['e2e_id']),
                'has_liquid_amount' => isset($metadata['liquid_amount'])
            ]);

            // Processar pagamento se status mudou para paid
            if ($newStatus === 'paid' && $oldStatus !== 'paid') {
                try {
                    // Processar retenção se aplicável
                    $this->retentionService->processTransaction($transaction);
                    
                    $user = $transaction->user;
                    if (!$user) {
                        Log::error('Usuário não encontrado para transação no webhook Pluggou', [
                            'transaction_id' => $transaction->transaction_id,
                            'user_id' => $transaction->user_id
                        ]);
                    } else {
                        $retentionType = $user->retention_type ?? 1; // Default to Type 1
                        
                        // Process wallet only if not retained
                        if (!$transaction->is_retained) {
                            $this->processPayment($transaction);
                            Log::info('Pagamento processado com sucesso via webhook Pluggou', [
                                'transaction_id' => $transaction->transaction_id,
                                'external_id' => $externalId,
                                'amount' => $transaction->amount,
                                'liquid_amount' => $metadata['liquid_amount'] ?? null,
                                'user_id' => $user->id
                            ]);
                        } else {
                            Log::info('Transação retida, não processando pagamento para wallet (Pluggou)', [
                                'transaction_id' => $transaction->transaction_id,
                                'external_id' => $externalId,
                                'amount' => $transaction->amount,
                                'user_id' => $user->id,
                                'retention_type' => $retentionType
                            ]);
                        }
                        // Sempre disparar webhook e UTMify (mesmo para retidas - será enviado com amount=0)
                        $this->dispatchPaidWebhookAndUtmify($transaction);
                    }
                } catch (\Exception $e) {
                    Log::error('Erro ao processar pagamento no webhook Pluggou', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $externalId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Não lançar exceção para evitar reenvios desnecessários
                }
            }

            // Processar reembolso se status mudou para refunded
            if (in_array($newStatus, ['refunded', 'partially_refunded']) && 
                !in_array($oldStatus, ['refunded', 'partially_refunded'])) {
                try {
                    // Process wallet only if not retained
                    if (!$transaction->is_retained) {
                        $this->processRefund($transaction, $transactionData);
                        
                        Log::info('Reembolso processado via webhook Pluggou', [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $externalId,
                            'status' => $newStatus,
                            'old_status' => $oldStatus
                        ]);
                    } else {
                        Log::info('Transação retida, não processando reembolso para wallet (Pluggou)', [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $externalId,
                            'status' => $newStatus
                        ]);
                    }
                    // Sempre disparar webhook para reembolsos
                    $webhookService = new WebhookService();
                    $webhookService->dispatchTransactionEvent($transaction, 'transaction.refunded');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar reembolso no webhook Pluggou', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $externalId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Não lançar exceção para evitar reenvios desnecessários
                }
            }

            // Processar chargeback se status mudou para chargeback
            if ($newStatus === 'chargeback' && $oldStatus !== 'chargeback') {
                try {
                    // Process wallet only if not retained
                    if (!$transaction->is_retained) {
                        $this->processChargeback($transaction);
                        
                        Log::info('Chargeback processado via webhook Pluggou', [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $externalId,
                            'status' => $newStatus,
                            'old_status' => $oldStatus
                        ]);
                    } else {
                        Log::info('Transação retida, não processando chargeback para wallet (Pluggou)', [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $externalId
                        ]);
                    }
                    // Sempre disparar webhook para chargebacks
                    $webhookService = new WebhookService();
                    $webhookService->dispatchTransactionEvent($transaction, 'transaction.chargeback');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar chargeback no webhook Pluggou', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $externalId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Não lançar exceção para evitar reenvios desnecessários
                }
            }
            
            // Processar falha se status mudou para failed
            if ($newStatus === 'failed' && $oldStatus !== 'failed') {
                try {
                    Log::info('Transação falhou via webhook Pluggou', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $externalId,
                        'status' => $newStatus,
                        'old_status' => $oldStatus
                    ]);
                    
                    // Sempre disparar webhook para falhas
                    $webhookService = new WebhookService();
                    $webhookService->dispatchTransactionEvent($transaction, 'transaction.failed');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar falha no webhook Pluggou', [
                        'transaction_id' => $transaction->transaction_id,
                        'external_id' => $externalId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Não lançar exceção para evitar reenvios desnecessários
                }
            }

            // Retornar sucesso (HTTP 200) para evitar reenvios
            // A Pluggou reenvia até 5 vezes se receber erro (4xx/5xx)
            return response()->json([
                'success' => true,
                'message' => 'Webhook processado com sucesso',
                'transaction_id' => $transaction->transaction_id,
                'status' => $newStatus
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook Pluggou: ' . $e->getMessage(), [
                'request_data' => [
                    'event_type' => $request->input('event_type'),
                    'has_data' => $request->has('data'),
                    'data_id' => $request->input('data.id'),
                ],
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Retornar 200 mesmo em caso de erro para evitar reenvios infinitos
            // A Pluggou vai reenviar até 5 vezes se retornar erro (4xx/5xx)
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar webhook: ' . $e->getMessage()
            ], 200);
        }
    }

    /**
     * Map Pluggou status to our status
     */
    private function mapPluggouStatus(string $gatewayStatus): string
    {
        return match(strtolower($gatewayStatus)) {
            'pending', 'waiting_payment' => 'pending',
            'processing' => 'processing',
            'paid', 'approved', 'success', 'completed' => 'paid',
            'cancelled', 'canceled' => 'cancelled',
            'expired' => 'expired',
            'failed', 'refused', 'error' => 'failed',
            'refunded', 'refound' => 'refunded',
            'partially_refunded' => 'partially_refunded',
            'chargeback' => 'chargeback',
            default => 'pending',
        };
    }
}