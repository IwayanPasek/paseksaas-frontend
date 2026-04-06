<?php
require_once 'includes/db.php';
$pdo = getDB();
try {
    $pdo->exec('ALTER TABLE toko ADD UNIQUE INDEX idx_subdomain (subdomain)');
    echo "Index idx_subdomain added successfully.\n";
} catch (PDOException $e) {
    if ($e->getCode() == '42000' || strpos($e->getMessage(), 'Duplicate key') !== false) {
        echo "Index already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
