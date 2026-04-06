<?php
/**
 * auth.php — Authentication guard helpers.
 */

function requireTenant(): int {
    if (empty($_SESSION['tenant_id'])) {
        header('Location: login.php');
        exit;
    }
    return (int) $_SESSION['tenant_id'];
}

function requireMaster(): void {
    if (empty($_SESSION['master_logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function isLoggedIn(): bool {
    return isset($_SESSION['role']);
}
