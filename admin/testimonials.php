<?php
$pageTitle  = 'Testimonials';
$breadcrumb = 'Testimonials';
require_once __DIR__ . '/includes/admin_header.php';

// Toggle approval
if (isset($_GET['toggle'])) {
    $t = $db->prepare("SELECT approved FROM testimonials WHERE id=:id");
    $t->execute([':id'=>(int)$_GET['toggle']]);
    $cur = $t->fetchColumn();
    $db->prepare("UPDATE testimonials SET approved=:a WHERE id=:id")->execute([':a'=>$cur?0:1,':id'=>(int)$_GET['toggle']]);
    redirect(SITE_URL . '/admin/testimonials.php');
}
// Delete
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM testimonials WHERE id=:id")->execute([':id'=>(int)$_GET['delete']]);
    flash('success','Testimonial deleted.'); redirect(SITE_URL . '/admin/testimonials.php');
}
// Add
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $ins = $db->prepare("INSERT INTO testimonials (name,role,message,rating,approved) VALUES (:n,:r,:m,:ra,:a)");
    $ins->execute([':n'=>sanitize($_POST['name'])??'',':r'=>sanitize($_POST['role']??''),':m'=>sanitize($_POST['message'])??'',':ra'=>(int)($_POST['rating']??5),':a'=>isset($_POST['approved'])?1:0]);
    flash('success','Testimonial added.'); redirect(SITE_URL . '/admin/testimonials.php');
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-700 mb-0" style="color:var(--dark)">Testimonials (<?= count($testimonials) ?>)</h4>
    </div>
    <div class="form-card p-0 overflow-hidden">
      <table class="table admin-table mb-0">
        <thead><tr><th>Name</th><th class="d-none d-md-table-cell">Message</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (empty($testimonials)): ?>
          <tr><td colspan="5" class="text-center py-4 text-muted">No testimonials yet.</td></tr>
          <?php else: ?>
          <?php foreach ($testimonials as $t): ?>
          <tr>
            <td>
              <div class="fw-600 small"><?= htmlspecialchars($t['name']) ?></div>
              <div class="text-muted" style="font-size:0.72rem"><?= htmlspecialchars($t['role']??'') ?></div>
            </td>
            <td class="small text-muted d-none d-md-table-cell"><?= htmlspecialchars(substr($t['message'],0,60)) ?>...</td>
            <td>
              <?= str_repeat('★', (int)$t['rating']) ?><span class="text-muted"><?= str_repeat('☆', 5-(int)$t['rating']) ?></span>
            </td>
            <td>
              <span class="status-badge <?= $t['approved']?'status-available':'status-closed' ?>">
                <?= $t['approved']?'Approved':'Hidden' ?>
              </span>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="?toggle=<?= $t['id'] ?>" class="btn btn-sm btn-light" title="<?= $t['approved']?'Hide':'Approve' ?>">
                  <i class="bi bi-<?= $t['approved']?'eye-slash':'check-circle' ?>"></i>
                </a>
                <a href="?delete=<?= $t['id'] ?>" data-confirm="Delete this testimonial?" class="btn btn-sm btn-light text-danger">
                  <i class="bi bi-trash"></i>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="form-card">
      <div class="form-section-title">Add Testimonial</div>
      <form method="POST">
        <div class="mb-3"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required placeholder="Client name"></div>
        <div class="mb-3"><label class="form-label">Role / Property</label><input type="text" name="role" class="form-control" placeholder="e.g. Purchased 2BR at Summit 72"></div>
        <div class="mb-3"><label class="form-label">Message *</label><textarea name="message" class="form-control" rows="4" required></textarea></div>
        <div class="mb-3">
          <label class="form-label">Rating</label>
          <select name="rating" class="form-select">
            <?php for ($r=5;$r>=1;$r--): ?>
            <option value="<?= $r ?>" <?= $r===5?'selected':'' ?>><?= str_repeat('★',$r) ?> (<?= $r ?>)</option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="approved" id="tapproved" checked>
          <label class="form-check-label fw-600" for="tapproved">Publish immediately</label>
        </div>
        <button type="submit" class="btn btn-gold w-100"><i class="bi bi-plus-circle me-2"></i>Add Testimonial</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
