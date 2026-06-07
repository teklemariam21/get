<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Contact Us';
$pageDesc = 'Contact Hitechcomputer. Visit us at Zefmesh Grand Mall, Megenagna, Addis Ababa or call 0968752100.';
$preselectedCourse = isset($_GET['course']) ? sanitize($_GET['course']) : '';
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="page-hero">
    <div class="container" data-aos="fade-up">
        <p class="text-uppercase" style="color:var(--cyan);font-size:0.8rem;letter-spacing:2px;margin-bottom:0.5rem">Get In Touch</p>
        <h1>Contact <span class="neon-text">Us</span></h1>
        <p>We're here to help. Reach us by phone, visit our shop, or send a message.</p>
    </div>
</div>

<section class="contact-section">
    <div class="container">
        <div class="row gy-5">
            <!-- Contact Info -->
            <div class="col-lg-4" data-aos="fade-right">
                <h3 style="font-size:1.3rem;margin-bottom:2rem">Reach <span class="neon-text">Us</span></h3>

                <div class="contact-info-item">
                    <div class="contact-info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-info-text">
                        <h6>Our Location</h6>
                        <p>Zefmesh Grand Mall, Mobile Zone<br>Megenagna, Addis Ababa</p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon" style="background:rgba(0,255,136,0.1);border-color:rgba(0,255,136,0.2);color:var(--green)"><i class="fas fa-phone-alt"></i></div>
                    <div class="contact-info-text">
                        <h6>Phone Number</h6>
                        <p><a href="tel:0968752100">0968752100</a></p>
                    </div>
                </div>

                <div class="contact-info-item">
                    <div class="contact-info-icon" style="background:rgba(123,47,255,0.1);border-color:rgba(123,47,255,0.2);color:#b47fff"><i class="fas fa-clock"></i></div>
                    <div class="contact-info-text">
                        <h6>Working Hours</h6>
                        <p>Mon – Saturday<br>8:00 AM – 7:00 PM</p>
                    </div>
                </div>

                <!-- Social / WhatsApp -->
                <div class="glass-card p-3 mt-3">
                    <h6 style="font-family:'Orbitron',sans-serif;font-size:0.8rem;color:var(--cyan);margin-bottom:0.8rem">CONNECT WITH US</h6>
                    <div class="social-links">
                        <a href="#" class="social-btn"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-telegram-plane"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-btn"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8" data-aos="fade-left">
                <div class="glass-card p-4 p-lg-5">
                    <h3 style="font-size:1.3rem;margin-bottom:0.5rem">Send Us a <span class="neon-text">Message</span></h3>
                    <p style="color:var(--text-muted);font-size:0.88rem;margin-bottom:2rem">Fill the form below and we'll get back to you as soon as possible.</p>

                    <form id="contact-form" novalidate>
                        <?php if ($preselectedCourse): ?>
                        <input type="hidden" name="subject" value="Course Inquiry: <?= htmlspecialchars($preselectedCourse) ?>">
                        <div class="alert-glass success mb-3">
                            <i class="fas fa-graduation-cap"></i>
                            Enrolling for: <strong><?= htmlspecialchars($preselectedCourse) ?></strong>
                        </div>
                        <?php endif; ?>

                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" placeholder="Your full name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" name="phone" placeholder="09XXXXXXXX" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" placeholder="your@email.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select class="form-select" name="subject">
                                    <option value="General Inquiry" <?= !$preselectedCourse ? 'selected' : '' ?>>General Inquiry</option>
                                    <option value="Course Enrollment">Course Enrollment</option>
                                    <option value="Phone Repair">Phone Repair</option>
                                    <option value="Laptop Repair">Laptop Repair</option>
                                    <option value="CCTV Installation">CCTV Installation</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Your Message *</label>
                                <textarea class="form-control" name="message" rows="5" placeholder="Tell us how we can help you..." required></textarea>
                            </div>
                            <div class="col-12 mt-2">
                                <button type="submit" class="btn-hero-primary w-100 justify-content-center">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Map placeholder -->
        <div class="row mt-5" data-aos="fade-up">
            <div class="col-12">
                <div class="glass-card p-0" style="overflow:hidden;border-radius:var(--radius)">
                    <div style="background:linear-gradient(135deg,rgba(0,212,255,0.05),rgba(123,47,255,0.05));padding:1rem;border-bottom:1px solid var(--glass-border)">
                        <h6 style="font-family:'Orbitron',sans-serif;font-size:0.85rem;color:var(--cyan);margin:0">
                            <i class="fas fa-map-marker-alt me-2"></i>Find Us: Zefmesh Grand Mall, Mobile Zone, Megenagna, Addis Ababa
                        </h6>
                    </div>
                    <div class="map-placeholder" style="height:350px">
                        <i class="fas fa-map-marked-alt" style="font-size:4rem;color:var(--cyan);opacity:0.4"></i>
                        <div style="text-align:center">
                            <div style="font-size:1rem;color:#fff;margin-bottom:0.5rem">Zefmesh Grand Mall · Mobile Zone</div>
                            <div style="font-size:0.85rem;color:var(--text-muted)">Megenagna, Addis Ababa, Ethiopia</div>
                            <a href="https://maps.google.com/?q=Megenagna+Addis+Ababa" target="_blank" class="btn-outline-glow mt-3" style="font-size:0.82rem;padding:6px 14px">
                                <i class="fas fa-external-link-alt me-1"></i> Open in Maps
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
