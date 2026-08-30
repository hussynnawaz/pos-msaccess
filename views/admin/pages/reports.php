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
                var itemLines =
                    '<div class="item-row">' +
                    '<div class="item-left">' +
                    '<div class="item-name">' + esc(item.product_name) + '</div>' +
                    '<div class="item-qty-price">' + item.quantity + ' x Rs. ' + parseFloat(item.selling_price).toFixed(2) + '</div>' +
                    '</div>' +
                    '<div class="item-right">Rs. ' + parseFloat(item.total).toFixed(2) + '</div>' +
                    '</div>';
                if (parseFloat(item.discount) > 0 || parseFloat(item.tax) > 0) {
                    var details = [];
                    if (parseFloat(item.discount) > 0) details.push('Disc: -Rs.' + parseFloat(item.discount).toFixed(2));
                    if (parseFloat(item.tax) > 0) details.push('Tax: ' + item.tax + '%');
                    itemLines += '<div class="item-sub">' + details.join(' | ') + '</div>';
                }
                itemsHtml += itemLines;
            }

            var paidSection = '';
            if (parseFloat(d.amount_paid) > 0) {
                paidSection =
                    '<div class="summary-row"><span>Amount Paid</span><span>Rs. ' + parseFloat(d.amount_paid).toFixed(2) + '</span></div>' +
                    '<div class="summary-row highlight-green"><span>Change</span><span>Rs. ' + parseFloat(d.change_amount).toFixed(2) + '</span></div>';
            }

            var receiptHtml =
                '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' +
                '@page { margin: 0 !important; size: 80mm auto; }' +
                '@media print { html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; overflow: hidden !important; } }' +
                '* { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }' +
                'html, body { width: 80mm; font-family: Calibri, sans-serif; font-size: 11px; color: #000; background: #fff; }' +
                '@media print { ' +
                '  body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }' +
                '  .receipt { filter: contrast(1.8) brightness(0.85) !important; }' +
                '}' +
                '.receipt { width: 80mm; padding: 4mm 3mm; filter: contrast(1.8) brightness(0.15); }' +
                '.center { text-align: center; }' +
                '.logo { width: 38mm; margin: 0 auto 2mm; display: block; }' +
                '.store-name { font-size: 15px; font-weight: 900; letter-spacing: 1.5px; margin-bottom: 1mm; color: #000; }' +
                '.store-tagline { font-size: 8px; color: #333; letter-spacing: 0.5px; margin-bottom: 2mm; font-weight: 600; }' +
                '.store-contact { font-size: 7.5px; color: #444; margin-bottom: 1mm; font-weight: 600; }' +
                '.divider { border-top: 1px dashed #666; margin: 2mm 0; }' +
                '.divider-solid { border-top: 2px solid #000; margin: 2mm 0; }' +
                '.divider-double { border-top: 3px double #000; margin: 2mm 0; }' +
                '.info-grid { margin: 1.5mm 0; }' +
                '.info-row { display: flex; justify-content: space-between; font-size: 9.5px; line-height: 1.6; }' +
                '.info-row .label { color: #333; font-weight: 600; }' +
                '.info-row .value { font-weight: 900; color: #000; }' +
                '.section-title { font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #333; margin: 2mm 0 1mm; }' +
                '.items-header { display: flex; justify-content: space-between; font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; padding: 1mm 0; border-top: 2px solid #000; border-bottom: 2px solid #000; margin-bottom: 1mm; }' +
                '.items-header span:first-child { flex: 1; }' +
                '.items-header span:last-child { text-align: right; width: 28mm; }' +
                '.item-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 0.8mm 0; line-height: 1.3; }' +
                '.item-left { flex: 1; padding-right: 2mm; }' +
                '.item-right { text-align: right; font-weight: 900; white-space: nowrap; color: #000; }' +
                '.item-name { font-size: 10px; font-weight: 900; margin-bottom: 0.3mm; word-break: break-word; color: #000; }' +
                '.item-qty-price { font-size: 8.5px; color: #333; font-weight: 600; }' +
                '.item-sub { font-size: 7.5px; color: #555; padding-left: 1mm; margin-top: 0.3mm; font-weight: 600; }' +
                '.summary { margin: 2mm 0; }' +
                '.summary-row { display: flex; justify-content: space-between; font-size: 9.5px; padding: 0.6mm 0; }' +
                '.summary-row .s-label { color: #333; font-weight: 600; }' +
                '.summary-row .s-value { font-weight: 900; color: #000; }' +
                '.summary-row.discount .s-value { color: #000; font-weight: 900; }' +
                '.summary-total { display: flex; justify-content: space-between; font-size: 14px; font-weight: 900; padding: 1.5mm 0; border-top: 3px solid #000; border-bottom: 3px solid #000; margin: 1.5mm 0; letter-spacing: 0.5px; color: #000; }' +
                '.summary-row.highlight-green .s-value { color: #000; font-weight: 900; }' +
                '.payment-badge { display: inline-block; padding: 0.5mm 2mm; background: #000; color: #fff; border-radius: 2mm; font-size: 8px; font-weight: 900; letter-spacing: 0.5px; margin-top: 1mm; }' +
                '.thankyou { font-size: 10px; font-weight: 900; text-align: center; margin: 3mm 0 1mm; letter-spacing: 0.5px; color: #000; }' +
                '.footer-text { font-size: 7.5px; color: #333; text-align: center; line-height: 1.4; margin: 0.5mm 0; font-weight: 600; }' +
                '</style></head><body><div class="receipt">' +
                '<div class="center">' +
                '<img src="/public/assets/images/malik-tuc-shop.png" class="logo" alt="Logo">' +
                '<div class="store-name">MALIK TUC SHOP</div>' +
                '<div class="store-tagline">Best Quality, Best Prices</div>' +
                '<div class="store-contact">Contact: 0315-5318453</div>' +
                '</div>' +
                '<div class="divider-double"></div>' +
                '<div class="info-grid">' +
                '<div class="info-row"><span class="label">Receipt #</span><span class="value">' + esc(d.sale_number) + '</span></div>' +
                '<div class="info-row"><span class="label">Date</span><span class="value">' + esc(d.created_at) + '</span></div>' +
                '<div class="info-row"><span class="label">Cashier</span><span class="value">' + esc(d.cashier_name) + '</span></div>' +
                (d.payment_method ? '<div class="info-row"><span class="label">Payment</span><span class="value"><span class="payment-badge">' + esc(d.payment_method.toUpperCase()) + '</span></span></div>' : '') +
                '</div>' +
                '<div class="divider"></div>' +
                '<div class="items-header"><span>Item</span><span style="text-align:right">Total</span></div>' +
                itemsHtml +
                '<div class="divider-double"></div>' +
                '<div class="summary">' +
                '<div class="summary-row"><span class="s-label">Subtotal</span><span class="s-value">Rs. ' + parseFloat(d.subtotal).toFixed(2) + '</span></div>' +
                (parseFloat(d.discount) > 0 ? '<div class="summary-row discount"><span class="s-label">Discount</span><span class="s-value">- Rs. ' + parseFloat(d.discount).toFixed(2) + '</span></div>' : '') +
                (parseFloat(d.tax) > 0 ? '<div class="summary-row"><span class="s-label">Tax</span><span class="s-value">Rs. ' + parseFloat(d.tax).toFixed(2) + '</span></div>' : '') +
                '<div class="summary-total"><span>TOTAL</span><span>Rs. ' + parseFloat(d.total).toFixed(2) + '</span></div>' +
                paidSection +
                '</div>' +
                '<div class="divider-double"></div>' +
                '<div class="thankyou">Thank You for Shopping!</div>' +
                '<div class="footer-text">Visit us again</div>' +
                '<div class="footer-text">Malik Tuc Shop &mdash; Quality You Can Trust</div>' +
                '</div>' +
                '<script>' +
                'window.onload = function() {' +
                '  var el = document.querySelector(".receipt");' +
                '  var h = Math.ceil(el.getBoundingClientRect().height) + 10;' +
                '  var s = document.createElement("style");' +
                '  s.textContent = "@page { size: 80mm " + h + "px; margin: 0 !important; }";' +
                '  document.head.appendChild(s);' +
                '  setTimeout(function() { window.print(); }, 300);' +
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
