var cart = [];
var lastOrderData = null;
var posInitialized = false;
var productCache = {};

function initPOS() {
  var el = document.getElementById("barcodeInput");
  if (!el) return;

  if (!posInitialized) {
    posInitialized = true;
    setupPOSListeners();
  }

  renderCart();
  recalculate();
}

function getCachedProduct(query) {
  if (productCache[query]) return productCache[query];
  return null;
}

function cacheProduct(query, product) {
  productCache[query] = product;
}

function playErrorBeep() {
  try {
    var ctx = new (window.AudioContext || window.webkitAudioContext)();
    var oscillator = ctx.createOscillator();
    var gainNode = ctx.createGain();
    oscillator.connect(gainNode);
    gainNode.connect(ctx.destination);
    oscillator.type = "square";
    oscillator.frequency.setValueAtTime(880, ctx.currentTime);
    gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
    oscillator.start(ctx.currentTime);
    oscillator.stop(ctx.currentTime + 0.3);
    setTimeout(function () {
      var osc2 = ctx.createOscillator();
      var gain2 = ctx.createGain();
      osc2.connect(gain2);
      gain2.connect(ctx.destination);
      osc2.type = "square";
      osc2.frequency.setValueAtTime(660, ctx.currentTime);
      gain2.gain.setValueAtTime(0.3, ctx.currentTime);
      gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
      osc2.start(ctx.currentTime);
      osc2.stop(ctx.currentTime + 0.3);
    }, 150);
  } catch (e) {}
}

function showProductErrorModal(message) {
  var modal = document.getElementById("productErrorModal");
  var msgEl = document.getElementById("productErrorMessage");
  if (!modal) return;
  if (msgEl)
    msgEl.textContent =
      message ||
      "This product is not in the database or failed to fetch the product.";
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  playErrorBeep();
}

function closeProductErrorModal() {
  var modal = document.getElementById("productErrorModal");
  if (!modal) return;
  modal.classList.add("hidden");
  modal.classList.remove("flex");
  var inp = document.getElementById("barcodeInput");
  if (inp) {
    inp.value = "";
    inp.focus();
  }
}

function setupPOSListeners() {
  var barcodeInput = document.getElementById("barcodeInput");

  barcodeInput.onkeydown = function (e) {
    if (e.key === "Enter") {
      e.preventDefault();
      var query = this.value.trim();
      if (!query) return;

      var self = this;

      var cached = getCachedProduct(query);
      if (cached) {
        addProductToCart(cached);
        self.value = "";
        return;
      }

      fetch("/api/products.php?barcode=" + encodeURIComponent(query), {
        credentials: "same-origin",
      })
        .then(function (r) {
          return r.json();
        })
        .then(function (data) {
          if (data.success && data.data.length > 0) {
            var product = data.data[0];
            cacheProduct(query, product);
            addProductToCart(product);
          } else {
            showProductErrorModal(
              "This product is not in the database or failed to fetch the product.",
            );
          }
        })
        .catch(function () {
          showProductErrorModal(
            "This product is not in the database or failed to fetch the product.",
          );
        })
        .finally(function () {
          self.value = "";
        });
    }
  };

  var paymentMethod = document.getElementById("paymentMethod");
  if (paymentMethod) {
    paymentMethod.onchange = function () {
      var section = document.getElementById("amountPaidSection");
      if (section)
        section.style.display = this.value === "credit" ? "none" : "block";
    };
  }
}

function addProductToCart(product) {
  var existing = null;
  for (var i = 0; i < cart.length; i++) {
    if (cart[i].product_id == product.id) {
      existing = cart[i];
      break;
    }
  }
  if (existing) {
    if (existing.quantity < product.stock) {
      existing.quantity++;
    } else {
      showToast("No more stock available");
      return;
    }
  } else {
    if (product.stock <= 0) {
      showToast("Product out of stock");
      return;
    }
    cart.push({
      product_id: product.id,
      product_name: product.name,
      sku: product.sku || "",
      barcode: product.barcode || "",
      purchase_price: parseFloat(product.purchase_price) || 0,
      selling_price: parseFloat(product.selling_price) || 0,
      quantity: 1,
      tax: 0,
      discount: 0,
      max_stock: product.stock,
    });
  }
  renderCart();
  recalculate();
  var inp = document.getElementById("barcodeInput");
  if (inp) {
    inp.value = "";
    inp.focus();
  }
}

