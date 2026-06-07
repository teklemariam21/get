<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            header('Location: ' . SITE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Hitechcomputer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-login-wrap">
    <div class="login-card glass-card">
        <div class="text-center mb-4">
            <div style="font-size:2.5rem;color:var(--cyan);margin-bottom:0.5rem;filter:drop-shadow(0 0 10px var(--cyan))">
                <i class="fas fa-microchip"></i>
            </div>
            <h4 style="font-family:'Orbitron',sans-serif">Hitech<span style="color:var(--cyan)">Computer</span></h4>
            <p style="color:var(--text-muted);font-size:0.85rem;margin:0">Admin Control Panel</p>
        </div>

        <?php if ($error): ?>
        <div class="alert-glass error mb-3">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="position-relative">
                    <input type="text" class="form-control ps-4" name="username" placeholder="admin" autocomplete="username" required>
                    <i class="fas fa-user position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.85rem"></i>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="position-relative">
                    <input type="password" class="form-control ps-4" name="password" placeholder="••••••••" autocomplete="current-password" required id="pwd-input">
                    <i class="fas fa-lock position-absolute" style="left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.85rem"></i>
                    <i class="fas fa-eye position-absolute" id="pwd-toggle" style="right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);cursor:pointer;font-size:0.85rem"></i>
                </div>
            </div>
            <button type="submit" class="btn-hero-primary w-100 justify-content-center">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <p class="text-center mt-3" style="font-size:0.8rem;color:var(--text-muted)">
            Default: admin / password
        </p>
        <div class="text-center mt-2">
            <a href="<?= SITE_URL ?>/" style="color:var(--text-muted);font-size:0.8rem;text-decoration:none">
                <i class="fas fa-arrow-left me-1"></i>Back to Website
            </a>
        </div>
    </div>
</div>
<script>
document.getElementById('pwd-toggle').addEventListener('click', function() {
    const input = document.getElementById('pwd-input');
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    this.className = `fas fa-${isPass ? 'eye-slash' : 'eye'} position-absolute`;
    this.style.cssText = 'right:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);cursor:pointer;font-size:0.85rem';
});
</script>
</body>
</html>
