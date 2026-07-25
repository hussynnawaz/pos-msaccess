<?php
$productModel = new Product();
$categories = $productModel->categories();
?>

<header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Inventory</h1>
            <p class="text-sm text-gray-500 mt-0.5">Stock details for all products</p>
        </div>
    </div>
</header>

<main class="p-8">
    <div id="statsCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse h-24"></div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse h-24"></div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse h-24"></div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 animate-pulse h-24"></div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="searchInput" placeholder="Search by name, SKU, or barcode..."
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <select id="categoryFilter" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="stockFilter" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Stock</option>
                <option value="in">In Stock</option>
                <option value="low">Low Stock (&le;5)</option>
                <option value="out">Out of Stock</option>
            </select>
            <div id="searchIndicator" class="hidden">
                <svg class="animate-spin h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Purchase</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Selling</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Adjust</th>
                </tr>
            </thead>
            <tbody id="inventoryList" class="divide-y divide-gray-100">
                <tr><td colspan="10" class="px-6 py-12 text-center text-gray-400">
                    <svg class="animate-spin h-6 w-6 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Loading inventory...
                </td></tr>
            </tbody>
        </table>
    </div>
</main>

<div id="adjustModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center transition-opacity">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 transform transition-transform">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Adjust Stock</h2>
            <button onclick="closeAdjustModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-600 mb-1">Adjusting stock for</p>
            <p id="adjustProductName" class="text-base font-semibold text-gray-900 mb-4"></p>

            <div class="flex items-center gap-3 mb-4">
                <span class="text-sm text-gray-600">Current Stock:</span>
                <span id="adjustCurrentStock" class="text-lg font-bold text-gray-900"></span>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Adjustment Type</label>
                <div class="flex gap-3">
                    <button type="button" id="btnAdd" onclick="setAdjustType('add')" class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium border-2 border-green-500 bg-green-50 text-green-700">Add Stock</button>
                    <button type="button" id="btnSubtract" onclick="setAdjustType('subtract')" class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium border-2 border-gray-200 text-gray-600">Remove Stock</button>
                    <button type="button" id="btnSet" onclick="setAdjustType('set')" class="flex-1 py-2.5 px-4 rounded-lg text-sm font-medium border-2 border-gray-200 text-gray-600">Set Stock</button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity</label>
                <input type="number" id="adjustQuantity" min="0" value="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                <p class="text-sm text-gray-500">New Stock will be:</p>
                <p id="adjustPreview" class="text-lg font-bold text-gray-900"></p>
            </div>

            <div id="adjustError" class="hidden mb-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>

            <div class="flex justify-end gap-3">
                <button onclick="closeAdjustModal()" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button onclick="submitAdjust()" id="adjustSubmitBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors flex items-center gap-2">
                    <span id="adjustBtnText">Save</span>
                    <svg id="adjustSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 hidden items-center gap-2 transform transition-transform">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span id="toastMessage"></span>
</div>

<script>
(function() {
var adjustType = 'add';
var adjustId = null;
var adjustEntityType = 'product';
var adjustCurrentStockValue = 0;
var searchTimeout = null;

function buildParams() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    const stock = document.getElementById('stockFilter').value;
    const params = new URLSearchParams();
    if (search) params.set('search', search);
    if (category) params.set('category', category);
    if (stock) params.set('stock', stock);
    return params.toString();
}

function showLoading() {
    document.getElementById('searchIndicator').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('searchIndicator').classList.add('hidden');
}

function fadeOut(el, callback) {
    el.style.opacity = '0';
    el.style.transition = 'opacity 0.15s ease';
    setTimeout(() => { if (callback) callback(); }, 150);
}

function fadeIn(el) {
    el.style.opacity = '0';
    el.style.transition = 'opacity 0.2s ease';
    requestAnimationFrame(() => { el.style.opacity = '1'; });
}

async function loadStats() {
    try {
        const res = await fetch('/api/inventory-stats.php', { credentials: 'same-origin' });
        const html = await res.text();
        const container = document.getElementById('statsCards');
        fadeOut(container, () => {
            container.innerHTML = html;
            fadeIn(container);
        });
    } catch (e) {}
}

async function loadTable() {
    showLoading();
    try {
        const res = await fetch('/api/inventory-render.php?' + buildParams(), { credentials: 'same-origin' });
        const html = await res.text();
        const tbody = document.getElementById('inventoryList');
        fadeOut(tbody, () => {
            tbody.innerHTML = html;
            fadeIn(tbody);
        });
    } catch (e) {
        document.getElementById('inventoryList').innerHTML =
            '<tr><td colspan="10" class="px-6 py-12 text-center text-red-400">Failed to load. <button onclick="loadTable()" class="underline">Retry</button></td></tr>';
    }
    hideLoading();
}

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(loadTable, 400);
});

document.getElementById('categoryFilter').addEventListener('change', loadTable);
document.getElementById('stockFilter').addEventListener('change', loadTable);

function toggleVariants(productId) {
    const rows = document.querySelectorAll('.variant-row-' + productId);
    const arrow = document.getElementById('arrow-' + productId);
    const isVisible = rows.length > 0 && !rows[0].classList.contains('hidden');
    rows.forEach(r => {
        if (isVisible) {
            fadeOut(r, () => r.classList.add('hidden'));
        } else {
            r.classList.remove('hidden');
            fadeIn(r);
        }
    });
    if (arrow) arrow.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(90deg)';
}

