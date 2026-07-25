<?php
class Order
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(array $filters = []): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $search = $this->db->quote('%' . $filters['search'] . '%');
            $where[] = "(o.order_number LIKE {$search})";
        }

        if (!empty($filters['status'])) {
            $status = $this->db->quote($filters['status']);
            $where[] = "o.status = {$status}";
        }

        if (!empty($filters['order_type'])) {
            $orderType = $this->db->quote($filters['order_type']);
            $where[] = "o.order_type = {$orderType}";
        }

        if (!empty($filters['date_from'])) {
            $dateFrom = $this->db->quote($filters['date_from']);
            $where[] = "DateValue(o.created_at) >= {$dateFrom}";
        }

        if (!empty($filters['date_to'])) {
            $dateTo = $this->db->quote($filters['date_to']);
            $where[] = "DateValue(o.created_at) <= {$dateTo}";
        }

        $sql = "SELECT o.*, u.name as user_name, s.name as supplier_name
                FROM (orders o
                LEFT JOIN users u ON o.user_id = u.id)
                LEFT JOIN suppliers s ON o.supplier_id = s.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY o.id DESC";

        $rs = $this->db->getConnection()->Execute($sql);
        $results = [];
        if ($rs && !$rs->EOF) {
            while (!$rs->EOF) {
                $row = [];
                for ($i = 0; $i < $rs->Fields->Count(); $i++) {
                    $field = $rs->Fields($i);
                    $row[$field->Name] = $this->db->castVariant($field->Value);
                }
                $results[] = $row;
                $rs->MoveNext();
            }
        }
        return $results;
    }

    public function find(int $id): ?array
    {
        $sql = "SELECT o.*, u.name as user_name, s.name as supplier_name, s.phone as supplier_phone, s.email as supplier_email, s.address as supplier_address
                FROM (orders o
                LEFT JOIN users u ON o.user_id = u.id)
                LEFT JOIN suppliers s ON o.supplier_id = s.id
                WHERE o.id = {$id}";

        $order = $this->db->queryOne($sql);

        if ($order) {
            $order['items'] = $this->db->query(
                "SELECT oi.*, p.sku, p.barcode, p.unit as product_unit
                 FROM order_items oi
                 LEFT JOIN products p ON p.id = oi.product_id
                 WHERE oi.order_id = {$id}"
            );
        }

        return $order;
    }

    public function create(array $data): array
    {
        $this->db->beginTransaction();

        try {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO orders ([order_number], [order_type], [user_id], [subtotal], [discount], [tax], [total], [payment_method], [amount_paid], [change_amount], [status], [notes])
                    VALUES ({$this->db->quote($orderNumber)}, 'sale', " . (int)$data['user_id'] . ", " . (float)($data['subtotal'] ?? 0) . ", " . (float)($data['discount'] ?? 0) . ", " . (float)($data['tax'] ?? 0) . ", " . (float)($data['total'] ?? 0) . ", {$this->db->quote($data['payment_method'] ?? 'cash')}, " . (float)($data['amount_paid'] ?? 0) . ", " . (float)($data['change_amount'] ?? 0) . ", {$this->db->quote($data['status'] ?? 'completed')}, " . (!empty($data['notes']) ? $this->db->quote($data['notes']) : 'NULL') . ")";

            $this->db->execute($sql);
            $orderId = $this->db->lastInsertId();

            foreach ($data['items'] as $item) {
                $productName = $this->db->quote($item['product_name']);
                $total = (float)($item['total'] ?? ($item['quantity'] * $item['unit_price']));
                $purchasePrice = (float)($item['purchase_price'] ?? 0);

                $itemSql = "INSERT INTO order_items ([order_id], [product_id], [product_name], [quantity], [unit_price], [purchase_price], [total])
                            VALUES ({$orderId}, " . (int)$item['product_id'] . ", {$productName}, " . (int)$item['quantity'] . ", " . (float)$item['unit_price'] . ", {$purchasePrice}, {$total})";
                $this->db->execute($itemSql);

                // Decrement stock
                $this->db->execute("UPDATE products SET stock = stock - " . (int)$item['quantity'] . " WHERE id = " . (int)$item['product_id'] . " AND stock >= " . (int)$item['quantity']);
            }

            $this->db->commit();

            return ['success' => true, 'message' => 'Order created', 'id' => $orderId, 'order_number' => $orderNumber];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create order: ' . $e->getMessage()];
        }
    }

    public function createPurchase(array $data): array
    {
        $this->db->beginTransaction();

        try {
            $orderNumber = 'PO-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $hasInitialPayment = $this->db->hasColumn('orders', 'initial_payment');

            $supplierId = (int)$data['supplier_id'];
            $userId = (int)$data['user_id'];
            $subtotal = (float)($data['subtotal'] ?? 0);
            $discount = (float)($data['discount'] ?? 0);
            $tax = (float)($data['tax'] ?? 0);
            $total = (float)($data['total'] ?? 0);
            $paymentMethod = $this->db->quote($data['payment_method'] ?? 'credit');
            $status = $this->db->quote($data['status'] ?? 'pending');
            $notes = !empty($data['notes']) ? $this->db->quote($data['notes']) : 'NULL';
            $expectedDate = !empty($data['expected_date']) ? $this->db->quote($data['expected_date']) : 'NULL';

            if ($hasInitialPayment) {
                $initialPayment = (float)($data['initial_payment'] ?? 0);
                $sql = "INSERT INTO orders ([order_number], [order_type], [user_id], [supplier_id], [subtotal], [discount], [tax], [total], [initial_payment], [payment_method], [status], [notes], [expected_date])
                        VALUES ({$this->db->quote($orderNumber)}, 'purchase', {$userId}, {$supplierId}, {$subtotal}, {$discount}, {$tax}, {$total}, {$initialPayment}, {$paymentMethod}, {$status}, {$notes}, {$expectedDate})";
            } else {
                $sql = "INSERT INTO orders ([order_number], [order_type], [user_id], [supplier_id], [subtotal], [discount], [tax], [total], [payment_method], [status], [notes], [expected_date])
                        VALUES ({$this->db->quote($orderNumber)}, 'purchase', {$userId}, {$supplierId}, {$subtotal}, {$discount}, {$tax}, {$total}, {$paymentMethod}, {$status}, {$notes}, {$expectedDate})";
            }

            $this->db->execute($sql);
            $orderId = $this->db->lastInsertId();

            foreach ($data['items'] as $item) {
                $productId = !empty($item['product_id']) ? (int)$item['product_id'] : 0;
                $productName = $this->db->quote($item['product_name'] ?? 'Unknown Product');

                if (!$productId) {
                    $this->db->execute("INSERT INTO products ([name], [purchase_price], [selling_price], [stock], [unit]) VALUES ({$productName}, " . (float)($item['unit_price'] ?? 0) . ", " . (float)($item['unit_price'] ?? 0) . ", 0, 'pcs')");
                    $productId = $this->db->lastInsertId();
                }

                $total = (float)($item['total'] ?? ($item['quantity'] * ($item['unit_price'] ?? 0)));
                $purchasePrice = (float)($item['purchase_price'] ?? $item['unit_price'] ?? 0);

                $this->db->execute("INSERT INTO order_items ([order_id], [product_id], [product_name], [quantity], [unit_price], [purchase_price], [total])
                                    VALUES ({$orderId}, {$productId}, {$productName}, " . (int)$item['quantity'] . ", " . (float)($item['unit_price'] ?? 0) . ", {$purchasePrice}, {$total})");
            }

            $this->db->commit();

            return ['success' => true, 'message' => 'Purchase order created', 'id' => $orderId, 'order_number' => $orderNumber];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create purchase order: ' . $e->getMessage()];
        }
    }

    public function receive(int $id, array $data = []): array
    {
        $order = $this->find($id);

        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        if ($order['order_type'] !== 'purchase') {
            return ['success' => false, 'message' => 'Not a purchase order'];
        }

        if ($order['status'] === 'completed') {
            return ['success' => false, 'message' => 'Order already received'];
        }

        $initialPayment = isset($data['initial_payment']) ? (float)$data['initial_payment'] : (float)($order['initial_payment'] ?? 0);
        $paymentNow = isset($data['payment_now']) ? (float)$data['payment_now'] : 0;
        $totalPaid = $initialPayment + $paymentNow;
        $total = (float)$order['total'];
        $newInitialPayment = min($totalPaid, $total);

        $this->db->beginTransaction();

        try {
            $this->db->execute("UPDATE orders SET status = 'completed', initial_payment = {$newInitialPayment} WHERE id = {$id}");

            foreach ($order['items'] as $item) {
                $this->db->execute("UPDATE products SET stock = stock + " . (int)$item['quantity'] . " WHERE id = " . (int)$item['product_id']);
            }

            $this->db->commit();

            $balance = $total - $newInitialPayment;
            $msg = 'Order received. Stock updated.';
            if ($newInitialPayment > 0) {
                $msg .= ' Paid: Rs. ' . number_format($newInitialPayment, 2);
            }
            if ($balance > 0) {
                $msg .= ' Balance due: Rs. ' . number_format($balance, 2);
            }

            return ['success' => true, 'message' => $msg, 'balance' => $balance, 'initial_payment' => $newInitialPayment];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to receive order: ' . $e->getMessage()];
        }
    }

    public function count(): int
    {
        return (int)$this->db->queryScalar("SELECT COUNT(*) FROM orders");
    }

    public function todaySales(): float
    {
        return (float)$this->db->queryScalar("SELECT IIF(SUM(total) IS NULL, 0, SUM(total)) FROM orders WHERE DateValue(created_at) = Date() AND status = 'completed' AND order_type = 'sale'");
    }

    public function todayCount(): int
    {
        return (int)$this->db->queryScalar("SELECT COUNT(*) FROM orders WHERE DateValue(created_at) = Date() AND order_type = 'sale'");
    }

    public function reportAll(array $filters = []): array
    {
        $where = ["o.status = 'completed'", "o.order_type = 'sale'"];

        if (!empty($filters['search'])) {
            $search = $this->db->quote('%' . $filters['search'] . '%');
            $where[] = "(o.order_number LIKE {$search})";
        }
        if (!empty($filters['date_from'])) {
            $dateFrom = $this->db->quote($filters['date_from']);
            $where[] = "DateValue(o.created_at) >= {$dateFrom}";
        }
        if (!empty($filters['date_to'])) {
            $dateTo = $this->db->quote($filters['date_to']);
            $where[] = "DateValue(o.created_at) <= {$dateTo}";
        }

        $sql = "SELECT o.id, o.order_number, o.total, o.discount, o.tax, o.created_at, u.name as user_name,
                IIF(SUM(oi.quantity * oi.unit_price) IS NULL, 0, SUM(oi.quantity * oi.unit_price)) as sale_total,
                IIF(SUM((oi.unit_price - IIF(oi.purchase_price IS NULL OR oi.purchase_price = 0, p.purchase_price, oi.purchase_price)) * oi.quantity) IS NULL, 0, SUM((oi.unit_price - IIF(oi.purchase_price IS NULL OR oi.purchase_price = 0, p.purchase_price, oi.purchase_price)) * oi.quantity)) as profit
                FROM ((orders o
                LEFT JOIN users u ON o.user_id = u.id)
                LEFT JOIN order_items oi ON oi.order_id = o.id)
                LEFT JOIN products p ON p.id = oi.product_id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY o.id, o.order_number, o.total, o.discount, o.tax, o.created_at, u.name
                ORDER BY o.id DESC";

        $rs = $this->db->getConnection()->Execute($sql);
        $results = [];
        if ($rs && !$rs->EOF) {
            while (!$rs->EOF) {
                $row = [];
                for ($i = 0; $i < $rs->Fields->Count(); $i++) {
                    $field = $rs->Fields($i);
                    $row[$field->Name] = $this->db->castVariant($field->Value);
                }
                $results[] = $row;
                $rs->MoveNext();
            }
        }
        return $results;
    }

    public function reportItems(int $orderId): array
    {
        $sql = "SELECT oi.*, IIF(oi.purchase_price IS NULL OR oi.purchase_price = 0, p.purchase_price, oi.purchase_price) as purchase_price,
                       (oi.unit_price - IIF(oi.purchase_price IS NULL OR oi.purchase_price = 0, p.purchase_price, oi.purchase_price)) * oi.quantity as item_profit
                FROM order_items oi
                LEFT JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = {$orderId}";

        return $this->db->query($sql);
    }
}
