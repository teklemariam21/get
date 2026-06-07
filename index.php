<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Home';
$pageDesc = 'Hitechcomputer - Professional tech training & repair services. Mobile phone, computer, CCTV & office machine courses in Addis Ababa.';

// Fetch data
$featuredCourses = $pdo->query("SELECT * FROM courses WHERE status='active' ORDER BY sort_order LIMIT 4")->fetchAll();
$latestPosts = $pdo->query("SELECT * FROM posts WHERE status='published' ORDER BY created_at DESC LIMIT 3")->fetchAll();
$stats = [
    ['count' => 500, 'label' => 'Graduates', 'suffix' => '+', 'icon' => 'fa-user-graduate'],
    ['count' => 4, 'label' => 'Courses', 'suffix' => '', 'icon' => 'fa-graduation-cap'],
    ['count' => 8, 'label' => 'Years Experience', 'suffix' => '+', 'icon' => 'fa-star'],
    ['count' => 1000, 'label' => 'Devices Repaired', 'suffix' => '+', 'icon' => 'fa-tools'],
];
?>
<?php include __DIR__ . '/includes/header.php'; ?>
<script>var SITE_URL = '<?= SITE_URL ?>';</script>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- ══ HERO ══ -->
<section class="hero-section">
    <div id="particles-js"></div>
    <div class="hero-grid-bg"></div>
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-6 hero-content" data-aos="fade-right">
                <div class="hero-badge">
                    <span class="hero-badge-dot"></span>
                    Addis Ababa's Premier Tech Hub
                </div>
                <h1 class="hero-title">
                    Master Tech<br>
                    <span class="line-gradient">Skills for the<br>Digital Future</span>
                </h1>
                <p class="hero-desc">
                    Learn to repair <strong id="typewriter" style="color:var(--cyan)">Mobile Phones</strong>
                    <span style="border-right:2px solid var(--cyan);animation:pulse 0.8s infinite">&nbsp;</span>
                    <br>Professional courses &amp; repair services at Zefmesh Grand Mall, Megenagna.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= SITE_URL ?>/courses.php" class="btn-hero-primary">
                        <i class="fas fa-graduation-cap"></i> Explore Courses
                    </a>
                    <a href="tel:0968752100" class="btn-hero-secondary">
                        <i class="fas fa-phone-alt"></i> 0968752100
                    </a>
                </div>
                <div class="hero-stats">
                    <?php foreach ($stats as $s): ?>
                    <div class="hero-stat-item">
                        <span class="hero-stat-num" data-count="<?= $s['count'] ?>" data-suffix="<?= $s['suffix'] ?>">0<?= $s['suffix'] ?></span>
                        <span class="hero-stat-label"><?= $s['label'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6 hero-visual" data-aos="fade-left">
                <div class="hero-card-main">
                    <div class="hero-card-header">
                        <span class="hero-card-dot" style="background:#ff5f57"></span>
                        <span class="hero-card-dot" style="background:#ffbd2e"></span>
                        <span class="hero-card-dot" style="background:#28ca41"></span>
                        <span style="margin-left:8px;font-size:0.8rem;color:var(--text-muted);font-family:'Orbitron',sans-serif">Our Courses</span>
                    </div>
                    <?php
                    $heroIcons = [
                        ['icon' => 'fas fa-mobile-alt', 'color' => '#00d4ff', 'name' => 'Mobile Phone Repair', 'dur' => '3 Months'],
                        ['icon' => 'fas fa-laptop', 'color' => '#7b2fff', 'name' => 'Computer Repair', 'dur' => '3 Months'],
                        ['icon' => 'fas fa-print', 'color' => '#00ff88', 'name' => 'Office Machine Repair', 'dur' => '2 Months'],
                        ['icon' => 'fas fa-video', 'color' => '#ff6b35', 'name' => 'CCTV Installation', 'dur' => '2 Months'],
                    ];
                    foreach ($heroIcons as $item): ?>
                    <div class="hero-course-item">
                        <div class="hero-course-icon" style="background:<?= $item['color'] ?>22;color:<?= $item['color'] ?>">
                            <i class="<?= $item['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="hero-course-name"><?= $item['name'] ?></div>
                            <div class="hero-course-dur"><i class="far fa-clock me-1"></i><?= $item['dur'] ?></div>
                        </div>
                        <i class="fas fa-chevron-right ms-auto" style="color:var(--text-muted);font-size:0.7rem"></i>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ STATS ══ -->
<div class="stats-section">
    <div class="container">
        <div class="row gy-4">
            <?php foreach ($stats as $s): ?>
            <div class="col-6 col-md-3">
                <div class="stat-item" data-aos="fade-up">
                    <span class="stat-number" data-count="<?= $s['count'] ?>" data-suffix="<?= $s['suffix'] ?>">0</span>
                    <div class="stat-label"><?= $s['label'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ══ COURSES ══ -->
<section id="courses">
    <div class="container">
        <div class="text-center mb-2" data-aos="fade-up">
            <div class="section-divider"></div>
            <p class="section-subtitle text-uppercase" style="color:var(--cyan);font-size:0.8rem;letter-spacing:2px">What We Teach</p>
            <h2 class="section-title">Our <span>Professional Courses</span></h2>
            <p class="section-subtitle">Hands-on technical training certified by industry professionals</p>
        </div>
        <div class="row gy-4">
            <?php
            $cardColors = ['#00d4ff', '#7b2fff', '#00ff88', '#ff6b35'];
            $cardIcons = ['fas fa-mobile-alt', 'fas fa-laptop', 'fas fa-print', 'fas fa-video'];
            foreach ($featuredCourses as $i => $course):
                $color = $course['color'] ?? $cardColors[$i % 4];
                $icon = $course['icon'] ?? $cardIcons[$i % 4];
            ?>
            <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="glass-card course-card h-100">
                    <div class="course-icon-wrap" style="background:<?= htmlspecialchars($color) ?>20;color:<?= htmlspecialchars($color) ?>">
                        <i class="<?= htmlspecialchars($icon) ?>"></i>
                    </div>
                    <h4><?= htmlspecialchars($course['title']) ?></h4>
                    <p><?= htmlspecialchars(substr($course['description'], 0, 120)) ?>...</p>
                    <div class="course-meta">
                        <span class="course-tag"><i class="far fa-clock me-1"></i><?= htmlspecialchars($course['duration']) ?></span>
                        <span class="course-tag text-capitalize"><?= htmlspecialchars($course['level']) ?></span>
                    </div>
                    <div class="course-price">
                        <?= number_format($course['price']) ?> ETB
                        <small>/ Full Course</small>
                    </div>
                    <a href="<?= SITE_URL ?>/courses.php#<?= htmlspecialchars($course['slug']) ?>" class="btn-outline-glow d-inline-block mt-3" style="border-color:<?= htmlspecialchars($color) ?>;color:<?= htmlspecialchars($color) ?> !important">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/courses.php" class="btn-glow">View All Courses <i class="fas fa-graduation-cap ms-2"></i></a>
        </div>
    </div>
</section>

<!-- ══ SERVICES ══ -->
<section class="services-section">
    <div class="container">
        <div class="row gy-5 align-items-center">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="section-divider left"></div>
                <p class="text-uppercase" style="color:var(--cyan);font-size:0.8rem;letter-spacing:2px">Our Shop</p>
                <h2 class="section-title">Repair &amp; <span>Sales Services</span></h2>
                <p style="color:var(--text-muted);margin-bottom:1.5rem">
                    Visit our shop at <strong style="color:#fff">Zefmesh Grand Mall, Mobile Zone, Megenagna</strong> for fast and professional repair services.
                </p>
                <div class="glass-card p-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-map-marker-alt fa-lg" style="color:var(--cyan)"></i>
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">Location</div>
                            <div style="font-size:0.9rem;font-weight:500">Zefmesh Grand Mall, Mobile Zone<br>Megenagna, Addis Ababa</div>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-phone-alt fa-lg" style="color:var(--green)"></i>
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">Phone</div>
                            <a href="tel:0968752100" style="font-size:1.1rem;font-weight:600;color:#fff;text-decoration:none">0968752100</a>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-3">
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-clock fa-lg" style="color:var(--orange)"></i>
                        <div>
                            <div style="font-size:0.75rem;color:var(--text-muted)">Working Hours</div>
                            <div style="font-size:0.9rem;font-weight:500">Mon – Sat: 8:00 AM – 7:00 PM</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <div class="row gy-3">
                    <?php
                    $services = [
                        ['icon' => 'fas fa-mobile-alt', 'title' => 'Mobile Phone Repair', 'desc' => 'Screen replacement, motherboard repair, charging port, water damage recovery and more.'],
                        ['icon' => 'fas fa-laptop', 'title' => 'Laptop Repair', 'desc' => 'Hardware diagnosis, screen repair, keyboard replacement, OS reinstallation.'],
                        ['icon' => 'fas fa-mobile', 'title' => 'Phone Sales', 'desc' => 'Wide selection of new and quality-checked mobile phones at competitive prices.'],
                        ['icon' => 'fas fa-laptop-code', 'title' => 'Laptop Sales', 'desc' => 'New and refurbished laptops with warranty for students and businesses.'],
                        ['icon' => 'fas fa-sim-card', 'title' => 'Accessories', 'desc' => 'Phone cases, chargers, screen protectors, earphones and more accessories.'],
                        ['icon' => 'fas fa-shield-alt', 'title' => 'Data Recovery', 'desc' => 'Recover lost data from phones, laptops, and hard drives professionally.'],
                    ];
                    foreach ($services as $i => $svc): ?>
                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?= ($i % 2) * 100 ?>">
                        <div class="glass-card service-item">
                            <div class="service-icon">
                                <i class="<?= $svc['icon'] ?>"></i>
                            </div>
                            <div>
                                <h5><?= $svc['title'] ?></h5>
                                <p><?= $svc['desc'] ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══ LATEST TUTORIALS ══ -->
<?php if (!empty($latestPosts)): ?>
<section style="background:var(--dark3)">
    <div class="container">
        <div class="text-center mb-2" data-aos="fade-up">
            <div class="section-divider"></div>
            <p class="text-uppercase" style="color:var(--cyan);font-size:0.8rem;letter-spacing:2px">Learn Daily</p>
            <h2 class="section-title">Latest <span>Tutorials & Tips</span></h2>
            <p class="section-subtitle">Stay updated with expert tech tips, tricks and tutorials</p>
        </div>
        <div class="row gy-4">
            <?php foreach ($latestPosts as $i => $post): ?>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
                <div class="glass-card post-card h-100">
                    <?php if ($post['image']): ?>
                    <img src="<?= UPLOAD_URL . htmlspecialchars($post['image']) ?>" alt="" class="post-card-img">
                    <?php else: ?>
                    <?php $catColors = ['tutorial'=>'#00d4ff22','tip'=>'#00ff8822','trick'=>'#7b2fff22','news'=>'#ff6b3522']; $catIcons = ['tutorial'=>'📖','tip'=>'💡','trick'=>'🔧','news'=>'📰']; ?>
                    <div class="post-card-img-placeholder" style="background:<?= $catColors[$post['category']] ?? '#ffffff10' ?>">
                        <span style="font-size:4rem"><?= $catIcons[$post['category']] ?? '📄' ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="post-card-body">
                        <span class="post-category-badge badge-<?= htmlspecialchars($post['category']) ?>"><?= ucfirst(htmlspecialchars($post['category'])) ?></span>
                        <h5 class="post-card-title"><?= htmlspecialchars($post['title']) ?></h5>
                        <p class="post-card-excerpt"><?= htmlspecialchars($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 100)) ?></p>
                        <div class="post-card-meta">
                            <span><?= timeAgo($post['created_at']) ?></span>
                            <a href="<?= SITE_URL ?>/tutorial.php?slug=<?= urlencode($post['slug']) ?>" class="post-read-link">
                                Read <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="<?= SITE_URL ?>/tutorials.php" class="btn-outline-glow">View All Tutorials <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══ CTA ══ -->
<section class="cta-section" data-aos="fade-up">
    <div class="container">
        <h2>Ready to Start Your <span class="neon-text">Tech Career?</span></h2>
        <p>Join hundreds of graduates who built their careers with Hitechcomputer's professional training programs.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= SITE_URL ?>/courses.php" class="btn-hero-primary"><i class="fas fa-graduation-cap"></i> Enroll Now</a>
            <a href="<?= SITE_URL ?>/contact.php" class="btn-hero-secondary"><i class="fas fa-comments"></i> Ask a Question</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