function renderCart() {
  var tbody = document.getElementById("cartItems");
  var empty = document.getElementById("emptyCart");
  if (!tbody) return;
  if (cart.length === 0) {
    tbody.innerHTML = "";
    if (empty) empty.classList.remove("hidden");
    return;
  }
  if (empty) empty.classList.add("hidden");
  var html = "";
  for (var i = 0; i < cart.length; i++) {
    var item = cart[i];
    var rt = calcRowTotal(item);
    html += '<tr class="hover:bg-gray-50">';
    html += '<td class="px-4 py-3 text-sm text-gray-500">' + (i + 1) + "</td>";
    html +=
      '<td class="px-4 py-3"><p class="text-sm font-medium text-gray-900">' +
      escHtml(item.product_name) +
      "</p>";
    html +=
      '<p class="text-xs text-gray-400">' +
      escHtml(item.sku || item.barcode || "") +
      "</p></td>";
    html +=
      '<td class="px-4 py-3"><input type="number" value="' +
      item.selling_price +
      '" min="0" step="0.01" class="w-24 text-right px-2 py-1 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="updateItem(' +
      i +
      ", 'selling_price', this.value)\"></td>";
    html +=
      '<td class="px-4 py-3"><div class="flex items-center justify-center gap-1">';
    html +=
      '<button onclick="changeQty(' +
      i +
      ', -1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm flex items-center justify-center">-</button>';
    html +=
      '<input type="number" value="' +
      item.quantity +
      '" min="1" max="' +
      item.max_stock +
      '" class="w-14 text-center px-1 py-1 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="updateItem(' +
      i +
      ", 'quantity', this.value)\">";
    html +=
      '<button onclick="changeQty(' +
      i +
      ', 1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm flex items-center justify-center">+</button>';
    html += "</div></td>";
    html +=
      '<td class="px-4 py-3"><input type="number" value="' +
      item.tax +
      '" min="0" max="100" step="0.01" class="w-16 text-right px-2 py-1 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="updateItem(' +
      i +
      ", 'tax', this.value)\"></td>";
    html +=
      '<td class="px-4 py-3"><input type="number" value="' +
      item.discount +
      '" min="0" step="0.01" class="w-20 text-right px-2 py-1 border border-gray-200 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500" onchange="updateItem(' +
      i +
      ", 'discount', this.value)\"></td>";
    html +=
      '<td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">Rs. ' +
      rt.toFixed(2) +
      "</td>";
    html +=
      '<td class="px-4 py-3"><button onclick="removeItem(' +
      i +
      ')" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></td>';
    html += "</tr>";
  }
  tbody.innerHTML = html;
}

function calcRowTotal(item) {
  var lineTotal = item.selling_price * item.quantity;
  var taxAmount = lineTotal * (item.tax / 100);
  return lineTotal + taxAmount - item.discount;
}

function updateItem(index, field, value) {
  cart[index][field] = parseFloat(value) || 0;
  if (field === "quantity")
    cart[index].quantity = Math.max(
      1,
      Math.min(cart[index].quantity, cart[index].max_stock),
    );
  renderCart();
  recalculate();
}

function changeQty(index, delta) {
  var newQty = cart[index].quantity + delta;
  if (newQty >= 1 && newQty <= cart[index].max_stock) {
    cart[index].quantity = newQty;
    renderCart();
    recalculate();
  }
}

function removeItem(index) {
  cart.splice(index, 1);
  renderCart();
  recalculate();
}

function clearCart() {
  if (cart.length === 0) return;
  if (!confirm("Clear all items?")) return;
  cart = [];
  renderCart();
  recalculate();
}

function recalculate() {
  var subtotal = 0,
    totalItemTax = 0,
    totalItemDiscount = 0;
  for (var i = 0; i < cart.length; i++) {
    var item = cart[i];
    var lineTotal = item.selling_price * item.quantity;
    subtotal += lineTotal;
    totalItemTax += lineTotal * (item.tax / 100);
    totalItemDiscount += item.discount;
  }
  var gdEl = document.getElementById("globalDiscount");
  var gtEl = document.getElementById("globalTax");
  var apEl = document.getElementById("amountPaid");
  if (!gdEl || !gtEl || !apEl) return;

  var globalDiscount = parseFloat(gdEl.value) || 0;
  var globalTaxRate = parseFloat(gtEl.value) || 0;
  var taxableAmount = subtotal - totalItemDiscount - globalDiscount;
  var globalTax = taxableAmount * (globalTaxRate / 100);
  var grandTotal = taxableAmount + totalItemTax + globalTax;
  var amountPaid = parseFloat(apEl.value) || 0;
  var change = amountPaid - grandTotal;

  document.getElementById("subtotalDisplay").textContent =
    "Rs. " + subtotal.toFixed(2);
  document.getElementById("discountDisplay").textContent =
    "- Rs. " + (totalItemDiscount + globalDiscount).toFixed(2);
  document.getElementById("taxDisplay").textContent =
    "Rs. " + (totalItemTax + globalTax).toFixed(2);
  document.getElementById("grandTotalDisplay").textContent =
    "Rs. " + grandTotal.toFixed(2);

  var changeRow = document.getElementById("changeRow");
  if (change > 0) {
    document.getElementById("changeDisplay").textContent =
      "Rs. " + change.toFixed(2);
    changeRow.style.display = "flex";
  } else {
    changeRow.style.display = "none";
  }
}

