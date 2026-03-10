<?php

namespace App\Http\Controllers;

use App\Models\DocumentVerification;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    /**
     * Show the document upload form
     */
    public function index()
    {
        $user = Auth::user();
        $verification = $user->documentVerification;

        // Se não existe verificação, criar uma nova
        if (!$verification) {
            $verification = DocumentVerification::create([
                'user_id' => $user->id,
                'status' => 'pendente'
            ]);
        }

        return view('documents.index', compact('user', 'verification'));
    }

    /**
     * Upload documents
     */
    public function upload(Request $request)
    {
        try {
            $user = Auth::user();
            $verification = $user->documentVerification;

            // Se não existe verificação, criar uma nova
            if (!$verification) {
                $verification = DocumentVerification::create([
                    'user_id' => $user->id,
                    'status' => 'pendente'
                ]);
            }

            // Verificar se já foi aprovado
            if ($verification->isApproved()) {
                return back()->withErrors(['error' => 'Documentos já foram aprovados. Não é possível alterar.']);
            }
            
            // Verificar se já foi submetido e tem todos os documentos
            // A menos que tenha sido rejeitado
            if ($verification->submitted_at && $verification->hasAllDocuments() && !$verification->isRejected()) {
                return back()->withErrors(['error' => 'Documentos já foram enviados e estão em análise. Não é possível alterar.']);
            }

            // Log dos arquivos recebidos para debug
            Log::info('Arquivos recebidos no upload:', [
                'front_document' => $request->hasFile('front_document') ? [
                    'name' => $request->file('front_document')->getClientOriginalName(),
                    'size' => $request->file('front_document')->getSize(),
                    'mime' => $request->file('front_document')->getMimeType()
                ] : 'não enviado',
                'back_document' => $request->hasFile('back_document') ? [
                    'name' => $request->file('back_document')->getClientOriginalName(),
                    'size' => $request->file('back_document')->getSize(),
                    'mime' => $request->file('back_document')->getMimeType()
                ] : 'não enviado',
                'selfie_document' => $request->hasFile('selfie_document') ? [
                    'name' => $request->file('selfie_document')->getClientOriginalName(),
                    'size' => $request->file('selfie_document')->getSize(),
                    'mime' => $request->file('selfie_document')->getMimeType()
                ] : 'não enviado',
            ]);

            // Validação dos arquivos (10MB = 10240 KB)
            $rules = [
                'front_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'back_document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'selfie_document' => 'nullable|file|mimes:jpg,jpeg,png|max:10240',
                'contrato_social' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            ];

            $request->validate($rules, [
                'front_document.file' => 'O documento frontal deve ser um arquivo válido.',
                'front_document.mimes' => 'O documento frontal deve ser JPG, PNG ou PDF.',
                'front_document.max' => 'O documento frontal deve ter no máximo 10MB. Comprima o arquivo se necessário.',
                'back_document.file' => 'O documento traseiro deve ser um arquivo válido.',
                'back_document.mimes' => 'O documento traseiro deve ser JPG, PNG ou PDF.',
                'back_document.max' => 'O documento traseiro deve ter no máximo 10MB. Comprima o arquivo se necessário.',
                'selfie_document.file' => 'A selfie deve ser um arquivo válido.',
                'selfie_document.mimes' => 'A selfie deve ser JPG ou PNG.',
                'selfie_document.max' => 'A selfie deve ter no máximo 10MB. Comprima o arquivo se necessário.',
                'contrato_social.file' => 'O contrato social deve ser um arquivo válido.',
                'contrato_social.mimes' => 'O contrato social deve ser JPG, PNG ou PDF.',
                'contrato_social.max' => 'O contrato social deve ter no máximo 10MB. Comprima o arquivo se necessário.',
            ]);

            $updateData = [];
            $cloudinary = new CloudinaryService();

            // Mapear contrato_social para proof_address
            $fieldMapping = [
                'contrato_social' => 'proof_address',
            ];

            // Processar cada arquivo enviado
            foreach ($rules as $field => $rule) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    
                    // Determinar o campo de destino (mapear se necessário)
                    $targetField = $fieldMapping[$field] ?? $field;
                    
                    // Upload para Cloudinary
                    $result = $cloudinary->uploadVerificationDocument($file, $user->id, $targetField);
                    
                    if (!$result['success']) {
                        return back()->withErrors(['error' => $result['error']]);
                    }

                    $updateData[$targetField] = $result['url'];

                    Log::info("Arquivo {$field} (mapeado para {$targetField}) enviado para Cloudinary: {$result['url']}");
                }
            }

            // Atualizar verificação
            if (!empty($updateData)) {
                // Se foi rejeitado e está reenviando, reseta o status para pendente
                if ($verification->isRejected()) {
                    $updateData['status'] = 'pendente';
                    $updateData['rejection_reason'] = null;
                }
                
                $verification->update($updateData);

                // Se todos os documentos foram enviados, marcar como submetido
                if ($verification->fresh()->hasAllDocuments()) {
                    $verification->update([
                        'submitted_at' => now(),
                        'status' => 'pendente'
                    ]);
                    
                    return redirect()->route('documents.index')
                        ->with('success', 'Todos os documentos foram enviados com sucesso! Aguarde a análise.');
                }

                return redirect()->route('documents.index')
                    ->with('success', 'Documentos enviados com sucesso!');
            }

            return back()->withErrors(['error' => 'Nenhum arquivo foi selecionado.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erros de validação já são tratados automaticamente
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erro no upload de documentos: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);
            
            // Mensagem amigável para produção
            $errorMessage = 'Erro ao fazer upload da imagem. Tente novamente em alguns instantes.';
            
            // Em desenvolvimento, mostrar erro detalhado
            if (config('app.debug')) {
                $errorMessage = 'Erro ao enviar documentos: ' . $e->getMessage();
            }
            
            return back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }

    /**
     * Delete a specific document
     */
    public function deleteDocument(Request $request)
    {
        try {
            $user = Auth::user();
            $verification = $user->documentVerification;

            if (!$verification || $verification->isApproved()) {
                return response()->json(['error' => 'Não é possível deletar documentos.'], 403);
            }
            
            // Verificar se já foi submetido e tem todos os documentos
            // A menos que tenha sido rejeitado
            if ($verification->submitted_at && $verification->hasAllDocuments() && !$verification->isRejected()) {
                return response()->json(['error' => 'Documentos já foram enviados e estão em análise. Não é possível alterar.'], 403);
            }

            $field = $request->input('field');
            $allowedFields = ['front_document', 'back_document', 'selfie_document', 'proof_address'];

            if (!in_array($field, $allowedFields) || !$verification->$field) {
                return response()->json(['error' => 'Documento não encontrado.'], 404);
            }

            // Atualizar banco de dados (Cloudinary mantém o arquivo)
            $verification->update([$field => null]);

            return response()->json(['success' => 'Documento removido com sucesso.']);

        } catch (\Exception $e) {
            Log::error('Erro ao deletar documento: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao deletar documento.'], 500);
        }
    }

    /**
     * View a document (protected route)
     */
    public function viewDocument($filename)
    {
        try {
            $user = Auth::user();
            
            // Verificar se o arquivo pertence ao usuário
            // O filename pode ser o caminho completo ou apenas o nome do arquivo
            $verification = $user->documentVerification;
            
            if (!$verification) {
                abort(404, 'Documento não encontrado.');
            }
            
            // Verificar se o arquivo está em algum dos campos de documento
            $documentFields = ['front_document', 'back_document', 'selfie_document', 'proof_address'];
            $documentUrl = null;
            $documentField = null;
            
            foreach ($documentFields as $field) {
                if ($verification->$field) {
                    // Se o filename está na URL do documento
                    if (str_contains($verification->$field, $filename)) {
                        $documentUrl = $verification->$field;
                        $documentField = $field;
                        break;
                    }
                }
            }
            
            if (!$documentUrl) {
                abort(404, 'Documento não encontrado.');
            }
            
            // Se for URL do Cloudinary, redirecionar
            if (str_contains($documentUrl, 'cloudinary.com') || (str_starts_with($documentUrl, 'http://') || str_starts_with($documentUrl, 'https://'))) {
                return redirect($documentUrl);
            }
            
            // Extrair o caminho do arquivo da URL
            // URL pode ser: /storage/documents/users/3/verification/front_document/images/file.png
            // ou: storage/documents/users/3/verification/front_document/images/file.png
            $path = str_replace(['/storage/', 'storage/'], '', $documentUrl);
            $path = str_replace(config('app.url') . '/', '', $path);
            
            // Tentar diferentes caminhos possíveis
            $possiblePaths = [
                storage_path('app/public/' . $path),
                storage_path('app/public/documents/users/' . $user->id . '/verification/' . $documentField . '/images/' . $filename),
                public_path('storage/' . $path),
            ];
            
            foreach ($possiblePaths as $filePath) {
                if (File::exists($filePath)) {
                    return response()->file($filePath);
                }
            }
            
            // Se não encontrou, tentar pelo Storage
            $storagePath = 'documents/users/' . $user->id . '/verification/' . $documentField . '/images/' . $filename;
            if (Storage::disk('public')->exists($storagePath)) {
                return Storage::disk('public')->response($storagePath);
            }
            
            abort(404, 'Arquivo não encontrado.');
            
        } catch (\Exception $e) {
            Log::error('Erro ao visualizar documento: ' . $e->getMessage(), [
                'filename' => $filename,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(404, 'Documento não encontrado.');
        }
    }
}
