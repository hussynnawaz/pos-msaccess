<?php
class CsrfProtection
{
    private string $tokenName;
    private string $secret;

    public function __construct()
    {
        $this->tokenName = getenv('CSRF_TOKEN_NAME') ?: 'csrf_token';
        $this->secret = getenv('JWT_SECRET') ?: 'change-this-secret';
    }

    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[$this->tokenName] = [
            'token'   => hash_hmac('sha256', $token, $this->secret),
            'expires' => time() + 1800,
        ];
        return $token;
    }

    public function validateToken(?string $token): bool
    {
        if (!$token || !isset($_SESSION[$this->tokenName])) {
            return false;
        }

        $stored = $_SESSION[$this->tokenName];

        if (time() > $stored['expires']) {
            unset($_SESSION[$this->tokenName]);
            return false;
        }

        return hash_equals($stored['token'], hash_hmac('sha256', $token, $this->secret));
    }

    public function getField(): string
    {
        $token = $this->generateToken();
        return "<input type=\"hidden\" name=\"{$this->tokenName}\" value=\"{$token}\">";
    }

    public function getFieldName(): string
    {
        return $this->tokenName;
    }
}
