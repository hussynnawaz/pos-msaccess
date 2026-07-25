<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$order = new Order();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $data = $order->find($id);
            echo json_encode(['success' => (bool)$data, 'data' => $data]);
        } else {
            $filters = [
                'search'     => $_GET['search'] ?? '',
                'status'     => $_GET['status'] ?? '',
                'order_type' => $_GET['order_type'] ?? '',
                'date_from'  => $_GET['date_from'] ?? '',
                'date_to'    => $_GET['date_to'] ?? '',
            ];
            $orders = $order->all($filters);
            echo json_encode(['success' => true, 'data' => $orders]);
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        if (empty($input['items'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No items in order']);
            exit;
        }

        $input['user_id'] = $user['id'] ?? $user['user_id'] ?? 1;
        $result = $order->create($input);
        http_response_code($result['success'] ? 201 : 400);
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
