<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * List all transactions
     * 
     * GET /api/v1/transactions
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Pagination and filter parameters
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $paymentMethod = $request->get('payment_method');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');

            // Load user's assigned gateway for logs
            $user->load('assignedGateway');
            
            Log::info('Listing transactions via API v1', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gateway_id' => $user->assignedGateway->id ?? null,
                'gateway_name' => $user->assignedGateway->name ?? null,
            ]);

            // Base query
            $query = Transaction::where('user_id', $user->id)
                ->with('gateway')
                ->orderBy('created_at', 'desc');

            // Filters
            if ($status) {
                $query->where('status', $status);
            }

            if ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            }

            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                      ->orWhere('external_id', 'like', "%{$search}%")
                      ->orWhere('customer_data->name', 'like', "%{$search}%")
                      ->orWhere('customer_data->email', 'like', "%{$search}%");
                });
            }

            // Pagination
            $transactions = $query->paginate($perPage);

            // Format response
            $data = $transactions->map(function ($transaction) {
                return $this->formatTransaction($transaction);
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error listing transactions via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error listing transactions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction details
     * 
     * GET /api/v1/transactions/{id}
     * 
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, string $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Find transaction by transaction_id or external_id
            $transaction = Transaction::where('user_id', $user->id)
                ->where(function($query) use ($id) {
                    $query->where('transaction_id', $id)
                          ->orWhere('external_id', $id);
                })
                ->with('gateway')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Transaction not found'
                ], 404);
            }

            // If PIX pending, check updated status on gateway before returning
            if ($transaction->payment_method === 'pix' && $transaction->status === 'pending' && $transaction->external_id) {
                try {
                    $gatewayType = $transaction->gateway ? $transaction->gateway->getConfig('gateway_type') : null;
                    $gatewaySlug = $transaction->gateway ? $transaction->gateway->slug : null;
                    
                    // Check status on gateway (Shark or Pluggou)
                    if ($transaction->gateway && (in_array($gatewayType, ['sharkgateway', 'pluggou']) || $gatewaySlug === 'pluggou')) {
                        // Check updated status on gateway
                        $paymentService = new PaymentGatewayService($transaction->gateway);
                        $statusResult = $paymentService->checkTransactionStatus($transaction);
                        
                        // If Pluggou and doesn't have GET endpoint, result may have note about webhook
                        if (($gatewaySlug === 'pluggou' || $gatewayType === 'pluggou') && isset($statusResult['note'])) {
                            Log::info('Pluggou: API verification not available, depends on webhook', [
                                'transaction_id' => $transaction->transaction_id,
                                'external_id' => $transaction->external_id,
                                'note' => $statusResult['note']
                            ]);
                        }
                        
                        // Reload transaction to get updated data
                        $transaction->refresh();
                    }
                } catch (\Exception $e) {
                    // If error checking, continue and return database data
                    Log::warning('Error checking status when fetching transaction', [
                        'transaction_id' => $transaction->transaction_id,
                        'gateway' => $transaction->gateway->name ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Reload to ensure most recent data
            $transaction->refresh();

            return response()->json([
                'success' => true,
                'data' => $this->formatTransaction($transaction)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching transaction via API', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error fetching transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new transaction
     * 
     * POST /api/v1/transactions
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Validate data
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'payment_method' => 'required|in:pix,credit_card,bank_slip',
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document' => 'required|string|min:11|max:18',
                'customer.phone' => 'nullable|string|min:10|max:15',
                'sale_name' => 'required_without:products|string|max:255',
                'description' => 'required|string|max:500',
                'external_id' => 'nullable|string|max:255|unique:transactions,external_id',
                'expires_in' => 'nullable|integer|min:60|max:86400', // 1 minute to 24 hours (in seconds)
                'pix_expires_in_minutes' => 'nullable|integer|min:1|max:129600', // 1 minute to 90 days (in minutes)
                'installments' => 'nullable|integer|min:1|max:12',
                'products' => 'nullable|array',
                'products.*.title' => 'required_with:products|string|max:255',
                'products.*.name' => 'nullable|string|max:255',
                'products.*.description' => 'required_with:products|string|max:500',
                'products.*.quantity' => 'nullable|integer|min:1',
                'products.*.unitPrice' => 'nullable|numeric|min:0.01',
                'products.*.price' => 'nullable|numeric|min:0.01',
                'products.*.tangible' => 'nullable|boolean',
            ], [
                'sale_name.required_without' => 'O nome do produto é obrigatório quando não há produtos especificados.',
                'description.required' => 'A descrição do produto é obrigatória.',
                'products.*.title.required_with' => 'O título do produto é obrigatório quando produtos são especificados.',
                'products.*.description.required_with' => 'A descrição do produto é obrigatória quando produtos são especificados.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Load user's assigned gateway
            $user->load('assignedGateway');
            
            // Check if user has gateway configured
            if (!$user->assignedGateway) {
                Log::warning('Attempt to create transaction without configured gateway', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'No payment gateway configured for this user'
                ], 400);
            }

            Log::info('User gateway identified via token', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gateway_id' => $user->assignedGateway->id,
                'gateway_name' => $user->assignedGateway->name,
                'gateway_slug' => $user->assignedGateway->slug,
                'gateway_type' => $user->assignedGateway->getConfig('gateway_type'),
                'gateway_api_url' => $user->assignedGateway->api_url,
            ]);

            // Clean document
            $document = preg_replace('/[^0-9]/', '', $request->input('customer.document'));

            // Prepare transaction data
            $transactionData = [
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'customer' => [
                    'name' => $request->input('customer.name'),
                    'email' => $request->input('customer.email'),
                    'document' => $document,
                ],
                'external_id' => $request->external_id ?? 'TXN_' . time() . '_' . uniqid(),
                'description' => $request->description,
                'installments' => $request->installments ?? 1,
                'metadata' => [
                    'created_via' => 'api_v1',
                    'user_ip' => $request->ip(),
                    'description' => $request->description,
                ],
            ];

            // Add sale_name if provided (when no products)
            if ($request->has('sale_name')) {
                $transactionData['sale_name'] = $request->sale_name;
                $transactionData['metadata']['sale_name'] = $request->sale_name;
            }

            // Add phone if provided
            if ($request->has('customer.phone')) {
                $transactionData['customer']['phone'] = preg_replace('/[^0-9]/', '', $request->input('customer.phone'));
            }

            // Add products if provided
            if ($request->has('products') && is_array($request->products)) {
                $transactionData['products'] = $request->products;
            } elseif ($request->has('sale_name')) {
                // If no products but has sale_name, create a single product
                $transactionData['products'] = [[
                    'title' => $request->sale_name,
                    'name' => $request->sale_name,
                    'description' => $request->description,
                    'quantity' => 1,
                    'unitPrice' => $request->amount,
                    'price' => $request->amount,
                ]];
            }

            // Configure PIX expiration
            if ($request->payment_method === 'pix') {
                if ($request->has('pix_expires_in_minutes')) {
                    $transactionData['pix_expires_in_minutes'] = $request->pix_expires_in_minutes;
                } elseif ($request->has('expires_in')) {
                    // Convert seconds to minutes
                    $transactionData['pix_expires_in_minutes'] = (int)ceil($request->expires_in / 60);
                } else {
                    // Default: 15 minutes for PIX
                    $transactionData['pix_expires_in_minutes'] = 15;
                }
            }

            // Create payment service
            $paymentService = new PaymentGatewayService($user->assignedGateway);

            Log::info('Creating transaction via API v1', [
                'user_id' => $user->id,
                'gateway_id' => $user->assignedGateway->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
            ]);

            // Create transaction via payment service
            $result = $paymentService->createTransaction($transactionData, $user);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Failed to create transaction'
                ], 400);
            }

            $transaction = $result['transaction'];

            // Extract PIX data if available
            if ($transaction->payment_method === 'pix' && isset($result['pix_data'])) {
                $pixData = $result['pix_data'];
                
                if (empty($pixData['payload']) && empty($pixData['qrcode']) && empty($pixData['emv'])) {
                    Log::warning('PIX data found in gatewayResponse but payload/qrcode/emv is empty', [
                        'transaction_id' => $transaction->transaction_id,
                        'pix_data' => $pixData,
                    ]);
                } else {
                    Log::info('PIX data found in gatewayResponse', [
                        'transaction_id' => $transaction->transaction_id,
                        'has_payload' => !empty($pixData['payload']),
                        'has_qrcode' => !empty($pixData['qrcode']),
                        'has_emv' => !empty($pixData['emv']),
                    ]);
                }
            }

            // Format and return complete response
            $formattedData = $this->formatTransaction($transaction);

            return response()->json([
                'success' => true,
                'data' => $formattedData
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating transaction via API v1', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error creating transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format transaction for API response
     * Returns complete JSON with all necessary data
     * 
     * @param Transaction $transaction
     * @return array
     */
    private function formatTransaction(Transaction $transaction): array
    {
        // Extract PIX data if available
        $pixData = null;
        $pixCode = null;
        $pixQrCode = null;
        $pixPayload = null;
        $endToEndId = null;
        $pixTxId = null;
        $pixExpiration = null;

        if ($transaction->payment_method === 'pix' && $transaction->payment_data) {
            // Try multiple formats of PIX data structure
            $paymentData = $transaction->payment_data;
            
            $pixData = $paymentData['payment_data']['pix'] ?? 
                       $paymentData['pix'] ?? 
                       $paymentData['data']['pix'] ?? null;
            
            if ($pixData) {
                // Extract PIX code (may be in multiple fields)
                $pixCode = $pixData['payload'] ?? 
                          $pixData['qrcode'] ?? 
                          $pixData['emv'] ?? 
                          $pixData['qr_code'] ?? 
                          $pixData['code'] ?? null;
                
                $pixQrCode = $pixData['qrcode'] ?? $pixCode;
                $pixPayload = $pixData['payload'] ?? $pixCode;
                
                // Extract end to end ID
                $endToEndId = $pixData['endToEndId'] ?? 
                             $pixData['end_to_end_id'] ?? 
                             $pixData['endToEnd'] ?? 
                             $pixData['end_to_end'] ?? null;
                
                // Extract TXID
                $pixTxId = $pixData['txid'] ?? 
                          $pixData['tx_id'] ?? 
                          $pixData['transaction_id'] ?? null;
                
                // Extract PIX expiration date
                if (isset($pixData['expiration']) || isset($pixData['expiration_date'])) {
                    $pixExpiration = $pixData['expiration_date'] ?? $pixData['expiration'] ?? null;
                }
            }
        }

        // Extract customer data
        $customerData = $transaction->customer_data ?? [];

        // Determine real status (considering retention)
        $realStatus = $transaction->is_retained ? 'pending' : $transaction->status;
        
        // If transaction is expired, adjust status
        if ($transaction->expires_at && $transaction->expires_at->isPast() && $realStatus === 'pending') {
            $realStatus = 'expired';
        }

        // Load gateway if not loaded
        if (!$transaction->relationLoaded('gateway')) {
            $transaction->load('gateway');
        }

        // Format complete response
        $response = [
            'id' => $transaction->transaction_id,
            'transaction_id' => $transaction->transaction_id,
            'external_id' => $transaction->external_id,
            'amount' => (float) $transaction->amount,
            'fee_amount' => (float) $transaction->fee_amount,
            'net_amount' => (float) $transaction->net_amount,
            'currency' => $transaction->currency ?? 'BRL',
            'payment_method' => $transaction->payment_method,
            'status' => $realStatus,
            'is_retained' => (bool) $transaction->is_retained,
            'gateway' => $transaction->gateway ? [
                'id' => $transaction->gateway->id,
                'name' => $transaction->gateway->name,
                'slug' => $transaction->gateway->slug,
                'type' => $transaction->gateway->getConfig('gateway_type'),
            ] : null,
            'customer' => [
                'name' => $customerData['name'] ?? null,
                'email' => $customerData['email'] ?? null,
                'document' => $customerData['document'] ?? null,
                'phone' => $customerData['phone'] ?? null,
            ],
            'description' => $transaction->description,
            'expires_at' => $transaction->expires_at ? $transaction->expires_at->toISOString() : null,
            'paid_at' => $transaction->paid_at ? $transaction->paid_at->toISOString() : null,
            'refunded_at' => $transaction->refunded_at ? $transaction->refunded_at->toISOString() : null,
            'created_at' => $transaction->created_at->toISOString(),
            'updated_at' => $transaction->updated_at->toISOString(),
        ];

        // Add PIX data if PIX payment
        if ($transaction->payment_method === 'pix') {
            $response['pix'] = [
                'qr_code' => $pixCode,
                'payload' => $pixPayload ?? $pixCode,
                'qrcode' => $pixQrCode ?? $pixCode, // Alias for compatibility
                'end_to_end_id' => $endToEndId,
                'txid' => $pixTxId ?? $transaction->external_id,
                'expiration_date' => $pixExpiration ?? ($transaction->expires_at ? $transaction->expires_at->toISOString() : null),
            ];
        } else {
            $response['pix'] = null;
        }

        return $response;
    }
}





