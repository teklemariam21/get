<?php
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
if (!$slug) { header('Location: properties.php'); exit; }

$stmt = $db->prepare("SELECT * FROM properties WHERE slug = :slug LIMIT 1");
$stmt->execute([':slug' => $slug]);
$prop = $stmt->fetch();
if (!$prop) { header('Location: properties.php'); exit; }

// Increment views
$db->prepare("UPDATE properties SET views = views + 1 WHERE id = :id")->execute([':id' => $prop['id']]);

$pageTitle = $prop['title'];
$images    = json_decode($prop['images']    ?? '[]', true);
$amenities = json_decode($prop['amenities'] ?? '[]', true);
$typeImages = [
  '1BR' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1200',
  '2BR' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200',
  '3BR' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1200',
];
if (empty($images)) $images = [$typeImages[$prop['type']] ?? 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1200'];
$mainImg = $images[0];

// Similar properties
$simStmt = $db->prepare("SELECT * FROM properties WHERE type = :type AND id != :id AND status = 'available' LIMIT 3");
$simStmt->execute([':type' => $prop['type'], ':id' => $prop['id']]);
$similar = $simStmt->fetchAll();

// Handle inquiry submission
$inquirySuccess = false;
$inquiryError   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $name    = sanitize($_POST['inq_name']    ?? '');
    $email   = filter_input(INPUT_POST, 'inq_email', FILTER_SANITIZE_EMAIL);
    $phone   = sanitize($_POST['inq_phone']   ?? '');
    $message = sanitize($_POST['inq_message'] ?? '');

    if (!$name || !$email || !$phone || !$message) {
        $inquiryError = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $inquiryError = 'Please enter a valid email address.';
    } else {
        $ins = $db->prepare("INSERT INTO inquiries (name, email, phone, message, property_id, ip_address) VALUES (:n,:e,:p,:m,:pid,:ip)");
        $ins->execute([
            ':n'   => $name,
            ':e'   => $email,
            ':p'   => $phone,
            ':m'   => "Re: " . $prop['title'] . "\n\n" . $message,
            ':pid' => $prop['id'],
            ':ip'  => $_SERVER['REMOTE_ADDR'],
        ]);
        $inquirySuccess = true;
    }
}
?>

<!-- Page Hero -->
<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="properties.php">Apartments</a></li>
        <li class="breadcrumb-item"><a href="properties.php?site=<?= urlencode($prop['site']) ?>"><?= htmlspecialchars($prop['site']) ?></a></li>
        <li class="breadcrumb-item active text-white-50"><?= htmlspecialchars($prop['type']) ?></li>
      </ol>
    </nav>
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <h1 class="text-white fw-bold mb-0"><?= htmlspecialchars($prop['title']) ?></h1>
      <span class="status-badge status-<?= $prop['status'] ?> ms-2"><?= ucfirst($prop['status']) ?></span>
    </div>
    <div class="d-flex gap-3 mt-2" style="color:rgba(255,255,255,0.65);font-size:0.875rem">
      <span><i class="bi bi-buildings me-1 text-gold"></i><?= htmlspecialchars($prop['site']) ?></span>
      <span><i class="bi bi-rulers me-1 text-gold"></i><?= number_format($prop['area']) ?> m²</span>
      <span><i class="bi bi-eye me-1 text-gold"></i><?= number_format($prop['views']) ?> views</span>
    </div>
  </div>
</div>

