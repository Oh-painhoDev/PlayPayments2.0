<?php
// Teste direto de arquivos estáticos
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$parsedUrl = parse_url($requestUri);
$path = $parsedUrl['path'] ?? '';

echo "Request URI: " . $requestUri . "\n";
echo "Path: " . $path . "\n";

if (preg_match('#^/(css|js|images)/(.+)$#', $path, $matches)) {
    $type = $matches[1];
    $file = $matches[2];
    $filePath = __DIR__ . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $file;
    
    echo "Type: " . $type . "\n";
    echo "File: " . $file . "\n";
    echo "File Path: " . $filePath . "\n";
    
    $realPath = realpath($filePath);
    $basePath = realpath(__DIR__ . DIRECTORY_SEPARATOR . $type);
    
    echo "Real Path: " . ($realPath ?: 'NULL') . "\n";
    echo "Base Path: " . ($basePath ?: 'NULL') . "\n";
    echo "Exists: " . (file_exists($realPath) ? 'YES' : 'NO') . "\n";
    echo "Is File: " . (is_file($realPath) ? 'YES' : 'NO') . "\n";
    
    if ($realPath && $basePath && strpos($realPath, $basePath) === 0 && file_exists($realPath) && is_file($realPath)) {
        $mime = $type === 'css' ? 'text/css' : ($type === 'js' ? 'application/javascript' : 'image/png');
        header('Content-Type: ' . $mime);
        readfile($realPath);
        exit;
    }
}

echo "File not found or invalid path\n";

