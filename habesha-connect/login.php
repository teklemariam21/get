<?php
require_once 'includes/functions.php';
if (isLoggedIn()) redirect(SITE_URL . '/browse.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = 'Please enter your email and password.';
        } else {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !verifyPassword($password, $user['password'])) {
                $errors[] = 'Incorrect email or password.';
            } elseif ($user['is_banned']) {
                $errors[] = 'Your account has been suspended. Reason: ' . ($user['ban_reason'] ?: 'Violation of terms.');
            } elseif (!$user['is_active']) {
                $errors[] = 'Your account is not active. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $db->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$user['id']]);
                $redirect = $_GET['redirect'] ?? (SITE_URL . '/browse.php');
                // Safety check – only allow relative redirects
                if (!str_starts_with($redirect, SITE_URL)) $redirect = SITE_URL . '/browse.php';
                redirect($redirect);
            }
        }
    }
}

$pageTitle = 'Login';
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
                        <div class="logo-icon mx-auto mb-3"><i class="bi bi-heart-fill"></i></div>
                        <h4 class="fw-800">Welcome Back</h4>
                        <p class="text-muted small">Sign in to your HabeshaConnect account</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger py-2 small">
                        <i class="bi bi-x-circle me-1"></i><?= htmlspecialchars($errors[0]) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST">
                        <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" name="email"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="you@email.com" autofocus required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label d-flex justify-content-between">
                                Password
                                <a href="<?= SITE_URL ?>/forgot-password.php" class="text-success small fw-500">Forgot password?</a>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="Your password" required>
                                <button type="button" class="btn btn-outline-secondary" id="togglePwd" tabindex="-1">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="remember">
                            <label class="form-check-label small" for="rememberMe">Keep me signed in</label>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg fw-700">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </div>
                    </form>

                    <!-- Demo credentials -->
                    <div class="alert alert-info py-2 small mb-0">
                        <strong>Demo:</strong> selam@demo.com / password
                    </div>

                    <div class="text-center mt-3 pt-3 border-top small text-muted">
                        Don't have an account? <a href="<?= SITE_URL ?>/register.php" class="text-success fw-600">Join Free</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
<script>
document.getElementById('togglePwd')?.addEventListener('click', function() {
    const pwd  = document.querySelector('input[name="password"]');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') { pwd.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { pwd.type = 'password'; icon.className = 'bi bi-eye'; }
});
</script>
