<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * List all customers
     * 
     * GET /api/v1/customers
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

            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');

            // Get all transactions with customer_data
            $query = Transaction::where('user_id', $user->id)
                ->whereNotNull('customer_data')
                ->orderBy('created_at', 'desc');

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('customer_data->name', 'like', "%{$search}%")
                      ->orWhere('customer_data->email', 'like', "%{$search}%")
                      ->orWhere('customer_data->document', 'like', "%{$search}%")
                      ->orWhere('customer_data->phone', 'like', "%{$search}%");
                });
            }

            $transactions = $query->paginate($perPage);

            // Group unique customers by document
            $customersMap = [];
            
            foreach ($transactions as $transaction) {
                $customerData = $transaction->customer_data;
                
                if (!is_array($customerData)) {
                    continue;
                }
                
                // Extract document
                $document = null;
                if (isset($customerData['document'])) {
                    if (is_array($customerData['document']) && isset($customerData['document']['number'])) {
                        $document = $customerData['document']['number'];
                    } elseif (is_string($customerData['document'])) {
                        $document = $customerData['document'];
                    }
                }
                
                if (!$document) {
                    continue;
                }
                
                // Clean document
                $document = preg_replace('/[^0-9]/', '', $document);
                
                // If customer already exists, update statistics
                if (isset($customersMap[$document])) {
                    $customersMap[$document]['total_transactions']++;
                    $customersMap[$document]['total_spent'] += (float) $transaction->amount;
                    if ($transaction->created_at > $customersMap[$document]['last_transaction_at']) {
                        $customersMap[$document]['last_transaction_at'] = $transaction->created_at;
                    }
                } else {
                    // Create new customer entry
                    $customersMap[$document] = [
                        'id' => $document,
                        'name' => $customerData['name'] ?? null,
                        'email' => $customerData['email'] ?? null,
                        'phone' => $customerData['phone'] ?? null,
                        'document' => $document,
                        'document_type' => isset($customerData['document']['type']) 
                            ? $customerData['document']['type'] 
                            : (strlen($document) === 11 ? 'cpf' : 'cnpj'),
                        'total_transactions' => 1,
                        'total_spent' => (float) $transaction->amount,
                        'first_transaction_at' => $transaction->created_at,
                        'last_transaction_at' => $transaction->created_at,
                    ];
                }
            }

            // Convert to array and paginate
            $customers = array_values($customersMap);
            $total = count($customers);
            $page = $request->get('page', 1);
            $offset = ($page - 1) * $perPage;
            $customers = array_slice($customers, $offset, $perPage);

            return response()->json([
                'success' => true,
                'data' => $customers,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => (int) $page,
                    'last_page' => (int) ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error listing customers via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error listing customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer details
     * 
     * GET /api/v1/customers/{id}
     * 
     * @param Request $request
     * @param string $id (document number)
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

            // Clean document
            $document = preg_replace('/[^0-9]/', '', $id);

            // Find all transactions for this customer
            $transactions = Transaction::where('user_id', $user->id)
                ->where(function($query) use ($document) {
                    $query->whereJsonContains('customer_data->document->number', $document)
                          ->orWhere('customer_data->document', 'like', "%{$document}%");
                })
                ->orderBy('created_at', 'desc')
                ->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found'
                ], 404);
            }

            // Get customer data from first transaction
            $firstTransaction = $transactions->first();
            $customerData = $firstTransaction->customer_data;

            $customer = [
                'id' => $document,
                'name' => $customerData['name'] ?? null,
                'email' => $customerData['email'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'document' => $document,
                'document_type' => isset($customerData['document']['type']) 
                    ? $customerData['document']['type'] 
                    : (strlen($document) === 11 ? 'cpf' : 'cnpj'),
                'total_transactions' => $transactions->count(),
                'total_spent' => $transactions->sum('amount'),
                'first_transaction_at' => $transactions->last()->created_at->toISOString(),
                'last_transaction_at' => $transactions->first()->created_at->toISOString(),
                'transactions' => $transactions->map(function($transaction) {
                    return [
                        'id' => $transaction->transaction_id,
                        'amount' => (float) $transaction->amount,
                        'status' => $transaction->status,
                        'payment_method' => $transaction->payment_method,
                        'created_at' => $transaction->created_at->toISOString(),
                    ];
                })->toArray(),
            ];

            return response()->json([
                'success' => true,
                'data' => $customer
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error fetching customer via API', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error fetching customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new customer
     * 
     * POST /api/v1/customers
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

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'document' => 'required|string|min:11|max:18',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Clean document
            $document = preg_replace('/[^0-9]/', '', $request->document);
            $documentType = strlen($document) === 11 ? 'cpf' : 'cnpj';

            // Check if customer already exists
            $existingTransaction = Transaction::where('user_id', $user->id)
                ->where(function($query) use ($document) {
                    $query->whereJsonContains('customer_data->document->number', $document)
                          ->orWhere('customer_data->document', 'like', "%{$document}%");
                })
                ->first();

            if ($existingTransaction) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer with this document already exists',
                    'data' => [
                        'id' => $document,
                        'name' => $existingTransaction->customer_data['name'] ?? null,
                        'email' => $existingTransaction->customer_data['email'] ?? null,
                    ]
                ], 422);
            }

            // Create a draft transaction to store customer data
            $customerData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone ? preg_replace('/[^0-9]/', '', $request->phone) : null,
                'document' => [
                    'type' => $documentType,
                    'number' => $document,
                ],
            ];

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => 'CUSTOMER_' . time() . '_' . uniqid(),
                'amount' => 0,
                'fee_amount' => 0,
                'net_amount' => 0,
                'currency' => 'BRL',
                'payment_method' => 'pix',
                'status' => 'draft',
                'customer_data' => $customerData,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => [
                    'id' => $document,
                    'name' => $customerData['name'],
                    'email' => $customerData['email'],
                    'phone' => $customerData['phone'],
                    'document' => $document,
                    'document_type' => $documentType,
                    'created_at' => $transaction->created_at->toISOString(),
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating customer via API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error creating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update customer
     * 
     * PUT /api/v1/customers/{id}
     * 
     * @param Request $request
     * @param string $id (document number)
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255',
                'phone' => 'nullable|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Clean document
            $document = preg_replace('/[^0-9]/', '', $id);

            // Find all transactions for this customer
            $transactions = Transaction::where('user_id', $user->id)
                ->where(function($query) use ($document) {
                    $query->whereJsonContains('customer_data->document->number', $document)
                          ->orWhere('customer_data->document', 'like', "%{$document}%");
                })
                ->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found'
                ], 404);
            }

            // Update all transactions with new customer data
            foreach ($transactions as $transaction) {
                $customerData = $transaction->customer_data;
                
                if (isset($request->name)) {
                    $customerData['name'] = $request->name;
                }
                if (isset($request->email)) {
                    $customerData['email'] = $request->email;
                }
                if (isset($request->phone)) {
                    $customerData['phone'] = preg_replace('/[^0-9]/', '', $request->phone);
                }
                
                $transaction->customer_data = $customerData;
                $transaction->save();
            }

            // Get updated customer data
            $firstTransaction = $transactions->first()->refresh();
            $customerData = $firstTransaction->customer_data;

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully',
                'data' => [
                    'id' => $document,
                    'name' => $customerData['name'] ?? null,
                    'email' => $customerData['email'] ?? null,
                    'phone' => $customerData['phone'] ?? null,
                    'document' => $document,
                    'updated_at' => now()->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error updating customer via API', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error updating customer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete customer (removes draft transactions only)
     * 
     * DELETE /api/v1/customers/{id}
     * 
     * @param Request $request
     * @param string $id (document number)
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Clean document
            $document = preg_replace('/[^0-9]/', '', $id);

            // Find draft transactions for this customer (only drafts can be deleted)
            $transactions = Transaction::where('user_id', $user->id)
                ->where('status', 'draft')
                ->where(function($query) use ($document) {
                    $query->whereJsonContains('customer_data->document->number', $document)
                          ->orWhere('customer_data->document', 'like', "%{$document}%");
                })
                ->get();

            if ($transactions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Customer not found or cannot be deleted (only draft customers can be deleted)'
                ], 404);
            }

            $deletedCount = $transactions->count();
            $transactions->each->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully',
                'data' => [
                    'id' => $document,
                    'deleted_transactions' => $deletedCount,
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error deleting customer via API', [
                'customer_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error deleting customer: ' . $e->getMessage()
            ], 500);
        }
    }
}





