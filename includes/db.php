<?php
/**
 * db.php — Secure PDO Connection Factory.
 * Returns a singleton PDO instance with security-hardened settings.
 */

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Removed persistent connections (security risk in shared environments)
                PDO::ATTR_PERSISTENT         => false,
                // Connection timeout (seconds)
                PDO::ATTR_TIMEOUT            => 5,
            ];

            // Enable SSL/TLS in production if certificate is available
            $sslCa = getenv('DB_SSL_CA');
            if ($sslCa && file_exists($sslCa)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
            }

            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                $options
            );
        } catch (PDOException $e) {
            // Log the real error server-side, show generic message to user
            error_log('DATABASE CONNECTION FAILED: ' . $e->getMessage());
            http_response_code(503);
            die('<div style="text-align:center;font-family:Inter,sans-serif;margin-top:20vh;color:#737373;"><h1>Service Unavailable</h1><p>We are experiencing a temporary issue. Please try again shortly.</p></div>');
        }
    }
    return $pdo;
}
