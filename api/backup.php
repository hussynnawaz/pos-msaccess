<?php
require_once __DIR__ . '/../bootstrap.php';

$session = new SessionManager();
$session->start();

$auth = new AuthController();
$user = $auth->requireAuth();

$content = $_GET['content'] ?? 'both';
$format  = $_GET['format']  ?? 'sql';

if (!in_array($content, ['structure', 'data', 'both'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid content type']);
    exit;
}
if (!in_array($format, ['sql', 'tsql', 'accessdb'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid format']);
    exit;
}

try {
    $db   = Database::getInstance();
    $conn = $db->getConnection();

    // Use ADOX Catalog to enumerate tables and columns
    $adox = new COM("ADOX.Catalog");
    $adox->ActiveConnection = $conn;

    $tableMeta = [];  // tableName => [ ['name','type','size','nullable','default'], ... ]
    foreach ($adox->Tables as $table) {
        if ($table->Type !== 'TABLE') continue;
        $cols = [];
        foreach ($table->Columns as $col) {
            $defVal = null;
            try { $defVal = $col->DefaultValue; } catch (\Exception $e) {}
            $cols[] = [
                'name'         => $col->Name,
                'type'         => $col->Type,
                'definedsize'  => $col->DefinedSize,
                'nullable'     => true,
                'defaultvalue' => $defVal,
            ];
        }
        $tableMeta[$table->Name] = $cols;
    }
    unset($adox);

    if (empty($tableMeta)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No tables found in database']);
        exit;
    }

    // --- Access DB: just copy the file ---
    if ($format === 'accessdb') {
        $sourcePath = $db->getDbPath();
        if (!file_exists($sourcePath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Source database file not found']);
            exit;
        }
        $timestamp = date('Y-m-d_H-i-s');
        $filename  = "pos_backup_{$timestamp}.accdb";
        $tmpPath   = sys_get_temp_dir() . '\\' . $filename;
        if (!copy($sourcePath, $tmpPath)) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to copy database file']);
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpPath));
        header('Cache-Control: no-cache');
        readfile($tmpPath);
        @unlink($tmpPath);
        exit;
    }

    // --- SQL / T-SQL dump ---
    $eol = "\r\n";
    $out = '';

    if ($format === 'tsql') {
        $out .= "-- T-SQL Backup generated on " . date('Y-m-d H:i:s') . $eol;
        $out .= "-- Source: POS System (Access -> SQL Server)" . $eol;
        $out .= "-- Usage: Execute in SQL Server Management Studio" . $eol . $eol;
        $out .= "USE [pos_database];" . $eol . $eol;
        $out .= "SET XACT_ABORT ON;" . $eol . $eol;
    } else {
        $out .= "-- MySQL/MariaDB Backup generated on " . date('Y-m-d_H:i:s') . $eol;
        $out .= "-- Source: POS System (Access -> MySQL)" . $eol;
        $out .= "SET NAMES utf8mb4;" . $eol;
        $out .= "SET FOREIGN_KEY_CHECKS = 0;" . $eol . $eol;
    }

    foreach ($tableMeta as $tableName => $columns) {
        $safe = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);

        // CREATE TABLE
        if ($content === 'structure' || $content === 'both') {
            $colDefs = [];
            foreach ($columns as $col) {
                $colDefs[] = '    ' . ($format === 'tsql'
                    ? formatColumnTSQL($col)
                    : formatColumnMySQL($col));
            }
            if ($format === 'tsql') {
                $out .= "IF OBJECT_ID('[{$safe}]', 'U') IS NULL" . $eol;
                $out .= "CREATE TABLE [{$safe}] (" . $eol;
                $out .= implode("," . $eol, $colDefs);
                $out .= $eol . ");" . $eol . $eol;
            } else {
                $out .= "DROP TABLE IF EXISTS `{$safe}`;" . $eol;
                $out .= "CREATE TABLE `{$safe}` (" . $eol;
                $out .= implode("," . $eol, $colDefs);
                $out .= $eol . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;" . $eol . $eol;
            }
        }

        // INSERT data
        if ($content === 'data' || $content === 'both') {
            $dataRs = $conn->Execute("SELECT * FROM [{$safe}]");
            if ($dataRs && !$dataRs->EOF) {
                $fieldCount = $dataRs->Fields->Count();
                $colNames = [];
                $fieldTypes = [];
                for ($i = 0; $i < $fieldCount; $i++) {
                    $f = $dataRs->Fields($i);
                    $colNames[]   = ($format === 'tsql' ? '[' . $f->Name . ']' : '`' . $f->Name . '`');
                    $fieldTypes[] = $f->Type;
                }
                $prefix = $format === 'tsql' ? "INSERT INTO [{$safe}]" : "INSERT INTO `{$safe}`";

                while (!$dataRs->EOF) {
                    $values = [];
                    for ($i = 0; $i < $fieldCount; $i++) {
                        $val = $db->castVariant($dataRs->Fields($i)->Value);
                        $values[] = ($format === 'tsql')
                            ? formatValueTSQL($val, $fieldTypes[$i])
                            : formatValueMySQL($val, $fieldTypes[$i]);
                    }
                    $out .= $prefix . " (" . implode(', ', $colNames) . ") VALUES (" . implode(', ', $values) . ");" . $eol;
                    $dataRs->MoveNext();
                }
                $out .= $eol;
            }
        }
    }

    if ($format === 'sql') {
        $out .= "SET FOREIGN_KEY_CHECKS = 1;" . $eol;
    }

    $timestamp = date('Y-m-d_H-i-s');
    $filename  = "pos_backup_{$timestamp}.sql";

    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache');
    echo $out;
    exit;

} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Backup failed: ' . $e->getMessage()]);
    exit;
}

// ================================================================
//  Helper functions
// ================================================================

function mapAccessTypeToMySQL(int $type, int $size): string
{
    $map = [
        20  => 'tinyint',
        3   => 'int',
        21  => 'bigint',
        4   => 'float',
        5   => 'double',
        6   => 'decimal(19,4)',
        131 => 'decimal(18,4)',
        133 => 'decimal(18,4)',
        135 => 'datetime',
        134 => 'time',
        7   => 'datetime',
        11  => 'tinyint(1)',
        128 => 'blob',
        205 => 'longblob',
        200 => 'varchar',
        201 => 'longtext',
        130 => 'varchar',
        202 => 'varchar',
        203 => 'longtext',
    ];
    $t = $map[$type] ?? 'text';
    if ($t === 'varchar' && $size > 0) $t .= "({$size})";
    return $t;
}

function mapAccessTypeToTSQL(int $type, int $size): string
{
    $map = [
        20  => 'SMALLINT',
        3   => 'INT',
        21  => 'BIGINT',
        4   => 'REAL',
        5   => 'FLOAT',
        6   => 'MONEY',
        131 => 'DECIMAL(18,4)',
        133 => 'DECIMAL(18,4)',
        135 => 'DATETIME2',
        134 => 'TIME',
        7   => 'DATE',
        11  => 'BIT',
        128 => 'VARBINARY(MAX)',
        205 => 'VARBINARY(MAX)',
        200 => 'NVARCHAR(255)',
        201 => 'NVARCHAR(MAX)',
        130 => 'NVARCHAR(255)',
        202 => 'NVARCHAR(255)',
        203 => 'NVARCHAR(MAX)',
    ];
    $t = $map[$type] ?? 'NVARCHAR(MAX)';
    if (in_array($t, ['NVARCHAR(255)']) && $size > 0) {
        $t = "NVARCHAR({$size})";
    }
    if ($t === 'varchar' && $size > 0) {
        $t = "VARCHAR({$size})";
    }
    return $t;
}

function formatColumnMySQL(array $col): string
{
    $name = '`' . $col['name'] . '`';
    $type = mapAccessTypeToMySQL($col['type'], $col['definedsize']);
    $null = $col['nullable'] ? 'NULL' : 'NOT NULL';
    $def  = '';
    if ($col['defaultvalue'] !== null && $col['defaultvalue'] !== '') {
        $def = ' DEFAULT ' . $col['defaultvalue'];
    }
    return "{$name} {$type} {$null}{$def}";
}

function formatColumnTSQL(array $col): string
{
    $name = '[' . $col['name'] . ']';
    $type = mapAccessTypeToTSQL($col['type'], $col['definedsize']);
    $null = $col['nullable'] ? 'NULL' : 'NOT NULL';
    $def  = '';
    if ($col['defaultvalue'] !== null && $col['defaultvalue'] !== '') {
        $def = ' DEFAULT ' . $col['defaultvalue'];
    }
    $identity = '';
    if ($col['type'] == 3 && strtolower($col['name']) === 'id') {
        $identity = ' IDENTITY(1,1)';
    }
    return "{$name} {$type}{$identity} {$null}{$def}";
}

function formatValueMySQL($value, int $type): string
{
    if ($value === null) return 'NULL';
    if ($type == 11) return $value ? '1' : '0';
    if (in_array($type, [3, 20, 21, 4, 5, 6, 131, 133])) return (string)$value;
    return "'" . addslashes((string)$value) . "'";
}

function formatValueTSQL($value, int $type): string
{
    if ($value === null) return 'NULL';
    if ($type == 11) return $value ? '1' : '0';
    if (in_array($type, [3, 20, 21, 4, 5, 6, 131, 133])) return (string)$value;
    return "N'" . addslashes((string)$value) . "'";
}
