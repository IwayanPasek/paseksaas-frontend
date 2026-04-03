<?php
$db_host = 'localhost';
$db_user = 'wayan_user';
$db_pass = 'WayanPass123!';
$db_name = 'websitewayan_db';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    
    // 1. Kosongkan tabel master_admin
    $pdo->exec("TRUNCATE TABLE master_admin");
    
    // 2. Buat Hash yang dijamin fresh oleh sistem PHP Anda sendiri
    $username = 'pasek';
    $password = 'pasek';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    // 3. Masukkan ke database
    $stmt = $pdo->prepare("INSERT INTO master_admin (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $hash]);
    
    echo "<h1>Selesai!</h1>";
    echo "User <b>$username</b> berhasil didaftarkan ulang.<br>";
    echo "Hash yang dibuat: <code>$hash</code><br><br>";
    echo "<a href='login.php'>Klik di sini untuk ke halaman Login</a>";

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