function completeOrder() {
  if (cart.length === 0) {
    showToast("Cart is empty");
    return;
  }
  var btn = document.getElementById("completeBtn");
  btn.disabled = true;
  btn.textContent = "Processing...";

  var globalDiscount =
    parseFloat(document.getElementById("globalDiscount").value) || 0;
  var globalTaxRate =
    parseFloat(document.getElementById("globalTax").value) || 0;
  var subtotal = 0,
    totalItemTax = 0,
    totalItemDiscount = 0;
  for (var i = 0; i < cart.length; i++) {
    var item = cart[i];
    var lineTotal = item.selling_price * item.quantity;
    subtotal += lineTotal;
    totalItemTax += lineTotal * (item.tax / 100);
    totalItemDiscount += item.discount;
  }
  var taxableAmount = subtotal - totalItemDiscount - globalDiscount;
  var globalTax = taxableAmount * (globalTaxRate / 100);
  var grandTotal = taxableAmount + totalItemTax + globalTax;
  var amountPaid = parseFloat(document.getElementById("amountPaid").value) || 0;

  lastOrderData = {
    order_number: "",
    date: new Date().toLocaleString(),
    subtotal: subtotal,
    discount: totalItemDiscount + globalDiscount,
    tax: totalItemTax + globalTax,
    total: grandTotal,
    amount_paid: amountPaid,
    change: Math.max(0, amountPaid - grandTotal),
    payment_method: document.getElementById("paymentMethod").value,
    cashier: typeof cashierName !== "undefined" ? cashierName : "Admin",
    items: [],
  };
  for (var j = 0; j < cart.length; j++) {
    lastOrderData.items.push({
      barcode: cart[j].barcode,
      product_name: cart[j].product_name,
      selling_price: cart[j].selling_price,
      quantity: cart[j].quantity,
      tax: cart[j].tax,
      discount: cart[j].discount,
      total: calcRowTotal(cart[j]),
    });
  }

  var orderData = {
    cashier_name: typeof cashierName !== "undefined" ? cashierName : "Admin",
    subtotal: subtotal,
    discount: totalItemDiscount + globalDiscount,
    tax: totalItemTax + globalTax,
    total: grandTotal,
    payment_method: lastOrderData.payment_method,
    amount_paid: amountPaid,
    change_amount: lastOrderData.change,
    status: "completed",
    notes: document.getElementById("orderNotes").value || null,
    items: [],
  };
  for (var k = 0; k < cart.length; k++) {
    orderData.items.push({
      product_id: cart[k].product_id,
      product_name: cart[k].product_name,
      sku: cart[k].sku,
      barcode: cart[k].barcode,
      quantity: cart[k].quantity,
      selling_price: cart[k].selling_price,
      purchase_price: cart[k].purchase_price,
      tax: cart[k].tax,
      discount: cart[k].discount,
      total: calcRowTotal(cart[k]),
    });
  }

  fetch("/api/pos-sales.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(orderData),
  })
    .then(function (r) {
      return r.json();
    })
    .then(function (result) {
      if (result.success) {
        lastOrderData.order_number = result.sale_number;
        document.getElementById("printOrderNumber").textContent =
          "Sale #" + result.sale_number;
        document.getElementById("printModal").classList.remove("hidden");
        document.getElementById("printModal").classList.add("flex");
        cart = [];
        renderCart();
        recalculate();
        document.getElementById("orderNotes").value = "";
        document.getElementById("amountPaid").value = "0";
        document.getElementById("globalDiscount").value = "0";
        document.getElementById("globalTax").value = "0";
      } else {
        showToast(result.message || "Failed to create order");
      }
    })
    .catch(function () {
      showToast("Network error");
    })
    .finally(function () {
      btn.disabled = false;
      btn.textContent = "Save Sale";
    });
}

function closePrintModal() {
  document.getElementById("printModal").classList.add("hidden");
  document.getElementById("printModal").classList.remove("flex");
  lastOrderData = null;
}

