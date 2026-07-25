<?php
class PosSale
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create(array $data): array
    {
        $this->db->beginTransaction();

        try {
            $saleNumber = 'POS-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $sql = "INSERT INTO pos_sales ([sale_number], [order_id], [cashier_name], [subtotal], [discount], [tax], [total], [payment_method], [amount_paid], [change_amount], [status], [notes])
                    VALUES ({$this->db->quote($saleNumber)}, " . (!empty($data['order_id']) ? (int)$data['order_id'] : 'NULL') . ", {$this->db->quote($data['cashier_name'] ?? 'Admin')}, " . (float)($data['subtotal'] ?? 0) . ", " . (float)($data['discount'] ?? 0) . ", " . (float)($data['tax'] ?? 0) . ", " . (float)($data['total'] ?? 0) . ", {$this->db->quote($data['payment_method'] ?? 'cash')}, " . (float)($data['amount_paid'] ?? 0) . ", " . (float)($data['change_amount'] ?? 0) . ", {$this->db->quote($data['status'] ?? 'completed')}, " . (!empty($data['notes']) ? $this->db->quote($data['notes']) : 'NULL') . ")";

            $this->db->execute($sql);
            $posSaleId = $this->db->lastInsertId();

            foreach ($data['items'] as $item) {
                $productName = $this->db->quote($item['product_name'] ?? 'Unknown');
                $sku = !empty($item['sku']) ? $this->db->quote($item['sku']) : 'NULL';
                $barcode = !empty($item['barcode']) ? $this->db->quote($item['barcode']) : 'NULL';
                $productId = !empty($item['product_id']) ? (int)$item['product_id'] : 'NULL';
                $total = (float)($item['total'] ?? 0);

                $this->db->execute("INSERT INTO pos_sale_items ([pos_sale_id], [product_id], [product_name], [sku], [barcode], [purchase_price], [selling_price], [quantity], [tax_percent], [discount], [total])
                                    VALUES ({$posSaleId}, {$productId}, {$productName}, {$sku}, {$barcode}, " . (float)($item['purchase_price'] ?? 0) . ", " . (float)($item['selling_price'] ?? 0) . ", " . (int)($item['quantity'] ?? 1) . ", " . (float)($item['tax'] ?? 0) . ", " . (float)($item['discount'] ?? 0) . ", {$total})");

                if (!empty($item['product_id']) && ($item['quantity'] ?? 1) > 0) {
                    $this->db->execute("UPDATE products SET stock = stock - " . (int)$item['quantity'] . " WHERE id = " . (int)$item['product_id'] . " AND stock >= " . (int)$item['quantity']);
                }
            }

            $this->db->commit();

            return ['success' => true, 'message' => 'Sale completed', 'id' => $posSaleId, 'sale_number' => $saleNumber];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create sale: ' . $e->getMessage()];
        }
    }

    public function all(array $filters = []): array
    {
        $where = ['1=1'];

        if (!empty($filters['search'])) {
            $search = $this->db->quote('%' . $filters['search'] . '%');
            $where[] = "(s.sale_number LIKE {$search})";
        }

        if (!empty($filters['status'])) {
            $status = $this->db->quote($filters['status']);
            $where[] = "s.status = {$status}";
        }

        if (!empty($filters['date_from'])) {
            $dateFrom = $this->db->quote($filters['date_from']);
            $where[] = "DateValue(s.created_at) >= {$dateFrom}";
        }

        if (!empty($filters['date_to'])) {
            $dateTo = $this->db->quote($filters['date_to']);
            $where[] = "DateValue(s.created_at) <= {$dateTo}";
        }

        if (!empty($filters['cashier'])) {
            $cashier = $this->db->quote($filters['cashier']);
            $where[] = "s.cashier_name = {$cashier}";
        }

        $sql = "SELECT s.*
                FROM pos_sales s
                WHERE " . implode(' AND ', $where) . "
                ORDER BY s.id DESC";

        return $this->db->query($sql);
    }

    public function find(int $id): ?array
    {
        $sale = $this->db->queryOne("SELECT * FROM pos_sales WHERE id = {$id}");

        if ($sale) {
            $sale['items'] = $this->db->query("SELECT * FROM pos_sale_items WHERE pos_sale_id = {$id}");
        }

        return $sale;
    }

    public function reportAll(array $filters = []): array
    {
        $where = ["s.status = 'completed'"];

        if (!empty($filters['search'])) {
            $search = $this->db->quote('%' . $filters['search'] . '%');
            $where[] = "(s.sale_number LIKE {$search})";
        }
        if (!empty($filters['date_from'])) {
            $dateFrom = $this->db->quote($filters['date_from']);
            $where[] = "DateValue(s.created_at) >= {$dateFrom}";
        }
        if (!empty($filters['date_to'])) {
            $dateTo = $this->db->quote($filters['date_to']);
            $where[] = "DateValue(s.created_at) <= {$dateTo}";
        }
        if (!empty($filters['cashier'])) {
            $cashier = $this->db->quote($filters['cashier']);
            $where[] = "s.cashier_name = {$cashier}";
        }

        $sql = "SELECT s.id, s.sale_number, s.cashier_name, s.total, s.discount, s.tax, s.payment_method, s.amount_paid, s.change_amount, s.created_at,
                IIF(SUM(si.selling_price * si.quantity) IS NULL, 0, SUM(si.selling_price * si.quantity)) as sale_total,
                IIF(SUM((si.selling_price - si.purchase_price) * si.quantity) IS NULL, 0, SUM((si.selling_price - si.purchase_price) * si.quantity)) as profit
                FROM pos_sales s
                LEFT JOIN pos_sale_items si ON si.pos_sale_id = s.id
                WHERE " . implode(' AND ', $where) . "
                GROUP BY s.id, s.sale_number, s.cashier_name, s.total, s.discount, s.tax, s.payment_method, s.amount_paid, s.change_amount, s.created_at
                ORDER BY s.id DESC";

        return $this->db->query($sql);
    }

    public function reportItems(int $saleId): array
    {
        return $this->db->query(
            "SELECT si.*, (si.selling_price - si.purchase_price) * si.quantity as item_profit
             FROM pos_sale_items si
             WHERE si.pos_sale_id = {$saleId}"
        );
    }

    public function count(): int
    {
        return (int)$this->db->queryScalar("SELECT COUNT(*) FROM pos_sales");
    }

    public function todaySales(): float
    {
        return (float)$this->db->queryScalar("SELECT IIF(SUM(total) IS NULL, 0, SUM(total)) FROM pos_sales WHERE DateValue(created_at) = Date() AND status = 'completed'");
    }

    public function todayCount(): int
    {
        return (int)$this->db->queryScalar("SELECT COUNT(*) FROM pos_sales WHERE DateValue(created_at) = Date() AND status = 'completed'");
    }
}
