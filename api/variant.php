<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$variant = new Variant();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid variant ID']);
        exit;
    }

    $result = $variant->delete($id);
    echo json_encode($result);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
