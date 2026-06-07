
<footer class="footer mt-auto">
  <div class="footer-top">
    <div class="container">
      <div class="row g-4">
        <!-- Brand -->
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="logo-icon"><i class="bi bi-buildings-fill"></i></div>
            <div>
              <span class="brand-name text-white">GETAS</span>
              <span class="brand-accent"> Real Estate</span>
              <div style="font-size:0.6rem;color:rgba(255,255,255,0.4);text-transform:uppercase;letter-spacing:0.1em">City Gate</div>
            </div>
          </div>
          <p class="small mb-3" style="color:#9ca3af"><?= htmlspecialchars($settings['about_short'] ?? '') ?></p>
          <div class="d-flex gap-2">
            <?php foreach (['facebook','instagram','telegram'] as $soc): if (!empty($settings[$soc])): ?>
            <a href="<?= $settings[$soc] ?>" target="_blank" class="footer-social"><i class="bi bi-<?= $soc ?>"></i></a>
            <?php endif; endforeach; ?>
            <a href="https://wa.me/251911234567" target="_blank" class="footer-social"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>

        <!-- City Gate 1 & 2 -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading">City Gate 1</h6>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/properties.php?tower=City+Gate+1&bedrooms=2"><i class="bi bi-chevron-right"></i>2 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?tower=City+Gate+1&bedrooms=3"><i class="bi bi-chevron-right"></i>3 Bedroom</a></li>
          </ul>
          <h6 class="footer-heading mt-3">City Gate 2</h6>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/properties.php?tower=City+Gate+2&bedrooms=2"><i class="bi bi-chevron-right"></i>2 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?tower=City+Gate+2&bedrooms=3"><i class="bi bi-chevron-right"></i>3 Bedroom</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading">City Gate 3</h6>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/properties.php?tower=City+Gate+3&bedrooms=2"><i class="bi bi-chevron-right"></i>2 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?tower=City+Gate+3&bedrooms=3"><i class="bi bi-chevron-right"></i>3 Bedroom</a></li>
          </ul>
          <h6 class="footer-heading mt-3">Quick Links</h6>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/about.php"><i class="bi bi-chevron-right"></i>About Us</a></li>
            <li><a href="<?= SITE_URL ?>/contact.php"><i class="bi bi-chevron-right"></i>Contact</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="col-lg-4 col-md-6">
          <h6 class="footer-heading">Contact Us</h6>
          <ul class="footer-contact">
            <li>
              <i class="bi bi-telephone-fill"></i>
              <div>
                <a href="tel:<?= preg_replace('/\s+/','',$settings['phone_1']??'') ?>"><?= htmlspecialchars($settings['phone_1']??'') ?></a>
                <?php if (!empty($settings['phone_2'])): ?><br><a href="tel:<?= preg_replace('/\s+/','',$settings['phone_2']) ?>"><?= htmlspecialchars($settings['phone_2']) ?></a><?php endif; ?>
              </div>
            </li>
            <li><i class="bi bi-envelope-fill"></i><a href="mailto:<?= htmlspecialchars($settings['email']??'') ?>"><?= htmlspecialchars($settings['email']??'') ?></a></li>
            <li><i class="bi bi-geo-alt-fill"></i><span><?= htmlspecialchars($settings['address']??'Addis Ababa, Ethiopia') ?></span></li>
            <li><i class="bi bi-clock-fill"></i><span><?= htmlspecialchars($settings['working_hours']??'Mon–Sat: 8AM–6PM') ?></span></li>
          </ul>
          <div class="mt-3 p-3 rounded-xl" style="background:rgba(201,168,76,0.08);border:1px solid rgba(201,168,76,0.2)">
            <div class="small fw-700 text-gold mb-1">10% Down Payment</div>
            <div class="small" style="color:rgba(255,255,255,0.6)">Reserve your City Gate apartment today. Limited units available across all three towers.</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <small style="color:#6b7280">© <?= date('Y') ?> Getas Real Estate. All rights reserved. City Gate — Addis Ababa.</small>
        <div class="d-flex gap-3">
          <a href="<?= SITE_URL ?>/contact.php" class="small hover-gold" style="color:#6b7280">Contact</a>
          <a href="<?= SITE_URL ?>/login.php"   class="small hover-gold" style="color:#6b7280">Admin</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
