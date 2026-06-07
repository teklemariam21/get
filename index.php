<?php
$pageTitle = 'Premium Apartments in Addis Ababa | Summit 72 & Kazanchis';
require_once 'includes/header.php';

// Featured properties
$stmt = $db->prepare("SELECT * FROM properties WHERE featured = 1 AND status = 'available' ORDER BY created_at DESC LIMIT 6");
$stmt->execute();
$featured = $stmt->fetchAll();

// Counts for stats
$totalProps = $db->query("SELECT COUNT(*) FROM properties WHERE status='available'")->fetchColumn();
$summit72   = $db->query("SELECT COUNT(*) FROM properties WHERE site='Summit 72'")->fetchColumn();
$kazanchis  = $db->query("SELECT COUNT(*) FROM properties WHERE site='Kazanchis'")->fetchColumn();

// Testimonials
$testimonials = $db->query("SELECT * FROM testimonials WHERE approved=1 ORDER BY id DESC LIMIT 4")->fetchAll();

// Property images map (using Unsplash for demo)
$siteImages = [
  'Summit 72' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=800',
  'Kazanchis' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=800',
];
$typeImages = [
  '1BR' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
  '2BR' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
  '3BR' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
];
?>

<!-- ======================== HERO ======================== -->
<section class="hero">
  <div class="container position-relative" style="z-index:2">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge">
          <i class="bi bi-stars"></i> New Apartments Now Available
        </div>
        <h1>Premium Living in the Heart of <span>Addis Ababa</span></h1>
        <p class="mb-0">Discover luxury apartments at <strong style="color:var(--gold)">Summit 72</strong> and <strong style="color:var(--gold)">Kazanchis</strong> — 1, 2 and 3 bedroom residences crafted to the highest standard.</p>

        <!-- Stats -->
        <div class="hero-stats row g-3 mt-2">
          <div class="col-4">
            <div class="stat-item">
              <div class="stat-number" data-count="<?= $totalProps ?>">0</div>
              <div class="stat-label">Units Available</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <div class="stat-number">2</div>
              <div class="stat-label">Prime Locations</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <div class="stat-number">100%</div>
              <div class="stat-label">Title Deed Ready</div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-3 mt-4">
          <a href="properties.php" class="btn btn-gold btn-lg px-4">
            <i class="bi bi-search me-2"></i>View Apartments
          </a>
          <a href="contact.php" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-calendar3 me-2"></i>Book Viewing
          </a>
        </div>
      </div>

      <!-- Search Card -->
      <div class="col-lg-6">
        <div class="hero-search">
          <h5 class="fw-700 mb-3" style="font-family:'Playfair Display',serif;color:var(--dark)">Find Your Apartment</h5>
          <form action="properties.php" method="GET">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Location</label>
                <select name="site" class="form-select">
                  <option value="">All Locations</option>
                  <option value="Summit 72">Summit 72</option>
                  <option value="Kazanchis">Kazanchis</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Apartment Type</label>
                <select name="type" class="form-select">
                  <option value="">All Types</option>
                  <option value="1BR">1 Bedroom</option>
                  <option value="2BR">2 Bedroom</option>
                  <option value="3BR">3 Bedroom</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Min Size (m²)</label>
                <select name="min_area" class="form-select">
                  <option value="">Any Size</option>
                  <option value="61">61m²+</option>
                  <option value="109">109m²+</option>
                  <option value="144">144m²+</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Max Budget</label>
                <select name="max_price" class="form-select">
                  <option value="">Any Price</option>
                  <option value="3000000">Up to ETB 3M</option>
                  <option value="5000000">Up to ETB 5M</option>
                  <option value="7000000">Up to ETB 7M</option>
                </select>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-gold w-100 py-2">
                  <i class="bi bi-search me-2"></i>Search Apartments
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ======================== SITES ======================== -->
<section class="section-py-sm">
  <div class="container">
    <div class="text-center mb-4">
      <div class="section-label">Our Developments</div>
      <h2 class="section-title">Two Prime Locations</h2>
    </div>
    <div class="row g-4">
      <div class="col-md-6">
        <a href="properties.php?site=Summit+72" class="d-block text-decoration-none">
          <div class="site-card">
            <img src="https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=900" alt="Summit 72">
            <div class="site-card-overlay">
              <div class="d-flex justify-content-between align-items-end">
                <div>
                  <h3 class="fw-bold mb-1">Summit 72</h3>
                  <p>Bole, Addis Ababa • <?= $summit72 ?> Apartments</p>
                </div>
                <span class="btn btn-gold btn-sm">View All <i class="bi bi-arrow-right ms-1"></i></span>
              </div>
            </div>
          </div>
        </a>
      </div>
      <div class="col-md-6">
        <a href="properties.php?site=Kazanchis" class="d-block text-decoration-none">
          <div class="site-card">
            <img src="https://images.unsplash.com/photo-1486325212027-8081e485255e?w=900" alt="Kazanchis">
            <div class="site-card-overlay">
              <div class="d-flex justify-content-between align-items-end">
                <div>
                  <h3 class="fw-bold mb-1">Kazanchis</h3>
                  <p>Kirkos, Addis Ababa • <?= $kazanchis ?> Apartments</p>
                </div>
                <span class="btn btn-gold btn-sm">View All <i class="bi bi-arrow-right ms-1"></i></span>
              </div>
            </div>
          </div>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ======================== APARTMENT PLANS ======================== -->
