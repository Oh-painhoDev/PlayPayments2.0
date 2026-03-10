<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloudinaryService
{

    /**
     * Comprimir imagem para reduzir tamanho
     */
    private function compressImage(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();
        $originalPath = $file->getRealPath();
        
        // Se não for imagem JPG/PNG, retorna original
        if (!in_array($mimeType, ['image/jpeg', 'image/jpg', 'image/png'])) {
            return $originalPath;
        }
        
        // Verificar se extensão GD está disponível
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagecreatefrompng')) {
            Log::warning('Extensão GD não disponível. Usando imagem original sem compressão.');
            return $originalPath;
        }
        
        // Criar imagem a partir do arquivo
        try {
            if ($mimeType === 'image/png') {
                $image = imagecreatefrompng($originalPath);
            } else {
                $image = imagecreatefromjpeg($originalPath);
            }
            
            if (!$image) {
                Log::warning('Falha ao criar imagem. Usando original.');
                return $originalPath; // Se falhar, usa original
            }
            
            // Obter dimensões originais
            $width = imagesx($image);
            $height = imagesy($image);
            
            // Redimensionar se maior que 1920px
            $maxDimension = 1920;
            if ($width > $maxDimension || $height > $maxDimension) {
                $ratio = min($maxDimension / $width, $maxDimension / $height);
                $newWidth = (int)($width * $ratio);
                $newHeight = (int)($height * $ratio);
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                // Preservar transparência para PNG
                if ($mimeType === 'image/png') {
                    imagealphablending($newImage, false);
                    imagesavealpha($newImage, true);
                }
                
                imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $newImage;
            }
            
            // Salvar com compressão (preservar formato original)
            $compressedPath = sys_get_temp_dir() . '/' . uniqid() . '_compressed.' . ($mimeType === 'image/png' ? 'png' : 'jpg');
            
            if ($mimeType === 'image/png') {
                imagepng($image, $compressedPath, 6); // 6 = nível de compressão (0-9)
            } else {
                imagejpeg($image, $compressedPath, 75); // 75% qualidade
            }
            
            imagedestroy($image);
            
            Log::info('Imagem comprimida', [
                'original_size' => $file->getSize(),
                'compressed_size' => filesize($compressedPath),
                'reduction' => round((1 - filesize($compressedPath) / $file->getSize()) * 100, 2) . '%'
            ]);
            
            return $compressedPath;
            
        } catch (\Exception $e) {
            Log::error('Erro ao comprimir imagem: ' . $e->getMessage());
            return $originalPath; // Retorna original em caso de erro
        }
    }

    /**
     * Verifica se Cloudinary está configurado
     */
    private function isCloudinaryConfigured(): bool
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $apiKey = env('CLOUDINARY_API_KEY');
        $apiSecret = env('CLOUDINARY_API_SECRET');
        $cloudUrl = env('CLOUDINARY_URL');
        
        return !empty($cloudUrl) || (!empty($cloudName) && !empty($apiKey) && !empty($apiSecret));
    }

    /**
     * Upload de imagem para o Cloudinary ou armazenamento local
     */
    public function uploadImage(UploadedFile $file, string $folder = 'documents'): array
    {
        try {
            Log::info('Iniciando upload de imagem', [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'folder' => $folder,
                'cloudinary_configured' => $this->isCloudinaryConfigured()
            ]);

            // Se Cloudinary estiver configurado, tenta usar
            if ($this->isCloudinaryConfigured()) {
                try {
                    // Comprimir imagem antes de enviar
                    $uploadPath = $this->compressImage($file);

                    $result = cloudinary()->uploadApi()->upload($uploadPath, [
                        'folder' => $folder,
                        'public_id' => uniqid(),
                    ]);
                    
                    // Limpar arquivo temporário se foi criado
                    if ($uploadPath !== $file->getRealPath() && file_exists($uploadPath)) {
                        @unlink($uploadPath);
                    }
                    
                    Log::info('Upload Cloudinary bem-sucedido', ['result' => $result]);
                    
                    if ($result && isset($result['secure_url'])) {
                        return [
                            'success' => true,
                            'url' => $result['secure_url'],
                            'public_id' => $result['public_id'],
                            'format' => $result['format'] ?? 'unknown',
                            'resource_type' => 'image',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('Erro ao usar Cloudinary, usando armazenamento local: ' . $e->getMessage());
                }
            }

            // Fallback: armazenamento local
            return $this->uploadImageLocal($file, $folder);

        } catch (\Exception $e) {
            Log::error('Erro no upload de imagem: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'original_file' => $file->getClientOriginalName()
            ]);
            
            // Mensagem amigável para produção
            $errorMessage = 'Erro ao fazer upload da imagem. Tente novamente em alguns instantes.';
            
            // Detectar tipo de erro e dar mensagem mais específica
            $errorMsg = strtolower($e->getMessage());
            if (strpos($errorMsg, 'permission') !== false || strpos($errorMsg, 'permissão') !== false) {
                $errorMessage = 'Erro de permissão. Verifique as configurações do servidor.';
            } elseif (strpos($errorMsg, 'disk') !== false || strpos($errorMsg, 'storage') !== false) {
                $errorMessage = 'Erro ao acessar armazenamento. Verifique as configurações.';
            } elseif (strpos($errorMsg, 'size') !== false || strpos($errorMsg, 'tamanho') !== false) {
                $errorMessage = 'Arquivo muito grande. Tente comprimir a imagem antes de enviar.';
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }
    }

    /**
     * Upload de imagem para armazenamento local
     */
    private function uploadImageLocal(UploadedFile $file, string $folder): array
    {
        try {
            // Verificar se o diretório existe e tem permissão de escrita
            $storageDisk = Storage::disk('public');
            $storagePath = 'documents/' . $folder;
            
            // Criar diretório se não existir
            if (!$storageDisk->exists($storagePath)) {
                $storageDisk->makeDirectory($storagePath, 0755, true);
            }
            
            // Comprimir imagem antes de salvar (se possível)
            $uploadPath = $this->compressImage($file);
            
            // Determinar extensão correta
            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension() ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
            
            // Validar extensão
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                // Tentar detectar pelo mime type
                $mimeExtensions = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'application/pdf' => 'pdf'
                ];
                $extension = $mimeExtensions[$mimeType] ?? 'jpg';
            }
            
            // Se foi comprimido, usar extensão do arquivo comprimido
            if ($uploadPath !== $file->getRealPath() && file_exists($uploadPath)) {
                $compressedExtension = strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION));
                if ($compressedExtension && in_array($compressedExtension, $allowedExtensions)) {
                    $extension = $compressedExtension;
                }
            }
            
            // Gerar nome único para o arquivo
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $fullPath = $storagePath . '/' . $fileName;
            
            // Salvar arquivo
            try {
                if ($uploadPath !== $file->getRealPath() && file_exists($uploadPath) && is_readable($uploadPath)) {
                    // Se foi comprimido, move o arquivo comprimido
                    $fileContent = @file_get_contents($uploadPath);
                    if ($fileContent !== false) {
                        $storageDisk->put($fullPath, $fileContent);
                        @unlink($uploadPath); // Limpar arquivo temporário
                    } else {
                        // Fallback: usar arquivo original
                        $storageDisk->putFileAs($storagePath, $file, $fileName);
                    }
                } else {
                    // Senão, salva o arquivo original
                    $storageDisk->putFileAs($storagePath, $file, $fileName);
                }
            } catch (\Exception $saveException) {
                Log::warning('Erro ao salvar arquivo comprimido, tentando original: ' . $saveException->getMessage());
                // Fallback: sempre salvar o original
                $storageDisk->putFileAs($storagePath, $file, $fileName);
            }
            
            // Verificar se arquivo foi salvo
            if (!$storageDisk->exists($fullPath)) {
                throw new \Exception('Falha ao salvar arquivo no servidor. Verifique permissões.');
            }
            
            // Gerar URL pública
            $url = $storageDisk->url($fullPath);
            
            // Garantir URL absoluta
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $baseUrl = config('app.url');
                $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
            }
            
            Log::info('Upload local bem-sucedido', [
                'path' => $fullPath,
                'url' => $url,
                'extension' => $extension,
                'file_size' => $storageDisk->size($fullPath)
            ]);
            
            return [
                'success' => true,
                'url' => $url,
                'public_id' => $fullPath,
                'format' => $extension,
                'resource_type' => 'image',
            ];

        } catch (\Exception $e) {
            Log::error('Erro no upload local: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Upload de PDF para o Cloudinary ou armazenamento local
     */
    public function uploadPdf(UploadedFile $file, string $folder = 'documents/pdfs'): array
    {
        try {
            Log::info('Iniciando upload de PDF', [
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'folder' => $folder,
                'cloudinary_configured' => $this->isCloudinaryConfigured()
            ]);

            // Se Cloudinary estiver configurado, tenta usar
            if ($this->isCloudinaryConfigured()) {
                try {
                    $result = cloudinary()->uploadApi()->upload($file->getRealPath(), [
                        'folder' => $folder,
                        'public_id' => uniqid(),
                        'resource_type' => 'raw',
                    ]);
                    
                    Log::info('Upload PDF Cloudinary bem-sucedido', ['result' => $result]);

                    if ($result && isset($result['secure_url'])) {
                        return [
                            'success' => true,
                            'url' => $result['secure_url'],
                            'public_id' => $result['public_id'],
                            'format' => 'pdf',
                            'resource_type' => 'raw',
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('Erro ao usar Cloudinary, usando armazenamento local: ' . $e->getMessage());
                }
            }

            // Fallback: armazenamento local
            return $this->uploadPdfLocal($file, $folder);

        } catch (\Exception $e) {
            Log::error('Erro no upload de PDF: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'original_file' => $file->getClientOriginalName()
            ]);
            
            // Mensagem amigável para produção
            $errorMessage = 'Erro ao fazer upload do PDF. Tente novamente.';
            
            // Detectar tipo de erro e dar mensagem mais específica
            $errorMsg = strtolower($e->getMessage());
            if (strpos($errorMsg, 'permission') !== false || strpos($errorMsg, 'permissão') !== false) {
                $errorMessage = 'Erro de permissão. Verifique as configurações do servidor.';
            } elseif (strpos($errorMsg, 'disk') !== false || strpos($errorMsg, 'storage') !== false) {
                $errorMessage = 'Erro ao acessar armazenamento. Verifique as configurações.';
            } elseif (strpos($errorMsg, 'size') !== false || strpos($errorMsg, 'tamanho') !== false) {
                $errorMessage = 'Arquivo muito grande. O limite é de 2MB.';
            }
            
            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        }
    }

    /**
     * Upload de PDF para armazenamento local
     */
    private function uploadPdfLocal(UploadedFile $file, string $folder): array
    {
        try {
            // Verificar se o diretório existe e tem permissão de escrita
            $storageDisk = Storage::disk('public');
            $storagePath = 'documents/' . $folder;
            
            // Criar diretório se não existir
            if (!$storageDisk->exists($storagePath)) {
                $storageDisk->makeDirectory($storagePath, 0755, true);
            }
            
            // Gerar nome único para o arquivo
            $fileName = uniqid() . '_' . time() . '.pdf';
            $fullPath = $storagePath . '/' . $fileName;
            
            // Salvar arquivo
            $storageDisk->putFileAs($storagePath, $file, $fileName);
            
            // Verificar se arquivo foi salvo
            if (!$storageDisk->exists($fullPath)) {
                throw new \Exception('Falha ao salvar arquivo PDF no servidor. Verifique permissões.');
            }
            
            // Gerar URL pública
            $url = $storageDisk->url($fullPath);
            
            // Garantir URL absoluta
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $baseUrl = config('app.url');
                $url = rtrim($baseUrl, '/') . '/' . ltrim($url, '/');
            }
            
            Log::info('Upload PDF local bem-sucedido', [
                'path' => $fullPath,
                'url' => $url,
                'file_size' => $storageDisk->size($fullPath)
            ]);
            
            return [
                'success' => true,
                'url' => $url,
                'public_id' => $fullPath,
                'format' => 'pdf',
                'resource_type' => 'raw',
            ];

        } catch (\Exception $e) {
            Log::error('Erro no upload PDF local: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Upload genérico de documento (imagem ou PDF)
     */
    public function uploadDocument(UploadedFile $file, string $folder = 'documents'): array
    {
        $mimeType = $file->getMimeType();
        
        // Se for PDF, usa upload de PDF
        if ($mimeType === 'application/pdf') {
            return $this->uploadPdf($file, $folder . '/pdfs');
        }
        
        // Caso contrário, usa upload de imagem
        return $this->uploadImage($file, $folder . '/images');
    }

    /**
     * Deleta um arquivo do Cloudinary
     */
    public function deleteFile(string $publicId, string $resourceType = 'image'): bool
    {
        try {
            Storage::disk('cloudinary')->delete($publicId);
            
            return true;

        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Upload de documento de verificação de usuário
     */
    public function uploadVerificationDocument(UploadedFile $file, int $userId, string $type): array
    {
        $folder = "users/{$userId}/verification/{$type}";
        return $this->uploadDocument($file, $folder);
    }

    /**
     * Upload de defesa de disputa (PDF)
     */
    public function uploadDisputeDefense(UploadedFile $file, string $disputeId): array
    {
        $folder = "disputes/{$disputeId}/defense";
        return $this->uploadPdf($file, $folder);
    }
}
