<?php
require_once __DIR__ . '/../bootstrap.php';

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$productModel = new Product();
$products = $productModel->all();

$totalProducts = 0;
$totalVariants = 0;
$totalStockValue = 0;
$lowStockCount = 0;
$outOfStockCount = 0;

foreach ($products as $p) {
    $totalProducts++;
    $stock = (int)$p['stock'];
    $totalStockValue += $stock * (float)$p['purchase_price'];
    if ($stock <= 0) $outOfStockCount++;
    elseif ($stock <= 5) $lowStockCount++;

    if (!empty($p['variants'])) {
        foreach ($p['variants'] as $v) {
            $totalVariants++;
            $vstock = (int)$v['stock'];
            $totalStockValue += $vstock * (float)$v['purchase_price'];
            if ($vstock <= 0) $outOfStockCount++;
            elseif ($vstock <= 5) $lowStockCount++;
        }
    }
}
?>

<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <p class="text-sm font-medium text-gray-500">Total Products</p>
    <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $totalProducts; ?></p>
    <p class="text-xs text-gray-400 mt-1"><?php echo $totalVariants; ?> variants</p>
</div>
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <p class="text-sm font-medium text-gray-500">Stock Value</p>
    <p class="text-2xl font-bold text-gray-900 mt-1">Rs. <?php echo number_format($totalStockValue, 0); ?></p>
    <p class="text-xs text-gray-400 mt-1">At purchase price</p>
</div>
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <p class="text-sm font-medium text-gray-500">Low Stock</p>
    <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo $lowStockCount; ?></p>
    <p class="text-xs text-gray-400 mt-1">Stock &le; 5</p>
</div>
<div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
    <p class="text-sm font-medium text-gray-500">Out of Stock</p>
    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo $outOfStockCount; ?></p>
    <p class="text-xs text-gray-400 mt-1">Stock = 0</p>
</div>
