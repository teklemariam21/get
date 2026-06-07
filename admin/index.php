<?php
$pageTitle  = 'Dashboard';
$breadcrumb = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

$totalProps  = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$available   = $db->query("SELECT COUNT(*) FROM properties WHERE status='available'")->fetchColumn();
$sold        = $db->query("SELECT COUNT(*) FROM properties WHERE status='sold'")->fetchColumn();
$reserved    = $db->query("SELECT COUNT(*) FROM properties WHERE status='reserved'")->fetchColumn();
$totalInq    = $db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$newInq      = $db->query("SELECT COUNT(*) FROM inquiries WHERE status='new'")->fetchColumn();
$cg1cnt      = $db->query("SELECT COUNT(*) FROM properties WHERE tower='City Gate 1'")->fetchColumn();
$cg2cnt      = $db->query("SELECT COUNT(*) FROM properties WHERE tower='City Gate 2'")->fetchColumn();
$cg3cnt      = $db->query("SELECT COUNT(*) FROM properties WHERE tower='City Gate 3'")->fetchColumn();
$totalValue  = $db->query("SELECT SUM(total_price) FROM properties WHERE status='available'")->fetchColumn();
$soldValue   = $db->query("SELECT SUM(total_price) FROM properties WHERE status='sold'")->fetchColumn();

$recentInq   = $db->query("SELECT i.*, p.title as prop_title FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id ORDER BY i.created_at DESC LIMIT 8")->fetchAll();
$byType      = $db->query("SELECT bedrooms, COUNT(*) as cnt, SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as avail FROM properties GROUP BY bedrooms ORDER BY bedrooms")->fetchAll();
?>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-buildings-fill"></i></div>
      <div><div class="stat-value"><?= $totalProps ?></div><div class="stat-title">Total Units</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-check-circle-fill"></i></div>
      <div><div class="stat-value"><?= $available ?></div><div class="stat-title">Available</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fef3c7;color:#b45309"><i class="bi bi-inbox-fill"></i></div>
      <div><div class="stat-value"><?= $totalInq ?></div><div class="stat-title">Total Inquiries</div></div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fee2e2;color:#b91c1c"><i class="bi bi-bell-fill"></i></div>
      <div><div class="stat-value"><?= $newInq ?></div><div class="stat-title">New Inquiries</div></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <!-- Tower overview -->
    <div class="form-card mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0" style="color:var(--dark)">City Gate Towers</h6>
        <a href="properties.php" class="btn btn-sm btn-outline-gold">Manage Units</a>
      </div>
      <div class="row g-3">
        <?php
        $towerCards = [
          ['City Gate 1','Wing 1',$cg1cnt,'#dbeafe','#1e40af'],
          ['City Gate 2','Wing 2',$cg2cnt,'var(--gold-bg)','var(--gold-dark)'],
          ['City Gate 3','Wing 3',$cg3cnt,'#dcfce7','#166534'],
        ];
        foreach ($towerCards as $tc): ?>
        <div class="col-md-4">
          <div class="p-3 rounded-xl" style="background:<?= $tc[3] ?>;border:1px solid <?= $tc[4] ?>22">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="fw-700 mb-0" style="color:var(--dark)"><?= $tc[0] ?></h6>
              <span class="badge" style="background:<?= $tc[4] ?>;color:#fff"><?= $tc[2] ?> units</span>
            </div>
            <div class="small text-muted"><?= $tc[1] ?></div>
            <a href="properties.php?tower=<?= urlencode($tc[0]) ?>" class="btn btn-sm mt-2" style="background:<?= $tc[4] ?>;color:#fff;border-radius:6px;font-size:0.78rem;padding:0.25rem 0.75rem">View Units</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="mt-3">
        <table class="table table-sm small mb-0">
          <thead style="background:#f9fafb"><tr><th class="small fw-600 text-muted">Bedrooms</th><th>Total</th><th>Available</th><th>Sold/Reserved</th></tr></thead>
          <tbody>
          <?php foreach ($byType as $bt): ?>
          <tr>
            <td><span class="badge bg-dark"><?= $bt['bedrooms'] ?>BR</span></td>
            <td><?= $bt['cnt'] ?></td>
            <td><span class="status-badge status-available"><?= $bt['avail'] ?></span></td>
            <td><span class="status-badge status-sold"><?= $bt['cnt']-$bt['avail'] ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent inquiries -->
    <div class="form-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0" style="color:var(--dark)">Recent Inquiries</h6>
        <a href="inquiries.php" class="btn btn-sm btn-outline-gold">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead style="background:#f9fafb"><tr><th>Name</th><th class="d-none d-md-table-cell">Tower Interest</th><th class="d-none d-sm-table-cell">Date</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($recentInq as $inq): ?>
          <tr>
            <td><div class="fw-600 small"><?= htmlspecialchars($inq['name']) ?></div><div class="text-muted" style="font-size:0.72rem"><?= htmlspecialchars($inq['phone']) ?></div></td>
            <td class="small text-muted d-none d-md-table-cell"><?= $inq['tower'] ? htmlspecialchars($inq['tower']) : ($inq['prop_title'] ? htmlspecialchars(substr($inq['prop_title'],0,30)) : '<em>General</em>') ?></td>
            <td class="small text-muted d-none d-sm-table-cell"><?= date('M j', strtotime($inq['created_at'])) ?></td>
            <td><span class="status-badge status-<?= $inq['status'] ?>"><?= ucfirst($inq['status']) ?></span></td>
            <td><a href="inquiries.php?id=<?= $inq['id'] ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <!-- Quick actions -->
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3" style="color:var(--dark)">Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="property-add.php" class="btn btn-gold"><i class="bi bi-plus-circle me-2"></i>Add New Unit</a>
        <a href="inquiries.php?status=new" class="btn btn-outline-gold">
          <i class="bi bi-inbox me-2"></i>New Inquiries
          <?php if ($newInq>0): ?><span class="badge ms-1" style="background:var(--gold)"><?= $newInq ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/index.php" target="_blank" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-2"></i>View Website</a>
      </div>
    </div>

    <!-- Status breakdown -->
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3" style="color:var(--dark)">Unit Status</h6>
      <?php
      foreach ([['Available',$available,'#166534'],['Reserved',$reserved,'#92400e'],['Sold',$sold,'#991b1b']] as $s):
        $pct = $totalProps>0 ? round(($s[1]/$totalProps)*100) : 0;
      ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between small mb-1"><span class="fw-600"><?= $s[0] ?></span><span><?= $s[1] ?> (<?= $pct ?>%)</span></div>
        <div class="progress" style="height:7px;border-radius:50px;background:#f3f4f6">
          <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $s[2] ?>;border-radius:50px"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Inventory value -->
    <div class="form-card">
      <h6 class="fw-700 mb-3" style="color:var(--dark)">Inventory Value</h6>
      <div class="mb-3 p-3 rounded-xl" style="background:var(--gold-bg);border:1px solid var(--gold-light)">
        <div class="small text-muted mb-1">Available Units</div>
        <div class="fw-800" style="font-size:1.1rem;color:var(--gold)">ETB <?= number_format((float)$totalValue) ?></div>
      </div>
      <div class="p-3 rounded-xl" style="background:#f0fdf4;border:1px solid #bbf7d0">
        <div class="small text-muted mb-1">Sold Units</div>
        <div class="fw-800" style="font-size:1.1rem;color:#16a34a">ETB <?= number_format((float)$soldValue) ?></div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
