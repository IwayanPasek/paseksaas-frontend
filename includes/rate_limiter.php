<?php
/**
 * rate_limiter.php — IP-based Rate Limiting via Database
 * 
 * Replaces the session-based brute force protection which could
 * be bypassed by clearing cookies.
 * 
 * Uses the `login_attempts` table for persistent IP tracking.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/error_handler.php';

/**
 * Configuration
 */
define('MAX_LOGIN_ATTEMPTS', 5);           // Max failed attempts before lockout
define('LOCKOUT_DURATION_MINUTES', 15);    // Lockout duration
define('ATTEMPT_WINDOW_MINUTES', 30);     // Rolling window for counting attempts

/**
 * Record a login attempt (success or failure).
 */
function recordLoginAttempt(string $username, bool $success): void {
    try {
        $pdo = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $pdo->prepare(
            'INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, ?)'
        );
        $stmt->execute([$ip, $username, $success ? 1 : 0]);
        
        // If this was a failed attempt, check if we should log a security event
        if (!$success) {
            $failCount = getRecentFailedAttempts();
            if ($failCount >= MAX_LOGIN_ATTEMPTS) {
                logSecurityEvent(
                    'BRUTE_FORCE',
                    'high',
                    "IP $ip locked out after $failCount failed attempts for user: $username"
                );
            }
        }
    } catch (PDOException $e) {
        error_log("Failed to record login attempt: " . $e->getMessage());
    }
}

/**
 * Get the number of recent failed login attempts from this IP.
 */
function getRecentFailedAttempts(): int {
    try {
        $pdo = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts 
             WHERE ip_address = ? 
               AND success = 0 
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$ip, ATTEMPT_WINDOW_MINUTES]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Failed to check login attempts: " . $e->getMessage());
        return 0; // Fail open — don't lock out on DB errors
    }
}

/**
 * Check if the current IP is locked out.
 * Returns remaining lockout seconds, or 0 if not locked.
 */
function checkRateLimit(): int {
    try {
        $pdo = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Count failed attempts in the window
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) as fail_count, MAX(attempted_at) as last_attempt 
             FROM login_attempts 
             WHERE ip_address = ? 
               AND success = 0 
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$ip, ATTEMPT_WINDOW_MINUTES]);
        $row = $stmt->fetch();
        
        if (!$row || (int)$row['fail_count'] < MAX_LOGIN_ATTEMPTS) {
            return 0; // Not locked out
        }
        
        // Check if lockout period has passed since last attempt
        $lastAttempt = strtotime($row['last_attempt']);
        $lockoutEnd = $lastAttempt + (LOCKOUT_DURATION_MINUTES * 60);
        $remaining = $lockoutEnd - time();
        
        return max(0, $remaining);
    } catch (PDOException $e) {
        error_log("Rate limit check failed: " . $e->getMessage());
        return 0; // Fail open
    }
}

/**
 * Clear failed attempts for an IP after successful login.
 */
function clearLoginAttempts(): void {
    try {
        $pdo = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Record a successful login to reset the counter
        $stmt = $pdo->prepare(
            'INSERT INTO login_attempts (ip_address, username, success) VALUES (?, ?, 1)'
        );
        $stmt->execute([$ip, 'success_reset']);
    } catch (PDOException $e) {
        error_log("Failed to clear login attempts: " . $e->getMessage());
    }
}

/**
 * Get remaining attempts before lockout for this IP.
 */
function getRemainingAttempts(): int {
    $failed = getRecentFailedAttempts();
    return max(0, MAX_LOGIN_ATTEMPTS - $failed);
}
