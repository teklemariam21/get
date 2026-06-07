<?php
$pageTitle  = 'Edit Apartment';
$breadcrumb = 'Edit Apartment';
require_once __DIR__ . '/includes/admin_header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { redirect(SITE_URL . '/admin/properties.php'); }

$prop = $db->prepare("SELECT * FROM properties WHERE id=:id");
$prop->execute([':id'=>$id]);
$data = $prop->fetch();
if (!$data) { flash('danger','Apartment not found.'); redirect(SITE_URL . '/admin/properties.php'); }

$amenitiesList = implode("\n", json_decode($data['amenities'] ?? '[]', true));
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title']        = trim($_POST['title']       ?? '');
    $data['site']         = trim($_POST['site']        ?? '');
    $data['type']         = trim($_POST['type']        ?? '');
    $data['bedrooms']     = (int)($_POST['bedrooms']   ?? 1);
    $data['bathrooms']    = (int)($_POST['bathrooms']  ?? 1);
    $data['area']         = (float)($_POST['area']     ?? 0);
    $data['floor_number'] = (int)($_POST['floor']      ?? 0) ?: null;
    $data['price']        = (float)str_replace(',','',$_POST['price'] ?? 0);
    $data['status']       = trim($_POST['status']      ?? 'available');
    $data['featured']     = isset($_POST['featured'])  ? 1 : 0;
    $data['parking']      = (int)($_POST['parking']    ?? 0);
    $data['balcony']      = isset($_POST['balcony'])   ? 1 : 0;
    $data['description']  = trim($_POST['description'] ?? '');
    $amenitiesArr         = array_filter(array_map('trim', explode("\n", $_POST['amenities'] ?? '')));
    $amenitiesList        = $_POST['amenities'] ?? '';

    if (!$data['title'])       $errors[] = 'Title is required.';
    if (!$data['area'])        $errors[] = 'Area is required.';
    if (!$data['price'])       $errors[] = 'Price is required.';
    if (!$data['description']) $errors[] = 'Description is required.';

    if (empty($errors)) {
        $upd = $db->prepare("UPDATE properties SET title=:title,site=:site,type=:type,bedrooms=:bed,bathrooms=:bath,area=:area,floor_number=:floor,price=:price,status=:status,featured=:featured,description=:desc,amenities=:amenities,parking=:parking,balcony=:balcony WHERE id=:id");
        $upd->execute([
            ':title'    => $data['title'],
            ':site'     => $data['site'],
            ':type'     => $data['type'],
            ':bed'      => $data['bedrooms'],
            ':bath'     => $data['bathrooms'],
            ':area'     => $data['area'],
            ':floor'    => $data['floor_number'],
            ':price'    => $data['price'],
            ':status'   => $data['status'],
            ':featured' => $data['featured'],
            ':desc'     => $data['description'],
            ':amenities'=> json_encode(array_values($amenitiesArr)),
            ':parking'  => $data['parking'],
            ':balcony'  => $data['balcony'],
            ':id'       => $id,
        ]);
        flash('success', 'Apartment updated successfully!');
        redirect(SITE_URL . '/admin/properties.php');
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-700 mb-0" style="color:var(--dark)">Edit Apartment</h4>
    <p class="text-muted small mb-0"><?= htmlspecialchars($data['title']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= SITE_URL ?>/property.php?slug=<?= urlencode($data['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-eye me-1"></i>Preview
    </a>
    <a href="properties.php" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4">
  <ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form method="POST" class="needs-validation" novalidate>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="form-card mb-4">
        <div class="form-section-title">Basic Information</div>
        <div class="mb-3">
          <label class="form-label">Apartment Title *</label>
          <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($data['title']) ?>">
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Site</label>
            <select name="site" class="form-select">
              <option value="Summit 72" <?= $data['site']==='Summit 72'?'selected':'' ?>>Summit 72</option>
              <option value="Kazanchis" <?= $data['site']==='Kazanchis'?'selected':'' ?>>Kazanchis</option>
              <option value="Other"     <?= $data['site']==='Other'?'selected':'' ?>>Other</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Type</label>
            <select name="type" class="form-select">
              <option value="1BR" <?= $data['type']==='1BR'?'selected':'' ?>>1 Bedroom</option>
              <option value="2BR" <?= $data['type']==='2BR'?'selected':'' ?>>2 Bedroom</option>
              <option value="3BR" <?= $data['type']==='3BR'?'selected':'' ?>>3 Bedroom</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Description *</label>
          <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($data['description']) ?></textarea>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-section-title">Specifications</div>
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <label class="form-label">Bedrooms</label>
            <input type="number" name="bedrooms" class="form-control" value="<?= $data['bedrooms'] ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Bathrooms</label>
            <input type="number" name="bathrooms" class="form-control" value="<?= $data['bathrooms'] ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Area (m²)</label>
            <input type="number" name="area" class="form-control" step="0.5" value="<?= $data['area'] ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Floor</label>
            <input type="number" name="floor" class="form-control" value="<?= $data['floor_number'] ?>">
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Parking</label>
            <input type="number" name="parking" class="form-control" min="0" value="<?= $data['parking'] ?>">
          </div>
          <div class="col-6 col-md-4 d-flex align-items-end pb-1">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="balcony" id="balcony" <?= $data['balcony']?'checked':'' ?>>
              <label class="form-check-label fw-600" for="balcony">Has Balcony</label>
            </div>
          </div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">Amenities (one per line)</div>
        <textarea name="amenities" class="form-control" rows="8"><?= htmlspecialchars($amenitiesList) ?></textarea>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="form-card mb-4">
        <div class="form-section-title">Pricing & Status</div>
        <div class="mb-3">
          <label class="form-label">Price (ETB)</label>
          <input type="number" name="price" class="form-control" value="<?= $data['price'] ?>">
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
          <label class="form-check-label fw-600" for="featured"><i class="bi bi-star-fill text-gold me-1"></i>Featured</label>
        </div>
      </div>
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-gold btn-lg"><i class="bi bi-check-circle me-2"></i>Save Changes</button>
        <a href="properties.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
