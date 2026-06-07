<?php
/**
 * Run this file once to set/reset admin password.
 * Delete after use.
 * URL: http://localhost/get/setup-password.php?pass=YourNewPassword
 */
require_once __DIR__ . '/config/db.php';

if (!isset($_GET['pass']) || strlen($_GET['pass']) < 6) {
    die('Provide a password via ?pass=YourNewPassword (min 6 chars)');
}

$hash = password_hash($_GET['pass'], PASSWORD_DEFAULT);
$pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$hash]);

echo "<h2 style='font-family:monospace;color:green'>Password updated!</h2>";
echo "<p>New hash: <code>$hash</code></p>";
echo "<p><strong>Delete this file now!</strong></p>";
echo "<p><a href='" . SITE_URL . "/admin/login.php'>Go to Admin Login</a></p>";
