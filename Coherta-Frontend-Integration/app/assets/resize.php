<?php
declare(strict_types=1);

define('ASSET_RESIZE_BASE_DIR', __DIR__);

$dir = __DIR__;
while ($dir !== dirname($dir)) {
    $candidate = $dir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'image-resize-handler.php';
    if (is_file($candidate)) {
        require $candidate;
        exit;
    }
    $dir = dirname($dir);
}

http_response_code(500);
exit('Image resize handler not found');
