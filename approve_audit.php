<?php
require_once 'h:/pasek-saas/paseksaas-frontend/includes/db.php';
$pdo = getDB();
$pdo->query("UPDATE toko SET status='active' WHERE subdomain='auditstore'");
echo "Approved successfully";
