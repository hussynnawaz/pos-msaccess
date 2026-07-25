<header class="bg-white border-b border-gray-200 px-8 py-4 sticky top-0 z-10">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Sales Report</h1>
            <p class="text-sm text-gray-500 mt-0.5">View all sales, profits, and item details</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="date" id="dateFrom" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="loadReport()">
            <span class="text-gray-400">to</span>
            <input type="date" id="dateTo" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="loadReport()">
            <input type="text" id="searchInput" placeholder="Search order..." class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-48" oninput="debounceSearch()">
        </div>
    </div>
</header>

<main class="p-8">
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Total Orders</p>
            <p id="summaryOrders" class="text-2xl font-bold text-gray-900">0</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Total Sales</p>
            <p id="summarySales" class="text-2xl font-bold text-blue-600">Rs. 0.00</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 mb-1">Total Profit</p>
            <p id="summaryProfit" class="text-2xl font-bold text-green-600">Rs. 0.00</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="w-10"></th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Sale #</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cashier</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total Sale</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Profit</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Receipt</th>
                </tr>
            </thead>
            <tbody id="reportBody" class="divide-y divide-gray-100"></tbody>
        </table>
        <div id="emptyState" class="flex flex-col items-center justify-center py-16 text-gray-400">
            <svg class="w-16 h-16 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm">No sales found</p>
        </div>
    </div>
</main>

<div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 hidden items-center gap-2">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    <span id="toastMessage"></span>
</div>

<script>
(function() {
var searchTimer;

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadReport, 300);
}

function loadReport() {
    var params = new URLSearchParams();
    var search = document.getElementById('searchInput').value;
    var dateFrom = document.getElementById('dateFrom').value;
    var dateTo = document.getElementById('dateTo').value;
    if (search) params.set('search', search);
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);
    params.set('action', 'report');

    fetch('/api/pos-sales.php?' + params.toString(), { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (!result.success) { showToast(result.message); return; }
            document.getElementById('summaryOrders').textContent = result.summary.total_orders;
            document.getElementById('summarySales').textContent = 'Rs. ' + result.summary.total_sale.toFixed(2);
            document.getElementById('summaryProfit').textContent = 'Rs. ' + result.summary.total_profit.toFixed(2);
            renderReport(result.data);
        })
        .catch(function() { showToast('Failed to load report'); });
}

