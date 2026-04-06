<?php
require_once 'h:/pasek-saas/paseksaas-frontend/includes/db.php';
$pdo = getDB();
try {
    $stmt = $pdo->query("DESCRIBE toko");
    $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $cols);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