<section class="section-py bg-light-gray">
  <div class="container">
    <div class="row align-items-center mb-5">
      <div class="col-md-7">
        <div class="section-label">Floor Plans</div>
        <h2 class="section-title">Available Apartment Sizes</h2>
        <p class="section-desc">All apartments available in both Summit 72 and Kazanchis. Choose the layout that fits your lifestyle.</p>
      </div>
      <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="properties.php" class="btn btn-outline-gold">View All Listings <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table specs-table bg-white">
        <thead>
          <tr>
            <th>Type</th>
            <th>Size</th>
            <th>Bedrooms</th>
            <th>Bathrooms</th>
            <th>Summit 72 Price</th>
            <th>Kazanchis Price</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $plans = [
            ['1BR', '61 m²',  1, 1, 2850000, 2700000],
            ['1BR', '65 m²',  1, 1, 3100000, 2950000],
            ['2BR', '109 m²', 2, 2, 4950000, 4750000],
            ['2BR', '115 m²', 2, 2, 5200000, 4950000],
            ['3BR', '144 m²', 3, 3, 6800000, 6500000],
            ['3BR', '151 m²', 3, 3, 7200000, 6900000],
          ];
          foreach ($plans as $p): ?>
          <tr>
            <td><span class="badge bg-dark"><?= $p[0] ?></span></td>
            <td><strong><?= $p[1] ?></strong></td>
            <td><?= $p[2] ?> <i class="bi bi-door-closed text-gold ms-1"></i></td>
            <td><?= $p[3] ?> <i class="bi bi-droplet text-gold ms-1"></i></td>
            <td class="price-cell"><?= formatPrice($p[4]) ?></td>
            <td class="price-cell"><?= formatPrice($p[5]) ?></td>
            <td><span class="status-badge status-available"><i class="bi bi-circle-fill" style="font-size:0.5rem"></i> Available</span></td>
            <td>
              <a href="properties.php?type=<?= urlencode($p[0]) ?>" class="btn btn-sm btn-gold">Enquire</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- ======================== FEATURED PROPERTIES ======================== -->
