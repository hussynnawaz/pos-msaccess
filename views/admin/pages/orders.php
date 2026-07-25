<?php
$orderModel = new Order();
$supplierModel = new Supplier();
$supplierList = $supplierModel->all(['active' => 1]);

$filters = [
    'search'     => $_GET['search'] ?? '',
    'status'     => $_GET['status'] ?? '',
    'order_type' => $_GET['type'] ?? 'purchase',
];
$orders = $orderModel->all($filters);
?>

<header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Orders</h1>
            <p class="text-sm text-gray-500 mt-0.5">Manage purchase and sales orders</p>
        </div>
        <button onclick="openCreateModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2.5 rounded-lg text-sm font-medium flex items-center gap-2 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Purchase Order
        </button>
    </div>
</header>

<main class="p-8">
    <div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-lg w-fit">
        <button onclick="switchTab('purchase')" id="tabPurchase" class="px-4 py-2 rounded-md text-sm font-medium transition-colors <?php echo $filters['order_type'] === 'purchase' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'; ?>">Purchase Orders</button>
        <button onclick="switchTab('sale')" id="tabSale" class="px-4 py-2 rounded-md text-sm font-medium transition-colors <?php echo $filters['order_type'] === 'sale' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'; ?>">Sales Orders</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" id="searchInput" placeholder="Search by order number..."
                    value="<?php echo htmlspecialchars($filters['search']); ?>"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    onkeyup="searchOrders()">
            </div>
            <select id="statusFilter" onchange="searchOrders()" class="border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $filters['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Order #</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Supplier</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="ordersList" class="divide-y divide-gray-100">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            No orders found
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o):
                        $statusClass = match($o['status']) {
                            'completed' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-amber-100 text-amber-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            'refunded' => 'bg-purple-100 text-purple-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-blue-600"><?php echo htmlspecialchars($o['order_number']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo htmlspecialchars($o['supplier_name'] ?? '-'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $statusClass; ?>"><?php echo ucfirst($o['status']); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 text-right">Rs. <?php echo number_format($o['total'], 2); ?></td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="viewOrder(<?php echo $o['id']; ?>)" class="text-blue-500 hover:text-blue-700 text-sm font-medium">View</button>
                                    <?php if ($o['order_type'] === 'purchase' && $o['status'] === 'pending'): ?>
                                        <button onclick="receiveOrder(<?php echo $o['id']; ?>)" class="text-green-500 hover:text-green-700 text-sm font-medium">Receive</button>
                                    <?php endif; ?>
                                    <?php if ($o['order_type'] === 'purchase'): ?>
                                        <button onclick="printInvoice(<?php echo $o['id']; ?>)" class="text-gray-500 hover:text-gray-700 text-sm font-medium">PDF</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div id="createModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h2 class="text-lg font-semibold text-gray-900">New Purchase Order</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Supplier *</label>
                    <select id="poSupplier" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Supplier</option>
                        <?php foreach ($supplierList as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Expected Date</label>
                    <input type="date" id="poExpectedDate" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method</label>
                    <select id="poPaymentMethod" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="credit">Credit</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                    <input type="text" id="poNotes" placeholder="Optional notes" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-900">Order Items</h3>
                    <button onclick="addOrderItem()" class="text-blue-500 hover:text-blue-600 text-sm font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Add Item
                    </button>
                </div>

                <div class="bg-gray-50 rounded-lg p-3 mb-2 grid grid-cols-12 gap-2 text-xs font-semibold text-gray-500 uppercase">
                    <div class="col-span-4">Product</div>
                    <div class="col-span-2">Barcode</div>
                    <div class="col-span-2">Price</div>
                    <div class="col-span-2">Qty</div>
                    <div class="col-span-1 text-right">Total</div>
                    <div class="col-span-1"></div>
                </div>

                <div id="orderItems" class="space-y-2">
                    <div class="order-item grid grid-cols-12 gap-2 items-center" data-idx="0">
                        <div class="col-span-4 relative">
                            <input type="text" placeholder="Type product name..." class="product-search w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" oninput="searchProduct(this)" onfocus="searchProduct(this)" autocomplete="off">
                            <input type="hidden" class="product-id" value="">
                            <input type="hidden" class="product-name" value="">
                            <div class="product-dropdown hidden absolute z-20 top-full left-0 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto mt-1"></div>
                        </div>
                        <div class="col-span-2">
                            <input type="text" class="item-barcode w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50" placeholder="-">
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" min="0" value="0" onchange="recalculateTotal()" class="item-price w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="col-span-2">
                            <input type="number" min="1" value="1" onchange="recalculateTotal()" class="item-qty w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="col-span-1 text-right">
                            <span class="item-total text-sm font-semibold text-gray-900">0</span>
                        </div>
                        <div class="col-span-1 text-right">
                            <button onclick="removeOrderItem(this)" class="text-red-400 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-start mt-6 pt-4 border-t border-gray-100">
                <div class="text-sm text-gray-500">
                    <p>Items: <span id="poItemCount" class="font-semibold text-gray-900">0</span></p>
                </div>
                <div class="text-right">
                    <div class="flex items-center gap-4 mb-2">
                        <span class="text-sm text-gray-500">Subtotal:</span>
                        <span id="poSubtotal" class="text-sm font-semibold text-gray-900">Rs. 0</span>
                    </div>
                    <div class="flex items-center gap-4 mb-2">
                        <label class="text-sm text-gray-500">Discount:</label>
                        <input type="number" id="poDiscount" step="0.01" min="0" value="0" onchange="recalculateTotal()" class="w-24 px-2 py-1 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-4 mb-2">
                        <label class="text-sm text-gray-500">Tax (%):</label>
                        <input type="number" id="poTax" step="0.01" min="0" value="0" onchange="recalculateTotal()" class="w-24 px-2 py-1 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-4 mb-2">
                        <label class="text-sm text-gray-500">Initial Payment:</label>
                        <input type="number" id="poInitialPayment" step="0.01" min="0" value="0" onchange="recalculateTotal()" class="w-24 px-2 py-1 border border-gray-200 rounded text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-center gap-4 pt-2 border-t border-gray-100">
                        <span class="text-base font-semibold text-gray-900">Grand Total:</span>
                        <span id="poGrandTotal" class="text-xl font-bold text-blue-600">Rs. 0</span>
                    </div>
                    <div id="poBalanceRow" class="hidden flex items-center gap-4 mt-1">
                        <span class="text-sm font-medium text-red-600">Balance Due:</span>
                        <span id="poBalanceDue" class="text-sm font-bold text-red-600">Rs. 0</span>
                    </div>
                </div>
            </div>

            <div id="poError" class="hidden mt-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>

            <div class="flex justify-end gap-3 mt-6">
                <button onclick="closeCreateModal()" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button onclick="submitPurchaseOrder()" id="poSubmitBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors flex items-center gap-2">
                    <span id="poBtnText">Create Order</span>
                    <svg id="poSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="viewModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h2 class="text-lg font-semibold text-gray-900">Order Details</h2>
            <div class="flex items-center gap-3">
                <button onclick="printInvoice(currentOrderId)" class="text-blue-500 hover:text-blue-700 text-sm font-medium">Download PDF</button>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
        <div id="viewContent" class="p-6">
            <div class="text-center py-8 text-gray-400">Loading...</div>
        </div>
    </div>
</div>

<div id="receiveModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Receive Purchase Order</h2>
            <button onclick="closeReceiveModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="bg-gray-50 rounded-lg p-4 mb-5">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Order #</span>
                    <span id="rcOrderNumber" class="font-semibold text-gray-900">-</span>
                </div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Supplier</span>
                    <span id="rcSupplier" class="font-medium text-gray-900">-</span>
                </div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-500">Items</span>
                    <span id="rcItemCount" class="font-medium text-gray-900">0</span>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-gray-200 mt-2">
                    <span class="font-semibold text-gray-900">Grand Total</span>
                    <span id="rcGrandTotal" class="font-bold text-blue-600">Rs. 0</span>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Initial Payment (Already Paid)</label>
                    <input type="number" id="rcInitialPayment" step="0.01" min="0" value="0" onchange="recalculateReceive()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Now</label>
                    <input type="number" id="rcPaymentNow" step="0.01" min="0" value="0" onchange="recalculateReceive()" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method</label>
                    <select id="rcPaymentMethod" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank Transfer</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Total Paid</span>
                        <span id="rcTotalPaid" class="font-semibold text-green-600">Rs. 0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Balance Due</span>
                        <span id="rcBalanceDue" class="font-bold text-red-600">Rs. 0</span>
                    </div>
                </div>
            </div>

            <div id="rcError" class="hidden mt-4 p-3 bg-red-50 border border-red-200 text-red-600 text-sm rounded-lg"></div>

            <div class="flex justify-end gap-3 mt-6">
                <button onclick="closeReceiveModal()" class="px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Cancel</button>
                <button onclick="confirmReceive()" id="rcSubmitBtn" class="px-6 py-2.5 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span id="rcBtnText">Confirm & Receive</span>
                    <svg id="rcSpinner" class="hidden animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 hidden items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span id="toastMessage"></span>
</div>

<script>
(function() {
var currentOrderId = null;
var orderItemIndex = 1;
var searchTimeouts = {};
var activeDropdown = null;

function switchTab(type) {
    window.location.href = '/admin/orders?type=' + type;
}

var searchTimeout;
function searchOrders() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const search = document.getElementById('searchInput').value;
        const status = document.getElementById('statusFilter').value;
        const type = new URLSearchParams(window.location.search).get('type') || 'purchase';
        window.location.href = '/admin/orders?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status) + '&type=' + type;
    }, 400);
}

function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.getElementById('createModal').classList.add('flex');
}

