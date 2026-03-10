<?php
/**
 * Router personalizado para o servidor de desenvolvimento do PHP
 * Este arquivo intercepta requisições para /users/photos/ e as processa
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Interceptar requisições para fotos de usuários
if (preg_match('#^/users/photos/(.+)$#', $uri, $matches)) {
    $filename = basename($matches[1]);
    $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    
    if (!empty($filename) && $filename !== 'index.php') {
        // Caminhos possíveis
        $possiblePaths = [
            __DIR__ . '/../storage/app/public/users/photos/' . $filename,
            __DIR__ . '/storage/users/photos/' . $filename,
        ];
        
        foreach ($possiblePaths as $path) {
            $realPath = realpath($path);
            if ($realPath && file_exists($realPath) && is_file($realPath) && is_readable($realPath)) {
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
                readfile($realPath);
                exit;
            }
        }
    }
    
    // Se não encontrou, passar para o Laravel
    $_SERVER['REQUEST_URI'] = $uri;
    require __DIR__ . '/index.php';
    return true;
}

// Para outras requisições, deixar o servidor padrão processar
return false;
