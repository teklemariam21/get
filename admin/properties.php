<?php
$pageTitle  = 'Apartments';
$breadcrumb = 'Apartments';
require_once __DIR__ . '/includes/admin_header.php';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $db->prepare("DELETE FROM properties WHERE id=:id")->execute([':id'=>(int)$_GET['delete']]);
    flash('success','Unit deleted.'); redirect(SITE_URL.'/admin/properties.php');
}

// Toggle featured
if (isset($_GET['feature']) && is_numeric($_GET['feature'])) {
    $cur = $db->prepare("SELECT featured FROM properties WHERE id=:id"); $cur->execute([':id'=>(int)$_GET['feature']]); $f = $cur->fetchColumn();
    $db->prepare("UPDATE properties SET featured=:f WHERE id=:id")->execute([':f'=>$f?0:1,':id'=>(int)$_GET['feature']]);
    redirect(SITE_URL.'/admin/properties.php?'.http_build_query(array_diff_key($_GET,['feature'=>''])));
}

$tower  = trim($_GET['tower']   ?? '');
$bed    = isset($_GET['bed']) && $_GET['bed']!=='' ? (int)$_GET['bed'] : '';
$status = trim($_GET['status']  ?? '');
$search = trim($_GET['q']       ?? '');

$where = ['1=1']; $params = [];
if ($tower)  { $where[]='tower=:tower';    $params[':tower']=$tower; }
if ($bed!==''){ $where[]='bedrooms=:bed'; $params[':bed']=$bed; }
if ($status) { $where[]='status=:status'; $params[':status']=$status; }
if ($search) { $where[]='title LIKE :q';  $params[':q']="%$search%"; }

$stmt = $db->prepare("SELECT * FROM properties WHERE ".implode(' AND ',$where)." ORDER BY tower,unit_type");
$stmt->execute($params); $props = $stmt->fetchAll();
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div><h4 class="fw-700 mb-0" style="color:var(--dark)">Apartments</h4><p class="text-muted small mb-0"><?= count($props) ?> units</p></div>
  <a href="property-add.php" class="btn btn-gold"><i class="bi bi-plus-circle me-2"></i>Add Unit</a>
</div>

<div class="form-card mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-6 col-md-2"><label class="form-label small fw-600">Search</label><input type="text" name="q" class="form-control form-control-sm" placeholder="Title..." value="<?= htmlspecialchars($search) ?>"></div>
    <div class="col-6 col-md-2"><label class="form-label small fw-600">Tower</label><select name="tower" class="form-select form-select-sm"><option value="">All</option><option value="City Gate 1" <?= $tower==='City Gate 1'?'selected':'' ?>>City Gate 1</option><option value="City Gate 2" <?= $tower==='City Gate 2'?'selected':'' ?>>City Gate 2</option><option value="City Gate 3" <?= $tower==='City Gate 3'?'selected':'' ?>>City Gate 3</option></select></div>
    <div class="col-6 col-md-2"><label class="form-label small fw-600">Bedrooms</label><select name="bed" class="form-select form-select-sm"><option value="">Any</option><option value="2" <?= $bed===2?'selected':'' ?>>2BR</option><option value="3" <?= $bed===3?'selected':'' ?>>3BR</option></select></div>
    <div class="col-6 col-md-2"><label class="form-label small fw-600">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option><option value="available" <?= $status==='available'?'selected':'' ?>>Available</option><option value="reserved" <?= $status==='reserved'?'selected':'' ?>>Reserved</option><option value="sold" <?= $status==='sold'?'selected':'' ?>>Sold</option></select></div>
    <div class="col-6 col-md-2 d-flex gap-2"><button type="submit" class="btn btn-gold btn-sm w-100">Filter</button><a href="properties.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a></div>
  </form>
</div>

<div class="form-card p-0 overflow-hidden">
  <div class="table-responsive">
    <table class="table admin-table mb-0">
      <thead>
        <tr><th>#</th><th>Unit</th><th>Tower</th><th class="d-none d-md-table-cell">Type</th><th class="d-none d-sm-table-cell">Area</th><th>Total Price</th><th>10% Down</th><th>Status</th><th class="d-none d-md-table-cell">★</th><th style="width:120px">Actions</th></tr>
      </thead>
      <tbody>
        <?php if (empty($props)): ?>
        <tr><td colspan="10" class="text-center py-4 text-muted">No apartments found.</td></tr>
        <?php else: ?>
        <?php foreach ($props as $i=>$p): ?>
        <tr>
          <td class="text-muted small"><?= $i+1 ?></td>
          <td><div class="fw-600 small" style="color:var(--dark)"><?= htmlspecialchars($p['title']) ?></div><div class="text-muted" style="font-size:0.72rem"><?= $p['bedrooms'] ?>BR · <?= number_format($p['area']) ?>m²</div></td>
          <td><span class="badge bg-dark small"><?= htmlspecialchars($p['tower']) ?></span></td>
          <td class="d-none d-md-table-cell"><span class="badge" style="background:var(--gold-bg);color:var(--gold-dark)"><?= htmlspecialchars($p['unit_type']) ?></span></td>
          <td class="small text-muted d-none d-sm-table-cell"><?= number_format($p['area']) ?>m²</td>
          <td class="fw-700 small" style="color:var(--gold)">ETB <?= number_format($p['total_price']) ?></td>
          <td class="small" style="color:#166534;font-weight:600">ETB <?= number_format($p['down_payment_10pct']) ?></td>
          <td><span class="status-badge status-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
          <td class="d-none d-md-table-cell">
            <a href="?feature=<?= $p['id'] ?>&<?= http_build_query(array_diff_key($_GET,['feature'=>''])) ?>">
              <i class="bi bi-star<?= $p['featured']?'-fill text-warning':' text-muted' ?>"></i>
            </a>
          </td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= SITE_URL ?>/property.php?slug=<?= urlencode($p['slug']) ?>" target="_blank" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
              <a href="property-edit.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-light" title="Edit"><i class="bi bi-pencil"></i></a>
              <a href="?delete=<?= $p['id'] ?>" data-confirm="Delete '<?= htmlspecialchars(addslashes($p['title'])) ?>'?" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></a>
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
