<?php
require_once __DIR__ . '/includes/db.php';
try {
    $pdo = getDB();
    echo "Koneksi Berhasil!\n";
} catch (Exception $e) {
    echo "Koneksi Gagal: " . $e->getMessage() . "\n";
}
