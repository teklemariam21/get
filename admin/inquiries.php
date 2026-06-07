<?php
$pageTitle  = 'Inquiries';
$breadcrumb = 'Inquiries';
require_once __DIR__ . '/includes/admin_header.php';

// Handle status update
if (isset($_GET['status_update']) && isset($_GET['id'])) {
    $allowed = ['new','read','replied','closed'];
    $newStatus = $_GET['status_update'];
    if (in_array($newStatus, $allowed)) {
        $db->prepare("UPDATE inquiries SET status=:s WHERE id=:id")->execute([':s'=>$newStatus,':id'=>(int)$_GET['id']]);
    }
    redirect(SITE_URL . '/admin/inquiries.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $db->prepare("DELETE FROM inquiries WHERE id=:id")->execute([':id'=>(int)$_GET['delete']]);
    flash('success','Inquiry deleted.');
    redirect(SITE_URL . '/admin/inquiries.php');
}

// View single inquiry
$viewing = null;
if (isset($_GET['id'])) {
    $viewStmt = $db->prepare("SELECT i.*, p.title as prop_title, p.slug as prop_slug FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id WHERE i.id=:id");
    $viewStmt->execute([':id'=>(int)$_GET['id']]);
    $viewing = $viewStmt->fetch();
    // Mark as read
    if ($viewing && $viewing['status'] === 'new') {
        $db->prepare("UPDATE inquiries SET status='read' WHERE id=:id")->execute([':id'=>$viewing['id']]);
        $viewing['status'] = 'read';
    }
}

// List
$statusFilter = trim($_GET['status'] ?? '');
$where  = ['1=1'];
$params = [];
if ($statusFilter) { $where[] = 'status=:s'; $params[':s'] = $statusFilter; }
$stmtList = $db->prepare("SELECT i.*, p.title as prop_title FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id WHERE " . implode(' AND ', $where) . " ORDER BY i.created_at DESC");
$stmtList->execute($params);
$inquiries = $stmtList->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0" style="color:var(--dark)">Inquiries (<?= count($inquiries) ?>)</h4>
  <div class="d-flex gap-2">
    <a href="inquiries.php" class="btn btn-sm <?= !$statusFilter?'btn-gold':'btn-outline-secondary' ?>">All</a>
    <a href="?status=new"     class="btn btn-sm <?= $statusFilter==='new'?'btn-gold':'btn-outline-secondary' ?>">New</a>
    <a href="?status=read"    class="btn btn-sm <?= $statusFilter==='read'?'btn-gold':'btn-outline-secondary' ?>">Read</a>
    <a href="?status=replied" class="btn btn-sm <?= $statusFilter==='replied'?'btn-gold':'btn-outline-secondary' ?>">Replied</a>
  </div>
</div>

<?php if ($viewing): ?>
<!-- Viewing a single inquiry -->
<div class="form-card mb-4" style="border-left:4px solid var(--gold)">
  <div class="d-flex justify-content-between align-items-start mb-3">
    <h5 class="fw-700 mb-0" style="color:var(--dark)">Inquiry from <?= htmlspecialchars($viewing['name']) ?></h5>
    <div class="d-flex gap-2 flex-wrap">
      <a href="?id=<?= $viewing['id'] ?>&status_update=replied" class="btn btn-sm btn-outline-gold">Mark Replied</a>
      <a href="?id=<?= $viewing['id'] ?>&status_update=closed" class="btn btn-sm btn-outline-secondary">Close</a>
      <a href="inquiries.php" class="btn btn-sm btn-light"><i class="bi bi-x-lg"></i></a>
    </div>
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <div class="small text-muted fw-600 mb-1">Name</div>
      <div class="fw-600"><?= htmlspecialchars($viewing['name']) ?></div>
    </div>
    <div class="col-md-4">
      <div class="small text-muted fw-600 mb-1">Email</div>
      <a href="mailto:<?= htmlspecialchars($viewing['email']) ?>" class="text-decoration-none" style="color:var(--gold)"><?= htmlspecialchars($viewing['email']) ?></a>
    </div>
    <div class="col-md-4">
      <div class="small text-muted fw-600 mb-1">Phone</div>
      <a href="tel:<?= htmlspecialchars($viewing['phone']) ?>" class="text-decoration-none" style="color:var(--gold)"><?= htmlspecialchars($viewing['phone']) ?></a>
    </div>
    <?php if ($viewing['prop_title']): ?>
    <div class="col-md-8">
      <div class="small text-muted fw-600 mb-1">Property</div>
      <a href="<?= SITE_URL ?>/property.php?slug=<?= urlencode($viewing['prop_slug']) ?>" target="_blank" class="text-decoration-none fw-600" style="color:var(--dark)"><?= htmlspecialchars($viewing['prop_title']) ?> <i class="bi bi-box-arrow-up-right ms-1 small"></i></a>
    </div>
    <?php endif; ?>
    <div class="col-md-4">
      <div class="small text-muted fw-600 mb-1">Received</div>
      <div class="small"><?= date('M j, Y g:i A', strtotime($viewing['created_at'])) ?></div>
    </div>
    <div class="col-12">
      <div class="small text-muted fw-600 mb-1">Message</div>
      <div class="p-3 rounded-xl" style="background:#f9fafb;border:1px solid #e5e7eb;white-space:pre-wrap;font-size:0.9rem"><?= htmlspecialchars($viewing['message']) ?></div>
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <a href="mailto:<?= htmlspecialchars($viewing['email']) ?>?subject=Re: Your Apartment Enquiry at Getas Reality&body=Dear <?= urlencode($viewing['name']) ?>," class="btn btn-gold">
      <i class="bi bi-envelope me-2"></i>Reply via Email
    </a>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/','',$viewing['phone']) ?>" target="_blank" class="btn" style="background:#25D366;color:#fff">
      <i class="bi bi-whatsapp me-2"></i>WhatsApp
    </a>
  </div>
</div>
<?php endif; ?>

<!-- Inquiries table -->
<div class="form-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table admin-table mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th class="d-none d-md-table-cell">Contact</th>
          <th class="d-none d-lg-table-cell">Property</th>
          <th class="d-none d-sm-table-cell">Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($inquiries)): ?>
        <tr><td colspan="6" class="text-center py-4 text-muted">No inquiries found.</td></tr>
        <?php else: ?>
        <?php foreach ($inquiries as $inq): ?>
        <tr class="<?= $inq['status']==='new'?'table-warning':'' ?>">
          <td>
            <div class="fw-600 small"><?= htmlspecialchars($inq['name']) ?></div>
            <?php if ($inq['status']==='new'): ?>
            <span class="badge" style="background:var(--gold);font-size:0.6rem">NEW</span>
            <?php endif; ?>
          </td>
          <td class="small d-none d-md-table-cell">
            <div><?= htmlspecialchars($inq['email']) ?></div>
            <div class="text-muted"><?= htmlspecialchars($inq['phone']) ?></div>
          </td>
          <td class="small text-muted d-none d-lg-table-cell">
            <?= $inq['prop_title'] ? htmlspecialchars(substr($inq['prop_title'],0,40)) : '<em>General</em>' ?>
          </td>
          <td class="small text-muted d-none d-sm-table-cell">
            <?= date('M j, Y', strtotime($inq['created_at'])) ?>
          </td>
          <td><span class="status-badge status-<?= $inq['status'] ?>"><?= ucfirst($inq['status']) ?></span></td>
          <td>
            <div class="d-flex gap-1">
              <a href="?id=<?= $inq['id'] ?>" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
              <a href="mailto:<?= htmlspecialchars($inq['email']) ?>" class="btn btn-sm btn-light" title="Email"><i class="bi bi-envelope"></i></a>
              <a href="?delete=<?= $inq['id'] ?>" data-confirm="Delete this inquiry?" class="btn btn-sm btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
