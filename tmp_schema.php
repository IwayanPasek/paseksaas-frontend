<?php
require_once 'h:/pasek-saas/paseksaas-frontend/includes/db.php';
$pdo = getDB();
$stmt = $pdo->query('DESCRIBE toko');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
