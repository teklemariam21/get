<?php
$pageTitle = 'City Gate – Premium Apartments | Getas Real Estate';
require_once 'includes/header.php';

$featured  = $db->query("SELECT * FROM properties WHERE featured=1 AND status='available' ORDER BY tower,unit_type LIMIT 6")->fetchAll();
$totalAvail = $db->query("SELECT COUNT(*) FROM properties WHERE status='available'")->fetchColumn();
$cg1cnt    = $db->query("SELECT COUNT(*) FROM properties WHERE tower='City Gate 1'")->fetchColumn();
$cg2cnt    = $db->query("SELECT COUNT(*) FROM properties WHERE tower='City Gate 2'")->fetchColumn();
$cg3cnt    = $db->query("SELECT COUNT(*) FROM properties WHERE tower='City Gate 3'")->fetchColumn();
$testimonials = $db->query("SELECT * FROM testimonials WHERE approved=1 ORDER BY id DESC LIMIT 4")->fetchAll();
$payPlans  = $db->query("SELECT DISTINCT plan_name, completion_pct, down_payment_pct FROM payment_plans GROUP BY plan_name ORDER BY completion_pct DESC, down_payment_pct ASC")->fetchAll();

// Tower hero images (actual building renders)
$towerImg = [
  'City Gate 1' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=900',
  'City Gate 2' => 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=900',
  'City Gate 3' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=900',
];
?>

<!-- ======================== HERO ======================== -->
<section class="hero">
  <div class="container position-relative" style="z-index:2">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="hero-badge">
          <i class="bi bi-stars"></i> Now Selling — Limited Units Available
        </div>
        <h1>
          <span>City Gate</span><br>
          Where Addis Ababa's Skyline Begins
        </h1>
        <p class="mb-0">Three iconic towers. 25 floors. Premium 2 and 3-bedroom apartments crafted to international standards by <strong style="color:var(--gold)">Getas Real Estate</strong> — right in the heart of Addis Ababa.</p>

        <!-- Stats -->
        <div class="hero-stats row g-3 mt-3">
          <div class="col-4">
            <div class="stat-item">
              <div class="stat-number" data-count="3">0</div>
              <div class="stat-label">Towers</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <div class="stat-number">25</div>
              <div class="stat-label">Floors</div>
            </div>
          </div>
          <div class="col-4">
            <div class="stat-item">
              <div class="stat-number">10%</div>
              <div class="stat-label">Down Payment</div>
            </div>
          </div>
        </div>

        <div class="d-flex gap-3 mt-4 flex-wrap">
          <a href="properties.php" class="btn btn-gold btn-lg px-4">
            <i class="bi bi-buildings me-2"></i>View All Apartments
          </a>
          <a href="contact.php" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-calendar3 me-2"></i>Book Site Visit
          </a>
        </div>
      </div>

      <!-- Search Card -->
      <div class="col-lg-6">
        <div class="hero-search">
          <h5 class="fw-700 mb-3" style="font-family:'Playfair Display',serif;color:var(--dark)">
            <i class="bi bi-search me-2 text-gold"></i>Find Your Apartment
          </h5>
          <form action="properties.php" method="GET">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Tower</label>
                <select name="tower" class="form-select">
                  <option value="">All Towers</option>
                  <option value="City Gate 1">City Gate 1</option>
                  <option value="City Gate 2">City Gate 2</option>
                  <option value="City Gate 3">City Gate 3</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Bedrooms</label>
                <select name="bedrooms" class="form-select">
                  <option value="">Any</option>
                  <option value="2">2 Bedrooms</option>
                  <option value="3">3 Bedrooms</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Min Area (m²)</label>
                <select name="min_area" class="form-select">
                  <option value="">Any Size</option>
                  <option value="115">115m²+</option>
                  <option value="132">132m²+</option>
                  <option value="144">144m²+</option>
                  <option value="149">149m²+</option>
                  <option value="159">159m²+</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Max Budget (ETB)</label>
                <select name="max_price" class="form-select">
                  <option value="">Any Price</option>
                  <option value="12000000">Up to 12M</option>
                  <option value="14000000">Up to 14M</option>
                  <option value="16000000">Up to 16M</option>
                  <option value="18000000">Up to 18M</option>
                </select>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-gold w-100 py-2 fw-600">
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

