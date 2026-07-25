<?php
require_once __DIR__ . '/../bootstrap.php';

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$productModel = new Product();
$filters = [
    'search'   => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
];
$stockFilter = $_GET['stock'] ?? '';
$products = $productModel->all($filters);

function renderStockClass(int $stock): string {
    if ($stock <= 0) return 'text-red-600 font-bold';
    if ($stock <= 5) return 'text-amber-600 font-semibold';
    return 'text-gray-900';
}

function renderStatusBadge(int $stock): string {
    if ($stock <= 0) return '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Out of Stock</span>';
    if ($stock <= 5) return '<span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Low Stock</span>';
    return '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">In Stock</span>';
}

function renderShortBadge(int $stock): string {
    if ($stock <= 0) return '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Out</span>';
    if ($stock <= 5) return '<span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Low</span>';
    return '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">In</span>';
}

function matchesStockFilter(int $stock, string $filter): bool {
    if ($filter === '') return true;
    if ($filter === 'out') return $stock <= 0;
    if ($filter === 'low') return $stock > 0 && $stock <= 5;
    if ($filter === 'in') return $stock > 5;
    return true;
}

$filtered = [];
foreach ($products as $p) {
    $ps = (int)$p['stock'];
    if (!matchesStockFilter($ps, $stockFilter)) continue;

    if (!empty($p['variants'])) {
        $visibleVariants = array_filter($p['variants'], function($v) use ($stockFilter) {
            return matchesStockFilter((int)$v['stock'], $stockFilter);
        });
        $p['variants'] = array_values($visibleVariants);
    }

    $filtered[] = $p;
}
$products = $filtered;

if (empty($products)):
?>
    <tr>
        <td colspan="10" class="px-6 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            No products found
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($products as $p):
        $stock = (int)$p['stock'];
        $hasVariants = !empty($p['has_variants']) && !empty($p['variants']);
        $value = $stock * (float)$p['purchase_price'];
    ?>
        <tr class="hover:bg-gray-50 inventory-row" data-stock="<?php echo $stock; ?>" data-id="<?php echo $p['id']; ?>" data-type="product">
            <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                    <?php if ($hasVariants): ?>
                        <button onclick="toggleVariants(<?php echo $p['id']; ?>)" class="text-gray-400 hover:text-gray-600 shrink-0">
                            <svg id="arrow-<?php echo $p['id']; ?>" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    <?php else: ?>
                        <span class="w-4 shrink-0"></span>
                    <?php endif; ?>
                    <div>
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['name']); ?></p>
                        <?php if ($hasVariants): ?>
                            <p class="text-xs text-blue-500"><?php echo count($p['variants']); ?> variant<?php echo count($p['variants']) > 1 ? 's' : ''; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600 font-mono"><?php echo htmlspecialchars($p['sku'] ?? '-'); ?></td>
            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($p['category'] ?? '-'); ?></td>
            <td class="px-6 py-4 text-sm text-gray-600 text-right">Rs. <?php echo number_format($p['purchase_price'], 2); ?></td>
            <td class="px-6 py-4 text-sm text-gray-900 font-medium text-right">Rs. <?php echo number_format($p['selling_price'], 2); ?></td>
            <td class="px-6 py-4 text-sm text-right <?php echo renderStockClass($stock); ?>" id="stock-<?php echo $p['id']; ?>"><?php echo $stock; ?></td>
            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($p['unit']); ?></td>
            <td class="px-6 py-4" id="status-<?php echo $p['id']; ?>"><?php echo renderStatusBadge($stock); ?></td>
            <td class="px-6 py-4 text-sm text-gray-600 text-right" id="value-<?php echo $p['id']; ?>">Rs. <?php echo number_format($value, 2); ?></td>
            <td class="px-6 py-4 text-center">
                <button onclick="openAdjustModal(<?php echo $p['id']; ?>, '<?php echo htmlspecialchars(addslashes($p['name'])); ?>', 'product', <?php echo $stock; ?>)" class="text-blue-500 hover:text-blue-700 text-sm font-medium">Adjust</button>
            </td>
        </tr>
        <?php if ($hasVariants): ?>
            <?php foreach ($p['variants'] as $v):
                $vstock = (int)$v['stock'];
                $vvalue = $vstock * (float)$v['purchase_price'];
            ?>
                <tr class="variant-row-<?php echo $p['id']; ?> bg-gray-50/50 hidden inventory-row" data-stock="<?php echo $vstock; ?>" data-id="<?php echo $v['id']; ?>" data-type="variant">
                    <td class="px-6 py-3 pl-12">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($v['name']); ?></span>
                            <?php if (!empty($v['unit_weight'])): ?>
                                <span class="text-xs text-gray-400">(<?php echo htmlspecialchars($v['unit_weight']); ?>)</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500 font-mono"><?php echo htmlspecialchars($v['sku'] ?? '-'); ?></td>
                    <td class="px-6 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($p['category'] ?? '-'); ?></td>
                    <td class="px-6 py-3 text-sm text-gray-500 text-right">Rs. <?php echo number_format($v['purchase_price'], 2); ?></td>
                    <td class="px-6 py-3 text-sm text-gray-700 font-medium text-right">Rs. <?php echo number_format($v['selling_price'], 2); ?></td>
                    <td class="px-6 py-3 text-sm text-right <?php echo renderStockClass($vstock); ?>" id="stock-<?php echo $v['id']; ?>"><?php echo $vstock; ?></td>
                    <td class="px-6 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($v['unit']); ?></td>
                    <td class="px-6 py-3" id="status-<?php echo $v['id']; ?>"><?php echo renderShortBadge($vstock); ?></td>
                    <td class="px-6 py-3 text-sm text-gray-500 text-right" id="value-<?php echo $v['id']; ?>">Rs. <?php echo number_format($vvalue, 2); ?></td>
                    <td class="px-6 py-3 text-center">
                        <button onclick="openAdjustModal(<?php echo $v['id']; ?>, '<?php echo htmlspecialchars(addslashes($v['name'])); ?>', 'variant', <?php echo $vstock; ?>)" class="text-blue-500 hover:text-blue-700 text-xs font-medium">Adjust</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
