<?php

namespace App\Http\Controllers;

use App\Models\PixKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PixKeyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $pixKeys = PixKey::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalKeys = $pixKeys->count();
        $activeKeys = $pixKeys->where('status', 'active')->count();
        $availableSlots = 2 - $totalKeys; // Limite máximo de 2 chaves
        
        return response()->json([
            'pixKeys' => $pixKeys,
            'stats' => [
                'total' => $totalKeys,
                'active' => $activeKeys,
                'available' => max(0, $availableSlots),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validar limite de chaves (máximo 2)
            $existingKeys = PixKey::where('user_id', $user->id)->count();
            if ($existingKeys >= 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Limite máximo de 2 chaves PIX atingido.',
                ], 400);
            }
            
            // Validar dados
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:EMAIL,CPF,CNPJ,PHONE,EVP',
                'key' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Validar formato da chave baseado no tipo
            if (!$this->validateKeyFormat($request->type, $request->key)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Formato da chave PIX inválido para o tipo ' . $request->type . '.',
                ], 422);
            }
            
            // Validar se já existe chave do mesmo tipo ativa
            $existingActiveKey = PixKey::where('user_id', $user->id)
                ->where('type', $request->type)
                ->where('status', 'active')
                ->first();
            
            if ($existingActiveKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma chave PIX ativa do tipo ' . $request->type . '.',
                ], 400);
            }
            
            // Validar se o usuário já tem esta chave cadastrada
            $userExistingKey = PixKey::where('user_id', $user->id)
                ->where('key', $request->key)
                ->first();
            
            if ($userExistingKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta chave PIX já está cadastrada para você.',
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Criar chave PIX
            $pixKey = PixKey::create([
                'user_id' => $user->id,
                'type' => $request->type,
                'key' => $request->key,
                'description' => $request->description,
                'status' => 'active',
            ]);
            
            DB::commit();
            
            Log::info('PixKey created', [
                'pix_key_id' => $pixKey->id,
                'user_id' => $user->id,
                'type' => $request->type,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Chave PIX cadastrada com sucesso.',
                'pixKey' => [
                    'id' => $pixKey->id,
                    'user_id' => $pixKey->user_id,
                    'type' => $pixKey->type,
                    'type_label' => $pixKey->type_label,
                    'key' => $pixKey->key,
                    'description' => $pixKey->description,
                    'status' => $pixKey->status,
                    'created_at' => $pixKey->created_at->toISOString(),
                    'updated_at' => $pixKey->updated_at->toISOString(),
                ],
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating PixKey', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar chave PIX.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $pixKey = PixKey::where('user_id', $user->id)
            ->where('id', $id)
            ->first();
        
        if (!$pixKey) {
            return response()->json([
                'success' => false,
                'message' => 'Chave PIX não encontrada.',
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'pixKey' => [
                'id' => $pixKey->id,
                'user_id' => $pixKey->user_id,
                'type' => $pixKey->type,
                'type_label' => $pixKey->type_label,
                'key' => $pixKey->key,
                'description' => $pixKey->description,
                'status' => $pixKey->status,
                'created_at' => $pixKey->created_at->toISOString(),
                'updated_at' => $pixKey->updated_at->toISOString(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            $pixKey = PixKey::where('user_id', $user->id)
                ->where('id', $id)
                ->first();
            
            if (!$pixKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave PIX não encontrada.',
                ], 404);
            }
            
            // Validar dados
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|in:EMAIL,CPF,CNPJ,PHONE,EVP',
                'key' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:255',
                'status' => 'sometimes|in:active,inactive',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Validar formato da chave se foi alterada
            if ($request->has('key') && $request->key !== $pixKey->key) {
                $type = $request->type ?? $pixKey->type;
                if (!$this->validateKeyFormat($type, $request->key)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Formato da chave PIX inválido para o tipo ' . $type . '.',
                    ], 422);
                }
                
                // Validar se o usuário já tem esta chave cadastrada em outra entrada
                $userExistingKey = PixKey::where('user_id', $user->id)
                    ->where('key', $request->key)
                    ->where('id', '!=', $id)
                    ->first();
                if ($userExistingKey) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Esta chave PIX já está cadastrada para você em outra entrada.',
                    ], 400);
                }
            }
            
            // Validar se há outra chave ativa do mesmo tipo (se tipo ou status foram alterados)
            $newType = $request->has('type') ? $request->type : $pixKey->type;
            $newStatus = $request->has('status') ? $request->status : $pixKey->status;
            
            if (($request->has('type') && $request->type !== $pixKey->type) || 
                ($request->has('status') && $request->status === 'active' && $pixKey->status !== 'active')) {
                $existingActiveKey = PixKey::where('user_id', $user->id)
                    ->where('type', $newType)
                    ->where('status', 'active')
                    ->where('id', '!=', $id)
                    ->first();
                
                if ($existingActiveKey) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Já existe uma chave PIX ativa do tipo ' . $newType . '.',
                    ], 400);
                }
            }
            
            DB::beginTransaction();
            
            // Atualizar chave PIX
            $pixKey->update($request->only(['type', 'key', 'description', 'status']));
            
            DB::commit();
            
            Log::info('PixKey updated', [
                'pix_key_id' => $pixKey->id,
                'user_id' => $user->id,
            ]);
            
            $updatedPixKey = $pixKey->fresh();
            return response()->json([
                'success' => true,
                'message' => 'Chave PIX atualizada com sucesso.',
                'pixKey' => [
                    'id' => $updatedPixKey->id,
                    'user_id' => $updatedPixKey->user_id,
                    'type' => $updatedPixKey->type,
                    'type_label' => $updatedPixKey->type_label,
                    'key' => $updatedPixKey->key,
                    'description' => $updatedPixKey->description,
                    'status' => $updatedPixKey->status,
                    'created_at' => $updatedPixKey->created_at->toISOString(),
                    'updated_at' => $updatedPixKey->updated_at->toISOString(),
                ],
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating PixKey', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'pix_key_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar chave PIX.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            $pixKey = PixKey::where('user_id', $user->id)
                ->where('id', $id)
                ->first();
            
            if (!$pixKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chave PIX não encontrada.',
                ], 404);
            }
            
            DB::beginTransaction();
            
            $pixKey->delete();
            
            DB::commit();
            
            Log::info('PixKey deleted', [
                'pix_key_id' => $id,
                'user_id' => $user->id,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Chave PIX removida com sucesso.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting PixKey', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'pix_key_id' => $id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover chave PIX.',
            ], 500);
        }
    }

    /**
     * Validar formato da chave PIX baseado no tipo
     */
    private function validateKeyFormat(string $type, string $key): bool
    {
        switch ($type) {
            case 'EMAIL':
                return filter_var($key, FILTER_VALIDATE_EMAIL) !== false;
            
            case 'CPF':
                // Remover formatação
                $cpf = preg_replace('/[^0-9]/', '', $key);
                // Validar se tem 11 dígitos
                return strlen($cpf) === 11 && preg_match('/^[0-9]{11}$/', $cpf);
            
            case 'CNPJ':
                // Remover formatação
                $cnpj = preg_replace('/[^0-9]/', '', $key);
                // Validar se tem 14 dígitos
                return strlen($cnpj) === 14 && preg_match('/^[0-9]{14}$/', $cnpj);
            
            case 'PHONE':
                // Remover formatação
                $phone = preg_replace('/[^0-9]/', '', $key);
                // Validar se tem 10 ou 11 dígitos (com DDD)
                return strlen($phone) >= 10 && strlen($phone) <= 11 && preg_match('/^[0-9]{10,11}$/', $phone);
            
            case 'EVP':
                // Chave aleatória (UUID format)
                return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $key);
            
            default:
                return false;
        }
    }
}
