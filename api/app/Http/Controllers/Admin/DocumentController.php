<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentVerification;
use App\Models\User;
use App\Models\UserFee;
use App\Models\FeeConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of pending document verifications
     */
    public function index()
    {
        $pendingVerifications = DocumentVerification::where('status', 'pendente')
            ->whereNotNull('submitted_at')
            ->with('user')
            ->latest('submitted_at')
            ->get();
            
        return view('admin.documents.index', compact('pendingVerifications'));
    }
    
    /**
     * Get document verification details
     */
    public function details($userId)
    {
        try {
            $user = User::with(['documentVerification', 'transactions' => function($query) {
                $query->where('status', 'paid')->latest()->limit(5);
            }])->findOrFail($userId);
            
            $verification = $user->documentVerification;
            
            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'error' => 'Verificação não encontrada'
                ]);
            }
            
            // Normalizar URLs dos documentos para garantir que funcionem
            $documentFields = ['front_document', 'back_document', 'selfie_document', 'proof_address', 'income_proof', 'financial_statement'];
            foreach ($documentFields as $field) {
                if ($verification->$field) {
                    $docUrl = $verification->$field;
                    
                    // Se for URL do Cloudinary, manter como está
                    if (str_contains($docUrl, 'cloudinary.com')) {
                        continue;
                    }
                    
                    // Se já for uma URL HTTP completa e não for Cloudinary, verificar se é local
                    if (str_starts_with($docUrl, 'http://') || str_starts_with($docUrl, 'https://')) {
                        // Se for URL local (localhost ou mesmo domínio), converter para rota do admin
                        if (str_contains($docUrl, '/storage/') || str_contains($docUrl, 'localhost') || str_contains($docUrl, parse_url(config('app.url'), PHP_URL_HOST))) {
                            // Extrair filename da URL
                            if (str_contains($docUrl, '/images/')) {
                                $parts = explode('/images/', $docUrl);
                                if (count($parts) > 1) {
                                    $filename = basename($parts[1]);
                                    $verification->$field = route('admin.documents.serve', [
                                        'userId' => $userId,
                                        'field' => $field,
                                        'filename' => $filename
                                    ]);
                                    continue;
                                }
                            }
                        } else {
                            // URL externa válida, manter como está
                            continue;
                        }
                    }
                    
                    // Se for caminho relativo ou local, usar rota do admin para servir
                    if (str_contains($docUrl, '/images/')) {
                        $parts = explode('/images/', $docUrl);
                        if (count($parts) > 1) {
                            $filename = basename($parts[1]);
                            $verification->$field = route('admin.documents.serve', [
                                'userId' => $userId,
                                'field' => $field,
                                'filename' => $filename
                            ]);
                        } else {
                            // Fallback para asset() se não conseguir extrair filename
                            if (str_starts_with($docUrl, '/storage/')) {
                                $verification->$field = asset($docUrl);
                            } else {
                                $verification->$field = asset('storage/' . ltrim($docUrl, '/'));
                            }
                        }
                    } else {
                        // Fallback para asset() se não tiver /images/
                        if (str_starts_with($docUrl, '/storage/')) {
                            $verification->$field = asset($docUrl);
                        } elseif (!str_starts_with($docUrl, 'http')) {
                            if (str_contains($docUrl, 'storage/')) {
                                $verification->$field = asset($docUrl);
                            } else {
                                $verification->$field = asset('storage/' . ltrim($docUrl, '/'));
                            }
                        }
                    }
                }
            }
            
            // Calculate average revenue and ticket
            $avgRevenue = $user->transactions()->where('status', 'paid')->avg('amount') ?? 0;
            $avgTicket = $user->transactions()->where('status', 'paid')->avg('amount') ?? 0;
            
            $html = View::make('admin.documents.details', compact('user', 'verification', 'avgRevenue', 'avgTicket'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes da verificação: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar detalhes da verificação'
            ]);
        }
    }
    
    /**
     * Respond to document verification request
     */
    public function respond(Request $request)
    {
        try {
            $request->validate([
                'verification_id' => 'required|exists:document_verifications,id',
                'user_id' => 'required|exists:users,id',
                'status' => 'required|in:aprovado,recusado',
                'rejection_reason' => 'required_if:status,recusado'
            ]);
            
            DB::beginTransaction();
            
            $verification = DocumentVerification::findOrFail($request->verification_id);
            $user = User::findOrFail($request->user_id);
            
            // Update verification status
            $verification->status = $request->status;
            $verification->reviewed_at = now();
            $verification->reviewed_by = auth()->id();
            
            if ($request->status === 'recusado') {
                $verification->rejection_reason = $request->rejection_reason;
                
                // Delete all documents if rejected
                $this->deleteAllDocuments($verification);
            }
            
            $verification->save();
            
            // Limpar cache de verificação de documentos
            Cache::forget("user_doc_approved_{$user->id}");
            
            // If approved, assign default gateway
            if ($request->status === 'aprovado') {
                $defaultGateway = \App\Models\PaymentGateway::where('is_default', true)->first();
                
                if ($defaultGateway) {
                    $user->assigned_gateway_id = $defaultGateway->id;
                    $user->save();
                }
                
                // Create default fees for user
                $this->createDefaultFees($user->id);
                
                // Atualizar cache com status aprovado
                Cache::put("user_doc_approved_{$user->id}", true, 300);
            } else {
                // Se foi rejeitado, atualizar cache com status não aprovado
                Cache::put("user_doc_approved_{$user->id}", false, 300);
            }
            
            DB::commit();
            
            return redirect()->route('admin.documents.index')
                ->with('success', 'Solicitação ' . ($request->status === 'aprovado' ? 'aprovada' : 'recusada') . ' com sucesso!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao responder solicitação: ' . $e->getMessage());
            
            return back()->withErrors(['error' => 'Erro ao processar solicitação: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Delete all documents for a verification
     */
    private function deleteAllDocuments(DocumentVerification $verification)
    {
        try {
            $fields = [
                'front_document',
                'back_document',
                'selfie_document',
                'proof_address',
                'income_proof',
                'financial_statement'
            ];
            
            foreach ($fields as $field) {
                if ($verification->$field) {
                    // Delete file from public directory
                    $filePath = public_path('usuarios/docs/documents/' . $verification->$field);
                    if (File::exists($filePath)) {
                        File::delete($filePath);
                    }
                    
                    // Clear field in database
                    $verification->$field = null;
                }
            }
            
            // Save changes to verification
            $verification->save();
            
            Log::info('Todos os documentos foram excluídos após rejeição', [
                'verification_id' => $verification->id,
                'user_id' => $verification->user_id
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Erro ao excluir documentos: ' . $e->getMessage(), [
                'verification_id' => $verification->id
            ]);
            
            return false;
        }
    }
    
    /**
     * Serve document file for admin viewing
     */
    public function serveDocument($userId, $field, $filename)
    {
        // Log para debug - verificar se o método está sendo chamado
        \Log::info('=== serveDocument CHAMADO ===', [
            'user_id' => $userId,
            'field' => $field,
            'filename' => $filename,
            'request_uri' => request()->getRequestUri(),
            'full_url' => request()->fullUrl()
        ]);
        
        try {
            
            $user = User::findOrFail($userId);
            $verification = $user->documentVerification;
            
            if (!$verification) {
                Log::warning('Verificação não encontrada', ['user_id' => $userId]);
                abort(404, 'Verificação não encontrada');
            }
            
            // Validar campo
            $allowedFields = ['front_document', 'back_document', 'selfie_document', 'proof_address', 'income_proof', 'financial_statement'];
            if (!in_array($field, $allowedFields)) {
                Log::warning('Campo inválido', ['field' => $field]);
                abort(404, 'Campo inválido');
            }
            
            $documentUrl = $verification->$field;
            
            if (!$documentUrl) {
                Log::warning('Documento não encontrado no banco', [
                    'user_id' => $userId,
                    'field' => $field
                ]);
                abort(404, 'Documento não encontrado');
            }
            
            Log::info('URL do documento encontrada', [
                'document_url' => $documentUrl,
                'user_id' => $userId,
                'field' => $field
            ]);
            
            // Se for URL do Cloudinary, redirecionar
            if (str_contains($documentUrl, 'cloudinary.com') || (str_starts_with($documentUrl, 'http://') || str_starts_with($documentUrl, 'https://'))) {
                // Se for URL completa do Cloudinary, redirecionar
                if (str_contains($documentUrl, 'cloudinary.com')) {
                    return redirect($documentUrl);
                }
                // Se for URL local completa (http://localhost:8000/storage/...), extrair o caminho
                if (str_contains($documentUrl, '/storage/')) {
                    $path = parse_url($documentUrl, PHP_URL_PATH);
                    $path = str_replace('/storage/', '', $path);
                } else {
                    $path = str_replace(['/storage/', 'storage/'], '', $documentUrl);
                    $path = str_replace(config('app.url') . '/', '', $path);
                }
            } else {
                // URL relativa
                $path = str_replace(['/storage/', 'storage/'], '', $documentUrl);
            }
            
            // Tentar extrair o filename da URL completa primeiro
            $actualFilename = $filename;
            if (str_contains($documentUrl, '/images/')) {
                $parts = explode('/images/', $documentUrl);
                if (count($parts) > 1) {
                    $extractedFilename = basename($parts[1]);
                    if ($extractedFilename) {
                        $actualFilename = $extractedFilename;
                    }
                }
            }
            
            // Tentar pelo Storage primeiro (mais confiável)
            $storagePath = 'documents/users/' . $userId . '/verification/' . $field . '/images/' . $actualFilename;
            Log::info('Tentando buscar documento', [
                'storage_path' => $storagePath,
                'exists' => Storage::disk('public')->exists($storagePath)
            ]);
            
            if (Storage::disk('public')->exists($storagePath)) {
                Log::info('Documento encontrado via Storage', [
                    'storage_path' => $storagePath,
                    'user_id' => $userId,
                    'field' => $field,
                    'filename' => $actualFilename
                ]);
                return Storage::disk('public')->response($storagePath);
            }
            
            // Tentar com o filename original também
            if ($actualFilename !== $filename) {
                $storagePath = 'documents/users/' . $userId . '/verification/' . $field . '/images/' . $filename;
                Log::info('Tentando buscar documento com filename original', [
                    'storage_path' => $storagePath,
                    'exists' => Storage::disk('public')->exists($storagePath)
                ]);
                
                if (Storage::disk('public')->exists($storagePath)) {
                    Log::info('Documento encontrado via Storage (filename original)', [
                        'storage_path' => $storagePath,
                        'user_id' => $userId,
                        'field' => $field,
                        'filename' => $filename
                    ]);
                    return Storage::disk('public')->response($storagePath);
                }
            }
            
            // Tentar diferentes caminhos possíveis no filesystem
            $possiblePaths = [
                storage_path('app/public/documents/users/' . $userId . '/verification/' . $field . '/images/' . $actualFilename),
                storage_path('app/public/documents/users/' . $userId . '/verification/' . $field . '/images/' . $filename),
                storage_path('app/public/' . $path),
                public_path('storage/documents/users/' . $userId . '/verification/' . $field . '/images/' . $actualFilename),
                public_path('storage/documents/users/' . $userId . '/verification/' . $field . '/images/' . $filename),
            ];
            
            foreach ($possiblePaths as $filePath) {
                Log::info('Tentando caminho do filesystem', [
                    'path' => $filePath,
                    'exists' => File::exists($filePath)
                ]);
                
                if (File::exists($filePath)) {
                    Log::info('Documento encontrado via filesystem', ['path' => $filePath]);
                    return response()->file($filePath);
                }
            }
            
            Log::warning('Documento não encontrado em nenhum caminho', [
                'user_id' => $userId,
                'field' => $field,
                'filename' => $filename,
                'actual_filename' => $actualFilename,
                'document_url' => $documentUrl,
                'storage_path_tried' => $storagePath,
                'possible_paths' => $possiblePaths,
            ]);
            
            abort(404, 'Arquivo não encontrado');
            
        } catch (\Exception $e) {
            Log::error('Erro ao servir documento: ' . $e->getMessage(), [
                'user_id' => $userId,
                'field' => $field,
                'filename' => $filename,
                'trace' => $e->getTraceAsString()
            ]);
            abort(404, 'Erro ao carregar documento');
        }
    }
    
    /**
     * Update user fees
     */
    public function updateFees(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Validate request
            $request->validate([
                'pix_fixed' => 'required|numeric|min:0',
                'pix_percentage' => 'required|numeric|min:0|max:100',
                'pix_min_transaction' => 'nullable|numeric|min:0',
                'pix_max_transaction' => 'nullable|numeric|min:0',
                'credit_card_fixed' => 'required|numeric|min:0',
                'credit_card_percentage' => 'required|numeric|min:0|max:100',
                'credit_card_min_transaction' => 'nullable|numeric|min:0',
                'credit_card_max_transaction' => 'nullable|numeric|min:0',
                'bank_slip_fixed' => 'required|numeric|min:0',
                'bank_slip_percentage' => 'required|numeric|min:0|max:100',
                'bank_slip_min_transaction' => 'nullable|numeric|min:0',
                'bank_slip_max_transaction' => 'nullable|numeric|min:0',
            ]);
            
            DB::beginTransaction();
            
            // Update or create PIX fee
            UserFee::updateOrCreate(
                ['user_id' => $userId, 'payment_method' => 'pix'],
                [
                    'percentage_fee' => $request->pix_percentage,
                    'fixed_fee' => $request->pix_fixed,
                    'min_amount' => 0.01,
                    'min_transaction_value' => $request->pix_min_transaction,
                    'max_transaction_value' => $request->pix_max_transaction,
                    'is_active' => true
                ]
            );
            
            // Update or create Credit Card fee
            UserFee::updateOrCreate(
                ['user_id' => $userId, 'payment_method' => 'credit_card'],
                [
                    'percentage_fee' => $request->credit_card_percentage,
                    'fixed_fee' => $request->credit_card_fixed,
                    'min_amount' => 0.50,
                    'min_transaction_value' => $request->credit_card_min_transaction,
                    'max_transaction_value' => $request->credit_card_max_transaction,
                    'is_active' => true
                ]
            );
            
            // Update or create Bank Slip fee
            UserFee::updateOrCreate(
                ['user_id' => $userId, 'payment_method' => 'bank_slip'],
                [
                    'percentage_fee' => $request->bank_slip_percentage,
                    'fixed_fee' => $request->bank_slip_fixed,
                    'min_amount' => 2.50,
                    'min_transaction_value' => $request->bank_slip_min_transaction,
                    'max_transaction_value' => $request->bank_slip_max_transaction,
                    'is_active' => true
                ]
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Taxas atualizadas com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar taxas: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao atualizar taxas: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Create default fees for user
     */
    private function createDefaultFees($userId)
    {
        // Get global fees
        $globalFees = FeeConfiguration::where('is_global', true)
            ->where('is_active', true)
            ->get()
            ->keyBy('payment_method');
            
        // Create PIX fee
        if (isset($globalFees['pix'])) {
            UserFee::updateOrCreate(
                [
                    'user_id' => $userId,
                    'payment_method' => 'pix',
                ],
                [
                'percentage_fee' => $globalFees['pix']->percentage_fee,
                'fixed_fee' => $globalFees['pix']->fixed_fee,
                'min_amount' => $globalFees['pix']->min_amount,
                'max_amount' => $globalFees['pix']->max_amount,
                'min_transaction_value' => $globalFees['pix']->min_transaction_value,
                'max_transaction_value' => $globalFees['pix']->max_transaction_value,
                'is_active' => true
                ]
            );
        }
        
        // Create Credit Card fee
        if (isset($globalFees['credit_card'])) {
            UserFee::updateOrCreate(
                [
                    'user_id' => $userId,
                    'payment_method' => 'credit_card',
                ],
                [
                'percentage_fee' => $globalFees['credit_card']->percentage_fee,
                'fixed_fee' => $globalFees['credit_card']->fixed_fee,
                'min_amount' => $globalFees['credit_card']->min_amount,
                'max_amount' => $globalFees['credit_card']->max_amount,
                'min_transaction_value' => $globalFees['credit_card']->min_transaction_value,
                'max_transaction_value' => $globalFees['credit_card']->max_transaction_value,
                'is_active' => true
                ]
            );
        }
        
        // Create Bank Slip fee
        if (isset($globalFees['bank_slip'])) {
            UserFee::updateOrCreate(
                [
                    'user_id' => $userId,
                    'payment_method' => 'bank_slip',
                ],
                [
                'percentage_fee' => $globalFees['bank_slip']->percentage_fee,
                'fixed_fee' => $globalFees['bank_slip']->fixed_fee,
                'min_amount' => $globalFees['bank_slip']->min_amount,
                'max_amount' => $globalFees['bank_slip']->max_amount,
                'min_transaction_value' => $globalFees['bank_slip']->min_transaction_value,
                'max_transaction_value' => $globalFees['bank_slip']->max_transaction_value,
                'is_active' => true
                ]
            );
        }
    }
}