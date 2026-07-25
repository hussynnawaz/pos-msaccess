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

// Start session for web pages
$session = new SessionManager();
$session->start();

$path = rtrim($path, '/');

$routes = [
    ''                => 'views/home.php',
    '/login'          => 'views/admin/login.php',
    '/admin'          => 'views/admin/layout.php',
    '/admin/login'    => 'views/admin/login.php',
    '/admin/products' => 'views/admin/layout.php',
    '/admin/suppliers'=> 'views/admin/layout.php',
    '/admin/orders'   => 'views/admin/layout.php',
    '/admin/pos'      => 'views/admin/layout.php',
    '/admin/inventory'=> 'views/admin/layout.php',
    '/admin/reports'  => 'views/admin/layout.php',
    '/admin/backup'   => 'views/admin/layout.php',
    '/admin/profile'  => 'views/admin/layout.php',
];

if (isset($routes[$path])) {
    $view = __DIR__ . '/' . $routes[$path];
    if (file_exists($view)) {
        require $view;
        exit;
    }
}

http_response_code(404);
require __DIR__ . '/views/404.php';
