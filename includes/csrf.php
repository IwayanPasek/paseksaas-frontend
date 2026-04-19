<?php
/**
 * csrf.php — CSRF Token Generation, Validation & Rotation
 * 
 * Security features:
 * - Tokens are rotated after each successful verification
 * - Uses cryptographically secure random bytes
 * - Supports form fields, JSON bodies, and header-based tokens
 */

/**
 * Generate or retrieve the current CSRF token.
 */
function csrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Render a hidden input field for HTML forms.
 */
function csrfField(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrfToken() . '">';
}

/**
 * Verify a CSRF token from various sources.
 * 
 * On successful verification, the token is ROTATED to prevent replay attacks.
 * 
 * @param array|null $data  Optional data array to check (e.g., decoded JSON body)
 * @return bool             True if valid, false if invalid
 */
function csrfVerify(?array $data = null): bool {
    $token = $data['_csrf_token'] ?? $_POST['_csrf_token'] ?? $_GET['_csrf_token'] ?? '';
    
    // Fallback: read from JSON body if not found elsewhere
    if (empty($token) && $_SERVER['REQUEST_METHOD'] === 'POST' && empty($data)) {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['_csrf_token'] ?? '';
    }
    
    // Fallback: read from X-CSRF-Token header
    if (empty($token)) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    }
    
    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    
    if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
        // Log CSRF failure as security event (if error_handler is loaded)
        if (function_exists('logSecurityEvent')) {
            logSecurityEvent('CSRF_FAIL', 'medium', 'Token mismatch on ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
        }
        return false;
    }
    
    // Rotate token after successful verification to prevent replay attacks
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    
    return true;
}
