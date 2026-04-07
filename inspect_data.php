<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();
$stmt = $pdo->prepare('SELECT * FROM toko WHERE subdomain = ?');
$stmt->execute(['iwayanpasek']);
$t = $stmt->fetch();
print_r($t);

$stmt = $pdo->prepare('SELECT * FROM produk WHERE id_toko = ?');
$stmt->execute([$t['id_toko']]);
print_r($stmt->fetchAll());
?>
