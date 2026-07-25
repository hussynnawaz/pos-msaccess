<?php
require_once __DIR__ . '/../bootstrap.php';

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$orderId = (int)($_GET['id'] ?? 0);
if (!$orderId) {
    http_response_code(400);
    echo 'Missing order ID';
    exit;
}

$orderModel = new Order();
$order = $orderModel->find($orderId);

if (!$order) {
    http_response_code(404);
    echo 'Order not found';
    exit;
}

$logoPath = dirname(__DIR__) . '/public/assets/images/malik-tuc-shop.png';
$logoUrl = '/public/assets/images/malik-tuc-shop.png';
$logoExists = file_exists($logoPath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($order['order_number']); ?> - Invoice</title>
<style>
    @page { size: A4; margin: 12mm 15mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #1a1a1a; background: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page { width: 180mm; margin: 0 auto; padding: 0; }
    .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 2px solid #1a56db; margin-bottom: 16px; }
    .logo img { height: 120px; width: auto; }
    .invoice-title { font-size: 22px; font-weight: 800; color: #1a56db; text-transform: uppercase; letter-spacing: 2px; }
    .info-row { display: flex; gap: 0; margin-bottom: 14px; background: #f1f5f9; border-radius: 6px; overflow: hidden; }
    .info-box { flex: 1; padding: 10px 14px; border-right: 1px solid #e2e8f0; }
    .info-box:last-child { border-right: none; }
    .info-label { font-size: 8px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 3px; font-weight: 600; }
    .info-value { font-size: 12px; font-weight: 700; color: #0f172a; }
    .status { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-completed { background: #d1fae5; color: #065f46; }
    .status-cancelled { background: #fee2e2; color: #991b1b; }
    .supplier-bar { margin-bottom: 14px; padding: 8px 12px; background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 0 6px 6px 0; }
    .supplier-title { font-size: 8px; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 700; margin-bottom: 2px; }
    .supplier-name { font-size: 12px; font-weight: 700; color: #1e3a5f; }
    .supplier-meta { font-size: 9px; color: #64748b; margin-top: 2px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
    thead th { background: #1a56db; color: #fff; padding: 7px 8px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
    tbody td { padding: 7px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
    tbody tr:nth-child(even) { background: #f8fafc; }
    tbody tr:last-child td { border-bottom: 2px solid #e5e7eb; }
    .th-num { width: 24px; text-align: center; }
    .th-product { text-align: left; }
    .th-qty { width: 44px; text-align: center; }
    .th-price { width: 85px; text-align: right; }
    .th-total { width: 90px; text-align: right; }
    .td-num { text-align: center; color: #94a3b8; font-weight: 600; }
    .td-product { font-weight: 600; color: #1e293b; }
    .td-qty { text-align: center; font-weight: 600; }
    .td-price { text-align: right; color: #475569; }
    .td-total { text-align: right; font-weight: 700; color: #0f172a; }
    .totals-wrap { display: flex; justify-content: flex-end; margin-bottom: 16px; }
    .totals { width: 200px; }
    .totals-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 10px; }
    .totals-row .t-label { color: #64748b; }
    .totals-row .t-value { font-weight: 600; color: #1e293b; }
    .totals-grand { border-top: 2px solid #1a56db; margin-top: 4px; padding-top: 6px; }
    .totals-grand .t-label { font-size: 12px; font-weight: 800; color: #0f172a; }
    .totals-grand .t-value { font-size: 14px; font-weight: 800; color: #1a56db; }
    .sig-stamp { display: flex; justify-content: flex-end; margin-bottom: 16px; margin-top: 8px; }
    .sig-stamp-inner { width: 200px; text-align: center; position: relative; height: 100px; }
    .stamp-img { height: 80px; width: auto; position: absolute; top: 0; left: 50%; transform: translateX(-50%); z-index: 2; opacity: 0.85; }
    .sign-img { height: 60px; width: auto; position: absolute; bottom: 8px; left: 50%; transform: translateX(-50%); z-index: 1; }
    .sign-label { font-size: 9px; color: #64748b; position: absolute; bottom: 0; left: 0; right: 0; border-top: 1px solid #cbd5e1; padding-top: 4px; }
    .notes { padding: 8px 12px; background: #fffbeb; border-left: 3px solid #f59e0b; border-radius: 0 6px 6px 0; margin-bottom: 16px; }
    .notes-label { font-size: 8px; font-weight: 700; color: #b45309; text-transform: uppercase; margin-bottom: 2px; }
    .notes-text { font-size: 10px; color: #78716c; }
    .footer { text-align: center; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #94a3b8; }
    .print-btn { position: fixed; bottom: 20px; right: 20px; padding: 12px 24px; background: #1a56db; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(26,86,219,0.3); z-index: 999; }
    .print-btn:hover { background: #1648b8; }
    @media print {
        .print-btn { display: none !important; }
        body { background: #fff; }
        .page { width: 100%; }
    }
</style>
</head>
<body>

<div class="page">
    <div class="header">
        <div class="logo">
            <?php if ($logoExists): ?>
                <img src="<?php echo $logoUrl; ?>" alt="Malik Tuc Shop">
            <?php else: ?>
                <div style="font-size:20px;font-weight:800;color:#1a56db;">Malik Tuc Shop</div>
            <?php endif; ?>
        </div>
        <div style="text-align:right;">
            <div class="invoice-title"><?php echo ($order['order_type'] ?? 'sale') === 'purchase' ? 'Purchase Order' : 'Sales Invoice'; ?></div>
        </div>
    </div>

    <div class="info-row">
        <div class="info-box">
            <div class="info-label">Order Number</div>
            <div class="info-value"><?php echo htmlspecialchars($order['order_number']); ?></div>
        </div>
        <div class="info-box">
            <div class="info-label">Date</div>
            <div class="info-value"><?php echo date('d M Y', strtotime($order['created_at'])); ?></div>
        </div>
        <?php if ($order['expected_date']): ?>
        <div class="info-box">
            <div class="info-label">Expected Date</div>
            <div class="info-value"><?php echo date('d M Y', strtotime($order['expected_date'])); ?></div>
        </div>
        <?php endif; ?>
        <div class="info-box">
            <div class="info-label">Status</div>
            <div class="info-value">
                <span class="status status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
            </div>
        </div>
    </div>

    <?php if ($order['supplier_name']): ?>
    <div class="supplier-bar">
        <div class="supplier-title">Supplier</div>
        <div class="supplier-name"><?php echo htmlspecialchars($order['supplier_name']); ?></div>
        <div class="supplier-meta">
            <?php
            $parts = [];
            if ($order['supplier_phone']) $parts[] = htmlspecialchars($order['supplier_phone']);
            if ($order['supplier_email']) $parts[] = htmlspecialchars($order['supplier_email']);
            if ($order['supplier_address']) $parts[] = htmlspecialchars($order['supplier_address']);
            echo implode(' &middot; ', $parts);
            ?>
        </div>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th class="th-num">#</th>
                <th class="th-product">Product</th>
                <th class="th-qty">Qty</th>
                <th class="th-price">Unit Price</th>
                <th class="th-total">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($order['items'] as $item): ?>
            <tr>
                <td class="td-num"><?php echo $i++; ?></td>
                <td class="td-product"><?php echo htmlspecialchars($item['product_name']); ?></td>
                <td class="td-qty"><?php echo $item['quantity']; ?></td>
                <td class="td-price">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                <td class="td-total">Rs. <?php echo number_format($item['total'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals-wrap">
        <div class="totals">
            <div class="totals-row">
                <span class="t-label">Subtotal</span>
                <span class="t-value">Rs. <?php echo number_format($order['subtotal'], 2); ?></span>
            </div>
            <?php if ($order['discount'] > 0): ?>
            <div class="totals-row">
                <span class="t-label">Discount</span>
                <span class="t-value" style="color:#ef4444;">- Rs. <?php echo number_format($order['discount'], 2); ?></span>
            </div>
            <?php endif; ?>
            <?php if ($order['tax'] > 0): ?>
            <div class="totals-row">
                <span class="t-label">Tax</span>
                <span class="t-value">Rs. <?php echo number_format($order['tax'], 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="totals-row totals-grand">
                <span class="t-label">Grand Total</span>
                <span class="t-value">Rs. <?php echo number_format($order['total'], 2); ?></span>
            </div>
            <?php if (($order['initial_payment'] ?? 0) > 0): ?>
            <div class="totals-row">
                <span class="t-label">Initial Payment</span>
                <span class="t-value" style="color:#059669;">- Rs. <?php echo number_format($order['initial_payment'], 2); ?></span>
            </div>
            <div class="totals-row" style="border-top:1px dashed #cbd5e1;margin-top:4px;padding-top:6px;">
                <span class="t-label" style="font-weight:800;color:#0f172a;">Balance Due</span>
                <span class="t-value" style="font-size:13px;font-weight:800;color:#dc2626;">Rs. <?php echo number_format($order['total'] - $order['initial_payment'], 2); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="sig-stamp">
        <div class="sig-stamp-inner">
            <?php
            $stampPath = dirname(__DIR__) . '/public/assets/images/malik-tuc-shop stamp.png';
            $stampUrl = '/public/assets/images/' . rawurlencode('malik-tuc-shop stamp.png');
            $signPath = dirname(__DIR__) . '/public/assets/images/muhammad-sign.png';
            $signUrl = '/public/assets/images/muhammad-sign.png';
            ?>
            <?php if (file_exists($stampPath)): ?>
                <img src="<?php echo $stampUrl; ?>" class="stamp-img" alt="Stamp">
            <?php endif; ?>
            <?php if (file_exists($signPath)): ?>
                <img src="<?php echo $signUrl; ?>" class="sign-img" alt="Signature">
            <?php endif; ?>
            <div class="sign-label">Authorized Signature &amp; Stamp</div>
        </div>
    </div>

    <?php if ($order['notes']): ?>
    <div class="notes">
        <div class="notes-label">Notes</div>
        <div class="notes-text"><?php echo htmlspecialchars($order['notes']); ?></div>
    </div>
    <?php endif; ?>

    <div class="footer">
        Thank you for your business &mdash; Malik Tuc Shop &middot; Contact: 0315-5318453
    </div>
</div>

<button class="print-btn" onclick="window.print(); setTimeout(function(){ window.close(); }, 500);">Print / Save PDF</button>

</body>
</html>
