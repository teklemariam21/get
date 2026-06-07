<?php
require_once 'includes/header.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: properties.php'); exit; }

$stmt = $db->prepare("SELECT * FROM properties WHERE slug=:slug LIMIT 1");
$stmt->execute([':slug'=>$slug]);
$p = $stmt->fetch();
if (!$p) { header('Location: properties.php'); exit; }

$db->prepare("UPDATE properties SET views=views+1 WHERE id=:id")->execute([':id'=>$p['id']]);
$pageTitle = $p['title'];
$images    = json_decode($p['images']    ?? '[]', true);
$amenities = json_decode($p['amenities'] ?? '[]', true);

$typeImgs = [
  2 => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200',
  3 => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200',
];
if (empty($images)) $images = [$typeImgs[$p['bedrooms']] ?? $typeImgs[2]];

// Similar units in same tower
$simStmt = $db->prepare("SELECT * FROM properties WHERE tower=:t AND id!=:id AND status='available' LIMIT 3");
$simStmt->execute([':t'=>$p['tower'],':id'=>$p['id']]);
$similar = $simStmt->fetchAll();

// Applicable payment plans
$ppStmt = $db->prepare("SELECT DISTINCT pp.* FROM payment_plans pp WHERE pp.bedrooms=:bed ORDER BY pp.completion_pct DESC, pp.down_payment_pct ASC");
$ppStmt->execute([':bed'=>$p['bedrooms']]);
$paymentPlans = $ppStmt->fetchAll();

// Inquiry
$inquirySuccess = false; $inquiryError = '';
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['submit_inquiry'])) {
    $name = sanitize($_POST['inq_name']??''); $email = filter_input(INPUT_POST,'inq_email',FILTER_SANITIZE_EMAIL);
    $phone = sanitize($_POST['inq_phone']??''); $message = sanitize($_POST['inq_message']??'');
    if (!$name||!$email||!$phone||!$message) { $inquiryError = 'Please fill in all fields.'; }
    elseif (!filter_var($email,FILTER_VALIDATE_EMAIL)) { $inquiryError = 'Invalid email address.'; }
    else {
        $ins = $db->prepare("INSERT INTO inquiries (name,email,phone,message,property_id,tower,ip_address) VALUES (:n,:e,:p,:m,:pid,:t,:ip)");
        $ins->execute([':n'=>$name,':e'=>$email,':p'=>$phone,':m'=>"Re: {$p['title']}\n\n$message",':pid'=>$p['id'],':t'=>$p['tower'],':ip'=>$_SERVER['REMOTE_ADDR']]);
        $inquirySuccess = true;
    }
}
?>

<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="properties.php">Apartments</a></li>
        <li class="breadcrumb-item"><a href="properties.php?tower=<?= urlencode($p['tower']) ?>"><?= htmlspecialchars($p['tower']) ?></a></li>
        <li class="breadcrumb-item active text-white-50"><?= htmlspecialchars($p['unit_type']) ?></li>
      </ol>
    </nav>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <h1 class="text-white fw-bold mb-0"><?= htmlspecialchars($p['title']) ?></h1>
      <span class="status-badge status-<?= $p['status'] ?> ms-2"><?= ucfirst($p['status']) ?></span>
    </div>
    <div class="d-flex gap-3 mt-2 flex-wrap" style="color:rgba(255,255,255,0.65);font-size:0.875rem">
      <span><i class="bi bi-buildings me-1 text-gold"></i><?= htmlspecialchars($p['tower']) ?></span>
      <span><i class="bi bi-rulers me-1 text-gold"></i><?= number_format($p['area']) ?> m²</span>
      <span><i class="bi bi-door-closed me-1 text-gold"></i><?= $p['bedrooms'] ?> Bedrooms</span>
      <span><i class="bi bi-eye me-1 text-gold"></i><?= number_format($p['views']) ?> views</span>
    </div>
  </div>
</div>

