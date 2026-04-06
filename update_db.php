<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'h:/pasek-saas/paseksaas-frontend/includes/db.php';
$pdo = getDB();

try {
    // Check if 'status' column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM toko LIKE 'status'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE toko ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER password");
        // Existing tenants should be 'active'
        $pdo->exec("UPDATE toko SET status = 'active' WHERE status IS NULL OR status = ''");
        echo "Added 'status' column. ";
    } else {
        echo "Column 'status' already exists. ";
    }

    // Check if 'email' column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM toko LIKE 'email'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE toko ADD COLUMN email VARCHAR(100) DEFAULT NULL AFTER nama_toko");
        echo "Added 'email' column. ";
    } else {
        echo "Column 'email' already exists. ";
    }

} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