function closeCreateModal() {
    document.getElementById('createModal').classList.add('hidden');
    document.getElementById('createModal').classList.remove('flex');
    resetForm();
}

function resetForm() {
    document.getElementById('poSupplier').value = '';
    document.getElementById('poExpectedDate').value = '';
    document.getElementById('poPaymentMethod').value = 'credit';
    document.getElementById('poNotes').value = '';
    document.getElementById('poDiscount').value = 0;
    document.getElementById('poTax').value = 0;
    document.getElementById('poInitialPayment').value = 0;
    document.getElementById('poError').classList.add('hidden');
    document.getElementById('poBalanceRow').classList.add('hidden');
    const container = document.getElementById('orderItems');
    container.innerHTML = container.firstElementChild.outerHTML;
    orderItemIndex = 1;
    recalculateTotal();
}

function addOrderItem() {
    const container = document.getElementById('orderItems');
    const template = container.firstElementChild.cloneNode(true);
    template.dataset.idx = orderItemIndex;
    template.querySelectorAll('input').forEach(el => {
        if (el.type === 'hidden') el.value = '';
        else if (el.type === 'number') el.value = el.classList.contains('item-qty') ? '1' : '0';
        else el.value = '';
    });
    template.querySelector('.item-total').textContent = '0';
    template.querySelector('.product-dropdown').classList.add('hidden');
    container.appendChild(template);
    orderItemIndex++;
}

