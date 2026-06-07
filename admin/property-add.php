<?php
$pageTitle  = 'Add Apartment';
$breadcrumb = 'Add Apartment';
require_once __DIR__ . '/includes/admin_header.php';

$errors = [];
$data   = ['tower'=>'City Gate 1','unit_type'=>'Type 1','bedrooms'=>2,'bathrooms'=>2,'area'=>115,'price_per_m2'=>99378,'total_price'=>0,'down_payment_10pct'=>0,'status'=>'available','featured'=>0,'title'=>'','description'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['title']            = trim($_POST['title']        ?? '');
    $data['tower']            = trim($_POST['tower']        ?? '');
    $data['unit_type']        = trim($_POST['unit_type']    ?? '');
    $data['bedrooms']         = (int)($_POST['bedrooms']    ?? 2);
    $data['bathrooms']        = (int)($_POST['bathrooms']   ?? 2);
    $data['area']             = (float)($_POST['area']      ?? 0);
    $data['price_per_m2']     = (float)str_replace(',','', $_POST['price_per_m2'] ?? 0);
    $data['total_price']      = (float)str_replace(',','', $_POST['total_price'] ?? 0);
    $data['down_payment_10pct'] = round($data['total_price'] * 0.10);
    $data['status']           = trim($_POST['status']       ?? 'available');
    $data['featured']         = isset($_POST['featured'])   ? 1 : 0;
    $data['description']      = trim($_POST['description']  ?? '');
    $amenitiesArr             = array_filter(array_map('trim', explode("\n", $_POST['amenities'] ?? '')));

    if (!$data['title'])       $errors[] = 'Title is required.';
    if (!$data['area'])        $errors[] = 'Area is required.';
    if (!$data['total_price']) $errors[] = 'Total price is required.';
    if (!$data['description']) $errors[] = 'Description is required.';

    if (empty($errors)) {
        $base = strtolower(str_replace([' ','Gate '],'-','cg'.substr($data['tower'],-1).'-'.strtolower($data['unit_type']).'-'.$data['bedrooms'].'br-'.(int)$data['area']));
        $slug = $base; $i = 2;
        while ($db->query("SELECT COUNT(*) FROM properties WHERE slug='$slug'")->fetchColumn() > 0) { $slug = $base.'-'.$i++; }

        $ins = $db->prepare("INSERT INTO properties (title,slug,description,tower,unit_type,bedrooms,bathrooms,area,price_per_m2,total_price,down_payment_10pct,status,featured,images,amenities,balcony,maids_room,laundry,parking) VALUES (:title,:slug,:desc,:tower,:ut,:bed,:bath,:area,:ppm,:price,:dp,:status,:feat,:imgs,:amen,1,1,1,1)");
        $ins->execute([':title'=>$data['title'],':slug'=>$slug,':desc'=>$data['description'],':tower'=>$data['tower'],':ut'=>$data['unit_type'],':bed'=>$data['bedrooms'],':bath'=>$data['bathrooms'],':area'=>$data['area'],':ppm'=>$data['price_per_m2'],':price'=>$data['total_price'],':dp'=>$data['down_payment_10pct'],':status'=>$data['status'],':feat'=>$data['featured'],':imgs'=>json_encode([]),':amen'=>json_encode(array_values($amenitiesArr))]);
        flash('success','Apartment added!'); redirect(SITE_URL.'/admin/properties.php');
    }
}

// Reference data
$wingData = [
  'City Gate 1' => [
    'Type 1'=>[2,115,99378,11428470],'Type 2'=>[2,116,107476,12467216],'Type 3'=>[2,116,119000,13804000],
    'Type 4'=>[3,137,119000,16303000],'Type 5'=>[3,144,99378,14310432],'Type 6'=>[2,117,99378,11627226],
  ],
  'City Gate 2' => [
    'Type 1'=>[3,144,99378,14310432],'Type 2'=>[3,137,119000,16303000],'Type 3'=>[2,116,119000,13804000],
    'Type 4'=>[2,116,119000,13804000],'Type 5'=>[2,115,119000,13685000],'Type 6'=>[2,117,107476,12574692],
  ],
  'City Gate 3' => [
    'Type 1'=>[2,149,107476,16013924],'Type 2'=>[3,159,99378,15801102],'Type 3'=>[2,132,99378,13117896],
    'Type 4'=>[2,132,99378,13117896],'Type 5'=>[3,159,107476,17088684],'Type 6'=>[2,149,107476,16013924],
  ],
];