<div class="container py-4">
  <div class="row g-4">
    <!-- Left -->
    <div class="col-lg-8">
      <!-- Gallery -->
      <div class="detail-hero mb-2">
        <img id="mainPropertyImage" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
      </div>
      <?php if (count($images)>1): ?>
      <div class="detail-thumbnails">
        <?php foreach ($images as $i=>$img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="Photo <?= $i+1 ?>" class="<?= $i===0?'active':'' ?>" onclick="changeMainImage('<?= htmlspecialchars($img) ?>')">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Price + specs -->
      <div class="form-card mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <div class="text-muted small mb-1">Total Price</div>
            <div class="price-tag">ETB <?= number_format($p['total_price']) ?></div>
            <div class="small mt-1" style="color:var(--gray-600)">
              ETB <?= number_format($p['price_per_m2']) ?>/m²
            </div>
          </div>
          <div>
            <div class="text-muted small mb-1">10% Down Payment</div>
            <div class="fw-800" style="font-size:1.4rem;color:#166534">ETB <?= number_format($p['down_payment_10pct']) ?></div>
            <div class="small text-muted">to secure this unit</div>
          </div>
          <div class="d-flex gap-2 align-items-start">
            <a href="contact.php?property=<?= urlencode($p['title']) ?>&tower=<?= urlencode($p['tower']) ?>" class="btn btn-gold">
              <i class="bi bi-calendar3 me-1"></i>Book Viewing
            </a>
            <a href="https://wa.me/251911234567?text=<?= urlencode("I'm interested in: ".$p['title']) ?>" target="_blank" class="btn btn-outline-secondary" title="WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
          </div>
        </div>
        <hr>
        <div class="row g-3">
          <div class="col-6 col-md-3"><div class="text-muted small">Tower</div><div class="fw-700"><?= htmlspecialchars($p['tower']) ?></div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Unit Type</div><div class="fw-700"><?= htmlspecialchars($p['unit_type']) ?></div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Bedrooms</div><div class="fw-700"><i class="bi bi-door-closed text-gold me-1"></i><?= $p['bedrooms'] ?></div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Bathrooms</div><div class="fw-700"><i class="bi bi-droplet text-gold me-1"></i><?= $p['bathrooms'] ?></div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Area</div><div class="fw-700"><i class="bi bi-rulers text-gold me-1"></i><?= number_format($p['area']) ?> m²</div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Balconies</div><div class="fw-700"><i class="bi bi-snow text-gold me-1"></i>2 Private</div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Maid's Room</div><div class="fw-700"><i class="bi bi-check-circle-fill text-success me-1"></i>Included</div></div>
          <div class="col-6 col-md-3"><div class="text-muted small">Laundry</div><div class="fw-700"><i class="bi bi-check-circle-fill text-success me-1"></i>Included</div></div>
        </div>
      </div>

      <!-- Description -->
      <div class="form-card mt-4">
        <div class="form-section-title">About This Apartment</div>
        <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
      </div>

      <!-- Amenities -->
      <?php if (!empty($amenities)): ?>
      <div class="form-card mt-4">
        <div class="form-section-title">Building Amenities & Features</div>
        <?php foreach ($amenities as $am): ?>
        <span class="amenity-pill"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($am) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Payment Plans -->
      <?php if (!empty($paymentPlans)): ?>
      <div class="form-card mt-4">
        <div class="form-section-title">Available Payment Plans for <?= $p['bedrooms'] ?>-Bedroom Units</div>
        <div class="table-responsive">
          <table class="table table-sm small mb-0">
            <thead style="background:var(--dark);color:#fff">
              <tr>
                <th>Plan</th>
                <th>Area</th>
                <th>Total Price</th>
                <th>Down Payment</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($paymentPlans as $pp): ?>
            <tr>
              <td>
                <div class="fw-600 small"><?= htmlspecialchars($pp['plan_name']) ?></div>
              </td>
              <td><?= (int)$pp['area'] ?>m²</td>
              <td class="fw-600" style="color:var(--gold)">ETB <?= number_format($pp['total_price']) ?></td>
              <td class="fw-700" style="color:#166534">ETB <?= number_format($pp['down_payment_amount']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="small text-muted mt-2"><i class="bi bi-info-circle me-1 text-gold"></i>Prices vary by completion stage. Contact us for the latest available units.</div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Right: Inquiry -->
    <div class="col-lg-4">
      <div class="inquiry-card">
        <?php if ($inquirySuccess): ?>
        <div class="alert alert-success alert-auto-dismiss small"><i class="bi bi-check-circle-fill me-2"></i>Thank you! We'll contact you shortly.</div>
        <?php elseif ($inquiryError): ?>
        <div class="alert alert-danger small"><?= htmlspecialchars($inquiryError) ?></div>
        <?php endif; ?>
        <h5 class="fw-700 mb-1" style="font-family:'Playfair Display',serif;color:var(--dark)">Request Information</h5>
        <p class="text-muted small mb-3">We respond within 2 hours, Mon–Sat.</p>
        <form method="POST" class="needs-validation" novalidate>
          <div class="mb-3"><label class="form-label small fw-600">Full Name *</label><input type="text" name="inq_name" class="form-control" required placeholder="Abebe Kebede"></div>
          <div class="mb-3"><label class="form-label small fw-600">Email *</label><input type="email" name="inq_email" class="form-control" required placeholder="you@example.com"></div>
          <div class="mb-3"><label class="form-label small fw-600">Phone *</label><input type="tel" name="inq_phone" class="form-control" required placeholder="+251 9XX XXX XXX"></div>
          <div class="mb-3">
            <label class="form-label small fw-600">Message *</label>
            <textarea name="inq_message" class="form-control" rows="4" required><?= htmlspecialchars("I'm interested in the {$p['title']} ({$p['unit_type']}, ".number_format($p['area'])."m²). Please contact me to arrange a viewing.") ?></textarea>
          </div>
          <button type="submit" name="submit_inquiry" class="btn btn-gold w-100 py-2 fw-600">
            <i class="bi bi-send me-2"></i>Send Enquiry
          </button>
        </form>
        <hr>
        <div class="text-center">
          <div class="small text-muted mb-2">Or call us directly</div>
          <a href="tel:+251911234567" class="btn btn-outline-gold w-100 mb-2 btn-sm">
            <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($settings['phone_1']??'+251 911 234 567') ?>
          </a>
          <a href="https://wa.me/251911234567?text=<?= urlencode("I'm interested in: ".$p['title']) ?>" target="_blank" class="btn w-100 btn-sm" style="background:#25D366;color:#fff;border-radius:8px">
            <i class="bi bi-whatsapp me-2"></i>WhatsApp Us
          </a>
        </div>

        <!-- Unit summary card -->
        <div class="mt-3 p-3 rounded-xl" style="background:var(--gold-bg);border:1px solid var(--gold-light)">
          <div class="small fw-700 mb-2" style="color:var(--dark)">Unit Summary</div>
          <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Tower</span><span class="fw-600"><?= htmlspecialchars($p['tower']) ?></span></div>
          <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Type</span><span class="fw-600"><?= htmlspecialchars($p['unit_type']) ?></span></div>
          <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Area</span><span class="fw-600"><?= number_format($p['area']) ?> m²</span></div>
          <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Beds/Baths</span><span class="fw-600"><?= $p['bedrooms'] ?> / <?= $p['bathrooms'] ?></span></div>
          <hr class="my-2">
          <div class="d-flex justify-content-between small"><span class="text-muted">Total Price</span><span class="fw-800" style="color:var(--gold)">ETB <?= number_format($p['total_price']) ?></span></div>
          <div class="d-flex justify-content-between small mt-1"><span class="text-muted">10% Down</span><span class="fw-700" style="color:#166534">ETB <?= number_format($p['down_payment_10pct']) ?></span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Similar -->
  <?php if (!empty($similar)): ?>
  <div class="mt-5">
    <div class="section-label">More in <?= htmlspecialchars($p['tower']) ?></div>
    <h3 class="section-title mb-4">Similar Apartments</h3>
    <div class="row g-4">
      <?php foreach ($similar as $sp):
        $spImgs = json_decode($sp['images']??'[]',true);
        $spImg  = !empty($spImgs[0]) ? $spImgs[0] : ($typeImgs[$sp['bedrooms']]??$typeImgs[2]);
      ?>
      <div class="col-md-4">
        <div class="property-card h-100">
          <div class="card-img-wrap">
            <a href="property.php?slug=<?= urlencode($sp['slug']) ?>">
              <img src="<?= htmlspecialchars($spImg) ?>" alt="<?= htmlspecialchars($sp['title']) ?>" loading="lazy">
            </a>
            <span class="badge-site"><?= htmlspecialchars($sp['unit_type']) ?></span>
          </div>
          <div class="card-body">
            <h3 class="property-title"><a href="property.php?slug=<?= urlencode($sp['slug']) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($sp['title']) ?></a></h3>
            <div class="property-price">ETB <?= number_format($sp['total_price']) ?></div>
            <div class="property-specs">
              <div class="spec-item"><i class="bi bi-door-closed"></i><?= $sp['bedrooms'] ?> Bed</div>
              <div class="spec-item"><i class="bi bi-rulers"></i><?= number_format($sp['area']) ?>m²</div>
            </div>
            <a href="property.php?slug=<?= urlencode($sp['slug']) ?>" class="btn btn-outline-gold btn-sm w-100">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<a href="https://wa.me/251911234567?text=<?= urlencode("I'm interested in: ".$p['title']) ?>" target="_blank" class="wa-float"><i class="bi bi-whatsapp"></i></a>
<?php require_once 'includes/footer.php'; ?>
