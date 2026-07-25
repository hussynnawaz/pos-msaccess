<?php
require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: application/json');

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$posSale = new PosSale();
$product = new Product();
$order = new Order();

echo json_encode([
    'success' => true,
    'today_sales'    => $posSale->todaySales(),
    'today_orders'   => $posSale->todayCount(),
    'total_products' => $product->count(),
    'low_stock'      => $product->lowStockCount(),
]);
