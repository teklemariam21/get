<?php
$pageTitle  = 'Dashboard';
$breadcrumb = 'Dashboard';
require_once __DIR__ . '/includes/admin_header.php';

// Stats
$totalProps   = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$available    = $db->query("SELECT COUNT(*) FROM properties WHERE status='available'")->fetchColumn();
$sold         = $db->query("SELECT COUNT(*) FROM properties WHERE status='sold'")->fetchColumn();
$reserved     = $db->query("SELECT COUNT(*) FROM properties WHERE status='reserved'")->fetchColumn();
$totalInq     = $db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$newInq       = $db->query("SELECT COUNT(*) FROM inquiries WHERE status='new'")->fetchColumn();
$summit72cnt  = $db->query("SELECT COUNT(*) FROM properties WHERE site='Summit 72'")->fetchColumn();
$kazanchisCnt = $db->query("SELECT COUNT(*) FROM properties WHERE site='Kazanchis'")->fetchColumn();

// Recent inquiries
$recentInq = $db->query("SELECT i.*, p.title as prop_title FROM inquiries i LEFT JOIN properties p ON i.property_id=p.id ORDER BY i.created_at DESC LIMIT 8")->fetchAll();

// Properties by type
$byType = $db->query("SELECT type, COUNT(*) as cnt, SUM(CASE WHEN status='available' THEN 1 ELSE 0 END) as avail FROM properties GROUP BY type ORDER BY type")->fetchAll();
?>