<div class="container py-4">
  <div class="row g-4">
    <!-- Left: Images + Details -->
    <div class="col-lg-8">
      <!-- Image Gallery -->
      <div class="detail-hero mb-2">
        <img id="mainPropertyImage" src="<?= htmlspecialchars($mainImg) ?>" alt="<?= htmlspecialchars($prop['title']) ?>">
      </div>
      <?php if (count($images) > 1): ?>
      <div class="detail-thumbnails">
        <?php foreach ($images as $i => $img): ?>
        <img src="<?= htmlspecialchars($img) ?>" alt="Photo <?= $i+1 ?>"
             class="<?= $i===0?'active':'' ?>"
             onclick="changeMainImage('<?= htmlspecialchars($img) ?>')">
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!-- Price + Quick Specs -->
      <div class="form-card mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
          <div>
            <div class="text-muted small mb-1">Sale Price</div>
            <div class="price-tag"><?= formatPrice($prop['price']) ?></div>
          </div>
          <div class="d-flex gap-2">
            <a href="contact.php?property=<?= urlencode($prop['title']) ?>" class="btn btn-gold">
              <i class="bi bi-calendar3 me-2"></i>Book Viewing
            </a>
            <a href="https://wa.me/251911234567?text=I'm interested in: <?= urlencode($prop['title']) ?>" target="_blank" class="btn btn-outline-secondary" title="WhatsApp">
              <i class="bi bi-whatsapp"></i>
            </a>
          </div>
        </div>
        <hr>
        <div class="row g-3">
          <div class="col-6 col-md-3">
            <div class="text-muted small">Bedrooms</div>
            <div class="fw-700"><i class="bi bi-door-closed text-gold me-1"></i><?= $prop['bedrooms'] ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Bathrooms</div>
            <div class="fw-700"><i class="bi bi-droplet text-gold me-1"></i><?= $prop['bathrooms'] ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Area</div>
            <div class="fw-700"><i class="bi bi-rulers text-gold me-1"></i><?= number_format($prop['area']) ?> m²</div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Parking</div>
            <div class="fw-700"><i class="bi bi-car-front text-gold me-1"></i><?= $prop['parking'] ?: 'Included' ?></div>
          </div>
          <?php if ($prop['floor_number']): ?>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Floor</div>
            <div class="fw-700"><i class="bi bi-layers text-gold me-1"></i><?= $prop['floor_number'] ?></div>
          </div>
          <?php endif; ?>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Balcony</div>
            <div class="fw-700"><?= $prop['balcony'] ? '<i class="bi bi-check-circle-fill text-success me-1"></i>Yes' : '<i class="bi bi-dash-circle text-muted me-1"></i>No' ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Type</div>
            <div class="fw-700"><?= htmlspecialchars($prop['type']) ?></div>
          </div>
          <div class="col-6 col-md-3">
            <div class="text-muted small">Site</div>
            <div class="fw-700"><?= htmlspecialchars($prop['site']) ?></div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="form-card mt-4">
        <div class="form-section-title">About This Apartment</div>
        <p class="text-muted"><?= nl2br(htmlspecialchars($prop['description'])) ?></p>
      </div>

      <!-- Amenities -->
      <?php if (!empty($amenities)): ?>
      <div class="form-card mt-4">
        <div class="form-section-title">Amenities & Features</div>
        <div>
          <?php foreach ($amenities as $am): ?>
          <span class="amenity-pill"><i class="bi bi-check-circle-fill"></i><?= htmlspecialchars($am) ?></span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Right: Inquiry Form -->
    <div class="col-lg-4">
      <div class="inquiry-card">
        <?php if ($inquirySuccess): ?>
        <div class="alert alert-success alert-auto-dismiss">
          <i class="bi bi-check-circle-fill me-2"></i>
          Thank you! We'll contact you shortly.
        </div>
        <?php endif; ?>
        <?php if ($inquiryError): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($inquiryError) ?></div>
        <?php endif; ?>
        <h5 class="fw-700 mb-1" style="font-family:'Playfair Display',serif;color:var(--dark)">Request Information</h5>
        <p class="text-muted small mb-3">Our team will respond within 2 hours.</p>
        <form method="POST" class="needs-validation" novalidate>
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="inq_name" class="form-control" placeholder="Your full name" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email Address *</label>
            <input type="email" name="inq_email" class="form-control" placeholder="you@example.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="inq_phone" class="form-control" placeholder="+251 9XX XXX XXX" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Message *</label>
            <textarea name="inq_message" class="form-control" rows="4" required
              placeholder="I am interested in this apartment and would like more information..."><?= htmlspecialchars("I'm interested in the {$prop['title']} and would like to schedule a viewing.") ?></textarea>
          </div>
          <button type="submit" name="submit_inquiry" class="btn btn-gold w-100 py-2">
            <i class="bi bi-send me-2"></i>Send Enquiry
          </button>
        </form>
        <hr>
        <div class="text-center">
          <div class="small text-muted mb-2">Or call us directly</div>
          <a href="tel:+251911234567" class="btn btn-outline-gold w-100 mb-2">
            <i class="bi bi-telephone me-2"></i><?= htmlspecialchars($settings['phone_1'] ?? '+251 911 234 567') ?>
          </a>
          <a href="https://wa.me/251911234567?text=I'm interested in: <?= urlencode($prop['title']) ?>" target="_blank" class="btn w-100" style="background:#25D366;color:#fff;border:none;border-radius:8px">
            <i class="bi bi-whatsapp me-2"></i>WhatsApp Us
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Similar Properties -->
  <?php if (!empty($similar)): ?>
  <div class="mt-5">
    <div class="section-label">Similar Apartments</div>
    <h3 class="section-title mb-4">You Might Also Like</h3>
    <div class="row g-4">
      <?php foreach ($similar as $sp):
        $spImages = json_decode($sp['images'] ?? '[]', true);
        $spImg = !empty($spImages[0]) ? $spImages[0] : ($typeImages[$sp['type']] ?? '');
      ?>
      <div class="col-md-4">
        <div class="property-card h-100">
          <div class="card-img-wrap">
            <a href="property.php?slug=<?= urlencode($sp['slug']) ?>">
              <img src="<?= htmlspecialchars($spImg) ?>" alt="<?= htmlspecialchars($sp['title']) ?>" loading="lazy">
            </a>
            <span class="badge-site"><?= htmlspecialchars($sp['site']) ?></span>
          </div>
          <div class="card-body">
            <h3 class="property-title"><a href="property.php?slug=<?= urlencode($sp['slug']) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($sp['title']) ?></a></h3>
            <div class="property-price"><?= formatPrice($sp['price']) ?></div>
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

<!-- WhatsApp Float -->
<a href="https://wa.me/251911234567?text=I'm interested in: <?= urlencode($prop['title']) ?>" target="_blank" class="wa-float">
  <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