<!-- ======================== TOWERS ======================== -->
<section class="section-py-sm">
  <div class="container">
    <div class="text-center mb-4">
      <div class="section-label">The Development</div>
      <h2 class="section-title">Three Towers — One Landmark</h2>
      <p class="section-desc mx-auto">City Gate 1, 2 and 3 rise 25 floors above Addis Ababa, each offering 6 apartment types across 2 and 3 bedroom configurations.</p>
    </div>
    <div class="row g-4">
      <?php
      $towers = [
        ['City Gate 1', $cg1cnt, 'Wing 1 — 2 & 3 Bedroom apartments from 115m² to 144m²', 'cg1', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=900'],
        ['City Gate 2', $cg2cnt, 'Wing 2 — 2 & 3 Bedroom apartments from 115m² to 144m²', 'cg2', 'https://images.unsplash.com/photo-1486325212027-8081e485255e?w=900'],
        ['City Gate 3', $cg3cnt, 'Wing 3 — 2 & 3 Bedroom apartments from 132m² to 159m²', 'cg3', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=900'],
      ];
      foreach ($towers as $tw): ?>
      <div class="col-md-4">
        <a href="properties.php?tower=<?= urlencode($tw[0]) ?>" class="d-block text-decoration-none">
          <div class="site-card">
            <img src="<?= $tw[4] ?>" alt="<?= $tw[0] ?>">
            <div class="site-card-overlay">
              <div class="d-flex justify-content-between align-items-end">
                <div>
                  <h3 class="fw-bold mb-1"><?= $tw[0] ?></h3>
                  <p class="mb-1"><?= $tw[1] ?> Apartment Types</p>
                  <p class="mb-0" style="font-size:0.78rem;opacity:0.7"><?= $tw[2] ?></p>
                </div>
              </div>
            </div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ======================== APARTMENT TYPES TABLE ======================== -->
<section class="section-py bg-light-gray">
  <div class="container">
    <div class="row align-items-center mb-4">
      <div class="col-md-7">
        <div class="section-label">Apartment Plans</div>
        <h2 class="section-title">All Unit Types at a Glance</h2>
        <p class="section-desc">Every apartment includes 2 balconies, maid's room, laundry space, and full building amenities. 10% down payment to secure your unit.</p>
      </div>
      <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="properties.php" class="btn btn-outline-gold">View All Units <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>

    <!-- Nav tabs for towers -->
    <ul class="nav nav-pills mb-3 gap-2" id="towerTab" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-cg1" style="background:transparent;color:var(--dark);border:1.5px solid #e5e7eb;border-radius:8px;font-weight:600;padding:0.5rem 1.25rem">City Gate 1</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-cg2" style="background:transparent;color:var(--dark);border:1.5px solid #e5e7eb;border-radius:8px;font-weight:600;padding:0.5rem 1.25rem">City Gate 2</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-cg3" style="background:transparent;color:var(--dark);border:1.5px solid #e5e7eb;border-radius:8px;font-weight:600;padding:0.5rem 1.25rem">City Gate 3</button>
      </li>
    </ul>

    <div class="tab-content">
      <?php
      $towerData = [
        'cg1' => ['City Gate 1', [
          ['Type 1','2BR',115,99378,11428470,1142847],
          ['Type 2','2BR',116,107476,12467216,1246722],
          ['Type 3','2BR',116,119000,13804000,1380400],
          ['Type 4','3BR',137,119000,16303000,1630300],
          ['Type 5','3BR',144,99378,14310432,1431043],
          ['Type 6','2BR',117,99378,11627226,1162723],
        ]],
        'cg2' => ['City Gate 2', [
          ['Type 1','3BR',144,99378,14310432,1431043],
          ['Type 2','3BR',137,119000,16303000,1630300],
          ['Type 3','2BR',116,119000,13804000,1380400],
          ['Type 4','2BR',116,119000,13804000,1380400],
          ['Type 5','2BR',115,119000,13685000,1368500],
          ['Type 6','2BR',117,107476,12574692,1257469],
        ]],
        'cg3' => ['City Gate 3', [
          ['Type 1','2BR',149,107476,16013924,1601392],
          ['Type 2','3BR',159,99378,15801102,1580110],
          ['Type 3','2BR',132,99378,13117896,1311790],
          ['Type 4','2BR',132,99378,13117896,1311790],
          ['Type 5','3BR',159,107476,17088684,1708868],
          ['Type 6','2BR',149,107476,16013924,1601392],
        ]],
      ];
      $first = true;
      foreach ($towerData as $key => [$tName, $types]): ?>
      <div class="tab-pane fade <?= $first?'show active':'' ?>" id="tab-<?= $key ?>">
        <div class="table-responsive">
          <table class="table specs-table bg-white">
            <thead>
              <tr>
                <th>Unit Type</th>
                <th>Bedrooms</th>
                <th>Area (m²)</th>
                <th>Price / m²</th>
                <th>Total Price</th>
                <th>10% Down Payment</th>
                <th>Includes</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($types as $t): ?>
              <tr>
                <td><span class="badge bg-dark"><?= $t[0] ?></span></td>
                <td>
                  <?= $t[1]==='2BR' ? '<i class="bi bi-door-closed text-gold me-1"></i>2' : '<i class="bi bi-door-closed text-gold me-1"></i>3' ?>
                  <span class="text-muted small ms-1"><?= $t[1] ?></span>
                </td>
                <td class="fw-600"><?= $t[2] ?> m²</td>
                <td class="text-muted small">ETB <?= number_format($t[3]) ?></td>
                <td class="price-cell fw-700">ETB <?= number_format($t[4]) ?></td>
                <td style="color:#166534;font-weight:700">ETB <?= number_format($t[5]) ?></td>
                <td>
                  <span class="small"><i class="bi bi-check-circle-fill text-gold me-1"></i>2 Balconies</span><br>
                  <span class="small"><i class="bi bi-check-circle-fill text-gold me-1"></i>Maid's Room</span>
                </td>
                <td>
                  <a href="properties.php?tower=<?= urlencode($tName) ?>&unit_type=<?= urlencode($t[0]) ?>" class="btn btn-sm btn-gold">Enquire</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php $first=false; endforeach; ?>
    </div>
  </div>
</section>

<!-- ======================== FEATURED APARTMENTS ======================== -->
<section class="section-py">
  <div class="container">
    <div class="row align-items-center mb-4">
      <div class="col-md-8">
        <div class="section-label">Featured Units</div>
        <h2 class="section-title">Available Apartments</h2>
      </div>
      <div class="col-md-4 text-md-end">
        <a href="properties.php" class="btn btn-outline-gold">All Apartments <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
    <div class="row g-4">
      <?php
      $typeImgs = [
        '2BR' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
        '3BR' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800',
      ];
      foreach ($featured as $p):
        $imgs = json_decode($p['images']??'[]',true);
        $brKey = $p['bedrooms']===2 ? '2BR' : '3BR';
        $img = !empty($imgs[0]) ? $imgs[0] : ($typeImgs[$brKey]??'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800');
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="property-card h-100">
          <div class="card-img-wrap">
            <a href="property.php?slug=<?= urlencode($p['slug']) ?>">
              <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
            </a>
            <span class="badge-site"><i class="bi bi-buildings me-1"></i><?= htmlspecialchars($p['tower']) ?></span>
            <span class="badge-status badge-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span>
            <span style="position:absolute;bottom:12px;left:12px;background:var(--gold);color:#fff;padding:0.2rem 0.65rem;border-radius:50px;font-size:0.68rem;font-weight:700">
              <?= htmlspecialchars($p['unit_type']) ?>
            </span>
          </div>
          <div class="card-body d-flex flex-column">
            <h3 class="property-title">
              <a href="property.php?slug=<?= urlencode($p['slug']) ?>" class="text-dark text-decoration-none">
                <?= htmlspecialchars($p['title']) ?>
              </a>
            </h3>
            <div class="property-price">ETB <?= number_format($p['total_price']) ?></div>
            <div class="property-specs">
              <div class="spec-item"><i class="bi bi-door-closed"></i><?= $p['bedrooms'] ?> Bed</div>
              <div class="spec-item"><i class="bi bi-droplet"></i><?= $p['bathrooms'] ?> Bath</div>
              <div class="spec-item"><i class="bi bi-rulers"></i><?= number_format($p['area']) ?>m²</div>
              <div class="spec-item"><i class="bi bi-snow"></i>2 Balconies</div>
            </div>
            <div class="small text-muted mb-3">
              <i class="bi bi-info-circle text-gold me-1"></i>
              10% Down: <strong>ETB <?= number_format($p['down_payment_10pct']) ?></strong>
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
  </div>
</section>

<!-- ======================== PAYMENT PLANS ======================== -->
<section class="section-py bg-light-gray">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Flexible Payment</div>
      <h2 class="section-title">Payment Plans</h2>
      <p class="section-desc mx-auto">Choose the plan that fits your situation. Flexible down payment options based on construction completion stage.</p>
    </div>

    <!-- Plan cards -->
    <div class="row g-4 mb-5">
      <?php
      $plans = [
        ['10% Down',   'Pre-Completion',  '10',  'Reserve your unit now with just 10% down. Balance payable on completion.', 'bi-clock', '#dbeafe','#1d4ed8'],
        ['25% Down',   '85% Completed',   '25',  '25% down with the building at 85% completion. Shorter wait, faster handover.', 'bi-hourglass-split','#fef3c7','#b45309'],
        ['50% Down',   '100% Completed',  '50',  '50% down on a fully completed and ready-to-move apartment.', 'bi-check-circle','#dcfce7','#15803d'],
        ['65% Down',   '100% Completed',  '65',  '65% down payment — larger initial stake, minimal remaining balance.', 'bi-star-fill','var(--gold-bg)','var(--gold-dark)'],
      ];
      foreach ($plans as $pl): ?>
      <div class="col-md-6 col-lg-3">
        <div class="feature-card text-center h-100">
          <div class="feature-icon mx-auto mb-3" style="background:<?= $pl[5] ?>;color:<?= $pl[6] ?>">
            <i class="bi <?= $pl[4] ?>"></i>
          </div>
          <div class="fw-800 mb-1" style="font-size:1.5rem;color:var(--gold)"><?= $pl[0] ?></div>
          <div class="badge mb-2" style="background:<?= $pl[5] ?>;color:<?= $pl[6] ?>"><?= $pl[1] ?></div>
          <p class="small text-muted mb-0"><?= $pl[2] ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Completion-based pricing table -->
    <div class="row g-4">
      <?php
      $planGroups = [
        ['100% Completed – 50% Down', 100, 50, '#dcfce7', '#166534'],
        ['100% Completed – 65% Down', 100, 65, '#fef3c7', '#92400e'],
        ['85% Completed – 25% Down',   85, 25, '#dbeafe', '#1e40af'],
      ];
      foreach ($planGroups as $pg):
        $rows = $db->prepare("SELECT * FROM payment_plans WHERE plan_name=:n ORDER BY bedrooms,area");
        $rows->execute([':n'=>$pg[0]]);
        $rows = $rows->fetchAll();
      ?>
      <div class="col-lg-4">
        <div class="form-card h-100">
          <div class="d-flex align-items-center gap-2 mb-3">
            <span class="badge rounded-pill px-3 py-2" style="background:<?= $pg[3] ?>;color:<?= $pg[4] ?>;font-size:0.8rem">
              <?= $pg[2] ?>% Down
            </span>
            <span class="fw-700" style="color:var(--dark)"><?= $pg[1] ?>% Complete</span>
          </div>
          <div class="table-responsive">
            <table class="table table-sm small mb-0">
              <thead><tr style="background:var(--dark);color:#fff">
                <th class="fw-600">BR</th>
                <th class="fw-600">Area</th>
                <th class="fw-600">Total</th>
                <th class="fw-600">Down (<?= $pg[2] ?>%)</th>
              </tr></thead>
              <tbody>
              <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= $r['bedrooms'] ?>BR</td>
                <td><?= (int)$r['area'] ?>m²</td>
                <td class="fw-600" style="color:var(--gold)">ETB <?= number_format($r['total_price']) ?></td>
                <td style="color:<?= $pg[4] ?>;font-weight:700">ETB <?= number_format($r['down_payment_amount']) ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <a href="contact.php" class="btn btn-gold btn-lg px-5">
        <i class="bi bi-telephone me-2"></i>Discuss Payment Plans with Our Team
      </a>
    </div>
  </div>
</section>

<!-- ======================== WHY GETAS ======================== -->
<section class="section-py">
  <div class="container">
    <div class="text-center mb-5">
      <div class="section-label">Why City Gate</div>
      <h2 class="section-title">Built for the Way You Live</h2>
    </div>
    <div class="row g-4">
      <?php
      $features = [
        ['bi-buildings-fill','25-Floor Towers','Three soaring towers dominate the Addis Ababa skyline — City Gate is visible from across the city.'],
        ['bi-shield-check','Title Deed Secured','All units come with fully registered Ethiopian title deeds — no legal complexity.'],
        ['bi-door-open','2 Balconies Per Unit','Every apartment features two private balconies, capturing panoramic city views from multiple aspects.'],
        ['bi-person-workspace','Maid\'s Room Included','Every unit comes with a dedicated maid\'s room and store — thoughtfully designed for Ethiopian living.'],
        ['bi-droplet','Premium Finishes','German and Italian fixtures, imported flooring, and bespoke kitchens in every apartment.'],
        ['bi-car-front','Full Amenities','Rooftop terrace, swimming pool, gym, underground parking, 24/7 security, backup generator.'],
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
<section class="section-py bg-light-gray">
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
            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:38px;height:38px;flex-shrink:0;font-size:0.9rem;background:var(--gold)">
              <?= strtoupper(substr($t['name'],0,1)) ?>
            </div>
            <div>
              <div class="fw-600 small" style="color:var(--dark)"><?= htmlspecialchars($t['name']) ?></div>
              <div class="text-muted" style="font-size:0.72rem"><?= htmlspecialchars($t['role']??'') ?></div>
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
        <div class="section-label" style="color:var(--gold)">Limited Units — Act Now</div>
        <h2 class="section-title text-white">Secure Your City Gate Apartment Today</h2>
        <p style="color:rgba(255,255,255,0.7)">Start with just 10% down payment. Units are selling fast — book a free site visit and speak with our sales team today.</p>
      </div>
      <div class="col-lg-5 text-lg-end">
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-lg-end">
          <a href="contact.php" class="btn btn-gold btn-lg">
            <i class="bi bi-calendar3 me-2"></i>Book Site Visit
          </a>
          <a href="tel:+251911234567" class="btn btn-outline-light btn-lg">
            <i class="bi bi-telephone me-2"></i>Call Now
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WhatsApp Float -->
<a href="https://wa.me/251911234567?text=Hi, I'm interested in City Gate apartments" target="_blank" class="wa-float" title="Chat on WhatsApp">
  <i class="bi bi-whatsapp"></i>
</a>

<!-- Nav pills active style fix -->
<style>
.nav-pills .nav-link.active { background:var(--gold) !important; color:#fff !important; border-color:var(--gold) !important; }
.nav-pills .nav-link:hover:not(.active) { background:var(--gold-bg) !important; color:var(--gold) !important; }
</style>

<?php require_once 'includes/footer.php'; ?>
