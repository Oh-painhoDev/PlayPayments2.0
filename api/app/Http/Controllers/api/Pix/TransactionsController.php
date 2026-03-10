<?php

namespace App\Http\Controllers\api\Pix;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{
    /**
     * Listar todas as vendas (transações)
     * 
     * GET /v1/transactions
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

            // Parâmetros de paginação e filtros
            $perPage = $request->get('per_page', 15);
            $status = $request->get('status');
            $paymentMethod = $request->get('payment_method');
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $search = $request->get('search');

            // Carregar gateway do usuário para logs
            $user->load('assignedGateway');
            
            Log::info('Listando transações via API v1', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gateway_id' => $user->assignedGateway->id ?? null,
                'gateway_name' => $user->assignedGateway->name ?? null,
                'gateway_slug' => $user->assignedGateway->slug ?? null,
            ]);

            // Query base
            $query = Transaction::where('user_id', $user->id)
                ->with('gateway')
                ->orderBy('created_at', 'desc');

            // Filtros
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

            // Paginação
            $transactions = $query->paginate($perPage);

            // Formatar resposta
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
            Log::error('Erro ao listar transações via API', [
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
     * Buscar uma venda específica
     * 
     * GET /v1/transactions/{id}
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

            // Buscar transação por transaction_id ou external_id
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

        // Se for PIX pendente, verificar status atualizado no gateway antes de retornar
        if ($transaction->payment_method === 'pix' && $transaction->status === 'pending' && $transaction->external_id) {
            try {
                $gatewayType = $transaction->gateway ? $transaction->gateway->getConfig('gateway_type') : null;
                $gatewaySlug = $transaction->gateway ? $transaction->gateway->slug : null;
                
                // Verificar status no gateway (Shark ou Pluggou)
                if ($transaction->gateway && (in_array($gatewayType, ['sharkgateway', 'pluggou']) || $gatewaySlug === 'pluggou')) {
                    // Verificar status atualizado no gateway
                    $paymentService = new \App\Services\PaymentGatewayService($transaction->gateway);
                    $statusResult = $paymentService->checkTransactionStatus($transaction);
                    
                    // Se for Pluggou e não tiver endpoint GET, o resultado pode ter note sobre webhook
                    // Isso é normal e não é erro
                    if (($gatewaySlug === 'pluggou' || $gatewayType === 'pluggou') && isset($statusResult['note'])) {
                        Log::info('Pluggou: Verificação via API não disponível, depende de webhook', [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                            'note' => $statusResult['note']
                        ]);
                    }
                    
                    // Recarregar transação para pegar dados atualizados
                    $transaction->refresh();
                }
            } catch (\Exception $e) {
                // Se der erro na verificação, continua e retorna os dados do banco
                Log::warning('Erro ao verificar status na busca da transação', [
                    'transaction_id' => $transaction->transaction_id,
                    'gateway' => $transaction->gateway->name ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Recarregar para garantir dados mais recentes
        $transaction->refresh();

            return response()->json([
                'success' => true,
                'data' => $this->formatTransaction($transaction)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar transação via API', [
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
     * Buscar venda por transaction_id ou external_id
     * 
     * GET /v1/transactions/search/{identifier}
     * 
     * @param Request $request
     * @param string $identifier - transaction_id ou external_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request, string $identifier)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            Log::info('Buscando venda via API v1', [
                'user_id' => $user->id,
                'identifier' => $identifier,
            ]);

            // Buscar por transaction_id ou external_id
            $transaction = Transaction::where('user_id', $user->id)
                ->where(function($query) use ($identifier) {
                    $query->where('transaction_id', $identifier)
                          ->orWhere('external_id', $identifier);
                })
                ->with('gateway')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Venda não encontrada',
                    'message' => 'Nenhuma venda encontrada com o identificador fornecido.'
                ], 404);
            }

            // Se for PIX pendente, verificar status atualizado no gateway
            if ($transaction->payment_method === 'pix' && $transaction->status === 'pending' && $transaction->external_id) {
                try {
                    if ($transaction->gateway) {
                        $paymentService = new PaymentGatewayService($transaction->gateway);
                        $paymentService->checkTransactionStatus($transaction);
                        $transaction->refresh();
                    }
                } catch (\Exception $e) {
                    Log::warning('Erro ao verificar status na busca da venda', [
                        'transaction_id' => $transaction->transaction_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatTransaction($transaction)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar venda via API', [
                'identifier' => $identifier,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar venda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Criar uma nova venda
     * 
     * POST /v1/transactions
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

            // Validação dos dados
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:0.01|max:999999.99',
                'payment_method' => 'required|in:pix,credit_card,bank_slip',
                'customer.name' => 'required|string|max:255',
                'customer.email' => 'required|email|max:255',
                'customer.document' => 'required|string|min:11|max:18',
                'customer.phone' => 'nullable|string|min:10|max:15',
                'customer.address' => 'nullable|array',
                'customer.address.street' => 'nullable|string|max:255',
                'customer.address.street_name' => 'nullable|string|max:255',
                'customer.address.streetName' => 'nullable|string|max:255',
                'customer.address.streetNumber' => 'nullable|string|max:20',
                'customer.address.number' => 'nullable|string|max:20',
                'customer.address.complement' => 'nullable|string|max:255',
                'customer.address.neighborhood' => 'nullable|string|max:255',
                'customer.address.district' => 'nullable|string|max:255',
                'customer.address.city' => 'nullable|string|max:255',
                'customer.address.state' => 'nullable|string|max:2',
                'customer.address.zipCode' => 'nullable|string|max:20',
                'customer.address.zip_code' => 'nullable|string|max:20',
                'customer.address.zipcode' => 'nullable|string|max:20',
                'customer.address.postalCode' => 'nullable|string|max:20',
                'customer.address.country' => 'nullable|string|max:2',
                'customer.street' => 'nullable|string|max:255',
                'customer.street_name' => 'nullable|string|max:255',
                'customer.streetName' => 'nullable|string|max:255',
                'customer.number' => 'nullable|string|max:20',
                'customer.streetNumber' => 'nullable|string|max:20',
                'customer.street_number' => 'nullable|string|max:20',
                'customer.complement' => 'nullable|string|max:255',
                'customer.neighborhood' => 'nullable|string|max:255',
                'customer.district' => 'nullable|string|max:255',
                'customer.city' => 'nullable|string|max:255',
                'customer.state' => 'nullable|string|max:2',
                'customer.zip_code' => 'nullable|string|max:20',
                'customer.zipCode' => 'nullable|string|max:20',
                'customer.zipcode' => 'nullable|string|max:20',
                'customer.postal_code' => 'nullable|string|max:20',
                'customer.postalCode' => 'nullable|string|max:20',
                'customer.country' => 'nullable|string|max:2',
                'sale_name' => 'required_without:products|string|max:255',
                'description' => 'required|string|max:500',
                'external_id' => 'nullable|string|max:255|unique:transactions,external_id',
                'expires_in' => 'nullable|integer|min:60|max:86400', // 1 minuto a 24 horas (em segundos)
                'pix_expires_in_minutes' => 'nullable|integer|min:1|max:129600', // 1 minuto a 90 dias (em minutos)
                'installments' => 'nullable|integer|min:1|max:12',
                'items' => 'nullable|array',
                'items.*.title' => 'required_with:items|string|max:255',
                'items.*.name' => 'nullable|string|max:255',
                'items.*.description' => 'nullable|string|max:1000',
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.unitPrice' => 'nullable|numeric|min:0.01',
                'items.*.price' => 'nullable|numeric|min:0.01',
                'items.*.tangible' => 'nullable|boolean',
                'products' => 'nullable|array', // Compatibilidade com formato antigo
                'products.*.title' => 'required_with:products|string|max:255',
                'products.*.name' => 'nullable|string|max:255',
                'products.*.description' => 'required_with:products|string|max:1000',
                'products.*.quantity' => 'nullable|integer|min:1',
                'products.*.unitPrice' => 'nullable|numeric|min:0.01',
                'products.*.price' => 'nullable|numeric|min:0.01',
                'products.*.tangible' => 'nullable|boolean',
                'shipping' => 'nullable|array',
                'shipping.fee' => 'nullable|numeric|min:0',
                'shipping.address.street' => 'required_with:shipping|string|max:255',
                'shipping.address.streetNumber' => 'required_with:shipping|string|max:20',
                'shipping.address.neighborhood' => 'required_with:shipping|string|max:255',
                'shipping.address.city' => 'required_with:shipping|string|max:255',
                'shipping.address.state' => 'required_with:shipping|string|size:2',
                'shipping.address.zipCode' => 'required_with:shipping|string|max:20',
                'shipping.address.country' => 'required_with:shipping|string|size:2',
                'shipping.address.complement' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Carregar gateway do usuário
            $user->load('assignedGateway');
            
            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                Log::warning('Tentativa de criar transação sem gateway configurado', [
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

            // Limpar documento
            $document = preg_replace('/[^0-9]/', '', $request->input('customer.document'));

            // Preparar dados da transação
            $transactionData = [
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'customer' => [
                    'name' => $request->input('customer.name'),
                    'email' => $request->input('customer.email'),
                    'document' => $document,
                ],
                'external_id' => $request->external_id ?? $this->generateExternalId(),
                'description' => $request->description ?? 'Sale via API',
                'installments' => $request->installments ?? 1,
                'metadata' => [
                    'created_via' => 'api_v1',
                    'user_ip' => $request->ip(),
                    'referer_url' => $request->header('Referer') ?? $request->header('Referrer') ?? null,
                    'origin_url' => $request->header('Origin') ?? null,
                    'user_agent' => $request->header('User-Agent') ?? null,
                    'request_url' => $request->fullUrl(),
                    'request_method' => $request->method(),
                    'api_version' => 'v1',
                    'request_timestamp' => now()->toIso8601String(),
                ],
            ];

            // Adicionar telefone se fornecido
            if ($request->has('customer.phone')) {
                $transactionData['customer']['phone'] = preg_replace('/[^0-9]/', '', $request->input('customer.phone'));
            }
            
            // Adicionar endereço normalizado se fornecido
            $normalizedAddress = $this->normalizeCustomerAddressData(
                $request->input('customer.address'),
                $request->input('customer', [])
            );
            
            if ($normalizedAddress) {
                $transactionData['customer']['address'] = $normalizedAddress;
            }

            // Adicionar produtos/items se fornecidos (priorizar items, depois products para compatibilidade)
            if ($request->has('items') && is_array($request->items)) {
                $transactionData['items'] = $request->items;
                $transactionData['products'] = $request->items; // Manter compatibilidade
            } elseif ($request->has('products') && is_array($request->products)) {
                $transactionData['products'] = $request->products;
                $transactionData['items'] = $request->products; // Manter compatibilidade
            }

            // Adicionar shipping/endereço se fornecido
            if ($request->has('shipping') && is_array($request->shipping)) {
                $transactionData['shipping'] = $request->shipping;
                Log::info('📦 Shipping recebido no controller', [
                    'shipping' => $request->shipping,
                    'has_address' => isset($request->shipping['address']),
                ]);
            } else {
                Log::debug('⚠️ Shipping não fornecido ou inválido', [
                    'has_shipping' => $request->has('shipping'),
                    'shipping_type' => $request->has('shipping') ? gettype($request->shipping) : 'not_set',
                ]);
            }

            // Configurar expiração PIX
            if ($request->payment_method === 'pix') {
                if ($request->has('pix_expires_in_minutes')) {
                    $transactionData['pix_expires_in_minutes'] = $request->pix_expires_in_minutes;
                } elseif ($request->has('expires_in')) {
                    // Converter segundos para minutos
                    $transactionData['pix_expires_in_minutes'] = (int)ceil($request->expires_in / 60);
                } else {
                    // Padrão: 15 minutos para PIX
                    $transactionData['pix_expires_in_minutes'] = 15;
                }
            }

            // Carregar gateway do usuário
            $user->load('assignedGateway');
            
            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum gateway de pagamento configurado para este usuário'
                ], 400);
            }

            // Criar serviço de pagamento
            $paymentService = new PaymentGatewayService($user->assignedGateway);

            Log::info('Creating transaction via API v1', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gateway_id' => $user->assignedGateway->id,
                'gateway_name' => $user->assignedGateway->name,
                'gateway_slug' => $user->assignedGateway->slug,
                'gateway_type' => $user->assignedGateway->getConfig('gateway_type'),
                'amount' => $transactionData['amount'],
                'payment_method' => $transactionData['payment_method'],
                'external_id' => $transactionData['external_id'],
            ]);

            // Criar transação
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success']) {
                Log::error('Falha ao criar transação via API v1', [
                    'user_id' => $user->id,
                    'error' => $result['error']
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'] ?? [];

            // Recarregar transação para garantir que temos os dados mais recentes (incluindo payment_data)
            $transaction->refresh();
            $transaction->load('gateway');

            // Verificar se temos os dados PIX necessários para pagamentos PIX
            if ($transaction->payment_method === 'pix') {
                $pixData = $gatewayResponse['payment_data']['pix'] ?? null;
                
                if (!$pixData || (empty($pixData['payload']) && empty($pixData['qrcode']) && empty($pixData['emv']))) {
                    Log::warning('Dados PIX não encontrados na resposta do gateway', [
                        'transaction_id' => $transaction->transaction_id,
                        'gateway_response' => $gatewayResponse,
                        'gateway_name' => $user->assignedGateway->name ?? 'unknown'
                    ]);
                    
                    // Se não encontrou no gatewayResponse, tentar garantir que está salvo no payment_data
                    // Isso pode acontecer se o gateway salvou mas não retornou na resposta
                    if (empty($transaction->payment_data)) {
                        Log::error('Pluggou: payment_data está vazio após criação', [
                            'transaction_id' => $transaction->transaction_id,
                            'external_id' => $transaction->external_id,
                        ]);
                    }
                } else {
                    Log::info('Dados PIX encontrados no gatewayResponse', [
                        'transaction_id' => $transaction->transaction_id,
                        'has_payload' => !empty($pixData['payload']),
                        'has_qrcode' => !empty($pixData['qrcode']),
                        'has_emv' => !empty($pixData['emv']),
                    ]);
                }
            }

            // Formatar e retornar resposta completa (formato v2)
            $formattedData = $this->formatTransaction($transaction);

            return response()->json($formattedData, 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar transação via API v1', [
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
     * Formatar transação para resposta da API v2
     * Retorna JSON completo no formato esperado
     * 
     * @param Transaction $transaction
     * @return array
     */
    private function formatTransaction(Transaction $transaction): array
    {
        // Extrair dados PIX se disponível
        $pixData = null;
        $pixCode = null;
        $pixQrCode = null;
        $pixPayload = null;
        $endToEndId = null;
        $pixTxId = null;
        $pixExpiration = null;

        if ($transaction->payment_method === 'pix' && $transaction->payment_data) {
            $paymentData = $transaction->payment_data;
            
            $pixData = $paymentData['payment_data']['pix'] ?? 
                       $paymentData['pix'] ?? 
                       $paymentData['data']['pix'] ?? null;
            
            if ($pixData) {
                $pixCode = $pixData['payload'] ?? 
                          $pixData['qrcode'] ?? 
                          $pixData['emv'] ?? 
                          $pixData['qr_code'] ?? 
                          $pixData['code'] ?? null;
                
                $pixQrCode = $pixData['qrcode'] ?? $pixCode;
                $pixPayload = $pixData['payload'] ?? $pixCode;
                
                $endToEndId = $pixData['endToEndId'] ?? 
                             $pixData['end_to_end_id'] ?? 
                             $pixData['endToEnd'] ?? 
                             $pixData['end_to_end'] ?? null;
                
                $pixTxId = $pixData['txid'] ?? 
                          $pixData['tx_id'] ?? 
                          $pixData['transaction_id'] ?? null;
                
                if (isset($pixData['expiration']) || isset($pixData['expiration_date'])) {
                    $pixExpiration = $pixData['expiration_date'] ?? $pixData['expiration'] ?? null;
                }
            }
        }

        // Extrair dados do cliente
        $customerData = $transaction->customer_data ?? [];
        $normalizedCustomerAddress = $this->normalizeCustomerAddressData(
            isset($customerData['address']) && is_array($customerData['address']) ? $customerData['address'] : null,
            $customerData
        );
        $user = $transaction->user ?? \App\Models\User::find($transaction->user_id);

        // Determinar status real (considerando retenção)
        $realStatus = $transaction->is_retained ? 'pending' : $transaction->status;
        
        // Mapear status para o formato esperado
        $statusMap = [
            'pending' => 'waiting_payment',
            'paid' => 'paid',
            'refunded' => 'refunded',
            'refused' => 'refused',
            'expired' => 'expired',
        ];
        $mappedStatus = $statusMap[$realStatus] ?? $realStatus;
        
        // Se a transação está expirada, ajustar status
        if ($transaction->expires_at && $transaction->expires_at->isPast() && $realStatus === 'pending') {
            $mappedStatus = 'expired';
        }

        // Gerar tenantId baseado no user_id (formato UUID-like)
        $tenantId = $this->generateTenantId($transaction->user_id);

        // Formatar items
        $items = [];
        if ($transaction->products && is_array($transaction->products)) {
            foreach ($transaction->products as $product) {
                // Se unitPrice/price já estiver em centavos, usar direto. Se estiver em reais, multiplicar por 100
                $unitPrice = $product['unitPrice'] ?? $product['price'] ?? 0;
                // Se o valor for menor que 100, provavelmente está em reais, então multiplicar por 100
                $unitPriceInCents = $unitPrice < 100 ? (int) ($unitPrice * 100) : (int) $unitPrice;
                
                $items[] = [
                    'title' => $product['title'] ?? $product['name'] ?? 'Produto',
                    'quantity' => $product['quantity'] ?? 1,
                    'tangible' => $product['tangible'] ?? false,
                    'unitPrice' => $unitPriceInCents,
                    'externalRef' => $product['externalRef'] ?? '',
                ];
            }
        } else {
            // Se não tiver produtos, criar um item padrão
            // amount já está em reais no banco, então multiplicar por 100 para centavos
            $items[] = [
                'title' => $transaction->description ?? 'Produto',
                'quantity' => 1,
                'tangible' => false,
                'unitPrice' => (int) ($transaction->amount * 100), // Converter para centavos
                'externalRef' => '',
            ];
        }

        // Formatar customer
        $documentNumber = preg_replace('/[^0-9]/', '', $customerData['document'] ?? '');
        $documentType = strlen($documentNumber) === 11 ? 'cpf' : (strlen($documentNumber) === 14 ? 'cnpj' : 'cpf');

        // Formatar resposta no formato esperado
        $response = [
            'id' => (int) $transaction->id,
            'tenantId' => $tenantId,
            'companyId' => (int) $transaction->user_id,
            'amount' => (int) ($transaction->amount * 100), // Converter para centavos
            'currency' => $transaction->currency ?? 'BRL',
            'paymentMethod' => $transaction->payment_method,
            'status' => $mappedStatus,
            'installments' => 1,
            'paidAt' => $transaction->paid_at ? $transaction->paid_at->toIso8601String() : null,
            'paidAmount' => $transaction->paid_at ? (int) ($transaction->amount * 100) : 0,
            'refundedAt' => $transaction->refunded_at ? $transaction->refunded_at->toIso8601String() : null,
            'refundedAmount' => (int) (($transaction->refunded_at ? $transaction->amount : 0) * 100),
            'redirectUrl' => null,
            'returnUrl' => null, // Removido para usuários comuns
            'postbackUrl' => null, // Removido para usuários comuns
            // 'metadata' => removido - apenas admin pode ver
            // 'ip' => removido - apenas admin pode ver
            'externalRef' => $transaction->external_id,
            'secureId' => $transaction->transaction_id,
            'secureUrl' => $transaction->transaction_id,
            'createdAt' => $transaction->created_at->toIso8601String(),
            'updatedAt' => $transaction->updated_at->toIso8601String(),
            'payer' => null,
            'traceable' => false,
            'authorizationCode' => null,
            'basePrice' => null,
            'interestRate' => null,
            'items' => $items,
            'customer' => [
                'id' => null, // Pode ser implementado se houver tabela de customers
                'name' => $customerData['name'] ?? null,
                'email' => $customerData['email'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'birthdate' => null,
                'createdAt' => $transaction->created_at->toIso8601String(),
                'externalRef' => null,
                'document' => [
                    'type' => $documentType,
                    'number' => $documentNumber,
                ],
                'address' => $this->transformAddressForResponse($normalizedCustomerAddress),
            ],
            'fee' => [
                'netAmount' => (int) (($transaction->net_amount ?? $transaction->amount) * 100),
                'estimatedFee' => (int) (($transaction->fee_amount ?? 0) * 100),
                'fixedAmount' => 0,
                'spreadPercent' => 0,
                'currency' => $transaction->currency ?? 'BRL',
            ],
            'splits' => [
                [
                    'amount' => (int) ($transaction->amount * 100),
                    'netAmount' => (int) (($transaction->net_amount ?? $transaction->amount) * 100),
                    'recipientId' => (int) $transaction->user_id,
                    'chargeProcessingFee' => false,
                ],
            ],
            'refunds' => [],
            'pix' => null,
            'boleto' => null,
            'card' => null,
            'refusedReason' => null,
            'shipping' => $transaction->shipping_address ? $transaction->shipping_address : null,
            'delivery' => null,
            'threeDS' => [
                'redirectUrl' => null,
                'returnUrl' => null, // Removido para usuários comuns
            ],
        ];

        // Adicionar dados PIX se for pagamento PIX
        if ($transaction->payment_method === 'pix' && $pixCode) {
            $expirationDate = $pixExpiration ?? ($transaction->expires_at ? $transaction->expires_at->format('Y-m-d') : null);
            
            $response['pix'] = [
                'qrcode' => $pixCode,
                'end2EndId' => $endToEndId,
                'receiptUrl' => null,
                'expirationDate' => $expirationDate,
            ];
        }

        return $response;
    }

    /**
     * Normalize different address formats into a single structure
     */
    private function normalizeCustomerAddressData(?array $addressInput, array $customerInput = []): ?array
    {
        $sources = [];
        if (is_array($addressInput)) {
            $sources[] = $addressInput;
        }
        if (!empty($customerInput)) {
            $sources[] = $customerInput;
        }
        
        if (empty($sources)) {
            return null;
        }
        
        $street = $this->firstFilled(['street', 'street_name', 'streetName', 'address_line1', 'line1'], ...$sources);
        $number = $this->firstFilled(['number', 'streetNumber', 'street_number'], ...$sources);
        $complement = $this->firstFilled(['complement', 'address_line2', 'line2'], ...$sources);
        $neighborhood = $this->firstFilled(['neighborhood', 'district', 'bairro'], ...$sources);
        $city = $this->firstFilled(['city', 'cidade'], ...$sources);
        $state = $this->firstFilled(['state', 'estado'], ...$sources);
        $zip = $this->firstFilled(['zip_code', 'zipCode', 'zipcode', 'postalCode', 'postal_code', 'cep', 'zip'], ...$sources);
        $country = $this->firstFilled(['country', 'country_code'], ...$sources);
        
        $normalized = [];
        
        if ($street) {
            $normalized['street'] = $street;
            $normalized['street_name'] = $street;
            $normalized['streetName'] = $street;
            $normalized['line1'] = $street;
        }
        
        if ($number) {
            $normalized['number'] = $number;
            $normalized['streetNumber'] = $number;
            $normalized['street_number'] = $number;
        }
        
        if ($complement) {
            $normalized['complement'] = $complement;
            $normalized['line2'] = $complement;
        }
        
        if ($neighborhood) {
            $normalized['neighborhood'] = $neighborhood;
            $normalized['district'] = $neighborhood;
            $normalized['bairro'] = $neighborhood;
        }
        
        if ($city) {
            $normalized['city'] = $city;
        }
        
        if ($state) {
            $normalized['state'] = strtoupper($state);
        }
        
        if ($zip) {
            $zipDigits = preg_replace('/[^0-9]/', '', $zip);
            $zipValue = $zipDigits ?: $zip;
            $normalized['zip_code'] = $zipValue;
            $normalized['zipCode'] = $zipValue;
            $normalized['zipcode'] = $zipValue;
            $normalized['postalCode'] = $zipValue;
        }
        
        if ($country) {
            $normalized['country'] = strtoupper($country);
            $normalized['country_code'] = strtoupper($country);
        }
        
        return empty($normalized) ? null : $normalized;
    }

    /**
     * Get the first non-empty value from a list of keys across sources
     */
    private function firstFilled(array $keys, array ...$sources): ?string
    {
        foreach ($sources as $source) {
            foreach ($keys as $key) {
                if (array_key_exists($key, $source) && $source[$key] !== null && $source[$key] !== '') {
                    return is_string($source[$key]) ? trim($source[$key]) : trim((string) $source[$key]);
                }
            }
        }
        
        return null;
    }

    /**
     * Prepare address payload for API responses
     */
    private function transformAddressForResponse(?array $address): ?array
    {
        if (!$address || !is_array($address)) {
            return null;
        }
        
        $formatted = $this->formatAddressLine($address);
        
        $payload = array_filter([
            'street_name' => $address['street'] ?? $address['street_name'] ?? $address['streetName'] ?? null,
            'number' => $address['number'] ?? $address['streetNumber'] ?? $address['street_number'] ?? null,
            'complement' => $address['complement'] ?? null,
            'neighborhood' => $address['neighborhood'] ?? $address['district'] ?? null,
            'city' => $address['city'] ?? null,
            'state' => $address['state'] ?? null,
            'zip_code' => $address['zip_code'] ?? $address['zipCode'] ?? $address['zipcode'] ?? $address['postalCode'] ?? null,
            'country' => $address['country'] ?? $address['country_code'] ?? null,
            'formatted' => $formatted ?: null,
        ], function ($value) {
            return $value !== null && $value !== '';
        });
        
        return empty($payload) ? null : $payload;
    }

    /**
     * Build a single-line formatted address for display
     */
    private function formatAddressLine(array $address): ?string
    {
        $segments = [];
        
        $street = $address['street'] ?? $address['street_name'] ?? $address['streetName'] ?? null;
        $number = $address['number'] ?? $address['streetNumber'] ?? $address['street_number'] ?? null;
        $complement = $address['complement'] ?? null;
        
        if ($street) {
            $line = $street;
            if ($number) {
                $line .= ', ' . $number;
            }
            if ($complement) {
                $line .= ' - ' . $complement;
            }
            $segments[] = $line;
        }
        
        $neighborhood = $address['neighborhood'] ?? $address['district'] ?? null;
        $city = $address['city'] ?? null;
        $state = $address['state'] ?? null;
        
        $cityLineParts = [];
        if ($neighborhood) {
            $cityLineParts[] = $neighborhood;
        }
        if ($city || $state) {
            $cityState = $city ?? '';
            if ($state) {
                $state = strtoupper($state);
                $cityState = $cityState ? "{$cityState} - {$state}" : $state;
            }
            if ($cityState) {
                $cityLineParts[] = $cityState;
            }
        }
        
        if (!empty($cityLineParts)) {
            $segments[] = implode(', ', $cityLineParts);
        }
        
        $zip = $address['zip_code'] ?? $address['zipCode'] ?? $address['zipcode'] ?? $address['postalCode'] ?? null;
        if ($zip) {
            $segments[] = $zip;
        }
        
        return empty($segments) ? null : implode(' | ', $segments);
    }

    /**
     * Criar PIX de teste usando o gateway configurado do usuário
     * 
     * POST /v2/transactions/test-pix
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testPix(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Não autorizado.'
                ], 401);
            }

            // Carregar gateway do usuário
            $user->load('assignedGateway');
            
            // Verificar se usuário tem gateway configurado
            if (!$user->assignedGateway) {
                return response()->json([
                    'success' => false,
                    'error' => 'Nenhum gateway de pagamento configurado para este usuário',
                    'message' => 'Configure um gateway de pagamento no painel administrativo antes de criar transações.'
                ], 400);
            }

            // Valor padrão ou do request (em reais)
            $amount = $request->input('amount', 10.00);
            
            // Tempo de expiração PIX (em minutos)
            $pixExpiresInMinutes = $request->input('pix_expires_in_minutes', 15);

            // Dados do cliente (usar dados do usuário ou do request)
            $customerName = $request->input('customer.name', $user->name);
            $customerEmail = $request->input('customer.email', $user->email);
            $customerDocument = $request->input('customer.document', preg_replace('/[^0-9]/', '', $user->document ?? '00000000000'));
            $customerPhone = $request->input('customer.phone', preg_replace('/[^0-9]/', '', $user->whatsapp ?? null));

            // Preparar dados da transação
            $transactionData = [
                'amount' => (float) $amount,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $customerName,
                    'email' => $customerEmail,
                    'document' => $customerDocument,
                ],
                'external_id' => 'TEST-' . $this->generateExternalId(),
                'description' => $request->input('description', 'PIX de Teste - API v2'),
                'metadata' => [
                    'test' => true,
                    'created_via' => 'api_v2_test',
                    'user_ip' => $request->ip(),
                    'referer_url' => $request->header('Referer') ?? $request->header('Referrer') ?? null,
                    'origin_url' => $request->header('Origin') ?? null,
                    'user_agent' => $request->header('User-Agent') ?? null,
                    'request_url' => $request->fullUrl(),
                    'request_method' => $request->method(),
                    'api_version' => 'v2',
                    'request_timestamp' => now()->toIso8601String(),
                ],
                'pix_expires_in_minutes' => (int) $pixExpiresInMinutes,
            ];

            // Adicionar telefone se fornecido
            if ($customerPhone) {
                $transactionData['customer']['phone'] = $customerPhone;
            }

            Log::info('🧪 TEST PIX v2: Criando transação de teste', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'gateway_id' => $user->assignedGateway->id,
                'gateway_name' => $user->assignedGateway->name,
                'gateway_slug' => $user->assignedGateway->slug,
                'amount' => $amount,
                'pix_expires_in_minutes' => $pixExpiresInMinutes,
            ]);

            // Criar serviço de pagamento
            $paymentService = new PaymentGatewayService($user->assignedGateway);

            // Criar transação
            $result = $paymentService->createTransaction($user, $transactionData);

            if (!$result['success']) {
                Log::error('Falha ao criar PIX de teste via API v2', [
                    'user_id' => $user->id,
                    'error' => $result['error']
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $result['error'] ?? 'Erro ao criar transação PIX de teste',
                ], 400);
            }

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'] ?? [];

            // Recarregar transação para garantir dados atualizados
            $transaction->refresh();

            // Formatar resposta no formato v2
            $formattedData = $this->formatTransaction($transaction);

            return response()->json($formattedData, 201);

        } catch (\Exception $e) {
            Log::error('Erro ao criar PIX de teste via API v2', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error creating test PIX: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar tenantId baseado no user_id
     * 
     * @param int $userId
     * @return string
     */
    private function generateTenantId(int $userId): string
    {
        // Gerar UUID-like baseado no user_id para manter consistência
        $hash = md5('tenant_' . $userId);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12)
        );
    }

    /**
     * Generate elegant external ID
     * Format: VCP-YYYYMMDD-HHMMSS-XXXXX
     * Example: VCP-20251117-143052-A7B9C
     */
    private function generateExternalId(): string
    {
        $date = now();
        $datePart = $date->format('Ymd-His'); // YYYYMMDD-HHMMSS
        $randomPart = strtoupper(substr(md5(uniqid(rand(), true)), 0, 5)); // 5 caracteres aleatórios
        
        return "VCP-{$datePart}-{$randomPart}";
    }
}