function openAdjustModal(id, name, type, currentStock) {
    adjustId = id;
    adjustEntityType = type;
    adjustCurrentStockValue = currentStock;
    document.getElementById('adjustProductName').textContent = name;
    document.getElementById('adjustCurrentStock').textContent = currentStock;
    document.getElementById('adjustQuantity').value = 0;
    setAdjustType('add');
    document.getElementById('adjustError').classList.add('hidden');
    const modal = document.getElementById('adjustModal');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.querySelector('.bg-white').style.transform = 'scale(1)';
        modal.style.opacity = '1';
    });
}

function closeAdjustModal() {
    const modal = document.getElementById('adjustModal');
    modal.style.opacity = '0';
    modal.querySelector('.bg-white').style.transform = 'scale(0.95)';
    setTimeout(() => { modal.classList.add('hidden'); }, 200);
    adjustId = null;
}

function setAdjustType(type) {
    adjustType = type;
    document.getElementById('btnAdd').className = 'flex-1 py-2.5 px-4 rounded-lg text-sm font-medium border-2 transition-all ' + (type === 'add' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 text-gray-600 hover:border-green-300');
    document.getElementById('btnSubtract').className = 'flex-1 py-2.5 px-4 rounded-lg text-sm font-medium border-2 transition-all ' + (type === 'subtract' ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-200 text-gray-600 hover:border-red-300');
    document.getElementById('btnSet').className = 'flex-1 py-2.5 px-4 rounded-lg text-sm font-medium border-2 transition-all ' + (type === 'set' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-blue-300');
    updatePreview();
}

function updatePreview() {
    const qty = parseInt(document.getElementById('adjustQuantity').value) || 0;
    let newStock = adjustCurrentStockValue;
    if (adjustType === 'add') newStock = adjustCurrentStockValue + qty;
    else if (adjustType === 'subtract') newStock = Math.max(0, adjustCurrentStockValue - qty);
    else newStock = qty;
    document.getElementById('adjustPreview').textContent = newStock;
}

document.getElementById('adjustQuantity').addEventListener('input', updatePreview);

function getStockClass(stock) {
    if (stock <= 0) return 'text-red-600 font-bold';
    if (stock <= 5) return 'text-amber-600 font-semibold';
    return 'text-gray-900';
}

function getStatusBadge(stock) {
    if (stock <= 0) return '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Out of Stock</span>';
    if (stock <= 5) return '<span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Low Stock</span>';
    return '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">In Stock</span>';
}

function getShortBadge(stock) {
    if (stock <= 0) return '<span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Out</span>';
    if (stock <= 5) return '<span class="px-2 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Low</span>';
    return '<span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">In</span>';
}

function updateRowStock(id, type, newStock) {
    const stockCell = document.getElementById('stock-' + id);
    const statusCell = document.getElementById('status-' + id);
    const valueCell = document.getElementById('value-' + id);
    const row = document.querySelector('tr[data-id="' + id + '"]');

    if (stockCell) {
        stockCell.style.transition = 'color 0.3s ease';
        stockCell.textContent = newStock;
        stockCell.className = stockCell.className.replace(/text-(red|amber|gray)-\d+\s*font-(bold|semibold)?/g, '').trim();
        getStockClass(newStock).split(' ').forEach(c => stockCell.classList.add(c));
    }
    if (statusCell) {
        statusCell.innerHTML = type === 'variant' ? getShortBadge(newStock) : getStatusBadge(newStock);
    }
    if (row) {
        row.setAttribute('data-stock', newStock);
    }
}

async function submitAdjust() {
    if (adjustId === null) return;
    const qty = parseInt(document.getElementById('adjustQuantity').value) || 0;
    const errorDiv = document.getElementById('adjustError');
    const btn = document.getElementById('adjustSubmitBtn');
    const btnText = document.getElementById('adjustBtnText');
    const spinner = document.getElementById('adjustSpinner');

    errorDiv.classList.add('hidden');
    btn.disabled = true;
    btnText.textContent = 'Saving...';
    spinner.classList.remove('hidden');

    try {
        const res = await fetch('/api/inventory.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: adjustId, type: adjustEntityType, action: adjustType, quantity: qty })
        });
        const result = await res.json();
        if (result.success) {
            updateRowStock(adjustId, adjustEntityType, result.new_stock);
            closeAdjustModal();
            showToast(result.message);
            loadStats();
        } else {
            errorDiv.textContent = result.message;
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorDiv.textContent = 'Network error. Please try again.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Save';
        spinner.classList.add('hidden');
    }
}

function showToast(message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('hidden');
    toast.style.transform = 'translateX(0)';
    setTimeout(() => {
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => { toast.classList.add('hidden'); }, 300);
    }, 3000);
}

// Expose public functions to window scope for HTML inline handlers
window.openAdjustModal = openAdjustModal;
window.closeAdjustModal = closeAdjustModal;
window.setAdjustType = setAdjustType;
window.submitAdjust = submitAdjust;
window.toggleVariants = toggleVariants;
window.loadTable = loadTable;

loadStats();
loadTable();
})();
</script>
