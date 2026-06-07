<?php
$pageTitle  = 'Add Apartment';
$breadcrumb = 'Add Apartment';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];
$data   = ['site'=>'Summit 72','type'=>'1BR','bedrooms'=>1,'bathrooms'=>1,'area'=>61,'price'=>2850000,'status'=>'available','featured'=>0,'floor_number'=>'','parking'=>1,'balcony'=>1,'title'=>'','description'=>'','amenities'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title']        = trim($_POST['title']       ?? '');
    $data['site']         = trim($_POST['site']        ?? '');
    $data['type']         = trim($_POST['type']        ?? '');
    $data['bedrooms']     = (int)($_POST['bedrooms']   ?? 1);
    $data['bathrooms']    = (int)($_POST['bathrooms']  ?? 1);
    $data['area']         = (float)($_POST['area']     ?? 0);
    $data['floor_number'] = (int)($_POST['floor']      ?? 0);
    $data['price']        = (float)str_replace(',','',$_POST['price'] ?? 0);
    $data['status']       = trim($_POST['status']      ?? 'available');
    $data['featured']     = isset($_POST['featured'])  ? 1 : 0;
    $data['parking']      = (int)($_POST['parking']    ?? 0);
    $data['balcony']      = isset($_POST['balcony'])   ? 1 : 0;
    $data['description']  = trim($_POST['description'] ?? '');
    $amenitiesList        = array_filter(array_map('trim', explode("\n", $_POST['amenities'] ?? '')));

    if (!$data['title'])       $errors[] = 'Title is required.';
    if (!$data['area'])        $errors[] = 'Area is required.';
    if (!$data['price'])       $errors[] = 'Price is required.';
    if (!$data['description']) $errors[] = 'Description is required.';

    if (empty($errors)) {
        // Generate unique slug
        $base = strtolower(str_replace([' ','/'], '-', $data['site'])) . '-' . strtolower($data['type']) . '-' . (int)$data['area'] . 'sqm';
        $slug = $base;
        $i    = 2;
        while ($db->prepare("SELECT id FROM properties WHERE slug=:s")->execute([':s'=>$slug]) && $db->query("SELECT COUNT(*) FROM properties WHERE slug='$slug'")->fetchColumn() > 0) {
            $slug = $base . '-' . $i++;
        }

        $ins = $db->prepare("INSERT INTO properties (title,slug,description,site,type,bedrooms,bathrooms,area,floor_number,price,status,featured,images,amenities,parking,balcony) VALUES (:title,:slug,:desc,:site,:type,:bed,:bath,:area,:floor,:price,:status,:featured,:images,:amenities,:parking,:balcony)");
        $ins->execute([
            ':title'     => $data['title'],
            ':slug'      => $slug,
            ':desc'      => $data['description'],
            ':site'      => $data['site'],
            ':type'      => $data['type'],
            ':bed'       => $data['bedrooms'],
            ':bath'      => $data['bathrooms'],
            ':area'      => $data['area'],
            ':floor'     => $data['floor_number'] ?: null,
            ':price'     => $data['price'],
            ':status'    => $data['status'],
            ':featured'  => $data['featured'],
            ':images'    => json_encode([]),
            ':amenities' => json_encode(array_values($amenitiesList)),
            ':parking'   => $data['parking'],
            ':balcony'   => $data['balcony'],
        ]);
        flash('success', 'Apartment added successfully!');
        redirect(SITE_URL . '/admin/properties.php');
    }
}

