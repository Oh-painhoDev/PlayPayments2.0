<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;

class CustomerController extends Controller
{
    /**
     * Lista todos os clientes únicos do usuário
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Buscar todas as transações do usuário com customer_data
        $transactions = Transaction::where('user_id', $user->id)
            ->whereNotNull('customer_data')
            ->get();
        
        // Agrupar clientes únicos por documento (CPF/CNPJ)
        $customersMap = [];
        
        foreach ($transactions as $transaction) {
            $customerData = $transaction->customer_data;
            
            // Verificar se customer_data é válido
            if (!is_array($customerData)) {
                continue;
            }
            
            // Extrair documento
            $document = null;
            if (isset($customerData['document'])) {
                if (is_array($customerData['document']) && isset($customerData['document']['number'])) {
                    $document = $customerData['document']['number'];
                } elseif (is_string($customerData['document'])) {
                    $document = $customerData['document'];
                }
            }
            
            // Se não tiver documento, pular
            if (!$document) {
                continue;
            }
            
            // Limpar documento (remover caracteres especiais)
            $document = preg_replace('/[^0-9]/', '', $document);
            
            // Se já existe este cliente, atualizar estatísticas
            if (isset($customersMap[$document])) {
                $customer = &$customersMap[$document];
                $customer['total_transactions']++;
                $transactionDate = is_string($transaction->created_at) ? \Carbon\Carbon::parse($transaction->created_at) : $transaction->created_at;
                $lastDate = is_string($customer['last_transaction_at']) ? \Carbon\Carbon::parse($customer['last_transaction_at']) : $customer['last_transaction_at'];
                $firstDate = is_string($customer['first_transaction_at']) ? \Carbon\Carbon::parse($customer['first_transaction_at']) : $customer['first_transaction_at'];
                
                if ($transactionDate > $lastDate) {
                    $customer['last_transaction_at'] = $transaction->created_at;
                }
                if ($transactionDate < $firstDate) {
                    $customer['first_transaction_at'] = $transaction->created_at;
                }
            } else {
                // Criar novo cliente
                $customersMap[$document] = [
                    'document' => $document,
                    'name' => $customerData['name'] ?? 'N/A',
                    'email' => $customerData['email'] ?? null,
                    'phone' => $customerData['phone'] ?? null,
                    'address' => $this->formatAddress($customerData['address'] ?? null),
                    'first_transaction_at' => $transaction->created_at,
                    'last_transaction_at' => $transaction->created_at,
                    'total_transactions' => 1,
                ];
            }
        }
        
        // Converter mapa para coleção
        $customers = collect(array_values($customersMap));
        
        // Aplicar busca
        if ($request->has('search') && $request->search) {
            $search = strtolower($request->search);
            $customers = $customers->filter(function ($customer) use ($search) {
                return stripos($customer['name'], $search) !== false
                    || stripos($customer['document'], $search) !== false
                    || ($customer['email'] && stripos($customer['email'], $search) !== false)
                    || ($customer['phone'] && stripos($customer['phone'], $search) !== false);
            });
        }
        
        // Ordenar por última transação (mais recente primeiro)
        $customers = $customers->sortByDesc(function ($customer) {
            $date = $customer['last_transaction_at'];
            if (is_string($date)) {
                $date = \Carbon\Carbon::parse($date);
            }
            return $date instanceof \Carbon\Carbon ? $date->timestamp : strtotime($date);
        })->values();
        
        // Paginar manualmente
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $total = $customers->count();
        $items = $customers->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
        
        return view('customers.index', [
            'customers' => $paginator,
            'totalCustomers' => $total,
            'user' => $user,
        ]);
    }
    
    /**
     * Formata endereço do cliente
     */
    private function formatAddress($address)
    {
        if (!$address || !is_array($address)) {
            return null;
        }
        
        $parts = [];
        
        if (isset($address['street'])) {
            $parts[] = $address['street'];
        }
        if (isset($address['number'])) {
            $parts[] = $address['number'];
        }
        if (isset($address['complement'])) {
            $parts[] = $address['complement'];
        }
        if (isset($address['neighborhood'])) {
            $parts[] = $address['neighborhood'];
        }
        if (isset($address['city'])) {
            $cityState = $address['city'];
            if (isset($address['state'])) {
                $cityState .= ' - ' . $address['state'];
            }
            $parts[] = $cityState;
        }
        if (isset($address['zipcode'])) {
            $parts[] = $address['zipcode'];
        }
        
        return implode(', ', $parts);
    }
    
    /**
     * Cria um novo cliente (armazena em uma transação de teste ou cria uma entrada)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'type' => 'required|in:individual,business',
            'document' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);
        
        // Limpar documento (remover caracteres especiais)
        $document = preg_replace('/[^0-9]/', '', $validated['document']);
        
        // Verificar se já existe um cliente com este documento
        $existingTransaction = Transaction::where('user_id', $user->id)
            ->whereJsonContains('customer_data->document->number', $document)
            ->first();
        
        if ($existingTransaction) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente com este documento já existe.',
            ], 422);
        }
        
        // Criar uma transação de teste para armazenar o cliente
        // (ou você pode criar uma tabela separada de clientes)
        $customerData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'document' => [
                'type' => $validated['type'] === 'individual' ? 'cpf' : 'cnpj',
                'number' => $document,
            ],
        ];
        
        // Criar transação de teste (status: draft ou similar)
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
            'message' => 'Cliente cadastrado com sucesso.',
            'customer' => [
                'document' => $document,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
            ],
        ], 201);
    }
}

