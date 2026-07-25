<?php
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="searchInput" placeholder="Search products by name or barcode..."
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
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="productsTable">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
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
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                No products found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900 font-mono"><?php echo $p['id']; ?></td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($p['name']); ?></td>
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
                                    <button onclick="editProduct(<?php echo htmlspecialchars(json_encode($p)); ?>)" class="text-blue-500 hover:text-blue-700 text-sm font-medium mr-3">Edit</button>
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
                <button onclick="loadPage(<?php echo $currentPage - 1; ?>)" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Prev</button>
            <?php endif; ?>
            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                <button onclick="loadPage(<?php echo $i; ?>)" class="px-3 py-1.5 text-sm rounded-lg <?php echo $i === $currentPage ? 'bg-blue-500 text-white' : 'border border-gray-200 hover:bg-gray-50'; ?>"><?php echo $i; ?></button>
            <?php endfor; ?>
            <?php if ($currentPage < $totalPages): ?>
                <button onclick="loadPage(<?php echo $currentPage + 1; ?>)" class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg hover:bg-gray-50">Next</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</main>

<div id="productModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 id="modalTitle" class="text-lg font-semibold text-gray-900">Add New Product</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="productForm" onsubmit="submitProduct(event)" class="p-6">
            <input type="hidden" name="id" id="productId" value="">
            <div id="formError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name *</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
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
                    <input type="number" name="purchase_price" id="purchasePrice" step="0.01" min="0" required oninput="calculateNetAmount()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price (Rs.) *</label>
                    <input type="number" name="selling_price" step="0.01" min="0" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount (%)</label>
                    <input type="number" name="discount_pct" id="discountPct" step="0.01" min="0" max="100" value="0" oninput="calculateNetAmount()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">GST (Rs.)</label>
                    <input type="number" name="gst_amount" id="gstAmount" step="0.01" min="0" value="0" oninput="calculateNetAmount()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">WHT (Rs.)</label>
                    <input type="number" name="wht_amount" id="whtAmount" step="0.01" min="0" value="0" oninput="calculateNetAmount()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-600">Net Amount:</span>
                        <span id="netAmount" class="text-lg font-bold text-gray-900">Rs. 0.00</span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">(Price × Qty) - Discount + GST + WHT</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Stock *</label>
                    <input type="number" name="stock" id="stock" min="0" required oninput="calculateNetAmount()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select name="is_active" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
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
(function() {
function loadPage(page) {
    var search = document.getElementById('searchInput').value;
    var category = document.getElementById('categoryFilter').value;
    htmx.ajax('GET', '/admin?page=products&pn=' + page + '&search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category), {target: '#admin-content'});
}

function openModal() {
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('productId').value = '';
    document.getElementById('btnText').textContent = 'Save Product';
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
}

function editProduct(p) {
    document.getElementById('modalTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = p.id;
    document.getElementById('btnText').textContent = 'Update Product';
    var form = document.getElementById('productForm');
    form.elements['name'].value = p.name || '';
    form.elements['barcode'].value = p.barcode || '';
    form.elements['category'].value = p.category || '';
    form.elements['unit'].value = p.unit || 'pcs';
    form.elements['purchase_price'].value = p.purchase_price || 0;
    form.elements['selling_price'].value = p.selling_price || 0;
    form.elements['discount_pct'].value = p.discount_pct || 0;
    form.elements['gst_amount'].value = p.gst_amount || 0;
    form.elements['wht_amount'].value = p.wht_amount || 0;
    form.elements['stock'].value = p.stock || 0;
    form.elements['is_active'].value = p.is_active ? '1' : '0';
    calculateNetAmount();
    document.getElementById('formError').classList.add('hidden');
    document.getElementById('productModal').classList.remove('hidden');
    document.getElementById('productModal').classList.add('flex');
}

function closeModal() {
    document.getElementById('productModal').classList.add('hidden');
    document.getElementById('productModal').classList.remove('flex');
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('formError').classList.add('hidden');
    document.getElementById('netAmount').textContent = 'Rs. 0.00';
}

function calculateNetAmount() {
    var purchasePrice = parseFloat(document.getElementById('purchasePrice').value) || 0;
    var stock = parseInt(document.getElementById('stock').value) || 0;
    var discountPct = parseFloat(document.getElementById('discountPct').value) || 0;
    var gstAmount = parseFloat(document.getElementById('gstAmount').value) || 0;
    var whtAmount = parseFloat(document.getElementById('whtAmount').value) || 0;
    var total = purchasePrice * stock;
    var discountAmt = total * (discountPct / 100);
    var net = total - discountAmt + gstAmount + whtAmount;
    document.getElementById('netAmount').textContent = 'Rs. ' + net.toLocaleString('en-PK', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function showToast(message) {
    var toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('flex');
    setTimeout(function() { toast.classList.add('hidden'); toast.classList.remove('flex'); }, 3000);
}

var confirmResolve = null;
function showConfirm(message) {
    return new Promise(function(resolve) {
        confirmResolve = resolve;
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmModal').classList.remove('hidden');
        document.getElementById('confirmModal').classList.add('flex');
    });
}
function confirmOk() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.getElementById('confirmModal').classList.remove('flex');
    if (confirmResolve) confirmResolve(true);
    confirmResolve = null;
}
function confirmCancel() {
    document.getElementById('confirmModal').classList.add('hidden');
    document.getElementById('confirmModal').classList.remove('flex');
    if (confirmResolve) confirmResolve(false);
    confirmResolve = null;
}

async function submitProduct(e) {
    e.preventDefault();
    var form = e.target;
    var id = document.getElementById('productId').value;
    var isEdit = id !== '';
    var btn = document.getElementById('submitBtn');
    var btnText = document.getElementById('btnText');
    var spinner = document.getElementById('spinner');
    var errorDiv = document.getElementById('formError');
    var data = {};
    new FormData(form).forEach(function(v, k) { if (k !== 'has_variants') data[k] = v; });
    data.has_variants = false;

    btn.disabled = true;
    btnText.textContent = isEdit ? 'Updating...' : 'Saving...';
    spinner.classList.remove('hidden');
    errorDiv.classList.add('hidden');
    try {
        var res = await fetch('/api/products.php', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        var result = await res.json();
        if (result.success) {
            closeModal();
            showToast(result.message);
            setTimeout(function() { htmx.ajax('GET', '/admin?page=products', {target: '#admin-content'}); }, 500);
        } else {
            errorDiv.textContent = result.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.textContent = isEdit ? 'Update Product' : 'Save Product';
        spinner.classList.add('hidden');
    }
}

async function deleteProduct(id) {
    var confirmed = await showConfirm('Are you sure you want to delete this product?');
    if (!confirmed) return;
    try {
        var res = await fetch('/api/product.php?id=' + id, { method: 'DELETE', credentials: 'same-origin' });
        var result = await res.json();
        if (result.success) {
            showToast(result.message);
            setTimeout(function() { htmx.ajax('GET', '/admin?page=products', {target: '#admin-content'}); }, 500);
        } else {
            showToast(result.message);
        }
    } catch (err) {
        showToast('Network error. Please try again.');
    }
}

var searchTimeout;
function searchProducts() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function() {
        var search = document.getElementById('searchInput').value;
        var category = document.getElementById('categoryFilter').value;
        htmx.ajax('GET', '/admin?page=products&search=' + encodeURIComponent(search) + '&category=' + encodeURIComponent(category), {target: '#admin-content'});
    }, 400);
}

window.loadPage = loadPage;
window.openModal = openModal;
window.editProduct = editProduct;
window.closeModal = closeModal;
window.confirmOk = confirmOk;
window.confirmCancel = confirmCancel;
window.submitProduct = submitProduct;
window.deleteProduct = deleteProduct;
window.searchProducts = searchProducts;
window.calculateNetAmount = calculateNetAmount;
})();
</script>
