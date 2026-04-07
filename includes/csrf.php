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

/**
 * Verify a CSRF token. 
 * If $data is provided, it checks there. Otherwise it checks $_POST/$_GET.
 */
function csrfVerify(?array $data = null): bool {
    $token = $data['_csrf_token'] ?? $_POST['_csrf_token'] ?? $_GET['_csrf_token'] ?? '';
    
    // Support JSON body fallback if $data was empty
    if (empty($token) && $_SERVER['REQUEST_METHOD'] === 'POST' && empty($data)) {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['_csrf_token'] ?? '';
    }

    if (empty($token) || !hash_equals($_SESSION['_csrf_token'] ?? '', $token)) {
        return false;
    }
    return true;
}
