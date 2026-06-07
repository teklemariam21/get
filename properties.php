<?php
$pageTitle = 'All Apartments';
require_once 'includes/header.php';

// Filters
$site      = isset($_GET['site'])      ? trim($_GET['site'])      : '';
$type      = isset($_GET['type'])      ? trim($_GET['type'])      : '';
$minArea   = isset($_GET['min_area'])  ? (float)$_GET['min_area'] : 0;
$maxPrice  = isset($_GET['max_price']) ? (float)$_GET['max_price']: 0;
$status    = isset($_GET['status'])    ? trim($_GET['status'])    : '';
$sort      = isset($_GET['sort'])      ? trim($_GET['sort'])      : 'newest';

// Build query
$where  = ['1=1'];
$params = [];

if ($site !== '') {
    $where[]  = 'site = :site';
    $params[':site'] = $site;
}
if ($type !== '') {
    $where[]  = 'type = :type';
    $params[':type'] = $type;
}
if ($minArea > 0) {
    $where[]  = 'area >= :minArea';
    $params[':minArea'] = $minArea;
}
if ($maxPrice > 0) {
    $where[]  = 'price <= :maxPrice';
    $params[':maxPrice'] = $maxPrice;
}
if ($status !== '') {
    $where[]  = 'status = :status';
    $params[':status'] = $status;
} else {
    $where[] = "status != 'coming_soon'";
}

$orderMap = [
    'newest'      => 'created_at DESC',
    'price_asc'   => 'price ASC',
    'price_desc'  => 'price DESC',
    'area_asc'    => 'area ASC',
    'area_desc'   => 'area DESC',
];
$order = $orderMap[$sort] ?? 'created_at DESC';

$sql  = "SELECT * FROM properties WHERE " . implode(' AND ', $where) . " ORDER BY featured DESC, $order";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
$total = count($properties);

$typeImages = [
  '1BR' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
  '2BR' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
  '3BR' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
];
?>

<!-- Page Hero -->
<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:rgba(255,255,255,0.4)">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active text-white-50">Apartments</li>
      </ol>
    </nav>
    <h1 class="text-white fw-bold mb-2">
      <?= $site ? htmlspecialchars($site) . ' Apartments' : 'All Apartments' ?>
      <?php if ($type): ?><span class="text-gold"> — <?= htmlspecialchars($type) ?></span><?php endif; ?>
    </h1>
    <p style="color:rgba(255,255,255,0.7);margin:0"><?= $total ?> unit<?= $total !== 1 ? 's' : '' ?> found</p>
  </div>
</div>

