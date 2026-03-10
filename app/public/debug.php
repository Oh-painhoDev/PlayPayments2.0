<?php
// Disable all error display for clean output
error_reporting(0);
ini_set('display_errors', '0');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>BRPIX Debug</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .ok { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        pre { background: #0a0a0a; padding: 10px; border: 1px solid #333; }
        h2 { color: #00aaff; }
    </style>
</head>
<body>
    <h1>🔍 BRPIX DEPLOYMENT DEBUG</h1>
    
    <h2>1. PHP Status</h2>
    <pre class="ok">✅ PHP Version: <?php echo PHP_VERSION; ?></pre>
    
    <h2>2. Server IP</h2>
    <pre class="ok">IP: <?php 
        $ip = @file_get_contents('https://api.ipify.org');
        echo $ip ? $ip : 'Unable to detect'; 
    ?></pre>
    
    <h2>3. Environment Variables</h2>
    <pre><?php
        $env_vars = [
            'APP_ENV',
            'APP_DEBUG',
            'DB_CONNECTION',
            'SESSION_DRIVER',
            'CACHE_DRIVER',
            'CLOUD_SQL_HOST',
            'CLOUD_SQL_PORT',
            'CLOUD_SQL_DATABASE',
            'CLOUD_SQL_USER',
        ];
        
        foreach ($env_vars as $var) {
            $value = getenv($var);
            $class = $value ? 'ok' : 'error';
            if ($var === 'CLOUD_SQL_USER' || $var === 'CLOUD_SQL_PASSWORD') {
                $value = $value ? '***SET***' : 'NOT SET';
            }
            echo "<span class='$class'>$var: " . ($value ?: 'NOT SET') . "</span>\n";
        }
    ?></pre>
    
    <h2>4. Database Connection Test</h2>
    <pre><?php
        $host = getenv('CLOUD_SQL_HOST') ?: '34.187.233.200';
        $port = getenv('CLOUD_SQL_PORT') ?: '5432';
        $db = getenv('CLOUD_SQL_DATABASE') ?: 'brpix';
        $user = getenv('CLOUD_SQL_USER') ?: 'brpix_user';
        $pass = getenv('CLOUD_SQL_PASSWORD') ?: '';
        
        echo "Connecting to: $host:$port/$db as $user\n";
        
        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$db;connect_timeout=10";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            echo "<span class='ok'>✅ DATABASE CONNECTED!</span>\n";
            
            $users = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            echo "<span class='ok'>Total users: $users</span>\n";
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ CONNECTION FAILED!</span>\n";
            echo "<span class='error'>Error: " . $e->getMessage() . "</span>\n";
        }
    ?></pre>
    
    <h2>5. File Permissions</h2>
    <pre><?php
        $paths = [
            'storage/logs' => is_writable(__DIR__ . '/../storage/logs'),
            'storage/framework/sessions' => is_writable(__DIR__ . '/../storage/framework/sessions'),
            'storage/framework/cache' => is_writable(__DIR__ . '/../storage/framework/cache'),
        ];
        
        foreach ($paths as $path => $writable) {
            $class = $writable ? 'ok' : 'error';
            $status = $writable ? '✅ Writable' : '❌ Not writable';
            echo "<span class='$class'>$path: $status</span>\n";
        }
    ?></pre>
    
    <h2>6. Laravel Status</h2>
    <pre><?php
        if (file_exists(__DIR__ . '/../bootstrap/app.php')) {
            echo "<span class='ok'>✅ Laravel bootstrap exists</span>\n";
            
            if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
                echo "<span class='ok'>✅ Vendor autoload exists</span>\n";
            } else {
                echo "<span class='error'>❌ Vendor autoload missing</span>\n";
            }
        } else {
            echo "<span class='error'>❌ Laravel bootstrap missing</span>\n";
        }
    ?></pre>
    
    <hr>
    <p><a href="/status.html">Go to Status Page</a> | <a href="/login">Go to Login</a></p>
</body>
</html>