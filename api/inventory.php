<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) $input = $_POST;

if (empty($input['id']) || empty($input['action']) || empty($input['type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$id = (int)$input['id'];
$action = $input['action'];
$type = $input['type'];
$quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;

$db = Database::getInstance();
$table = $type === 'variant' ? 'product_variants' : 'products';

$item = $db->queryOne("SELECT id, stock, name FROM [{$table}] WHERE id = {$id}");

if (!$item) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => ucfirst($type) . ' not found']);
    exit;
}

$currentStock = (int)$item['stock'];

switch ($action) {
    case 'add':
        $newStock = $currentStock + $quantity;
        break;
    case 'subtract':
        $newStock = max(0, $currentStock - $quantity);
        break;
    case 'set':
        $newStock = max(0, $quantity);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
}

$db->execute("UPDATE [{$table}] SET stock = {$newStock} WHERE id = {$id}");

$actionText = $action === 'add' ? 'added' : ($action === 'subtract' ? 'removed' : 'set to');
echo json_encode([
    'success' => true,
    'message' => "Stock {$actionText} for {$item['name']}. New stock: {$newStock}",
    'new_stock' => $newStock
]);
