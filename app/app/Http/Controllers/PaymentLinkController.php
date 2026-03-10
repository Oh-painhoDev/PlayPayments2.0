<?php

namespace App\Http\Controllers;

use App\Models\PaymentLink;
use App\Models\Transaction;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentLinkController extends Controller
{
    /**
     * Display a listing of payment links.
     */
    public function index()
    {
        $user = Auth::user();
        $paymentLinks = PaymentLink::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('payment-links.index', compact('paymentLinks'));
    }

    /**
     * Show the form for creating a new payment link.
     */
    public function create()
    {
        // This is handled by the index view with modal
        return redirect()->route('payment-links.index');
    }

    /**
     * Store a newly created payment link.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'title' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_.,!?()áàâãéèêíìîóòôõúùûçÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇ]+$/u',
            'description' => 'nullable|string|max:1000',
            'amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'payment_method' => 'required|in:pix,credit_card,bank_slip,all',
            'allow_custom_amount' => 'nullable|boolean',
            'min_amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'max_amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'max_uses' => 'nullable|integer|min:1|max:100000',
            'expires_at' => 'nullable|date|after:now|before:+1 year',
        ]);
        
        // Additional validation: ensure min_amount <= max_amount if both are set
        if (isset($validated['min_amount']) && isset($validated['max_amount'])) {
            if ($validated['min_amount'] > $validated['max_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor mínimo não pode ser maior que o valor máximo.',
                ], 422);
            }
        }

        // Handle allow_custom_amount checkbox
        $validated['allow_custom_amount'] = $request->has('allow_custom_amount') && $request->input('allow_custom_amount') == '1';

        // If amount is provided and allow_custom_amount is false, don't allow custom amount
        if (isset($validated['amount']) && $validated['amount'] && !$validated['allow_custom_amount']) {
            $validated['allow_custom_amount'] = false;
        } elseif (!isset($validated['amount']) || !$validated['amount']) {
            // If no amount, must allow custom amount
            $validated['allow_custom_amount'] = true;
        }

        $validated['user_id'] = $user->id;
        $validated['slug'] = Str::random(32);
        $validated['is_active'] = true;

        $paymentLink = PaymentLink::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Link de pagamento criado com sucesso!',
            'payment_link' => $paymentLink,
            'checkout_url' => $paymentLink->checkout_url,
        ], 201);
    }

    /**
     * Display the specified payment link.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Validate ID is numeric to prevent injection
        if (!is_numeric($id)) {
            abort(404);
        }
        
        $paymentLink = PaymentLink::where('user_id', $user->id)
            ->with('transactions')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'payment_link' => $paymentLink,
        ]);
    }

    /**
     * Update the specified payment link.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Validate ID is numeric to prevent injection
        if (!is_numeric($id)) {
            abort(404);
        }
        
        $paymentLink = PaymentLink::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255|regex:/^[a-zA-Z0-9\s\-_.,!?()áàâãéèêíìîóòôõúùûçÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇ]+$/u',
            'description' => 'nullable|string|max:1000',
            'amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'payment_method' => 'sometimes|required|in:pix,credit_card,bank_slip,all',
            'allow_custom_amount' => 'nullable|boolean',
            'min_amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'max_amount' => 'nullable|numeric|min:0.01|max:999999.99',
            'max_uses' => 'nullable|integer|min:1|max:100000',
            'expires_at' => 'nullable|date|after:now|before:+1 year',
            'is_active' => 'nullable|boolean',
        ]);
        
        // Additional validation: ensure min_amount <= max_amount if both are set
        if (isset($validated['min_amount']) && isset($validated['max_amount'])) {
            if ($validated['min_amount'] > $validated['max_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor mínimo não pode ser maior que o valor máximo.',
                ], 422);
            }
        }

        // Handle allow_custom_amount checkbox
        $validated['allow_custom_amount'] = $request->has('allow_custom_amount') && $request->input('allow_custom_amount') == '1';
        
        // Handle is_active checkbox
        if ($request->has('is_active')) {
            $validated['is_active'] = $request->input('is_active') == '1' || $request->input('is_active') === true;
        }

        $paymentLink->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Link de pagamento atualizado com sucesso!',
            'payment_link' => $paymentLink->fresh(),
        ]);
    }

    /**
     * Remove the specified payment link.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        // Validate ID is numeric to prevent injection
        if (!is_numeric($id)) {
            abort(404);
        }
        
        $paymentLink = PaymentLink::where('user_id', $user->id)->findOrFail($id);

        $paymentLink->delete();

        return response()->json([
            'success' => true,
            'message' => 'Link de pagamento excluído com sucesso!',
        ]);
    }

    /**
     * Show checkout page (public)
     */
    public function checkout($slug)
    {
        $paymentLink = PaymentLink::where('slug', $slug)->firstOrFail();

        if (!$paymentLink->canBeUsed()) {
            return view('payment-links.expired', compact('paymentLink'));
        }

        return view('payment-links.checkout', compact('paymentLink'));
    }

    /**
     * Process payment from checkout
     */
    public function processCheckout(Request $request, $slug)
    {
        $paymentLink = PaymentLink::where('slug', $slug)->firstOrFail();

        if (!$paymentLink->canBeUsed()) {
            return response()->json([
                'success' => false,
                'message' => 'Este link de pagamento não está mais disponível.',
            ], 400);
        }

        $user = $paymentLink->user;

        // Validate request
        $validated = $request->validate([
            'amount' => $paymentLink->amount ? 'nullable|numeric|min:0.01|max:999999.99' : 'required|numeric|min:0.01|max:999999.99',
            'payment_method' => 'required|in:pix,credit_card,bank_slip',
            'customer.name' => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_.,!?()áàâãéèêíìîóòôõúùûçÁÀÂÃÉÈÊÍÌÎÓÒÔÕÚÙÛÇ]+$/u',
            'customer.email' => 'required|email|max:255',
            'customer.document' => 'required|string|regex:/^[0-9]{11,14}$/',
            'customer.phone' => 'nullable|string|regex:/^[0-9\s\(\)\-\+]{10,20}$/',
        ]);
        
        // Sanitize document - remove non-numeric characters
        $validated['customer']['document'] = preg_replace('/[^0-9]/', '', $validated['customer']['document']);
        
        // Validate document length (CPF: 11, CNPJ: 14)
        if (strlen($validated['customer']['document']) !== 11 && strlen($validated['customer']['document']) !== 14) {
            return response()->json([
                'success' => false,
                'message' => 'CPF deve ter 11 dígitos ou CNPJ deve ter 14 dígitos.',
            ], 422);
        }

        // Use link amount if provided, otherwise use customer amount
        $amount = $paymentLink->amount ?? $validated['amount'];

        // Validate amount limits if custom amount is allowed
        if ($paymentLink->allow_custom_amount && !$paymentLink->amount) {
            if ($paymentLink->min_amount && $amount < $paymentLink->min_amount) {
                return response()->json([
                    'success' => false,
                    'message' => "O valor mínimo é R$ " . number_format($paymentLink->min_amount, 2, ',', '.'),
                ], 400);
            }

            if ($paymentLink->max_amount && $amount > $paymentLink->max_amount) {
                return response()->json([
                    'success' => false,
                    'message' => "O valor máximo é R$ " . number_format($paymentLink->max_amount, 2, ',', '.'),
                ], 400);
            }
        }

        // Validate payment method
        if ($paymentLink->payment_method !== 'all' && $validated['payment_method'] !== $paymentLink->payment_method) {
            return response()->json([
                'success' => false,
                'message' => 'Método de pagamento não permitido para este link.',
            ], 400);
        }

        // Check if user has gateway configured
        if (!$user->assignedGateway) {
            return response()->json([
                'success' => false,
                'message' => 'Gateway não configurado. Entre em contato com o suporte.',
            ], 400);
        }

        // Create transaction
        $paymentService = new PaymentGatewayService($user->assignedGateway);

        $transactionData = [
            'amount' => $amount,
            'payment_method' => $validated['payment_method'],
            'description' => $paymentLink->title,
            'customer' => $validated['customer'],
            'payment_link_id' => $paymentLink->id,
            'metadata' => [
                'payment_link_id' => $paymentLink->id,
                'payment_link_title' => $paymentLink->title,
            ],
        ];

        // Set PIX expiration to 15 minutes
        if ($validated['payment_method'] === 'pix') {
            $transactionData['pix_expires_in_minutes'] = 15;
        }

        $result = $paymentService->createTransaction($user, $transactionData);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Erro ao processar pagamento.',
            ], 400);
        }

        // Increment current uses
        $paymentLink->increment('current_uses');

        // Reload transaction to get fresh payment_data
        $transaction = $result['transaction']->fresh();
        
        // Extract payment data from gateway response or transaction
        $paymentData = null;
        
        // Try to get PIX data from multiple sources
        if ($validated['payment_method'] === 'pix') {
            $pixData = null;
            
            // First, try gateway_response
            if (isset($result['gateway_response']['payment_data']['pix'])) {
                $pixData = $result['gateway_response']['payment_data']['pix'];
            }
            // Second, try transaction payment_data (nested structure)
            elseif ($transaction->payment_data && isset($transaction->payment_data['payment_data']['pix'])) {
                $pixData = $transaction->payment_data['payment_data']['pix'];
            }
            // Third, try transaction payment_data (direct structure)
            elseif ($transaction->payment_data && isset($transaction->payment_data['pix'])) {
                $pixData = $transaction->payment_data['pix'];
            }
            
            if ($pixData) {
                // Extract qrcode/payload (they might be the same or different)
                $qrcode = $pixData['qrcode'] ?? $pixData['payload'] ?? $pixData['emv'] ?? null;
                
                if ($qrcode) {
                    $paymentData = [
                        'qrcode' => $qrcode,
                        'payload' => $qrcode,
                        'expirationDate' => $pixData['expirationDate'] ?? null,
                    ];
                }
            }
        } else {
            // For other payment methods, return the full payment_data
            if (isset($result['gateway_response']['payment_data'])) {
                $paymentData = $result['gateway_response']['payment_data'];
            } elseif ($transaction->payment_data) {
                $paymentData = $transaction->payment_data;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Pagamento processado com sucesso!',
            'transaction' => $transaction,
            'payment_data' => $paymentData,
        ]);
    }
}
