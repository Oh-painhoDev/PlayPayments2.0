<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AstrofyIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

/**
 * Controller para endpoints do ecossistema Astrofy (chamados pelo provedor)
 * 
 * Endpoints:
 * - GET /v1/gateway - Consultar gateway
 * - POST /v1/gateway - Criar/Atualizar gateway
 * - DELETE /v1/gateway - Remover gateway
 * - POST /v1/gateway/logo - Upload de logo
 */
class AstrofyEcosystemController extends Controller
{
    protected $astrofyBaseUrl = 'https://gatewayhub.astrofy.site';

    /**
     * GET /v1/gateway - Retorna dados do gateway vinculado à X-Gateway-Key
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     */
    public function getGateway(Request $request)
    {
        try {
            $gatewayKey = $request->header('X-Gateway-Key');

            if (!$gatewayKey || empty(trim($gatewayKey))) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Buscar integração pela gateway_key
            $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)
                ->where('is_active', true)
                ->first();

            if (!$integration) {
                return response()->json([
                    'error' => 'NotFound',
                    'message' => 'Gateway not found.',
                ], 404);
            }

            // Preparar resposta
            $response = [
                'data' => [
                    'id' => (string) $integration->id,
                    'name' => $integration->name,
                    'baseUrl' => $integration->base_url,
                    'paymentTypes' => $integration->payment_types ?? ['PIX'],
                    'pictureUrl' => $integration->picture_url ?? null,
                    'description' => $integration->description ?? '',
                ],
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao consultar gateway', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'BadRequest',
                'message' => 'An error occurred while processing the request.',
            ], 400);
        }
    }

    /**
     * POST /v1/gateway - Criar ou atualizar gateway (idempotente)
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     * 
     * Payload:
     * {
     *   "name": "Meu Gateway",
     *   "baseUrl": "https://api.meugateway.com/v1",
     *   "paymentTypes": ["PIX", "CARD"],
     *   "description": "Gateway de pagamento"
     * }
     */
    public function createOrUpdateGateway(Request $request)
    {
        try {
            $gatewayKey = $request->header('X-Gateway-Key');

            if (!$gatewayKey || empty(trim($gatewayKey))) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Validar payload
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'baseUrl' => 'required|url|max:500',
                'paymentTypes' => 'required|array',
                'paymentTypes.*' => 'string|in:PIX,CARD',
                'description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Invalid payload: ' . $firstError,
                ], 400);
            }

            // IDEMPOTÊNCIA: Buscar integração existente ou criar nova
            $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)->first();

            if ($integration) {
                // Atualizar integração existente
                $integration->update([
                    'name' => $request->input('name'),
                    'base_url' => $request->input('baseUrl'),
                    'payment_types' => $request->input('paymentTypes'),
                    'description' => $request->input('description'),
                    'is_active' => true,
                ]);
            } else {
                // Criar nova integração
                // Nota: Se não tiver user_id, será uma integração global
                $integration = AstrofyIntegration::create([
                    'gateway_key' => $gatewayKey,
                    'name' => $request->input('name'),
                    'base_url' => $request->input('baseUrl'),
                    'payment_types' => $request->input('paymentTypes'),
                    'description' => $request->input('description'),
                    'is_active' => true,
                ]);
            }

            // Preparar resposta
            $response = [
                'data' => [
                    'id' => (string) $integration->id,
                    'name' => $integration->name,
                    'baseUrl' => $integration->base_url,
                    'paymentTypes' => $integration->payment_types,
                    'pictureUrl' => $integration->picture_url ?? null,
                    'description' => $integration->description ?? '',
                ],
            ];

            Log::info('✅ Astrofy: Gateway criado/atualizado', [
                'integration_id' => $integration->id,
                'gateway_key' => substr($gatewayKey, 0, 10) . '...',
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao criar/atualizar gateway', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'BadRequest',
                'message' => 'An error occurred while processing the request.',
            ], 400);
        }
    }

    /**
     * DELETE /v1/gateway - Remove o gateway associado à chave
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     */
    public function deleteGateway(Request $request)
    {
        try {
            $gatewayKey = $request->header('X-Gateway-Key');

            if (!$gatewayKey || empty(trim($gatewayKey))) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Buscar integração
            $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)->first();

            if (!$integration) {
                return response()->json([
                    'error' => 'NotFound',
                    'message' => 'Gateway not found.',
                ], 404);
            }

            // Desativar ou remover
            $integration->update(['is_active' => false]);
            // Ou remover completamente: $integration->delete();

            Log::info('✅ Astrofy: Gateway removido', [
                'integration_id' => $integration->id,
                'gateway_key' => substr($gatewayKey, 0, 10) . '...',
            ]);

            return response()->json([
                'data' => 'Gateway Removido',
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao remover gateway', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'BadRequest',
                'message' => 'An error occurred while processing the request.',
            ], 400);
        }
    }

    /**
     * POST /v1/gateway/logo - Upload de logo do gateway
     * 
     * Headers obrigatórios:
     * - X-Gateway-Key: Chave do gateway
     * 
     * Form data:
     * - logo: arquivo de imagem (multipart/form-data)
     * 
     * Regras:
     * - Imagem recomendada 512x512
     * - Máximo 50MB
     */
    public function uploadLogo(Request $request)
    {
        try {
            $gatewayKey = $request->header('X-Gateway-Key');

            if (!$gatewayKey || empty(trim($gatewayKey))) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Missing or invalid authentication headers.',
                ], 401);
            }

            // Validar arquivo
            $validator = Validator::make($request->all(), [
                'logo' => 'required|image|mimes:jpeg,jpg,png,webp|max:51200', // 50MB = 51200 KB
            ]);

            if ($validator->fails()) {
                $firstError = $validator->errors()->first();
                return response()->json([
                    'error' => 'BadRequest',
                    'message' => 'Invalid payload: ' . $firstError,
                ], 400);
            }

            // Buscar integração
            $integration = AstrofyIntegration::where('gateway_key', $gatewayKey)->first();

            if (!$integration) {
                return response()->json([
                    'error' => 'NotFound',
                    'message' => 'Gateway not found.',
                ], 404);
            }

            // Upload do arquivo
            $file = $request->file('logo');
            $filename = 'astrofy_logo_' . $integration->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('astrofy/logos', $filename, 'public');

            // Obter URL pública (URL completa)
            $url = Storage::url($path);
            // Se não for URL completa, adicionar domínio
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $url = rtrim(config('app.url'), '/') . '/' . ltrim($url, '/');
            }

            // Obter dimensões da imagem
            $imageInfo = getimagesize($file->getRealPath());
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            // Atualizar integração com URL da logo
            $integration->update([
                'picture_url' => $url,
            ]);

            // Preparar resposta
            $response = [
                'data' => [
                    'url' => $url,
                    'width' => $width,
                    'height' => $height,
                ],
            ];

            Log::info('✅ Astrofy: Logo enviada com sucesso', [
                'integration_id' => $integration->id,
                'url' => $url,
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('❌ Astrofy: Erro ao fazer upload da logo', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'BadRequest',
                'message' => 'An error occurred while processing the request.',
            ], 400);
        }
    }
}

