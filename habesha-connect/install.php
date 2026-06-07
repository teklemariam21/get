<?php
/**
 * Habesha Connect - Installation Wizard
 * Run this once to set up the database. Delete this file after installation.
 */

define('INSTALL_MODE', true);
$step    = (int)($_POST['step'] ?? $_GET['step'] ?? 1);
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int)($_POST['step'] ?? 1);

    if ($step === 1) {
        // Test DB connection
        $host = trim($_POST['db_host'] ?? 'localhost');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_pass'] ?? '';
        $name = trim($_POST['db_name'] ?? '');

        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");

            // Write config
            $configContent = <<<PHP
<?php
define('DB_HOST', '$host');
define('DB_USER', '$user');
define('DB_PASS', '$pass');
define('DB_NAME', '$name');
define('DB_CHARSET', 'utf8mb4');
define('SITE_NAME', 'Habesha Connect');
define('SITE_URL', '{$_POST['site_url']}');
define('SITE_EMAIL', '{$_POST['admin_email']}');
define('UPLOAD_PATH', __DIR__ . '/../uploads/profiles/');
define('UPLOAD_URL', SITE_URL . '/uploads/profiles/');
define('MAX_PHOTO_SIZE', 5 * 1024 * 1024);
define('ALLOWED_PHOTO_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('SESSION_NAME', 'habesha_session');
define('SESSION_LIFETIME', 86400 * 30);
date_default_timezone_set('Africa/Addis_Ababa');
error_reporting(0);
ini_set('display_errors', 0);

function getDB(): PDO {
    static \$pdo = null;
    if (\$pdo === null) {
        \$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return \$pdo;
}

if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
PHP;

            // Import SQL schema
            $sql = file_get_contents(__DIR__ . '/database.sql');
            $pdo->exec($sql);

            // Create admin user
            $adminEmail = trim($_POST['admin_email'] ?? 'admin@habeshaconnect.com');
            $adminPass  = password_hash($_POST['admin_password'] ?? 'Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("INSERT INTO admins (username, email, password, role) VALUES (?,?,?,'superadmin') ON DUPLICATE KEY UPDATE password=?")->execute(['admin', $adminEmail, $adminPass, $adminPass]);

            $_SESSION['install_config'] = $configContent;
            $_SESSION['install_done']   = true;
            $step = 2;
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install HabeshaConnect</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg,#1a6e3c,#0d3a1f); min-height:100vh; font-family:'Segoe UI',sans-serif; display:flex; align-items:center; justify-content:center; }
        .install-card { background:#fff; border-radius:16px; padding:2.5rem; width:100%; max-width:560px; box-shadow:0 20px 60px rgba(0,0,0,.3); }
        .step-icon { width:56px;height:56px;background:linear-gradient(135deg,#1a6e3c,#13512c);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:#fff; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="install-card mx-auto">
        <?php if ($step === 2 && !empty($_SESSION['install_done'])): ?>
        <!-- Step 2: Done -->
        <div class="text-center">
            <div class="step-icon"><i class="bi bi-check-lg"></i></div>
            <h4 class="fw-800 mb-2">Installation Complete! 🎉</h4>
            <p class="text-muted mb-4">Your HabeshaConnect dating platform is ready.</p>

            <?php if (isset($_SESSION['install_config'])): ?>
            <div class="alert alert-warning text-start">
                <strong>Important:</strong> Copy the configuration below and save it as <code>includes/config.php</code>, replacing the existing file.
            </div>
            <div class="position-relative">
                <pre class="bg-dark text-success p-3 rounded-3 text-start" style="font-size:.72rem;max-height:200px;overflow:auto"><?= htmlspecialchars($_SESSION['install_config']) ?></pre>
                <button class="btn btn-sm btn-outline-secondary position-absolute top-0 end-0 m-2"
                        onclick="navigator.clipboard.writeText(document.querySelector('pre').textContent)">Copy</button>
            </div>
            <?php unset($_SESSION['install_config']); unset($_SESSION['install_done']); ?>
            <?php endif; ?>

            <div class="alert alert-danger text-start mt-3">
                <i class="bi bi-shield-exclamation me-2"></i>
                <strong>Security:</strong> Delete <code>install.php</code> immediately after setup!
            </div>

            <div class="d-grid gap-2 mt-3">
                <a href="/" class="btn btn-primary fw-700"><i class="bi bi-house me-2"></i>Go to Homepage</a>
                <a href="/admin/" class="btn btn-outline-success fw-700"><i class="bi bi-speedometer2 me-2"></i>Go to Admin Panel</a>
            </div>
        </div>

        <?php else: ?>
        <!-- Step 1: Database Setup -->
        <div class="text-center mb-4">
            <div class="step-icon"><i class="bi bi-server"></i></div>
            <h4 class="fw-800">Install HabeshaConnect</h4>
            <p class="text-muted small">Ethiopian Dating Platform Setup Wizard</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errors[0]) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="step" value="1">
            <h6 class="fw-700 text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.5px">Database Configuration</h6>
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <label class="form-label fw-600 small">DB Host</label>
                    <input type="text" class="form-control" name="db_host" value="localhost" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-600 small">DB Name</label>
                    <input type="text" class="form-control" name="db_name" value="habesha_connect" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-600 small">DB Username</label>
                    <input type="text" class="form-control" name="db_user" value="root" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-600 small">DB Password</label>
                    <input type="text" class="form-control" name="db_pass" value="">
                </div>
            </div>
            <h6 class="fw-700 text-muted text-uppercase mb-3" style="font-size:.75rem;letter-spacing:.5px">Site Configuration</h6>
            <div class="mb-3">
                <label class="form-label fw-600 small">Site URL (no trailing slash)</label>
                <input type="text" class="form-control" name="site_url" value="http://localhost/habesha-connect" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-600 small">Admin Email</label>
                <input type="email" class="form-control" name="admin_email" value="admin@habeshaconnect.com" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-600 small">Admin Password</label>
                <input type="password" class="form-control" name="admin_password" placeholder="Min 8 characters" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg fw-700">
                    <i class="bi bi-play-fill me-2"></i>Install Now
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
