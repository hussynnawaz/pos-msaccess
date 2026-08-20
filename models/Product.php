<?php
class Product
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function buildWhere(array $filters): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['search'])) {
            $search = $this->db->quote('%' . $filters['search'] . '%');
            $where[] = "(p.name LIKE {$search} OR p.barcode LIKE {$search} OR p.sku LIKE {$search})";
        }

        if (!empty($filters['category'])) {
            $cat = $this->db->quote($filters['category']);
            $where[] = "p.category = {$cat}";
        }

        if (isset($filters['active'])) {
            $where[] = "p.is_active = " . (int)$filters['active'];
        }

        return $where;
    }

    public function all(array $filters = []): array
    {
        $where = $this->buildWhere($filters);

        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = (int)($filters['per_page'] ?? 50);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM products p WHERE " . implode(' AND ', $where);

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

    public function totalCount(array $filters = []): int
    {
        $where = $this->buildWhere($filters);
        $sql = "SELECT COUNT(*) AS cnt FROM products p WHERE " . implode(' AND ', $where);
        $result = $this->db->queryOne($sql);
        return (int)($result['cnt'] ?? 0);
    }

    public function findByBarcode(string $barcode): ?array
    {
        $barcode = $this->db->quote($barcode);
        $sql = "SELECT * FROM products WHERE barcode = {$barcode} AND is_active = 1";
        return $this->db->queryOne($sql);
    }

    public function find(int $id): ?array
    {
        return $this->db->queryOne("SELECT * FROM products WHERE id = {$id}");
    }

    public function create(array $data): array
    {
        if (!empty($data['barcode'])) {
            $barcode = $this->db->quote($data['barcode']);
            $existing = $this->db->queryOne("SELECT id FROM products WHERE barcode = {$barcode}");
            if ($existing) {
                return ['success' => false, 'message' => 'Barcode already exists'];
            }
        }

        $hasVariants = !empty($data['has_variants']) ? 1 : 0;

        $name = $this->db->quote($data['name']);
        $barcode = !empty($data['barcode']) ? $this->db->quote($data['barcode']) : 'NULL';
        $category = !empty($data['category']) ? $this->db->quote($data['category']) : 'NULL';
        $purchasePrice = (float)($data['purchase_price'] ?? 0);
        $sellingPrice = (float)($data['selling_price'] ?? 0);
        $discountPct = (float)($data['discount_pct'] ?? 0);
        $gstAmount = (float)($data['gst_amount'] ?? 0);
        $whtAmount = (float)($data['wht_amount'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $unit = $this->db->quote($data['unit'] ?? 'pcs');
        $isActive = (int)($data['is_active'] ?? 1);

        $sql = "INSERT INTO products ([name], [barcode], [category], [purchase_price], [selling_price], [discount_pct], [gst_amount], [wht_amount], [stock], [unit], [is_active], [has_variants])
                VALUES ({$name}, {$barcode}, {$category}, {$purchasePrice}, {$sellingPrice}, {$discountPct}, {$gstAmount}, {$whtAmount}, {$stock}, {$unit}, {$isActive}, {$hasVariants})";

        $this->db->execute($sql);
        $productId = $this->db->lastInsertId();

        return ['success' => true, 'message' => 'Product created', 'id' => $productId];
    }

    public function update(int $id, array $data): array
    {
        if (!empty($data['barcode'])) {
            $barcode = $this->db->quote($data['barcode']);
            $existing = $this->db->queryOne("SELECT id FROM products WHERE barcode = {$barcode} AND id <> {$id}");
            if ($existing) {
                return ['success' => false, 'message' => 'Barcode already exists'];
            }
        }

        $hasVariants = !empty($data['has_variants']) ? 1 : 0;

        $name = $this->db->quote($data['name']);
        $barcode = !empty($data['barcode']) ? $this->db->quote($data['barcode']) : 'NULL';
        $category = !empty($data['category']) ? $this->db->quote($data['category']) : 'NULL';
        $purchasePrice = (float)($data['purchase_price'] ?? 0);
        $sellingPrice = (float)($data['selling_price'] ?? 0);
        $discountPct = (float)($data['discount_pct'] ?? 0);
        $gstAmount = (float)($data['gst_amount'] ?? 0);
        $whtAmount = (float)($data['wht_amount'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $unit = $this->db->quote($data['unit'] ?? 'pcs');
        $isActive = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE products SET
                    [name] = {$name},
                    [barcode] = {$barcode},
                    [category] = {$category},
                    [purchase_price] = {$purchasePrice},
                    [selling_price] = {$sellingPrice},
                    [discount_pct] = {$discountPct},
                    [gst_amount] = {$gstAmount},
                    [wht_amount] = {$whtAmount},
                    [stock] = {$stock},
                    [unit] = {$unit},
                    [is_active] = {$isActive},
                    [has_variants] = {$hasVariants}
                WHERE id = {$id}";

        $this->db->execute($sql);

        return ['success' => true, 'message' => 'Product updated'];
    }

    public function delete(int $id): array
    {
        $existing = $this->db->queryOne("SELECT TOP 1 id FROM order_items WHERE product_id = {$id}");
        if ($existing) {
            return ['success' => false, 'message' => 'Cannot delete product with existing orders'];
        }

        $this->db->execute("DELETE FROM products WHERE id = {$id}");

        return ['success' => true, 'message' => 'Product deleted'];
    }

    public function count(): int
    {
        $result = $this->db->queryScalar("SELECT COUNT(*) FROM products");
        return (int)$result;
    }

    public function lowStockCount(): int
    {
        $result = $this->db->queryScalar("SELECT COUNT(*) FROM products WHERE stock <= 5 AND is_active = 1");
        return (int)$result;
    }

public function categories(): array
{
    $rs = $this->db->getConnection()->Execute("SELECT [name] FROM [categories]");

    $results = [];

    while (!$rs->EOF) {
        $results[] = $rs->Fields("name")->Value;
        $rs->MoveNext();
    }

    return $results;
}
}