<div class="container py-4">
  <!-- Filter Bar -->
  <div class="filter-bar mb-4">
    <form method="GET" action="properties.php" class="row g-2 align-items-end">
      <div class="col-6 col-md-2">
        <label class="form-label mb-1 small fw-600">Location</label>
        <select name="site" class="form-select form-select-sm">
          <option value="">All Sites</option>
          <option value="Summit 72" <?= $site==='Summit 72'?'selected':'' ?>>Summit 72</option>
          <option value="Kazanchis" <?= $site==='Kazanchis'?'selected':'' ?>>Kazanchis</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1 small fw-600">Type</label>
        <select name="type" class="form-select form-select-sm">
          <option value="">All Types</option>
          <option value="1BR" <?= $type==='1BR'?'selected':'' ?>>1 Bedroom</option>
          <option value="2BR" <?= $type==='2BR'?'selected':'' ?>>2 Bedroom</option>
          <option value="3BR" <?= $type==='3BR'?'selected':'' ?>>3 Bedroom</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1 small fw-600">Min Size</label>
        <select name="min_area" class="form-select form-select-sm">
          <option value="">Any</option>
          <option value="61"  <?= $minArea==61?'selected':''  ?>>61m²+</option>
          <option value="65"  <?= $minArea==65?'selected':''  ?>>65m²+</option>
          <option value="109" <?= $minArea==109?'selected':'' ?>>109m²+</option>
          <option value="144" <?= $minArea==144?'selected':'' ?>>144m²+</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1 small fw-600">Max Price</label>
        <select name="max_price" class="form-select form-select-sm">
          <option value="">Any</option>
          <option value="3000000" <?= $maxPrice==3000000?'selected':'' ?>>ETB 3M</option>
          <option value="5000000" <?= $maxPrice==5000000?'selected':'' ?>>ETB 5M</option>
          <option value="7500000" <?= $maxPrice==7500000?'selected':'' ?>>ETB 7.5M</option>
        </select>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label mb-1 small fw-600">Sort By</label>
        <select name="sort" class="form-select form-select-sm">
          <option value="newest"     <?= $sort==='newest'?'selected':''     ?>>Newest First</option>
          <option value="price_asc"  <?= $sort==='price_asc'?'selected':''  ?>>Price: Low→High</option>
          <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High→Low</option>
          <option value="area_asc"   <?= $sort==='area_asc'?'selected':''   ?>>Area: Small→Large</option>
        </select>
      </div>
      <div class="col-6 col-md-2 d-flex gap-2">
        <button type="submit" class="btn btn-gold btn-sm w-100">
          <i class="bi bi-search me-1"></i>Filter
        </button>
        <a href="properties.php" class="btn btn-outline-secondary btn-sm" title="Clear filters">
          <i class="bi bi-x-lg"></i>
        </a>
      </div>
    </form>
  </div>

  <!-- Active filters -->
  <?php if ($site || $type || $minArea || $maxPrice): ?>
  <div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <span class="small text-muted fw-600">Active filters:</span>
    <?php if ($site): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['site'=>''])) ?>" class="badge text-bg-dark text-decoration-none">
      <?= htmlspecialchars($site) ?> <i class="bi bi-x ms-1"></i>
    </a>
    <?php endif; ?>
    <?php if ($type): ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['type'=>''])) ?>" class="badge text-bg-dark text-decoration-none">
      <?= htmlspecialchars($type) ?> <i class="bi bi-x ms-1"></i>
    </a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Grid -->
  <?php if (empty($properties)): ?>
  <div class="text-center py-5">
    <i class="bi bi-buildings" style="font-size:4rem;color:#d1d5db"></i>
    <h4 class="mt-3 text-muted">No apartments found</h4>
    <p class="text-muted">Try adjusting your filters or <a href="properties.php" class="text-gold">view all listings</a>.</p>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($properties as $prop):
      $images  = json_decode($prop['images'] ?? '[]', true);
      $mainImg = !empty($images[0]) ? $images[0] : ($typeImages[$prop['type']] ?? 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800');
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="property-card h-100">
        <div class="card-img-wrap">
          <a href="property.php?slug=<?= urlencode($prop['slug']) ?>">
            <img src="<?= htmlspecialchars($mainImg) ?>" alt="<?= htmlspecialchars($prop['title']) ?>" loading="lazy">
          </a>
          <span class="badge-site"><i class="bi bi-buildings me-1"></i><?= htmlspecialchars($prop['site']) ?></span>
          <span class="badge-status badge-<?= $prop['status'] ?>"><?= ucfirst($prop['status']) ?></span>
          <?php if ($prop['featured']): ?>
          <span style="position:absolute;bottom:12px;left:12px;background:var(--gold);color:#fff;padding:0.2rem 0.65rem;border-radius:50px;font-size:0.68rem;font-weight:700"><i class="bi bi-star-fill me-1"></i>Featured</span>
          <?php endif; ?>
        </div>
        <div class="card-body d-flex flex-column">
          <h3 class="property-title">
            <a href="property.php?slug=<?= urlencode($prop['slug']) ?>" class="text-decoration-none text-dark">
              <?= htmlspecialchars($prop['title']) ?>
            </a>
          </h3>
          <div class="property-price"><?= formatPrice($prop['price']) ?></div>
          <div class="property-specs">
            <div class="spec-item"><i class="bi bi-door-closed"></i><?= $prop['bedrooms'] ?> Bed</div>
            <div class="spec-item"><i class="bi bi-droplet"></i><?= $prop['bathrooms'] ?> Bath</div>
            <div class="spec-item"><i class="bi bi-rulers"></i><?= number_format($prop['area']) ?>m²</div>
            <?php if ($prop['balcony']): ?>
            <div class="spec-item"><i class="bi bi-snow"></i>Balcony</div>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-2 mt-auto">
            <a href="property.php?slug=<?= urlencode($prop['slug']) ?>" class="btn btn-outline-gold btn-sm flex-grow-1">Details</a>
            <a href="contact.php?property=<?= urlencode($prop['title']) ?>&site=<?= urlencode($prop['site']) ?>" class="btn btn-gold btn-sm flex-grow-1">Enquire</a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- WhatsApp Float -->
<a href="https://wa.me/251911234567?text=Hi, I'm interested in an apartment" target="_blank" class="wa-float">
  <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
