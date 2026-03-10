<?php
// Mostra TODOS os erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/vendor/autoload.php';

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "✅ Bootstrap OK<br>";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "✅ Kernel OK<br>";
    
    $request = Illuminate\Http\Request::create('/dashboard', 'GET');
    $response = $kernel->handle($request);
    
    echo "✅ Resposta: " . $response->getStatusCode() . "<br>";
    echo $response->getContent();
    
} catch (Throwable $e) {
    echo "❌ ERRO PEGO:<br>";
    echo "<strong>" . get_class($e) . "</strong><br>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br>";
    
    echo "<br><strong>Stack Trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}