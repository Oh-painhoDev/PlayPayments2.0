<?php
/**
 * Servir fotos de usuários diretamente
 * Este arquivo intercepta TODAS as requisições para /users/photos/
 */

// Pegar o filename do parâmetro ou da URI
$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    // Tentar pegar da URI
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $parsedUri = parse_url($requestUri, PHP_URL_PATH);
    
    // Extrair o filename da URI
    if (preg_match('#/users/photos/(.+)$#', $parsedUri, $matches)) {
        $filename = $matches[1];
    } else {
        $filename = basename($parsedUri);
    }
}

// Limpar filename
$filename = basename($filename);
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

if (empty($filename) || $filename === 'index.php' || $filename === 'users' || $filename === 'photos') {
    http_response_code(404);
    die('Foto não encontrada');
}

// Caminhos possíveis (verificar em ordem de prioridade)
$possiblePaths = [
    __DIR__ . '/../../../storage/app/public/users/photos/' . $filename,
    __DIR__ . '/../../../../storage/app/public/users/photos/' . $filename,
    __DIR__ . '/../../storage/users/photos/' . $filename,
];

$fullPath = null;

foreach ($possiblePaths as $path) {
    $realPath = realpath($path);
    if ($realPath && file_exists($realPath) && is_file($realPath) && is_readable($realPath)) {
        $fullPath = $realPath;
        break;
    }
}

if (!$fullPath) {
    http_response_code(404);
    die('Foto não encontrada: ' . $filename);
}

// Determinar Content-Type
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

// Servir arquivo
header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=31536000');
header('Content-Length: ' . filesize($fullPath));

readfile($fullPath);
exit;
