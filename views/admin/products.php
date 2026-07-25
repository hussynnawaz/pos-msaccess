<?php
require_once __DIR__ . '/../../bootstrap.php';
$session = new SessionManager();
$session->start();

if (!$session->isLoggedIn()) {
    header('Location: /login');
    exit;
}

$userName = $session->get('user_name', 'Admin');
$userRole = $session->get('user_role', 'staff');

$productModel = new Product();
$filters = [
    'search'   => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
    'page'     => max(1, (int)($_GET['pn'] ?? 1)),
    'per_page' => 50,
];
$products = $productModel->all($filters);
$totalProducts = $productModel->totalCount($filters);
$totalPages = max(1, ceil($totalProducts / $filters['per_page']));
$currentPage = $filters['page'];
$categories = $productModel->categories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>
    <link rel="stylesheet" href="/public/assets/css/tailwind.css">
    <style>
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .sidebar-link { transition: all 0.15s ease; }
        .sidebar-link.active { background: #eff6ff; color: #2563eb; font-weight: 600; }
        .sidebar-link:hover:not(.active) { background: #f1f5f9; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex">
    <!-- Main Content -->
    <div class="flex-1 ml-72">
        <header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Products</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Manage your product catalog</p>
                </div>
                <button onclick="openModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Product
                </button>
            </div>
        </header>

        <main class="p-8">
            <!-- Search & Filter -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="flex-1 relative">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" id="searchInput" placeholder="Search products by name, SKU, or barcode..."
                            value="<?php echo htmlspecialchars($filters['search']); ?>"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            onkeyup="searchProducts()">
                    </div>
                    <select id="categoryFilter" onchange="searchProducts()" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filters['category'] === $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div id="productsTable">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Purchase</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Selling</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsList" class="divide-y divide-gray-100">
                            <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        No products found
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($products as $p): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-gray-900 font-mono"><?php echo $p['id']; ?></td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 font-mono"><?php echo htmlspecialchars($p['sku'] ?? '-'); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($p['category'] ?? '-'); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-600">Rs. <?php echo number_format($p['purchase_price'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">Rs. <?php echo number_format($p['selling_price'], 2); ?></td>
                                        <td class="px-6 py-4 text-sm <?php echo $p['stock'] <= 5 ? 'text-red-600 font-semibold' : 'text-gray-600'; ?>"><?php echo $p['stock']; ?> <?php echo $p['unit']; ?></td>
                                        <td class="px-6 py-4">
                                            <?php if ($p['is_active']): ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">Active</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="text-red-500 hover:text-red-700 text-sm font-medium">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-between mt-4">
                <p class="text-sm text-gray-500">Showing <?php echo (($currentPage - 1) * $filters['per_page']) + 1; ?>-<?php echo min($currentPage * $filters['per_page'], $totalProducts); ?> of <?php echo $totalProducts; ?> products</p>
                <div class="flex gap-1">
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Prev</a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="px-3 py-1.5 text-sm rounded-lg <?php echo $i === $currentPage ? 'bg-blue-500 text-white' : 'border border-gray-200 hover:bg-gray-50'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Product Modal -->
    <div id="productModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Add New Product</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="productForm" onsubmit="submitProduct(event)" class="p-6">
                <div id="formError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name *</label>
                        <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU</label>
                        <input type="text" name="sku" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Barcode</label>
                        <input type="text" name="barcode" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Category</label>
                        <select name="category" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Unit</label>
                        <select name="unit" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="pcs">Pieces</option>
                            <option value="kg">Kilogram</option>
                            <option value="g">Gram</option>
                            <option value="l">Liter</option>
                            <option value="ml">Milliliter</option>
                            <option value="box">Box</option>
                            <option value="pack">Pack</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Purchase Price (Rs.) *</label>
                        <input type="number" name="purchase_price" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price (Rs.) *</label>
                        <input type="number" name="selling_price" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock *</label>
                        <input type="number" name="stock" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select name="is_active" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="has_variants" id="hasVariants" onchange="toggleVariants()" class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700">This product has variants (e.g., different sizes/weights)</span>
                        </label>
                    </div>
                </div>

                <!-- Variants Section -->
                <div id="variantsSection" class="hidden mt-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-semibold text-gray-900">Product Variants</h3>
                        <button type="button" onclick="addVariantRow()" class="text-blue-500 hover:text-blue-600 text-sm font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Add Variant
                        </button>
                    </div>
                    <div id="variantsContainer" class="space-y-3">
                        <div class="variant-row bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <div class="grid grid-cols-6 gap-2 items-end">
                                <div class="col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Variant Name *</label>
                                    <input type="text" name="variants[0][name]" placeholder="e.g. 250ml, 1kg" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit</label>
                                    <select name="variants[0][unit]" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="pcs">Pieces</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="g">Gram</option>
                                        <option value="l">Liter</option>
                                        <option value="ml">Milliliter</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Purchase Price</label>
                                    <input type="number" name="variants[0][purchase_price]" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Selling Price *</label>
                                    <input type="number" name="variants[0][selling_price]" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock</label>
                                    <input type="number" name="variants[0][stock]" min="0" placeholder="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div class="flex justify-end mt-2">
                                <button type="button" onclick="removeVariantRow(this)" class="text-red-500 hover:text-red-600 text-xs font-medium">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeModal()" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                    <button type="submit" id="submitBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors flex items-center gap-2">
                        <span id="btnText">Save Product</span>
                        <svg id="spinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Toast -->
    <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 hidden items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span id="toastMessage"></span>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm</h3>
                    <p id="confirmMessage" class="text-sm text-gray-600 mt-1"></p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button onclick="confirmCancel()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                <button onclick="confirmOk()" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors">Delete</button>
            </div>
        </div>
    </div>

    <script>
    let variantIndex = 1;

    function toggleVariants() {
        const section = document.getElementById('variantsSection');
        const checkbox = document.getElementById('hasVariants');
        if (checkbox.checked) {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    function addVariantRow() {
        const container = document.getElementById('variantsContainer');
        const idx = variantIndex++;
        const units = ['pcs', 'kg', 'g', 'l', 'ml'];
        const unitOptions = units.map(u => `<option value="${u}">${u === 'pcs' ? 'Pieces' : u === 'kg' ? 'Kilogram' : u === 'g' ? 'Gram' : u === 'l' ? 'Liter' : 'Milliliter'}</option>`).join('');

        const row = document.createElement('div');
        row.className = 'variant-row bg-gray-50 rounded-lg p-3 border border-gray-200';
        row.innerHTML = `
            <div class="grid grid-cols-6 gap-2 items-end">
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Variant Name *</label>
                    <input type="text" name="variants[${idx}][name]" placeholder="e.g. 250ml, 1kg" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unit</label>
                    <select name="variants[${idx}][unit]" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">${unitOptions}</select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Purchase Price</label>
                    <input type="number" name="variants[${idx}][purchase_price]" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Selling Price *</label>
                    <input type="number" name="variants[${idx}][selling_price]" step="0.01" min="0" placeholder="0.00" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Stock</label>
                    <input type="number" name="variants[${idx}][stock]" min="0" placeholder="0" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="flex justify-end mt-2">
                <button type="button" onclick="removeVariantRow(this)" class="text-red-500 hover:text-red-600 text-xs font-medium">Remove</button>
            </div>
        `;
        container.appendChild(row);
    }

    function removeVariantRow(btn) {
        const row = btn.closest('.variant-row');
        if (document.querySelectorAll('.variant-row').length > 1) {
            row.remove();
        } else {
            showToast('You need at least one variant row.');
        }
    }

    function openModal() {
        const modal = document.getElementById('productModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('productModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.getElementById('productForm').reset();
        document.getElementById('formError').classList.add('hidden');
        document.getElementById('variantsSection').classList.add('hidden');
        const container = document.getElementById('variantsContainer');
        const rows = container.querySelectorAll('.variant-row');
        rows.forEach((r, i) => { if (i > 0) r.remove(); });
        variantIndex = 1;
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        document.getElementById('toastMessage').textContent = message;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        setTimeout(() => { toast.classList.add('hidden'); toast.classList.remove('flex'); }, 3000);
    }

    let confirmResolve = null;
    function showConfirm(message) {
        return new Promise(resolve => {
            confirmResolve = resolve;
            document.getElementById('confirmMessage').textContent = message;
            const modal = document.getElementById('confirmModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });
    }
    function confirmOk() {
        const modal = document.getElementById('confirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (confirmResolve) confirmResolve(true);
        confirmResolve = null;
    }
    function confirmCancel() {
        const modal = document.getElementById('confirmModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (confirmResolve) confirmResolve(false);
        confirmResolve = null;
    }

    async function submitProduct(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('spinner');
        const errorDiv = document.getElementById('formError');

        const formData = new FormData(form);
        const data = {};
        const variants = [];

        formData.forEach((v, k) => {
            const match = k.match(/^variants\[(\d+)\]\[(.+)\]$/);
            if (match) {
                const idx = parseInt(match[1]);
                const field = match[2];
                if (!variants[idx]) variants[idx] = {};
                variants[idx][field] = v;
            } else {
                data[k] = v;
            }
        });

        if (data.has_variants === 'on') {
            data.has_variants = true;
            data.variants = variants.filter(v => v && v.name);
        } else {
            data.has_variants = false;
        }

        btn.disabled = true;
        btnText.textContent = 'Saving...';
        spinner.classList.remove('hidden');
        errorDiv.classList.add('hidden');

        try {
            const res = await fetch('/api/products.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (result.success) {
                closeModal();
                showToast(result.message);
                setTimeout(() => location.reload(), 500);
            } else {
                errorDiv.textContent = result.message;
                errorDiv.classList.remove('hidden');
            }
        } catch (err) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Save Product';
            spinner.classList.add('hidden');
        }
    }

    async function deleteProduct(id) {
        const confirmed = await showConfirm('Are you sure you want to delete this product?');
        if (!confirmed) return;

        try {
            const res = await fetch('/api/product.php?id=' + id, {
                method: 'DELETE',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (result.success) {
                showToast(result.message);
                setTimeout(() => location.reload(), 500);
            } else {
                showToast(result.message);
            }
        } catch (err) {
            showToast('Network error. Please try again.');
        }
    }

    let searchTimeout;
    function searchProducts() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            const search = document.getElementById('searchInput').value;
            const category = document.getElementById('categoryFilter').value;
            window.location.href = '/admin/products?search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category);
        }, 400);
    }
    </script>
</body>
</html>
