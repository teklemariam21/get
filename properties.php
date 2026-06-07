<?php
$pageTitle = 'All Apartments';
require_once 'includes/header.php';

$tower    = trim($_GET['tower']    ?? '');
$bedrooms = isset($_GET['bedrooms']) && $_GET['bedrooms'] !== '' ? (int)$_GET['bedrooms'] : '';
$unitType = trim($_GET['unit_type'] ?? '');
$minArea  = isset($_GET['min_area']) && $_GET['min_area'] !== '' ? (float)$_GET['min_area'] : 0;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 0;
$status   = trim($_GET['status']   ?? '');
$sort     = trim($_GET['sort']     ?? 'tower');

$where  = ['1=1'];
$params = [];
if ($tower)    { $where[] = 'tower=:tower';          $params[':tower']    = $tower; }
if ($bedrooms !== '') { $where[] = 'bedrooms=:bed';  $params[':bed']      = $bedrooms; }
if ($unitType) { $where[] = 'unit_type=:ut';         $params[':ut']       = $unitType; }
if ($minArea)  { $where[] = 'area>=:mina';           $params[':mina']     = $minArea; }
if ($maxPrice) { $where[] = 'total_price<=:maxp';    $params[':maxp']     = $maxPrice; }
if ($status)   { $where[] = 'status=:status';        $params[':status']   = $status; }

$orderMap = ['tower'=>'tower,unit_type','price_asc'=>'total_price ASC','price_desc'=>'total_price DESC','area_asc'=>'area ASC','area_desc'=>'area DESC'];
$order = $orderMap[$sort] ?? 'tower,unit_type';

$sql   = "SELECT * FROM properties WHERE " . implode(' AND ',$where) . " ORDER BY featured DESC, $order";
$stmt  = $db->prepare($sql); $stmt->execute($params);
$props = $stmt->fetchAll();
$total = count($props);

$typeImgs = [
  2 => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
  3 => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
];
?>

<!-- Page Hero -->
<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="index.php">Home</a></li><li class="breadcrumb-item active text-white-50">Apartments</li></ol>
    </nav>
    <h1 class="text-white fw-bold mb-2">
      <?= $tower ? htmlspecialchars($tower).' Apartments' : 'All City Gate Apartments' ?>
      <?php if ($bedrooms !== ''): ?><span class="text-gold"> — <?= $bedrooms ?>BR</span><?php endif; ?>
    </h1>
    <p style="color:rgba(255,255,255,0.65);margin:0"><?= $total ?> unit<?= $total!==1?'s':'' ?> found</p>
  </div>
</div>

