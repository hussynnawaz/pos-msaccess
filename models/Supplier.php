<?php
class Supplier
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(array $filters = []): array
    {
        $where = ['1=1'];

        if (!empty($filters['search'])) {
            $search = $this->db->quote('%' . $filters['search'] . '%');
            $where[] = "(name LIKE {$search} OR contact_person LIKE {$search} OR phone LIKE {$search} OR email LIKE {$search})";
        }

        if (isset($filters['active'])) {
            $where[] = "is_active = " . (int)$filters['active'];
        }

        $sql = "SELECT * FROM suppliers WHERE " . implode(' AND ', $where) . " ORDER BY id DESC";
        return $this->db->query($sql);
    }

    public function find(int $id): ?array
    {
        return $this->db->queryOne("SELECT * FROM suppliers WHERE id = {$id}");
    }

    public function create(array $data): array
    {
        $name = $this->db->quote($data['name']);
        $contactPerson = !empty($data['contact_person']) ? $this->db->quote($data['contact_person']) : 'NULL';
        $email = !empty($data['email']) ? $this->db->quote($data['email']) : 'NULL';
        $phone = !empty($data['phone']) ? $this->db->quote($data['phone']) : 'NULL';
        $address = !empty($data['address']) ? $this->db->quote($data['address']) : 'NULL';
        $isActive = (int)($data['is_active'] ?? 1);

        $sql = "INSERT INTO suppliers ([name], [contact_person], [email], [phone], [address], [is_active])
                VALUES ({$name}, {$contactPerson}, {$email}, {$phone}, {$address}, {$isActive})";

        $this->db->execute($sql);

        return ['success' => true, 'message' => 'Supplier created', 'id' => $this->db->lastInsertId()];
    }

    public function update(int $id, array $data): array
    {
        $name = $this->db->quote($data['name']);
        $contactPerson = !empty($data['contact_person']) ? $this->db->quote($data['contact_person']) : 'NULL';
        $email = !empty($data['email']) ? $this->db->quote($data['email']) : 'NULL';
        $phone = !empty($data['phone']) ? $this->db->quote($data['phone']) : 'NULL';
        $address = !empty($data['address']) ? $this->db->quote($data['address']) : 'NULL';
        $isActive = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE suppliers SET
                    [name] = {$name},
                    [contact_person] = {$contactPerson},
                    [email] = {$email},
                    [phone] = {$phone},
                    [address] = {$address},
                    [is_active] = {$isActive}
                WHERE id = {$id}";

        $this->db->execute($sql);

        return ['success' => true, 'message' => 'Supplier updated'];
    }

    public function delete(int $id): array
    {
        $this->db->execute("DELETE FROM suppliers WHERE id = {$id}");
        return ['success' => true, 'message' => 'Supplier deleted'];
    }

    public function count(): int
    {
        return (int)$this->db->queryScalar("SELECT COUNT(*) FROM suppliers");
    }
}
