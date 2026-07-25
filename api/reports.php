<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$order = new Order();

$action = $_GET['action'] ?? 'list';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($action === 'items') {
            $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            if (!$orderId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Order ID required']);
                exit;
            }
            $items = $order->reportItems($orderId);
            echo json_encode(['success' => true, 'data' => $items]);
        } else {
            $filters = [
                'search'    => $_GET['search'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to'   => $_GET['date_to'] ?? '',
            ];
            $reports = $order->reportAll($filters);

            $totalSale = 0;
            $totalProfit = 0;
            foreach ($reports as $row) {
                $totalSale += (float)$row['sale_total'];
                $totalProfit += (float)$row['profit'];
            }

            echo json_encode([
                'success' => true,
                'data'    => $reports,
                'summary' => ['total_sale' => $totalSale, 'total_profit' => $totalProfit, 'total_orders' => count($reports)]
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
