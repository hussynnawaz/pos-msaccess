<?php
$currentPage = $_GET['page'] ?? 'pos';
$userName = $_SESSION['user_name'] ?? 'Admin';
$userRole = $_SESSION['user_role'] ?? 'admin';
?>
<aside class="w-72 bg-white min-h-screen fixed left-0 top-0 flex flex-col border-r border-gray-200">
    <div class="px-6 py-5 border-b border-gray-100">
        <a href="/admin" class="flex items-center">
            <img src="/public/assets/images/malik-tuc-shop.png" alt="Logo" class="h-20 w-auto">
        </a>
    </div>
    <nav class="flex-1 px-4 py-5 space-y-1 overflow-y-auto">
        <a href="/admin" data-page="dashboard" hx-get="/admin?page=dashboard" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Dashboard
        </a>
        <a href="/admin/pos" data-page="pos" hx-get="/admin?page=pos" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'pos' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Point of Sale
        </a>
        <a href="/admin/orders" data-page="orders" hx-get="/admin?page=orders" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            Orders
        </a>
        <a href="/admin/reports" data-page="reports" hx-get="/admin?page=reports" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'reports' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Reports
        </a>
        <a href="/admin/products" data-page="products" hx-get="/admin?page=products" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'products' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Products
        </a>
        <a href="/admin/suppliers" data-page="suppliers" hx-get="/admin?page=suppliers" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'suppliers' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Suppliers
        </a>
        <a href="/admin/inventory" data-page="inventory" hx-get="/admin?page=inventory" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'inventory' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            Inventory
        </a>
        <a href="/admin/backup" data-page="backup" hx-get="/admin?page=backup" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'backup' ? 'active' : ''; ?> flex items-center gap-3.5 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            Database Backup
        </a>
    </nav>
    <div class="border-t border-gray-100 px-4 py-4">
        <a href="/admin/profile" data-page="profile" hx-get="/admin?page=profile" hx-target="#admin-content" hx-push-url="true" class="sidebar-link <?php echo $currentPage === 'profile' ? 'active' : ''; ?> flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 text-[15px] font-medium">
            <div class="w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-semibold shrink-0"><?php echo strtoupper(substr($userName, 0, 1)); ?></div>
            <div class="flex-1 min-w-0">
                <p class="font-medium text-gray-900 truncate"><?php echo htmlspecialchars($userName); ?></p>
                <p class="text-xs text-gray-500 capitalize"><?php echo htmlspecialchars($userRole); ?></p>
            </div>
            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
</aside>
<script>
function setActive(page) {
    document.querySelectorAll('.sidebar-link').forEach(function(l) {
        l.classList.remove('active');
        if (l.getAttribute('data-page') === page) {
            l.classList.add('active');
        }
    });
}
</script>
