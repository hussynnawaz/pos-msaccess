<?php
$userName = $_SESSION['user_name'] ?? 'Admin';
?>
<header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Point of Sale</h1>
            <p class="text-sm text-gray-500 mt-0.5">Scan or add items to cart</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-gray-500">Cashier:</label>
                <input type="text" id="cashierNameInput" value="<?php echo addslashes($userName); ?>"
                    class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40"
                    onchange="cashierName = this.value; localStorage.setItem('cashierName', this.value);"
                    onfocus="this.select()">
            </div>
            <span class="text-gray-300">|</span>
            <span id="currentTime" class="text-sm text-gray-500"></span>
            <button onclick="clearCart()" class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg transition-colors">Clear All</button>
        </div>
    </div>
</header>

<main class="p-8 flex gap-6 h-[calc(100vh-73px)]">
    <div class="flex-1 flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <input type="text" id="barcodeInput" placeholder="Scan barcode or type product name/SKU..."
                    class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" autofocus>
                <div id="barcodeSuggestions" class="hidden absolute left-0 right-0 top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-20 max-h-60 overflow-y-auto"></div>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100 sticky top-0">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Sell. Price</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Tax %</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Disc.</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody id="cartItems" class="divide-y divide-gray-100"></tbody>
            </table>
            <div id="emptyCart" class="flex flex-col items-center justify-center py-20 text-gray-400">
                <svg class="w-16 h-16 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                <p class="text-sm">Scan a barcode or search for a product</p>
            </div>
        </div>
    </div>

    <div class="w-96 flex flex-col bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100"><h2 class="text-lg font-semibold text-gray-900">Order Summary</h2></div>
        <div class="flex-1 p-5 space-y-4 overflow-y-auto">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Discount (Rs.)</label>
                <input type="number" id="globalDiscount" value="0" min="0" step="0.01" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" oninput="recalculate()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Tax Rate (%)</label>
                <input type="number" id="globalTax" value="0" min="0" max="100" step="0.01" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" oninput="recalculate()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Payment Method</label>
                <select id="paymentMethod" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="cash">Cash</option><option value="card">Card</option><option value="bank">Bank Transfer</option><option value="credit">Credit</option>
                </select>
            </div>
            <div id="amountPaidSection">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Amount Paid (Rs.)</label>
                <input type="number" id="amountPaid" value="0" min="0" step="0.01" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" oninput="recalculate()">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes</label>
                <textarea id="orderNotes" rows="2" placeholder="Optional notes..." class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
        </div>
        <div class="border-t border-gray-200 p-5 space-y-3 bg-gray-50">
            <div class="flex justify-between text-sm"><span class="text-gray-500">Subtotal</span><span id="subtotalDisplay" class="font-medium text-gray-900">Rs. 0.00</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Discount</span><span id="discountDisplay" class="font-medium text-red-600">- Rs. 0.00</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500">Tax</span><span id="taxDisplay" class="font-medium text-gray-900">Rs. 0.00</span></div>
            <div class="border-t border-gray-200 pt-3 flex justify-between"><span class="text-base font-semibold text-gray-900">Grand Total</span><span id="grandTotalDisplay" class="text-xl font-bold text-blue-600">Rs. 0.00</span></div>
            <div class="flex justify-between text-sm" id="changeRow" style="display:none;"><span class="text-gray-500">Change</span><span id="changeDisplay" class="font-semibold text-green-600">Rs. 0.00</span></div>
        </div>
        <div class="p-5 border-t border-gray-100">
            <button onclick="completeOrder()" id="completeBtn" class="w-full py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">Save Sale</button>
        </div>
    </div>
</main>

<div id="printModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-1">Sale Completed!</h3>
        <p id="printOrderNumber" class="text-sm text-gray-500 mb-6">Order #ORD-0000</p>
        <div class="flex gap-3">
            <button onclick="closePrintModal()" class="flex-1 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">Close</button>
            <button onclick="printReceipt()" class="flex-1 px-4 py-2.5 text-sm font-medium text-white bg-blue-500 hover:bg-blue-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Receipt
            </button>
        </div>
    </div>
</div>

<div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 hidden items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span id="toastMessage"></span>
</div>

<div id="productErrorModal" class="fixed inset-0 bg-black/50 z-[60] hidden items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl w-80 mx-4 p-8 text-center">
        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Product Not Found</h3>
        <p id="productErrorMessage" class="text-sm text-gray-500 mb-6">This product is not in the database or failed to fetch the product.</p>
        <button onclick="closeProductErrorModal()" class="w-full px-4 py-2.5 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-lg transition-colors">OK</button>
    </div>
</div>

<script>
var cashierName = '<?php echo addslashes($userName); ?>';
(function() {
    var saved = localStorage.getItem('cashierName');
    if (saved) {
        cashierName = saved;
        var input = document.getElementById('cashierNameInput');
        if (input) input.value = saved;
    }
})();
document.getElementById('productErrorModal').addEventListener('click', function(e) {
    if (e.target === this) closeProductErrorModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modal = document.getElementById('productErrorModal');
        if (modal && !modal.classList.contains('hidden')) closeProductErrorModal();
    }
});
</script>
<script src="/views/admin/pages/pos.js"></script>
</body>
</html>
