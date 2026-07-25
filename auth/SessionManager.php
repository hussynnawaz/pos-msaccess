<?php
class SessionManager
{
    private int $lifetime;

    public function __construct()
    {
        $this->lifetime = (int)(getenv('SESSION_LIFETIME') ?: '120');
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '0');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.gc_maxlifetime', (string)$this->lifetime);

        session_set_cookie_params([
            'lifetime' => 31536000,
            'path'     => '/',
            'httponly'  => true,
            'secure'   => false,
            'samesite' => 'Lax',
        ]);

        session_start();
        $this->regenerateIfNeeded();
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    private function regenerateIfNeeded(): void
    {
        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = time();
            return;
        }

        if (time() - $_SESSION['_last_activity'] > 300) {
            $this->regenerate();
            $_SESSION['_last_activity'] = time();
        }
    }

    public function getId(): string
    {
        return session_id();
    }

    public function isLoggedIn(): bool
    {
        return $this->get('logged_in') === true;
    }
}
