<?php
class Variant
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function byProduct(int $productId): array
    {
        return $this->db->query("SELECT * FROM product_variants WHERE product_id = {$productId} ORDER BY id ASC");
    }

    public function byProducts(array $productIds): array
    {
        if (empty($productIds)) return [];
        $ids = implode(',', array_map('intval', $productIds));
        $all = $this->db->query("SELECT * FROM product_variants WHERE product_id IN ({$ids}) ORDER BY id ASC");
        $grouped = [];
        foreach ($all as $v) {
            $grouped[$v['product_id']][] = $v;
        }
        return $grouped;
    }

    public function find(int $id): ?array
    {
        return $this->db->queryOne("SELECT * FROM product_variants WHERE id = {$id}");
    }

    public function create(int $productId, array $data): array
    {
        $name = $this->db->quote($data['name']);
        $sku = !empty($data['sku']) ? $this->db->quote($data['sku']) : 'NULL';
        $unit = $this->db->quote($data['unit'] ?? 'pcs');
        $unitWeight = !empty($data['unit_weight']) ? $this->db->quote($data['unit_weight']) : 'NULL';
        $purchasePrice = (float)($data['purchase_price'] ?? 0);
        $sellingPrice = (float)($data['selling_price'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);

        $sql = "INSERT INTO product_variants ([product_id], [name], [sku], [unit], [unit_weight], [purchase_price], [selling_price], [stock], [is_active])
                VALUES ({$productId}, {$name}, {$sku}, {$unit}, {$unitWeight}, {$purchasePrice}, {$sellingPrice}, {$stock}, {$isActive})";

        $this->db->execute($sql);

        return ['success' => true, 'message' => 'Variant created', 'id' => $this->db->lastInsertId()];
    }

    public function update(int $id, array $data): array
    {
        $name = $this->db->quote($data['name']);
        $sku = !empty($data['sku']) ? $this->db->quote($data['sku']) : 'NULL';
        $unit = $this->db->quote($data['unit'] ?? 'pcs');
        $unitWeight = !empty($data['unit_weight']) ? $this->db->quote($data['unit_weight']) : 'NULL';
        $purchasePrice = (float)($data['purchase_price'] ?? 0);
        $sellingPrice = (float)($data['selling_price'] ?? 0);
        $stock = (int)($data['stock'] ?? 0);
        $isActive = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE product_variants SET
                    [name] = {$name},
                    [sku] = {$sku},
                    [unit] = {$unit},
                    [unit_weight] = {$unitWeight},
                    [purchase_price] = {$purchasePrice},
                    [selling_price] = {$sellingPrice},
                    [stock] = {$stock},
                    [is_active] = {$isActive}
                WHERE id = {$id}";

        $this->db->execute($sql);

        return ['success' => true, 'message' => 'Variant updated'];
    }

    public function delete(int $id): array
    {
        $this->db->execute("DELETE FROM product_variants WHERE id = {$id}");
        return ['success' => true, 'message' => 'Variant deleted'];
    }

    public function deleteByProduct(int $productId): void
    {
        $this->db->execute("DELETE FROM product_variants WHERE product_id = {$productId}");
    }
}
