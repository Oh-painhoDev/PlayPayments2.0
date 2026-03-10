<?php
header('Content-Type: text/plain');

echo "=== DEPLOYMENT ENV TEST ===\n\n";

// Load deployment config
if (file_exists(__DIR__ . '/../bootstrap/deployment.php')) {
    require_once __DIR__ . '/../bootstrap/deployment.php';
    echo "✅ deployment.php loaded\n\n";
}

echo "Environment Variables:\n";
echo "---------------------\n";

$vars = [
    'DB_CONNECTION',
    'DB_HOST', 
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'SESSION_DRIVER',
    'CACHE_DRIVER',
    'CLOUD_SQL_HOST',
    'CLOUD_SQL_PASSWORD',
];

foreach ($vars as $var) {
    $value = getenv($var);
    if ($var === 'DB_PASSWORD' || $var === 'CLOUD_SQL_PASSWORD') {
        $value = $value ? '***SET***' : 'NOT SET';
    }
    echo "$var = " . ($value ?: 'NOT SET') . "\n";
}

echo "\n=== TEST DB CONNECTION ===\n";

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$db = getenv('DB_DATABASE');
$user = getenv('DB_USERNAME');
$pass = getenv('DB_PASSWORD');

echo "Connecting to: $host:$port/$db as $user\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
    echo "✅ CONNECTION SUCCESS!\n";
} catch (Exception $e) {
    echo "❌ CONNECTION FAILED: " . $e->getMessage() . "\n";
}
