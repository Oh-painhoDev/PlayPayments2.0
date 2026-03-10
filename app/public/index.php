<?php

// CRITICAL FIX: Force remove DATABASE_URL BEFORE Laravel boots
// This prevents "Invalid URI" error from DATABASE_URL with special characters
putenv('DATABASE_URL');
putenv('DB_URL');
$_ENV['DATABASE_URL'] = null;
$_SERVER['DATABASE_URL'] = null;
unset($_ENV['DATABASE_URL'], $_SERVER['DATABASE_URL'], $_ENV['DB_URL'], $_SERVER['DB_URL']);

// ============================================
// INTERCEPTAR REQUISIÇÕES DE FOTOS DE USUÁRIOS
// Servir diretamente antes do Laravel processar
// ============================================
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// Interceptar requisições para /users/photos/
if (preg_match('#^/users/photos/(.+)$#', $requestUri, $matches)) {
    $filename = basename($matches[1]);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    if (!empty($filename) && $filename !== 'index.php' && $filename !== 'users' && $filename !== 'photos') {
        // Caminhos possíveis (verificar em ordem de prioridade)
        $baseDir = __DIR__;
        $possiblePaths = [
            $baseDir . '/../storage/app/public/users/photos/' . $filename,
            $baseDir . '/storage/users/photos/' . $filename,
        ];
        
        foreach ($possiblePaths as $path) {
            $realPath = realpath($path);
            if ($realPath && file_exists($realPath) && is_file($realPath)) {
                // Verificar se é legível
                if (is_readable($realPath)) {
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $mimeTypes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        'svg' => 'image/svg+xml'
                    ];
                    $contentType = $mimeTypes[$extension] ?? 'image/' . $extension;
                    
                    header('Content-Type: ' . $contentType);
                    header('Cache-Control: public, max-age=31536000');
                    header('Content-Length: ' . filesize($realPath));
                    
                    // Tentar servir o arquivo
                    try {
                        readfile($realPath);
                        exit;
                    } catch (\Exception $e) {
                        // Se falhar, tentar usar file_get_contents
                        $content = @file_get_contents($realPath);
                        if ($content !== false) {
                            header('Content-Length: ' . strlen($content));
                            echo $content;
                            exit;
                        }
                    }
                } else {
                    // Tentar ajustar permissões no Windows
                    if (PHP_OS_FAMILY === 'Windows') {
                        @chmod($realPath, 0644);
                        if (is_readable($realPath)) {
                            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $mimeTypes = [
                                'jpg' => 'image/jpeg',
                                'jpeg' => 'image/jpeg',
                                'png' => 'image/png',
                                'gif' => 'image/gif',
                                'webp' => 'image/webp',
                                'svg' => 'image/svg+xml'
                            ];
                            $contentType = $mimeTypes[$extension] ?? 'image/' . $extension;
                            
                            header('Content-Type: ' . $contentType);
                            header('Cache-Control: public, max-age=31536000');
                            header('Content-Length: ' . filesize($realPath));
                            
                            $content = @file_get_contents($realPath);
                            if ($content !== false) {
                                header('Content-Length: ' . strlen($content));
                                echo $content;
                                exit;
                            }
                        }
                    }
                }
            }
        }
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Servir arquivos estáticos diretamente antes do Laravel processar
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '';

// Servir CSS, JS e imagens diretamente
if (preg_match('#^/(css|js|images)/(.+)$#', $path, $matches)) {
    $type = $matches[1];
    $file = $matches[2];
    $filePath = __DIR__ . '/' . $type . '/' . $file;
    
    // Segurança: prevenir directory traversal
    $filePath = realpath($filePath);
    $basePath = realpath(__DIR__ . '/' . $type);
    
    if ($filePath && strpos($filePath, $basePath) === 0 && file_exists($filePath) && is_file($filePath)) {
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'images' => mime_content_type($filePath) ?: 'image/png'
        ];
        
        $mime = $mimeTypes[$type] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

// Servir favicon diretamente
if ($path === '/favicon.ico' || $path === '/favicon.svg') {
    $filePath = __DIR__ . $path;
    if (file_exists($filePath)) {
        $mime = $path === '/favicon.svg' ? 'image/svg+xml' : 'image/x-icon';
        header('Content-Type: ' . $mime);
        readfile($filePath);
        exit;
    }
}

// Set PHP upload and execution limits for large file uploads
// Using both putenv and ini_set for maximum compatibility
putenv('upload_max_filesize=500M');
putenv('post_max_size=500M');
putenv('memory_limit=512M');

@ini_set('upload_max_filesize', '500M');
@ini_set('post_max_size', '500M');
@ini_set('memory_limit', '512M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');
@ini_set('max_file_uploads', '50');

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