$defaultAmenities = 'Elevator
24/7 Security
CCTV
Generator
Parking
Swimming Pool
Gym
Rooftop Terrace
Concierge
Fiber Internet
Storage';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-700 mb-0" style="color:var(--dark)">Add New Apartment</h4>
  <a href="properties.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-4"><ul class="mb-0 ps-3"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="POST" class="needs-validation" novalidate>
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="form-card mb-4">
        <div class="form-section-title">Unit Identification</div>
        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">Tower *</label>
            <select name="tower" class="form-select" id="towerSel" required>
              <option value="City Gate 1" <?= $data['tower']==='City Gate 1'?'selected':'' ?>>City Gate 1</option>
              <option value="City Gate 2" <?= $data['tower']==='City Gate 2'?'selected':'' ?>>City Gate 2</option>
              <option value="City Gate 3" <?= $data['tower']==='City Gate 3'?'selected':'' ?>>City Gate 3</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Unit Type *</label>
            <select name="unit_type" class="form-select" id="typeSel" required>
              <?php for ($i=1;$i<=6;$i++): ?>
              <option value="Type <?= $i ?>" <?= $data['unit_type']==="Type $i"?'selected':'' ?>>Type <?= $i ?></option>
              <?php endfor; ?>
            </select>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Title *</label>
          <input type="text" name="title" id="titleInput" class="form-control" required value="<?= htmlspecialchars($data['title']) ?>" placeholder="Auto-filled from tower/type/area">
        </div>
        <div class="mb-3">
          <label class="form-label">Description *</label>
          <textarea name="description" class="form-control" rows="5" required><?= htmlspecialchars($data['description']) ?></textarea>
        </div>
      </div>

      <div class="form-card mb-4">
        <div class="form-section-title">Specifications</div>
        <div class="row g-3">
          <div class="col-6 col-md-3"><label class="form-label">Bedrooms</label><input type="number" name="bedrooms" id="bedsInput" class="form-control" value="<?= $data['bedrooms'] ?>"></div>
          <div class="col-6 col-md-3"><label class="form-label">Bathrooms</label><input type="number" name="bathrooms" class="form-control" value="<?= $data['bathrooms'] ?>"></div>
          <div class="col-6 col-md-3"><label class="form-label">Area (m²)</label><input type="number" name="area" id="areaInput" class="form-control" step="0.5" required value="<?= $data['area'] ?>"></div>
          <div class="col-6 col-md-3"><label class="form-label">Price/m²</label><input type="number" name="price_per_m2" id="ppmInput" class="form-control" value="<?= $data['price_per_m2'] ?>"></div>
        </div>
      </div>

      <div class="form-card">
        <div class="form-section-title">Amenities (one per line)</div>
        <textarea name="amenities" class="form-control" rows="8"><?= htmlspecialchars($defaultAmenities) ?></textarea>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="form-card mb-4">
        <div class="form-section-title">Pricing & Status</div>
        <div class="mb-3">
          <label class="form-label">Total Price (ETB) *</label>
          <input type="number" name="total_price" id="priceInput" class="form-control" required value="<?= $data['total_price'] ?>">
        </div>
        <div class="mb-3">
          <div class="p-2 rounded" style="background:var(--gold-bg);border:1px solid var(--gold-light)">
            <div class="small text-muted">10% Down Payment</div>
            <div class="fw-800" style="color:var(--gold)" id="dpDisplay">—</div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="available">Available</option>
            <option value="reserved">Reserved</option>
            <option value="sold">Sold</option>
          </select>
        </div>
        <div class="form-check"><input class="form-check-input" type="checkbox" name="featured" id="featured" <?= $data['featured']?'checked':'' ?>><label class="form-check-label fw-600" for="featured"><i class="bi bi-star-fill text-warning me-1"></i>Featured</label></div>
      </div>

      <!-- Quick reference -->
      <div class="form-card mb-4" style="background:var(--gold-bg);border-color:var(--gold-light)">
        <div class="form-section-title">Wing Reference Prices</div>
        <div id="refTable" class="small"></div>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-gold btn-lg"><i class="bi bi-plus-circle me-2"></i>Add Apartment</button>
        <a href="properties.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </div>
  </div>
</form>

<script>
const wingData = <?= json_encode($wingData) ?>;

function updateRef() {
  const tower = document.getElementById('towerSel').value;
  const type  = document.getElementById('typeSel').value;
  const data  = wingData[tower] && wingData[tower][type];
  const refEl = document.getElementById('refTable');

  if (data) {
    const [beds, area, ppm, price] = data;
    document.getElementById('bedsInput').value  = beds;
    document.getElementById('areaInput').value  = area;
    document.getElementById('ppmInput').value   = ppm;
    document.getElementById('priceInput').value = price;
    document.getElementById('titleInput').value = beds+' Bedroom '+beds+'BR '+area+'m² | '+tower;
    updateDP();
  }

  // Show full tower ref
  const towerData = wingData[tower];
  if (towerData) {
    let html = '<table class="table table-sm mb-0"><thead><tr><th>Type</th><th>BR</th><th>m²</th><th>Price</th></tr></thead><tbody>';
    for (const [t, d] of Object.entries(towerData)) {
      const active = t===type ? 'style="background:rgba(201,168,76,0.2)"' : '';
      html += `<tr ${active}><td><b>${t}</b></td><td>${d[0]}BR</td><td>${d[1]}</td><td>${(d[3]/1000000).toFixed(1)}M</td></tr>`;
    }
    html += '</tbody></table>';
    refEl.innerHTML = html;
  }
}

function updateDP() {
  const price = parseFloat(document.getElementById('priceInput').value) || 0;
  const dp    = Math.round(price * 0.10);
  document.getElementById('dpDisplay').textContent = dp ? 'ETB '+dp.toLocaleString() : '—';
}

document.getElementById('towerSel').addEventListener('change', updateRef);
document.getElementById('typeSel').addEventListener('change', updateRef);
document.getElementById('priceInput').addEventListener('input', updateDP);
document.addEventListener('DOMContentLoaded', function() { updateRef(); updateDP(); });
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
