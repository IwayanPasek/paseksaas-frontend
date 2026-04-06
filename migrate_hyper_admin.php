<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

try {
    // 1. Create audit_logs table
    $sqlAudit = "CREATE TABLE IF NOT EXISTS audit_logs (
        id_audit INT AUTO_INCREMENT PRIMARY KEY,
        id_admin INT NULL,
        action_type VARCHAR(50) NOT NULL,
        entity_type VARCHAR(50) NOT NULL,
        entity_id INT NULL,
        action_details TEXT NULL,
        ip_address VARCHAR(45) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sqlAudit);
    echo "Tabel audit_logs berhasil dibuat.\n";

    // 2. Create system_config table
    $sqlConfig = "CREATE TABLE IF NOT EXISTS system_config (
        config_key VARCHAR(100) PRIMARY KEY,
        config_value TEXT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlConfig);
    echo "Tabel system_config berhasil dibuat.\n";

    // 3. Add 'status' and 'plan' columns to 'toko' table if they don't exist
    // Checking for columns in 'toko'
    $stmt = $pdo->query("SHOW COLUMNS FROM toko LIKE 'status'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE toko ADD COLUMN status ENUM('pending', 'active', 'suspended') DEFAULT 'pending' AFTER knowledge_base");
        echo "Kolom 'status' ditambahkan ke tabel toko.\n";
    }

    $stmt = $pdo->query("SHOW COLUMNS FROM toko LIKE 'plan'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE toko ADD COLUMN plan VARCHAR(50) DEFAULT 'starter' AFTER status");
        echo "Kolom 'plan' ditambahkan ke tabel toko.\n";
    }

    // 4. Create impersonation_tokens table for secure redirection
    $sqlImpersonation = "CREATE TABLE IF NOT EXISTS impersonation_tokens (
        token VARCHAR(100) PRIMARY KEY,
        id_toko INT NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sqlImpersonation);
    echo "Tabel impersonation_tokens berhasil dibuat.\n";

} catch (PDOException $e) {
    die("Error migrasi: " . $e->getMessage());
}
