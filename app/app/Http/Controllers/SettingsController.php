<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Http\Requests\Settings\PasswordUpdateRequest;
use App\Models\FeeConfiguration;
use App\Models\UserFee;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Show settings index page with tabs
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get fees
        try {
            // First check for user's custom fees
            $userFees = [
                'pix' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'pix')
                    ->where('is_active', true)
                    ->first(),
                    
                'credit_card' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'credit_card')
                    ->where('is_active', true)
                    ->first(),
                    
                'bank_slip' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'bank_slip')
                    ->where('is_active', true)
                    ->first(),
            ];
            
            // Get global fees as fallback
            $globalFees = FeeConfiguration::getGlobalFees();
            
            // Get withdrawal fee from fee_configurations table
            $globalWithdrawalFee = FeeConfiguration::where('payment_method', 'withdrawal')
                ->where('is_global', true)
                ->where('is_active', true)
                ->first();
            
            // Get withdrawal fee - prioritize user's custom settings, then global fee, then defaults
            // IMPORTANTE: Se o usuário tem withdrawal_fee_type configurado, usar os valores do usuário
            $withdrawalFee = [
                'type' => $user->withdrawal_fee_type ?? 'global',
                'percentage' => $user->withdrawal_fee_type && $user->withdrawal_fee_type !== 'global' && $user->withdrawal_fee_percentage
                    ? (float)$user->withdrawal_fee_percentage
                    : ($globalWithdrawalFee 
                        ? (float)$globalWithdrawalFee->percentage_fee 
                        : 1.00),
                'fixed' => $user->withdrawal_fee_type && $user->withdrawal_fee_type !== 'global' && $user->withdrawal_fee_fixed
                    ? (float)$user->withdrawal_fee_fixed
                    : ($globalWithdrawalFee 
                        ? (float)$globalWithdrawalFee->fixed_fee 
                        : 5.00),
                'min' => $globalWithdrawalFee 
                    ? (float)$globalWithdrawalFee->min_amount 
                    : 10.00,
                'max' => $globalWithdrawalFee 
                    ? ($globalWithdrawalFee->max_amount ? (float)$globalWithdrawalFee->max_amount : null) 
                    : null
            ];
            
            // Prepare formatted fees, prioritizing user's custom fees
            $formattedFees = [
                'pix' => [
                    'percentage' => $userFees['pix'] ? (float)$userFees['pix']->percentage_fee : ($globalFees['pix'] ? (float)$globalFees['pix']->percentage_fee : 3.50),
                    'fixed' => $userFees['pix'] ? (float)$userFees['pix']->fixed_fee : ($globalFees['pix'] ? (float)$globalFees['pix']->fixed_fee : 0.00),
                    'min' => $userFees['pix'] ? (float)$userFees['pix']->min_amount : ($globalFees['pix'] ? (float)$globalFees['pix']->min_amount : 0.80),
                    'max' => $userFees['pix'] ? (float)$userFees['pix']->max_amount : ($globalFees['pix'] ? (float)$globalFees['pix']->max_amount : null)
                ],
                'credit_card' => [
                    'percentage' => $userFees['credit_card'] ? (float)$userFees['credit_card']->percentage_fee : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->percentage_fee : 0),
                    'fixed' => $userFees['credit_card'] ? (float)$userFees['credit_card']->fixed_fee : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->fixed_fee : 0),
                    'min' => $userFees['credit_card'] ? (float)$userFees['credit_card']->min_amount : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->min_amount : 0),
                    'max' => $userFees['credit_card'] ? (float)$userFees['credit_card']->max_amount : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->max_amount : null)
                ],
                'bank_slip' => [
                    'percentage' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->percentage_fee : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->percentage_fee : 0),
                    'fixed' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->fixed_fee : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->fixed_fee : 0),
                    'min' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->min_amount : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->min_amount : 0),
                    'max' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->max_amount : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->max_amount : null)
                ],
                'withdrawal' => $withdrawalFee
            ];
            
        } catch (\Exception $e) {
            Log::error('Error loading user fees: ' . $e->getMessage());
            
            $formattedFees = [
                'pix' => ['percentage' => 3.50, 'fixed' => 0.00, 'min' => 0.80, 'max' => null],
                'credit_card' => ['percentage' => 0, 'fixed' => 0, 'min' => 0, 'max' => null],
                'bank_slip' => ['percentage' => 0, 'fixed' => 0, 'min' => 0, 'max' => null],
                'withdrawal' => ['percentage' => 0, 'fixed' => 0]
            ];
        }
        
        return view('settings.index', compact('user', 'formattedFees'));
    }

    /**
     * Show profile settings
     */
    public function profile()
    {
        $user = Auth::user();
        return view('settings.profile', compact('user'));
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Allow updating email, fantasy_name, website, address, city, and photo
            $validated = $request->validate([
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'fantasy_name' => ['nullable', 'string', 'max:255'],
                'website' => ['nullable', 'url', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'city' => ['nullable', 'string', 'max:100'],
                'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
            ]);
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Usar pasta pública direta
                $directory = public_path('images/users/photos');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }
                
                // Delete old photo if exists
                if ($user->photo) {
                    $oldPhotoPath = public_path('images/users/photos/' . basename($user->photo));
                    if (file_exists($oldPhotoPath)) {
                        @unlink($oldPhotoPath);
                    }
                }
                
                // Generate unique filename
                $filename = $user->id . '_' . time() . '_' . uniqid() . '.' . $request->file('photo')->getClientOriginalExtension();
                
                // Store new photo diretamente na pasta pública
                $request->file('photo')->move($directory, $filename);
                $validated['photo'] = $filename;
            }
            
            $user->update($validated);
            
            // Return JSON if AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso!',
                    'photo_url' => isset($validated['photo']) ? url('/images/users/photos/' . $validated['photo']) : null
                ]);
            }
            
            return back()->with('success', 'Perfil atualizado com sucesso!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao atualizar perfil: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Update profile photo
     */
    public function updatePhoto(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }
            
            // Ensure photo column exists
            if (!Schema::hasColumn('users', 'photo')) {
                try {
                    DB::statement('ALTER TABLE `users` ADD COLUMN `photo` VARCHAR(255) NULL AFTER `email`');
                } catch (\Exception $e) {
                    Log::error('Erro ao criar coluna photo: ' . $e->getMessage());
                    // Continue anyway - column might already exist
                }
            }
            
            // Check if file was uploaded
            if (!$request->hasFile('photo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum arquivo foi enviado.'
                ], 422);
            }
            
            $file = $request->file('photo');
            
            // Validate file
            $validated = $request->validate([
                'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB max
            ]);
            
            // Usar pasta pública direta - muito mais simples!
            $directory = public_path('images/users/photos');
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // Delete old photo if exists
            if ($user->photo) {
                $oldPhotoPath = public_path('images/users/photos/' . basename($user->photo));
                if (file_exists($oldPhotoPath)) {
                    try {
                        @unlink($oldPhotoPath);
                    } catch (\Exception $e) {
                        Log::warning('Não foi possível deletar foto antiga: ' . $e->getMessage());
                    }
                }
            }
            
            // Generate unique filename
            $filename = $user->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Store new photo diretamente na pasta pública
            $file->move($directory, $filename);
            
            // Salvar apenas o filename no banco (não o caminho completo)
            DB::table('users')->where('id', $user->id)->update(['photo' => $filename]);
            
            // Refresh user model
            $user->refresh();
            
            // Generate URL - direto da pasta images
            $photoUrl = url('/images/users/photos/' . $filename);
            
            // Verificar se o arquivo existe
            $fileExists = file_exists($directory . '/' . $filename);
            
            Log::info('Foto upload concluído', [
                'user_id' => $user->id,
                'filename' => $filename,
                'directory' => $directory,
                'file_exists' => $fileExists,
                'photo_url' => $photoUrl,
                'full_path' => $directory . '/' . $filename
            ]);
            
            if (!$fileExists) {
                Log::error('Foto não encontrada após upload', [
                    'filename' => $filename,
                    'directory' => $directory,
                    'full_path' => $directory . '/' . $filename
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Foto atualizada com sucesso!',
                'photo_url' => $photoUrl,
                'debug' => [
                    'filename' => $filename,
                    'file_exists' => $fileExists
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $message = 'Erro de validação: ';
            if (isset($errors['photo'])) {
                $message .= implode(', ', $errors['photo']);
            } else {
                $message .= 'Arquivo inválido.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $message = 'Erro de validação: ';
            if (isset($errors['photo'])) {
                $message .= implode(', ', $errors['photo']);
            } else {
                $message .= 'Arquivo inválido.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar foto do usuário', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => [
                    'has_file' => $request->hasFile('photo'),
                    'file_size' => $request->hasFile('photo') ? $request->file('photo')->getSize() : null,
                    'file_type' => $request->hasFile('photo') ? $request->file('photo')->getMimeType() : null,
                ]
            ]);
            
            // Return more user-friendly error message
            $errorMessage = 'Erro ao atualizar foto.';
            if (strpos($e->getMessage(), 'Column not found') !== false) {
                $errorMessage = 'Erro no banco de dados. A coluna de foto não existe. Contate o suporte.';
            } elseif (strpos($e->getMessage(), 'Permission denied') !== false || strpos($e->getMessage(), 'permission') !== false) {
                $errorMessage = 'Erro de permissão ao salvar a foto. Verifique as permissões do servidor.';
            } elseif (strpos($e->getMessage(), 'disk') !== false || strpos($e->getMessage(), 'storage') !== false) {
                $errorMessage = 'Erro ao acessar o armazenamento. Verifique as configurações do servidor.';
            } else {
                $errorMessage = 'Erro ao atualizar foto: ' . $e->getMessage();
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(PasswordUpdateRequest $request)
    {
        try {
            $user = Auth::user();
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Senha atual incorreta.']);
            }
            
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            return back()->with('success', 'Senha alterada com sucesso!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao alterar senha: ' . $e->getMessage()]);
        }
    }

    /**
     * Show API credentials
     */
    public function api()
    {
        $user = Auth::user();
        
        return view('settings.api', compact('user'));
    }

    /**
     * Show API documentation
     */
    public function apiDocs()
    {
        return view('settings.api-docs');
    }

    /**
     * Show API Keys page (new design)
     */
    public function apiKeys()
    {
        $user = Auth::user();
        
        return view('api-keys', compact('user'));
    }

    /**
     * Generate new API Secret
     */
    public function generateApiSecret()
    {
        try {
            $user = Auth::user();
            
            // Generate new API Secret and Public Key
            $apiSecret = $this->generateSecureApiSecret();
            $apiPublicKey = $this->generateSecureApiPublicKey();
            
            Log::info('Gerando API Keys para usuário: ' . $user->id);
            
            // Update user with new API keys
            $user->api_secret = $apiSecret;
            $user->api_secret_created_at = now();
            $user->api_secret_last_used_at = null;
            $user->api_public_key = $apiPublicKey;
            $user->api_public_key_created_at = now();
            $saved = $user->save();
            
            Log::info('Save result: ' . ($saved ? 'success' : 'failed'));
            
            if (!$saved) {
                throw new \Exception('Falha ao salvar no banco de dados');
            }
            
            // Refresh user data
            $user->refresh();
            
            Log::info('API Secret salvo: ' . ($user->api_secret ? 'sim' : 'não'));
            
            return redirect()->route('api-key')->with('success', 'Chave Secreta gerada com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao gerar API Secret: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->withErrors(['error' => 'Erro ao gerar chave secreta: ' . $e->getMessage()]);
        }
    }

    /**
     * Regenerate API Secret
     */
    public function regenerateApiSecret()
    {
        try {
            $user = Auth::user();
            
            // Generate new API Secret and Public Key
            $apiSecret = $this->generateSecureApiSecret();
            $apiPublicKey = $this->generateSecureApiPublicKey();
            
            // Update user with new API keys
            $user->api_secret = $apiSecret;
            $user->api_secret_created_at = now();
            $user->api_secret_last_used_at = null;
            $user->api_public_key = $apiPublicKey;
            $user->api_public_key_created_at = now();
            $user->save();
            
            return redirect()->route('api-key')->with('success', 'Chave Secreta regenerada com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao regenerar API Secret: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erro ao regenerar chave secreta: ' . $e->getMessage()]);
        }
    }

    /**
     * Show fees configuration
     */
    public function fees()
    {
        $user = Auth::user();
        
        try {
            // First check for user's custom fees
            $userFees = [
                'pix' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'pix')
                    ->where('is_active', true)
                    ->first(),
                    
                'credit_card' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'credit_card')
                    ->where('is_active', true)
                    ->first(),
                    
                'bank_slip' => UserFee::where('user_id', $user->id)
                    ->where('payment_method', 'bank_slip')
                    ->where('is_active', true)
                    ->first(),
            ];
            
            // Get global fees as fallback
            $globalFees = FeeConfiguration::getGlobalFees();
            
            // Prepare formatted fees, prioritizing user's custom fees
            $formattedFees = [
                'pix' => [
                    'percentage' => $userFees['pix'] ? (float)$userFees['pix']->percentage_fee : ($globalFees['pix'] ? (float)$globalFees['pix']->percentage_fee : 1.99),
                    'fixed' => $userFees['pix'] ? (float)$userFees['pix']->fixed_fee : ($globalFees['pix'] ? (float)$globalFees['pix']->fixed_fee : 0.00),
                    'min' => $userFees['pix'] ? (float)$userFees['pix']->min_amount : ($globalFees['pix'] ? (float)$globalFees['pix']->min_amount : 0.01),
                    'max' => $userFees['pix'] ? (float)$userFees['pix']->max_amount : ($globalFees['pix'] ? (float)$globalFees['pix']->max_amount : null)
                ],
                'credit_card' => [
                    'percentage' => $userFees['credit_card'] ? (float)$userFees['credit_card']->percentage_fee : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->percentage_fee : 3.99),
                    'fixed' => $userFees['credit_card'] ? (float)$userFees['credit_card']->fixed_fee : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->fixed_fee : 0.39),
                    'min' => $userFees['credit_card'] ? (float)$userFees['credit_card']->min_amount : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->min_amount : 0.50),
                    'max' => $userFees['credit_card'] ? (float)$userFees['credit_card']->max_amount : ($globalFees['credit_card'] ? (float)$globalFees['credit_card']->max_amount : null)
                ],
                'bank_slip' => [
                    'percentage' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->percentage_fee : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->percentage_fee : 2.49),
                    'fixed' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->fixed_fee : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->fixed_fee : 2.00),
                    'min' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->min_amount : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->min_amount : 2.50),
                    'max' => $userFees['bank_slip'] ? (float)$userFees['bank_slip']->max_amount : ($globalFees['bank_slip'] ? (float)$globalFees['bank_slip']->max_amount : null)
                ]
            ];
            
            // Log for debugging
            Log::info('User fees loaded', [
                'user_id' => $user->id,
                'has_custom_pix' => $userFees['pix'] ? 'yes' : 'no',
                'has_custom_card' => $userFees['credit_card'] ? 'yes' : 'no',
                'has_custom_slip' => $userFees['bank_slip'] ? 'yes' : 'no',
                'formatted_fees' => $formattedFees
            ]);
            
        } catch (\Exception $e) {
            // Fallback to default fees if database is not ready
            Log::error('Error loading user fees: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            $formattedFees = [
                'pix' => [
                    'percentage' => 1.99,
                    'fixed' => 0.00,
                    'min' => 0.01,
                    'max' => null
                ],
                'credit_card' => [
                    'percentage' => 3.99,
                    'fixed' => 0.39,
                    'min' => 0.50,
                    'max' => null
                ],
                'bank_slip' => [
                    'percentage' => 2.49,
                    'fixed' => 2.00,
                    'min' => 2.50,
                    'max' => null
                ]
            ];
        }
        
        return view('settings.fees', compact('user', 'formattedFees'));
    }

    /**
     * Update withdrawal fees for user
     */
    public function updateWithdrawalFees(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate request
            $request->validate([
                'withdrawal_fee_type' => 'required|in:global,fixed,percentage,both',
                'withdrawal_fee_fixed' => 'required_if:withdrawal_fee_type,fixed,both|nullable|numeric|min:0',
                'withdrawal_fee_percentage' => 'required_if:withdrawal_fee_type,percentage,both|nullable|numeric|min:0|max:100',
            ]);
            
            // Update fees based on type
            $updateData = [
                'withdrawal_fee_type' => $request->withdrawal_fee_type
            ];
            
            // Set fee values based on type
            switch ($request->withdrawal_fee_type) {
                case 'global':
                    $updateData['withdrawal_fee_fixed'] = 0.00;
                    $updateData['withdrawal_fee_percentage'] = 0.00;
                    break;
                case 'fixed':
                    $updateData['withdrawal_fee_fixed'] = $request->withdrawal_fee_fixed ?? 0.00;
                    $updateData['withdrawal_fee_percentage'] = 0.00;
                    break;
                case 'percentage':
                    $updateData['withdrawal_fee_fixed'] = 0.00;
                    $updateData['withdrawal_fee_percentage'] = $request->withdrawal_fee_percentage ?? 0.00;
                    break;
                case 'both':
                    $updateData['withdrawal_fee_fixed'] = $request->withdrawal_fee_fixed ?? 0.00;
                    $updateData['withdrawal_fee_percentage'] = $request->withdrawal_fee_percentage ?? 0.00;
                    break;
            }
            
            $user->update($updateData);
            
            // Clear user cache
            \Cache::forget('user_' . $user->id);
            
            // Log the action
            Log::info('Taxas de saque atualizadas pelo usuário', [
                'user_id' => $user->id,
                'update_data' => $updateData
            ]);
            
            // Return JSON if AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Taxa de saque atualizada com sucesso!',
                    'data' => $updateData
                ]);
            }
            
            return back()->with('success', 'Taxa de saque atualizada com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar taxa de saque: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return JSON if AJAX request
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar taxa de saque: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Erro ao atualizar taxa de saque: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate secure API Secret
     * Format: sk_ + 51 caracteres alfanuméricos (mantendo maiúsculas e minúsculas)
     */
    private function generateSecureApiSecret(): string
    {
        // Gerar 51 caracteres alfanuméricos aleatórios (mantendo maiúsculas e minúsculas)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 51;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'sk_' . $randomString;
    }

    /**
     * Generate secure API Public Key
     * Format: pk_ + 51 caracteres alfanuméricos (mantendo maiúsculas e minúsculas)
     */
    private function generateSecureApiPublicKey(): string
    {
        // Gerar 51 caracteres alfanuméricos aleatórios (mantendo maiúsculas e minúsculas)
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 51;
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'pk_' . $randomString;
    }
}