function removeOrderItem(btn) {
    const container = document.getElementById('orderItems');
    if (container.children.length > 1) {
        btn.closest('.order-item').remove();
        recalculateTotal();
    }
}

function searchProduct(input) {
    const row = input.closest('.order-item');
    const dropdown = row.querySelector('.product-dropdown');
    const query = input.value.trim();

    clearTimeout(searchTimeouts[row.dataset.idx || '0']);

    if (query.length < 1) {
        dropdown.classList.add('hidden');
        return;
    }

    searchTimeouts[row.dataset.idx || '0'] = setTimeout(async () => {
        try {
            const res = await fetch('/api/products.php?search=' + encodeURIComponent(query), { credentials: 'same-origin' });
            const result = await res.json();
            if (!result.success || !result.data.length) {
                dropdown.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500 cursor-pointer hover:bg-blue-50" onclick="selectCustomProduct(this, \'' + query.replace(/'/g, "\\'") + '\')">Add "' + query + '" as new product</div>';
                dropdown.classList.remove('hidden');
                return;
            }
            let html = '';
            result.data.forEach(p => {
                html += '<div class="px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 border-b border-gray-50 last:border-0" onclick="selectProduct(this)" data-id="' + p.id + '" data-name="' + p.name.replace(/"/g, '&quot;') + '" data-barcode="' + (p.barcode || '') + '" data-price="' + p.purchase_price + '"><span class="font-medium">' + p.name + '</span> <span class="text-gray-400 text-xs">' + (p.sku || '') + '</span></div>';
            });
            html += '<div class="px-3 py-2 text-sm text-gray-500 cursor-pointer hover:bg-blue-50" onclick="selectCustomProduct(this, \'' + query.replace(/'/g, "\\'") + '\')">Add "' + query + '" as new product</div>';
            dropdown.innerHTML = html;
            dropdown.classList.remove('hidden');
        } catch (e) {
            dropdown.classList.add('hidden');
        }
    }, 200);
}

function selectProduct(el) {
    const row = el.closest('.order-item');
    row.querySelector('.product-id').value = el.dataset.id;
    row.querySelector('.product-name').value = el.dataset.name;
    row.querySelector('.product-search').value = el.dataset.name;
    row.querySelector('.item-barcode').value = el.dataset.barcode || '-';
    row.querySelector('.item-price').value = el.dataset.price || 0;
    row.querySelector('.product-dropdown').classList.add('hidden');
    recalculateTotal();
}

function selectCustomProduct(el, name) {
    const row = el.closest('.order-item');
    row.querySelector('.product-id').value = '';
    row.querySelector('.product-name').value = name;
    row.querySelector('.product-search').value = name;
    row.querySelector('.item-barcode').value = '-';
    row.querySelector('.item-price').value = 0;
    row.querySelector('.product-dropdown').classList.add('hidden');
    row.querySelector('.item-price').focus();
    recalculateTotal();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-search') && !e.target.closest('.product-dropdown')) {
        document.querySelectorAll('.product-dropdown').forEach(d => d.classList.add('hidden'));
    }
});

function recalculateTotal() {
    const items = document.querySelectorAll('.order-item');
    let subtotal = 0;
    let count = 0;

    items.forEach(row => {
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const qty = parseInt(row.querySelector('.item-qty').value) || 0;
        const total = price * qty;
        row.querySelector('.item-total').textContent = total.toFixed(2);
        if (row.querySelector('.product-search').value.trim()) {
            subtotal += total;
            count++;
        }
    });

    const discount = parseFloat(document.getElementById('poDiscount').value) || 0;
    const taxPercent = parseFloat(document.getElementById('poTax').value) || 0;
    const taxable = subtotal - discount;
    const tax = taxable * (taxPercent / 100);
    const grandTotal = taxable + tax;
    const initialPayment = parseFloat(document.getElementById('poInitialPayment').value) || 0;
    const balance = grandTotal - initialPayment;

    document.getElementById('poItemCount').textContent = count;
    document.getElementById('poSubtotal').textContent = 'Rs. ' + subtotal.toFixed(2);
    document.getElementById('poGrandTotal').textContent = 'Rs. ' + grandTotal.toFixed(2);

    const balanceRow = document.getElementById('poBalanceRow');
    if (initialPayment > 0 && balance > 0) {
        balanceRow.classList.remove('hidden');
        document.getElementById('poBalanceDue').textContent = 'Rs. ' + balance.toFixed(2);
    } else {
        balanceRow.classList.add('hidden');
    }
}

async function submitPurchaseOrder() {
    const supplier = document.getElementById('poSupplier').value;
    if (!supplier) {
        showError('Please select a supplier');
        return;
    }

    const items = [];
    document.querySelectorAll('.order-item').forEach(row => {
        const productId = row.querySelector('.product-id').value;
        const productName = row.querySelector('.product-name').value || row.querySelector('.product-search').value;
        if (!productName) return;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const qty = parseInt(row.querySelector('.item-qty').value) || 1;
        const item = {
            product_name: productName,
            unit_price: price,
            purchase_price: price,
            quantity: qty,
            total: price * qty
        };
        if (productId) item.product_id = parseInt(productId);
        items.push(item);
    });

    if (items.length === 0) {
        showError('Please add at least one item');
        return;
    }

    const subtotal = items.reduce((sum, i) => sum + i.total, 0);
    const discount = parseFloat(document.getElementById('poDiscount').value) || 0;
    const taxPercent = parseFloat(document.getElementById('poTax').value) || 0;
    const taxable = subtotal - discount;
    const tax = taxable * (taxPercent / 100);
    const grandTotal = taxable + tax;
    const initialPayment = parseFloat(document.getElementById('poInitialPayment').value) || 0;

    const data = {
        supplier_id: parseInt(supplier),
        subtotal: subtotal,
        discount: discount,
        tax: tax,
        total: grandTotal,
        initial_payment: initialPayment,
        payment_method: document.getElementById('poPaymentMethod').value,
        notes: document.getElementById('poNotes').value,
        expected_date: document.getElementById('poExpectedDate').value || null,
        items: items
    };

    const btn = document.getElementById('poSubmitBtn');
    const btnText = document.getElementById('poBtnText');
    const spinner = document.getElementById('poSpinner');

    btn.disabled = true;
    btnText.textContent = 'Creating...';
    spinner.classList.remove('hidden');

    try {
        const res = await fetch('/api/purchase-order.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const text = await res.text();
        let result;
        try { result = JSON.parse(text); } catch(e) { result = { success: false, message: 'Server error: ' + text.substring(0, 200) }; }
        if (result.success) {
            closeCreateModal();
            showToast(result.message);
            setTimeout(() => location.reload(), 500);
        } else {
            showError(result.message);
        }
    } catch (err) {
        showError('Network error: ' + err.message);
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Create Order';
        spinner.classList.add('hidden');
    }
}

function showError(msg) {
    const div = document.getElementById('poError');
    div.textContent = msg;
    div.classList.remove('hidden');
}

async function viewOrder(id) {
    currentOrderId = id;
    const content = document.getElementById('viewContent');
    content.innerHTML = '<div class="text-center py-8 text-gray-400">Loading...</div>';
    document.getElementById('viewModal').classList.remove('hidden');
    document.getElementById('viewModal').classList.add('flex');

    try {
        const res = await fetch('/api/orders.php?id=' + id, { credentials: 'same-origin' });
        const result = await res.json();
        if (!result.success) {
            content.innerHTML = '<div class="text-center py-8 text-red-400">Order not found</div>';
            return;
        }
        const o = result.data;
        const statusClass = { completed: 'bg-green-100 text-green-700', pending: 'bg-amber-100 text-amber-700', cancelled: 'bg-red-100 text-red-700' }[o.status] || 'bg-gray-100 text-gray-700';

        let itemsHtml = '';
        (o.items || []).forEach(item => {
            itemsHtml += `
                <tr class="border-b border-gray-100">
                    <td class="py-2 text-sm text-gray-900">${item.product_name}</td>
                    <td class="py-2 text-sm text-gray-500 font-mono">${item.sku || '-'}</td>
                    <td class="py-2 text-sm text-gray-500 text-center">${item.quantity}</td>
                    <td class="py-2 text-sm text-gray-500 text-right">Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td class="py-2 text-sm font-semibold text-gray-900 text-right">Rs. ${parseFloat(item.total).toFixed(2)}</td>
                </tr>`;
        });

        const initialPayment = parseFloat(o.initial_payment) || 0;
        const balance = parseFloat(o.total) - initialPayment;

        content.innerHTML = `
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-lg font-bold text-gray-900">${o.order_number}</p>
                    <p class="text-sm text-gray-500">${o.supplier_name ? 'Supplier: ' + o.supplier_name : ''}</p>
                </div>
                <span class="px-3 py-1 text-sm font-medium rounded-full ${statusClass}">${o.status.charAt(0).toUpperCase() + o.status.slice(1)}</span>
            </div>
            <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                <div><span class="text-gray-500">Date:</span> <span class="font-medium">${new Date(o.created_at).toLocaleDateString()}</span></div>
                <div><span class="text-gray-500">Payment:</span> <span class="font-medium capitalize">${o.payment_method}</span></div>
                ${o.expected_date ? '<div><span class="text-gray-500">Expected:</span> <span class="font-medium">' + o.expected_date + '</span></div>' : ''}
                ${o.notes ? '<div><span class="text-gray-500">Notes:</span> <span class="font-medium">' + o.notes + '</span></div>' : ''}
            </div>
            <table class="w-full mb-6">
                <thead class="border-b border-gray-200">
                    <tr>
                        <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="text-left py-2 text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="text-center py-2 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Price</th>
                        <th class="text-right py-2 text-xs font-semibold text-gray-500 uppercase">Total</th>
                    </tr>
                </thead>
                <tbody>${itemsHtml}</tbody>
            </table>
            <div class="text-right space-y-1">
                <div class="text-sm text-gray-500">Subtotal: <span class="font-medium text-gray-900">Rs. ${parseFloat(o.subtotal).toFixed(2)}</span></div>
                ${parseFloat(o.discount) > 0 ? '<div class="text-sm text-gray-500">Discount: <span class="font-medium text-gray-900">- Rs. ' + parseFloat(o.discount).toFixed(2) + '</span></div>' : ''}
                ${parseFloat(o.tax) > 0 ? '<div class="text-sm text-gray-500">Tax: <span class="font-medium text-gray-900">Rs. ' + parseFloat(o.tax).toFixed(2) + '</span></div>' : ''}
                <div class="text-lg font-bold text-gray-900 pt-2 border-t border-gray-200">Total: Rs. ${parseFloat(o.total).toFixed(2)}</div>
                ${initialPayment > 0 ? '<div class="text-sm text-green-600">Initial Payment: <span class="font-medium">Rs. ' + initialPayment.toFixed(2) + '</span></div>' : ''}
                ${initialPayment > 0 && balance > 0 ? '<div class="text-sm font-bold text-red-600">Balance Due: Rs. ' + balance.toFixed(2) + '</div>' : ''}
            </div>`;
    } catch (err) {
        content.innerHTML = '<div class="text-center py-8 text-red-400">Failed to load order</div>';
    }
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
    document.getElementById('viewModal').classList.remove('flex');
    currentOrderId = null;
}

let receiveOrderId = null;
let receiveOrderData = null;

async function receiveOrder(id) {
    receiveOrderId = id;
    const content = document.getElementById('rcOrderNumber');
    document.getElementById('rcError').classList.add('hidden');

    try {
        const res = await fetch('/api/orders.php?id=' + id, { credentials: 'same-origin' });
        const result = await res.json();
        if (!result.success) {
            showToast('Order not found');
            return;
        }
        receiveOrderData = result.data;
        const o = receiveOrderData;

        const initialPayment = parseFloat(o.initial_payment) || 0;
        const total = parseFloat(o.total) || 0;
        const balance = total - initialPayment;

        document.getElementById('rcOrderNumber').textContent = o.order_number;
        document.getElementById('rcSupplier').textContent = o.supplier_name || '-';
        document.getElementById('rcItemCount').textContent = (o.items || []).length;
        document.getElementById('rcGrandTotal').textContent = 'Rs. ' + total.toFixed(2);
        document.getElementById('rcInitialPayment').value = initialPayment.toFixed(2);
        document.getElementById('rcPaymentNow').value = balance > 0 ? balance.toFixed(2) : '0';
        document.getElementById('rcPaymentMethod').value = 'cash';

        recalculateReceive();

        document.getElementById('receiveModal').classList.remove('hidden');
        document.getElementById('receiveModal').classList.add('flex');
    } catch (err) {
        showToast('Failed to load order');
    }
}

function closeReceiveModal() {
    document.getElementById('receiveModal').classList.add('hidden');
    document.getElementById('receiveModal').classList.remove('flex');
    receiveOrderId = null;
    receiveOrderData = null;
}

function recalculateReceive() {
    if (!receiveOrderData) return;
    const total = parseFloat(receiveOrderData.total) || 0;
    const initial = parseFloat(document.getElementById('rcInitialPayment').value) || 0;
    const now = parseFloat(document.getElementById('rcPaymentNow').value) || 0;
    const totalPaid = initial + now;
    const balance = total - totalPaid;

    document.getElementById('rcTotalPaid').textContent = 'Rs. ' + totalPaid.toFixed(2);
    document.getElementById('rcTotalPaid').className = 'font-semibold ' + (totalPaid >= total ? 'text-green-600' : 'text-amber-600');
    document.getElementById('rcBalanceDue').textContent = 'Rs. ' + Math.max(0, balance).toFixed(2);
    document.getElementById('rcBalanceDue').className = 'font-bold ' + (balance > 0 ? 'text-red-600' : 'text-green-600');
}

async function confirmReceive() {
    if (!receiveOrderId) return;

    const initial = parseFloat(document.getElementById('rcInitialPayment').value) || 0;
    const now = parseFloat(document.getElementById('rcPaymentNow').value) || 0;
    const totalPaid = initial + now;
    const total = parseFloat(receiveOrderData.total) || 0;

    if (totalPaid > total + 0.01) {
        document.getElementById('rcError').textContent = 'Total paid cannot exceed grand total';
        document.getElementById('rcError').classList.remove('hidden');
        return;
    }

    const btn = document.getElementById('rcSubmitBtn');
    const btnText = document.getElementById('rcBtnText');
    const spinner = document.getElementById('rcSpinner');
    btn.disabled = true;
    btnText.textContent = 'Receiving...';
    spinner.classList.remove('hidden');
    document.getElementById('rcError').classList.add('hidden');

    try {
        const res = await fetch('/api/purchase-order.php', {
            method: 'PUT',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: receiveOrderId,
                action: 'receive',
                initial_payment: initial,
                payment_now: now,
                payment_method: document.getElementById('rcPaymentMethod').value
            })
        });
        const text = await res.text();
        let result;
        try { result = JSON.parse(text); } catch(e) { result = { success: false, message: 'Server error' }; }

        if (result.success) {
            closeReceiveModal();
            showToast(result.message);
            setTimeout(() => location.reload(), 500);
        } else {
            document.getElementById('rcError').textContent = result.message;
            document.getElementById('rcError').classList.remove('hidden');
        }
    } catch (err) {
        document.getElementById('rcError').textContent = 'Network error. Please try again.';
        document.getElementById('rcError').classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.textContent = 'Confirm & Receive';
        spinner.classList.add('hidden');
    }
}

function printInvoice(id) {
    window.open('/api/invoice.php?id=' + id, '_blank');
}

function showToast(message) {
    const toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('flex');
    setTimeout(() => { toast.classList.add('hidden'); toast.classList.remove('flex'); }, 3000);
}

// Expose public functions to window scope for HTML inline handlers
window.switchTab = switchTab;
window.searchOrders = searchOrders;
window.openCreateModal = openCreateModal;
window.closeCreateModal = closeCreateModal;
window.addOrderItem = addOrderItem;
window.removeOrderItem = removeOrderItem;
window.searchProduct = searchProduct;
window.selectProduct = selectProduct;
window.selectCustomProduct = selectCustomProduct;
window.recalculateTotal = recalculateTotal;
window.submitPurchaseOrder = submitPurchaseOrder;
window.viewOrder = viewOrder;
window.closeViewModal = closeViewModal;
window.receiveOrder = receiveOrder;
window.closeReceiveModal = closeReceiveModal;
window.recalculateReceive = recalculateReceive;
window.confirmReceive = confirmReceive;
window.printInvoice = printInvoice;
})();
</script>