<!-- Stats row -->
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8"><i class="bi bi-buildings-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalProps ?></div>
        <div class="stat-title">Total Apartments</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#dcfce7;color:#15803d"><i class="bi bi-check-circle-fill"></i></div>
      <div>
        <div class="stat-value"><?= $available ?></div>
        <div class="stat-title">Available</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fef3c7;color:#b45309"><i class="bi bi-inbox-fill"></i></div>
      <div>
        <div class="stat-value"><?= $totalInq ?></div>
        <div class="stat-title">Total Inquiries</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fee2e2;color:#b91c1c"><i class="bi bi-bell-fill"></i></div>
      <div>
        <div class="stat-value"><?= $newInq ?></div>
        <div class="stat-title">New Inquiries</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Left column -->
  <div class="col-lg-8">
    <!-- Site overview -->
    <div class="form-card mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0" style="color:var(--dark)">Site Overview</h6>
        <a href="properties.php" class="btn btn-sm btn-outline-gold">Manage</a>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <div style="background:var(--gold-bg);border-radius:12px;padding:1.25rem;border:1px solid var(--gold-light)">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="fw-700 mb-0" style="color:var(--dark)"><i class="bi bi-buildings me-2 text-gold"></i>Summit 72</h6>
              <span class="badge" style="background:var(--gold);color:#fff"><?= $summit72cnt ?> units</span>
            </div>
            <div class="small text-muted">Bole, Addis Ababa</div>
            <a href="properties.php?site=Summit+72" class="btn btn-sm btn-gold mt-2">View Units</a>
          </div>
        </div>
        <div class="col-md-6">
          <div style="background:#f0f9ff;border-radius:12px;padding:1.25rem;border:1px solid #bae6fd">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h6 class="fw-700 mb-0" style="color:var(--dark)"><i class="bi bi-geo-alt me-2" style="color:#0284c7"></i>Kazanchis</h6>
              <span class="badge" style="background:#0284c7;color:#fff"><?= $kazanchisCnt ?> units</span>
            </div>
            <div class="small text-muted">Kirkos, Addis Ababa</div>
            <a href="properties.php?site=Kazanchis" class="btn btn-sm btn-sm mt-2" style="background:#0284c7;color:#fff;border:none;border-radius:6px;padding:0.25rem 0.75rem;font-size:0.8rem">View Units</a>
          </div>
        </div>
      </div>

      <!-- By type table -->
      <div class="mt-3">
        <table class="table table-sm mb-0">
          <thead style="background:#f9fafb">
            <tr>
              <th class="small fw-600 text-muted">Type</th>
              <th class="small fw-600 text-muted">Total</th>
              <th class="small fw-600 text-muted">Available</th>
              <th class="small fw-600 text-muted">Sold/Reserved</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($byType as $bt): ?>
            <tr>
              <td><span class="badge bg-dark"><?= htmlspecialchars($bt['type']) ?></span></td>
              <td><?= $bt['cnt'] ?></td>
              <td><span class="status-badge status-available"><?= $bt['avail'] ?></span></td>
              <td><span class="status-badge status-sold"><?= $bt['cnt'] - $bt['avail'] ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Inquiries -->
    <div class="form-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-700 mb-0" style="color:var(--dark)">Recent Inquiries</h6>
        <a href="inquiries.php" class="btn btn-sm btn-outline-gold">View All</a>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
          <thead style="background:#f9fafb">
            <tr>
              <th class="small fw-600 text-muted">Name</th>
              <th class="small fw-600 text-muted d-none d-md-table-cell">Property</th>
              <th class="small fw-600 text-muted d-none d-sm-table-cell">Date</th>
              <th class="small fw-600 text-muted">Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentInq as $inq): ?>
            <tr>
              <td>
                <div class="fw-600 small"><?= htmlspecialchars($inq['name']) ?></div>
                <div class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars($inq['phone']) ?></div>
              </td>
              <td class="small text-muted d-none d-md-table-cell">
                <?= $inq['prop_title'] ? htmlspecialchars(substr($inq['prop_title'],0,35)).'...' : '<em>General</em>' ?>
              </td>
              <td class="small text-muted d-none d-sm-table-cell">
                <?= date('M j', strtotime($inq['created_at'])) ?>
              </td>
              <td><span class="status-badge status-<?= $inq['status'] ?>"><?= ucfirst($inq['status']) ?></span></td>
              <td><a href="inquiries.php?id=<?= $inq['id'] ?>" class="btn btn-sm btn-light"><i class="bi bi-eye"></i></a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Right column -->
  <div class="col-lg-4">
    <!-- Quick Actions -->
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3" style="color:var(--dark)">Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="property-add.php" class="btn btn-gold"><i class="bi bi-plus-circle me-2"></i>Add New Apartment</a>
        <a href="inquiries.php?status=new" class="btn btn-outline-gold">
          <i class="bi bi-inbox me-2"></i>New Inquiries
          <?php if ($newInq > 0): ?><span class="badge ms-1" style="background:var(--gold)"><?= $newInq ?></span><?php endif; ?>
        </a>
        <a href="<?= SITE_URL ?>/properties.php" target="_blank" class="btn btn-outline-secondary">
          <i class="bi bi-box-arrow-up-right me-2"></i>View Public Site
        </a>
      </div>
    </div>

    <!-- Property Status Breakdown -->
    <div class="form-card mb-4">
      <h6 class="fw-700 mb-3" style="color:var(--dark)">Property Status</h6>
      <?php
      $statuses = [
        ['available','Available', $available, '#dcfce7','#166534'],
        ['reserved','Reserved', $reserved, '#fef3c7','#92400e'],
        ['sold','Sold', $sold, '#fee2e2','#991b1b'],
      ];
      foreach ($statuses as $s):
        $pct = $totalProps > 0 ? round(($s[2] / $totalProps) * 100) : 0;
      ?>
      <div class="mb-3">
        <div class="d-flex justify-content-between small mb-1">
          <span class="fw-600" style="color:var(--dark)"><?= $s[1] ?></span>
          <span class="fw-600"><?= $s[2] ?> (<?= $pct ?>%)</span>
        </div>
        <div class="progress" style="height:8px;border-radius:50px;background:#f3f4f6">
          <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $s[4] ?>;border-radius:50px"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Revenue Snapshot -->
    <div class="form-card">
      <h6 class="fw-700 mb-3" style="color:var(--dark)">Inventory Value</h6>
      <?php
      $totalValue = $db->query("SELECT SUM(price) FROM properties WHERE status='available'")->fetchColumn();
      $soldValue  = $db->query("SELECT SUM(price) FROM properties WHERE status='sold'")->fetchColumn();
      ?>
      <div class="mb-3 p-3 rounded-xl" style="background:var(--gold-bg);border:1px solid var(--gold-light)">
        <div class="small text-muted mb-1">Available Inventory</div>
        <div class="fw-800" style="font-size:1.2rem;color:var(--gold)"><?= formatPrice((float)$totalValue) ?></div>
      </div>
      <div class="mb-0 p-3 rounded-xl" style="background:#f0fdf4;border:1px solid #bbf7d0">
        <div class="small text-muted mb-1">Sold Units Value</div>
        <div class="fw-800" style="font-size:1.2rem;color:#16a34a"><?= formatPrice((float)$soldValue) ?></div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
