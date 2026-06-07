<?php
require_once dirname(__DIR__) . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isAdminLoggedIn()) redirect(SITE_URL . '/admin/');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $db       = getDB();
    $stmt     = $db->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if (!$admin || !verifyPassword($password, $admin['password'])) {
        $errors[] = 'Invalid credentials.';
    } else {
        $_SESSION['admin_id'] = $admin['id'];
        $db->prepare("UPDATE admins SET last_login=NOW() WHERE id=?")->execute([$admin['id']]);
        redirect(SITE_URL . '/admin/');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | HabeshaConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body class="bg-light">
<div class="min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card border-0 shadow-lg rounded-4" style="width:360px">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <div class="logo-icon mx-auto mb-2" style="width:44px;height:44px;font-size:1.2rem"><i class="bi bi-heart-fill"></i></div>
                <h5 class="fw-800">Admin Panel</h5>
                <p class="text-muted small">HabeshaConnect Management</p>
            </div>
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger small py-2"><?= htmlspecialchars($errors[0]) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-600 small">Email</label>
                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email']??'') ?>" autofocus required>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-600 small">Password</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-700">Login to Admin</button>
            </form>
            <div class="text-center mt-3 small text-muted">
                <a href="<?= SITE_URL ?>/" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Back to site</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
