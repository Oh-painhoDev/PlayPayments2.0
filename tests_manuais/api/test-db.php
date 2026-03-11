<?php
header('Content-Type: text/plain');
echo "=== BRPIX DATABASE TEST ===\n\n";

$ip = @file_get_contents('https://api.ipify.org');
echo "Current Server IP: " . ($ip ?: 'unknown') . "\n";
echo "This IP MUST be in Google Cloud SQL authorized networks!\n\n";

$host = getenv('CLOUD_SQL_HOST') ?: '34.187.233.200';
$port = getenv('CLOUD_SQL_PORT') ?: '5432';
$db = getenv('CLOUD_SQL_DATABASE') ?: 'brpix';
$user = getenv('CLOUD_SQL_USER') ?: 'brpix_user';

echo "Database Config:\n";
echo "  Host: $host\n";
echo "  Port: $port\n";
echo "  Database: $db\n";
echo "  User: $user\n\n";

echo "Testing connection...\n";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;connect_timeout=10";
    $pass = getenv('CLOUD_SQL_PASSWORD') ?: '';
    
    $start = microtime(true);
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $time = round((microtime(true) - $start) * 1000, 2);
    
    echo "✅ SUCCESS! Connected in {$time}ms\n\n";
    
    $version = $pdo->query('SELECT version()')->fetchColumn();
    echo "Database Version: $version\n";
    
    $result = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    echo "Total Users: $result\n";
    
} catch (Exception $e) {
    echo "❌ FAILED!\n\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "SOLUTION:\n";
    echo "1. Go to Google Cloud Console\n";
    echo "2. SQL → brpix-production → Connections → Networking\n";
    echo "3. Add this IP: $ip\n";
    echo "4. Click SAVE (important!)\n";
    echo "5. Wait 30 seconds\n";
    echo "6. Republish in Replit\n";
}
