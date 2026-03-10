<?php

// CRITICAL FIX: Force remove DATABASE_URL BEFORE Laravel boots
// This prevents "Invalid URI" error from DATABASE_URL with special characters
putenv('DATABASE_URL');
putenv('DB_URL');
$_ENV['DATABASE_URL'] = null;
$_SERVER['DATABASE_URL'] = null;
unset($_ENV['DATABASE_URL'], $_SERVER['DATABASE_URL'], $_ENV['DB_URL'], $_SERVER['DB_URL']);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Set PHP upload and execution limits for large file uploads
// Using both putenv and ini_set for maximum compatibility
@ini_set('upload_max_filesize', '100M');
@ini_set('post_max_size', '100M');
@ini_set('memory_limit', '256M');
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

