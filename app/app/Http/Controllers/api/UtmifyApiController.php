<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\UtmifyIntegration;
use App\Services\PaymentGatewayService;
use App\Services\UtmifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UtmifyApiController extends Controller
{
    /**
     * Generate PIX and send to Utmify
     * Similar to the PHP example provided
     * 
     * POST /api/utmify/generate-pix
     */
    public function generatePix(Request $request)
    {
        try {
            // Get authenticated user (via API key)
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'valor' => 'required|numeric|min:0.01',
                'customer' => 'nullable|array',
                'customer.name' => 'nullable|string|max:255',
                'customer.email' => 'nullable|email|max:255',
                'customer.document' => 'nullable|string|max:18',
                'customer.phone' => 'nullable|string|max:20',
                'src' => 'nullable|string|max:255',
                'sck' => 'nullable|string|max:255',
                'utm_source' => 'nullable|string|max:255',
                'utm_campaign' => 'nullable|string|max:255',
                'utm_medium' => 'nullable|string|max:255',
                'utm_content' => 'nullable|string|max:255',
                'utm_term' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            $valor = floatval($request->valor);
            
            if ($valor <= 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid amount'
                ], 422);
            }

            // Generate order ID
            $orderId = 'ORDER_' . time() . '_' . rand(1000, 9999);
            $createdAt = now()->format('Y-m-d H:i:s');

            // Prepare customer data
            $customerData = $request->input('customer', []);
            
            // Generate document if not provided
            $document = null;
            if (isset($customerData['document']) && !empty($customerData['document'])) {
                $document = preg_replace('/[^0-9]/', '', $customerData['document']);
            } else {
                $document = $this->generateValidCPF();
            }

            // Generate phone if not provided
            $phone = $customerData['phone'] ?? null;
            if (empty($phone)) {
                $phone = '11' . rand(900000000, 999999999);
            } else {
                $phone = preg_replace('/[^0-9]/', '', $phone);
            }

            // Generate email if not provided
            $email = $customerData['email'] ?? null;
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = 'cliente' . time() . '@example.com';
            }

            // Generate name if not provided
            $name = $customerData['name'] ?? null;
            if (empty($name)) {
                $name = 'Cliente';
            }

            // Load user's assigned gateway
            $user->load('assignedGateway');
            
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'No payment gateway configured for this user'
                ], 400);
            }

            // Prepare transaction data for playpayments API
            $transactionData = [
                'amount' => $valor,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $name,
                    'email' => $email,
                    'document' => $document,
                    'phone' => $phone,
                ],
                'external_id' => $orderId,
                'description' => 'Pagamento via PIX',
                'expires_in' => 1800, // 30 minutes
                'metadata' => [
                    'created_via' => 'utmify_api',
                    'user_ip' => $request->ip(),
                    'src' => $request->input('src'),
                    'sck' => $request->input('sck'),
                    'utm_source' => $request->input('utm_source'),
                    'utm_campaign' => $request->input('utm_campaign'),
                    'utm_medium' => $request->input('utm_medium'),
                    'utm_content' => $request->input('utm_content'),
                    'utm_term' => $request->input('utm_term'),
                ],
            ];

            // Create transaction via PaymentGatewayService
            $paymentService = new PaymentGatewayService($user->assignedGateway);
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to create transaction'
                ], 400);
            }

            $transaction = $result['transaction'];
            $transaction->refresh();
            $transaction->load('gateway');

            // Extract PIX code
            $pixCode = $this->extractPixCode($result['gateway_response'] ?? []);
            
            // Extract QR Code Image
            $qrCodeImage = $this->extractQrCodeImage($result['gateway_response'] ?? []);

            // Extract receiver data
            $receiver = $this->extractReceiver($result['gateway_response'] ?? []);

            // Prepare response
            $output = [
                'success' => true,
                'data' => [
                    'id' => $transaction->transaction_id,
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status,
                    'pix' => [
                        'qr_code' => $pixCode,
                        'qrcode' => $qrCodeImage,
                    ],
                ],
                'order_id' => $orderId,
                'transaction_id' => $transaction->transaction_id,
                'created_at_br' => $createdAt,
                'blackcat' => [
                    'id' => $transaction->transaction_id,
                    'copia_e_cola' => $pixCode,
                    'qrcode' => $qrCodeImage,
                ],
                'receiver' => $receiver,
                'customer' => [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'document' => $document,
                ],
                'trackingParams' => [
                    'src' => $request->input('src'),
                    'sck' => $request->input('sck'),
                    'utm_source' => $request->input('utm_source'),
                    'utm_campaign' => $request->input('utm_campaign'),
                    'utm_medium' => $request->input('utm_medium'),
                    'utm_content' => $request->input('utm_content'),
                    'utm_term' => $request->input('utm_term'),
                ],
            ];

            // Send to Utmify (if integration exists)
            $utmifyResult = $this->sendToUtmify($transaction, $request);
            $output['utmify'] = $utmifyResult;

            return response()->json($output, 201);

        } catch (\Exception $e) {
            Log::error('Error in Utmify API generatePix: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error generating PIX: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract PIX code from gateway response
     */
    private function extractPixCode($data): ?string
    {
        // Search in multiple possible locations
        $candidates = [];

        // Direct paths
        if (isset($data['pix']['qr_code']) && $this->isEmvPix($data['pix']['qr_code'])) {
            $candidates[] = trim($data['pix']['qr_code']);
        }
        if (isset($data['pix']['copia_e_cola']) && $this->isEmvPix($data['pix']['copia_e_cola'])) {
            $candidates[] = trim($data['pix']['copia_e_cola']);
        }
        if (isset($data['qr_code']) && $this->isEmvPix($data['qr_code'])) {
            $candidates[] = trim($data['qr_code']);
        }
        if (isset($data['copia_e_cola']) && $this->isEmvPix($data['copia_e_cola'])) {
            $candidates[] = trim($data['copia_e_cola']);
        }
        if (isset($data['emv']) && $this->isEmvPix($data['emv'])) {
            $candidates[] = trim($data['emv']);
        }

        // Search recursively
        $this->searchRecursively($data, $candidates);

        // Return the longest code (usually the most complete)
        if (!empty($candidates)) {
            usort($candidates, function($a, $b) {
                return strlen($b) - strlen($a);
            });
            return $candidates[0];
        }

        return null;
    }

    /**
     * Check if string is valid EMV PIX code
     */
    private function isEmvPix($str): bool
    {
        if (empty($str) || !is_string($str)) {
            return false;
        }
        $str = trim($str);
        // EMV PIX code starts with "000201" or contains "BR.GOV.BCB.PIX" and has at least 40 characters
        return (strpos($str, '000201') === 0 || stripos($str, 'BR.GOV.BCB.PIX') !== false) && strlen($str) > 40;
    }

    /**
     * Search recursively for PIX codes
     */
    private function searchRecursively($obj, &$candidates)
    {
        if (is_array($obj)) {
            foreach ($obj as $value) {
                if (is_string($value) && $this->isEmvPix($value)) {
                    $candidates[] = trim($value);
                } elseif (is_array($value) || is_object($value)) {
                    $this->searchRecursively($value, $candidates);
                }
            }
        } elseif (is_object($obj)) {
            foreach (get_object_vars($obj) as $value) {
                if (is_string($value) && $this->isEmvPix($value)) {
                    $candidates[] = trim($value);
                } elseif (is_array($value) || is_object($value)) {
                    $this->searchRecursively($value, $candidates);
                }
            }
        }
    }

    /**
     * Extract QR Code Image
     */
    private function extractQrCodeImage($data): ?string
    {
        $paths = [
            ['pix', 'qr_code_image'],
            ['pix', 'qrcode'],
            ['qr_code_image'],
            ['qrcode'],
            ['qr_code'],
            ['qrCode'],
        ];

        foreach ($paths as $path) {
            $value = $data;
            $found = true;
            foreach ($path as $key) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    $found = false;
                    break;
                }
            }
            if ($found && !empty($value) && is_string($value)) {
                if (strpos($value, 'data:image') === 0 || strpos($value, 'http') === 0) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Extract receiver data
     */
    private function extractReceiver($data): array
    {
        $receiver = [
            'name' => 'Brasil Transacoes Seguras LTDA',
            'doc' => '58.167.464/0001-77',
            'bank' => 'Fitbank Ip',
        ];

        // Try to extract from response
        $namePaths = [
            ['receiver', 'name'],
            ['receiver', 'nome'],
            ['beneficiary', 'name'],
            ['merchant', 'name'],
        ];

        foreach ($namePaths as $path) {
            $value = $data;
            $found = true;
            foreach ($path as $key) {
                if (isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    $found = false;
                    break;
                }
            }
            if ($found && !empty($value) && is_string($value)) {
                $receiver['name'] = $value;
                break;
            }
        }

        return $receiver;
    }

    /**
     * Send transaction to Utmify
     */
    private function sendToUtmify(Transaction $transaction, Request $request): array
    {
        try {
            // Get active Utmify integrations for this user
            $integrations = UtmifyIntegration::where('user_id', $transaction->user_id)
                ->where('is_active', true)
                ->where('trigger_on_creation', true)
                ->get();

            if ($integrations->isEmpty()) {
                return [
                    'success' => false,
                    'error' => 'No active Utmify integration found for this user'
                ];
            }

            $successCount = 0;
            $errors = [];

            foreach ($integrations as $integration) {
                try {
                    $utmifyPayload = [
                        'orderId' => $transaction->transaction_id,
                        'platform' => 'playpayments',
                        'paymentMethod' => 'pix',
                        'status' => 'waiting_payment',
                        'createdAt' => $transaction->created_at->utc()->format('Y-m-d H:i:s'),
                        'approvedDate' => null,
                        'refundedAt' => null,
                        'customer' => [
                            'name' => $transaction->customer_data['name'] ?? 'Cliente',
                            'email' => $transaction->customer_data['email'] ?? 'cliente@example.com',
                            'phone' => $transaction->customer_data['phone'] ?? null,
                            'document' => $transaction->customer_data['document'] ?? null,
                            'country' => 'BR',
                            'ip' => $request->ip(),
                        ],
                        'products' => [[
                            'id' => 'PROD_' . $transaction->transaction_id,
                            'name' => $transaction->description ?? 'Doação',
                            'planId' => null,
                            'planName' => null,
                            'quantity' => 1,
                            'priceInCents' => intval($transaction->amount * 100),
                        ]],
                        'trackingParameters' => [
                            'src' => $request->input('src'),
                            'sck' => $request->input('sck'),
                            'utm_source' => $request->input('utm_source'),
                            'utm_campaign' => $request->input('utm_campaign'),
                            'utm_medium' => $request->input('utm_medium'),
                            'utm_content' => $request->input('utm_content'),
                            'utm_term' => $request->input('utm_term'),
                        ],
                        'commission' => [
                            'totalPriceInCents' => intval($transaction->amount * 100),
                            'gatewayFeeInCents' => intval($transaction->fee_amount * 100),
                            'userCommissionInCents' => intval($transaction->net_amount * 100),
                        ],
                    ];

                    $apiToken = trim($integration->api_token);
                    $apiToken = preg_replace('/\s+/', '', $apiToken);

                    $response = Http::timeout(30)
                        ->withHeaders([
                            'x-api-token' => $apiToken,
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json',
                        ])
                        ->post('https://api.utmify.com.br/api-credentials/orders', $utmifyPayload);

                    if ($response->successful()) {
                        $successCount++;
                    } else {
                        $errors[] = [
                            'integration_id' => $integration->id,
                            'status' => $response->status(),
                            'error' => $response->json()['message'] ?? 'Unknown error',
                        ];
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'integration_id' => $integration->id,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return [
                'success' => $successCount > 0,
                'sent_count' => $successCount,
                'total_integrations' => $integrations->count(),
                'errors' => $errors,
            ];

        } catch (\Exception $e) {
            Log::error('Error sending to Utmify: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate valid CPF
     */
    private function generateValidCPF(): string
    {
        $n1 = rand(0, 9);
        $n2 = rand(0, 9);
        $n3 = rand(0, 9);
        $n4 = rand(0, 9);
        $n5 = rand(0, 9);
        $n6 = rand(0, 9);
        $n7 = rand(0, 9);
        $n8 = rand(0, 9);
        $n9 = rand(0, 9);

        $d1 = $n9*2 + $n8*3 + $n7*4 + $n6*5 + $n5*6 + $n4*7 + $n3*8 + $n2*9 + $n1*10;
        $d1 = 11 - ($d1 % 11);
        if ($d1 >= 10) $d1 = 0;

        $d2 = $d1*2 + $n9*3 + $n8*4 + $n7*5 + $n6*6 + $n5*7 + $n4*8 + $n3*9 + $n2*10 + $n1*11;
        $d2 = 11 - ($d2 % 11);
        if ($d2 >= 10) $d2 = 0;

        return sprintf('%d%d%d%d%d%d%d%d%d%d%d', $n1, $n2, $n3, $n4, $n5, $n6, $n7, $n8, $n9, $d1, $d2);
    }
}





