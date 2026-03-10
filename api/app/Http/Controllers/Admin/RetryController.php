<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RetryConfig;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RetryController extends Controller
{
    /**
     * Show retry configuration page
     */
    public function index()
    {
        $retryConfig = RetryConfig::getGlobal();
        
        // Se não existe, criar uma configuração padrão
        if (!$retryConfig) {
            $retryConfig = RetryConfig::create([
                'is_enabled' => false,
                'retry_gateway_id' => null,
                'description' => 'Configuração global de retentativa de pagamento',
            ]);
        }
        
        $gateways = PaymentGateway::where('is_active', true)->get();
        
        return view('admin.retry.index', compact('retryConfig', 'gateways'));
    }

    /**
     * Update retry configuration
     */
    public function update(Request $request)
    {
        try {
            $request->validate([
                'is_enabled' => 'nullable|boolean',
                'retry_gateway_id' => 'nullable|exists:payment_gateways,id',
                'description' => 'nullable|string|max:1000',
            ]);

            $retryConfig = RetryConfig::getGlobal();
            
            // Se não existe, criar
            if (!$retryConfig) {
                $retryConfig = RetryConfig::create([
                    'is_enabled' => false,
                    'retry_gateway_id' => null,
                    'description' => 'Configuração global de retentativa de pagamento',
                ]);
            }
            
            $retryConfig->update([
                'is_enabled' => $request->has('is_enabled') ? $request->boolean('is_enabled') : false,
                'retry_gateway_id' => $request->retry_gateway_id ?: null,
                'description' => $request->description,
            ]);

            return back()->with('success', 'Configuração de retentativa atualizada com sucesso!');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao atualizar configuração de retentativa', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar configuração de retentativa: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return back()->withErrors(['error' => 'Erro ao salvar configuração: ' . $e->getMessage()])->withInput();
        }
    }
}
