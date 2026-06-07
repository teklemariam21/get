<?php
$pageTitle  = 'Users';
$breadcrumb = 'Users';
require_once __DIR__ . '/includes/admin_header.php';

// Delete
if (isset($_GET['delete']) && (int)$_GET['delete'] !== (int)$_SESSION['user_id']) {
    $db->prepare("DELETE FROM users WHERE id=:id")->execute([':id'=>(int)$_GET['delete']]);
    flash('success','User deleted.'); redirect(SITE_URL . '/admin/users.php');
}

// Add user
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name  = sanitize($_POST['name']  ?? '');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $pass  = $_POST['password'] ?? '';
    $role  = in_array($_POST['role']??'',['admin','agent','user']) ? $_POST['role'] : 'user';
    $phone = sanitize($_POST['phone'] ?? '');

    if (!$name||!$email||!$pass) $errors[] = 'Name, email and password are required.';
    elseif (strlen($pass) < 6)   $errors[] = 'Password must be at least 6 characters.';
    elseif ($db->prepare("SELECT id FROM users WHERE email=:e")->execute([':e'=>$email]) && $db->query("SELECT COUNT(*) FROM users WHERE email='$email'")->fetchColumn() > 0)
        $errors[] = 'Email already exists.';
    else {
        $db->prepare("INSERT INTO users (name,email,password,role,phone) VALUES (:n,:e,:p,:r,:ph)")->execute([':n'=>$name,':e'=>$email,':p'=>password_hash($pass,PASSWORD_DEFAULT),':r'=>$role,':ph'=>$phone]);
        flash('success',"User '$name' added."); redirect(SITE_URL . '/admin/users.php');
    }
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="row g-4">
  <div class="col-lg-7">
    <h4 class="fw-700 mb-4" style="color:var(--dark)">Users (<?= count($users) ?>)</h4>
    <div class="form-card p-0 overflow-hidden">
      <table class="table admin-table mb-0">
        <thead><tr><th>Name</th><th class="d-none d-md-table-cell">Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:32px;height:32px;background:var(--gold);font-size:0.8rem;flex-shrink:0">
                  <?= strtoupper(substr($u['name'],0,1)) ?>
                </div>
                <div>
                  <div class="fw-600 small"><?= htmlspecialchars($u['name']) ?></div>
                  <div class="text-muted" style="font-size:0.72rem"><?= htmlspecialchars($u['phone']??'') ?></div>
                </div>
              </div>
            </td>
            <td class="small text-muted d-none d-md-table-cell"><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge <?= $u['role']==='admin'?'bg-dark':($u['role']==='agent'?'':'bg-secondary') ?>" style="<?= $u['role']==='agent'?'background:var(--gold)':''; ?>"><?= ucfirst($u['role']) ?></span></td>
            <td><span class="status-badge <?= $u['status']==='active'?'status-available':'status-closed' ?>"><?= ucfirst($u['status']) ?></span></td>
            <td>
              <?php if ((int)$u['id'] !== (int)$_SESSION['user_id']): ?>
              <a href="?delete=<?= $u['id'] ?>" data-confirm="Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>?" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></a>
              <?php else: ?>
              <span class="small text-muted">(You)</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="form-card">
      <div class="form-section-title">Add New User</div>
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger mb-3"><ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>
      <form method="POST">
        <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required placeholder="Min 6 characters"></div>
        <div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" placeholder="+251 9XX XXX XXX"></div>
        <div class="mb-3">
          <label class="form-label">Role</label>
          <select name="role" class="form-select">
            <option value="admin">Admin</option>
            <option value="agent" selected>Agent</option>
            <option value="user">User</option>
          </select>
        </div>
        <button type="submit" class="btn btn-gold w-100"><i class="bi bi-person-plus me-2"></i>Add User</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
