<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\PaymentGateway;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesController extends Controller
{
    /**
     * Show sales page with transactions
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Set default date range if not provided
            if (!$request->filled('date_from')) {
                $request->merge(['date_from' => Carbon::now()->subDays(6)->format('Y-m-d')]);
            }
            
            if (!$request->filled('date_to')) {
                $request->merge(['date_to' => Carbon::now()->format('Y-m-d')]);
            }
            
            // Get transactions with filters
            $query = Transaction::where('user_id', $user->id)->with(['gateway', 'user']);
            
            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('transaction_id', 'like', "%{$search}%")
                      ->orWhere('external_id', 'like', "%{$search}%")
                      ->orWhereJsonContains('customer_data->name', $search)
                      ->orWhereJsonContains('customer_data->email', $search);
                });
            }
            
            if ($request->filled('status')) {
                // If filtering by status, handle retained transactions specially
                if ($request->status === 'pending') {
                    $query->where(function($q) {
                        $q->where('status', 'pending')
                          ->orWhere('is_retained', true);
                    });
                } else {
                    // For other statuses, exclude retained transactions
                    $query->where('status', $request->status)
                          ->where('is_retained', false);
                }
            }
            
            if ($request->filled('payment_method')) {
                $query->where('payment_method', $request->payment_method);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            // Always order by created_at (newest first)
            $query->orderBy('created_at', 'desc');
            
            $transactions = $query->paginate(20);
            
            // Calculate summary stats with simple queries
            $summaryStats = $this->calculateSalesSummaryStats($user, $request);
            
            extract($summaryStats);
            // Use paid_count from stats, not pagination total (which is filtered)
            $totalTransactions = $transactions->total();
            $paidTransactionsCount = $paidCount ?? 0; // Número real de transações pagas
            
            return view('transactions.index', compact(
                'transactions',
                'totalAmount',
                'totalTransactions',
                'paidTransactionsCount',
                'paidAmount',
                'pendingAmount',
                'refundPercentage',
                'user'
            ));
            
        } catch (\Exception $e) {
            Log::error('Erro na página de vendas: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback with empty data - criar paginator vazio corretamente
            $user = Auth::user();
            $transactions = Transaction::where('id', 0)->paginate(20);
            $totalAmount = 0;
            $totalTransactions = 0;
            $paidAmount = 0;
            $pendingAmount = 0;
            $refundPercentage = 0;
            $paidCount = 0;
            
            return view('sales.index', compact(
                'transactions',
                'totalAmount',
                'totalTransactions', 
                'paidTransactionsCount',
                'paidAmount',
                'pendingAmount',
                'refundPercentage',
                'user'
            ))->with('error', 'Erro ao carregar dados de vendas');
        }
    }
    
    /**
     * Calculate sales summary stats with optimized single query (combate latência de 200ms do banco)
     */
    private function calculateSalesSummaryStats($user, $request)
    {
        try {
            // Get all possible paid statuses
            $paidStatuses = [
                'paid',
                'paid_out',
                'paidout',
                'completed',
                'success',
                'successful',
                'approved',
                'confirmed',
                'settled',
                'captured'
            ];
            
            // OTIMIZADO: Uma única query agregada ao invés de 5 queries separadas
            // Reduz de 5x200ms = 1000ms para 1x200ms = 200ms (5x mais rápido!)
            $query = DB::table('transactions')
                ->select([
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw("SUM(CASE WHEN status IN ('" . implode("','", $paidStatuses) . "') AND is_retained = false THEN amount ELSE 0 END) as paid_amount"),
                    DB::raw("SUM(CASE WHEN (status = 'pending' OR is_retained = true) THEN amount ELSE 0 END) as pending_amount"),
                    DB::raw("COUNT(CASE WHEN status IN ('" . implode("','", $paidStatuses) . "') AND is_retained = false THEN 1 END) as paid_count"),
                    DB::raw("COUNT(CASE WHEN status IN ('refunded', 'partially_refunded', 'chargeback') AND is_retained = false THEN 1 END) as refunded_count")
                ])
                ->where('user_id', $user->id);
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $stats = $query->first();
            
            $totalAmount = $stats->total_amount ?? 0;
            $paidAmount = $stats->paid_amount ?? 0;
            $pendingAmount = $stats->pending_amount ?? 0;
            $paidCount = $stats->paid_count;
            $refundedCount = $stats->refunded_count;
            
            // Calculate refund percentage
            $refundPercentage = $paidCount > 0 ? ($refundedCount / $paidCount) * 100 : 0;
            
            return compact('totalAmount', 'paidAmount', 'pendingAmount', 'refundPercentage', 'paidCount');
            
        } catch (\Exception $e) {
            Log::error('Erro ao calcular estatísticas de vendas: ' . $e->getMessage());
            
            return [
                'totalAmount' => 0,
                'paidAmount' => 0,
                'pendingAmount' => 0,
                'refundPercentage' => 0
            ];
        }
    }
    
    /**
     * Show transaction details
     */
    public function show(string $transactionId)
    {
        try {
            $user = Auth::user();
            
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->with(['gateway', 'user'])
                ->firstOrFail();
            
            return view('transactions.show', compact('transaction', 'user'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar transação: ' . $e->getMessage());
            return redirect()->route('sales.index')->with('error', 'Transação não encontrada');
        }
    }
    
    /**
     * Generate receipt PDF
     */
    public function receipt(string $transactionId)
    {
        try {
            $user = Auth::user();
            
            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $user->id)
                ->with(['gateway', 'user'])
                ->firstOrFail();
            
            // Get PIX payload
            $pixPayload = $transaction->payment_data['payment_data']['pix']['payload'] ?? 
                          $transaction->payment_data['payment_data']['pix']['qrcode'] ?? 
                          $transaction->payment_data['payment_data']['pix']['emv'] ??
                          $transaction->payment_data['pix']['payload'] ?? 
                          $transaction->payment_data['pix']['qrcode'] ?? 
                          $transaction->payment_data['pix']['emv'] ?? null;
            
            // Get End to End ID
            $endToEndId = $transaction->payment_data['payment_data']['pix']['endToEndId'] ?? 
                          $transaction->payment_data['payment_data']['pix']['end_to_end_id'] ?? 
                          $transaction->payment_data['pix']['endToEndId'] ?? 
                          $transaction->payment_data['pix']['end_to_end_id'] ?? 
                          null;
            
            // Get authentication code
            $authCode = $transaction->payment_data['payment_data']['pix']['authenticationCode'] ?? 
                        $transaction->payment_data['payment_data']['pix']['authentication_code'] ?? 
                        $transaction->payment_data['payment_data']['pix']['auth_code'] ?? 
                        $transaction->payment_data['pix']['authenticationCode'] ?? 
                        $transaction->payment_data['pix']['authentication_code'] ?? 
                        $transaction->payment_data['pix']['auth_code'] ?? 
                        '-';
            
            // Get customer address if available
            $customerAddress = null;
            if (isset($transaction->customer_data['address']) && is_array($transaction->customer_data['address'])) {
                $customerAddress = $transaction->customer_data['address'];
            } elseif (isset($transaction->customer_data['address']) && is_string($transaction->customer_data['address'])) {
                // Try to parse address string (format: "Rua, Número - Bairro - Complemento" or "Rua, Número, Bairro")
                $addressString = $transaction->customer_data['address'];
                $addressParts = explode(', ', $addressString);
                
                $customerAddress = [
                    'street' => $addressParts[0] ?? '',
                    'number' => $addressParts[1] ?? '',
                ];
                
                // Try to extract neighborhood and complement
                if (isset($addressParts[2])) {
                    $restParts = explode(' - ', $addressParts[2]);
                    $customerAddress['neighborhood'] = $restParts[0] ?? '';
                    $customerAddress['complement'] = $restParts[1] ?? null;
                } else {
                    $customerAddress['neighborhood'] = '';
                    $customerAddress['complement'] = null;
                }
                
                $customerAddress['city'] = $transaction->customer_data['city'] ?? '';
                $customerAddress['state'] = $transaction->customer_data['state'] ?? '';
                $zipFromCustomer = $transaction->customer_data['zipcode'] 
                    ?? $transaction->customer_data['zip_code'] 
                    ?? $transaction->customer_data['zipCode'] 
                    ?? $transaction->customer_data['postalCode'] 
                    ?? $transaction->customer_data['cep'] 
                    ?? '';
                if ($zipFromCustomer) {
                    $customerAddress['zipcode'] = $zipFromCustomer;
                    $customerAddress['zip_code'] = $zipFromCustomer;
                }
            } else {
                // Try to get individual address fields
                if (isset($transaction->customer_data['street']) || isset($transaction->customer_data['address_line1'])) {
                    $zipFromCustomer = $transaction->customer_data['zipcode'] 
                        ?? $transaction->customer_data['zip_code'] 
                        ?? $transaction->customer_data['zipCode'] 
                        ?? $transaction->customer_data['postalCode'] 
                        ?? $transaction->customer_data['cep'] 
                        ?? '';
                    $customerAddress = [
                        'street' => $transaction->customer_data['street'] ?? $transaction->customer_data['street_name'] ?? $transaction->customer_data['streetName'] ?? $transaction->customer_data['address_line1'] ?? '',
                        'number' => $transaction->customer_data['number'] ?? $transaction->customer_data['address_line2'] ?? '',
                        'neighborhood' => $transaction->customer_data['neighborhood'] ?? $transaction->customer_data['district'] ?? '',
                        'complement' => $transaction->customer_data['complement'] ?? null,
                        'city' => $transaction->customer_data['city'] ?? '',
                        'state' => $transaction->customer_data['state'] ?? '',
                        'zipcode' => $zipFromCustomer,
                        'zip_code' => $zipFromCustomer,
                    ];
                }
            }
            
            // Format customer document
            $customerDocument = $transaction->customer_data['document'] ?? '';
            $customerDocumentClean = preg_replace('/[^0-9]/', '', $customerDocument);
            if (strlen($customerDocumentClean) == 11) {
                $customerDocument = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $customerDocumentClean);
            } elseif (strlen($customerDocumentClean) == 14) {
                $customerDocument = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $customerDocumentClean);
            }
            
            // Format user document (favorecido)
            $favoredDocument = $user->document ?? '';
            $favoredDocumentClean = preg_replace('/[^0-9]/', '', $favoredDocument);
            if (strlen($favoredDocumentClean) == 11) {
                $favoredDocument = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $favoredDocumentClean);
            } elseif (strlen($favoredDocumentClean) == 14) {
                $favoredDocument = preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $favoredDocumentClean);
            }
            
            // Format user phone
            $favoredPhone = $user->whatsapp ?? '';
            if ($favoredPhone && strlen(preg_replace('/[^0-9]/', '', $favoredPhone)) >= 10) {
                $favoredPhoneClean = preg_replace('/[^0-9]/', '', $favoredPhone);
                if (strlen($favoredPhoneClean) == 11) {
                    $favoredPhone = preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $favoredPhoneClean);
                } elseif (strlen($favoredPhoneClean) == 10) {
                    $favoredPhone = preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $favoredPhoneClean);
                }
            }
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('transactions.receipt', [
                'transaction' => $transaction,
                'user' => $user,
                'pixPayload' => $pixPayload,
                'endToEndId' => $endToEndId,
                'authCode' => $authCode,
                'customerAddress' => $customerAddress,
                'customerDocument' => $customerDocument,
                'favoredDocument' => $favoredDocument,
                'favoredPhone' => $favoredPhone,
            ]);
            
            return $pdf->download('comprovante-' . $transaction->transaction_id . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar comprovante: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('transactions.show', $transactionId)->with('error', 'Erro ao gerar comprovante');
        }
    }
    
    /**
     * Create new sale
     */
    public function create()
    {
        try {
            $user = Auth::user();
            
            // Check if user has gateway configured
            if (!$user->assignedGateway) {
                return redirect()->route('sales.index')
                    ->with('error', 'Você precisa ter um liquidante configurado para criar vendas. Entre em contato com o suporte.');
            }
            
            return view('sales.create', compact('user'));
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar página de criação de venda: ' . $e->getMessage());
            return redirect()->route('sales.index')->with('error', 'Erro ao carregar página');
        }
    }
    
    /**
     * Store new sale
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'payment_method' => 'required|in:pix,credit_card,bank_slip',
                'sale_name' => 'required|string|max:255',
                'description' => 'required|string|max:500',
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_document' => 'required|string|min:11|max:18',
                'customer_phone' => 'nullable|string|min:10|max:15',
                'installments' => 'nullable|integer|min:1|max:12',
                'pix_expires_in_minutes' => 'nullable|integer|min:1|max:129600', // 1 minuto a 90 dias (129,600 minutos)
                'pix_expires_in_days' => 'nullable|integer|min:1|max:90', // 1 a 90 dias
                'products' => 'nullable|array',
                'products.*.title' => 'required_with:products|string|max:255',
                'products.*.name' => 'nullable|string|max:255',
                'products.*.description' => 'nullable|string|max:500',
                'products.*.quantity' => 'nullable|integer|min:1',
                'products.*.unitPrice' => 'nullable|numeric|min:0.01',
                'products.*.price' => 'nullable|numeric|min:0.01',
                'products.*.tangible' => 'nullable|boolean',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            // Check if user has gateway configured
            if (!$user->assignedGateway) {
                return back()->withErrors(['error' => 'Gateway não configurado. Entre em contato com o suporte.'])->withInput();
            }
            
            // Clean document
            $document = preg_replace('/[^0-9]/', '', $request->customer_document);
            
            // Prepare customer data
            $customerData = [
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'document' => $document,
                'phone' => preg_replace('/[^0-9]/', '', $request->customer_phone ?? ''),
            ];
            
            // Prepare transaction data
            $transactionData = [
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'sale_name' => $request->sale_name ?? 'Venda',
                'description' => $request->description ?? '',
                'customer' => $customerData,
                'installments' => $request->installments ?? 1,
                'metadata' => [
                    'created_via' => 'sales_panel',
                    'user_ip' => $request->ip(),
                    'sale_name' => $request->sale_name ?? 'Venda', // Salvar sale_name no metadata para exibição
                    'description' => $request->description ?? '', // Salvar description no metadata também
                ],
            ];
            
            // Add PIX expiration time if provided
            if ($request->payment_method === 'pix') {
                // Priority: pix_expires_in_days (converted to minutes), then pix_expires_in_minutes, default 15 minutes
                if ($request->filled('pix_expires_in_days')) {
                    $days = (int)$request->pix_expires_in_days;
                    // Convert days to minutes (1 day = 1440 minutes)
                    $transactionData['pix_expires_in_minutes'] = $days * 1440;
                } elseif ($request->filled('pix_expires_in_minutes')) {
                    $transactionData['pix_expires_in_minutes'] = (int)$request->pix_expires_in_minutes;
                } else {
                    $transactionData['pix_expires_in_minutes'] = 15; // Default: 15 minutes
                }
            }
            
            // Add products if provided, otherwise create single item from sale_name and description
            if ($request->has('products') && is_array($request->products) && !empty($request->products)) {
                $transactionData['products'] = $request->products;
            } else {
                // Create single product item from sale_name
                $transactionData['products'] = [
                    [
                        'title' => $request->sale_name ?? 'Venda',
                        'name' => $request->sale_name ?? 'Venda',
                        'description' => $request->description ?? ($request->sale_name ?? 'Venda'),
                        'quantity' => 1,
                        'price' => $request->amount,
                        'unitPrice' => $request->amount,
                        'tangible' => false,
                    ]
                ];
            }
            
            // Check if multi-gateway is enabled for this user
            $isMultiGatewayEnabled = \App\Models\MultiGatewayConfig::isEnabledForUser($user->id);
            
            if ($isMultiGatewayEnabled && $request->payment_method === 'pix') {
                // Multi-gateway mode: create transactions in multiple gateways
                $multiTransactions = $this->createMultiGatewayTransactions($user, $transactionData);
                
                if ($multiTransactions && count($multiTransactions) > 0) {
                    // Return first successful transaction
                    $transaction = $multiTransactions[0]['transaction'];
                    $gatewayResponse = $multiTransactions[0]['gateway_response'] ?? [];
                    
                    Log::info('Multi-gateway transaction created', [
                        'user_id' => $user->id,
                        'total_gateways' => count($multiTransactions)
                    ]);
                } else {
                    // Fallback to single gateway
                    $paymentService = new PaymentGatewayService($user->assignedGateway);
                    $result = $paymentService->createTransaction($user, $transactionData);
                    
                    if (!$result['success']) {
                        $errorMessage = $result['error'];
                        
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'error' => $errorMessage
                            ], 400);
                        }
                        
                        return back()->withErrors(['error' => $errorMessage])->withInput();
                    }
                    
                    $transaction = $result['transaction'];
                    $gatewayResponse = $result['gateway_response'] ?? [];
                }
            } else {
                // Single gateway mode
                $paymentService = new PaymentGatewayService($user->assignedGateway);
                
                // Create transaction
                $result = $paymentService->createTransaction($user, $transactionData);
                
                if (!$result['success']) {
                    // Try retry gateway if configured
                    $retryResult = $this->tryRetryGateway($user, $transactionData);
                    
                    if ($retryResult && $retryResult['success']) {
                        Log::info('Transaction successful with retry gateway', [
                            'user_id' => $user->id,
                            'original_error' => $result['error']
                        ]);
                        $result = $retryResult;
                    } else {
                        $errorMessage = $result['error'];
                        
                        if ($request->ajax() || $request->wantsJson()) {
                            return response()->json([
                                'success' => false,
                                'error' => $errorMessage
                            ], 400);
                        }
                        
                        return back()->withErrors(['error' => $errorMessage])->withInput();
                    }
                }
                
                $transaction = $result['transaction'];
                $gatewayResponse = $result['gateway_response'] ?? [];
            }
                
                // Check if we have the necessary PIX data for PIX payments
                if ($transaction->payment_method === 'pix') {
                    $paymentData = $transaction->payment_data;
                    
                    if (!isset($paymentData['payment_data']['pix']['payload']) || 
                        empty($paymentData['payment_data']['pix']['payload'])) {
                        
                        Log::warning('PIX payload não encontrado na transação', [
                            'transaction_id' => $transaction->transaction_id,
                            'payment_data' => $paymentData
                        ]);
                        
                        // Delete the failed transaction
                        $transaction->delete();
                        
                        // Try retry gateway for PIX
                        $retryResult = $this->tryRetryGateway($user, $transactionData);
                        
                        if ($retryResult && $retryResult['success']) {
                            Log::info('PIX QR Code successful with retry gateway', [
                                'user_id' => $user->id
                            ]);
                            $transaction = $retryResult['transaction'];
                            $gatewayResponse = $retryResult['gateway_response'] ?? [];
                        } else {
                            $errorMessage = 'Não foi possível gerar o QR Code PIX. Por favor, tente novamente.';
                            
                            if ($request->ajax() || $request->wantsJson()) {
                                return response()->json([
                                    'success' => false,
                                    'error' => $errorMessage
                                ], 400);
                            }
                            
                            return back()->withErrors(['error' => $errorMessage])->withInput();
                        }
                    }
                }
                
                Log::info('Venda criada via painel', [
                    'user_id' => $user->id,
                    'transaction_id' => $transaction->transaction_id,
                    'external_id' => $transaction->external_id,
                    'amount' => $transaction->amount,
                    'payment_method' => $transaction->payment_method,
                    'is_retained' => $transaction->is_retained
                ]);
                
                // Enviar para UTMify se integração estiver ativa
                try {
                    $utmifyService = new \App\Services\UtmifyService();
                    $utmifyService->sendTransaction($transaction, 'created');
                } catch (\Exception $e) {
                    Log::error('Erro ao enviar transação para UTMify (created) - SalesController', [
                        'transaction_id' => $transaction->transaction_id,
                        'error' => $e->getMessage(),
                    ]);
                }
                
                // If AJAX request, return JSON
                if ($request->ajax() || $request->wantsJson()) {
                    // Format payment_data for frontend compatibility
                    $paymentDataForFrontend = $transaction->payment_data;
                    
                    // If payment_data has nested 'payment_data', extract it for frontend
                    // Frontend expects: payment_data.pix.payload
                    if (isset($paymentDataForFrontend['payment_data']['pix'])) {
                        $paymentDataForFrontend = [
                            'pix' => $paymentDataForFrontend['payment_data']['pix']
                        ];
                    }
                    
                    return response()->json([
                        'success' => true,
                        'transaction' => [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                            'amount' => $transaction->amount,
                            'formatted_amount' => $transaction->formatted_amount,
                            'payment_method' => $transaction->payment_method,
                            'status' => $transaction->status,
                            'payment_data' => $paymentDataForFrontend,
                            'details_url' => route('sales.show', $transaction->transaction_id)
                        ]
                    ]);
                }
                
                return redirect()->route('sales.show', $transaction->transaction_id)
                    ->with('success', 'Venda criada com sucesso!');
                    
        } catch (\Exception $e) {
            Log::error('❌ ERRO AO CRIAR VENDA', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            $errorMessage = 'Erro interno. Tente novamente.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }
    
    /**
     * Try retry gateway if configured
     */
    protected function tryRetryGateway($user, $transactionData)
    {
        // Check user's retry configuration first
        if ($user->retry_enabled && $user->retry_gateway_id) {
            $retryGateway = PaymentGateway::find($user->retry_gateway_id);
            if ($retryGateway && $retryGateway->is_active) {
                Log::info('Trying user retry gateway', [
                    'user_id' => $user->id,
                    'retry_gateway' => $retryGateway->name
                ]);
                
                $paymentService = new PaymentGatewayService($retryGateway);
                return $paymentService->createTransaction($user, $transactionData);
            }
        }
        
        // Check global retry configuration
        $retryConfig = \App\Models\RetryConfig::getGlobal();
        if ($retryConfig && $retryConfig->is_enabled && $retryConfig->retry_gateway_id) {
            $retryGateway = PaymentGateway::find($retryConfig->retry_gateway_id);
            if ($retryGateway && $retryGateway->is_active) {
                Log::info('Trying global retry gateway', [
                    'user_id' => $user->id,
                    'retry_gateway' => $retryGateway->name
                ]);
                
                $paymentService = new PaymentGatewayService($retryGateway);
                return $paymentService->createTransaction($user, $transactionData);
            }
        }
        
        return null;
    }

    /**
     * Create multi-gateway transactions
     */
    protected function createMultiGatewayTransactions($user, $transactionData)
    {
        $multiGateways = \App\Models\MultiGatewayConfig::getActiveGateways();
        
        if ($multiGateways->isEmpty()) {
            return null;
        }
        
        $transactions = [];
        $hasSuccess = false;
        
        foreach ($multiGateways as $gateway) {
            try {
                $paymentService = new PaymentGatewayService($gateway);
                $result = $paymentService->createTransaction($user, $transactionData);
                
                if ($result['success']) {
                    $transactions[] = [
                        'gateway' => $gateway->name,
                        'transaction' => $result['transaction'],
                        'gateway_response' => $result['gateway_response']
                    ];
                    $hasSuccess = true;
                    
                    Log::info('Multi-gateway transaction created', [
                        'gateway' => $gateway->name,
                        'transaction_id' => $result['transaction']->transaction_id
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Multi-gateway transaction failed', [
                    'gateway' => $gateway->name,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $hasSuccess ? $transactions : null;
    }
}
