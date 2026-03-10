<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\User;
use App\Services\Gateways\HopyGatewayService;
use App\Services\Gateways\SplitwaveGatewayService;
use App\Services\Gateways\SharkgatewayGatewayService;
use App\Services\Gateways\ArkamaGatewayService;
use App\Services\Gateways\VersellGatewayService;
use App\Services\Gateways\GetpayGatewayService;
use App\Services\Gateways\CashtimeGatewayService;
use App\Services\Gateways\E2BankGatewayService;
use App\Services\Gateways\PluggouGatewayService;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    private PaymentGateway $gateway;
    private $gatewayService;

    public function __construct(PaymentGateway $gateway)
    {
        $this->gateway = $gateway;
        $this->gatewayService = $this->createGatewayService();
    }

    /**
     * Create the appropriate gateway service based on gateway type
     */
    private function createGatewayService()
    {
        $gatewayType = $this->gateway->getConfig('gateway_type', 'avivhub');

        return match($gatewayType) {
            'hopy' => new HopyGatewayService($this->gateway),
            'splitwave' => new SplitwaveGatewayService($this->gateway),
            'sharkgateway' => new SharkgatewayGatewayService($this->gateway),
            'arkama' => new ArkamaGatewayService($this->gateway),
            'versell' => new VersellGatewayService($this->gateway),
            'getpay' => new GetpayGatewayService($this->gateway),
            'cashtime' => new CashtimeGatewayService($this->gateway),
            'e2bank' => new E2BankGatewayService($this->gateway),
            'pluggou' => new PluggouGatewayService($this->gateway),
            default => throw new \Exception("Tipo de gateway não suportado: {$gatewayType}")
        };
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(User $user, array $data): array
    {
        try {
            // Validate required data
            $this->validateTransactionData($data);

            // Check if gateway service is configured
            if (!$this->gatewayService->isConfigured()) {
                throw new \Exception('Gateway não está configurado com credenciais válidas');
            }

            // Delegate to the specific gateway service
            return $this->gatewayService->createTransaction($user, $data);

        } catch (\Exception $e) {
            Log::error('Erro no PaymentGatewayService: ' . $e->getMessage(), [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
                'user_id' => $user->id,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate transaction data
     */
    private function validateTransactionData(array $data): void
    {
        $required = ['amount', 'payment_method', 'customer'];
        
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                throw new \Exception("Campo obrigatório ausente: {$field}");
            }
        }

        // Validate customer data
        $customerRequired = ['name', 'email', 'document'];
        foreach ($customerRequired as $field) {
            if (!isset($data['customer'][$field])) {
                throw new \Exception("Campo obrigatório do cliente ausente: {$field}");
            }
        }

        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            throw new \Exception('Valor deve ser um número positivo');
        }

        // Validate payment method
        $validMethods = ['pix', 'credit_card', 'bank_slip'];
        if (!in_array($data['payment_method'], $validMethods)) {
            throw new \Exception('Método de pagamento inválido');
        }
    }

    /**
     * Get gateway name
     */
    public function getGatewayName(): string
    {
        return $this->gateway->name;
    }

    /**
     * Check if gateway is configured
     */
    public function isConfigured(): bool
    {
        return $this->gatewayService->isConfigured();
    }

    /**
     * Check transaction status from gateway API
     */
    public function checkTransactionStatus(\App\Models\Transaction $transaction): array
    {
        try {
            // Check if gateway service has the checkTransactionStatus method
            if (!method_exists($this->gatewayService, 'checkTransactionStatus')) {
                return [
                    'success' => false,
                    'error' => 'Gateway não suporta verificação de status via API',
                ];
            }

            return $this->gatewayService->checkTransactionStatus($transaction);

        } catch (\Exception $e) {
            Log::error('Erro ao verificar status da transação no PaymentGatewayService: ' . $e->getMessage(), [
                'gateway_id' => $this->gateway->id,
                'gateway_name' => $this->gateway->name,
                'transaction_id' => $transaction->transaction_id,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}