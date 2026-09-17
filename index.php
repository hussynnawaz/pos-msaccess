<?php
require_once __DIR__ . '/bootstrap.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Serve API files directly
if (strpos($path, '/api/') === 0) {
    $file = __DIR__ . $path;
    if (file_exists($file) && is_file($file)) {
        require $file;
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'API endpoint not found']);
    exit;
}

// Serve static files
if ($path !== '/') {
    $file = __DIR__ . $path;
    if (file_exists($file) && is_file($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
        ];
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($file);
        exit;
    }
}

// All web routes go directly to POS
$path = rtrim($path, '/');

if ($path === '' || $path === '/admin' || strpos($path, '/admin/') === 0) {
    require __DIR__ . '/views/admin/layout.php';
    exit;
}

http_response_code(404);
require __DIR__ . '/views/404.php';
