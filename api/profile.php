<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

if (!$session->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$auth = new AuthController();
$user = $auth->requireAuth();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

$action = $input['action'] ?? '';
$db = Database::getInstance();

switch ($action) {
    case 'update_name':
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Name is required']);
            exit;
        }

        $nameQ = $db->quote($name);
        $emailQ = $db->quote($email);
        $userId = (int)$user['user_id'];

        $db->execute("UPDATE users SET [name] = {$nameQ}, [email] = {$emailQ} WHERE id = {$userId}");

        $session->set('user_name', $name);
        $session->set('user_email', $email);

        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        break;

    case 'update_password':
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['success' => false, 'message' => 'All password fields are required']);
            exit;
        }

        if (strlen($newPassword) < 6) {
            echo json_encode(['success' => false, 'message' => 'New password must be at least 6 characters']);
            exit;
        }

        $userId = (int)$user['user_id'];
        $row = $db->queryOne("SELECT [password] FROM users WHERE id = {$userId}");

        if (!$row || !password_verify($currentPassword, $row['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            exit;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $hashedQ = $db->quote($hashed);

        $db->execute("UPDATE users SET [password] = {$hashedQ} WHERE id = {$userId}");

        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
