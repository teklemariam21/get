<footer class="footer mt-auto">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="logo-icon">
                            <i class="bi bi-heart-fill"></i>
                        </div>
                        <span class="fs-5 fw-700 text-white">Habesha<span class="text-warning">Connect</span></span>
                    </div>
                    <p class="text-white-50 mb-3">Ethiopia's premier dating and matchmaking platform. Connecting hearts across the nation and the diaspora.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-link"><i class="bi bi-telegram"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-600 mb-3">Quick Links</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= SITE_URL ?>/">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/browse.php">Browse</a></li>
                        <li><a href="<?= SITE_URL ?>/search.php">Search</a></li>
                        <li><a href="<?= SITE_URL ?>/subscription.php">Premium</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="text-white fw-600 mb-3">Support</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Safety Tips</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Report Abuse</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="text-white fw-600 mb-3">Contact</h6>
                    <ul class="list-unstyled footer-links">
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-envelope text-warning"></i>
                            <span>admin@habeshaconnect.com</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-telephone text-warning"></i>
                            <span>+251 911 000 000</span>
                        </li>
                        <li class="d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-geo-alt text-warning"></i>
                            <span>Addis Ababa, Ethiopia</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span class="text-white-50 small">&copy; <?= date('Y') ?> HabeshaConnect. All rights reserved.</span>
            <div class="d-flex gap-3">
                <a href="#" class="text-white-50 small text-decoration-none">Privacy Policy</a>
                <a href="#" class="text-white-50 small text-decoration-none">Terms of Service</a>
                <a href="#" class="text-white-50 small text-decoration-none">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main JS -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
