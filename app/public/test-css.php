<?php
// Teste direto de CSS
$cssFile = __DIR__ . '/css/dashboard.css';
if (file_exists($cssFile)) {
    header('Content-Type: text/css');
    readfile($cssFile);
} else {
    http_response_code(404);
    echo "File not found: " . $cssFile;
}

