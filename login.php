<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';

if (isAdmin()) { redirect(SITE_URL . '/admin/index.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = getDB()->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login | Getas Reality</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
  <style>
    :root { --gold:#C9A84C; }
    body { font-family:'Inter',sans-serif; background:linear-gradient(135deg,#1A1A2E 0%,#16213E 100%); min-height:100vh; display:flex; align-items:center; }
    .login-card { background:#fff; border-radius:20px; padding:2.5rem; width:100%; max-width:420px; margin:0 auto; box-shadow:0 20px 60px rgba(0,0,0,0.3); }
    .logo-icon { width:52px;height:52px;background:linear-gradient(135deg,#C9A84C,#A8872E);border-radius:14px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;margin:0 auto 1rem; }
    .form-control { border:1.5px solid #e5e7eb;border-radius:8px;padding:0.65rem 0.875rem; }
    .form-control:focus { border-color:var(--gold);box-shadow:0 0 0 3px rgba(201,168,76,0.12); }
    .btn-gold { background:var(--gold);color:#fff;border:none;border-radius:8px;font-weight:600;padding:0.7rem;transition:all 0.2s; }
    .btn-gold:hover { background:#A8872E;color:#fff;transform:translateY(-1px); }
    .badge-demo { background:#fef3c7;color:#92400e;padding:0.5rem 1rem;border-radius:8px;font-size:0.8rem;border:1px solid #fde68a; }
  </style>
</head>
<body>
<div class="container px-3">
  <div class="login-card">
    <div class="text-center mb-4">
      <div class="logo-icon"><i class="bi bi-buildings-fill"></i></div>
      <h4 style="font-family:'Playfair Display',serif;color:#1A1A2E;margin-bottom:0.25rem">Getas Reality</h4>
      <p class="text-muted small">Admin Panel Login</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-sm mb-3" style="border-radius:8px;border:none;background:#fee2e2;color:#991b1b;padding:0.65rem 1rem;font-size:0.875rem">
      <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label fw-600 small">Email Address</label>
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
          <input type="email" name="email" class="form-control border-start-0" placeholder="admin@getasreality.com" required
                 value="<?= isset($_POST['email'])?htmlspecialchars($_POST['email']):'' ?>">
        </div>
      </div>
      <div class="mb-4">
        <label class="form-label fw-600 small">Password</label>
        <div class="input-group">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
          <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn btn-gold w-100 mb-3">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard
      </button>
    </form>

    <div class="badge-demo text-center">
      <i class="bi bi-info-circle me-1"></i>
      Demo: <code>admin@getasreality.com</code> / <code>password</code>
    </div>

    <div class="text-center mt-3">
      <a href="index.php" class="small text-muted" style="text-decoration:none">
        <i class="bi bi-arrow-left me-1"></i>Back to Website
      </a>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
