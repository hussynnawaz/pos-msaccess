<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$product = new Product();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $filters = [
                'search'   => $_GET['search'] ?? '',
                'category' => $_GET['category'] ?? '',
                'active'   => isset($_GET['active']) ? (int)$_GET['active'] : null,
            ];
            $products = $product->all($filters);
            echo json_encode(['success' => true, 'data' => $products]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input) $input = $_POST;

            if (empty($input['name'])) {
                echo json_encode(['success' => false, 'message' => 'Name is required']);
                exit;
            }
            if (!isset($input['selling_price']) || $input['selling_price'] === '') {
                echo json_encode(['success' => false, 'message' => 'Selling price is required']);
                exit;
            }

            if (!empty($input['id'])) {
                $id = (int)$input['id'];
                $result = $product->update($id, $input);
            } else {
                $result = $product->create($input);
            }
            echo json_encode($result);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
