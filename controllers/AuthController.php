<?php
class AuthController
{
    private Database $db;
    private JwtHandler $jwt;
    private SessionManager $session;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->jwt = new JwtHandler();
        $this->session = new SessionManager();
    }

    public function login(string $username, string $password): array
    {
        $quoted = $this->db->quote($username);
        $user = $this->db->queryOne("SELECT TOP 1 id, username, name, email, password, role, is_active FROM users WHERE username = {$quoted}");

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is disabled'];
        }

        $this->db->execute("UPDATE users SET last_login = Now() WHERE id = " . (int)$user['id']);

        unset($user['password']);

        $token = $this->jwt->generateToken([
            'user_id'  => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ]);

        $this->session->start();
        $this->session->set('user_id', $user['id']);
        $this->session->set('username', $user['username']);
        $this->session->set('user_name', $user['name']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('logged_in', true);
        $this->session->set('jwt_token', $token);

        return [
            'success'  => true,
            'message'  => 'Login successful',
            'token'    => $token,
            'user'     => $user,
            'redirect' => '/admin',
        ];
    }

    public function logout(): array
    {
        $this->session->start();
        $this->session->destroy();
        return ['success' => true, 'message' => 'Logged out'];
    }

    public function verify(): ?array
    {
        $this->session->start();

        if (!$this->session->isLoggedIn()) {
            return null;
        }

        $token = $this->session->get('jwt_token');
        if (!$token) {
            return null;
        }

        $payload = $this->jwt->validateToken($token);
        if (!$payload) {
            $token = $this->jwt->generateToken([
                'user_id'  => $this->session->get('user_id'),
                'username' => $this->session->get('username'),
                'role'     => $this->session->get('user_role'),
            ]);
            $this->session->set('jwt_token', $token);
            $payload = $this->jwt->validateToken($token);
            if (!$payload) {
                return null;
            }
        }

        return [
            'user_id'  => $payload['user_id'],
            'username' => $payload['username'],
            'role'     => $payload['role'],
        ];
    }

    public function requireAuth(): array
    {
        $user = $this->verify();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        return $user;
    }

    public function requireAdmin(): array
    {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Forbidden']);
            exit;
        }
        return $user;
    }
}
