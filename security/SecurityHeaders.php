<?php
class SecurityHeaders
{
    public static function send(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com; font-src \'self\' https://fonts.gstatic.com; img-src \'self\' data:;');
    }

    public static function setCors(string $origin = '*'): void
    {
        header("Access-Control-Allow-Origin: {$origin}");
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
    }

    public static function jsonHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
    }

    public static function rateLimit(int $maxRequests = 60, int $window = 60): void
    {
        $key = 'rate_limit_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'start' => time()];
        }

        $data = &$_SESSION[$key];

        if (time() - $data['start'] > $window) {
            $data = ['count' => 0, 'start' => time()];
        }

        $data['count']++;

        header("X-RateLimit-Limit: {$maxRequests}");
        header("X-RateLimit-Remaining: " . max(0, $maxRequests - $data['count']));
        header("X-RateLimit-Reset: " . ($data['start'] + $window));

        if ($data['count'] > $maxRequests) {
            http_response_code(429);
            echo json_encode(['success' => false, 'message' => 'Too many requests. Please try again later.']);
            exit;
        }
    }
}