// Default amenities by type
$defaultAmenities = [
    '1BR' => "Elevator\n24/7 Security\nCCTV\nGenerator\nParking\nIntercom\nFiber Internet",
    '2BR' => "Elevator\n24/7 Security\nCCTV\nGenerator\nParking\nSwimming Pool\nGym\nIntercom\nFiber Internet\nStorage Room",
    '3BR' => "Elevator\n24/7 Security\nCCTV\nGenerator\nParking\nSwimming Pool\nGym\nIntercom\nFiber Internet\nStorage Room\nMaids Room",
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-700 mb-0" style="color:var(--dark)">Add New Apartment</h4>
    <p class="text-muted small mb-0">Fill in the apartment details below.</p>
  </div>
  <a href="properties.php" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
  <ul class="mb-0 ps-3">
    <?php foreach ($errors as $e): ?>
    <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<form method="POST" class="needs-validation" novalidate>
  <div class="row g-4">
    <!-- Left -->
    <div class="col-lg-8">
      <div class="form-card mb-4">
        <div class="form-section-title">Basic Information</div>
        <div class="mb-3">
          <label class="form-label">Apartment Title *</label>
          <input type="text" name="title" class="form-control" required placeholder="e.g. 2 Bedroom Apartment – 109m² | Summit 72"
                 value="<?= htmlspecialchars($data['title']) ?>">
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Site *</label>
            <select name="site" class="form-select" required id="siteSelect">
              <option value="Summit 72" <?= $data['site']==='Summit 72'?'selected':'' ?>>Summit 72</option>
              <option value="Kazanchis" <?= $data['site']==='Kazanchis'?'selected':'' ?>>Kazanchis</option>
              <option value="Other"     <?= $data['site']==='Other'?'selected':'' ?>>Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Apartment Type *</label>
            <select name="type" class="form-select" required id="typeSelect">
              <option value="1BR" <?= $data['type']==='1BR'?'selected':'' ?>>1 Bedroom (1BR)</option>
              <option value="2BR" <?= $data['type']==='2BR'?'selected':'' ?>>2 Bedroom (2BR)</option>
              <option value="3BR" <?= $data['type']==='3BR'?'selected':'' ?>>3 Bedroom (3BR)</option>
            </select>
          </div>
        </div>
        <div class="mb-3 mt-3">
          <label class="form-label">Description *</label>
          <textarea name="description" class="form-control" rows="5" required placeholder="Describe the apartment..."><?= htmlspecialchars($data['description']) ?></textarea>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-section-title">Apartment Specifications</div>
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <label class="form-label">Bedrooms</label>
            <input type="number" name="bedrooms" class="form-control" min="1" max="6" value="<?= $data['bedrooms'] ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Bathrooms</label>
            <input type="number" name="bathrooms" class="form-control" min="1" max="6" value="<?= $data['bathrooms'] ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Area (m²) *</label>
            <input type="number" name="area" class="form-control" step="0.5" required min="1" id="areaInput" value="<?= $data['area'] ?>" placeholder="e.g. 61">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Floor Number</label>
            <input type="number" name="floor" class="form-control" min="1" value="<?= $data['floor_number'] ?>">
          </div>
          <div class="col-6 col-md-4">
            <label class="form-label">Parking Spaces</label>
            <input type="number" name="parking" class="form-control" min="0" max="5" value="<?= $data['parking'] ?>">
          </div>
          <div class="col-6 col-md-4 d-flex align-items-end">
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="balcony" id="balcony" <?= $data['balcony']?'checked':'' ?>>
              <label class="form-check-label fw-600" for="balcony">Has Balcony</label>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-section-title">Amenities</div>
        <label class="form-label">Amenities (one per line)</label>
        <textarea name="amenities" class="form-control" rows="8" id="amenitiesInput"
          placeholder="Elevator&#10;24/7 Security&#10;Swimming Pool..."><?= htmlspecialchars($data['amenities']) ?></textarea>
        <div class="mt-2 d-flex gap-2 flex-wrap">
          <small class="text-muted me-2">Quick fill:</small>
          <?php foreach (['1BR','2BR','3BR'] as $t): ?>
          <button type="button" class="btn btn-sm btn-outline-secondary"
            onclick="document.getElementById('amenitiesInput').value = <?= json_encode($defaultAmenities[$t]) ?>"><?= $t ?> defaults</button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Right -->
    <div class="col-lg-4">
      <div class="form-card mb-4">
        <div class="form-section-title">Pricing & Status</div>
        <div class="mb-3">
          <label class="form-label">Sale Price (ETB) *</label>
          <div class="input-group">
            <span class="input-group-text bg-light fw-600 text-muted small">ETB</span>
            <input type="number" name="price" class="form-control" required min="0" step="50000"
                   value="<?= $data['price'] ?>" placeholder="e.g. 2850000">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="available"   <?= $data['status']==='available'?'selected':'' ?>>Available</option>
            <option value="reserved"    <?= $data['status']==='reserved'?'selected':'' ?>>Reserved</option>
            <option value="sold"        <?= $data['status']==='sold'?'selected':'' ?>>Sold</option>
            <option value="coming_soon" <?= $data['status']==='coming_soon'?'selected':'' ?>>Coming Soon</option>
          </select>
        </div>
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= $data['featured']?'checked':'' ?>>
          <label class="form-check-label fw-600" for="featured">
            <i class="bi bi-star-fill text-gold me-1"></i>Mark as Featured
          </label>
        </div>
      </div>

      <!-- Quick size reference -->
      <div class="form-card mb-4" style="background:var(--gold-bg);border-color:var(--gold-light)">
        <div class="form-section-title">Size Reference</div>
        <table class="table table-sm small mb-0">
          <thead><tr><th>Type</th><th>Sizes</th></tr></thead>
          <tbody>
            <tr><td><span class="badge bg-dark">1BR</span></td><td>61m² or 65m²</td></tr>
            <tr><td><span class="badge bg-dark">2BR</span></td><td>109m² or 115m²</td></tr>
            <tr><td><span class="badge bg-dark">3BR</span></td><td>144m² or 151m²</td></tr>
          </tbody>
        </table>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-gold btn-lg">
          <i class="bi bi-plus-circle me-2"></i>Add Apartment
        </button>
        <a href="properties.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>

<script>
// Auto-fill title when site/type/area change
function autoTitle() {
  const site = document.getElementById('siteSelect').value;
  const type = document.getElementById('typeSelect').value;
  const area = document.getElementById('areaInput').value;
  const titleEl = document.querySelector('[name=title]');
  const beds = type==='1BR'?1:type==='2BR'?2:3;
  const baths = type==='3BR'?3:type==='2BR'?2:1;
  if (area && site && type) {
    titleEl.value = beds+' Bedroom Apartment – '+area+'m² | '+site;
    document.querySelector('[name=bedrooms]').value = beds;
    document.querySelector('[name=bathrooms]').value = baths;
  }
}
document.getElementById('siteSelect').addEventListener('change', autoTitle);
document.getElementById('typeSelect').addEventListener('change', autoTitle);
document.getElementById('areaInput').addEventListener('change', autoTitle);
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
