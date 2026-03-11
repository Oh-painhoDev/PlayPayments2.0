<?php
/**
 * Script de teste para verificar se as fotos estão sendo servidas corretamente
 */

$filename = '2_1763850165_692237b537e90.png';
$baseDir = __DIR__;

$possiblePaths = [
    $baseDir . '/../storage/app/public/users/photos/' . $filename,
    $baseDir . '/storage/users/photos/' . $filename,
];

echo "<h1>Teste de Caminhos de Fotos</h1>";
echo "<p>Filename: $filename</p>";
echo "<p>Base Dir: $baseDir</p>";

foreach ($possiblePaths as $path) {
    echo "<hr>";
    echo "<p><strong>Caminho testado:</strong> $path</p>";
    $realPath = realpath($path);
    if ($realPath && file_exists($realPath) && is_file($realPath) && is_readable($realPath)) {
        echo "<p style='color: green;'>✓ Arquivo encontrado!</p>";
        echo "<p>Real Path: $realPath</p>";
        echo "<p>Tamanho: " . filesize($realPath) . " bytes</p>";
        echo "<p><a href='/users/photos/$filename'>Testar URL</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Arquivo NÃO encontrado</p>";
        if ($realPath) {
            echo "<p>Real Path: $realPath (mas não existe ou não é legível)</p>";
        }
    }
}

echo "<hr>";
echo "<p><strong>REQUEST_URI atual:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<p><strong>SCRIPT_NAME:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "</p>";

