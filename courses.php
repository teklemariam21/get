<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Courses';
$pageDesc = 'Professional tech repair courses: Mobile Phone, Computer, Office Machine & CCTV Camera training in Addis Ababa.';

$courses = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY sort_order")->fetchAll();
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="page-hero">
    <div class="container" data-aos="fade-up">
        <p class="text-uppercase" style="color:var(--cyan);font-size:0.8rem;letter-spacing:2px;margin-bottom:0.5rem">Professional Training</p>
        <h1>Our <span class="neon-text">Courses</span></h1>
        <p>Hands-on technical training programs designed to get you job-ready</p>
    </div>
</div>

<section>
    <div class="container">
        <?php foreach ($courses as $i => $course):
            $color = htmlspecialchars($course['color'] ?? '#00d4ff');
            $icon = htmlspecialchars($course['icon'] ?? 'fas fa-graduation-cap');
            $isEven = $i % 2 === 0;
        ?>
        <div class="row gy-5 align-items-center mb-5 <?= $isEven ? '' : 'flex-row-reverse' ?>" id="<?= htmlspecialchars($course['slug']) ?>" data-aos="fade-up">
            <div class="col-lg-5">
                <div class="glass-card p-5 text-center" style="border-color:<?= $color ?>33;background:<?= $color ?>08">
                    <div style="font-size:6rem;color:<?= $color ?>;filter:drop-shadow(0 0 20px <?= $color ?>88);margin-bottom:1rem">
                        <i class="<?= $icon ?>"></i>
                    </div>
                    <div style="font-family:'Orbitron',sans-serif;font-size:1.4rem;color:<?= $color ?>;margin-bottom:0.5rem"><?= number_format($course['price']) ?> ETB</div>
                    <div style="color:var(--text-muted);font-size:0.85rem">Full Course Fee</div>
                    <div class="cyber-line"></div>
                    <div class="d-flex justify-content-around">
                        <div class="text-center">
                            <div style="font-family:'Orbitron',sans-serif;color:#fff;font-size:1.1rem"><?= htmlspecialchars($course['duration']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">Duration</div>
                        </div>
                        <div style="width:1px;background:var(--glass-border)"></div>
                        <div class="text-center">
                            <div style="font-family:'Orbitron',sans-serif;color:#fff;font-size:1.1rem;text-transform:capitalize"><?= htmlspecialchars($course['level']) ?></div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">Level</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="section-divider left" style="background:linear-gradient(90deg,<?= $color ?>,transparent)"></div>
                <h2 style="font-size:1.8rem;margin-bottom:1rem"><?= htmlspecialchars($course['title']) ?></h2>
                <p style="color:var(--text-muted);margin-bottom:1.5rem;line-height:1.8"><?= htmlspecialchars($course['description']) ?></p>
                <?php if ($course['content']): ?>
                <div class="glass-card p-3 mb-3" style="border-color:<?= $color ?>33">
                    <h6 style="color:<?= $color ?>;font-family:'Orbitron',sans-serif;font-size:0.8rem;margin-bottom:1rem">WHAT YOU'LL LEARN</h6>
                    <div style="color:var(--text-muted);font-size:0.88rem"><?= nl2br(htmlspecialchars($course['content'])) ?></div>
                </div>
                <?php endif; ?>
                <div class="d-flex gap-3 flex-wrap mt-3">
                    <a href="<?= SITE_URL ?>/contact.php?course=<?= urlencode($course['title']) ?>" class="btn-hero-primary" style="background:linear-gradient(135deg,<?= $color ?>,<?= $color ?>99)">
                        <i class="fas fa-paper-plane"></i> Enroll Now
                    </a>
                    <a href="tel:0968752100" class="btn-hero-secondary">
                        <i class="fas fa-phone-alt"></i> Call for Info
                    </a>
                </div>
            </div>
        </div>
        <?php if ($i < count($courses)-1): ?>
        <div class="cyber-line"></div>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" data-aos="fade-up">
    <div class="container">
        <h2>Visit Us at <span class="neon-text">Zefmesh Grand Mall</span></h2>
        <p>Megenagna, Addis Ababa · Mobile Zone · Tel: 0968752100</p>
        <a href="tel:0968752100" class="btn-hero-primary"><i class="fas fa-phone-alt"></i> Call Now</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
