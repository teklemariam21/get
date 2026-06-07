
<!-- Footer -->
<footer class="footer mt-auto">
  <div class="footer-top">
    <div class="container">
      <div class="row g-4">
        <!-- Brand -->
        <div class="col-lg-4 col-md-6">
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="logo-icon"><i class="bi bi-buildings-fill"></i></div>
            <div>
              <span class="brand-name text-white">Getas</span>
              <span class="brand-accent"> Reality</span>
            </div>
          </div>
          <p class="text-gray-400 small mb-3">
            <?= htmlspecialchars($settings['about_short'] ?? 'Your trusted partner for premium apartments in Addis Ababa.') ?>
          </p>
          <div class="d-flex gap-2">
            <?php if (!empty($settings['facebook'])): ?>
            <a href="<?= $settings['facebook'] ?>" target="_blank" class="footer-social"><i class="bi bi-facebook"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['instagram'])): ?>
            <a href="<?= $settings['instagram'] ?>" target="_blank" class="footer-social"><i class="bi bi-instagram"></i></a>
            <?php endif; ?>
            <?php if (!empty($settings['telegram'])): ?>
            <a href="<?= $settings['telegram'] ?>" target="_blank" class="footer-social"><i class="bi bi-telegram"></i></a>
            <?php endif; ?>
            <a href="https://wa.me/251911234567" target="_blank" class="footer-social"><i class="bi bi-whatsapp"></i></a>
          </div>
        </div>

        <!-- Summit 72 -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading"><i class="bi bi-buildings me-2"></i>Summit 72</h6>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/properties.php?site=Summit+72&type=1BR"><i class="bi bi-chevron-right"></i>1 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?site=Summit+72&type=2BR"><i class="bi bi-chevron-right"></i>2 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?site=Summit+72&type=3BR"><i class="bi bi-chevron-right"></i>3 Bedroom</a></li>
          </ul>
        </div>

        <!-- Kazanchis -->
        <div class="col-lg-2 col-md-6 col-6">
          <h6 class="footer-heading"><i class="bi bi-geo-alt me-2"></i>Kazanchis</h6>
          <ul class="footer-links">
            <li><a href="<?= SITE_URL ?>/properties.php?site=Kazanchis&type=1BR"><i class="bi bi-chevron-right"></i>1 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?site=Kazanchis&type=2BR"><i class="bi bi-chevron-right"></i>2 Bedroom</a></li>
            <li><a href="<?= SITE_URL ?>/properties.php?site=Kazanchis&type=3BR"><i class="bi bi-chevron-right"></i>3 Bedroom</a></li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="col-lg-4 col-md-6">
          <h6 class="footer-heading">Contact Us</h6>
          <ul class="footer-contact">
            <li>
              <i class="bi bi-telephone-fill"></i>
              <div>
                <a href="tel:<?= preg_replace('/\s+/', '', $settings['phone_1'] ?? '') ?>"><?= htmlspecialchars($settings['phone_1'] ?? '+251 911 234 567') ?></a>
                <?php if (!empty($settings['phone_2'])): ?>
                <br><a href="tel:<?= preg_replace('/\s+/', '', $settings['phone_2']) ?>"><?= htmlspecialchars($settings['phone_2']) ?></a>
                <?php endif; ?>
              </div>
            </li>
            <li>
              <i class="bi bi-envelope-fill"></i>
              <a href="mailto:<?= htmlspecialchars($settings['email'] ?? '') ?>"><?= htmlspecialchars($settings['email'] ?? 'info@getasreality.com') ?></a>
            </li>
            <li>
              <i class="bi bi-geo-alt-fill"></i>
              <span><?= htmlspecialchars($settings['address'] ?? 'Addis Ababa, Ethiopia') ?></span>
            </li>
            <li>
              <i class="bi bi-clock-fill"></i>
              <span><?= htmlspecialchars($settings['working_hours'] ?? 'Mon–Sat: 8:00 AM – 6:00 PM') ?></span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <small class="text-gray-400">© <?= date('Y') ?> <?= htmlspecialchars($siteName) ?>. All rights reserved.</small>
        <div class="d-flex gap-3">
          <a href="<?= SITE_URL ?>/contact.php" class="small text-gray-400 hover-gold">Contact</a>
          <a href="<?= SITE_URL ?>/admin/index.php" class="small text-gray-400 hover-gold">Admin</a>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
