<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\AstrofyIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AstrofyService
{
    /**
     * Envia notificação de mudança de status para Astrofy via webhook
     * 
     * @param Transaction $transaction
     * @param string $event Evento: 'created', 'paid', 'refunded'
     * @return bool
     */
    public function sendWebhook(Transaction $transaction, string $event = 'created'): bool
    {
        try {
            // Buscar integrações ativas
            $integrations = AstrofyIntegration::where(function($query) use ($transaction) {
                    $query->whereNull('user_id') // Integrações globais
                          ->orWhere('user_id', $transaction->user_id); // Integrações do usuário
                })
                ->where('is_active', true)
                ->get();

            if ($integrations->isEmpty()) {
                Log::debug('Astrofy: Nenhuma integração ativa encontrada', [
                    'user_id' => $transaction->user_id,
                    'transaction_id' => $transaction->transaction_id,
                    'event' => $event,
                ]);
                return false;
            }

            $successCount = 0;

            foreach ($integrations as $integration) {
                try {
                    // Mapear status para o formato da Astrofy
                    $astrofyStatus = $this->mapStatusToAstrofy($transaction->status, $event);
                    
                    if (!$astrofyStatus) {
                        Log::debug('Astrofy: Status não mapeável', [
                            'transaction_id' => $transaction->transaction_id,
                            'status' => $transaction->status,
                            'event' => $event,
                        ]);
                        continue;
                    }

                    // Preparar payload do webhook
                    $payload = [
                        'externalId' => $transaction->transaction_id,
                        'status' => $astrofyStatus,
                    ];

                    // Adicionar instructions se ainda estiver pendente
                    if ($astrofyStatus === 'PENDING') {
                        $paymentMethod = strtoupper($transaction->payment_method ?? 'PIX');
                        
                        if ($paymentMethod === 'PIX') {
                            $pixData = $transaction->payment_data ?? [];
                            $qrCode = $pixData['payload'] ?? $pixData['qrcode'] ?? $pixData['emv'] ?? $pixData['qr_code'] ?? $pixData['emvqrcps'] ?? null;
                            
                            if ($qrCode) {
                                $payload['instructions'] = [
                                    'type' => 'TOKEN',
                                    'value' => $qrCode,
                                ];
                            }
                        } elseif ($paymentMethod === 'CARD') {
                            $cardData = $transaction->payment_data ?? [];
                            $checkoutUrl = $cardData['checkout_url'] ?? $cardData['url'] ?? $cardData['payment_url'] ?? null;
                            
                            if ($checkoutUrl) {
                                $payload['instructions'] = [
                                    'type' => 'URL',
                                    'value' => $checkoutUrl,
                                ];
                            }
                        }
                    }

                    // URL do webhook da Astrofy
                    $webhookUrl = 'https://gatewayhub.astrofy.site/v1/gateway/webhook';

                    Log::info('🟢 Astrofy: Enviando webhook', [
                        'integration_id' => $integration->id,
                        'transaction_id' => $transaction->transaction_id,
                        'status' => $astrofyStatus,
                        'webhook_url' => $webhookUrl,
                    ]);

                    // Enviar webhook para Astrofy
                    $response = Http::withHeaders([
                        'X-Gateway-Key' => $integration->gateway_key,
                        'Content-Type' => 'application/json',
                    ])->timeout(10)->post($webhookUrl, $payload);

                    if ($response->successful()) {
                        $successCount++;
                        Log::info('✅ Astrofy: Webhook enviado com sucesso', [
                            'integration_id' => $integration->id,
                            'transaction_id' => $transaction->transaction_id,
                            'status_code' => $response->status(),
                        ]);
                    } else {
                        Log::error('❌ Astrofy: Erro ao enviar webhook', [
                            'integration_id' => $integration->id,
                            'transaction_id' => $transaction->transaction_id,
                            'status_code' => $response->status(),
                            'response' => $response->body(),
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('❌ Astrofy: Exceção ao enviar webhook', [
                        'integration_id' => $integration->id,
                        'transaction_id' => $transaction->transaction_id,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                }
            }

            return $successCount > 0;
        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Exceção ao processar webhook', [
                'transaction_id' => $transaction->transaction_id ?? 'N/A',
                'event' => $event,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }

    /**
     * Mapeia o status da transação para o formato da Astrofy
     * 
     * Status possíveis na Astrofy: PENDING, APPROVED, REJECTED, REFUNDED
     * 
     * @param string $status Status da transação
     * @param string $event Evento
     * @return string|null Status no formato Astrofy ou null se não mapeável
     */
    private function mapStatusToAstrofy(string $status, string $event): ?string
    {
        $statusLower = strtolower($status);

        // Mapeamento de status para APPROVED
        if (in_array($statusLower, ['paid', 'paid_out', 'paidout', 'completed', 'success', 'successful', 'approved', 'confirmed', 'settled', 'captured'])) {
            return 'APPROVED';
        }

        // Mapeamento de status para PENDING
        if (in_array($statusLower, ['pending', 'waiting_payment', 'waiting', 'processing'])) {
            return 'PENDING';
        }

        // Mapeamento de status para REFUNDED
        if (in_array($statusLower, ['refunded', 'partially_refunded', 'reversed'])) {
            return 'REFUNDED';
        }

        // Mapeamento de status para REJECTED (falha no pagamento)
        if (in_array($statusLower, ['failed', 'cancelled', 'canceled', 'expired', 'rejected', 'declined', 'error', 'failed_payment'])) {
            return 'REJECTED';
        }

        return null;
    }

    /**
     * Cria um pedido na Astrofy (quando a Astrofy chama nosso endpoint)
     * 
     * @param array $orderData Dados do pedido
     * @param AstrofyIntegration $integration Integração ativa
     * @return array|null Resposta com externalId e instructions ou null em caso de erro
     */
    public function createOrder(array $orderData, AstrofyIntegration $integration): ?array
    {
        try {
            // Validar método de pagamento suportado
            $paymentMethod = strtoupper($orderData['paymentMethod'] ?? '');
            if (!in_array($paymentMethod, $integration->payment_types)) {
                Log::warning('Astrofy: Método de pagamento não suportado', [
                    'payment_method' => $paymentMethod,
                    'supported_methods' => $integration->payment_types,
                ]);
                return null;
            }

            // Aqui você deve criar a transação no seu sistema
            // Por enquanto, vamos retornar um exemplo
            // Você precisará adaptar isso para criar a transação real
            
            // TODO: Criar transação real usando PaymentGatewayService ou similar
            // Por enquanto, vamos simular
            
            $externalId = 'gw_' . uniqid();
            
            // Se for PIX, retornar token/QR Code
            if ($paymentMethod === 'PIX') {
                // Aqui você deve gerar o PIX real
                // Por enquanto, retornamos um exemplo
                return [
                    'externalId' => $externalId,
                    'status' => 'PENDING',
                    'instructions' => [
                        'type' => 'TOKEN',
                        'value' => '00020101021226...', // QR Code real aqui
                    ],
                ];
            }

            // Se for CARD, retornar URL de checkout
            if ($paymentMethod === 'CARD') {
                return [
                    'externalId' => $externalId,
                    'status' => 'PENDING',
                    'instructions' => [
                        'type' => 'URL',
                        'value' => $integration->base_url . '/checkout/' . $externalId,
                    ],
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao criar pedido', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return null;
        }
    }

    /**
     * Consulta status de um pedido
     * 
     * @param string $externalId ID externo do pedido
     * @param AstrofyIntegration $integration Integração ativa
     * @return array|null Dados do pedido ou null se não encontrado
     */
    public function getOrderStatus(string $externalId, AstrofyIntegration $integration): ?array
    {
        try {
            // Buscar transação pelo externalId (transaction_id)
            $transaction = Transaction::where('transaction_id', $externalId)->first();

            if (!$transaction) {
                return null;
            }

            // Mapear status
            $status = $this->mapStatusToAstrofy($transaction->status, 'status');

            if (!$status) {
                $status = 'PENDING'; // Default
            }

            return [
                'externalId' => $transaction->transaction_id,
                'status' => $status,
            ];
        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao consultar status do pedido', [
                'external_id' => $externalId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}

