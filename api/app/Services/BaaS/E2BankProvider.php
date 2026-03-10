<?php

namespace App\Services\BaaS;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class E2BankProvider
{
    private string $qrcodeClientId;
    private string $qrcodeClientSecret;
    private string $qrcodePixKey;
    private string $contasClientId;
    private string $contasClientSecret;
    private string $certPassword;
    
    // ONZ Software API endpoints
    private string $qrcodeBaseUrl = 'https://api.pix.bancoe2.com.br';
    private string $contasBaseUrl = 'https://secureapi.bancoe2-prod.onz.software';
    private string $financeBaseUrl = 'https://finance.bancoe2.com.br';
    
    private string $qrcodeCertPath;
    private string $qrcodeKeyPath;
    private string $contasCertPath;
    private string $contasKeyPath;
    private string $caCertPath;

    public function __construct()
    {
        $this->qrcodeClientId = env('E2BANK_QRCODE_CLIENT_ID');
        $this->qrcodeClientSecret = env('E2BANK_QRCODE_CLIENT_SECRET');
        $this->qrcodePixKey = env('E2BANK_QRCODE_PIX_KEY');
        $this->contasClientId = env('E2BANK_CONTAS_CLIENT_ID');
        $this->contasClientSecret = env('E2BANK_CONTAS_CLIENT_SECRET');
        $this->certPassword = env('E2BANK_CERT_PASSWORD');
        
        // Certificate paths
        // QRCode API uses onz.software signed certificates
        $this->qrcodeCertPath = storage_path('certificates/e2bank/cashin/BANCOE2_41.crt');
        $this->qrcodeKeyPath = storage_path('certificates/e2bank/cashin/BANCOE2_41.key');
        
        // Contas API uses ONZ-SECURE-AREA-BANCOE2 signed certificates
        $this->contasCertPath = storage_path('certificates/e2bank/cashout/BANCOE2_41_CORRECT.crt');
        $this->contasKeyPath = storage_path('certificates/e2bank/cashout/BANCOE2_41_CORRECT.key');
        
        $this->caCertPath = storage_path('certificates/cacert.pem');
    }

