<?php
header('Content-Type: text/plain');
$ip = file_get_contents('https://api.ipify.org');
if (!$ip) {
    $ip = $_SERVER['SERVER_ADDR'] ?? 'unknown';
}
echo "Deployment VM IP: " . $ip . "\n";
echo "Add this IP to Google Cloud SQL authorized networks!\n";
?>