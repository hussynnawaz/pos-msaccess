<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$session = new SessionManager();
$session->start();

SecurityHeaders::rateLimit(10, 60);

$csrf = new CsrfProtection();
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$csrfToken = $input[$csrf->getFieldName()] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;

if (!$csrf->validateToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing CSRF token. Please refresh the page.']);
    exit;
}

$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$auth = new AuthController();
$result = $auth->login($username, $password);

http_response_code($result['success'] ? 200 : 401);
echo json_encode($result);
