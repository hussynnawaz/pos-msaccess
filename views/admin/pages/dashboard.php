<?php
$userName = $_SESSION['user_name'] ?? 'Admin';
?>
<header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-0.5">Welcome back, <?php echo htmlspecialchars($userName); ?></p>
        </div>
        <div class="flex items-center gap-4">
        </div>
    </div>
</header>
<main class="p-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Today's Sales</p>
                    <p id="dashSales" class="text-2xl font-bold text-gray-900 mt-1">Rs. 0</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Sales Today</p>
                    <p id="dashOrders" class="text-2xl font-bold text-gray-900 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Products</p>
                    <p id="dashProducts" class="text-2xl font-bold text-gray-900 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Low Stock</p>
                    <p id="dashLowStock" class="text-2xl font-bold text-red-600 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="/admin/pos" hx-get="/admin?page=pos" hx-target="#admin-content" hx-push-url="true" class="flex items-center gap-3 p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors group">
                <div class="w-10 h-10 bg-blue-500 group-hover:bg-blue-600 rounded-lg flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Open POS</p>
                    <p class="text-xs text-gray-500">Start selling</p>
                </div>
            </a>
            <a href="/admin/products" hx-get="/admin?page=products" hx-target="#admin-content" hx-push-url="true" class="flex items-center gap-3 p-4 bg-green-50 hover:bg-green-100 rounded-xl transition-colors group">
                <div class="w-10 h-10 bg-green-500 group-hover:bg-green-600 rounded-lg flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Add Product</p>
                    <p class="text-xs text-gray-500">New item</p>
                </div>
            </a>
            <a href="/admin/orders" hx-get="/admin?page=orders" hx-target="#admin-content" hx-push-url="true" class="flex items-center gap-3 p-4 bg-purple-50 hover:bg-purple-100 rounded-xl transition-colors group">
                <div class="w-10 h-10 bg-purple-500 group-hover:bg-purple-600 rounded-lg flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">View Orders</p>
                    <p class="text-xs text-gray-500">Order history</p>
                </div>
            </a>
            <a href="/admin/inventory" hx-get="/admin?page=inventory" hx-target="#admin-content" hx-push-url="true" class="flex items-center gap-3 p-4 bg-amber-50 hover:bg-amber-100 rounded-xl transition-colors group">
                <div class="w-10 h-10 bg-amber-500 group-hover:bg-amber-600 rounded-lg flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">Inventory</p>
                    <p class="text-xs text-gray-500">Stock levels</p>
                </div>
            </a>
        </div>
    </div>
</main>
<script>
(function() {
fetch('/api/dashboard.php', { credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            document.getElementById('dashSales').textContent = 'Rs. ' + parseFloat(d.today_sales).toFixed(2);
            document.getElementById('dashOrders').textContent = d.today_orders;
            document.getElementById('dashProducts').textContent = d.total_products;
            document.getElementById('dashLowStock').textContent = d.low_stock;
        }
    });
})();
</script>