function renderReport(data) {
    var tbody = document.getElementById('reportBody');
    var empty = document.getElementById('emptyState');
    if (!data.length) { tbody.innerHTML = ''; empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');

    var html = '';
    for (var i = 0; i < data.length; i++) {
        var row = data[i];
        var profitClass = parseFloat(row.profit) >= 0 ? 'text-green-600' : 'text-red-600';
        html += '<tr class="report-row hover:bg-gray-50 cursor-pointer" onclick="toggleItems(this, ' + row.id + ')" data-order-id="' + row.id + '">';
        html += '<td class="px-5 py-4"><svg class="w-4 h-4 text-gray-400 expand-icon transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></td>';
        html += '<td class="px-5 py-4 text-sm font-medium text-blue-600">' + esc(row.sale_number) + '</td>';
        html += '<td class="px-5 py-4 text-sm text-gray-500">' + esc(row.created_at) + '</td>';
        html += '<td class="px-5 py-4 text-sm text-gray-500">' + esc(row.cashier_name) + '</td>';
        html += '<td class="px-5 py-4 text-sm font-medium text-gray-900 text-right">Rs. ' + parseFloat(row.sale_total).toFixed(2) + '</td>';
        html += '<td class="px-5 py-4 text-sm font-semibold text-right ' + profitClass + '">Rs. ' + parseFloat(row.profit).toFixed(2) + '</td>';
        html += '<td class="px-5 py-4 text-right"><button onclick="event.stopPropagation(); printPosReceipt(' + row.id + ')" class="text-blue-500 hover:text-blue-700 text-sm font-medium">Print</button></td>';
        html += '</tr>';
        html += '<tr class="items-container hidden" id="items-' + row.id + '"><td colspan="7" class="p-0"><div class="bg-gray-50 px-12 py-4"><table class="w-full"><thead><tr>';
        html += '<th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Product</th>';
        html += '<th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Sell Price</th>';
        html += '<th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Qty</th>';
        html += '<th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Total</th>';
        html += '<th class="text-right px-3 py-2 text-xs font-semibold text-gray-500 uppercase">Profit</th>';
        html += '</tr></thead><tbody id="items-body-' + row.id + '"></tbody></table></div></td></tr>';
    }
    tbody.innerHTML = html;
}

function toggleItems(tr, orderId) {
    var container = document.getElementById('items-' + orderId);
    var icon = tr.querySelector('.expand-icon');
    if (!container.classList.contains('hidden')) {
        container.classList.add('hidden');
        icon.style.transform = '';
        return;
    }
    icon.style.transform = 'rotate(90deg)';
    container.classList.remove('hidden');

    var body = document.getElementById('items-body-' + orderId);
    if (body.children.length > 0) return;

    fetch('/api/pos-sales.php?action=items&sale_id=' + orderId, { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (!result.success || !result.data.length) {
                body.innerHTML = '<tr><td colspan="5" class="px-3 py-3 text-sm text-gray-400 text-center">No items found</td></tr>';
                return;
            }
            var html = '';
            for (var i = 0; i < result.data.length; i++) {
                var item = result.data[i];
                var profitClass = parseFloat(item.item_profit) >= 0 ? 'text-green-600' : 'text-red-600';
                html += '<tr class="border-t border-gray-200">';
                html += '<td class="px-3 py-2.5 text-sm text-gray-900">' + esc(item.product_name) + '</td>';
                html += '<td class="px-3 py-2.5 text-sm text-gray-600 text-right">Rs. ' + parseFloat(item.selling_price).toFixed(2) + '</td>';
                html += '<td class="px-3 py-2.5 text-sm text-gray-600 text-center">' + item.quantity + '</td>';
                html += '<td class="px-3 py-2.5 text-sm font-medium text-gray-900 text-right">Rs. ' + parseFloat(item.total).toFixed(2) + '</td>';
                html += '<td class="px-3 py-2.5 text-sm font-semibold text-right ' + profitClass + '">Rs. ' + parseFloat(item.item_profit).toFixed(2) + '</td>';
                html += '</tr>';
            }
            body.innerHTML = html;
        })
        .catch(function() { body.innerHTML = '<tr><td colspan="5" class="px-3 py-3 text-sm text-red-400 text-center">Failed to load items</td></tr>'; });
}

function esc(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

function showToast(message) {
    var toast = document.getElementById('toast');
    document.getElementById('toastMessage').textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('flex');
    setTimeout(function() { toast.classList.add('hidden'); toast.classList.remove('flex'); }, 3000);
}

function printPosReceipt(saleId) {
    fetch('/api/pos-sales.php?id=' + saleId, { credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (!result.success || !result.data) { showToast('Sale not found'); return; }
            var d = result.data;
            var items = d.items || [];

            var itemsHtml = '';
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                itemsHtml += '<tr><td>' + esc(item.product_name) + '</td>';
                itemsHtml += '<td class="c">' + item.quantity + '</td>';
                itemsHtml += '<td class="r">Rs. ' + parseFloat(item.total).toFixed(2) + '</td></tr>';
            }

            var paidSection = '';
            if (parseFloat(d.amount_paid) > 0) {
                paidSection = '<div class="row"><span>Paid</span><span>Rs. ' + parseFloat(d.amount_paid).toFixed(2) + '</span></div>';
                paidSection += '<div class="row"><span>Change</span><span>Rs. ' + parseFloat(d.change_amount).toFixed(2) + '</span></div>';
            }

            var receiptHtml = '<!DOCTYPE html><html><head><style>' +
                '@page { margin: 0 !important; size: 80mm; }' +
                '@media print { html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; overflow: hidden !important; } }' +
                '* { margin: 0; padding: 0; box-sizing: border-box; }' +
                'html, body { width: 80mm; margin: 0; padding: 0; font-family: "Courier New", monospace; font-size: 10px; color: #000; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; overflow-x: hidden; }' +
                '.receipt { width: 100%; margin: 0; padding: 0; }' +
                '.center { text-align: center; }' +
                '.store-name { font-size: 13px; font-weight: bold; letter-spacing: 1px; margin-bottom: 0.5mm; }' +
                '.store-info { font-size: 7.5px; color: #666; margin-bottom: 1mm; }' +
                '.divider { border-top: 1px dashed #999; margin: 1.5mm 0; }' +
                '.double-divider { border-top: 2px double #000; margin: 1.5mm 0; }' +
                '.info-row { display: flex; justify-content: space-between; font-size: 9px; margin-bottom: 0.3mm; line-height: 1.3; }' +
                '.info-row span:first-child { color: #555; }' +
                'table { width: 100%; border-collapse: collapse; margin: 1mm 0; font-size: 9px; }' +
                'th { border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 1mm 0; text-align: left; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.5px; }' +
                'td { padding: 0.8mm 0; vertical-align: top; line-height: 1.2; }' +
                '.r { text-align: right; } .c { text-align: center; }' +
                '.totals { margin-top: 1mm; }' +
                '.totals .row { display: flex; justify-content: space-between; padding: 0.5mm 0; font-size: 9px; }' +
                '.totals .row span:first-child { color: #555; }' +
                '.totals .grand { border-top: 2px solid #000; padding-top: 1mm; margin-top: 1mm; font-size: 12px; font-weight: bold; letter-spacing: 0.5px; }' +
                '.thank-you { font-size: 9.5px; font-weight: bold; margin-top: 2mm; color: #333; text-align: center; }' +
                '.footer { text-align: center; margin-top: 1mm; font-size: 7.5px; color: #888; }' +
                '</style></head><body><div class="receipt">' +

                '<div class="center">' +
                '<div class="store-name">MALIK TUC SHOP</div>' +
                '<div class="store-info">Best Quality, Best Prices</div>' +
                '</div>' +

                '<div class="double-divider"></div>' +

                '<div class="info-row"><span>Sale #</span><span>' + esc(d.sale_number) + '</span></div>' +
                '<div class="info-row"><span>Date</span><span>' + esc(d.created_at) + '</span></div>' +
                '<div class="info-row"><span>Cashier</span><span>' + esc(d.cashier_name) + '</span></div>' +
                (d.payment_method ? '<div class="info-row"><span>Payment</span><span>' + esc(d.payment_method.toUpperCase()) + '</span></div>' : '') +

                '<div class="divider"></div>' +

                '<table><thead><tr><th>Item</th><th class="c" style="width:15%">Qty</th><th class="r" style="width:28%">Total</th></tr></thead>' +
                '<tbody>' + itemsHtml + '</tbody></table>' +

                '<div class="double-divider"></div>' +

                '<div class="totals">' +
                '<div class="row"><span>Subtotal</span><span>Rs. ' + parseFloat(d.subtotal).toFixed(2) + '</span></div>' +
                (parseFloat(d.discount) > 0 ? '<div class="row"><span>Discount</span><span>- Rs. ' + parseFloat(d.discount).toFixed(2) + '</span></div>' : '') +
                (parseFloat(d.tax) > 0 ? '<div class="row"><span>Tax</span><span>Rs. ' + parseFloat(d.tax).toFixed(2) + '</span></div>' : '') +
                '<div class="row grand"><span>TOTAL</span><span>Rs. ' + parseFloat(d.total).toFixed(2) + '</span></div>' +
                paidSection +
                '</div>' +

                '<div class="double-divider"></div>' +

                '<div class="thank-you">Thank you for shopping!</div>' +
                '<div class="footer"><p>Visit us again</p></div>' +
                '</div>' +
                '<script>' +
                'window.onload = function() {' +
                '  var h = document.querySelector(".receipt").offsetHeight + 10;' +
                '  var s = document.createElement("style");' +
                '  s.textContent = "@page { size: 80mm " + h + "px; margin: 0 !important; }";' +
                '  document.head.appendChild(s);' +
                '  setTimeout(function() { window.print(); }, 200);' +
                '};' +
                '<\/script>' +
                '</body></html>';

            var win = window.open('', '_blank', 'width=400,height=600');
            win.document.write(receiptHtml);
            win.document.close();
        })
        .catch(function() { showToast('Failed to load sale data'); });
}

// Expose public functions to window scope for HTML inline handlers
window.debounceSearch = debounceSearch;
window.loadReport = loadReport;
window.toggleItems = toggleItems;
window.printPosReceipt = printPosReceipt;

loadReport();
})();
</script>
