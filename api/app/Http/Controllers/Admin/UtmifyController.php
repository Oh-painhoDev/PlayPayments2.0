<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UtmifyIntegration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UtmifyController extends Controller
{
    /**
     * Display UTMify integrations management page
     */
    public function index()
    {
        // Get all UTMify integrations (user-specific and global)
        $integrations = UtmifyIntegration::with('user')
            ->orderByRaw('CASE WHEN user_id IS NULL THEN 0 ELSE 1 END') // Globais primeiro
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Get all users for dropdown (exclude admin and manager)
        $users = User::whereNotIn('role', ['admin', 'gerente'])
            ->orderBy('name')
            ->get();
        
        return view('admin.white-label.utmify', compact('integrations', 'users'));
    }
    
    /**
     * Store a new UTMify integration for a user
     */
    public function store(Request $request)
    {
        // Validar user_id - pode ser null para integração global ou deve existir
        $rules = [
            'user_id' => 'nullable',
            'name' => 'required|string|max:255',
            'api_token' => 'required|string|max:255',
            'pixel_id' => 'nullable|string|max:255',
            'platform_name' => 'nullable|string|max:100',
            'trigger_on_payment' => 'boolean',
            'trigger_on_creation' => 'boolean',
            'is_active' => 'boolean',
        ];
        
        // Se user_id não for 'global' ou vazio, deve existir na tabela users
        if ($request->user_id && $request->user_id !== 'global' && $request->user_id !== '') {
            $rules['user_id'] = 'required|exists:users,id';
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            // Se user_id for 'global' ou vazio, definir como null (integração global)
            $userId = null;
            if ($request->user_id && $request->user_id !== 'global' && $request->user_id !== '') {
                $userId = $request->user_id;
            }
            
            UtmifyIntegration::create([
                'user_id' => $userId,
                'name' => $request->name,
                'api_token' => $request->api_token,
                'pixel_id' => $request->pixel_id ?? null,
                'platform_name' => $request->platform_name ?? null,
                'trigger_on_payment' => $request->has('trigger_on_payment'),
                'trigger_on_creation' => $request->has('trigger_on_creation'),
                'is_active' => $request->has('is_active'),
            ]);
            
            Log::info('UTMify integration created by admin', [
                'user_id' => $userId,
                'is_global' => $userId === null,
                'admin_id' => auth()->id(),
            ]);
            
            return redirect()->route('admin.white-label.utmify.index')
                ->with('success', 'Integração UTMify criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar integração UTMify: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao criar integração: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Update an existing UTMify integration
     */
    public function update(Request $request, $id)
    {
        $integration = UtmifyIntegration::findOrFail($id);
        
        $rules = [
            'name' => 'required|string|max:255',
            'api_token' => 'required|string|max:255',
            'pixel_id' => 'nullable|string|max:255',
            'platform_name' => 'nullable|string|max:100',
            'trigger_on_payment' => 'boolean',
            'trigger_on_creation' => 'boolean',
            'is_active' => 'boolean',
        ];
        
        // Se user_id for fornecido e não for 'global', validar
        if ($request->has('user_id')) {
            if ($request->user_id && $request->user_id !== 'global' && $request->user_id !== '') {
                $rules['user_id'] = 'nullable|exists:users,id';
            }
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        try {
            // Se user_id for fornecido, atualizar (pode ser null para global)
            $updateData = [
                'name' => $request->name,
                'api_token' => $request->api_token,
                'pixel_id' => $request->pixel_id ?? null,
                'platform_name' => $request->platform_name ?? null,
                'trigger_on_payment' => $request->has('trigger_on_payment'),
                'trigger_on_creation' => $request->has('trigger_on_creation'),
                'is_active' => $request->has('is_active'),
            ];
            
            // Se user_id foi fornecido no request, atualizar
            if ($request->has('user_id')) {
                if ($request->user_id === 'global' || $request->user_id === '') {
                    $updateData['user_id'] = null;
                } elseif ($request->user_id) {
                    $updateData['user_id'] = $request->user_id;
                }
            }
            
            $integration->update($updateData);
            
            Log::info('UTMify integration updated by admin', [
                'integration_id' => $id,
                'user_id' => $integration->user_id,
                'admin_id' => auth()->id(),
            ]);
            
            return redirect()->route('admin.white-label.utmify.index')
                ->with('success', 'Integração UTMify atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar integração UTMify: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao atualizar integração: ' . $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Delete an UTMify integration
     */
    public function destroy($id)
    {
        try {
            $integration = UtmifyIntegration::findOrFail($id);
            $userId = $integration->user_id;
            
            $integration->delete();
            
            Log::info('UTMify integration deleted by admin', [
                'integration_id' => $id,
                'user_id' => $userId,
                'admin_id' => auth()->id(),
            ]);
            
            return redirect()->route('admin.white-label.utmify.index')
                ->with('success', 'Integração UTMify removida com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao remover integração UTMify: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao remover integração: ' . $e->getMessage()]);
        }
    }
}

