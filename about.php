<?php
$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-3">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item active text-white-50">About</li>
      </ol>
    </nav>
    <h1 class="text-white fw-bold">About Getas Real Estate</h1>
    <p style="color:rgba(255,255,255,0.7);margin:0">Addis Ababa's trusted partner for premium City Gate apartments.</p>
  </div>
</div>

<!-- Mission -->
<section class="section-py">
  <div class="container">
    <div class="row g-5 align-items-center">
      <div class="col-lg-6">
        <div class="section-label">Our Story</div>
        <h2 class="section-title">Building Dreams in Addis Ababa</h2>
        <p class="text-muted"><?= htmlspecialchars($settings['about_short'] ?? '') ?></p>
        <p class="text-muted">Our flagship development — <strong>City Gate</strong> — comprises three iconic towers (Wing 1, Wing 2 and Wing 3) in the heart of Addis Ababa, offering premium 2 and 3 bedroom apartments designed for modern Ethiopian living.</p>
        <p class="text-muted">Every City Gate unit features dual balconies, maid's room, laundry room, dedicated parking, and international-grade finishes — all backed by a flexible 10% down payment entry plan.</p>
        <div class="row g-3 mt-2">
          <div class="col-4">
            <div class="d-flex align-items-center gap-2">
              <div class="feature-icon" style="width:44px;height:44px;border-radius:10px"><i class="bi bi-buildings" style="font-size:1.1rem"></i></div>
              <div><div class="fw-700" style="font-size:1.3rem;color:var(--dark)">3</div><div class="small text-muted">Towers</div></div>
            </div>
          </div>
          <div class="col-4">
            <div class="d-flex align-items-center gap-2">
              <div class="feature-icon" style="width:44px;height:44px;border-radius:10px"><i class="bi bi-grid-3x3-gap" style="font-size:1.1rem"></i></div>
              <div><div class="fw-700" style="font-size:1.3rem;color:var(--dark)">18</div><div class="small text-muted">Unit Types</div></div>
            </div>
          </div>
          <div class="col-4">
            <div class="d-flex align-items-center gap-2">
              <div class="feature-icon" style="width:44px;height:44px;border-radius:10px"><i class="bi bi-percent" style="font-size:1.1rem"></i></div>
              <div><div class="fw-700" style="font-size:1.3rem;color:var(--dark)">10%</div><div class="small text-muted">To Reserve</div></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <div class="col-6">
            <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600" alt="City Gate 1" class="img-fluid rounded-xl shadow-soft" style="height:220px;object-fit:cover;width:100%">
          </div>
          <div class="col-6">
            <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=600" alt="City Gate 2" class="img-fluid rounded-xl shadow-soft" style="height:220px;object-fit:cover;width:100%">
          </div>
          <div class="col-12">
            <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800" alt="Interior" class="img-fluid rounded-xl shadow-soft" style="height:200px;object-fit:cover;width:100%">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Values -->
<section class="section-py bg-light-gray">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Our Values</div>
      <h2 class="section-title">Why Buyers Trust Us</h2>
    </div>
    <div class="row g-4">
      <?php
      $values = [
        ['bi-award','Transparency','No hidden costs, no surprises. Full disclosure on pricing, title deeds and legalities.'],
        ['bi-shield-check','Legal Security','All properties have clear Ethiopian title deeds, verified and registered.'],
        ['bi-people','Client Focus','We listen, advise and guide — never rush. Your dream home is our mission.'],
        ['bi-buildings-fill','Quality Build','International-standard construction with German and Italian fixtures and fittings.'],
        ['bi-graph-up','Investment Value','Both sites are in high-growth areas with strong resale and rental potential.'],
        ['bi-headset','After-Sale Support','We stay with you after handover — maintenance referrals, legal support and more.'],
      ];
      foreach ($values as $v): ?>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card text-center">
          <div class="feature-icon mx-auto"><i class="bi <?= $v[0] ?>"></i></div>
          <h6 class="fw-700 mb-2" style="color:var(--dark)"><?= $v[1] ?></h6>
          <p class="small text-muted mb-0"><?= $v[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section section-py">
  <div class="container position-relative" style="z-index:1">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h2 class="section-title text-white">Ready to Find Your City Gate Apartment?</h2>
        <p style="color:rgba(255,255,255,0.7)">Browse available units across City Gate 1, 2 and 3, or book a free site visit today.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <a href="properties.php" class="btn btn-gold btn-lg me-3">View Apartments</a>
        <a href="contact.php" class="btn btn-outline-light btn-lg">Contact Us</a>
      </div>
    </div>
  </div>
</section>

<!-- WhatsApp Float -->
<a href="https://wa.me/251911234567" target="_blank" class="wa-float"><i class="bi bi-whatsapp"></i></a>

<?php require_once 'includes/footer.php'; ?>
