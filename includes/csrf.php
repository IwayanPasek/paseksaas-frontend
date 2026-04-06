<?php
/**
 * csrf.php — CSRF token generation and validation.
 */

function csrfToken(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrfToken() . '">';
}

function csrfVerify(): bool {
    $token = $_POST['_csrf_token'] ?? $_GET['_csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
        return false;
    }
    // Regenerate after successful verification
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    return true;
}
