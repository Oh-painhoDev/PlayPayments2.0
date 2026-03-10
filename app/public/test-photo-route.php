<?php
// Teste para verificar se o servidor está processando corretamente
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'Servidor está processando PHP corretamente',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'N/A',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'php_self' => $_SERVER['PHP_SELF'] ?? 'N/A'
]);