    /**
     * Get OAuth2 access token for QR Code API (PIX IN)
     */
    private function getQRCodeAccessToken(): ?string
    {
        $cacheKey = 'e2bank_qrcode_token';
        
        // Check cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::withOptions([
                'cert' => $this->qrcodeCertPath,
                'ssl_key' => $this->qrcodeKeyPath,
                'verify' => false,
            ])
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])
            ->asForm()->post("{$this->qrcodeBaseUrl}/oauth/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->qrcodeClientId,
                'client_secret' => $this->qrcodeClientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;
                
                // Cache token (expires 5 minutes before actual expiration)
                Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 300));
                
                return $token;
            }

            Log::error('E2Bank QRCode OAuth failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('E2Bank QRCode OAuth exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get OAuth2 access token for Contas API (PIX OUT)
     */
    private function getContasAccessToken(): ?string
    {
        $cacheKey = 'e2bank_contas_token';
        
        // Check cache
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            Log::info('E2Bank Contas OAuth attempt', [
                'client_id' => $this->contasClientId,
                'cert_path' => $this->contasCertPath,
                'cert_exists' => file_exists($this->contasCertPath),
                'key_exists' => file_exists($this->contasKeyPath),
            ]);
            
            $response = Http::withOptions([
                'cert' => $this->contasCertPath,
                'ssl_key' => $this->contasKeyPath,
                'verify' => false,
            ])
            ->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ])
            ->asForm()->post("{$this->contasBaseUrl}/api/v2/oauth/token", [
                'grant_type' => 'client_credentials',
                'client_id' => $this->contasClientId,
                'client_secret' => $this->contasClientSecret,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $token = $data['access_token'];
                $expiresIn = $data['expires_in'] ?? 3600;
                
                // Cache token (expires 5 minutes before actual expiration)
                Cache::put($cacheKey, $token, now()->addSeconds($expiresIn - 300));
                
                return $token;
            }

            Log::error('E2Bank Contas OAuth failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('E2Bank Contas OAuth exception', [
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create PIX QR Code (PIX IN)
     */
    public function createPixQRCode(array $data): array
    {
        $token = $this->getQRCodeAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'error' => 'Failed to authenticate with E2Bank QRCode API'
            ];
        }

        try {
            // ONZ Software QR Code API payload format (Padrão BACEN PIX)
            $payload = [
                'chave' => $this->qrcodePixKey,
                'valor' => [
                    'original' => number_format($data['amount'], 2, '.', '')
                ],
                'calendario' => [
                    'expiracao' => $data['expiration'] ?? 3600
                ],
                'infoAdicionais' => [
                    [
                        'nome' => 'Descricao',
                        'valor' => $data['description'] ?? 'Pagamento PIX'
                    ]
                ]
            ];
            
            // Add payer info if provided
            if (!empty($data['payer_cpf']) || !empty($data['payer_cnpj'])) {
                $payload['devedor'] = [
                    'nome' => $data['payer_name'] ?? 'Cliente'
                ];
                
                if (!empty($data['payer_cpf'])) {
                    $payload['devedor']['cpf'] = $data['payer_cpf'];
                } elseif (!empty($data['payer_cnpj'])) {
                    $payload['devedor']['cnpj'] = $data['payer_cnpj'];
                }
            }
            
            $response = Http::withOptions([
                'cert' => $this->qrcodeCertPath,
                'ssl_key' => $this->qrcodeKeyPath,
                'verify' => false,
            ])
            ->withToken($token)
            ->post("{$this->qrcodeBaseUrl}/cob", $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'qr_code' => $responseData['qrCode'] ?? $responseData['imagemQrcode'] ?? null,
                    'qr_code_text' => $responseData['pixCopiaECola'] ?? $responseData['qrCodeText'] ?? null,
                    'txid' => $responseData['txid'] ?? $responseData['id'] ?? null,
                    'external_id' => $responseData['id'] ?? $responseData['txid'] ?? null,
                    'expiration' => $responseData['calendario']['criacao'] ?? $responseData['expiresAt'] ?? null,
                    'raw_response' => $responseData
                ];
            }

            Log::error('E2Bank create QR Code failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create QR Code',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('E2Bank create QR Code exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get PIX QR Code status
     */
    public function getPixQRCodeStatus(string $txid): array
    {
        $token = $this->getQRCodeAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'error' => 'Failed to authenticate with E2Bank QRCode API'
            ];
        }

        try {
            $response = Http::withOptions([
                'cert' => $this->qrcodeCertPath,
                'ssl_key' => $this->qrcodeKeyPath,
                'verify' => false,
            ])
            ->withToken($token)
            ->get("{$this->qrcodeBaseUrl}/v2/cob/{$txid}");

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'paid' => ($data['status'] ?? '') === 'CONCLUIDA',
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get QR Code status'
            ];

        } catch (\Exception $e) {
            Log::error('E2Bank get QR Code status exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create PIX transfer (PIX OUT)
     */
    public function createPixTransfer(array $data): array
    {
        $token = $this->getContasAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'error' => 'Failed to authenticate with E2Bank Contas API'
            ];
        }

        try {
            $payload = [
                'value' => $data['amount'], // Amount in cents
                'key' => $data['pix_key'],
                'description' => $data['description'] ?? 'Transferência PIX',
                'idempotencyKey' => $data['withdrawal_id'],
            ];

            $response = Http::withOptions([
                'cert' => $this->contasCertPath,
                'ssl_key' => $this->contasKeyPath,
                'verify' => false, 'curl' => [ CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, ],
            ])
            ->withToken($token)
            ->post("{$this->contasBaseUrl}/v1/pix/transfer", $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'transfer_id' => $responseData['id'] ?? null,
                    'status' => $responseData['status'] ?? 'PENDING',
                    'e2e_id' => $responseData['endToEndId'] ?? null,
                    'raw_response' => $responseData
                ];
            }

            Log::error('E2Bank create PIX transfer failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create PIX transfer',
                'details' => $response->json()
            ];

        } catch (\Exception $e) {
            Log::error('E2Bank create PIX transfer exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get PIX transfer status
     */
    public function getPixTransferStatus(string $transferId): array
    {
        $token = $this->getContasAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'error' => 'Failed to authenticate with E2Bank Contas API'
            ];
        }

        try {
            $response = Http::withOptions([
                'cert' => $this->contasCertPath,
                'ssl_key' => $this->contasKeyPath,
                'verify' => false, 'curl' => [ CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, ],
            ])
            ->withToken($token)
            ->get("{$this->contasBaseUrl}/v1/pix/transfer/{$transferId}");

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'UNKNOWN',
                    'completed' => in_array($data['status'] ?? '', ['COMPLETED', 'APPROVED']),
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get transfer status'
            ];

        } catch (\Exception $e) {
            Log::error('E2Bank get transfer status exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate PIX key
     */
    public function validatePixKey(string $pixKey): array
    {
        $token = $this->getContasAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'error' => 'Failed to authenticate with E2Bank Contas API'
            ];
        }

        try {
            $response = Http::withOptions([
                'cert' => $this->contasCertPath,
                'ssl_key' => $this->contasKeyPath,
                'verify' => false, 'curl' => [ CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => false, ],
            ])
            ->withToken($token)
            ->post("{$this->contasBaseUrl}/v1/pix/key/validate", [
                'key' => $pixKey
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'valid' => $data['valid'] ?? false,
                    'owner_name' => $data['ownerName'] ?? null,
                    'owner_document' => $data['ownerDocument'] ?? null,
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to validate PIX key'
            ];

        } catch (\Exception $e) {
            Log::error('E2Bank validate PIX key exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