function printReceipt() {
  if (!lastOrderData) return;
  var d = lastOrderData;
  var itemsHtml = "";
  for (var i = 0; i < d.items.length; i++) {
    var item = d.items[i];
    var itemLines =
      '<div class="item-row">' +
      '<div class="item-left">' +
      '<div class="item-name">' +
      escHtml(item.product_name) +
      "</div>" +
      '<div class="item-qty-price">' +
      item.quantity +
      " x Rs. " +
      item.selling_price.toFixed(2) +
      "</div>" +
      "</div>" +
      '<div class="item-right">Rs. ' +
      item.total.toFixed(2) +
      "</div>" +
      "</div>";
    if (item.discount > 0 || item.tax > 0) {
      var details = [];
      if (item.discount > 0)
        details.push("Disc: -Rs." + item.discount.toFixed(2));
      if (item.tax > 0) details.push("Tax: " + item.tax + "%");
      itemLines += '<div class="item-sub">' + details.join(" | ") + "</div>";
    }
    itemsHtml += itemLines;
  }

  var paidSection = "";
  if (d.amount_paid > 0) {
    paidSection =
      '<div class="summary-row"><span>Amount Paid</span><span>Rs. ' +
      d.amount_paid.toFixed(2) +
      "</span></div>" +
      '<div class="summary-row highlight-green"><span>Change</span><span>Rs. ' +
      d.change.toFixed(2) +
      "</span></div>";
  }

  var receiptHtml =
    '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>' +
    "@page { margin: 0 !important; size: 80mm auto; }" +
    "@media print { html, body { margin: 0 !important; padding: 0 !important; width: 80mm !important; overflow: hidden !important; } }" +
    "* { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }" +
    'html, body { width: 80mm; font-family: Calibri, sans-serif; font-size: 14px; color: #000; background: #fff; font-weight: 700; }' +
    "@media print { " +
    "  body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }" +
    "  .receipt { filter: contrast(3) brightness(0.5) !important; }" +
    "}" +
    ".receipt { width: 80mm; padding: 4mm 3mm; filter: contrast(3) brightness(0.5); }" +
    ".center { text-align: center; }" +
    ".logo { width: 38mm; margin: 0 auto 2mm; display: block; }" +
    ".store-name { font-size: 20px; font-weight: 900; letter-spacing: 1.5px; margin-bottom: 1mm; color: #000; }" +
    ".store-tagline { font-size: 11px; color: #000; letter-spacing: 0.5px; margin-bottom: 2mm; font-weight: 900; }" +
    ".store-contact { font-size: 10px; color: #000; margin-bottom: 1mm; font-weight: 900; }" +
    ".divider { border-top: 2px dashed #000; margin: 2mm 0; }" +
    ".divider-solid { border-top: 2px solid #000; margin: 2mm 0; }" +
    ".divider-double { border-top: 3px double #000; margin: 2mm 0; }" +
    ".info-grid { margin: 1.5mm 0; }" +
    ".info-row { display: flex; justify-content: space-between; font-size: 12px; line-height: 1.6; }" +
    ".info-row .label { color: #000; font-weight: 900; }" +
    ".info-row .value { font-weight: 900; color: #000; }" +
    ".section-title { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 1px; color: #000; margin: 2mm 0 1mm; }" +
    ".items-header { display: flex; justify-content: space-between; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; padding: 1mm 0; border-top: 2px solid #000; border-bottom: 2px solid #000; margin-bottom: 1mm; }" +
    ".items-header span:first-child { flex: 1; }" +
    ".items-header span:last-child { text-align: right; width: 28mm; }" +
    ".item-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 0.8mm 0; line-height: 1.3; }" +
    ".item-left { flex: 1; padding-right: 2mm; }" +
    ".item-right { text-align: right; font-weight: 900; white-space: nowrap; color: #000; }" +
    ".item-name { font-size: 13px; font-weight: 900; margin-bottom: 0.3mm; word-break: break-word; color: #000; }" +
    ".item-qty-price { font-size: 11px; color: #000; font-weight: 900; }" +
    ".item-sub { font-size: 10px; color: #000; padding-left: 1mm; margin-top: 0.3mm; font-weight: 900; }" +
    ".summary { margin: 2mm 0; }" +
    ".summary-row { display: flex; justify-content: space-between; font-size: 12px; padding: 0.6mm 0; }" +
    ".summary-row .s-label { color: #000; font-weight: 900; }" +
    ".summary-row .s-value { font-weight: 900; color: #000; }" +
    ".summary-row.discount .s-value { color: #000; font-weight: 900; }" +
    ".summary-total { display: flex; justify-content: space-between; font-size: 18px; font-weight: 900; padding: 1.5mm 0; border-top: 3px solid #000; border-bottom: 3px solid #000; margin: 1.5mm 0; letter-spacing: 0.5px; color: #000; }" +
    ".summary-row.highlight-green .s-value { color: #000; font-weight: 900; }" +
    ".payment-badge { display: inline-block; padding: 0.5mm 2mm; background: #000; color: #fff; border-radius: 2mm; font-size: 11px; font-weight: 900; letter-spacing: 0.5px; margin-top: 1mm; }" +
    ".thankyou { font-size: 13px; font-weight: 900; text-align: center; margin: 3mm 0 1mm; letter-spacing: 0.5px; color: #000; }" +
    ".footer-text { font-size: 10px; color: #000; text-align: center; line-height: 1.4; margin: 0.5mm 0; font-weight: 900; }" +
    ".qr-placeholder { text-align: center; margin: 2mm 0; font-size: 7px; color: #000; }" +
    '</style></head><body><div class="receipt">' +
    '<div class="center">' +
    '<img src="/public/assets/images/malik-tuc-shop.png" class="logo" alt="Logo">' +
    '<div class="store-name">MALIK TUC SHOP</div>' +
    '<div class="store-tagline">Best Quality, Best Prices</div>' +
    '<div class="store-contact">Contact: 0315-5318453</div>' +
    "</div>" +
    '<div class="divider-double"></div>' +
    '<div class="info-grid">' +
    '<div class="info-row"><span class="label">Receipt #</span><span class="value">' +
    escHtml(d.order_number) +
    "</span></div>" +
    '<div class="info-row"><span class="label">Date</span><span class="value">' +
    escHtml(d.date) +
    "</span></div>" +
    '<div class="info-row"><span class="label">Cashier</span><span class="value">' +
    escHtml(d.cashier) +
    "</span></div>" +
    '<div class="info-row"><span class="label">Payment</span><span class="value"><span class="payment-badge">' +
    escHtml(d.payment_method.toUpperCase()) +
    "</span></span></div>" +
    "</div>" +
    '<div class="divider"></div>' +
    '<div class="items-header"><span>Item</span><span style="text-align:right">Total</span></div>' +
    itemsHtml +
    '<div class="divider-double"></div>' +
    '<div class="summary">' +
    '<div class="summary-row"><span class="s-label">Subtotal</span><span class="s-value">Rs. ' +
    d.subtotal.toFixed(2) +
    "</span></div>" +
    (d.discount > 0
      ? '<div class="summary-row discount"><span class="s-label">Discount</span><span class="s-value">- Rs. ' +
        d.discount.toFixed(2) +
        "</span></div>"
      : "") +
    (d.tax > 0
      ? '<div class="summary-row"><span class="s-label">Tax</span><span class="s-value">Rs. ' +
        d.tax.toFixed(2) +
        "</span></div>"
      : "") +
    '<div class="summary-total"><span>TOTAL</span><span>Rs. ' +
    d.total.toFixed(2) +
    "</span></div>" +
    paidSection +
    "</div>" +
    '<div class="divider-double"></div>' +
    '<div class="thankyou">Thank You for Shopping!</div>' +
    '<div class="footer-text">Visit us again</div>' +
    '<div class="footer-text">Malik Tuc Shop &mdash; Quality You Can Trust</div>' +
    "</div>" +
    "<script>" +
    "window.onload = function() {" +
    '  var el = document.querySelector(".receipt");' +
    "  var h = Math.ceil(el.getBoundingClientRect().height) + 10;" +
    '  var s = document.createElement("style");' +
    '  s.textContent = "@page { size: 80mm " + h + "px; margin: 0 !important; }";' +
    "  document.head.appendChild(s);" +
    "  setTimeout(function() { window.print(); }, 300);" +
    "};" +
    "<\/script>" +
    "</body></html>";

  var win = window.open("", "_blank", "width=400,height=600");
  win.document.write(receiptHtml);
  win.document.close();
  closePrintModal();
}

function showToast(message) {
  var toast = document.getElementById("toast");
  var msg = document.getElementById("toastMessage");
  if (!toast || !msg) return;
  msg.textContent = message;
  toast.classList.remove("hidden");
  toast.classList.add("flex");
  setTimeout(function () {
    toast.classList.add("hidden");
    toast.classList.remove("flex");
  }, 3000);
}

function escHtml(str) {
  if (str === null || str === undefined) return "";
  var div = document.createElement("div");
  div.textContent = String(str);
  return div.innerHTML;
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initPOS);
} else {
  initPOS();
}
