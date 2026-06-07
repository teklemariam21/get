<?php
$pageTitle  = 'Apartments';
$breadcrumb = 'Apartments';
require_once __DIR__ . '/includes/admin_header.php';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM properties WHERE id=:id")->execute([':id'=>(int)$_GET['delete']]);
    flash('success','Apartment deleted successfully.');
    redirect(SITE_URL . '/admin/properties.php');
}

// Filters
$site   = trim($_GET['site']   ?? '');
$type   = trim($_GET['type']   ?? '');
$status = trim($_GET['status'] ?? '');
$search = trim($_GET['q']      ?? '');

$where  = ['1=1'];
$params = [];
if ($site)   { $where[] = 'site=:site';     $params[':site']   = $site; }
if ($type)   { $where[] = 'type=:type';     $params[':type']   = $type; }
if ($status) { $where[] = 'status=:status'; $params[':status'] = $status; }
if ($search) { $where[] = 'title LIKE :q';  $params[':q']      = "%$search%"; }

$sql   = "SELECT * FROM properties WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
$stmt  = $db->prepare($sql);
$stmt->execute($params);
$props = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h4 class="fw-700 mb-0" style="color:var(--dark)">Apartments</h4>
    <p class="text-muted small mb-0"><?= count($props) ?> units total</p>
  </div>
  <a href="property-add.php" class="btn btn-gold">
    <i class="bi bi-plus-circle me-2"></i>Add Apartment
  </a>
</div>

<!-- Filter bar -->
<div class="form-card mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-6 col-md-3">
      <label class="form-label small fw-600">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search title..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-600">Site</label>
      <select name="site" class="form-select form-select-sm">
        <option value="">All Sites</option>
        <option value="Summit 72" <?= $site==='Summit 72'?'selected':'' ?>>Summit 72</option>
        <option value="Kazanchis" <?= $site==='Kazanchis'?'selected':'' ?>>Kazanchis</option>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-600">Type</label>
      <select name="type" class="form-select form-select-sm">
        <option value="">All Types</option>
        <option value="1BR" <?= $type==='1BR'?'selected':'' ?>>1BR</option>
        <option value="2BR" <?= $type==='2BR'?'selected':'' ?>>2BR</option>
        <option value="3BR" <?= $type==='3BR'?'selected':'' ?>>3BR</option>
      </select>
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label small fw-600">Status</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">All Status</option>
        <option value="available" <?= $status==='available'?'selected':'' ?>>Available</option>
        <option value="reserved"  <?= $status==='reserved'?'selected':'' ?>>Reserved</option>
        <option value="sold"      <?= $status==='sold'?'selected':'' ?>>Sold</option>
      </select>
    </div>
    <div class="col-6 col-md-3 d-flex gap-2">
      <button type="submit" class="btn btn-gold btn-sm w-100">Filter</button>
      <a href="properties.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="form-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table admin-table mb-0">
      <thead>
        <tr>
          <th style="width:40px">#</th>
          <th>Apartment</th>
          <th>Site</th>
          <th class="d-none d-md-table-cell">Type</th>
          <th class="d-none d-sm-table-cell">Area</th>
          <th>Price</th>
          <th>Status</th>
          <th class="d-none d-md-table-cell">Featured</th>
          <th style="width:120px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($props)): ?>
        <tr><td colspan="9" class="text-center py-4 text-muted">No apartments found.</td></tr>
        <?php else: ?>
        <?php foreach ($props as $i => $p): ?>
        <tr>
          <td class="text-muted small"><?= $i+1 ?></td>
          <td>
            <div class="fw-600 small" style="color:var(--dark)"><?= htmlspecialchars($p['title']) ?></div>
            <div class="text-muted" style="font-size:0.72rem"><?= number_format($p['views']) ?> views</div>
          </td>
          <td><span class="badge bg-dark small"><?= htmlspecialchars($p['site']) ?></span></td>
          <td class="d-none d-md-table-cell"><span class="badge" style="background:var(--gold-bg);color:var(--gold-dark)"><?= $p['type'] ?></span></td>
          <td class="small text-muted d-none d-sm-table-cell"><?= number_format($p['area']) ?>m²</td>
          <td class="fw-700 small" style="color:var(--gold)"><?= formatPrice($p['price']) ?></td>
          <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
          <td class="d-none d-md-table-cell">
            <?php if ($p['featured']): ?>
            <i class="bi bi-star-fill" style="color:var(--gold)"></i>
            <?php else: ?>
            <i class="bi bi-star text-muted"></i>
            <?php endif; ?>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= SITE_URL ?>/property.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
              <a href="property-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
              <a href="?delete=<?= $p['id'] ?>"
                 data-confirm="Delete '<?= htmlspecialchars(addslashes($p['title'])) ?>'? This cannot be undone."
                 class="btn btn-sm btn-light text-danger" title="Delete"><i class="bi bi-trash"></i></a>
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
