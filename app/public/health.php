<?php
// Ultra-fast health check - bypasses Laravel framework
// Responds in <5ms for deployment health checks
header('Content-Type: text/plain');
http_response_code(200);
echo 'OK';
exit;
