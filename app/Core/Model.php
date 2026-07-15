<?php
/**
 * Base Model — PDO query helpers
 */
abstract class Model
{
    protected PDO $db;
    protected string $table  = '';
    protected string $pk     = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── Fetch all rows ────────────────────────────────────────
    public function all(string $orderBy = ''): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        return $this->db->query($sql)->fetchAll();
    }

    // ── Find by PK ────────────────────────────────────────────
    public function find(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->pk}` = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // ── Find by column ────────────────────────────────────────
    public function findBy(string $column, mixed $value): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$column}` = ? LIMIT 1");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }

    // ── Find all by column ────────────────────────────────────
    public function where(string $column, mixed $value, string $order = ''): array
    {
        $sql  = "SELECT * FROM `{$this->table}` WHERE `{$column}` = ?";
        if ($order) $sql .= " ORDER BY {$order}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }

    // ── Raw query ─────────────────────────────────────────────
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Single row raw ────────────────────────────────────────
    public function queryOne(string $sql, array $params = []): array|false
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // ── Scalar value ──────────────────────────────────────────
    public function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    // ── Insert ────────────────────────────────────────────────
    public function insert(array $data): int
    {
        $cols = implode('`, `', array_keys($data));
        $phs  = implode(', ', array_fill(0, count($data), '?'));
        $sql  = "INSERT INTO `{$this->table}` (`{$cols}`) VALUES ({$phs})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    // ── Update ────────────────────────────────────────────────
    public function update(int $id, array $data): bool
    {
        $set  = implode(' = ?, ', array_map(fn($k) => "`{$k}`", array_keys($data))) . ' = ?';
        $sql  = "UPDATE `{$this->table}` SET {$set} WHERE `{$this->pk}` = ?";
        $vals = array_values($data);
        $vals[] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($vals);
    }

    // ── Delete ────────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->pk}` = ?");
        return $stmt->execute([$id]);
    }

    // ── Count ─────────────────────────────────────────────────
    public function count(string $where = '1=1', array $params = []): int
    {
        $sql  = "SELECT COUNT(*) FROM `{$this->table}` WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // ── Paginate ──────────────────────────────────────────────
    public function paginate(int $page, int $perPage = PER_PAGE, string $where = '1=1', array $params = [], string $order = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $total  = $this->count($where, $params);
        $sql    = "SELECT * FROM `{$this->table}` WHERE {$where}";
        if ($order) $sql .= " ORDER BY {$order}";
        $sql   .= " LIMIT {$perPage} OFFSET {$offset}";
        $stmt   = $this->db->prepare($sql);
        $stmt->execute($params);
        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'pages'      => (int) ceil($total / $perPage),
            'current'    => $page,
            'per_page'   => $perPage,
        ];
    }

    // ── Execute statement (INSERT/UPDATE/DELETE) ──────────────
    public function exec(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
