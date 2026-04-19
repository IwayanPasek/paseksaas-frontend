<?php
/**
 * error_handler.php — Centralized Error Handling
 * 
 * Provides production-safe error display:
 * - Logs real error details to server error log
 * - Shows generic user-friendly messages
 * - Logs security events to database when possible
 */

/**
 * Log a security event to the database.
 * Fails silently — never crashes the request.
 */
function logSecurityEvent(
    string $eventType,
    string $severity = 'medium',
    ?string $details = null
): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare(
            'INSERT INTO security_events (event_type, severity, ip_address, user_agent, request_uri, details)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $eventType,
            $severity,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            substr($_SERVER['REQUEST_URI'] ?? '', 0, 2000),
            $details,
        ]);
    } catch (Exception $e) {
        // Silently fail — don't crash the request for a logging failure
        error_log("Failed to log security event ($eventType): " . $e->getMessage());
    }
}

/**
 * Handle a database error safely.
 * Logs the real error, returns/outputs a generic message.
 * 
 * @param PDOException $e       The caught exception
 * @param string       $context A developer-readable context string (e.g., "login query")
 * @param bool         $asJson  If true, outputs JSON error and exits. If false, returns error message.
 * @return string               Generic error message (only returned if $asJson is false)
 */
function handleDbError(PDOException $e, string $context = 'operation', bool $asJson = false): string {
    // Log real error server-side
    error_log("DB ERROR [$context]: " . $e->getMessage());
    
    $message = 'A system error occurred. Please try again later.';
    
    if ($asJson) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
    
    return $message;
}

/**
 * Show a generic error page and exit.
 */
function showErrorPage(int $statusCode = 500, string $title = 'Error', string $message = ''): void {
    http_response_code($statusCode);
    $safeTitle = htmlspecialchars($title);
    $safeMsg = htmlspecialchars($message ?: 'An unexpected error occurred. Please try again later.');
    
    die(<<<HTML
<div style="text-align:center;font-family:Inter,sans-serif;margin-top:20vh;color:#737373;max-width:500px;margin-left:auto;margin-right:auto;">
    <h1 style="color:#404040;">$safeTitle</h1>
    <p>$safeMsg</p>
    <a href="/" style="color:#3b82f6;text-decoration:none;font-size:14px;">← Back to Home</a>
</div>
HTML);
}
