<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StorageHelper
{
    /**
     * Garantir que o diretório de storage existe e tem permissões corretas
     */
    public static function ensureStorageDirectories(): void
    {
        try {
            $directories = [
                'documents',
                'documents/images',
                'documents/pdfs',
                'documents/users',
            ];
            
            $storageDisk = Storage::disk('public');
            $basePath = storage_path('app/public');
            
            // Criar diretório base se não existir
            if (!file_exists($basePath)) {
                @mkdir($basePath, 0755, true);
            }
            
            foreach ($directories as $dir) {
                $fullPath = $basePath . '/' . $dir;
                
                if (!file_exists($fullPath)) {
                    @mkdir($fullPath, 0755, true);
                }
                
                // Verificar e corrigir permissões
                if (file_exists($fullPath) && !is_writable($fullPath)) {
                    @chmod($fullPath, 0755);
                }
            }
            
            // Criar link simbólico se não existir (para Laravel storage:link)
            // Nota: Em alguns servidores Windows/hospedagem compartilhada, symlink pode não estar disponível
            $publicStoragePath = public_path('storage');
            if (!file_exists($publicStoragePath) && !is_link($publicStoragePath)) {
                try {
                    if (file_exists(storage_path('app/public'))) {
                        // Usar função global symlink() com verificação de disponibilidade
                        if (function_exists('symlink')) {
                            @\symlink(storage_path('app/public'), $publicStoragePath);
                        } else {
                            // Se symlink não estiver disponível, copiar arquivos ou criar diretório
                            // Em hospedagem compartilhada, geralmente o storage já está linkado manualmente
                            Log::info('Função symlink() não disponível. Certifique-se de que o link do storage está configurado manualmente.');
                        }
                    }
                } catch (\Exception $e) {
                    // Ignorar erro de link simbólico (pode não ter permissão)
                    Log::warning('Não foi possível criar link simbólico do storage: ' . $e->getMessage());
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Erro ao criar diretórios de storage: ' . $e->getMessage());
        }
    }
    
    /**
     * Verificar se o storage está funcionando
     */
    public static function checkStorageHealth(): array
    {
        $health = [
            'public_directory_exists' => file_exists(storage_path('app/public')),
            'public_directory_writable' => is_writable(storage_path('app/public')),
            'storage_link_exists' => file_exists(public_path('storage')) || is_link(public_path('storage')),
        ];
        
        // Testar escrita
        try {
            $testFile = 'storage_test_' . time() . '.txt';
            $testContent = 'test';
            Storage::disk('public')->put($testFile, $testContent);
            $health['write_test'] = Storage::disk('public')->exists($testFile);
            Storage::disk('public')->delete($testFile);
        } catch (\Exception $e) {
            $health['write_test'] = false;
            $health['write_error'] = $e->getMessage();
        }
        
        return $health;
    }
}

