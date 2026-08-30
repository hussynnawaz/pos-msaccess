<?php
class Database
{
    private static ?Database $instance = null;
    private ?object $connection = null;

    private string $dbPath;

    private function __construct()
    {
        // Always resolve relative to project root — works on any PC
        $projectRoot = __DIR__ . '/..';
        $relativePath = getenv('DB_PATH') ?: 'data/pos_system.accdb';
        $this->dbPath = $projectRoot . '/' . ltrim($relativePath, '/');

        // Ensure data directory exists
        $dataDir = dirname($this->dbPath);
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get ADODB connection (COM)
     */
    public function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }

        if (!file_exists($this->dbPath)) {
            throw new \RuntimeException("Database file not found: {$this->dbPath}");
        }

        try {
            $this->connection = new COM("ADODB.Connection");
            $this->connection->Mode = 3; // adModeReadWrite (shared)
            $this->connection->Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source={$this->dbPath};Mode=Share Deny None");
            $this->connection->CursorLocation = 3; // adUseClient
        } catch (\com_exception $e) {
            $this->connection = null;
            throw $e;
        }

        return $this->connection;
    }

    /**
     * Execute a SQL query and return results as associative array
     */
    public function query(string $sql, array $params = []): array
    {
        $conn = $this->getConnection();
        $rs = $conn->Execute($sql);

        $results = [];
        if ($rs && !$rs->EOF) {
            while (!$rs->EOF) {
                $row = [];
                for ($i = 0; $i < $rs->Fields->Count(); $i++) {
                    $field = $rs->Fields($i);
                    $row[$field->Name] = $this->castVariant($field->Value);
                }
                $results[] = $row;
                $rs->MoveNext();
            }
        }
        return $results;
    }

    /**
     * Execute a SQL query and return first row
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        $results = $this->query($sql, $params);
        return !empty($results) ? $results[0] : null;
    }

    /**
     * Execute a SQL query and return single value
     */
    public function queryScalar(string $sql): mixed
    {
        $conn = $this->getConnection();
        $rs = $conn->Execute($sql);
        if ($rs && !$rs->EOF) {
            return $this->castVariant($rs->Fields(0)->Value);
        }
        return null;
    }

    /**
     * Execute a SQL statement (INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql): bool
    {
        $conn = $this->getConnection();
        $conn->Execute($sql);
        return true;
    }

    /**
     * Get last inserted ID (Access uses SELECT @@IDENTITY)
     */
    public function lastInsertId(): int
    {
        $result = $this->queryScalar("SELECT @@IDENTITY");
        return (int)$result;
    }

    /**
     * Convert ADODB variant to native PHP type
     */
    public function castVariant(mixed $value): mixed
    {
        if (is_null($value)) return null;
        if (is_object($value)) {
            // ADODB variant — try to convert
            $val = (string)$value;
            if (is_numeric($val)) {
                return strpos($val, '.') !== false ? (float)$val : (int)$val;
            }
            return $val;
        }
        return $value;
    }

    /**
     * Quote a string value for SQL
     */
    public function quote(string $value): string
    {
        return "'" . addslashes($value) . "'";
    }

    /**
     * Escape a field/table name with brackets
     */
    public function br(string $name): string
    {
        return "[{$name}]";
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction(): void
    {
        $this->getConnection()->BeginTrans();
    }

    /**
     * Commit a transaction
     */
    public function commit(): void
    {
        $this->getConnection()->CommitTrans();
    }

    /**
     * Rollback a transaction
     */
    public function rollBack(): void
    {
        $this->getConnection()->RollbackTrans();
    }

    /**
     * Check if a column exists in a table
     */
    public function hasColumn(string $table, string $column): bool
    {
        try {
            $this->query("SELECT TOP 1 [{$column}] FROM [{$table}]");
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isAccess(): bool
    {
        return true;
    }

    public function getDbPath(): string
    {
        return $this->dbPath;
    }

    /**
     * Close the ADODB connection and release the lock file
     */
    public function close(): void
    {
        if ($this->connection !== null) {
            try {
                $this->connection->Close();
            } catch (\Throwable $e) {
                // Ignore close errors
            }
            $this->connection = null;
        }
    }

    /**
     * Reset the singleton so next request gets a fresh instance
     */
    public static function resetInstance(): void
    {
        if (self::$instance !== null) {
            self::$instance->close();
            self::$instance = null;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    private function __clone() {}
    public function __wakeup() {}
}
