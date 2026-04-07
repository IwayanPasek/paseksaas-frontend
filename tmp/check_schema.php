<?php
require_once __DIR__ . '/../includes/config.php';
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    foreach(['audit_logs', 'impersonation_tokens', 'system_config', 'toko'] as $t) {
        echo "--- TABLE: $t ---\n";
        $stmt = $pdo->query("DESCRIBE $t");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "Column: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Default: {$row['Default']}\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