<section class="section-py">
  <div class="container">
    <div class="row align-items-center mb-4">
      <div class="col-md-8">
        <div class="section-label">Featured Listings</div>
        <h2 class="section-title">Available Apartments</h2>
      </div>
      <div class="col-md-4 text-md-end mt-2 mt-md-0">
        <a href="properties.php" class="btn btn-outline-gold">All Apartments <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>

    <div class="row g-4">
      <?php foreach ($featured as $prop):
        $images = json_decode($prop['images'] ?? '[]', true);
        $mainImg = !empty($images[0]) ? $images[0] : ($typeImages[$prop['type']] ?? 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800');
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="property-card">
          <div class="card-img-wrap">
            <a href="property.php?slug=<?= urlencode($prop['slug']) ?>">
              <img src="<?= htmlspecialchars($mainImg) ?>" alt="<?= htmlspecialchars($prop['title']) ?>" loading="lazy">
            </a>
            <span class="badge-site"><i class="bi bi-buildings me-1"></i><?= htmlspecialchars($prop['site']) ?></span>
            <span class="badge-status badge-<?= $prop['status'] ?>"><?= ucfirst($prop['status']) ?></span>
          </div>
          <div class="card-body">
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
              <?php if ($prop['parking']): ?>
              <div class="spec-item"><i class="bi bi-car-front"></i>Parking</div>
              <?php endif; ?>
            </div>
            <div class="d-flex gap-2">
              <a href="property.php?slug=<?= urlencode($prop['slug']) ?>" class="btn btn-outline-gold btn-sm flex-grow-1">View Details</a>
              <a href="contact.php?property=<?= urlencode($prop['title']) ?>" class="btn btn-gold btn-sm flex-grow-1">Enquire</a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ======================== WHY CHOOSE US ======================== -->
<section class="section-py bg-light-gray">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Why Getas Reality</div>
      <h2 class="section-title">The Smart Choice for Your New Home</h2>
      <p class="section-desc mx-auto">We provide end-to-end support from selection to handover.</p>
    </div>
    <div class="row g-4">
      <?php
      $features = [
        ['bi-shield-check','Title Deed Secured','All properties come with clear, registered title deeds and legal documentation fully in order.'],
        ['bi-building','Prime Locations','Summit 72 in Bole and Kazanchis in Kirkos — two of Addis Ababa\'s most sought-after addresses.'],
        ['bi-star','Premium Finishes','German and Italian fittings, imported tiles, and high-end fixtures throughout every apartment.'],
        ['bi-camera-video','24/7 Security','CCTV surveillance, trained security personnel, biometric access, and backup generator.'],
        ['bi-car-front','Parking & Amenities','Dedicated underground parking, swimming pool, gym, rooftop terrace and concierge service.'],
        ['bi-headset','Expert Guidance','Our experienced team walks you through every step — financing, legal, handover and beyond.'],
      ];
      foreach ($features as $f): ?>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card d-flex gap-3">
          <div class="feature-icon flex-shrink-0"><i class="bi <?= $f[0] ?>"></i></div>
          <div>
            <h6 class="fw-700 mb-1" style="color:var(--dark)"><?= $f[1] ?></h6>
            <p class="small text-muted mb-0"><?= $f[2] ?></p>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ======================== TESTIMONIALS ======================== -->
<?php if (!empty($testimonials)): ?>
<section class="section-py">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Client Stories</div>
      <h2 class="section-title">What Our Buyers Say</h2>
    </div>
    <div class="row g-4">
      <?php foreach ($testimonials as $t): ?>
      <div class="col-md-6 col-lg-3">
        <div class="testimonial-card">
          <div class="testimonial-stars">
            <?= str_repeat('<i class="bi bi-star-fill"></i>', (int)$t['rating']) ?>
          </div>
          <p class="small text-muted mb-3">"<?= htmlspecialchars($t['message']) ?>"</p>
          <div class="d-flex align-items-center gap-2">
            <div class="bg-gold rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:38px;height:38px;flex-shrink:0;font-size:0.9rem;background:var(--gold) !important;">
              <?= strtoupper(substr($t['name'],0,1)) ?>
            </div>
            <div>
              <div class="fw-600 small" style="color:var(--dark)"><?= htmlspecialchars($t['name']) ?></div>
              <?php if ($t['role']): ?>
              <div class="text-muted" style="font-size:0.75rem"><?= htmlspecialchars($t['role']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ======================== CTA ======================== -->
<section class="cta-section section-py">
  <div class="container position-relative" style="z-index:1">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="section-label text-gold">Limited Units</div>
        <h2 class="section-title text-white">Ready to Find Your Perfect Apartment?</h2>
        <p style="color:rgba(255,255,255,0.7)">Our sales team is available 6 days a week. Book a free consultation and site visit today — units are selling fast.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-lg-end">
          <a href="contact.php" class="btn btn-gold btn-lg">
            <i class="bi bi-calendar3 me-2"></i>Book a Site Visit
          </a>
          <a href="tel:<?= preg_replace('/\s+/', '', $settings['phone_1'] ?? '') ?>" class="btn btn-outline-light btn-lg">
            <i class="bi bi-telephone me-2"></i>Call Now
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WhatsApp Float -->
<a href="https://wa.me/251911234567?text=Hi, I'm interested in apartments at Getas Reality" target="_blank" class="wa-float" title="Chat on WhatsApp">
  <i class="bi bi-whatsapp"></i>
</a>

<?php require_once 'includes/footer.php'; ?>