<div class="container py-4">
  <!-- Filter Bar -->
  <div class="filter-bar mb-4">
    <form method="GET" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label small fw-600 mb-1">Tower</label>
        <select name="tower" class="form-select form-select-sm">
          <option value="">All Towers</option>
          <option value="City Gate 1" <?= $tower==='City Gate 1'?'selected':'' ?>>City Gate 1</option>
          <option value="City Gate 2" <?= $tower==='City Gate 2'?'selected':'' ?>>City Gate 2</option>
          <option value="City Gate 3" <?= $tower==='City Gate 3'?'selected':'' ?>>City Gate 3</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small fw-600 mb-1">Bedrooms</label>
        <select name="bedrooms" class="form-select form-select-sm">
          <option value="">Any</option>
          <option value="2" <?= $bedrooms===2?'selected':'' ?>>2 Bedroom</option>
          <option value="3" <?= $bedrooms===3?'selected':'' ?>>3 Bedroom</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small fw-600 mb-1">Unit Type</label>
        <select name="unit_type" class="form-select form-select-sm">
          <option value="">All Types</option>
          <?php for ($i=1;$i<=6;$i++): ?>
          <option value="Type <?= $i ?>" <?= $unitType==="Type $i"?'selected':'' ?>>Type <?= $i ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small fw-600 mb-1">Min Area</label>
        <select name="min_area" class="form-select form-select-sm">
          <option value="">Any</option>
          <option value="115" <?= $minArea==115?'selected':'' ?>>115m²+</option>
          <option value="132" <?= $minArea==132?'selected':'' ?>>132m²+</option>
          <option value="144" <?= $minArea==144?'selected':'' ?>>144m²+</option>
          <option value="149" <?= $minArea==149?'selected':'' ?>>149m²+</option>
          <option value="159" <?= $minArea==159?'selected':'' ?>>159m²+</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label small fw-600 mb-1">Sort</label>
        <select name="sort" class="form-select form-select-sm">
          <option value="tower"      <?= $sort==='tower'?'selected':'' ?>>By Tower</option>
          <option value="price_asc"  <?= $sort==='price_asc'?'selected':'' ?>>Price ↑</option>
          <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price ↓</option>
          <option value="area_asc"   <?= $sort==='area_asc'?'selected':'' ?>>Area ↑</option>
          <option value="area_desc"  <?= $sort==='area_desc'?'selected':'' ?>>Area ↓</option>
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-gold btn-sm w-100">Filter</button>
        <a href="properties.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
      </div>
    </form>
  </div>

  <!-- Active filters -->
  <?php if ($tower || $bedrooms !== '' || $unitType): ?>
  <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <span class="small text-muted fw-600">Filters:</span>
    <?php if ($tower): ?><span class="badge bg-dark"><?= htmlspecialchars($tower) ?> <a href="?<?= http_build_query(array_merge($_GET,['tower'=>''])) ?>" class="text-white ms-1"><i class="bi bi-x"></i></a></span><?php endif; ?>
    <?php if ($bedrooms !== ''): ?><span class="badge bg-dark"><?= $bedrooms ?> Bedroom <a href="?<?= http_build_query(array_merge($_GET,['bedrooms'=>''])) ?>" class="text-white ms-1"><i class="bi bi-x"></i></a></span><?php endif; ?>
    <?php if ($unitType): ?><span class="badge bg-dark"><?= htmlspecialchars($unitType) ?> <a href="?<?= http_build_query(array_merge($_GET,['unit_type'=>''])) ?>" class="text-white ms-1"><i class="bi bi-x"></i></a></span><?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Grid -->
  <?php if (empty($props)): ?>
  <div class="text-center py-5">
    <i class="bi bi-buildings" style="font-size:4rem;color:#d1d5db"></i>
    <h4 class="mt-3 text-muted">No apartments match your filters</h4>
    <a href="properties.php" class="btn btn-gold mt-2">View All Apartments</a>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($props as $p):
      $imgs  = json_decode($p['images']??'[]',true);
      $img   = !empty($imgs[0]) ? $imgs[0] : ($typeImgs[$p['bedrooms']] ?? $typeImgs[2]);
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="property-card h-100">
        <div class="card-img-wrap">
          <a href="property.php?slug=<?= urlencode($p['slug']) ?>">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
          </a>
          <span class="badge-site"><i class="bi bi-buildings me-1"></i><?= htmlspecialchars($p['tower']) ?></span>
          <span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span>
          <span style="position:absolute;bottom:12px;left:12px;background:var(--gold);color:#fff;padding:0.2rem 0.65rem;border-radius:50px;font-size:0.68rem;font-weight:700"><?= htmlspecialchars($p['unit_type']) ?></span>
          <?php if ($p['featured']): ?>
          <span style="position:absolute;bottom:12px;right:12px;background:var(--dark);color:var(--gold);padding:0.2rem 0.65rem;border-radius:50px;font-size:0.65rem;font-weight:700"><i class="bi bi-star-fill me-1"></i>Featured</span>
          <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column">
          <h3 class="property-title"><a href="property.php?slug=<?= urlencode($p['slug']) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($p['title']) ?></a></h3>
          <div class="property-price">ETB <?= number_format($p['total_price']) ?></div>
          <div class="property-specs">
            <div class="spec-item"><i class="bi bi-door-closed"></i><?= $p['bedrooms'] ?> Bed</div>
            <div class="spec-item"><i class="bi bi-droplet"></i><?= $p['bathrooms'] ?> Bath</div>
            <div class="spec-item"><i class="bi bi-rulers"></i><?= number_format($p['area']) ?>m²</div>
            <div class="spec-item"><i class="bi bi-snow"></i>2 Balconies</div>
          </div>
          <div class="small text-muted mb-3">
            <i class="bi bi-info-circle text-gold me-1"></i>
            10% Down: <strong class="text-dark">ETB <?= number_format($p['down_payment_10pct']) ?></strong>
          </div>
          <div class="d-flex gap-2 mt-auto">
            <a href="property.php?slug=<?= urlencode($p['slug']) ?>" class="btn btn-outline-gold btn-sm flex-grow-1">Details</a>
            <a href="contact.php?property=<?= urlencode($p['title']) ?>&tower=<?= urlencode($p['tower']) ?>" class="btn btn-gold btn-sm flex-grow-1">Enquire</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<a href="https://wa.me/251911234567?text=Hi, I'm interested in City Gate apartments" target="_blank" class="wa-float"><i class="bi bi-whatsapp"></i></a>
<?php require_once 'includes/footer.php'; ?>
