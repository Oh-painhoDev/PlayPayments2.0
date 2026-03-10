<?php

namespace App\Http\Controllers;

use App\Models\UserWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaction;
use App\Services\WebhookService;
use Carbon\Carbon;

class WebhookManagementController extends Controller
{
    /**
     * Display the webhook management page
     */
    public function index()
    {
        $user = Auth::user();
        $webhooks = UserWebhook::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(10);
        
        return view('webhooks.index', compact('webhooks', 'user'));
    }
    
    /**
     * Display webhook documentation
     */
    public function documentation()
    {
        return view('webhooks.documentation');
    }
    
    /**
     * Store a new webhook
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'url' => 'required|url|max:255',
                'description' => 'nullable|string|max:255',
                'events' => 'required|array',
                'events.*' => 'required|in:transaction.created,transaction.paid,transaction.failed,transaction.expired,transaction.refunded,transaction.chargeback,transaction.cancelled',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $user = Auth::user();
            
            // Create webhook
            $webhook = new UserWebhook();
            $webhook->user_id = $user->id;
            $webhook->url = $request->url;
            $webhook->description = $request->description;
            $webhook->events = $request->events;
            $webhook->is_active = $request->has('is_active');
            $webhook->secret = $this->generateWebhookSecret();
            $webhook->save();
            
            return redirect()->route('webhooks.index')->with('success', 'Webhook adicionado com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar webhook: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao salvar webhook: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Update an existing webhook
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'url' => 'required|url|max:255',
                'description' => 'nullable|string|max:255',
                'events' => 'required|array',
                'events.*' => 'required|in:transaction.created,transaction.paid,transaction.failed,transaction.expired,transaction.refunded,transaction.chargeback,transaction.cancelled',
                'is_active' => 'boolean',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $user = Auth::user();
            $webhook = UserWebhook::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            
            // Update webhook
            $webhook->url = $request->url;
            $webhook->description = $request->description;
            $webhook->events = $request->events;
            $webhook->is_active = $request->has('is_active');
            $webhook->save();
            
            return redirect()->route('webhooks.index')->with('success', 'Webhook atualizado com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar webhook: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar webhook: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Delete a webhook
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $webhook = UserWebhook::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            
            $webhook->delete();
            
            return redirect()->route('webhooks.index')->with('success', 'Webhook removido com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao remover webhook: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao remover webhook: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Regenerate webhook secret
     */
    public function regenerateSecret($id)
    {
        try {
            $user = Auth::user();
            $webhook = UserWebhook::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            
            $webhook->secret = $this->generateWebhookSecret();
            $webhook->save();
            
            return redirect()->route('webhooks.index')->with('success', 'Secret do webhook regenerado com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao regenerar secret do webhook: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao regenerar secret: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Test a webhook
     */
    public function test($id)
    {
        try {
            $user = Auth::user();
            $webhook = UserWebhook::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();
            
            // Create test payload
            $payload = [
                'event' => 'test',
                'timestamp' => now()->toIso8601String(),
                'data' => [
                    'message' => 'This is a test webhook from PixBolt',
                    'user_id' => $user->id
                ]
            ];
            
            // Add signature
            $signature = $this->generateSignature($payload, $webhook->secret);
            
            // Send test webhook
            $response = $this->sendWebhook($webhook->url, $payload, $signature);
            
            if ($response['success']) {
                return redirect()->route('webhooks.index')->with('success', 'Webhook testado com sucesso! Resposta: ' . $response['message']);
            } else {
                return redirect()->route('webhooks.index')->with('error', 'Erro ao testar webhook: ' . $response['message']);
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao testar webhook: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao testar webhook: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Dispatch webhooks for paid transactions on a specific date
     */
    public function dispatchWebhooks(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'dispatch_date' => 'required|date|before_or_equal:today',
                'event_type' => 'required|in:transaction.created,transaction.paid',
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $user = Auth::user();
            $dispatchDate = Carbon::parse($request->dispatch_date);
            $eventType = $request->event_type;
            
            // Get paid transactions for the selected date that are NOT retained
            $transactions = Transaction::where('user_id', $user->id)
                ->where('status', 'paid')
                ->where('is_retained', false) // Exclude retained transactions
                ->whereDate('created_at', $dispatchDate)
                ->get();
            
            if ($transactions->isEmpty()) {
                return redirect()->route('webhooks.index')
                    ->with('error', 'Nenhuma transa��o paga encontrada para a data selecionada.');
            }
            
            // Get active webhooks for this user and event
            $webhooks = UserWebhook::where('user_id', $user->id)
                ->where('is_active', true)
                ->get()
                ->filter(function ($webhook) use ($eventType) {
                    return $webhook->shouldTriggerForEvent($eventType);
                });
            
            if ($webhooks->isEmpty()) {
                return redirect()->route('webhooks.index')
                    ->with('error', 'Nenhum webhook ativo encontrado para o evento selecionado.');
            }
            
            $webhookService = new WebhookService();
            $dispatchedCount = 0;
            
            // Dispatch webhooks for each transaction
            foreach ($transactions as $transaction) {
                $webhookService->dispatchTransactionEvent($transaction, $eventType);
                $dispatchedCount++;
            }
            
            Log::info('Webhooks disparados manualmente', [
                'user_id' => $user->id,
                'date' => $dispatchDate->format('Y-m-d'),
                'event_type' => $eventType,
                'transactions_count' => $dispatchedCount,
                'webhooks_count' => $webhooks->count()
            ]);
            
            return redirect()->route('webhooks.index')
                ->with('success', "Webhooks disparados com sucesso! {$dispatchedCount} transa��es processadas para {$webhooks->count()} webhook(s).");
            
        } catch (\Exception $e) {
            Log::error('Erro ao disparar webhooks: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Erro ao disparar webhooks: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Generate a webhook secret
     */
    protected function generateWebhookSecret(): string
    {
        return 'whsec_' . bin2hex(random_bytes(24));
    }
    
    /**
     * Generate signature for webhook payload
     */
    protected function generateSignature(array $payload, string $secret): string
    {
        $payloadString = json_encode($payload);
        return hash_hmac('sha256', $payloadString, $secret);
    }
    
    /**
     * Send webhook to URL
     */
    protected function sendWebhook(string $url, array $payload, string $signature): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'PixBolt-Webhook/1.0',
                    'X-PixBolt-Signature' => $signature
                ])
                ->post($url, $payload);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'HTTP ' . $response->status() . ' - ' . substr($response->body(), 0, 100)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'HTTP ' . $response->status() . ' - ' . substr($response->body(), 0, 100)
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }
}