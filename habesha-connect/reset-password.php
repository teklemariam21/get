<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/browse.php');

$token  = trim($_GET['token'] ?? '');
$errors = [];
$done   = false;

$db   = getDB();
$stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW() AND is_active = 1 LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$token || !$user) {
    flash('error', 'Invalid or expired reset link. Please request a new one.');
    redirect(SITE_URL . '/forgot-password.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)  $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $db->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?")->execute([hashPassword($password), $user['id']]);
        $done = true;
    }
}

$pageTitle = 'Reset Password';
$bodyClass = 'page-auth bg-light';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="min-vh-80 d-flex align-items-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <div class="logo-icon mx-auto mb-3"><i class="bi bi-lock-fill"></i></div>
                        <h4 class="fw-800">Set New Password</h4>
                    </div>
                    <?php if ($done): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i>Password changed! You can now log in.</div>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary w-100 fw-700">Go to Login</a>
                    <?php else: ?>
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($errors[0]) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                            <div class="password-strength strength-0 mt-1" id="passwordStrength"></div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-700">Reset Password</button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
