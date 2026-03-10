<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!<br>";
} else {
    echo "OPcache is not enabled.<br>";
}

// Clear Laravel cache
if (file_exists(__DIR__ . '/../storage/framework/views')) {
    $files = glob(__DIR__ . '/../storage/framework/views/*');
    foreach($files as $file) {
        if(is_file($file)) {
            unlink($file);
        }
    }
    echo "Laravel view cache cleared!<br>";
}

echo "Done! Now try accessing the page again.";

