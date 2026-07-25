<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$posSale = new PosSale();

$action = $_GET['action'] ?? '';

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if ($action === 'items') {
            $saleId = isset($_GET['sale_id']) ? (int)$_GET['sale_id'] : 0;
            if (!$saleId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Sale ID required']);
                exit;
            }
            $items = $posSale->reportItems($saleId);
            echo json_encode(['success' => true, 'data' => $items]);
        } elseif ($action === 'report') {
            $filters = [
                'search'    => $_GET['search'] ?? '',
                'date_from' => $_GET['date_from'] ?? '',
                'date_to'   => $_GET['date_to'] ?? '',
                'cashier'   => $_GET['cashier'] ?? '',
            ];
            $reports = $posSale->reportAll($filters);

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
        } else {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if ($id) {
                $data = $posSale->find($id);
                echo json_encode(['success' => (bool)$data, 'data' => $data]);
            } else {
                $filters = [
                    'search'    => $_GET['search'] ?? '',
                    'status'    => $_GET['status'] ?? '',
                    'date_from' => $_GET['date_from'] ?? '',
                    'date_to'   => $_GET['date_to'] ?? '',
                ];
                $sales = $posSale->all($filters);
                echo json_encode(['success' => true, 'data' => $sales]);
            }
        }
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) $input = $_POST;

        if (empty($input['items']) || !is_array($input['items'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No items in sale']);
            exit;
        }

        $input['cashier_name'] = $input['cashier_name'] ?? ($user['username'] ?? 'Admin');

        $result = $posSale->create($input);
        http_response_code($result['success'] ? 201 : 400);
        echo json_encode($result);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
