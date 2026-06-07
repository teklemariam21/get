<?php
require_once dirname(__DIR__, 2) . '/includes/functions.php';

function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function getAdmin(): ?array {
    if (!isAdminLoggedIn()) return null;
    static $admin = null;
    if ($admin === null) {
        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();
    }
    return $admin ?: null;
}
