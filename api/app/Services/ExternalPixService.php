<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExternalPixService
{
    protected string $apiUrl;
    protected string $apiToken;
    protected string $authType; // 'bearer', 'basic', 'header'
    protected string $tokenHeader; // Nome do header para token (ex: 'X-API-Key', 'Authorization')
    protected ?string $apiSecret; // Para Basic Auth ou outros casos
    protected int $timeout;
    protected bool $verifySsl;

    /**
     * Constructor
     * 
     * @param array $config Configuração da API externa
     *   - api_url: URL base da API
     *   - api_token: Token/API Key do cliente
     *   - auth_type: Tipo de autenticação ('bearer', 'basic', 'header')
     *   - token_header: Nome do header para token (padrão: 'Authorization')
     *   - api_secret: Secret para Basic Auth (opcional)
     *   - timeout: Timeout em segundos (padrão: 30)
     *   - verify_ssl: Verificar SSL (padrão: true)
     */
    public function __construct(array $config)
    {
        $this->apiUrl = rtrim($config['api_url'] ?? '', '/');
        $this->apiToken = $config['api_token'] ?? '';
        $this->authType = $config['auth_type'] ?? 'bearer';
        $this->tokenHeader = $config['token_header'] ?? 'Authorization';
        $this->apiSecret = $config['api_secret'] ?? null;
        $this->timeout = $config['timeout'] ?? 30;
        $this->verifySsl = $config['verify_ssl'] ?? true;

        if (empty($this->apiUrl) || empty($this->apiToken)) {
            throw new \Exception('API URL e Token são obrigatórios');
        }
    }

    /**
     * Criar um QR Code PIX
     * 
     * @param array $data Dados do pagamento
     *   - amount: Valor em reais (ex: 100.50)
     *   - description: Descrição do pagamento
     *   - customer: Dados do cliente (name, email, document, phone)
     *   - expires_in: Tempo de expiração em minutos (opcional)
     *   - external_id: ID externo da transação (opcional)
     * 
     * @return array Resposta com dados do PIX
     */
    public function createPixQrCode(array $data): array
    {
        try {
            // Preparar payload baseado no formato comum de APIs PIX
            $payload = $this->preparePixPayload($data);

            // Determinar endpoint (pode variar por API)
            $endpoint = $this->getEndpoint('create_pix');

            // Fazer requisição
            $response = $this->makeRequest('POST', $endpoint, $payload);

            // Processar resposta
            return $this->processPixResponse($response, $data);

        } catch (\Exception $e) {
            Log::error('Erro ao criar QR Code PIX via API externa', [
                'error' => $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Consultar status de um pagamento PIX
     * 
     * @param string $transactionId ID da transação
     * @return array Dados da transação
     */
    public function getPixStatus(string $transactionId): array
    {
        try {
            $endpoint = $this->getEndpoint('check_status', ['id' => $transactionId]);
            $response = $this->makeRequest('GET', $endpoint);

            return $this->processStatusResponse($response);

        } catch (\Exception $e) {
            Log::error('Erro ao consultar status PIX via API externa', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Preparar payload para criação de PIX (formato PodPay)
     */
    protected function preparePixPayload(array $data): array
    {
        $customer = $data['customer'] ?? [];
        
        // Formato PodPay
        $payload = [
            'amount' => (int)round(($data['amount'] ?? 0) * 100), // Converter para centavos
            'currency' => $data['currency'] ?? 'BRL',
            'paymentMethod' => 'pix',
            'customer' => [
                'name' => $customer['name'] ?? '',
                'email' => $customer['email'] ?? '',
                'document' => preg_replace('/[^0-9]/', '', $customer['document'] ?? ''),
            ],
        ];

        // Adicionar telefone se fornecido
        if (!empty($customer['phone'])) {
            $payload['customer']['phone'] = preg_replace('/[^0-9]/', '', $customer['phone']);
        }

        // Adicionar PIX com expiração
        $pixConfig = [];
        if (isset($data['expires_in'])) {
            $pixConfig['expiresIn'] = (int)($data['expires_in'] * 60); // converter minutos para segundos
        } else {
            $pixConfig['expiresIn'] = 900; // padrão: 15 minutos em segundos
        }
        $payload['pix'] = $pixConfig;

        // Adicionar items se fornecidos
        if (isset($data['items']) && is_array($data['items'])) {
            $payload['items'] = $data['items'];
        } else {
            // Criar item padrão baseado na descrição
            $payload['items'] = [
                [
                    'title' => $data['description'] ?? 'Pagamento PIX',
                    'unitPrice' => (int)round(($data['amount'] ?? 0) * 100),
                    'quantity' => 1,
                    'tangible' => false,
                ]
            ];
        }

        // Adicionar campos opcionais
        if (isset($data['postbackUrl'])) {
            $payload['postbackUrl'] = $data['postbackUrl'];
        }
        if (isset($data['returnUrl'])) {
            $payload['returnUrl'] = $data['returnUrl'];
        }
        if (isset($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }
        if (isset($data['external_id']) || isset($data['externalRef'])) {
            $payload['externalRef'] = $data['external_id'] ?? $data['externalRef'];
        }
        if (isset($data['ip'])) {
            $payload['ip'] = $data['ip'];
        }

        return $payload;
    }

    /**
     * Processar resposta da criação de PIX
     */
    protected function processPixResponse(array $response, array $originalData): array
    {
        // Tentar extrair dados PIX de diferentes formatos de resposta
        $pixData = [
            'qrcode' => null,
            'payload' => null,
            'emv' => null,
            'qr_code_image' => null,
            'expiration_date' => null,
            'transaction_id' => null,
        ];

        // Tentar diferentes formatos de resposta
        if (isset($response['data']['pix']['qrcode'])) {
            $pixData['qrcode'] = $response['data']['pix']['qrcode'];
            $pixData['payload'] = $response['data']['pix']['qrcode'];
        } elseif (isset($response['pix']['qrcode'])) {
            $pixData['qrcode'] = $response['pix']['qrcode'];
            $pixData['payload'] = $response['pix']['qrcode'];
        } elseif (isset($response['data']['qrcode'])) {
            $pixData['qrcode'] = $response['data']['qrcode'];
            $pixData['payload'] = $response['data']['qrcode'];
        } elseif (isset($response['qrcode'])) {
            $pixData['qrcode'] = $response['qrcode'];
            $pixData['payload'] = $response['qrcode'];
        }

        // Tentar extrair EMV/payload
        if (isset($response['data']['pix']['emv'])) {
            $pixData['emv'] = $response['data']['pix']['emv'];
            $pixData['payload'] = $response['data']['pix']['emv'];
        } elseif (isset($response['pix']['emv'])) {
            $pixData['emv'] = $response['pix']['emv'];
            $pixData['payload'] = $response['pix']['emv'];
        } elseif (isset($response['data']['emv'])) {
            $pixData['emv'] = $response['data']['emv'];
            $pixData['payload'] = $response['data']['emv'];
        } elseif (isset($response['emv'])) {
            $pixData['emv'] = $response['emv'];
            $pixData['payload'] = $response['emv'];
        }

        // Tentar extrair payload diretamente
        if (isset($response['data']['pix']['payload'])) {
            $pixData['payload'] = $response['data']['pix']['payload'];
            $pixData['qrcode'] = $response['data']['pix']['payload'];
        } elseif (isset($response['pix']['payload'])) {
            $pixData['payload'] = $response['pix']['payload'];
            $pixData['qrcode'] = $response['pix']['payload'];
        }

        // Tentar extrair imagem do QR Code
        if (isset($response['data']['pix']['qr_code_image'])) {
            $pixData['qr_code_image'] = $response['data']['pix']['qr_code_image'];
        } elseif (isset($response['pix']['qr_code_image'])) {
            $pixData['qr_code_image'] = $response['pix']['qr_code_image'];
        }

        // Tentar extrair data de expiração
        if (isset($response['data']['pix']['expiration_date'])) {
            $pixData['expiration_date'] = $response['data']['pix']['expiration_date'];
        } elseif (isset($response['pix']['expiration_date'])) {
            $pixData['expiration_date'] = $response['pix']['expiration_date'];
        } elseif (isset($response['data']['expires_at'])) {
            $pixData['expiration_date'] = $response['data']['expires_at'];
        }

        // Tentar extrair ID da transação
        if (isset($response['data']['id'])) {
            $pixData['transaction_id'] = $response['data']['id'];
        } elseif (isset($response['id'])) {
            $pixData['transaction_id'] = $response['id'];
        } elseif (isset($response['data']['transaction_id'])) {
            $pixData['transaction_id'] = $response['data']['transaction_id'];
        }

        // Se não encontrou nenhum dado PIX, lançar exceção
        if (empty($pixData['qrcode']) && empty($pixData['payload']) && empty($pixData['emv'])) {
            throw new \Exception('Resposta da API não contém dados PIX válidos. Resposta: ' . json_encode($response));
        }

        return [
            'success' => true,
            'pix' => $pixData,
            'raw_response' => $response,
        ];
    }

    /**
     * Processar resposta de status
     */
    protected function processStatusResponse(array $response): array
    {
        $status = 'pending';
        
        // Mapear diferentes formatos de status
        if (isset($response['data']['status'])) {
            $status = $this->mapStatus($response['data']['status']);
        } elseif (isset($response['status'])) {
            $status = $this->mapStatus($response['status']);
        }

        return [
            'status' => $status,
            'raw_response' => $response,
        ];
    }

    /**
     * Mapear status da API externa para status padrão
     */
    protected function mapStatus(string $externalStatus): string
    {
        $statusMap = [
            'paid' => 'paid',
            'pago' => 'paid',
            'approved' => 'paid',
            'aprovado' => 'paid',
            'pending' => 'pending',
            'pendente' => 'pending',
            'waiting_payment' => 'pending',
            'aguardando_pagamento' => 'pending',
            'expired' => 'expired',
            'expirado' => 'expired',
            'cancelled' => 'cancelled',
            'cancelado' => 'cancelled',
            'refunded' => 'refunded',
            'reembolsado' => 'refunded',
        ];

        $normalized = strtolower(trim($externalStatus));
        return $statusMap[$normalized] ?? 'pending';
    }

    /**
     * Fazer requisição HTTP
     */
    protected function makeRequest(string $method, string $endpoint, ?array $payload = null): array
    {
        $url = $this->apiUrl . '/' . ltrim($endpoint, '/');
        
        $http = Http::timeout($this->timeout);

        if (!$this->verifySsl) {
            $http = $http->withoutVerifying();
        }

        // Configurar autenticação
        $http = $this->configureAuth($http);

        // Adicionar headers padrão
        $http = $http->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'PixBolt-ExternalPix/1.0',
        ]);

        // Fazer requisição
        if ($method === 'GET') {
            $response = $http->get($url);
        } elseif ($method === 'POST') {
            $response = $http->post($url, $payload ?? []);
        } elseif ($method === 'PUT') {
            $response = $http->put($url, $payload ?? []);
        } elseif ($method === 'DELETE') {
            $response = $http->delete($url);
        } else {
            throw new \Exception("Método HTTP não suportado: {$method}");
        }

        // Log da requisição
        Log::info('Requisição para API PIX externa', [
            'method' => $method,
            'url' => $url,
            'payload' => $payload,
            'status' => $response->status(),
            'response' => $response->json(),
        ]);

        // Verificar se houve erro
        if (!$response->successful()) {
            $errorBody = $response->body();
            $errorJson = $response->json();
            
            throw new \Exception(
                'Erro na API externa: ' . 
                ($errorJson['message'] ?? $errorJson['error'] ?? $errorBody ?? 'Erro desconhecido')
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Configurar autenticação na requisição HTTP
     */
    protected function configureAuth($http)
    {
        switch ($this->authType) {
            case 'bearer':
                return $http->withToken($this->apiToken);
            
            case 'basic':
                $password = $this->apiSecret ?? '';
                return $http->withBasicAuth($this->apiToken, $password);
            
            case 'header':
                return $http->withHeader($this->tokenHeader, $this->apiToken);
            
            default:
                throw new \Exception("Tipo de autenticação não suportado: {$this->authType}");
        }
    }

    /**
     * Obter endpoint da API
     * Pode ser sobrescrito para APIs com formatos diferentes
     */
    protected function getEndpoint(string $action, array $params = []): string
    {
        $endpoints = [
            'create_pix' => 'v1/transactions', // Formato PodPay
            'check_status' => 'v1/transactions/' . ($params['id'] ?? ''),
        ];

        return $endpoints[$action] ?? '';
    }

    /**
     * Criar instância do serviço a partir de configuração do arquivo de config
     */
    public static function fromConfig(string $provider = 'default'): self
    {
        $config = config("services.external_pix.{$provider}", []);

        if (empty($config)) {
            throw new \Exception("Configuração não encontrada para o provedor: {$provider}");
        }

        return new self($config);
    }
}

