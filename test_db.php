<?php
require_once __DIR__ . '/includes/db.php';
try {
    $pdo = getDB();
    echo "Connection Successful!\n";
} catch (Exception $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
}
