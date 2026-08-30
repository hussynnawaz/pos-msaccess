<?php
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

spl_autoload_register(function ($class) {
    $dirs = [
        __DIR__ . '/config',
        __DIR__ . '/auth',
        __DIR__ . '/security',
        __DIR__ . '/controllers',
        __DIR__ . '/models',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

SecurityHeaders::send();

// Reset DB singleton so each request gets a fresh COM connection
Database::resetInstance();

// Ensure DB connection is closed at request end to release .laccdb lock file
register_shutdown_function(function () {
    Database::resetInstance();
});
