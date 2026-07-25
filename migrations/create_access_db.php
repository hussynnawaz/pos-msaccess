<?php
/**
 * Microsoft Access Database Migration Script
 * Uses ADOX/ADODB COM to create .accdb file and tables
 * Run: php migrations/create_access_db.php
 */

$dbPath = __DIR__ . '/../data/pos_system.accdb';
$dataDir = __DIR__ . '/../data';

if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
    echo "Created data directory\n";
}

if (file_exists($dbPath)) {
    echo "Access database already exists: {$dbPath}\n";
    echo "Delete it first to recreate.\n";
    exit(0);
}

echo "Creating Access database: {$dbPath}\n";

try {
    // Create the .accdb file using ADOX
    $adox = new COM("ADOX.Catalog");
    $adox->Create("Provider=Microsoft.ACE.OLEDB.12.0;Data Source={$dbPath}");
    $adox->ActiveConnection->Close();
    unset($adox);
    echo "Created .accdb file\n";

    // Connect using ADODB
    $conn = new COM("ADODB.Connection");
    $conn->Open("Provider=Microsoft.ACE.OLEDB.12.0;Data Source={$dbPath}");
    echo "Connected to database\n";

    $tables = [
        "CREATE TABLE users (
            [id] COUNTER PRIMARY KEY,
            [username] VARCHAR(50) NOT NULL,
            [name] VARCHAR(100) NOT NULL,
            [email] VARCHAR(255) NOT NULL,
            [password] VARCHAR(255) NOT NULL,
            [role] VARCHAR(10) DEFAULT 'staff',
            [is_active] YESNO DEFAULT 1,
            [last_login] DATETIME,
            [created_at] DATETIME DEFAULT NOW(),
            [updated_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE products (
            [id] COUNTER PRIMARY KEY,
            [name] VARCHAR(255) NOT NULL,
            [sku] VARCHAR(100),
            [barcode] VARCHAR(100),
            [category] VARCHAR(100),
            [purchase_price] CURRENCY DEFAULT 0,
            [selling_price] CURRENCY DEFAULT 0,
            [discount_pct] DOUBLE DEFAULT 0,
            [gst_amount] CURRENCY DEFAULT 0,
            [wht_amount] CURRENCY DEFAULT 0,
            [stock] INTEGER DEFAULT 0,
            [unit] VARCHAR(20) DEFAULT 'pcs',
            [image] VARCHAR(255),
            [is_active] YESNO DEFAULT 1,
            [has_variants] YESNO DEFAULT 0,
            [created_at] DATETIME DEFAULT NOW(),
            [updated_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE suppliers (
            [id] COUNTER PRIMARY KEY,
            [name] VARCHAR(255) NOT NULL,
            [contact_person] VARCHAR(100),
            [email] VARCHAR(255),
            [phone] VARCHAR(50),
            [address] MEMO,
            [is_active] YESNO DEFAULT 1,
            [created_at] DATETIME DEFAULT NOW(),
            [updated_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE orders (
            [id] COUNTER PRIMARY KEY,
            [order_number] VARCHAR(50) NOT NULL,
            [order_type] VARCHAR(10) DEFAULT 'sale',
            [user_id] INTEGER NOT NULL,
            [supplier_id] INTEGER,
            [subtotal] CURRENCY DEFAULT 0,
            [discount] CURRENCY DEFAULT 0,
            [tax] CURRENCY DEFAULT 0,
            [total] CURRENCY DEFAULT 0,
            [initial_payment] CURRENCY DEFAULT 0,
            [payment_method] VARCHAR(10) DEFAULT 'cash',
            [amount_paid] CURRENCY DEFAULT 0,
            [change_amount] CURRENCY DEFAULT 0,
            [status] VARCHAR(15) DEFAULT 'completed',
            [notes] MEMO,
            [expected_date] DATETIME,
            [created_at] DATETIME DEFAULT NOW(),
            [updated_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE order_items (
            [id] COUNTER PRIMARY KEY,
            [order_id] INTEGER NOT NULL,
            [product_id] INTEGER NOT NULL,
            [product_name] VARCHAR(255) NOT NULL,
            [quantity] INTEGER DEFAULT 1,
            [unit_price] CURRENCY DEFAULT 0,
            [purchase_price] CURRENCY DEFAULT 0,
            [total] CURRENCY DEFAULT 0,
            [created_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE categories (
            [id] COUNTER PRIMARY KEY,
            [name] VARCHAR(100) NOT NULL,
            [description] MEMO,
            [created_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE product_variants (
            [id] COUNTER PRIMARY KEY,
            [product_id] INTEGER NOT NULL,
            [name] VARCHAR(100) NOT NULL,
            [sku] VARCHAR(100),
            [unit] VARCHAR(20) DEFAULT 'pcs',
            [unit_weight] VARCHAR(50),
            [purchase_price] CURRENCY DEFAULT 0,
            [selling_price] CURRENCY DEFAULT 0,
            [stock] INTEGER DEFAULT 0,
            [is_active] YESNO DEFAULT 1,
            [created_at] DATETIME DEFAULT NOW(),
            [updated_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE pos_sales (
            [id] COUNTER PRIMARY KEY,
            [order_id] INTEGER,
            [sale_number] VARCHAR(50) NOT NULL,
            [cashier_name] VARCHAR(100) DEFAULT 'Admin',
            [subtotal] CURRENCY DEFAULT 0,
            [discount] CURRENCY DEFAULT 0,
            [tax] CURRENCY DEFAULT 0,
            [total] CURRENCY DEFAULT 0,
            [payment_method] VARCHAR(10) DEFAULT 'cash',
            [amount_paid] CURRENCY DEFAULT 0,
            [change_amount] CURRENCY DEFAULT 0,
            [status] VARCHAR(15) DEFAULT 'completed',
            [notes] MEMO,
            [created_at] DATETIME DEFAULT NOW(),
            [updated_at] DATETIME DEFAULT NOW()
        )",

        "CREATE TABLE pos_sale_items (
            [id] COUNTER PRIMARY KEY,
            [pos_sale_id] INTEGER NOT NULL,
            [product_id] INTEGER,
            [product_name] VARCHAR(255) NOT NULL,
            [sku] VARCHAR(100),
            [barcode] VARCHAR(100),
            [purchase_price] CURRENCY DEFAULT 0,
            [selling_price] CURRENCY DEFAULT 0,
            [quantity] INTEGER DEFAULT 1,
            [tax_percent] DOUBLE DEFAULT 0,
            [discount] CURRENCY DEFAULT 0,
            [total] CURRENCY DEFAULT 0,
            [created_at] DATETIME DEFAULT NOW()
        )",
    ];

    foreach ($tables as $i => $sql) {
        $conn->Execute($sql);
        echo "Created table " . ($i + 1) . "/" . count($tables) . "\n";
    }

    // Create indexes
    $indexes = [
        "CREATE UNIQUE INDEX idx_username ON users ([username])",
        "CREATE UNIQUE INDEX idx_email ON users ([email])",
        "CREATE INDEX idx_role ON users ([role])",
        "CREATE UNIQUE INDEX idx_sku ON products ([sku])",
        "CREATE UNIQUE INDEX idx_barcode ON products ([barcode])",
        "CREATE INDEX idx_category ON products ([category])",
        "CREATE INDEX idx_active ON products ([is_active])",
        "CREATE INDEX idx_name_suppliers ON suppliers ([name])",
        "CREATE INDEX idx_active_suppliers ON suppliers ([is_active])",
        "CREATE UNIQUE INDEX idx_order_number ON orders ([order_number])",
        "CREATE INDEX idx_user ON orders ([user_id])",
        "CREATE INDEX idx_status_orders ON orders ([status])",
        "CREATE INDEX idx_created_orders ON orders ([created_at])",
        "CREATE INDEX idx_order_type ON orders ([order_type])",
        "CREATE INDEX idx_supplier ON orders ([supplier_id])",
        "CREATE INDEX idx_order ON order_items ([order_id])",
        "CREATE INDEX idx_product ON order_items ([product_id])",
        "CREATE UNIQUE INDEX idx_name_categories ON categories ([name])",
        "CREATE INDEX idx_product_variants ON product_variants ([product_id])",
        "CREATE INDEX idx_name_variants ON product_variants ([name])",
        "CREATE UNIQUE INDEX idx_sale_number ON pos_sales ([sale_number])",
        "CREATE INDEX idx_cashier ON pos_sales ([cashier_name])",
        "CREATE INDEX idx_status_pos ON pos_sales ([status])",
        "CREATE INDEX idx_created_pos ON pos_sales ([created_at])",
        "CREATE INDEX idx_pos_sale ON pos_sale_items ([pos_sale_id])",
        "CREATE INDEX idx_product_pos ON pos_sale_items ([product_id])",
    ];

    foreach ($indexes as $sql) {
        try {
            $conn->Execute($sql);
        } catch (Exception $e) {
            // Ignore duplicate index errors
        }
    }
    echo "Created indexes\n";

    // Insert admin user
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $username = 'admin';
    $name = 'Administrator';
    $email = 'hussyn.nawaz@gmail.com';
    $role = 'admin';
    $conn->Execute("INSERT INTO users ([username], [name], [email], [password], [role]) VALUES ('{$username}', '{$name}', '{$email}', '" . addslashes($hashedPassword) . "', '{$role}')");
    echo "Inserted admin user (admin / admin123)\n";

    $conn->Close();
    echo "\nAccess database created successfully!\n";
    echo "Path: {$dbPath}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
