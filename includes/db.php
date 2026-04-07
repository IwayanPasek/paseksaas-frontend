<?php
/**
 * db.php — PDO connection factory.
 * Returns a singleton PDO instance with consistent settings.
 */

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_PERSISTENT         => true,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(503);
            die("<div style='text-align:center;font-family:Inter,sans-serif;margin-top:20vh;color:#737373;'><h1>Database Connection Error</h1><p>Our server is currently unable to reach the database. Please ensure your database service is running.</p><p style='font-size:11px;color:#a3a3a3;'>" . htmlspecialchars($e->getMessage()) . "</p></div>");
        }
    }
    return $pdo;
}
