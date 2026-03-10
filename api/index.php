<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Verifica se o autoload existe
if (file_exists($autoload = __DIR__.'/vendor/autoload.php')) {
    require $autoload;
} else {
    die('Vendor autoload não encontrado!');
}

// Inicializa a aplicação
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);