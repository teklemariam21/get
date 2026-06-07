<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/browse.php');

$sent   = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf($_POST['csrf'] ?? '')) {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $token   = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $db->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?")->execute([$token, $expires, $user['id']]);
            // In production, send email. For demo, show link.
            $_SESSION['reset_link'] = SITE_URL . '/reset-password.php?token=' . $token;
        }
        $sent = true; // Always show success to prevent email enumeration
    }
}

$pageTitle = 'Forgot Password';
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
                        <div class="logo-icon mx-auto mb-3"><i class="bi bi-key-fill"></i></div>
                        <h4 class="fw-800">Forgot Password?</h4>
                        <p class="text-muted small">Enter your email and we'll send you a reset link</p>
                    </div>

                    <?php if ($sent): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        If an account with that email exists, a reset link has been sent.
                    </div>
                    <?php if (isset($_SESSION['reset_link'])): ?>
                    <div class="alert alert-info small">
                        <strong>Demo mode:</strong> <a href="<?= $_SESSION['reset_link'] ?>">Click here to reset your password</a>
                    </div>
                    <?php unset($_SESSION['reset_link']); endif; ?>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-primary w-100">Back to Login</a>
                    <?php else: ?>
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small"><?= htmlspecialchars($errors[0]) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                        <div class="mb-4">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="you@email.com" autofocus required>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-700">Send Reset Link</button>
                        </div>
                    </form>
                    <div class="text-center mt-3 small text-muted">
                        <a href="<?= SITE_URL ?>/login.php" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Back to Login</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
